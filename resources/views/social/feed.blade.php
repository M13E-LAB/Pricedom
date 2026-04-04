@extends('layouts.app')

@section('title', 'Feed Communautaire - Pricedom')

@section('content')
<div class="min-h-screen">
    <div class="container mx-auto px-4 py-8 max-w-2xl">
        <!-- En-tête du feed -->
        <div class="bg-black/30 backdrop-blur-lg rounded-xl border border-white/10 p-6 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-white">🍽️ Feed Communautaire</h1>
                    <p class="text-white/70 mt-1">Découvrez les délicieux repas de la communauté Pricedom</p>
                </div>
                <a href="{{ route('social.create') }}" 
                   class="bg-gradient-to-r from-orange-500 to-pink-500 hover:from-orange-600 hover:to-pink-600 text-white px-6 py-3 rounded-lg font-medium transition-all transform hover:scale-105 shadow-lg">
                    + Partager un repas
                </a>
            </div>
        </div>

        <!-- Navigation -->
        <div class="bg-black/30 backdrop-blur-lg rounded-xl border border-white/10 p-4 mb-6">
            <div class="flex space-x-4">
                <a href="{{ route('social.index') }}" 
                   class="bg-gradient-to-r from-orange-500 to-pink-500 text-white px-6 py-3 rounded-lg font-medium shadow-lg">
                    🌟 Feed
                </a>
                <a href="{{ route('social.my-posts') }}" 
                   class="text-white/70 hover:text-white hover:bg-white/10 px-6 py-3 rounded-lg transition-all">
                    📸 Mes posts
                </a>
                <a href="{{ route('products.search') }}" 
                   class="text-white/70 hover:text-white hover:bg-white/10 px-6 py-3 rounded-lg transition-all">
                    🔍 Recherche
                </a>
            </div>
        </div>

        <!-- Messages flash -->
        @if(session('success'))
            <div class="bg-green-500/20 border border-green-400/30 text-green-300 px-4 py-3 rounded-lg mb-6 backdrop-blur-sm">
                {{ session('success') }}
            </div>
        @endif

        <!-- Posts -->
        @if($posts->count() > 0)
            @foreach($posts as $post)
                <div class="bg-black/30 backdrop-blur-lg rounded-xl border border-white/10 mb-6 overflow-hidden shadow-xl max-w-2xl mx-auto w-full">
                    <!-- En-tête du post -->
                    <div class="p-4 border-b border-white/10">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="w-12 h-12 bg-gradient-to-r from-orange-400 to-pink-400 rounded-full flex items-center justify-center text-white font-bold text-lg shadow-lg">
                                    {{ strtoupper(substr($post->user->name, 0, 1)) }}
                                </div>
                                <div>
                                    <h3 class="font-medium text-white">{{ $post->user->name }}</h3>
                                    <p class="text-sm text-white/60">{{ $post->created_at->diffForHumans() }}</p>
                                </div>
                            </div>
                            @if($post->user_id === auth()->id())
                                <form action="{{ route('social.destroy', $post) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="text-red-400 hover:text-red-300 text-sm p-2 hover:bg-red-500/20 rounded-lg transition-all"
                                            onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce post ?')">
                                        🗑️
                                    </button>
                                </form>
                            @endif
                        </div>
                        
                        <!-- Score de santé pour la ligue -->
                        @if(isset($post->health_score))
                            <div class="px-4 py-2 border-b border-white/10">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-2">
                                        @php
                                            $score = $post->health_score;
                                            $category = $post->health_analysis['category'] ?? 'Non évalué';
                                            
                                            if ($score >= 80) {
                                                $color = 'text-green-400';
                                                $bgColor = 'bg-green-500/20';
                                                $emoji = '🌟';
                                            } elseif ($score >= 60) {
                                                $color = 'text-blue-400';
                                                $bgColor = 'bg-blue-500/20';
                                                $emoji = '👍';
                                            } elseif ($score >= 40) {
                                                $color = 'text-yellow-400';
                                                $bgColor = 'bg-yellow-500/20';
                                                $emoji = '⚡';
                                            } else {
                                                $color = 'text-orange-400';
                                                $bgColor = 'bg-orange-500/20';
                                                $emoji = '🔥';
                                            }
                                        @endphp
                                        
                                        <div class="flex items-center space-x-2 {{ $bgColor }} px-3 py-1 rounded-full">
                                            <span class="text-lg">{{ $emoji }}</span>
                                            <span class="{{ $color }} font-semibold text-sm">{{ $score }}/100</span>
                                            <span class="text-white/60 text-xs">{{ $category }}</span>
                                        </div>
                                    </div>
                                    
                                    <a href="{{ route('league.index') }}" class="text-xs text-white/50 hover:text-white/70 transition-colors">
                                        🏆 Voir classement
                                    </a>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Contenu principal du post : image + analyse -->
                    <div class="flex flex-col md:flex-row">
                        <!-- Image du repas -->
                        <div class="relative md:w-1/2 w-full">
                            @php
                                // Si image_path est déjà une URL complète (R2), utiliser la route proxy
                                if (str_starts_with($post->image_path, 'https://')) {
                                    // Extraire le chemin depuis l'URL R2
                                    $parsedUrl = parse_url($post->image_path);
                                    $path = ltrim($parsedUrl['path'], '/');
                                    
                                    // Supprimer le préfixe "zyma-files/" si présent
                                    if (str_starts_with($path, 'zyma-files/')) {
                                        $path = substr($path, strlen('zyma-files/'));
                                    }
                                    
                                    // Utiliser la route proxy pour servir l'image R2
                                    $imageUrl = url('/r2-image/' . urlencode($path));
                                } else {
                                    // Sinon, c'est un chemin local, construire l'URL
                                    $imageUrl = asset('storage/' . $post->image_path);
                                }
                            @endphp
                            
                            <img src="{{ $imageUrl }}" 
                                 alt="Repas partagé par {{ $post->user->name }}"
                                 class="w-full h-96 object-cover shadow-lg"
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            
                            <div class="hidden items-center justify-center w-full h-96 bg-gray-800 text-white text-center">
                                <div>
                                    <div class="text-4xl mb-2">📷</div>
                                    <div>Image non disponible</div>
                                    <div class="text-xs text-gray-400 mt-2 max-w-xs break-all">
                                        URL: {{ $imageUrl }}
                                    </div>
                                </div>
                            </div>
                            <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent"></div>
                        </div>
                        <!-- Résumé nutritionnel -->
                        <div class="md:w-1/2 w-full flex flex-col justify-center p-6">
                            @if(!empty($post->nutrition_analysis))
                                <div class="bg-gradient-to-br from-emerald-500/10 via-blue-500/5 to-purple-500/10 border border-emerald-400/30 rounded-2xl p-6 mb-2 animate-fade-in shadow-xl backdrop-blur-sm">
                                    <div class="space-y-4">
                                        @php
                                            $lines = explode("\n", $post->nutrition_analysis);
                                            $currentSection = '';
                                        @endphp
                                        
                                        @foreach($lines as $line)
                                            @php $line = trim($line); @endphp
                                            @if(!empty($line))
                                                @if(str_contains($line, '🍽️ Repas identifié'))
                                                    <div class="text-center mb-4">
                                                        <h3 class="text-lg font-bold text-white bg-gradient-to-r from-orange-400 to-pink-400 bg-clip-text text-transparent">
                                                            {{ str_replace('**🍽️ Repas identifié** :', '🍽️', $line) }}
                                                        </h3>
                                                    </div>
                                                @elseif(str_contains($line, '🔥') || str_contains($line, '🥖') || str_contains($line, '🍗') || str_contains($line, '🥑'))
                                                    <div class="flex items-center justify-between bg-white/5 rounded-lg px-4 py-2 border border-white/10">
                                                        <span class="text-white/90 font-medium">{{ $line }}</span>
                                                    </div>
                                                @elseif(str_contains($line, '💚 Score Santé'))
                                                    @php
                                                        preg_match('/(\d+)\/10/', $line, $matches);
                                                        $score = $matches[1] ?? 0;
                                                        $scoreColor = $score >= 7 ? 'from-green-400 to-emerald-500' : ($score >= 5 ? 'from-yellow-400 to-orange-500' : 'from-red-400 to-pink-500');
                                                    @endphp
                                                    <div class="bg-gradient-to-r {{ $scoreColor }} rounded-xl p-4 text-center">
                                                        <div class="text-white font-bold text-lg">
                                                            💚 Score Santé Pricedom : {{ $score }}/10
                                                        </div>
                                                        <div class="flex justify-center mt-2">
                                                            @for($i = 1; $i <= 10; $i++)
                                                                <div class="w-3 h-3 mx-1 rounded-full {{ $i <= $score ? 'bg-white' : 'bg-white/30' }}"></div>
                                                            @endfor
                                                        </div>
                                                    </div>
                                                @elseif(str_contains($line, '✍️ **Feedback**'))
                                                    <div class="border-t border-white/20 pt-4">
                                                        <h4 class="text-emerald-300 font-semibold mb-2 flex items-center">
                                                            <span class="mr-2">💬</span> Conseils personnalisés
                                                        </h4>
                                                    </div>
                                                @elseif($currentSection === 'feedback' || str_contains($line, 'Repas') || str_contains($line, 'Ajouter') || str_contains($line, 'Pensez'))
                                                    @php $currentSection = 'feedback'; @endphp
                                                    @if(!str_contains($line, '✍️'))
                                                        <p class="text-white/80 italic leading-relaxed bg-white/5 rounded-lg p-3 border-l-4 border-emerald-400">
                                                            {{ $line }}
                                                        </p>
                                                    @endif
                                                @elseif(!str_contains($line, '---') && !empty($line))
                                                    <div class="text-white/70 text-sm">{{ $line }}</div>
                                                @endif
                                            @endif
                                        @endforeach
                                        
                                        @if($currentSection === 'feedback')
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @else
                                <div class="bg-gradient-to-r from-yellow-500/10 to-orange-500/10 border border-yellow-400/20 rounded-xl p-6">
                                    <div class="text-center">
                                        <div class="animate-pulse text-2xl mb-2">🔍</div>
                                        <div class="text-white/70 text-sm">
                                            Analyse nutritionnelle en cours...
                                        </div>
                                        <div class="mt-2 text-xs text-white/50">
                                            Cela peut prendre quelques secondes
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Actions et description -->
                    <div class="p-4">
                        <!-- Boutons d'action -->
                        <div class="flex items-center space-x-4 mb-3">
                            <!-- Bouton J'aime (cœur rouge) -->
                            <button onclick="toggleLike({{ $post->id }})" 
                                    class="like-button flex items-center space-x-2 text-white/70 hover:text-red-400 transition-colors p-2 hover:bg-white/10 rounded-lg"
                                    data-post-id="{{ $post->id }}"
                                    data-liked="{{ $post->isLikedBy(auth()->user()) ? 'true' : 'false' }}">
                                <span class="like-icon text-2xl">
                                    {{ $post->isLikedBy(auth()->user()) ? '❤️' : '🤍' }}
                                </span>
                                <span class="like-count font-medium">{{ $post->likes_count }}</span>
                            </button>

                            <!-- Réactions émojis -->
                            <div class="flex items-center space-x-2">
                                <div class="reactions-container" data-post-id="{{ $post->id }}">
                                    <!-- Émojis populaires -->
                                    <div class="flex space-x-1">
                                        @php
                                            $popularEmojis = ['😍', '👍', '😂', '🔥', '👏', '💯'];
                                            $userReactions = $post->reactions()->where('user_id', auth()->id())->pluck('emoji')->toArray();
                                        @endphp
                                        @foreach($popularEmojis as $emoji)
                                            <button onclick="toggleReaction({{ $post->id }}, '{{ $emoji }}')"
                                                    class="reaction-btn text-xl p-1 rounded-full transition-all hover:scale-110 {{ in_array($emoji, $userReactions) ? 'bg-orange-500/30 border border-orange-400/50' : 'hover:bg-white/10' }}"
                                                    data-emoji="{{ $emoji }}"
                                                    data-post-id="{{ $post->id }}">
                                                {{ $emoji }}
                                            </button>
                                        @endforeach
                                    </div>
                                    
                                    <!-- Compteurs de réactions -->
                                    <div class="reactions-count ml-2 flex space-x-1">
                                        @php
                                            $reactionsByEmoji = $post->reactions_by_emoji;
                                        @endphp
                                        @foreach($reactionsByEmoji as $emoji => $count)
                                            @if($count > 0)
                                                <span class="bg-white/10 text-white text-xs px-2 py-1 rounded-full">
                                                    {{ $emoji }} {{ $count }}
                                                </span>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Description -->
                        @if($post->description)
                            <div class="text-white mb-2 bg-white/5 rounded-lg p-3">
                                <span class="font-medium text-orange-400">{{ $post->user->name }}</span>
                                <span class="text-white/90"> {{ $post->description }}</span>
                            </div>
                        @endif

                        <!-- Section Commentaires -->
                        <div class="mt-4 border-t border-white/10 pt-4">
                            <!-- Formulaire de commentaire -->
                            <form action="{{ route('comments.store', $post) }}" method="POST" class="mb-4">
                                @csrf
                                <div class="flex gap-2">
                                    <input type="text" 
                                           name="content" 
                                           placeholder="Ajouter un commentaire..." 
                                           class="flex-1 bg-white/5 border border-white/10 rounded-lg px-4 py-2 text-white placeholder-white/50 focus:outline-none focus:border-orange-400/50">
                                    <button type="submit" 
                                            class="bg-gradient-to-r from-orange-500 to-pink-500 hover:from-orange-600 hover:to-pink-600 text-white px-4 py-2 rounded-lg font-medium transition-all">
                                        💬 Commenter
                                    </button>
                                </div>
                            </form>

                            <!-- Liste des commentaires -->
                            <div class="space-y-3">
                                @foreach($post->comments as $comment)
                                    <div class="flex items-start gap-3 bg-white/5 rounded-lg p-3">
                                        <div class="w-8 h-8 bg-gradient-to-r from-orange-400 to-pink-400 rounded-full flex items-center justify-center text-white font-bold text-sm flex-shrink-0">
                                            {{ strtoupper(substr($comment->user->name, 0, 1)) }}
                                        </div>
                                        <div class="flex-1">
                                            <div class="flex items-center justify-between">
                                                <span class="font-medium text-orange-400">{{ $comment->user->name }}</span>
                                                <span class="text-xs text-white/50">{{ $comment->created_at->diffForHumans() }}</span>
                                            </div>
                                            <p class="text-white/90 mt-1">{{ $comment->content }}</p>
                                        </div>
                                        @if($comment->user_id === auth()->id() || auth()->user()->isAdmin())
                                            <form action="{{ route('comments.destroy', $comment) }}" method="POST" class="flex-shrink-0">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="text-red-400 hover:text-red-300 text-sm p-2 hover:bg-red-500/20 rounded-lg transition-all"
                                                        onclick="return confirm('Voulez-vous vraiment supprimer ce commentaire ?')">
                                                    🗑️
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach

            <!-- Pagination -->
            <div class="mt-8">
                {{ $posts->links() }}
            </div>
        @else
            <div class="bg-black/30 backdrop-blur-lg rounded-xl border border-white/10 p-8 text-center">
                <div class="text-6xl mb-4">🍽️</div>
                <h3 class="text-xl font-bold text-white mb-2">Aucun post pour le moment</h3>
                <p class="text-white/70 mb-6">Soyez le premier à partager un délicieux repas !</p>
                <a href="{{ route('social.create') }}" 
                   class="bg-gradient-to-r from-orange-500 to-pink-500 hover:from-orange-600 hover:to-pink-600 text-white px-8 py-4 rounded-lg font-medium transition-all transform hover:scale-105 inline-block shadow-lg">
                    Partager votre premier repas
                </a>
            </div>
        @endif
    </div>
