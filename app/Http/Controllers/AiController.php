<?php

namespace App\Http\Controllers;

use App\Services\AiRecommender;
use Illuminate\Http\Request;

class AiController extends Controller
{
    /** Assistant IA : "je veux un téléphone à 100 000 FCFA" → recommandations. */
    public function index(Request $request, AiRecommender $ai)
    {
        $query  = trim((string) $request->query('q'));
        $result = null;

        if (mb_strlen($query) >= 3) {
            $result = $ai->recommend($query, $request->user(), $request->session()->getId());
        }

        $suggestions = [
            __('Je veux un téléphone à 100 000 FCFA'),
            __('Un ordinateur portable pour le travail'),
            __('Une robe en pagne pour moins de 30 000 FCFA'),
            __('Un cadeau pour ma maman, budget 20 000 FCFA'),
        ];

        return view('assistant', [
            'query'       => $query,
            'result'      => $result,
            'suggestions' => $suggestions,
        ]);
    }
}
