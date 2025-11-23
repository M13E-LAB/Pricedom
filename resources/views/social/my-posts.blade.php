@extends('layouts.app')

@section('title', 'Mes Posts - Zyma')

@section('content')
<div class="min-h-screen">
    <div class="container mx-auto px-4 py-8 max-w-2xl">
        <!-- En-tête -->
        <div class="bg-black/30 backdrop-blur-lg rounded-xl border border-white/10 p-6 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-white">📸 Mes Posts</h1>
                    <p class="text-white/70 mt-1">Gérez vos partages culinaires</p>
                </div>
                <a href="{{ route('social.create') }}" 
                   class="bg-gradient-to-r from-orange-500 to-pink-500 hover:from-orange-600 hover:to-pink-600 text-white px-6 py-3 rounded-lg font-medium transition-all transform hover:scale-105 shadow-lg">
                    + Nouveau post
                </a>
            </div>
        </div>

        <!-- Navigation -->
        <div class="bg-black/30 backdrop-blur-lg rounded-xl border border-white/10 p-4 mb-6">
            <div class="flex space-x-4">
                <a href="{{ route('social.index') }}" 
                   class="text-white/70 hover:text-white hover:bg-white/10 px-6 py-3 rounded-lg transition-all">
                    🌟 Feed
                </a>
                <a href="{{ route('social.my-posts') }}" 
                   class="bg-gradient-to-r from-orange-500 to-pink-500 text-white px-6 py-3 rounded-lg font-medium shadow-lg">
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

        @if(session('error'))
            <div class="bg-red-500/20 border border-red-400/30 text-red-300 px-4 py-3 rounded-lg mb-6 backdrop-blur-sm">
                {{ session('error') }}
            </div>
        @endif

        <!-- Statistiques -->
        <div class="bg-black/30 backdrop-blur-lg rounded-xl border border-white/10 p-6 mb-6">
            <div class="grid grid-cols-2 gap-4 text-center">
                <div class="bg-gradient-to-r from-orange-500/20 to-pink-500/20 rounded-lg p-4">
                    <div class="text-3xl font-bold text-orange-400">{{ $posts->total() }}</div>
                    <div class="text-sm text-white/70">Posts partagés</div>
                </div>
                <div class="bg-gradient-to-r from-red-500/20 to-pink-500/20 rounded-lg p-4">
                    <div class="text-3xl font-bold text-red-400">
                                                    {{ $posts->sum(function($post) { return $post->likes_count; }) }}
                    </div>
                    <div class="text-sm text-white/70">Likes reçus</div>
                </div>
            </div>
        </div>

        <!-- Posts -->
        @if($posts->count() > 0)
            @foreach($posts as $post)
                <div class="bg-black/30 backdrop-blur-lg rounded-xl border border-white/10 mb-6 overflow-hidden shadow-xl">
                    <!-- En-tête du post -->
                    <div class="p-4 border-b border-white/10">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="w-12 h-12 bg-gradient-to-r from-orange-400 to-pink-400 rounded-full flex items-center justify-center text-white font-bold text-lg shadow-lg">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                </div>
                                <div>
                                    <h3 class="font-medium text-white">{{ auth()->user()->name }} <span class="text-sm text-orange-400">(Vous)</span></h3>
                                    <p class="text-sm text-white/60">{{ $post->created_at->diffForHumans() }}</p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-3">
                                <span class="text-sm text-white/70 bg-white/10 px-3 py-1 rounded-lg">{{ $post->likes_count }} ❤️</span>
                                <span class="text-sm text-white/70 bg-white/10 px-3 py-1 rounded-lg">{{ $post->reactions->count() }} 😍</span>
                                <span class="text-sm text-white/70 bg-white/10 px-3 py-1 rounded-lg">{{ $post->comments->count() }} 💬</span>
                                <form action="{{ route('social.destroy', $post) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="text-red-400 hover:text-red-300 text-sm p-2 hover:bg-red-500/20 rounded-lg transition-all"
                                            onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce post ?')">
                                        🗑️
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Image du repas -->
                    <div class="relative">
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
                             alt="Repas partagé"
                             class="w-full h-96 object-cover shadow-lg"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        
                        <div class="hidden items-center justify-center w-full h-96 bg-gray-800 text-white text-center">
                            <div>
                                <div class="text-4xl mb-2">📷</div>
                                <div>Image non disponible</div>
                            </div>
                        </div>
                        
                        <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent"></div>
                    </div>

                    <!-- Description et statistiques -->
                    <div class="p-4">
                        <!-- Statistiques du post -->
                        <div class="flex items-center space-x-4 mb-3 text-sm text-white/70">
                            <span class="bg-red-500/20 px-3 py-1 rounded-lg">❤️ {{ $post->likes_count }} likes</span>
                            <span class="bg-orange-500/20 px-3 py-1 rounded-lg">😍 {{ $post->reactions->count() }} réactions</span>
                            <span class="bg-blue-500/20 px-3 py-1 rounded-lg">💬 {{ $post->comments->count() }} commentaires</span>
                            <span class="bg-gray-500/20 px-3 py-1 rounded-lg">📅 {{ $post->created_at->format('d/m/Y à H:i') }}</span>
                        </div>

                        <!-- Description -->
                        @if($post->description)
                            <div class="text-white mb-2 bg-white/5 rounded-lg p-3">
                                <span class="font-medium text-orange-400">{{ auth()->user()->name }}</span>
                                <span class="text-white/90"> {{ $post->description }}</span>
                            </div>
                        @endif

                        <!-- Liste des utilisateurs qui ont liké -->
                        @if($post->likes_count > 0)
                            <div class="mt-3 pt-3 border-t border-white/10">
                                <p class="text-sm text-white/70 mb-2">Aimé par :</p>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($post->likes->take(5) as $like)
                                        <span class="inline-flex items-center bg-gradient-to-r from-orange-500/20 to-pink-500/20 text-white text-xs px-3 py-1 rounded-full border border-orange-400/30">
                                            {{ $like->user->name }}
                                        </span>
                                    @endforeach
                                    @if($post->likes_count > 5)
                                        <span class="text-xs text-white/50">
                                            et {{ $post->likes_count - 5 }} autres...
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endif

                        <!-- Réactions émojis -->
                        @if($post->reactions->count() > 0)
                            <div class="mt-3 pt-3 border-t border-white/10">
                                <p class="text-sm text-white/70 mb-2">Réactions :</p>
                                <div class="flex flex-wrap gap-2">
                                    @php
                                        $reactionsByEmoji = $post->reactions_by_emoji;
                                    @endphp
                                    @foreach($reactionsByEmoji as $emoji => $count)
                                        <span class="inline-flex items-center bg-gradient-to-r from-orange-500/20 to-pink-500/20 text-white text-xs px-3 py-1 rounded-full border border-orange-400/30">
                                            {{ $emoji }} {{ $count }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- Commentaires récents -->
                        @if($post->comments->count() > 0)
                            <div class="mt-3 pt-3 border-t border-white/10">
                                <p class="text-sm text-white/70 mb-2">Commentaires récents :</p>
                                <div class="space-y-2">
                                    @foreach($post->comments->take(3) as $comment)
                                        <div class="flex items-start gap-2 bg-white/5 rounded-lg p-2">
                                            <div class="w-6 h-6 bg-gradient-to-r from-orange-400 to-pink-400 rounded-full flex items-center justify-center text-white font-bold text-xs flex-shrink-0">
                                                {{ strtoupper(substr($comment->user->name, 0, 1)) }}
                                            </div>
                                            <div class="flex-1">
                                                <div class="flex items-center justify-between">
                                                    <span class="font-medium text-orange-400 text-xs">{{ $comment->user->name }}</span>
                                                    <span class="text-xs text-white/50">{{ $comment->created_at->diffForHumans() }}</span>
                                                </div>
                                                <p class="text-white/90 text-xs mt-1">{{ Str::limit($comment->content, 50) }}</p>
                                            </div>
                                        </div>
                                    @endforeach
                                    @if($post->comments->count() > 3)
                                        <p class="text-xs text-white/50 text-center">
                                            et {{ $post->comments->count() - 3 }} autres commentaires...
                                        </p>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach

            <!-- Pagination -->
            <div class="mt-8">
                {{ $posts->links() }}
            </div>
        @else
            <div class="bg-black/30 backdrop-blur-lg rounded-xl border border-white/10 p-8 text-center">
                <div class="text-6xl mb-4">📸</div>
                <h3 class="text-xl font-bold text-white mb-2">Aucun post pour le moment</h3>
                <p class="text-white/70 mb-6">Commencez à partager vos créations culinaires !</p>
                <a href="{{ route('social.create') }}" 
                   class="bg-gradient-to-r from-orange-500 to-pink-500 hover:from-orange-600 hover:to-pink-600 text-white px-8 py-4 rounded-lg font-medium transition-all transform hover:scale-105 inline-block shadow-lg">
                    Partager mon premier repas
                </a>
            </div>
        @endif
    </div>
</div>
@endsection 