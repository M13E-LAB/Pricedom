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
            \Log::info('🤖 Début analyse Claude complète pour post #' . $post->id);
            
            // Utiliser Claude au lieu d'OpenAI
            $claudeService = app(\App\Services\ClaudeService::class);

            // Analyse avec Claude (plus besoin de télécharger l'image pour l'instant)
            \Log::info('📋 Analyse nutritionnelle avec Claude basée sur la description');
            
            
            // Analyse nutritionnelle avec Claude (avec image)
            $nutritionAnalysis = $claudeService->analyzeNutrition(
                $request->description ?? '', 
                $post->image_path
            );
            
            if ($nutritionAnalysis && isset($nutritionAnalysis['analysis'])) {
                $post->nutrition_analysis = $nutritionAnalysis['analysis'];
                \Log::info('💾 Analyse nutritionnelle Claude sauvegardée');
            }

            // Analyse de santé pour la ligue (Claude aussi)
            try {
                \Log::info('🤖 Démarrage analyse santé Claude', ['description' => $request->description]);
                $healthService = app(HealthAnalysisService::class);
                $healthAnalysis = $healthService->analyzePost($request->description ?? '', $post->image_path);
                
                $post->health_score = $healthAnalysis['score'];
                $post->health_analysis = $healthAnalysis;
                
                \Log::info('✅ Analyse santé Claude terminée', [
                    'score' => $healthAnalysis['score'],
                    'category' => $healthAnalysis['category'] ?? 'N/A',
                    'claude_powered' => $healthAnalysis['claude_powered'] ?? false
                ]);
            } catch (\Exception $e) {
                \Log::error('❌ Erreur analyse santé Claude: ' . $e->getMessage());
                $post->health_score = 50;
                $post->health_analysis = ['score' => 50, 'category' => 'Non analysé'];
            }
            
            $post->save();
            \Log::info('✅ Post sauvegardé avec analyses Claude');
            
        } catch (\Exception $e) {
            \Log::error('❌ Exception lors de l\'analyse Claude: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            // Fallback : sauvegarder le post sans analyse
            $post->save();
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
