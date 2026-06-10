<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

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
            Log::info('🤖 Claude API call starting', [
                'description' => $description,
                'has_image' => !is_null($imagePath),
                'api_key_set' => !empty($this->apiKey),
                'model' => $this->model
            ]);

            if (empty($this->apiKey)) {
                Log::error('❌ Claude API key not configured');
                return $this->getDefaultAnalysis();
            }

            // Construire le message avec ou sans image
            $content = [];
            
            // Ajouter l'image si disponible
            if ($imagePath) {
                $imageData = $this->downloadAndEncodeImage($imagePath);
                if ($imageData && isset($imageData['data'])) {
                    $content[] = [
                        'type' => 'image',
                        'source' => [
                            'type' => 'base64',
                            'media_type' => $imageData['mime_type'] ?? 'image/jpeg',
                            'data' => $imageData['data']
                        ]
                    ];
                    Log::info('✅ Image ajoutée à l\'analyse Claude', ['mime' => $imageData['mime_type']]);
                }
            }
            
            // Ajouter le texte du prompt
            $promptText = $this->buildFoodAnalysisPrompt($description, $imagePath);
            $content[] = [
                'type' => 'text',
                'text' => $promptText
            ];

            $messages = [
                [
                    'role' => 'user',
                    'content' => $content
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

            Log::info('🤖 Claude API response', [
                'status' => $response->status(),
                'successful' => $response->successful()
            ]);

            if ($response->successful()) {
                $data = $response->json();
                Log::info('✅ Claude API success', ['response_keys' => array_keys($data)]);
                return $this->parseFoodAnalysis($data['content'][0]['text']);
            }

            Log::error('❌ Claude API Error', [
                'status' => $response->status(),
                'response' => $response->body()
            ]);
            return $this->getDefaultAnalysis();

        } catch (\Exception $e) {
            Log::error('❌ Claude Service Error', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
            return $this->getDefaultAnalysis();
        }
    }

    /**
     * Analyze receipt/ticket image for product extraction
     */
    public function analyzeTicket($imagePath)
    {
        try {
            Log::info('🎫 Début analyse ticket avec Claude', ['path' => $imagePath]);
            
            if (empty($this->apiKey)) {
                Log::error('❌ Claude API key not configured');
                return null;
            }

            // Construire le message avec l'image
            $content = [];
            
            // Télécharger et ajouter l'image
            $imageData = $this->downloadAndEncodeImage($imagePath);
            if (!$imageData || !isset($imageData['data'])) {
                Log::error('❌ Impossible de charger l\'image du ticket');
                return null;
            }
            
            $content[] = [
                'type' => 'image',
                'source' => [
                    'type' => 'base64',
                    'media_type' => $imageData['mime_type'] ?? 'image/jpeg',
                    'data' => $imageData['data']
                ]
            ];
            Log::info('✅ Image ticket ajoutée à l\'analyse Claude');
            
            // Ajouter le prompt
            $ticketPrompt = "Tu es un expert en analyse de tickets de caisse. Analyse cette image de ticket et extrais TOUTES les informations des produits.

Pour chaque produit trouvé, retourne un JSON avec cette structure exacte :
{
  \"store_info\": {
    \"name\": \"nom du magasin\",
    \"location\": \"adresse si visible\",
    \"date\": \"date du ticket\"
  },
  \"products\": [
    {
      \"name\": \"nom exact du produit\",
      \"price\": prix_en_euros,
      \"quantity\": quantité,
      \"category\": \"catégorie estimée (alimentaire/hygiène/etc)\"
    }
  ],
  \"total\": montant_total
}

Instructions :
- Extrais TOUS les produits visibles
- Prix en format numérique (ex: 2.45)
- Nom de produit exact du ticket
- Si quantité non visible, mets 1
- Ignore les lignes de sous-totaux, taxes, etc.
- Retourne UNIQUEMENT le JSON, rien d'autre";

            $content[] = [
                'type' => 'text',
                'text' => $ticketPrompt
            ];

            $messages = [
                [
                    'role' => 'user',
                    'content' => $content
                ]
            ];

            $response = Http::withHeaders([
                'x-api-key' => $this->apiKey,
                'content-type' => 'application/json',
                'anthropic-version' => '2023-06-01'
            ])->timeout(90)->post($this->baseUrl . '/messages', [
                'model' => $this->model,
                'max_tokens' => 4096,
                'messages' => $messages
            ]);

            Log::info('🤖 Claude ticket API response', [
                'status' => $response->status(),
                'successful' => $response->successful()
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $content = $data['content'][0]['text'] ?? '';
                
                Log::info('📄 Contenu reçu de Claude', ['content' => substr($content, 0, 200)]);
                
                // Extraire le JSON
                if (preg_match('/{.*}/s', $content, $matches)) {
                    $jsonData = json_decode($matches[0], true);
                    
                    if (json_last_error() === JSON_ERROR_NONE && isset($jsonData['products'])) {
                        Log::info('✅ Ticket analysé avec succès par Claude', ['products_count' => count($jsonData['products'])]);
                        return $jsonData;
                    }
                }
            }

            Log::error('❌ Claude Ticket API Error', [
                'status' => $response->status(),
                'response' => $response->body()
            ]);
            return null;

        } catch (\Exception $e) {
            Log::error('❌ Claude Ticket Service Error', [
                'error' => $e->getMessage(),
                'line' => $e->getLine()
            ]);
            return null;
        }
    }

    /**
     * Analyze nutrition from receipt text and/or image
     */
    public function analyzeNutrition($description, $imagePath = null)
    {
        try {
            // Construire le message avec ou sans image
            $content = [];
            
            // Ajouter l'image si disponible
            if ($imagePath) {
                $imageData = $this->downloadAndEncodeImage($imagePath);
                if ($imageData && isset($imageData['data'])) {
                    $content[] = [
                        'type' => 'image',
                        'source' => [
                            'type' => 'base64',
                            'media_type' => $imageData['mime_type'] ?? 'image/jpeg',
                            'data' => $imageData['data']
                        ]
                    ];
                    Log::info('✅ Image ajoutée à l\'analyse nutrition Claude', ['mime' => $imageData['mime_type']]);
                }
            }
            
            // Ajouter le texte du prompt
            $promptText = $this->buildNutritionPrompt($description, $imagePath);
            $content[] = [
                'type' => 'text',
                'text' => $promptText
            ];

            $messages = [
                [
                    'role' => 'user',
                    'content' => $content
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
        if ($imagePath && empty(trim($description))) {
            // Si seulement l'image est fournie sans description
            $prompt = "Tu es un nutritionniste expert. Analyse le repas visible dans cette image.\n\n";
        } else if ($imagePath) {
            // Si image + description
            $prompt = "Tu es un nutritionniste expert. Analyse ce repas en te basant sur l'image et cette description : '{$description}'\n\n";
        } else {
            // Si seulement la description
            $prompt = "Tu es un nutritionniste expert. Analyse ce repas : '{$description}'\n\n";
        }
        
        $prompt .= "Donne une analyse complète au format suivant :\n\n";
        $prompt .= "---\n\n";
        $prompt .= "**🍽️ Repas identifié** : [nom du plat]\n\n";
        $prompt .= "**🔥 Calories** : [estimation] kcal\n\n";
        $prompt .= "**🥖 Glucides** : [estimation] g\n\n";
        $prompt .= "**🍗 Protéines** : [estimation] g\n\n";
        $prompt .= "**🥑 Lipides** : [estimation] g\n\n";
        $prompt .= "**💚 Score Santé** : [score]/10\n\n";
        $prompt .= "✍️ **Feedback** :\n\n";
        $prompt .= "[Ton analyse personnalisée du repas avec conseils]";

        return $prompt;
    }

    private function buildNutritionPrompt($description, $imagePath = null)
    {
        if ($imagePath && empty(trim($description))) {
            // Si seulement l'image est fournie sans description
            $prompt = "Tu es un nutritionniste expert. Analyse le repas visible dans cette image.\n\n";
        } else if ($imagePath) {
            // Si image + description
            $prompt = "Tu es un nutritionniste expert. Analyse ce repas en te basant sur l'image et cette description : '{$description}'\n\n";
        } else {
            // Si seulement la description
            $prompt = "Tu es un nutritionniste expert. Analyse ce repas : '{$description}'\n\n";
        }
        
        $prompt .= "Donne une analyse complète au format texte lisible avec :\n\n" .
                   "**🍽️ Repas identifié** : [nom du plat]\n\n" .
                   "**🔥 Calories** : [estimation] kcal\n" .
                   "**🥖 Glucides** : [estimation] g\n" .
                   "**🍗 Protéines** : [estimation] g\n" .
                   "**🥑 Lipides** : [estimation] g\n\n" .
                   "**💚 Score Santé** : [score]/10\n\n" .
                   "✍️ **Feedback** : [Ton analyse personnalisée avec conseils]";
        
        return $prompt;
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
        // Retourne directement le texte formaté de Claude
        return [
            'analysis' => $response, // Texte formaté prêt à afficher
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

    /**
     * Télécharge l'image depuis R2 et l'encode en base64
     */
    private function downloadAndEncodeImage($imagePath)
    {
        try {
            Log::info('📥 Téléchargement image pour Claude', ['path' => $imagePath]);
            
            // Si c'est une URL complète, extraire le path R2
            if (strpos($imagePath, 'http') === 0) {
                // Extraire le path depuis l'URL
                // Format: https://domain.com/r2-proxy/posts/filename.jpg
                // ou: https://bucket.r2.cloudflarestorage.com/posts/filename.jpg
                
                if (strpos($imagePath, '/r2-proxy/') !== false) {
                    // URL proxy locale
                    $path = substr($imagePath, strpos($imagePath, '/r2-proxy/') + 10);
                    $path = urldecode($path);
                } elseif (strpos($imagePath, 'r2.cloudflarestorage.com') !== false) {
                    // URL R2 directe - extraire le path après le bucket
                    $parts = parse_url($imagePath);
                    $path = ltrim($parts['path'] ?? '', '/');
                    // Supprimer le nom du bucket s'il est présent dans le path
                    $bucket = env('R2_BUCKET', 'zyma-files');
                    if (strpos($path, $bucket . '/') === 0) {
                        $path = substr($path, strlen($bucket) + 1);
                    }
                } else {
                    // Autre format d'URL
                    Log::error('❌ Format URL non reconnu', ['url' => $imagePath]);
                    return null;
                }
            } else {
                // C'est déjà un path R2
                $path = $imagePath;
            }
            
            Log::info('📂 Path R2 extrait', ['path' => $path]);
            
            // Lire directement depuis R2
            $imageContent = \Storage::disk('r2')->get($path);
            
            if (!$imageContent) {
                Log::error('❌ Image introuvable dans R2', ['path' => $path]);
                return null;
            }
            
            // Détecter le type MIME de l'image
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->buffer($imageContent);
            
            // Encoder en base64
            $base64 = base64_encode($imageContent);
            Log::info('✅ Image encodée en base64', [
                'size' => strlen($base64),
                'mime_type' => $mimeType
            ]);
            
            return [
                'data' => $base64,
                'mime_type' => $mimeType
            ];
            
        } catch (\Exception $e) {
            Log::error('❌ Erreur encodage image', [
                'error' => $e->getMessage(),
                'path' => $imagePath,
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }
}