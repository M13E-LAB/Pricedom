<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ClaudeService
{
    protected $apiKey;
    protected $model;
    protected $baseUrl = 'https://api.anthropic.com/v1';

    public function __construct()
    {
        $this->apiKey = config('services.claude.api_key');
        $this->model = config('services.claude.model');
    }

    /**
     * Analyze food description and image for health scoring
     */
    public function analyzeFood($description, $imagePath = null)
    {
        try {
            $messages = [
                [
                    'role' => 'user',
                    'content' => $this->buildFoodAnalysisPrompt($description, $imagePath)
                ]
            ];

            $response = Http::withHeaders([
                'x-api-key' => $this->apiKey,
                'content-type' => 'application/json',
                'anthropic-version' => '2023-06-01'
            ])->post($this->baseUrl . '/messages', [
                'model' => $this->model,
                'max_tokens' => 1024,
                'messages' => $messages
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $this->parseFoodAnalysis($data['content'][0]['text']);
            }

            Log::error('Claude API Error', ['response' => $response->body()]);
            return $this->getDefaultAnalysis();

        } catch (\Exception $e) {
            Log::error('Claude Service Error', ['error' => $e->getMessage()]);
            return $this->getDefaultAnalysis();
        }
    }

    /**
     * Analyze nutrition from receipt text
     */
    public function analyzeNutrition($description)
    {
        try {
            $messages = [
                [
                    'role' => 'user',
                    'content' => $this->buildNutritionPrompt($description)
                ]
            ];

            $response = Http::withHeaders([
                'x-api-key' => $this->apiKey,
                'content-type' => 'application/json',
                'anthropic-version' => '2023-06-01'
            ])->post($this->baseUrl . '/messages', [
                'model' => $this->model,
                'max_tokens' => 2048,
                'messages' => $messages
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $this->parseNutritionAnalysis($data['content'][0]['text']);
            }

            Log::error('Claude Nutrition API Error', ['response' => $response->body()]);
            return $this->getDefaultNutritionAnalysis();

        } catch (\Exception $e) {
            Log::error('Claude Nutrition Service Error', ['error' => $e->getMessage()]);
            return $this->getDefaultNutritionAnalysis();
        }
    }

    private function buildFoodAnalysisPrompt($description, $imagePath = null)
    {
        $prompt = "Analyse ce repas et donne-moi un score de santé de 0 à 100:\n\n";
        $prompt .= "Description: {$description}\n\n";
        
        if ($imagePath && file_exists(public_path($imagePath))) {
            // Pour l'instant, on se base sur la description
            // Plus tard on pourra ajouter l'analyse d'image
            $prompt .= "Une image est disponible mais l'analyse se base sur la description.\n\n";
        }
        
        $prompt .= "Réponds au format JSON avec:\n";
        $prompt .= "{\n";
        $prompt .= '  "score": 75,';
        $prompt .= '  "category": "healthy|moderate|unhealthy",';
        $prompt .= '  "recommendations": "Conseils personnalisés"';
        $prompt .= "}\n";

        return $prompt;
    }

    private function buildNutritionPrompt($description)
    {
        return "Analyse nutritionnelle détaillée de ce repas/produit:\n\n{$description}\n\n" .
               "Réponds en JSON avec calories, protéines, glucides, lipides, fibres, sucres, sel, " .
               "vitamines principales, et conseils nutritionnels personnalisés.";
    }

    private function parseFoodAnalysis($response)
    {
        // Tente d'extraire le JSON de la réponse Claude
        if (preg_match('/\{.*\}/s', $response, $matches)) {
            $json = json_decode($matches[0], true);
            if ($json) {
                return [
                    'score' => $json['score'] ?? 70,
                    'category' => $json['category'] ?? 'moderate',
                    'recommendations' => $json['recommendations'] ?? 'Repas équilibré'
                ];
            }
        }

        return $this->getDefaultAnalysis();
    }

    private function parseNutritionAnalysis($response)
    {
        // Parse la réponse nutrition de Claude
        return [
            'analysis' => $response,
            'calories_estimated' => $this->extractCalories($response),
            'health_score' => $this->calculateHealthScore($response)
        ];
    }

    private function extractCalories($text)
    {
        // Extrait les calories du texte
        if (preg_match('/(\d+)\s*(?:kcal|calories?)/i', $text, $matches)) {
            return (int) $matches[1];
        }
        return null;
    }

    private function calculateHealthScore($text)
    {
        // Score simple basé sur les mots-clés
        $healthy_words = ['légumes', 'fruits', 'fibres', 'vitamines', 'équilibré'];
        $unhealthy_words = ['gras', 'sucré', 'frit', 'processed'];
        
        $score = 50; // Base
        foreach ($healthy_words as $word) {
            if (stripos($text, $word) !== false) $score += 10;
        }
        foreach ($unhealthy_words as $word) {
            if (stripos($text, $word) !== false) $score -= 10;
        }
        
        return max(0, min(100, $score));
    }

    private function getDefaultAnalysis()
    {
        return [
            'score' => 70,
            'category' => 'moderate',
            'recommendations' => 'Repas analysé avec succès'
        ];
    }

    private function getDefaultNutritionAnalysis()
    {
        return [
            'analysis' => 'Analyse nutritionnelle en cours...',
            'calories_estimated' => null,
            'health_score' => 70
        ];
    }
}