<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HealthAnalysisService
{
    public function analyzePost($description, $imagePath = null)
    {
        try {
            // Analyse basée sur le texte de description
            $textScore = $this->analyzeDescription($description);
            
            // Si on a une image, analyse avec OpenAI Vision
            $imageScore = 0;
            $imageAnalysis = null;
            
            if ($imagePath && config('services.openai.api_key')) {
                $imageAnalysis = $this->analyzeImageWithAI($imagePath, $description);
                $imageScore = $imageAnalysis['score'] ?? 0;
            }
            
            // Score final (moyenne pondérée)
            $finalScore = $imagePath ? 
                round(($textScore * 0.3) + ($imageScore * 0.7)) : 
                $textScore;
            
            // Catégorisation
            $category = $this->getHealthCategory($finalScore);
            
            return [
                'score' => max(0, min(100, $finalScore)),
                'category' => $category,
                'text_analysis' => $this->getTextAnalysisDetails($description),
                'image_analysis' => $imageAnalysis,
                'recommendations' => $this->getRecommendations($finalScore, $description)
            ];
            
        } catch (\Exception $e) {
            Log::error('Erreur analyse santé: ' . $e->getMessage());
            
            // Fallback sur analyse textuelle simple
            $score = $this->analyzeDescription($description);
            return [
                'score' => $score,
                'category' => $this->getHealthCategory($score),
                'text_analysis' => $this->getTextAnalysisDetails($description),
                'image_analysis' => null,
                'recommendations' => []
            ];
        }
    }
    
    private function analyzeDescription($description)
    {
        $score = 50; // Score de base
        $text = strtolower($description);
        
        // Mots-clés positifs (augmentent le score)
        $positiveKeywords = [
            'légumes' => 15, 'fruits' => 15, 'salade' => 12, 'bio' => 10,
            'quinoa' => 12, 'avocat' => 10, 'saumon' => 10, 'poisson' => 8,
            'épinards' => 10, 'brocoli' => 10, 'tomate' => 8, 'carotte' => 8,
            'complet' => 8, 'graines' => 8, 'noix' => 6, 'huile d\'olive' => 8,
            'fait maison' => 10, 'frais' => 6, 'naturel' => 6, 'cru' => 8,
            'vapeur' => 8, 'grillé' => 6, 'smoothie' => 8, 'jus' => 4
        ];
        
        // Mots-clés négatifs (diminuent le score)
        $negativeKeywords = [
            'frites' => -15, 'burger' => -12, 'pizza' => -10, 'mcdo' => -20,
            'coca' => -10, 'soda' => -8, 'chips' => -10, 'bonbon' => -12,
            'chocolat' => -6, 'gâteau' => -8, 'pâtisserie' => -8, 'friture' => -15,
            'fast food' => -18, 'kebab' => -12, 'tacos' => -10, 'nutella' => -8,
            'mayo' => -6, 'ketchup' => -4, 'sucré' => -6, 'gras' => -8,
            'industriel' => -8, 'transformé' => -6, 'conservateur' => -6
        ];
        
        // Analyser les mots-clés positifs
        foreach ($positiveKeywords as $keyword => $points) {
            if (strpos($text, $keyword) !== false) {
                $score += $points;
            }
        }
        
        // Analyser les mots-clés négatifs
        foreach ($negativeKeywords as $keyword => $points) {
            if (strpos($text, $keyword) !== false) {
                $score += $points; // $points est déjà négatif
            }
        }
        
        return max(0, min(100, $score));
    }
    
    private function analyzeImageWithAI($imagePath, $description)
    {
        try {
            $apiKey = config('services.openai.api_key');
            if (!$apiKey) {
                return ['score' => 50, 'analysis' => 'API key manquante'];
            }
            
            // Encoder l'image en base64
            $imageData = base64_encode(file_get_contents($imagePath));
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-4o-mini',
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => [
                            [
                                'type' => 'text',
                                'text' => "Analyse ce repas et donne un score de santé de 0 à 100. Description: {$description}. 
                                
                                Critères:
                                - Légumes/fruits: +20 points
                                - Protéines saines: +15 points  
                                - Céréales complètes: +10 points
                                - Cuisson saine: +10 points
                                - Aliments transformés: -15 points
                                - Friture/gras: -20 points
                                - Sucre ajouté: -10 points
                                
                                Réponds UNIQUEMENT avec un JSON: {\"score\": X, \"analysis\": \"explication courte\"}"
                            ],
                            [
                                'type' => 'image_url',
                                'image_url' => [
                                    'url' => "data:image/jpeg;base64,{$imageData}"
                                ]
                            ]
                        ]
                    ]
                ],
                'max_tokens' => 300
            ]);
            
            if ($response->successful()) {
                $content = $response->json()['choices'][0]['message']['content'];
                $analysis = json_decode($content, true);
                
                return [
                    'score' => $analysis['score'] ?? 50,
                    'analysis' => $analysis['analysis'] ?? 'Analyse non disponible'
                ];
            }
            
            return ['score' => 50, 'analysis' => 'Erreur API'];
            
        } catch (\Exception $e) {
            Log::error('Erreur analyse image IA: ' . $e->getMessage());
            return ['score' => 50, 'analysis' => 'Erreur technique'];
        }
    }
    
    private function getHealthCategory($score)
    {
        if ($score >= 80) return 'Excellent';
        if ($score >= 60) return 'Bon';
        if ($score >= 40) return 'Moyen';
        if ($score >= 20) return 'Faible';
        return 'Très faible';
    }
    
    private function getTextAnalysisDetails($description)
    {
        $text = strtolower($description);
        $details = [];
        
        // Détection d'éléments spécifiques
        if (strpos($text, 'légumes') !== false || strpos($text, 'salade') !== false) {
            $details[] = '🥬 Riche en légumes';
        }
        
        if (strpos($text, 'fruits') !== false) {
            $details[] = '🍎 Contient des fruits';
        }
        
        if (strpos($text, 'poisson') !== false || strpos($text, 'saumon') !== false) {
            $details[] = '🐟 Protéines de qualité';
        }
        
        if (strpos($text, 'fait maison') !== false || strpos($text, 'frais') !== false) {
            $details[] = '🏠 Fait maison';
        }
        
        if (strpos($text, 'frites') !== false || strpos($text, 'friture') !== false) {
            $details[] = '🍟 Aliment frit';
        }
        
        if (strpos($text, 'fast food') !== false || strpos($text, 'mcdo') !== false) {
            $details[] = '🍔 Fast food';
        }
        
        return $details;
    }
    
    private function getRecommendations($score, $description)
    {
        $recommendations = [];
        
        if ($score < 60) {
            $recommendations[] = 'Ajouter plus de légumes à vos repas';
            $recommendations[] = 'Privilégier les cuissons vapeur ou grillées';
            $recommendations[] = 'Réduire les aliments transformés';
        }
        
        if ($score >= 80) {
            $recommendations[] = 'Excellent choix alimentaire !';
            $recommendations[] = 'Continuez sur cette voie';
        }
        
        return $recommendations;
    }
}