</div>

<script>
function toggleLike(postId) {
    const button = document.querySelector(`.like-button[data-post-id="${postId}"]`);
    if (!button) {
        console.error('Bouton like non trouvé pour le post:', postId);
        return;
    }
    
    const icon = button.querySelector('.like-icon');
    const count = button.querySelector('.like-count');
    
    if (!icon || !count) {
        console.error('Éléments icon ou count non trouvés');
        return;
    }
    
    // Désactiver le bouton pendant la requête
    button.disabled = true;
    button.style.opacity = '0.7';
    
    fetch(`/social/like/${postId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Mettre à jour l'interface
            icon.textContent = data.liked ? '❤️' : '🤍';
            count.textContent = data.likes_count;
            button.setAttribute('data-liked', data.liked.toString());
            
            // Animation de feedback
            button.style.transform = 'scale(1.1)';
            setTimeout(() => {
                button.style.transform = 'scale(1)';
            }, 150);
        } else {
            throw new Error('Réponse serveur invalide');
        }
    })
    .catch(error => {
        console.error('Erreur lors du like:', error);
        // Afficher un message d'erreur à l'utilisateur
        const errorMsg = document.createElement('div');
        errorMsg.className = 'fixed top-4 right-4 bg-red-500 text-white px-4 py-2 rounded-lg z-50';
        errorMsg.textContent = 'Erreur lors du like. Veuillez réessayer.';
        document.body.appendChild(errorMsg);
        setTimeout(() => errorMsg.remove(), 3000);
    })
    .finally(() => {
        // Réactiver le bouton
        button.disabled = false;
        button.style.opacity = '1';
    });
}

function toggleReaction(postId, emoji) {
    const button = document.querySelector(`[data-post-id="${postId}"][data-emoji="${emoji}"]`);
    const container = document.querySelector(`.reactions-container[data-post-id="${postId}"]`);
    
    fetch(`/posts/${postId}/reactions`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ emoji: emoji })
    })
    .then(response => response.json())
    .then(data => {
        // Mettre à jour l'apparence du bouton
        if (data.reacted) {
            button.classList.add('bg-orange-500/30', 'border', 'border-orange-400/50');
        } else {
            button.classList.remove('bg-orange-500/30', 'border', 'border-orange-400/50');
        }
        
        // Mettre à jour les compteurs
        updateReactionsCount(container, data.reactions_by_emoji);
        
        // Animation de feedback
        button.style.transform = 'scale(1.2)';
        setTimeout(() => {
            button.style.transform = 'scale(1)';
        }, 200);
    })
    .catch(error => {
        console.error('Erreur:', error);
    });
}

function updateReactionsCount(container, reactionsByEmoji) {
    const countContainer = container.querySelector('.reactions-count');
    countContainer.innerHTML = '';
    
    Object.entries(reactionsByEmoji).forEach(([emoji, count]) => {
        if (count > 0) {
            const span = document.createElement('span');
            span.className = 'bg-white/10 text-white text-xs px-2 py-1 rounded-full';
            span.textContent = `${emoji} ${count}`;
            countContainer.appendChild(span);
        }
    });
}
</script>
@endsection 