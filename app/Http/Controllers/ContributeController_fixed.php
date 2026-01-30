<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ContributeController extends Controller
{
    public function index()
    {
        return view('contribute.index');
    }

    public function scan()
    {
        return view('contribute.scan');
    }

    public function manual()
    {
        return view('contribute.manual');
    }

    public function scanTicket(Request $request)
    {
        $request->validate([
            'ticket_image' => 'required|image|mimes:jpeg,png,jpg|max:15360', // 15MB max
        ]);

        try {
            Log::info('🎫 Début analyse ticket avec OpenAI GPT-4 Vision');
            
            // Utiliser OpenAI API
            $apiKey = env('OPENAI_API_KEY');
            if (empty($apiKey)) {
                Log::error('❌ OPENAI_API_KEY non configurée dans .env');
                return response()->json([
                    'success' => false,
                    'message' => 'Configuration API manquante. Veuillez configurer OpenAI.'
                ], 500);
            }
            Log::info('🔑 Utilisation OpenAI GPT-4o-mini pour analyse ticket');

            Log::info('🔑 Service d\'analyse disponible, début traitement image');

            // Sauvegarder l'image temporairement
            $imagePath = $request->file('ticket_image')->store('temp_tickets', 'public');
            $fullImagePath = Storage::disk('public')->path($imagePath);
            
            if (!file_exists($fullImagePath)) {
                throw new \Exception('Impossible de sauvegarder l\'image');
            }
            
            $imageSize = filesize($fullImagePath);
            Log::info('📷 Image sauvée', ['path' => $imagePath, 'size' => $imageSize]);
            
            // Compresser l'image si elle est trop lourde (>2MB)
            if ($imageSize > 2 * 1024 * 1024) {
                Log::info('🗜️ Compression image nécessaire');
                $this->compressImage($fullImagePath);
                $imageSize = filesize($fullImagePath);
                Log::info('✅ Image compressée', ['new_size' => $imageSize]);
            }
            
            $imageData = base64_encode(file_get_contents($fullImagePath));
            Log::info('🔗 Image encodée en base64', ['length' => strlen($imageData)]);
            
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

            Log::info('🚀 Envoi pour analyse automatique avec OpenAI GPT-4o-mini (PHP direct)...');

            // Utiliser directement l'API OpenAI depuis PHP (plus fiable que Python)
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(90)->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-4o-mini',
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => [
                            ['type' => 'text', 'text' => $ticketPrompt],
                            [
                                'type' => 'image_url',
                                'image_url' => [
                                    'url' => 'data:image/jpeg;base64,' . $imageData
                                ]
                            ]
                        ]
                    ]
                ],
                'max_tokens' => 2000,
                'temperature' => 0.1
            ]);

            Log::info('📡 Réponse OpenAI reçue', ['status' => $response->status()]);

            if ($response->failed()) {
                Log::error('❌ Erreur API OpenAI', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                Storage::disk('public')->delete($imagePath);
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur de connexion. Vérifiez votre connexion internet.'
                ], 500);
            }

            $responseData = $response->json();
            
            if (!isset($responseData['choices'][0]['message']['content'])) {
                Log::error('❌ Structure de réponse OpenAI invalide', ['response' => $responseData]);
                Storage::disk('public')->delete($imagePath);
                return response()->json([
                    'success' => false,
                    'message' => 'Format de réponse invalide.'
                ], 500);
            }
            
            $content = $responseData['choices'][0]['message']['content'];
            Log::info('📄 Contenu reçu d\'OpenAI', ['content' => substr($content, 0, 200)]);
            
            // Extraire le JSON
            if (preg_match('/{.*}/s', $content, $matches)) {
                $jsonData = json_decode($matches[0], true);
                
                if (json_last_error() === JSON_ERROR_NONE && isset($jsonData['products'])) {
                    // Supprimer l'image temporaire
                    Storage::disk('public')->delete($imagePath);
                    
                    Log::info('✅ Ticket analysé avec succès', ['products_count' => count($jsonData['products'])]);
                    
                    return response()->json([
                        'success' => true,
                        'data' => $jsonData
                    ]);
                } else {
                    Log::error('❌ Format invalide ou pas de produits', ['json_error' => json_last_error_msg(), 'content' => $content]);
                }
            } else {
                Log::error('❌ Pas de données trouvées dans la réponse', ['content' => $content]);
            }
            
            // Supprimer l'image temporaire
            Storage::disk('public')->delete($imagePath);
            
            return response()->json([
                'success' => false,
                'message' => 'Impossible d\'analyser ce ticket. Assurez-vous que l\'image est claire et lisible.'
            ], 500);
            
        } catch (\Exception $e) {
            Log::error('❌ Erreur analyse ticket: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            
            // Nettoyer l'image temporaire si elle existe
            if (isset($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur technique lors de l\'analyse. Réessayez dans quelques instants.'
            ], 500);
        }
    }

    private function compressImage($imagePath)
    {
        // Compression d'image simple
        $info = getimagesize($imagePath);
        $mime = $info['mime'];
        
        switch ($mime) {
            case 'image/jpeg':
                $image = imagecreatefromjpeg($imagePath);
                break;
            case 'image/png':
                $image = imagecreatefrompng($imagePath);
                break;
            case 'image/jpg':
                $image = imagecreatefromjpeg($imagePath);
                break;
            default:
                return;
        }
        
        // Redimensionner si nécessaire
        $width = imagesx($image);
        $height = imagesy($image);
        
        if ($width > 1920 || $height > 1920) {
            $ratio = min(1920/$width, 1920/$height);
            $newWidth = $width * $ratio;
            $newHeight = $height * $ratio;
            
            $newImage = imagecreatetruecolor($newWidth, $newHeight);
            imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            
            imagejpeg($newImage, $imagePath, 85);
            imagedestroy($newImage);
        } else {
            imagejpeg($image, $imagePath, 85);
        }
        
        imagedestroy($image);
    }
}
