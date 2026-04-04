@extends('layouts.app')

@section('title', 'Classement Complet - Ligue Healthy')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-green-50 via-blue-50 to-purple-50">
    <!-- Header -->
    <div class="bg-white/80 backdrop-blur-sm border-b border-green-100">
        <div class="container mx-auto px-4 py-8">
            <div class="flex items-center gap-4 mb-6">
                <a href="{{ route('league.index') }}" class="text-gray-600 hover:text-gray-800">
                    ← Retour
                </a>
                <h1 class="text-3xl font-bold bg-gradient-to-r from-green-600 to-blue-600 bg-clip-text text-transparent">
                    🏆 Classement Complet
                </h1>
            </div>
        </div>
    </div>

    <div class="container mx-auto px-4 py-8">
        <div class="bg-white/80 backdrop-blur-sm rounded-3xl p-6 border border-gray-100 shadow-lg">
            
            <!-- Podium Top 3 -->
            @if($rankings->count() >= 3)
                <div class="mb-8">
                    <h2 class="text-2xl font-bold text-center mb-6 text-gray-800">🏅 Podium</h2>
                    <div class="flex justify-center items-end gap-4 mb-8">
                        
                        <!-- 2ème place -->
                        <div class="text-center">
                            <div class="bg-gradient-to-b from-gray-200 to-gray-300 rounded-2xl p-6 mb-4 relative">
                                <div class="absolute -top-3 left-1/2 transform -translate-x-1/2 bg-gray-400 text-white rounded-full w-8 h-8 flex items-center justify-center font-bold text-sm">2</div>
                                <div class="text-3xl mb-2">🥈</div>
                                <div class="font-bold text-gray-800">{{ $rankings[1]->name }}</div>
                                <div class="text-sm text-gray-600">{{ $rankings[1]->level }}</div>
                                <div class="text-lg font-bold text-gray-600 mt-2">{{ $rankings[1]->health_points }} pts</div>
                            </div>
                        </div>

                        <!-- 1ère place -->
                        <div class="text-center">
                            <div class="bg-gradient-to-b from-yellow-200 to-yellow-400 rounded-2xl p-8 mb-4 relative transform scale-110">
                                <div class="absolute -top-4 left-1/2 transform -translate-x-1/2 bg-yellow-500 text-white rounded-full w-10 h-10 flex items-center justify-center font-bold">1</div>
                                <div class="text-4xl mb-2">👑</div>
                                <div class="font-bold text-gray-800 text-lg">{{ $rankings[0]->name }}</div>
                                <div class="text-sm text-gray-700">{{ $rankings[0]->level }}</div>
                                <div class="text-xl font-bold text-yellow-700 mt-2">{{ $rankings[0]->health_points }} pts</div>
                            </div>
                        </div>

                        <!-- 3ème place -->
                        <div class="text-center">
                            <div class="bg-gradient-to-b from-orange-200 to-orange-300 rounded-2xl p-6 mb-4 relative">
                                <div class="absolute -top-3 left-1/2 transform -translate-x-1/2 bg-orange-400 text-white rounded-full w-8 h-8 flex items-center justify-center font-bold text-sm">3</div>
                                <div class="text-3xl mb-2">🥉</div>
                                <div class="font-bold text-gray-800">{{ $rankings[2]->name }}</div>
                                <div class="text-sm text-gray-600">{{ $rankings[2]->level }}</div>
                                <div class="text-lg font-bold text-orange-600 mt-2">{{ $rankings[2]->health_points }} pts</div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Classement Complet -->
            <div class="space-y-3">
                <h3 class="text-xl font-bold text-gray-800 mb-4">📊 Classement Complet</h3>
                
                @forelse($rankings as $player)
                    <div class="flex items-center gap-4 p-4 rounded-2xl {{ $player->rank <= 3 ? 'bg-gradient-to-r from-yellow-50 to-orange-50 border-2 border-yellow-200' : ($player->rank <= 10 ? 'bg-blue-50 border border-blue-200' : 'bg-gray-50 hover:bg-gray-100') }} transition-all duration-300">
                        
                        <!-- Rang et Badge -->
                        <div class="flex items-center gap-3 min-w-[80px]">
                            <div class="text-xl font-bold {{ $player->rank <= 3 ? 'text-yellow-600' : ($player->rank <= 10 ? 'text-blue-600' : 'text-gray-500') }}">
                                #{{ $player->rank }}
                            </div>
                            <div class="text-2xl">{{ $player->badge }}</div>
                        </div>

                        <!-- Informations Joueur -->
                        <div class="flex-1">
                            <div class="font-semibold text-gray-800 text-lg">{{ $player->name }}</div>
                            <div class="text-sm text-gray-600">{{ $player->level }}</div>
                            @if($player->last_post_date)
                                <div class="text-xs text-gray-500 mt-1">
                                    Dernier post: {{ \Carbon\Carbon::parse($player->last_post_date)->diffForHumans() }}
                                </div>
                            @endif
                        </div>

                        <!-- Statistiques Détaillées -->
                        <div class="text-right">
                            <div class="font-bold text-green-600 text-lg">{{ $player->health_points }} pts</div>
                            <div class="text-sm text-gray-600">
                                Score moyen: {{ number_format($player->avg_health_score, 1) }}%
                            </div>
                            <div class="text-xs text-gray-500">
                                {{ $player->total_posts }} repas partagés
                            </div>
                        </div>

                        <!-- Progression -->
                        <div class="w-24">
                            @php
                                $nextLevelPoints = 0;
                                if($player->health_points < 20) $nextLevelPoints = 20;
                                elseif($player->health_points < 50) $nextLevelPoints = 50;
                                elseif($player->health_points < 100) $nextLevelPoints = 100;
                                elseif($player->health_points < 200) $nextLevelPoints = 200;
                                elseif($player->health_points < 500) $nextLevelPoints = 500;
                                
                                $progress = $nextLevelPoints > 0 ? ($player->health_points / $nextLevelPoints * 100) : 100;
                            @endphp
                            
                            @if($nextLevelPoints > 0)
                                <div class="text-xs text-gray-500 mb-1">{{ $nextLevelPoints - $player->health_points }} pts</div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-gradient-to-r from-green-400 to-blue-400 h-2 rounded-full" style="width: {{ min(100, $progress) }}%"></div>
                                </div>
                            @else
                                <div class="text-xs text-green-600 font-medium">Max Level!</div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="text-center py-12 text-gray-500">
                        <div class="text-6xl mb-4">🍽️</div>
                        <h3 class="text-xl font-medium mb-2">Aucun joueur dans le classement</h3>
                        <p>Soyez le premier à partager un repas healthy !</p>
                    </div>
                @endforelse
            </div>

            <!-- Pagination si nécessaire -->
            @if($rankings->count() >= 50)
                <div class="mt-8 text-center">
                    <p class="text-gray-600">Affichage des 50 premiers joueurs</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection