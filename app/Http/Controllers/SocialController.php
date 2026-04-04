<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Like;
use App\Services\CloudflareR2Service;
use App\Services\HealthAnalysisService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;

class SocialController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Vérifier si l'utilisateur a posté au moins une fois
        if (!$user->hasPosted()) {
            return view('social.no-access', [
                'userHasPosted' => false
            ]);
        }

        // Si l'utilisateur a posté, montrer le feed
        $posts = Post::with(['user', 'likes', 'reactions', 'comments.user'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('social.feed', [
            'posts' => $posts,
            'userHasPosted' => true
        ]);
    }

    public function create()
    {
        return view('social.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'description' => 'nullable|string|max:500',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:15360', // 15MB max
        ]);

        $user = Auth::user();
        
        // Upload de l'image vers Cloudflare R2
        $r2Service = app(CloudflareR2Service::class);
        $imageUrl = $r2Service->uploadImage($request->file('image'), 'posts');

        // Créer le post avec l'URL R2
        $post = Post::create([
            'user_id' => $user->id,
            'description' => $request->description,
            'image_path' => $imageUrl, // Stocker l'URL complète R2
        ]);

        try {
            \Log::info('🚀 Début analyse OpenAI pour post #' . $post->id);
            
            // Configuration de la clé API OpenAI depuis les variables d'environnement
            $apiKey = env('OPENAI_API_KEY');
            if (empty($apiKey)) {
                throw new \Exception('OPENAI_API_KEY non configurée. Veuillez définir cette variable d\'environnement.');
            }
            \Log::info('🔑 Utilisation clé API OpenAI depuis environnement');

            // Télécharger l'image depuis R2 et l'encoder en base64 pour OpenAI
            \Log::info('📥 Téléchargement image depuis R2 via service pour analyse');
            
            // Extraire le chemin relatif depuis l'URL R2
            $r2Service = new \App\Services\CloudflareR2Service();
            $parsedUrl = parse_url($post->image_path);
            $path = ltrim($parsedUrl['path'], '/');
            
            // Supprimer le préfixe "zyma-files/" si présent
            if (str_starts_with($path, 'zyma-files/')) {
                $path = substr($path, strlen('zyma-files/'));
            }
            
            \Log::info('🔍 Chemin extrait pour R2: ' . $path);
            
            // Télécharger via le service R2 avec credentials
            $imageContent = $r2Service->getContent($path);
            
            if (!$imageContent) {
                \Log::error('❌ Impossible de télécharger l\'image R2 via service');
                throw new \Exception('Impossible d\'accéder à l\'image pour l\'analyse');
            }
            
            $imageData = base64_encode($imageContent);
            \Log::info('✅ Image téléchargée via R2 Service et encodée', ['size_kb' => number_format(strlen($imageData)/1024, 1)]);
            
            $advancedPrompt = "Tu es l'assistant nutritionnel de l'app **Zyma**, qui aide les gens à mieux manger grâce à une photo de leur repas.

Voici une photo de repas : {image}

Ta mission :

1. À partir de cette photo uniquement :
    - Identifie chaque aliment.
    - Estime les **quantités** en grammes ou millilitres.
2. Génère une fiche nutritionnelle simple, dans ce format :
    - **Calories** : {nombre} kcal
    - **Glucides** : {g}
    - **Protéines** : {g}
    - **Lipides** : {g}
    - **Score Santé Zyma** : sur 10 (1 = très mauvais, 10 = excellent)
    → Prends en compte : fibres, protéines, sucres ajoutés, sel, gras saturés, additifs, aliments ultra-transformés.
3. Rédige un **court retour personnalisé** (3 à 4 lignes max), en langage naturel, bienveillant et motivant :
    - Félicite si c'est un bon choix ;
    - Donne 1 à 2 **conseils simples** si le repas peut être amélioré ;
    - Pas de termes techniques ni culpabilisants.

❗ Ne fais pas de supposition sur les quantités non visibles. Ne commente que ce qui est visible.

Affiche les résultats comme ceci :

---

**🍽️ Repas identifié** : {nom simple du plat}

**🔥 Calories** : {kcal} kcal

**🥖 Glucides** : {g} g

**🍗 Protéines** : {g} g

**🥑 Lipides** : {g} g

**💚 Score Santé Zyma** : {x}/10

✍️ **Feedback** :

{analyse concise et humaine, exemple : \"Repas gourmand mais un peu sucré. Ajouter une source de protéines ou quelques fruits frais pourrait mieux équilibrer.\"}";

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(90)->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-4o',
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => [
                            ['type' => 'text', 'text' => $advancedPrompt],
                            [
                                'type' => 'image_url',
                                'image_url' => [
                                    'url' => 'data:image/jpeg;base64,' . $imageData
                                ]
                            ]
                        ]
                    ]
                ],
                'max_tokens' => 800,
                'temperature' => 0.7
            ]);

            \Log::info('📥 Réponse OpenAI brute', ['response' => $response->body()]);

            if ($response->failed()) {
                \Log::error('❌ Erreur API OpenAI', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                throw new \Exception('Erreur API OpenAI: ' . $response->body());
            }

            $responseData = $response->json();
            
            if (isset($responseData['choices'][0]['message']['content'])) {
                $content = $responseData['choices'][0]['message']['content'];
                \Log::info('✨ Contenu OpenAI reçu', ['content' => $content]);
                
                // Sauvegarder directement le contenu formaté
                $post->nutrition_analysis = $content;
                
                // Analyse de santé pour la ligue
                try {
                    $healthService = app(HealthAnalysisService::class);
                    $healthAnalysis = $healthService->analyzePost($request->description ?? '', $post->image_path);
                    
                    $post->health_score = $healthAnalysis['score'];
                    $post->health_analysis = $healthAnalysis;
                    
                    \Log::info('🏆 Analyse santé calculée', [
                        'score' => $healthAnalysis['score'],
                        'category' => $healthAnalysis['category']
                    ]);
                } catch (\Exception $e) {
                    \Log::error('❌ Erreur analyse santé: ' . $e->getMessage());
                    // Valeurs par défaut si l'analyse échoue
                    $post->health_score = 50;
                    $post->health_analysis = ['score' => 50, 'category' => 'Non analysé'];
                }
                
                $post->save();
                \Log::info('✅ Analyse nutritionnelle et santé sauvegardées');
            } else {
                \Log::error('❌ Structure de réponse OpenAI invalide', ['response' => $responseData]);
                throw new \Exception('Structure de réponse OpenAI invalide');
            }
            
        } catch (\Exception $e) {
            \Log::error('❌ Exception lors de l\'analyse: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
        }

        return redirect()->route('social.index')->with('success', 'Votre repas a été partagé avec succès ! 🍽️');
    }

    public function like(Post $post)
    {
        $user = Auth::user();

        // Utiliser la relation du modèle Post (table post_likes)
        if ($post->likes()->where('user_id', $user->id)->exists()) {
            // Supprimer le like (unlike)
            $post->likes()->detach($user->id);
            $liked = false;
        } else {
            // Ajouter le like
            $post->likes()->attach($user->id);
            $liked = true;
        }

        // Rafraîchir le modèle pour obtenir le bon compteur
        $post->refresh();
        
        // Retourner la réponse JSON pour AJAX
        return response()->json([
            'liked' => $liked,
            'likes_count' => $post->likes_count, // Utiliser l'accessor du modèle
            'success' => true
        ]);
    }

    public function myPosts()
    {
        $user = Auth::user();
        $posts = $user->posts()->with(['likes', 'reactions', 'comments.user'])->orderBy('created_at', 'desc')->paginate(10);
        
        return view('social.my-posts', [
            'posts' => $posts
        ]);
    }

    public function destroy(Post $post)
    {
        $user = Auth::user();
        
        // Vérifier que l'utilisateur est propriétaire du post
        if ($post->user_id !== $user->id) {
            return redirect()->back()->with('error', 'Vous ne pouvez pas supprimer ce post.');
        }

        // Supprimer l'image de R2
        try {
            $r2Service = app(CloudflareR2Service::class);
            // Extraire le path de l'URL R2
            $urlParts = parse_url($post->image_path);
            $imagePath = ltrim($urlParts['path'] ?? '', '/');
            if ($imagePath) {
                $r2Service->delete($imagePath);
            }
        } catch (\Exception $e) {
            \Log::error('Erreur suppression image R2: ' . $e->getMessage());
        }
        
        // Supprimer le post
        $post->delete();
        
        return redirect()->back()->with('success', 'Post supprimé avec succès.');
    }
}
