@extends('layouts.app')

@section('title', 'Ligue Healthy - Pricedom')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-green-50 via-blue-50 to-purple-50">
    <!-- Header avec statistiques -->
    <div class="bg-white/80 backdrop-blur-sm border-b border-green-100">
        <div class="container mx-auto px-4 py-8">
            <div class="text-center mb-8">
                <h1 class="text-4xl font-bold bg-gradient-to-r from-green-600 to-blue-600 bg-clip-text text-transparent mb-4">
                    🏆 Ligue Healthy
                </h1>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                    Défiez la communauté et montrez vos meilleurs repas sains ! 
                    Gagnez des points en partageant des plats nutritifs et équilibrés.
                </p>
            </div>

            <!-- Statistiques globales -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
                <div class="bg-white/60 backdrop-blur-sm rounded-2xl p-4 text-center border border-green-100">
                    <div class="text-2xl font-bold text-green-600">{{ number_format($stats['total_players']) }}</div>
                    <div class="text-sm text-gray-600">Joueurs</div>
                </div>
                <div class="bg-white/60 backdrop-blur-sm rounded-2xl p-4 text-center border border-blue-100">
                    <div class="text-2xl font-bold text-blue-600">{{ number_format($stats['total_healthy_posts']) }}</div>
                    <div class="text-sm text-gray-600">Repas Healthy</div>
                </div>
                <div class="bg-white/60 backdrop-blur-sm rounded-2xl p-4 text-center border border-purple-100">
                    <div class="text-2xl font-bold text-purple-600">{{ number_format($stats['average_health_score']) }}%</div>
                    <div class="text-sm text-gray-600">Score Moyen</div>
                </div>
                <div class="bg-white/60 backdrop-blur-sm rounded-2xl p-4 text-center border border-orange-100">
                    <div class="text-2xl font-bold text-orange-600">
                        @if($stats['top_scorer_this_week'])
                            {{ $stats['top_scorer_this_week']->name }}
                        @else
                            -
                        @endif
                    </div>
                    <div class="text-sm text-gray-600">Top Semaine</div>
                </div>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-4 py-8">
        <div class="grid lg:grid-cols-3 gap-8">
            
            <!-- Classement Principal -->
            <div class="lg:col-span-2">
                <div class="bg-white/80 backdrop-blur-sm rounded-3xl p-6 border border-gray-100 shadow-lg">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                            🏅 Top 10 Healthy
                        </h2>
                        <a href="{{ route('league.rankings') }}" class="text-blue-600 hover:text-blue-700 font-medium">
                            Voir tout →
                        </a>
                    </div>

                    <div class="space-y-4">
                        @forelse($rankings as $player)
                            <div class="flex items-center gap-4 p-4 rounded-2xl {{ $player->rank <= 3 ? 'bg-gradient-to-r from-yellow-50 to-orange-50 border-2 border-yellow-200' : 'bg-gray-50 hover:bg-gray-100' }} transition-all duration-300">
                                <!-- Rang et Badge -->
                                <div class="flex items-center gap-2">
                                    <div class="text-2xl font-bold {{ $player->rank <= 3 ? 'text-yellow-600' : 'text-gray-500' }}">
                                        #{{ $player->rank }}
                                    </div>
                                    <div class="text-2xl">{{ $player->badge }}</div>
                                </div>

                                <!-- Avatar et Info -->
                                <div class="flex-1">
                                    <div class="font-semibold text-gray-800">{{ $player->name }}</div>
                                    <div class="text-sm text-gray-600">{{ $player->level }}</div>
                                </div>

                                <!-- Statistiques -->
                                <div class="text-right">
                                    <div class="font-bold text-green-600">{{ $player->health_points }} pts</div>
                                    <div class="text-sm text-gray-500">
                                        {{ number_format($player->avg_health_score, 1) }}% • {{ $player->total_posts }} repas
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-8 text-gray-500">
                                <div class="text-4xl mb-2">🍽️</div>
                                <p>Aucun classement disponible pour le moment.</p>
                                <p class="text-sm">Partagez votre premier repas pour apparaître !</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Sidebar - Profil Utilisateur -->
            <div class="space-y-6">
                <!-- Ma Position -->
                <div class="bg-white/80 backdrop-blur-sm rounded-3xl p-6 border border-gray-100 shadow-lg">
                    <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                        🎯 Ma Position
                    </h3>
                    
                    <div class="text-center">
                        @if($userRank['position'])
                            <div class="text-3xl font-bold text-blue-600 mb-2">#{{ $userRank['position'] }}</div>
                            <div class="text-lg font-semibold text-gray-700 mb-1">{{ $userRank['level'] }}</div>
                            <div class="text-sm text-gray-600 mb-4">{{ $userRank['badge'] }} {{ $user->name }}</div>
                            
                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <div class="bg-green-50 rounded-xl p-3">
                                    <div class="font-bold text-green-600">{{ $userRank['health_points'] }}</div>
                                    <div class="text-gray-600">Points</div>
                                </div>
                                <div class="bg-blue-50 rounded-xl p-3">
                                    <div class="font-bold text-blue-600">{{ number_format($userRank['avg_health_score'], 1) }}%</div>
                                    <div class="text-gray-600">Score Moyen</div>
                                </div>
                            </div>
                        @else
                            <div class="text-gray-500 text-center py-4">
                                <div class="text-3xl mb-2">🌱</div>
                                <p class="font-medium">Pas encore classé</p>
                                <p class="text-sm">Partagez votre premier repas healthy !</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Comment ça marche -->
                <div class="bg-white/80 backdrop-blur-sm rounded-3xl p-6 border border-gray-100 shadow-lg">
                    <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                        ❓ Comment ça marche
                    </h3>
                    
                    <div class="space-y-3 text-sm">
                        <div class="flex items-start gap-3">
                            <div class="text-green-500 font-bold">+10</div>
                            <div>Repas très healthy (80-100%)</div>
                        </div>
                        <div class="flex items-start gap-3">
                            <div class="text-blue-500 font-bold">+5</div>
                            <div>Repas healthy (60-79%)</div>
                        </div>
                        <div class="flex items-start gap-3">
                            <div class="text-orange-500 font-bold">+2</div>
                            <div>Repas correct (40-59%)</div>
                        </div>
                        <div class="flex items-start gap-3">
                            <div class="text-red-500 font-bold">0</div>
                            <div>Repas peu healthy (&lt;40%)</div>
                        </div>
                    </div>

                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <p class="text-xs text-gray-600">
                            Le score est calculé automatiquement selon les ingrédients, 
                            la cuisson et la composition nutritionnelle.
                        </p>
                    </div>
                </div>

                <!-- Niveaux -->
                <div class="bg-white/80 backdrop-blur-sm rounded-3xl p-6 border border-gray-100 shadow-lg">
                    <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                        🏅 Niveaux
                    </h3>
                    
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between items-center">
                            <span>👑 Maître Healthy</span>
                            <span class="text-gray-500">500+ pts</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span>🏆 Expert Nutrition</span>
                            <span class="text-gray-500">200+ pts</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span>🥇 Pro Wellness</span>
                            <span class="text-gray-500">100+ pts</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span>🥈 Amateur Sain</span>
                            <span class="text-gray-500">50+ pts</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span>🥉 Apprenti Healthy</span>
                            <span class="text-gray-500">20+ pts</span>
                        </div>
                    </div>
                </div>

                <!-- CTA -->
                <div class="bg-gradient-to-r from-green-500 to-blue-500 rounded-3xl p-6 text-white text-center">
                    <div class="text-2xl mb-2">🚀</div>
                    <h3 class="font-bold mb-2">Prêt à grimper ?</h3>
                    <p class="text-sm mb-4 opacity-90">
                        Partagez vos meilleurs repas healthy et gagnez des points !
                    </p>
                    <a href="{{ route('social.index') }}" class="inline-block bg-white text-green-600 px-6 py-2 rounded-full font-medium hover:bg-gray-100 transition-colors">
                        Partager un repas
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection