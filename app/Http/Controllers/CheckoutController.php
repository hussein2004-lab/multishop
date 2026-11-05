<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OtpCode;
use App\Services\CartService;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    // ------------------------------------------------------------ Étape 1 : livraison + paiement

    public function show(Request $request)
    {
        $items = CartService::items();

        if ($items->isEmpty()) {
            return redirect()->route('cart.index');
        }

        $user        = $request->user();
        $byShop      = $items->groupBy(fn ($i) => $i['product']->shop_id);
        $subtotal    = $items->sum('line');
        $deliveryFee = (int) setting('delivery_fee', 1000) * $byShop->count();
        $pointValue  = (int) setting('loyalty_point_value', 10);

        return view('checkout.index', [
            'items'        => $items,
            'byShop'       => $byShop,
            'subtotal'     => $subtotal,
            'deliveryFee'  => $deliveryFee,
            'total'        => $subtotal + $deliveryFee,
            'pointsValue'  => $user->loyalty_points * $pointValue,
            'creditValue'  => $user->store_credit,
        ]);
    }

    public function place(Request $request)
    {
        $items = CartService::items();

        if ($items->isEmpty()) {
            return redirect()->route('cart.index');
        }

        $data = $request->validate([
            'delivery_name'    => 'required|string|max:100',
            'delivery_phone'   => 'required|string|max:30',
            'delivery_address' => 'required|string|max:500',
            'latitude'         => 'nullable|numeric|between:-90,90',
            'longitude'        => 'nullable|numeric|between:-180,180',
            'payment_method'   => 'required|in:card,paypal,cash',
            'note'             => 'nullable|string|max:500',
        ]);

        $user = $request->user();

        // Vérification finale du stock
        foreach ($items as $item) {
            if ($item['qty'] > $item['product']->stock) {
                return redirect()->route('cart.index')
                    ->with('error', __('Stock insuffisant pour ":name".', ['name' => $item['product']->name]));
            }
        }

        $deliveryFee = (int) setting('delivery_fee', 1000);
        $pointValue  = max(1, (int) setting('loyalty_point_value', 10));
        $byShop      = $items->groupBy(fn ($i) => $i['product']->shop_id);
        $grandTotal  = $items->sum('line') + $deliveryFee * $byShop->count();

        // 🎁 Crédit parrainage puis 🏆 points de fidélité
        $creditUsed = $request->boolean('use_credit') ? min($user->store_credit, $grandTotal) : 0;
        $pointsUsed = 0;

        if ($request->boolean('use_points')) {
            $remaining  = $grandTotal - $creditUsed;
            $pointsUsed = min($user->loyalty_points, intdiv($remaining, $pointValue));
        }

        $totalDiscount = $creditUsed + $pointsUsed * $pointValue;
        $groupCode     = strtoupper(Str::random(10));
        $isCash        = $data['payment_method'] === 'cash';

        DB::transaction(function () use ($items, $byShop, $data, $user, $deliveryFee, $creditUsed, $pointsUsed, $pointValue, $totalDiscount, $groupCode, $isCash) {

            // Déduit crédit et points immédiatement (remboursés si annulation)
            if ($creditUsed > 0) {
                $user->decrement('store_credit', $creditUsed);
                $user->creditTransactions()->create([
                    'amount'      => -$creditUsed,
                    'type'        => 'spend',
                    'description' => __('Utilisé sur la commande :code', ['code' => $groupCode]),
                ]);
            }

            if ($pointsUsed > 0) {
                $user->decrement('loyalty_points', $pointsUsed);
                $user->loyaltyTransactions()->create([
                    'points'      => -$pointsUsed,
                    'type'        => 'spend',
                    'description' => __('Utilisés sur la commande :code', ['code' => $groupCode]),
                ]);
            }

            $discountLeft = $totalDiscount;

            foreach ($byShop as $shopId => $shopItems) {
                $subtotal = collect($shopItems)->sum('line');
                $gross    = $subtotal + $deliveryFee;

                $applied      = min($discountLeft, $gross);
                $discountLeft -= $applied;

                $order = Order::create([
                    'group_code'       => $groupCode,
                    'user_id'          => $user->id,
                    'shop_id'          => $shopId,
                    'status'           => 'pending',
                    'subtotal'         => $subtotal,
                    'delivery_fee'     => $deliveryFee,
                    'discount'         => $applied,
                    'total'            => $gross - $applied,
                    'payment_method'   => $data['payment_method'],
                    'payment_status'   => $isCash ? 'pending' : 'awaiting',
                    'delivery_name'    => $data['delivery_name'],
                    'delivery_phone'   => $data['delivery_phone'],
                    'delivery_address' => $data['delivery_address'],
                    'latitude'         => $data['latitude'] ?? null,
                    'longitude'        => $data['longitude'] ?? null,
                    'note'             => $data['note'] ?? null,
                ]);

                foreach ($shopItems as $item) {
                    OrderItem::create([
                        'order_id'     => $order->id,
                        'product_id'   => $item['product']->id,
                        'product_name' => $item['product']->name,
                        'unit_price'   => $item['product']->price,
                        'quantity'     => $item['qty'],
                        'line_total'   => $item['line'],
                    ]);

                    $item['product']->decrement('stock', $item['qty']);
                    $item['product']->increment('sales_count', $item['qty']);
                }
            }
        });

        CartService::clear();

        if ($isCash) {
            return redirect()->route('checkout.success', $groupCode);
        }

        return redirect()->route('payment.show', $groupCode);
    }

    // ------------------------------------------------------------ Étape 2 : paiement en ligne (simulation)

    public function payment(Request $request, string $group)
    {
        $orders = $this->awaitingOrders($request, $group);

        if ($orders->isEmpty()) {
            return redirect()->route('orders.index');
        }

        $method = $orders->first()->payment_method;
        $total  = $orders->sum('total');

        return view($method === 'card' ? 'checkout.payment-card' : 'checkout.payment-paypal', [
            'orders' => $orders,
            'group'  => $group,
            'total'  => $total,
        ]);
    }

    public function processCard(Request $request, string $group)
    {
        $orders = $this->awaitingOrders($request, $group);
        abort_if($orders->isEmpty(), 404);

        $request->validate([
            'card_holder' => 'required|string|max:100',
            'card_number' => ['required', 'string', function ($attr, $value, $fail) {
                if (! $this->passesLuhn($value)) {
                    $fail(__('Numéro de carte invalide.'));
                }
            }],
            'card_expiry' => ['required', 'regex:/^(0[1-9]|1[0-2])\s*\/\s*\d{2}$/', function ($attr, $value, $fail) {
                [$m, $y] = array_map('intval', array_map('trim', explode('/', $value)));
                if (mktime(0, 0, 0, $m + 1, 1, 2000 + $y) < time()) {
                    $fail(__('Carte expirée.'));
                }
            }],
            'card_cvv'    => 'required|digits_between:3,4',
        ]);

        return $this->sendOtp($request, $group, $orders);
    }

    public function processPaypal(Request $request, string $group)
    {
        $orders = $this->awaitingOrders($request, $group);
        abort_if($orders->isEmpty(), 404);

        $request->validate([
            'paypal_email'    => 'required|email',
            'paypal_password' => 'required|string|min:4',
        ]);

        return $this->sendOtp($request, $group, $orders);
    }

    // ------------------------------------------------------------ Étape 3 : vérification OTP (SMS simulé)

    public function otp(Request $request, string $group)
    {
        $orders = $this->awaitingOrders($request, $group);

        if ($orders->isEmpty()) {
            return redirect()->route('orders.index');
        }

        $currentOtp = OtpCode::where('user_id', $request->user()->id)
            ->where('group_code', $group)
            ->whereNull('used_at')
            ->latest()
            ->first();

        if (! $currentOtp) {
            return redirect()->route('payment.show', $group);
        }

        return view('checkout.otp', [
            'group'    => $group,
            'total'    => $orders->sum('total'),
            'phone'    => $orders->first()->delivery_phone,
            'demoCode' => SmsService::isDemoMode() ? $currentOtp->code : null,
        ]);
    }

    public function verifyOtp(Request $request, string $group)
    {
        $orders = $this->awaitingOrders($request, $group);
        abort_if($orders->isEmpty(), 404);

        $request->validate(['code' => 'required|digits:6']);

        $otp = OtpCode::where('user_id', $request->user()->id)
            ->where('group_code', $group)
            ->whereNull('used_at')
            ->latest()
            ->first();

        if (! $otp || $otp->isExpired()) {
            return back()->withErrors(['code' => __('Code expiré. Cliquez sur "Renvoyer le code".')]);
        }

        if ($otp->hasTooManyAttempts()) {
            return redirect()->route('payment.show', $group)
                ->with('error', __('Trop de tentatives. Recommencez le paiement.'));
        }

        if (! hash_equals($otp->code, $request->input('code'))) {
            $otp->increment('attempts');
            $left = OtpCode::MAX_ATTEMPTS - $otp->attempts;

            return back()->withErrors(['code' => __('Code incorrect. Il vous reste :count essai(s).', ['count' => $left])]);
        }

        $otp->update(['used_at' => now()]);

        foreach ($orders as $order) {
            $order->update(['payment_status' => 'paid', 'paid_at' => now()]);
        }

        return redirect()->route('checkout.success', $group)
            ->with('success', __('Paiement confirmé. Merci !'));
    }

    public function resendOtp(Request $request, string $group)
    {
        $orders = $this->awaitingOrders($request, $group);
        abort_if($orders->isEmpty(), 404);

        $this->sendOtp($request, $group, $orders);

        return redirect()->route('payment.otp', $group)->with('success', __('Un nouveau code vous a été envoyé.'));
    }

    // ------------------------------------------------------------ Confirmation

    public function success(Request $request, string $group)
    {
        $orders = Order::with(['shop', 'items'])
            ->where('group_code', $group)
            ->where('user_id', $request->user()->id)
            ->get();

        abort_if($orders->isEmpty(), 404);

        return view('checkout.success', [
            'orders' => $orders,
            'group'  => $group,
            'total'  => $orders->sum('total'),
        ]);
    }

    // ------------------------------------------------------------ Helpers

    private function awaitingOrders(Request $request, string $group)
    {
        return Order::with('shop')
            ->where('group_code', $group)
            ->where('user_id', $request->user()->id)
            ->where('payment_status', 'awaiting')
            ->where('status', '!=', 'cancelled')
            ->get();
    }

    private function sendOtp(Request $request, string $group, $orders)
    {
        $otp = OtpCode::generateFor($request->user(), $group);

        $phone = $orders->first()->delivery_phone ?: ($request->user()->phone ?? '');

        SmsService::send($phone, __('MultiShop : votre code de confirmation est :code. Valable 5 minutes.', ['code' => $otp->code]));

        return redirect()->route('payment.otp', $group);
    }

    /** Validation Luhn du numéro de carte (comme les vraies banques). */
    private function passesLuhn(string $number): bool
    {
        $digits = strrev(preg_replace('/\D/', '', $number));
        $length = strlen($digits);

        if ($length < 12 || $length > 19) {
            return false;
        }

        $sum = 0;
        for ($i = 0; $i < $length; $i++) {
            $d = (int) $digits[$i];
            if ($i % 2 === 1) {
                $d *= 2;
                if ($d > 9) {
                    $d -= 9;
                }
            }
            $sum += $d;
        }

        return $sum % 10 === 0;
    }
}
