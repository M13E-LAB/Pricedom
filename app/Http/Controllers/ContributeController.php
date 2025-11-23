<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Arr;
use App\Models\Contribution;
use App\Services\GamificationService;

class ContributeController extends Controller
{
    protected $gamificationService;

    public function __construct(GamificationService $gamificationService)
    {
        $this->gamificationService = $gamificationService;
    }

    public function index()
    {
        $user = Auth::user();
        $stats = $this->gamificationService->getGamificationStats($user);
        
        return view('contribute.index', compact('stats'));
    }

    public function scan()
    {
        return view('contribute.scan');
    }

    public function scanTicket(Request $request)
    {
        $request->validate([
            'ticket_image' => 'required|image|mimes:jpeg,png,jpg|max:15360', // 15MB max
        ]);

        try {
            \Log::info('🎫 Début analyse ticket avec OpenAI GPT-4 Vision');
            
            // Utiliser OpenAI API
            $apiKey = env('OPENAI_API_KEY');
            if (empty($apiKey)) {
                \Log::error('❌ OPENAI_API_KEY non configurée dans .env');
                return response()->json([
                    'success' => false,
                    'message' => 'Configuration API manquante. Veuillez configurer OpenAI.'
                ], 500);
            }
            \Log::info('🔑 Utilisation OpenAI GPT-4o-mini pour analyse ticket');

            \Log::info('🔑 Service d\'analyse disponible, début traitement image');

            // Sauvegarder l'image temporairement
            $imagePath = $request->file('ticket_image')->store('temp_tickets', 'public');
            $fullImagePath = Storage::disk('public')->path($imagePath);
            
            if (!file_exists($fullImagePath)) {
                throw new \Exception('Impossible de sauvegarder l\'image');
            }
            
            $imageSize = filesize($fullImagePath);
            \Log::info('📷 Image sauvée', ['path' => $imagePath, 'size' => $imageSize]);
            
            // Compresser l'image si elle est trop lourde (>2MB)
            if ($imageSize > 2 * 1024 * 1024) {
                \Log::info('🗜️ Compression image nécessaire');
                $this->compressImage($fullImagePath);
                $imageSize = filesize($fullImagePath);
                \Log::info('✅ Image compressée', ['new_size' => $imageSize]);
            }
            
            $imageData = base64_encode(file_get_contents($fullImagePath));
            \Log::info('🔗 Image encodée en base64', ['length' => strlen($imageData)]);
            
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

            \Log::info('🚀 Envoi pour analyse automatique avec OpenAI GPT-4o-mini (Python)...');

            // Utiliser le script Python avec OpenAI
            $pythonScript = base_path('analyze_ticket.py');
            $pythonScriptEscaped = escapeshellarg($pythonScript);
            $promptEscaped = escapeshellarg($ticketPrompt);
            $imagePathEscaped = escapeshellarg($fullImagePath);
            
            // Définir la variable d'environnement OPENAI_API_KEY pour Python
            putenv("OPENAI_API_KEY={$apiKey}");
            
            \Log::info('📷 Lancement script Python', ['image' => $fullImagePath, 'script' => $pythonScript]);

            // Exécuter le script Python avec chemins correctement échappés
            $command = "python3 {$pythonScriptEscaped} {$imagePathEscaped} {$promptEscaped} 2>&1";
            $output = shell_exec($command);
            
            \Log::info('📡 Réponse Python reçue', ['output' => substr($output, 0, 500)]);

            if (empty($output)) {
                \Log::error('❌ Pas de réponse du script Python');
                Storage::disk('public')->delete($imagePath);
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de l\'exécution du script d\'analyse.'
                ], 500);
            }

            // Décoder la réponse JSON du script Python
            $pythonResult = json_decode($output, true);
            
            if (!$pythonResult || !isset($pythonResult['success'])) {
                \Log::error('❌ Réponse Python invalide', ['output' => $output]);
                Storage::disk('public')->delete($imagePath);
                return response()->json([
                    'success' => false,
                    'message' => 'Format de réponse invalide.'
                ], 500);
            }

