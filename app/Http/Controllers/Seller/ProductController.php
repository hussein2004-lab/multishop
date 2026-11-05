<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $shop = $request->user()->shop;

        $products = $shop->products()
            ->with('category')
            ->when($request->query('q'), fn ($q, $term) => $q->where('name', 'like', "%{$term}%"))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('seller.products.index', compact('products', 'shop'));
    }

    public function create()
    {
        return view('seller.products.form', [
            'product'    => new Product(),
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $shop = $request->user()->shop;
        $data = $this->validated($request);

        $data['shop_id'] = $shop->id;
        $data['slug']    = $this->uniqueSlug($data['name']);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        Product::create($data);

        return redirect()->route('seller.products.index')->with('success', __('Produit ajouté.'));
    }

    public function edit(Request $request, Product $product)
    {
        $this->authorizeProduct($request, $product);

        return view('seller.products.form', [
            'product'    => $product,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $this->authorizeProduct($request, $product);

        $data = $this->validated($request);

        if ($product->name !== $data['name']) {
            $data['slug'] = $this->uniqueSlug($data['name'], $product->id);
        }

        if ($request->hasFile('image')) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $product->update($data);

        return redirect()->route('seller.products.index')->with('success', __('Produit mis à jour.'));
    }

    public function destroy(Request $request, Product $product)
    {
        $this->authorizeProduct($request, $product);

        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return back()->with('success', __('Produit supprimé.'));
    }

    // ------------------------------------------------------------ Helpers

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name'        => 'required|string|max:150',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string|max:3000',
            'price'       => 'required|integer|min:100|max:100000000',
            'old_price'   => 'nullable|integer|min:100|max:100000000|gt:price',
            'stock'       => 'required|integer|min:0|max:1000000',
            'image'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);
        unset($data['image']);

        return $data;
    }

    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 1;

        while (Product::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }

    private function authorizeProduct(Request $request, Product $product): void
    {
        abort_unless($product->shop_id === $request->user()->shop->id, 403);
    }
}
