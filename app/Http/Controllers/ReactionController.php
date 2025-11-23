<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Reaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReactionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function toggle(Request $request, Post $post)
    {
        $request->validate([
            'emoji' => 'required|string|max:10',
        ]);

        $user = Auth::user();
        $emoji = $request->emoji;

        // Vérifier si l'utilisateur a déjà réagi avec cet émoji
        $existingReaction = Reaction::where('user_id', $user->id)
            ->where('post_id', $post->id)
            ->where('emoji', $emoji)
            ->first();

        if ($existingReaction) {
            // Supprimer la réaction
            $existingReaction->delete();
            $reacted = false;
        } else {
            // Ajouter la réaction
            Reaction::create([
                'user_id' => $user->id,
                'post_id' => $post->id,
                'emoji' => $emoji,
            ]);
            $reacted = true;
        }

        // Retourner les statistiques mises à jour
        return response()->json([
            'reacted' => $reacted,
            'reactions_by_emoji' => $post->fresh()->reactions_by_emoji,
            'total_reactions' => $post->fresh()->reactions_count
        ]);
    }

    public function getReactions(Post $post)
    {
        return response()->json([
            'reactions_by_emoji' => $post->reactions_by_emoji,
            'total_reactions' => $post->reactions_count,
            'user_reactions' => $post->reactions()
                ->where('user_id', Auth::id())
                ->pluck('emoji')
        ]);
    }
} 