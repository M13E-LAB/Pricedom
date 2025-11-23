@extends('layouts.app')

@section('title', 'Contribuer - Zyma')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-900 py-8 px-4">
    <div class="max-w-6xl mx-auto">
        
        <!-- Notification de nouveau badge -->
        @if(session('new_badges'))
            @foreach(session('new_badges') as $badgeData)
            <div class="fixed inset-0 bg-black/80 flex items-center justify-center z-50" id="badge-modal">
                <div class="bg-gradient-to-r {{ $badgeData['config']['color'] }} p-1 rounded-3xl max-w-md mx-4">
                    <div class="bg-black/90 rounded-3xl p-8 text-center">
                        <div class="text-8xl mb-4 animate-bounce">{{ $badgeData['config']['emoji'] }}</div>
                        <h2 class="text-3xl font-bold text-white mb-2">🎉 NOUVEAU BADGE !</h2>
                        <h3 class="text-2xl font-semibold text-transparent bg-gradient-to-r {{ $badgeData['config']['color'] }} bg-clip-text mb-3">
                            {{ $badgeData['config']['name'] }}
                        </h3>
                        <p class="text-gray-300 mb-6">{{ $badgeData['config']['description'] }}</p>
                        <button onclick="closeBadgeModal()" class="bg-gradient-to-r {{ $badgeData['config']['color'] }} text-white font-bold py-3 px-8 rounded-xl hover:scale-105 transition-transform">
                            Continuer ! 🚀
                        </button>
                    </div>
                </div>
            </div>
            @endforeach
        @endif

        <!-- Header avec titre gamifié -->
        <div class="text-center mb-8">
            <h1 class="text-5xl font-bold text-white mb-4">
                <span class="text-transparent bg-gradient-to-r from-yellow-400 via-orange-500 to-red-500 bg-clip-text">
                    Contribuer 🎮
                </span>
            </h1>
            <p class="text-xl text-gray-300">Gagne des badges épiques en partageant les prix !</p>
        </div>

        <!-- Statistiques utilisateur -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
            <!-- Stats principales -->
            <div class="lg:col-span-2 bg-black/70 border border-white/10 rounded-2xl p-6">
                <h3 class="text-2xl font-bold text-white mb-6 flex items-center">
                    📊 Tes Statistiques 
                    <span class="ml-3 text-lg bg-gradient-to-r from-yellow-400 to-orange-500 text-black px-3 py-1 rounded-full font-black">
                        Niveau {{ $stats['badges_count'] + 1 }}
                    </span>
                </h3>
                
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-6">
                    <div class="bg-gradient-to-r from-blue-500/20 to-cyan-500/20 rounded-xl p-4 text-center">
                        <div class="text-3xl font-bold text-blue-400">{{ $stats['total_contributions'] }}</div>
                        <div class="text-gray-300 text-sm">Prix ajoutés</div>
                    </div>
                    <div class="bg-gradient-to-r from-purple-500/20 to-pink-500/20 rounded-xl p-4 text-center">
                        <div class="text-3xl font-bold text-purple-400">{{ $stats['badges_count'] }}</div>
                        <div class="text-gray-300 text-sm">Badges gagnés</div>
                    </div>
                    <div class="bg-gradient-to-r from-green-500/20 to-emerald-500/20 rounded-xl p-4 text-center">
                        <div class="text-3xl font-bold text-green-400">{{ $stats['completion_percentage'] }}%</div>
                        <div class="text-gray-300 text-sm">Progression</div>
                    </div>
                </div>

                <!-- Prochain badge -->
                @if($stats['next_badge'])
                <div class="bg-white/5 rounded-xl p-6">
                    <h4 class="text-lg font-bold text-white mb-3 flex items-center">
                        🎯 Prochain Badge: {{ $stats['next_badge']['emoji'] }} {{ $stats['next_badge']['name'] }}
                    </h4>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-gray-300">Progression</span>
                        <span class="text-white font-semibold">{{ $stats['total_contributions'] }}/{{ $stats['next_badge']['threshold'] }}</span>
                    </div>
                    <div class="w-full bg-gray-700 rounded-full h-3 mb-2">
                        <div class="bg-gradient-to-r from-yellow-400 to-orange-500 h-3 rounded-full transition-all duration-500" 
                             style="width: {{ $stats['next_badge']['progress'] }}%"></div>
                    </div>
                    <p class="text-sm text-gray-400">Plus que {{ $stats['next_badge']['remaining'] }} contributions pour débloquer ce badge !</p>
                </div>
                @else
                <div class="bg-gradient-to-r from-yellow-400/20 to-orange-500/20 rounded-xl p-6 text-center">
                    <div class="text-6xl mb-3">👑</div>
                    <h4 class="text-xl font-bold text-yellow-400 mb-2">Maître Suprême Atteint !</h4>
                    <p class="text-gray-300">Tu as débloqué tous les badges disponibles ! 🎉</p>
                </div>
                @endif
            </div>

            <!-- Badges récents -->
            <div class="bg-black/70 border border-white/10 rounded-2xl p-6">
                <h3 class="text-xl font-bold text-white mb-4">🏆 Tes Badges</h3>
                @if($stats['all_badges']->count() > 0)
                    <div class="space-y-3 max-h-64 overflow-y-auto">
                        @foreach($stats['all_badges']->take(5) as $badge)
                        <div class="bg-gradient-to-r {{ $badge->badge_color }} p-0.5 rounded-xl">
                            <div class="bg-black/90 rounded-xl p-3 flex items-center">
                                <span class="text-3xl mr-3">{{ $badge->badge_emoji }}</span>
                                <div>
                                    <div class="text-white font-semibold text-sm">{{ $badge->badge_name }}</div>
                                    <div class="text-gray-400 text-xs">Niveau {{ $badge->badge_level }}</div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @if($stats['all_badges']->count() > 5)
                    <button class="w-full mt-3 text-center text-blue-400 hover:text-blue-300 text-sm">
                        Voir tous les badges ({{ $stats['all_badges']->count() }})
                    </button>
                    @endif
                @else
                    <div class="text-center py-8">
                        <div class="text-6xl opacity-50 mb-3">🏆</div>
                        <p class="text-gray-400">Aucun badge pour le moment</p>
                        <p class="text-gray-500 text-sm">Ajoute 10 prix pour ton premier badge !</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Boutons d'action principaux -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 max-w-4xl mx-auto">
            <!-- Scanner un ticket -->
            <a href="{{ route('contribute.scan') }}" class="group">
                <div class="bg-gradient-to-r from-green-500 to-emerald-600 p-1 rounded-3xl hover:scale-105 transition-transform duration-300">
                    <div class="bg-black/20 rounded-3xl p-8 text-center h-full flex flex-col justify-center">
                        <div class="text-7xl mb-4 group-hover:animate-pulse">📸</div>
                        <h3 class="text-2xl font-bold text-white mb-3">Scanner un ticket</h3>
                        <p class="text-green-100 mb-4">Prends une photo de ton ticket pour ajouter plusieurs prix rapidement</p>
                        <div class="bg-white/20 rounded-full px-4 py-2 inline-block">
                            <span class="text-white font-semibold">+5 XP par prix</span>
                        </div>
                    </div>
                </div>
            </a>

            <!-- Ajouter manuellement -->
            <a href="{{ route('contribute.manual') }}" class="group">
                <div class="bg-gradient-to-r from-orange-500 to-pink-600 p-1 rounded-3xl hover:scale-105 transition-transform duration-300">
                    <div class="bg-black/20 rounded-3xl p-8 text-center h-full flex flex-col justify-center">
                        <div class="text-7xl mb-4 group-hover:animate-pulse">✍️</div>
                        <h3 class="text-2xl font-bold text-white mb-3">Ajouter un prix manuellement</h3>
                        <p class="text-orange-100 mb-4">Saisis directement les informations d'un produit et son prix</p>
                        <div class="bg-white/20 rounded-full px-4 py-2 inline-block">
                            <span class="text-white font-semibold">+3 XP par prix</span>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- Leaderboard tease -->
        <div class="mt-12 text-center">
            <div class="bg-black/70 border border-white/10 rounded-2xl p-6 max-w-2xl mx-auto">
                <h3 class="text-xl font-bold text-white mb-3">🚀 Continue ton ascension !</h3>
                <p class="text-gray-300 mb-4">Plus tu contribues, plus tu débloques de badges épiques et exclusifs.</p>
                <div class="flex justify-center space-x-2">
                    <span class="text-2xl">🌟</span>
                    <span class="text-2xl">🔍</span>
                    <span class="text-2xl">🎯</span>
                    <span class="text-2xl">💎</span>
                    <span class="text-2xl">👑</span>
                    <span class="text-2xl">🚀</span>
                    <span class="text-2xl">⚡</span>
                    <span class="text-2xl">✨</span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function closeBadgeModal() {
    document.getElementById('badge-modal').style.display = 'none';
}

// Auto-fermer après 10 secondes
setTimeout(() => {
    const modal = document.getElementById('badge-modal');
    if (modal) {
        modal.style.display = 'none';
    }
}, 10000);
</script>
@endsection 