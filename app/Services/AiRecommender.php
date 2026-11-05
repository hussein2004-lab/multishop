<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductView;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Assistant IA de MultiShop.
 *
 * Comprend une demande en langage naturel ("je veux un téléphone à 100 000 FCFA")
 * et recommande les produits les plus pertinents.
 *
 * Moteur hybride :
 *  1. Si ANTHROPIC_API_KEY est renseignée dans .env, l'API Claude analyse la
 *     demande et classe les produits.
 *  2. Sinon (ou en cas d'erreur réseau), moteur local : extraction du budget,
 *     de la catégorie et des mots-clés, puis scoring des produits.
 */
class AiRecommender
{
    /** Mots vides ignorés lors de l'analyse de la demande. */
    private const STOPWORDS = [
        'je', 'tu', 'il', 'elle', 'on', 'nous', 'vous', 'ils', 'veux', 'voudrais', 'souhaite',
        'cherche', 'recherche', 'aimerais', 'desire', 'acheter', 'commander', 'trouver', 'avoir',
        'un', 'une', 'des', 'du', 'de', 'le', 'la', 'les', 'l', 'd', 'au', 'aux', 'ce', 'cette',
        'mon', 'ma', 'mes', 'ton', 'ta', 'tes', 'son', 'sa', 'ses', 'a', 'et', 'ou', 'en', 'dans',
        'sur', 'pour', 'avec', 'sans', 'par', 'qui', 'que', 'quoi', 'est', 'sont', 'suis', 'ai',
        'moins', 'plus', 'entre', 'environ', 'autour', 'vers', 'maximum', 'minimum', 'max', 'min',
        'budget', 'prix', 'cout', 'coute', 'fcfa', 'franc', 'francs', 'cfa', 'xof', 'f',
        'bonjour', 'salut', 'svp', 'stp', 'merci', 'besoin', 'faut', 'me', 'moi', 'donne',
        'montre', 'propose', 'recommande', 'quelque', 'chose', 'bon', 'bonne', 'pas', 'cher', 'chere',
        'qualite', 'nouveau', 'nouvelle', 'mille', 'million', 'millions', 'k',
    ];

    /** Synonymes vers slug de catégorie. */
    private const CATEGORY_SYNONYMS = [
        'telephones-tablettes' => [
            'telephone', 'telephones', 'portable', 'portables', 'smartphone', 'smartphones',
            'phone', 'tel', 'tablette', 'tablettes', 'iphone', 'samsung', 'tecno', 'infinix',
            'itel', 'redmi', 'xiaomi', 'android', 'ipad', 'mobile', 'mobiles',
        ],
        'informatique-electronique' => [
            'ordinateur', 'ordinateurs', 'pc', 'laptop', 'portatif', 'imprimante', 'imprimantes',
            'ecran', 'ecrans', 'clavier', 'claviers', 'souris', 'informatique', 'electronique',
            'ecouteur', 'ecouteurs', 'casque', 'casques', 'powerbank', 'batterie', 'chargeur',
            'montre', 'montres', 'connectee', 'bluetooth', 'hp', 'dell', 'lenovo', 'canon', 'usb',
        ],
        'mode-vetements' => [
            'robe', 'robes', 'chemise', 'chemises', 'vetement', 'vetements', 'habit', 'habits',
            'pagne', 'pagnes', 'wax', 'sac', 'sacs', 'chaussure', 'chaussures', 'basket', 'baskets',
            'boubou', 'boubous', 'mode', 'pantalon', 'jean', 'jupe', 'tenue', 'bazin', 'tissu',
        ],
        'beaute-sante' => [
            'beaute', 'sante', 'savon', 'savons', 'karite', 'soin', 'soins', 'creme', 'cremes',
            'parfum', 'parfums', 'maquillage', 'cosmetique', 'cosmetiques', 'huile',
        ],
        'maison-cuisine' => [
            'maison', 'cuisine', 'mixeur', 'blender', 'ventilateur', 'ventilateurs', 'marmite',
            'marmites', 'casserole', 'meuble', 'meubles', 'electromenager', 'frigo', 'refrigerateur',
            'matelas', 'drap', 'rideau',
        ],
        'alimentation' => [
            'riz', 'attieke', 'nourriture', 'alimentation', 'aliment', 'aliments', 'manger',
            'provision', 'provisions', 'courses', 'epicerie', 'sucre', 'tomate', 'pate', 'lait',
        ],
        'sport-loisirs' => [
            'sport', 'sports', 'ballon', 'ballons', 'football', 'foot', 'velo', 'velos',
            'fitness', 'musculation', 'jeu', 'jeux', 'loisir', 'loisirs',
        ],
        'bebes-enfants' => [
            'bebe', 'bebes', 'enfant', 'enfants', 'jouet', 'jouets', 'poussette', 'couche', 'couches',
        ],
    ];

    /**
     * Point d'entrée : retourne les produits recommandés + un message explicatif.
     *
     * @return array{products: Collection<int, Product>, message: string, engine: string}
     */
    public function recommend(string $query, ?User $user = null, ?string $sessionId = null): array
    {
        $query = trim($query);
        $limit = (int) setting('ai_max_results', 6);

        if (config('services.anthropic.key')) {
            try {
                return $this->recommendWithClaude($query, $limit);
            } catch (\Throwable $e) {
                Log::warning('Assistant IA : repli sur le moteur local. '.$e->getMessage());
            }
        }

        return $this->recommendLocally($query, $user, $sessionId, $limit);
    }

    // =================================================================
    // MOTEUR LOCAL
    // =================================================================

    private function recommendLocally(string $query, ?User $user, ?string $sessionId, int $limit): array
    {
        $normalized = $this->normalize($query);
        $budget     = $this->extractBudget($normalized);
        $catSlug    = $this->detectCategory($normalized);
        $keywords   = $this->extractKeywords($normalized);
        $preferred  = $this->preferredCategoryIds($user, $sessionId);

        // --- Sélection des candidats ---
        $candidates = $this->fetchCandidates($catSlug, $budget);

        // Trop restrictif ? On relâche progressivement les filtres.
        if ($candidates->isEmpty() && $budget) {
            $candidates = $this->fetchCandidates($catSlug, null);
        }

        if ($candidates->isEmpty() && $catSlug) {
            $candidates = $this->fetchCandidates(null, $budget);

            if ($candidates->isEmpty()) {
                $candidates = $this->fetchCandidates(null, null);
            }
        }

        if ($candidates->isEmpty()) {
            $candidates = Product::visible()->inStock()->with(['shop', 'category'])->trending()->limit(50)->get();
        }

        // --- Scoring ---
        $target = $budget['target'] ?? null;

        $scored = $candidates->map(function (Product $p) use ($target, $keywords, $preferred) {
            $score = 0.0;

            // Proximité avec le budget demandé
            if ($target) {
                $distance = abs($p->price - $target) / max($target, 1);
                $score += max(0, 40 - $distance * 100);
            }

            // Mots-clés trouvés dans le nom / la description
            $name = $this->normalize($p->name);
            $desc = $this->normalize((string) $p->description);
            foreach ($keywords as $kw) {
                if (str_contains($name, $kw)) {
                    $score += 30;
                } elseif (str_contains($desc, $kw)) {
                    $score += 10;
                }
            }

            // Fiabilité du vendeur
            $score += ($p->shop->reliability ?? 70) / 100 * 15;

            // Popularité (tendance)
            $score += min(10, ($p->sales_count * 3 + $p->views_count) / 100);

            // Préférences de navigation du client
            if ($p->category_id && in_array($p->category_id, $preferred)) {
                $score += 12;
            }

            return ['product' => $p, 'score' => $score];
        });

        $products = $scored->sortByDesc('score')->take($limit)->pluck('product')->values();

        return [
            'products' => $products,
            'message'  => $this->buildMessage($products->count(), $budget, $catSlug),
            'engine'   => 'local',
        ];
    }

    private function fetchCandidates(?string $catSlug, ?array $budget): Collection
    {
        $q = Product::visible()->inStock()->with(['shop', 'category']);

        if ($catSlug) {
            $q->whereHas('category', fn ($c) => $c->where('slug', $catSlug));
        }

        if ($budget) {
            if (isset($budget['min'])) {
                $q->where('price', '>=', $budget['min']);
            }
            if (isset($budget['max'])) {
                $q->where('price', '<=', $budget['max']);
            }
        }

        return $q->limit(100)->get();
    }

    /** Minuscules + suppression des accents. */
    private function normalize(string $text): string
    {
        $text = mb_strtolower($text);
        $text = strtr($text, [
            'à' => 'a', 'â' => 'a', 'ä' => 'a', 'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
            'î' => 'i', 'ï' => 'i', 'ô' => 'o', 'ö' => 'o', 'ù' => 'u', 'û' => 'u', 'ü' => 'u',
            'ç' => 'c', 'œ' => 'oe',
        ]);

        return preg_replace('/\s+/', ' ', $text);
    }

    /**
     * Extrait le budget de la demande.
     * Gère : "100 000", "100000", "100k", "100 mille", "1 million",
     * "moins de X", "plus de X", "entre X et Y", "à X FCFA"...
     *
     * @return array{min?: int, max?: int, target?: int}|null
     */
    private function extractBudget(string $text): ?array
    {
        $amount = '(\d[\d\s.,]*)\s*(k|mille|million|millions)?';

        // "entre X et Y"
        if (preg_match('/entre\s+'.$amount.'\s*(?:fcfa|francs?|cfa|xof|f)?\s+et\s+'.$amount.'/u', $text, $m)) {
            $min = $this->parseAmount($m[1], $m[2] ?? '');
            $max = $this->parseAmount($m[3], $m[4] ?? '');
            if ($min && $max) {
                return ['min' => min($min, $max), 'max' => max($min, $max), 'target' => (int) (($min + $max) / 2)];
            }
        }

        // "moins de X", "max X", "pas plus de X"...
        if (preg_match('/(?:moins\s+de|maximum|max|au\s+plus|pas\s+plus\s+de|en\s+dessous\s+de|inferieur\s+a|jusqu\'?a)\s+'.$amount.'/u', $text, $m)) {
            if ($v = $this->parseAmount($m[1], $m[2] ?? '')) {
                return ['max' => $v, 'target' => (int) ($v * 0.85)];
            }
        }

        // "plus de X", "minimum X", "à partir de X"...
        if (preg_match('/(?:plus\s+de|minimum|min|au\s+moins|a\s+partir\s+de|superieur\s+a)\s+'.$amount.'/u', $text, $m)) {
            if ($v = $this->parseAmount($m[1], $m[2] ?? '')) {
                return ['min' => $v, 'target' => (int) ($v * 1.2)];
            }
        }

        // Montant simple : "un téléphone à 100 000 FCFA", "budget 50k"
        if (preg_match_all('/'.$amount.'\s*(?:fcfa|francs?|cfa|xof|f\b)?/u', $text, $all, PREG_SET_ORDER)) {
            foreach ($all as $m) {
                $v = $this->parseAmount($m[1], $m[2] ?? '');
                if ($v && $v >= 500) {  // ignore les petits nombres (quantités, pouces...)
                    return ['min' => (int) ($v * 0.7), 'max' => (int) ($v * 1.3), 'target' => $v];
                }
            }
        }

        return null;
    }

    private function parseAmount(string $raw, string $suffix): ?int
    {
        $clean = preg_replace('/[\s.,\x{202F}\x{00A0}]/u', '', $raw);

        if ($clean === '' || ! ctype_digit($clean)) {
            return null;
        }

        $value = (int) $clean;

        $value *= match ($suffix) {
            'k', 'mille'          => 1000,
            'million', 'millions' => 1000000,
            default               => 1,
        };

        return $value > 0 ? $value : null;
    }

    /** Détecte la catégorie demandée (synonymes + noms des catégories en base). */
    private function detectCategory(string $text): ?string
    {
        $words = preg_split('/[^a-z0-9]+/', $text, -1, PREG_SPLIT_NO_EMPTY);

        foreach (self::CATEGORY_SYNONYMS as $slug => $synonyms) {
            foreach ($words as $word) {
                if (in_array($word, $synonyms)) {
                    return $slug;
                }
            }
        }

        // Catégories ajoutées par l'admin après l'installation
        foreach (Category::all() as $category) {
            $catWords = preg_split('/[^a-z0-9]+/', $this->normalize($category->name), -1, PREG_SPLIT_NO_EMPTY);
            foreach ($catWords as $cw) {
                if (mb_strlen($cw) > 3 && in_array($cw, $words)) {
                    return $category->slug;
                }
            }
        }

        return null;
    }

    /** @return string[] */
    private function extractKeywords(string $text): array
    {
        $words = preg_split('/[^a-z0-9]+/', $text, -1, PREG_SPLIT_NO_EMPTY);

        return collect($words)
            ->filter(fn ($w) => mb_strlen($w) > 2 && ! in_array($w, self::STOPWORDS) && ! ctype_digit($w))
            ->unique()
            ->values()
            ->all();
    }

    /** Catégories les plus consultées par ce client (adaptation aux préférences). */
    private function preferredCategoryIds(?User $user, ?string $sessionId): array
    {
        $q = ProductView::query()
            ->whereNotNull('category_id')
            ->where('created_at', '>=', now()->subDays(30));

        if ($user) {
            $q->where('user_id', $user->id);
        } elseif ($sessionId) {
            $q->where('session_id', $sessionId);
        } else {
            return [];
        }

        return $q->selectRaw('category_id, COUNT(*) as nb')
            ->groupBy('category_id')
            ->orderByDesc('nb')
            ->limit(3)
            ->pluck('category_id')
            ->all();
    }

    private function buildMessage(int $count, ?array $budget, ?string $catSlug): string
    {
        if ($count === 0) {
            return __("Je n'ai pas trouvé de produit correspondant exactement à votre demande. Essayez avec d'autres mots ou un budget différent.");
        }

        $category = $catSlug ? Category::where('slug', $catSlug)->value('name') : null;

        if ($budget && isset($budget['target']) && $category) {
            return __('Voici :count produit(s) de la catégorie ":category" autour de :budget :', [
                'count' => $count, 'category' => $category, 'budget' => fcfa($budget['target']),
            ]);
        }

        if ($budget && isset($budget['target'])) {
            return __('Voici :count produit(s) correspondant à votre budget d\'environ :budget :', [
                'count' => $count, 'budget' => fcfa($budget['target']),
            ]);
        }

        if ($category) {
            return __('Voici :count produit(s) de la catégorie ":category" qui devraient vous plaire :', [
                'count' => $count, 'category' => $category,
            ]);
        }

        return __('Voici :count produit(s) qui correspondent à votre recherche :', ['count' => $count]);
    }

    // =================================================================
    // MOTEUR CLAUDE (API Anthropic)
    // =================================================================

    private function recommendWithClaude(string $query, int $limit): array
    {
        $catalog = Product::visible()->inStock()->with(['shop', 'category'])
            ->limit(150)
            ->get()
            ->map(fn (Product $p) => [
                'id'          => $p->id,
                'nom'         => $p->name,
                'prix_fcfa'   => $p->price,
                'categorie'   => $p->category?->name,
                'boutique'    => $p->shop->name,
                'fiabilite'   => $p->shop->reliability,
                'description' => Str::limit((string) $p->description, 120),
            ]);

        $system = "Tu es l'assistant shopping de MultiShop, une marketplace ivoirienne (prix en FCFA). "
            ."On te donne la demande d'un client et le catalogue. Choisis les produits les plus pertinents "
            ."(maximum {$limit}), en respectant le budget si mentionné. "
            ."Réponds UNIQUEMENT avec un JSON valide de la forme : "
            .'{"product_ids": [1, 2], "message": "..."} '
            ."où message est une courte phrase chaleureuse dans la langue du client (par défaut le français) expliquant ton choix.";

        $response = Http::withHeaders([
            'x-api-key'         => config('services.anthropic.key'),
            'anthropic-version' => '2023-06-01',
        ])->timeout(15)->post('https://api.anthropic.com/v1/messages', [
            'model'      => config('services.anthropic.model'),
            'max_tokens' => 500,
            'system'     => $system,
            'messages'   => [[
                'role'    => 'user',
                'content' => "Demande du client : \"{$query}\"\n\nCatalogue :\n".json_encode($catalog, JSON_UNESCAPED_UNICODE),
            ]],
        ]);

        if (! $response->successful()) {
            throw new \RuntimeException('API Anthropic : HTTP '.$response->status());
        }

        $text = collect($response->json('content', []))->firstWhere('type', 'text')['text'] ?? '';

        if (! preg_match('/\{.*\}/s', $text, $m)) {
            throw new \RuntimeException('Réponse IA illisible');
        }

        $data = json_decode($m[0], true, 512, JSON_THROW_ON_ERROR);
        $ids  = array_slice(array_map('intval', $data['product_ids'] ?? []), 0, $limit);

        $products = Product::visible()->with(['shop', 'category'])
            ->whereIn('id', $ids)
            ->get()
            ->sortBy(fn ($p) => array_search($p->id, $ids))
            ->values();

        if ($products->isEmpty()) {
            throw new \RuntimeException('Aucun produit retourné par l\'IA');
        }

        return [
            'products' => $products,
            'message'  => $data['message'] ?? __('Voici mes recommandations :'),
            'engine'   => 'claude',
        ];
    }
}