            if (!$pythonResult['success']) {
                $errorMsg = $pythonResult['error'] ?? 'unknown';
                \Log::error('❌ Erreur Python', ['error' => $errorMsg]);
                Storage::disk('public')->delete($imagePath);
                
                // Détecter rate limit Replicate
                if (strpos($errorMsg, '429') !== false || strpos($errorMsg, 'throttled') !== false || strpos($errorMsg, 'rate limit') !== false) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Limite de requêtes atteinte. Attendez 1 minute et réessayez (limite gratuite Replicate : 6 req/min).'
                    ], 429);
                }
                
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur: ' . $errorMsg
                ], 500);
            }

            $content = $pythonResult['content'] ?? '';
            
            \Log::info('📝 Contenu reçu du service d\'analyse', ['content_length' => strlen($content), 'preview' => substr($content, 0, 200)]);
            
            // Extraire le JSON
            if (preg_match('/{.*}/s', $content, $matches)) {
                $jsonData = json_decode($matches[0], true);
                
                if (json_last_error() === JSON_ERROR_NONE && isset($jsonData['products'])) {
                    // Supprimer l'image temporaire
                    Storage::disk('public')->delete($imagePath);
                    
                    \Log::info('✅ Ticket analysé avec succès', ['products_count' => count($jsonData['products'])]);
                    
                    return response()->json([
                        'success' => true,
                        'data' => $jsonData
                    ]);
                } else {
                    \Log::error('❌ Format invalide ou pas de produits', ['json_error' => json_last_error_msg(), 'content' => $content]);
                }
            } else {
                \Log::error('❌ Pas de données trouvées dans la réponse', ['content' => $content]);
            }
            
            // Supprimer l'image temporaire
            Storage::disk('public')->delete($imagePath);
            
            return response()->json([
                'success' => false,
                'message' => 'Impossible d\'analyser ce ticket. Assurez-vous que l\'image est claire et lisible.'
            ], 500);
            
        } catch (\Exception $e) {
            \Log::error('❌ Erreur analyse ticket: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            
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
        $imageInfo = getimagesize($imagePath);
        $imageType = $imageInfo[2];
        
        switch ($imageType) {
            case IMAGETYPE_JPEG:
                $image = imagecreatefromjpeg($imagePath);
                imagejpeg($image, $imagePath, 85);
                break;
            case IMAGETYPE_PNG:
                $image = imagecreatefrompng($imagePath);
                imagejpeg($image, $imagePath, 85);
                break;
            default:
                // Type non supporté, on garde l'original
                break;
        }
        
        if (isset($image)) {
            imagedestroy($image);
        }
    }

    public function storeBulk(Request $request)
    {
        try {
            // Nettoyer et valider les données
            $requestData = $request->all();
            
            // Nettoyer les quantités pour s'assurer qu'elles sont des entiers
            if (isset($requestData['products']) && is_array($requestData['products'])) {
                foreach ($requestData['products'] as $index => &$product) {
                    // Nettoyer la quantité
                    if (isset($product['quantity'])) {
                        $quantity = $product['quantity'];
                        // Si c'est une chaîne, essayer de la convertir en entier
                        if (is_string($quantity)) {
                            $quantity = trim($quantity);
                            if (is_numeric($quantity)) {
                                $product['quantity'] = (int) $quantity;
                            } else {
                                $product['quantity'] = 1; // Valeur par défaut
                            }
                        } elseif (is_numeric($quantity)) {
                            $product['quantity'] = (int) $quantity;
                        } else {
                            $product['quantity'] = 1; // Valeur par défaut
                        }
                        
                        // S'assurer que la quantité est au moins 1
                        if ($product['quantity'] < 1) {
                            $product['quantity'] = 1;
                        }
                    } else {
                        $product['quantity'] = 1; // Valeur par défaut si manquante
                    }
                }
            }
            
            // Créer une nouvelle requête avec les données nettoyées
            $request->merge($requestData);
            
            $request->validate([
                'products' => 'required|array',
                'products.*.name' => 'required|string',
                'products.*.price' => 'required|numeric|min:0',
                'products.*.quantity' => 'nullable|integer|min:1',
                'products.*.category' => 'nullable|string',
                'store_name' => 'nullable|string',
                'location' => 'nullable|string'
            ]);

            $user = Auth::user();
            $contributionsCreated = 0;

            \Log::info('🛒 Début enregistrement contributions en lot', [
                'user_id' => $user->id,
                'products_count' => count($request->products),
                'store_name' => $request->store_name
            ]);

            foreach ($request->products as $index => $productData) {
                try {
                    \Log::info("📦 Traitement produit #{$index}", [
                        'name' => $productData['name'],
                        'price' => $productData['price'],
                        'quantity' => $productData['quantity'] ?? 1,
                        'category' => $productData['category'] ?? null
                    ]);
                    
                    $contribution = Contribution::create([
                        'user_id' => $user->id,
                        'product_name' => $productData['name'],
                        'price' => $productData['price'],
                        'quantity' => $productData['quantity'] ?? 1,
                        'category' => $productData['category'] ?? null,
                        'store_name' => $request->store_name,
                        'location' => $request->location,
                        'contribution_type' => 'scan',
                        'verified' => false
                    ]);
                    
                    $contributionsCreated++;
                    \Log::info("✅ Contribution #{$index} créée", ['id' => $contribution->id, 'product' => $productData['name']]);
                    
                } catch (\Exception $e) {
                    \Log::error("❌ Erreur création contribution #{$index}: " . $e->getMessage(), [
                        'product_data' => $productData,
                        'error' => $e->getMessage()
                    ]);
                    throw $e;
                }
            }

            // Attribuer les badges pour toutes les contributions
            $newBadges = $this->gamificationService->checkAndAwardBadges($user);

            \Log::info("✅ {$contributionsCreated} contributions créées depuis le ticket");

            return response()->json([
                'success' => true,
                'message' => "{$contributionsCreated} produits ajoutés avec succès !",
                'contributions_count' => $contributionsCreated,
                'new_badges' => $newBadges
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('❌ Erreur validation storeBulk: ', $e->errors());
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation: ' . implode(', ', Arr::flatten($e->errors()))
            ], 422);
            
        } catch (\Exception $e) {
            \Log::error('❌ Erreur storeBulk: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'enregistrement: ' . $e->getMessage()
            ], 500);
        }
    }

    public function manual()
    {
        return view('contribute.manual');
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'store_name' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'product_barcode' => 'nullable|string',
            'notes' => 'nullable|string|max:500',
            'receipt_image' => 'nullable|image|max:2048',
            'contribution_type' => 'required|in:scan,manual'
        ]);

        $user = Auth::user();
        
        // Gérer l'upload d'image si présente
        $receiptPath = null;
        if ($request->hasFile('receipt_image')) {
            $receiptPath = $request->file('receipt_image')->store('receipts', 'public');
        }

        // Créer la contribution
        $contribution = Contribution::create([
            'user_id' => $user->id,
            'product_name' => $request->product_name,
            'product_barcode' => $request->product_barcode,
            'price' => $request->price,
            'store_name' => $request->store_name,
            'location' => $request->location,
            'notes' => $request->notes,
            'receipt_image' => $receiptPath,
            'contribution_type' => $request->contribution_type,
            'verified' => false
        ]);

        // Vérifier et attribuer les nouveaux badges
        $newBadges = $this->gamificationService->checkAndAwardBadges($user);

        // Préparer la réponse avec les badges gagnés
        $response = [
            'success' => true,
            'message' => 'Contribution ajoutée avec succès !',
            'contribution' => $contribution,
            'new_badges' => $newBadges
        ];

        if ($request->ajax()) {
            return response()->json($response);
        }

        // Rediriger avec les données des nouveaux badges
        if (!empty($newBadges)) {
            return redirect()->route('contribute.index')
                ->with('new_badges', $newBadges)
                ->with('success', 'Contribution ajoutée ! Nouveau(x) badge(s) débloqué(s) !');
        }

        return redirect()->route('contribute.index')
            ->with('success', 'Contribution ajoutée avec succès !');
    }

    public function badges()
    {
        $user = Auth::user();
        $stats = $this->gamificationService->getGamificationStats($user);
        
        return view('contribute.badges', compact('stats'));
    }
}
