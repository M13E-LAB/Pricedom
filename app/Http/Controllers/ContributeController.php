<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
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
        // Récupérer les statistiques de l'utilisateur connecté
        $user = auth()->user();
        
        if ($user) {
            $totalContributions = $user->contributions()->count();
            $badgesCount = $user->badges()->count();
            
            $stats = [
                'total_contributions' => $totalContributions,
                'badges_count' => $badgesCount,
                'recent_contributions' => $user->contributions()->latest()->take(5)->get(),
                'completion_percentage' => min(100, ($totalContributions * 10)), // 10% par contribution, max 100%
                'next_badge' => [
                    'name' => $badgesCount < 5 ? 'Contributeur Expert' : 'Master Scanner',
                    'emoji' => $badgesCount < 5 ? '🏆' : '👑',
                    'threshold' => ($badgesCount + 1) * 5,
                    'progress' => min(100, ($totalContributions / (($badgesCount + 1) * 5)) * 100),
                    'remaining' => max(0, (($badgesCount + 1) * 5) - $totalContributions)
                ],
                'points' => $totalContributions * 10 + $badgesCount * 50,
                'level' => floor($totalContributions / 5) + 1,
                'scan_count' => $totalContributions, // Approximation
                'manual_count' => 0, // À implémenter si nécessaire
                'all_badges' => $user->badges()->take(5)->get(), // Les 5 derniers badges
            ];
        } else {
            $stats = [
                'total_contributions' => 0,
                'badges_count' => 0,
                'recent_contributions' => collect(),
                'completion_percentage' => 0,
                'next_badge' => [
                    'name' => 'Premier Contributeur',
                    'emoji' => '🌟',
                    'threshold' => 5,
                    'progress' => 0,
                    'remaining' => 5
                ],
                'points' => 0,
                'level' => 1,
                'scan_count' => 0,
                'manual_count' => 0,
                'all_badges' => collect(), // Collection vide pour utilisateur non connecté
            ];
        }
        
        return view('contribute.index', compact('stats'));
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
            Log::info('🎫 Début analyse ticket avec Claude Vision API');
            Log::info('📋 Request data', ['files' => $request->allFiles(), 'has_image' => $request->hasFile('ticket_image')]);
            
            // Utiliser le service R2 pour uploader l'image
            $r2Service = app(\App\Services\CloudflareR2Service::class);
            $file = $request->file('ticket_image');
            
            // Compresser l'image si elle est trop lourde (>2MB)
            $imageSize = $file->getSize();
            if ($imageSize > 2 * 1024 * 1024) {
                Log::info('🗜️ Compression image nécessaire');
                $tempPath = $file->getRealPath();
                $this->compressImage($tempPath);
                Log::info('✅ Image compressée');
            }
            
            // Uploader sur R2
            $imageUrl = $r2Service->uploadImage($file, 'tickets');
            Log::info('📷 Image uploadée sur R2', ['url' => $imageUrl]);
            
            // Analyser avec Claude
            $claudeService = app(\App\Services\ClaudeService::class);
            $jsonData = $claudeService->analyzeTicket($imageUrl);
            
            if ($jsonData && isset($jsonData['products'])) {
                Log::info('✅ Ticket analysé avec succès par Claude', ['products_count' => count($jsonData['products'])]);
                
                return response()->json([
                    'success' => true,
                    'data' => $jsonData
                ]);
            }
            
            Log::error('❌ Analyse ticket échouée ou format invalide');
            return response()->json([
                'success' => false,
                'message' => 'Impossible d\'analyser ce ticket. Assurez-vous que l\'image est claire et lisible.'
            ], 500);
            
        } catch (\Exception $e) {
            Log::error('❌ Erreur analyse ticket: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur technique lors de l\'analyse. Réessayez dans quelques instants.'
            ], 500);
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
