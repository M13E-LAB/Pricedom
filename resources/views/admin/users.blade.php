@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-900 to-blue-900 py-8 px-4">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="mb-8 flex justify-between items-center">
            <div>
                <h1 class="text-4xl font-bold text-white mb-2">
                    👥 Dashboard Utilisateurs
                </h1>
                <p class="text-gray-300">Analyse complète des utilisateurs et de leur activité - {{ Auth::user()->email }}</p>
            </div>
            <div class="flex space-x-4">
                <a href="{{ route('admin.analytics') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition-colors">
                    📊 Analytics Général
                </a>
                <a href="{{ route('admin.prices') }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors">
                    💰 Dashboard Prix
                </a>
            </div>
        </div>

        <!-- Statistiques Principales Utilisateurs -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Total Utilisateurs -->
            <div class="bg-black/70 border border-blue-500/30 rounded-xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-blue-300 text-sm">Utilisateurs Total</p>
                        <p class="text-3xl font-bold text-white">{{ $userStats['total_users'] ?? 0 }}</p>
                    </div>
                    <div class="bg-blue-500/20 p-3 rounded-lg">
                        <span class="text-blue-400 text-2xl">👥</span>
                    </div>
                </div>
                <div class="mt-4 flex items-center">
                    <span class="text-blue-400 text-sm">+{{ $userStats['new_users_week'] ?? 0 }}</span>
                    <span class="text-gray-400 text-sm ml-2">cette semaine</span>
                </div>
            </div>

            <!-- Nouveaux Aujourd'hui -->
            <div class="bg-black/70 border border-green-500/30 rounded-xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-green-300 text-sm">Nouveaux Aujourd'hui</p>
                        <p class="text-3xl font-bold text-white">{{ $userStats['new_users_today'] ?? 0 }}</p>
                    </div>
                    <div class="bg-green-500/20 p-3 rounded-lg">
                        <span class="text-green-400 text-2xl">✨</span>
                    </div>
                </div>
                <div class="mt-4 flex items-center">
                    <span class="text-green-400 text-sm">+{{ $userStats['new_users_month'] ?? 0 }}</span>
                    <span class="text-gray-400 text-sm ml-2">ce mois</span>
                </div>
            </div>

            <!-- Actifs Aujourd'hui -->
            <div class="bg-black/70 border border-purple-500/30 rounded-xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-purple-300 text-sm">Actifs Aujourd'hui</p>
                        <p class="text-3xl font-bold text-white">{{ $userStats['active_today'] ?? 0 }}</p>
                    </div>
                    <div class="bg-purple-500/20 p-3 rounded-lg">
                        <span class="text-purple-400 text-2xl">🔥</span>
                    </div>
                </div>
                <div class="mt-4 flex items-center">
                    <span class="text-purple-400 text-sm">{{ $userStats['active_week'] ?? 0 }}</span>
                    <span class="text-gray-400 text-sm ml-2">cette semaine</span>
                </div>
            </div>

            <!-- Actifs Ce Mois -->
            <div class="bg-black/70 border border-orange-500/30 rounded-xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-orange-300 text-sm">Actifs Ce Mois</p>
                        <p class="text-3xl font-bold text-white">{{ $userStats['active_month'] ?? 0 }}</p>
                    </div>
                    <div class="bg-orange-500/20 p-3 rounded-lg">
                        <span class="text-orange-400 text-2xl">📈</span>
                    </div>
                </div>
                <div class="mt-4 flex items-center">
                    <span class="text-orange-400 text-sm">
                        {{ $userStats['total_users'] > 0 ? round(($userStats['active_month'] / $userStats['total_users']) * 100, 1) : 0 }}%
                    </span>
                    <span class="text-gray-400 text-sm ml-2">du total</span>
                </div>
            </div>
        </div>

        <!-- Graphique Inscriptions & Top Contributors -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Graphique Inscriptions 30 jours -->
            <div class="bg-black/70 border border-white/10 rounded-xl p-6">
                <h3 class="text-xl font-semibold text-white mb-4">📈 Inscriptions des 30 derniers jours</h3>
                <div class="h-64 flex items-end justify-between space-x-1 overflow-x-auto">
                    @php $maxRegistrations = max($userStats['daily_registrations'] ?? [1]); @endphp
                    @foreach($userStats['daily_registrations'] ?? [] as $day => $count)
                    <div class="flex flex-col items-center min-w-0 flex-1">
                        <div class="bg-gradient-to-t from-blue-500 to-cyan-400 rounded-t-lg" 
                             style="height: {{ $count > 0 ? ($count / $maxRegistrations) * 200 : 2 }}px; width: 100%;">
                        </div>
                        <span class="text-xs text-gray-400 mt-1 transform rotate-45 origin-left">{{ $day }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Top Contributors -->
            <div class="bg-black/70 border border-white/10 rounded-xl p-6">
                <h3 class="text-xl font-semibold text-white mb-4">🏆 Top Contributeurs</h3>
                <div class="space-y-3 max-h-64 overflow-y-auto">
                    @foreach($topContributors as $index => $contributor)
                    <div class="flex items-center justify-between p-3 bg-white/5 rounded-lg">
                        <div class="flex items-center">
                            <span class="text-2xl mr-3">
                                @if($index == 0) 🥇
                                @elseif($index == 1) 🥈
                                @elseif($index == 2) 🥉
                                @else {{ $index + 1 }}.
                                @endif
                            </span>
                            <div class="w-8 h-8 bg-gradient-to-r from-blue-400 to-purple-500 rounded-full flex items-center justify-center mr-3">
                                <span class="text-white text-sm font-semibold">{{ strtoupper(substr($contributor->name, 0, 1)) }}</span>
                            </div>
                            <div>
                                <p class="text-white font-medium">{{ $contributor->name }}</p>
                                <p class="text-gray-400 text-sm">{{ number_format($contributor->total_contributed ?? 0, 2) }}€ générés</p>
                            </div>
                        </div>
                        <span class="text-green-400 font-bold">{{ $contributor->contributions_count ?? 0 }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Utilisateurs Récents -->
        <div class="bg-black/70 border border-white/10 rounded-xl p-6 mb-8">
            <h3 class="text-xl font-semibold text-white mb-4">👤 Utilisateurs Récents</h3>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-white/10">
                            <th class="text-left text-gray-400 text-sm py-3">Utilisateur</th>
                            <th class="text-left text-gray-400 text-sm py-3">Email</th>
                            <th class="text-left text-gray-400 text-sm py-3">Inscription</th>
                            <th class="text-left text-gray-400 text-sm py-3">Activité</th>
                            <th class="text-left text-gray-400 text-sm py-3">Contributions</th>
                            <th class="text-left text-gray-400 text-sm py-3">Revenus</th>
                            <th class="text-left text-gray-400 text-sm py-3">Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentUsers as $user)
                        @php
                            $contributionsCount = $user->contributions->count();
                            $totalRevenue = $user->contributions->sum('price');
                            $daysSinceLastActivity = $user->updated_at->diffInDays(now());
                            
                            $statusColor = 'text-red-400';
                            $statusText = 'Inactif';
                            if ($daysSinceLastActivity == 0) {
                                $statusColor = 'text-green-400';
                                $statusText = 'Actif';
                            } elseif ($daysSinceLastActivity <= 7) {
                                $statusColor = 'text-yellow-400';
                                $statusText = 'Récent';
                            }
                        @endphp
                        <tr class="border-b border-white/5 hover:bg-white/5 transition-colors">
                            <td class="py-3">
                                <div class="flex items-center">
                                    <div class="w-8 h-8 bg-gradient-to-r from-blue-400 to-purple-500 rounded-full flex items-center justify-center mr-3">
                                        <span class="text-white text-sm font-semibold">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                                    </div>
                                    <span class="text-white">{{ $user->name }}</span>
                                </div>
                            </td>
                            <td class="py-3 text-gray-300">{{ $user->email }}</td>
                            <td class="py-3 text-gray-400">{{ $user->created_at->format('d/m/Y') }}</td>
                            <td class="py-3 text-gray-400">{{ $user->updated_at->format('d/m/Y') }}</td>
                            <td class="py-3">
                                <span class="bg-blue-500/20 text-blue-400 px-2 py-1 rounded text-sm">
                                    {{ $contributionsCount }}
                                </span>
                            </td>
                            <td class="py-3 text-green-400 font-semibold">{{ number_format($totalRevenue, 2) }}€</td>
                            <td class="py-3">
                                <span class="{{ $statusColor }} text-sm font-medium">{{ $statusText }}</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-gray-400 py-8">Aucun utilisateur trouvé</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Statistiques d'Activité par Période -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <!-- Activité Aujourd'hui -->
            <div class="bg-gradient-to-r from-green-500/20 to-emerald-600/20 border border-green-400/30 rounded-xl p-6">
                <div class="text-center">
                    <h3 class="text-green-300 text-lg font-semibold mb-2">📅 Aujourd'hui</h3>
                    <p class="text-3xl font-bold text-white mb-2">{{ $userStats['active_today'] ?? 0 }}</p>
                    <p class="text-green-400">utilisateurs actifs</p>
                    <div class="mt-3 text-sm text-gray-300">
                        {{ $userStats['new_users_today'] ?? 0 }} nouvelles inscriptions
                    </div>
                </div>
            </div>

            <!-- Activité Cette Semaine -->
            <div class="bg-gradient-to-r from-blue-500/20 to-cyan-600/20 border border-blue-400/30 rounded-xl p-6">
                <div class="text-center">
                    <h3 class="text-blue-300 text-lg font-semibold mb-2">📊 Cette Semaine</h3>
                    <p class="text-3xl font-bold text-white mb-2">{{ $userStats['active_week'] ?? 0 }}</p>
                    <p class="text-blue-400">utilisateurs actifs</p>
                    <div class="mt-3 text-sm text-gray-300">
                        {{ $userStats['new_users_week'] ?? 0 }} nouvelles inscriptions
                    </div>
                </div>
            </div>

            <!-- Activité Ce Mois -->
            <div class="bg-gradient-to-r from-purple-500/20 to-pink-600/20 border border-purple-400/30 rounded-xl p-6">
                <div class="text-center">
                    <h3 class="text-purple-300 text-lg font-semibold mb-2">📈 Ce Mois</h3>
                    <p class="text-3xl font-bold text-white mb-2">{{ $userStats['active_month'] ?? 0 }}</p>
                    <p class="text-purple-400">utilisateurs actifs</p>
                    <div class="mt-3 text-sm text-gray-300">
                        {{ $userStats['new_users_month'] ?? 0 }} nouvelles inscriptions
                    </div>
                </div>
            </div>
        </div>

        <!-- Exports Spécialisés Utilisateurs -->
        <div class="bg-black/70 border border-white/10 rounded-xl p-6">
            <h3 class="text-xl font-semibold text-white mb-6">👥 Exports Utilisateurs</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Export Utilisateurs Détaillé -->
                <a href="{{ route('admin.export.users-data') }}" 
                   class="block w-full bg-gradient-to-r from-blue-500 to-cyan-600 text-white font-semibold py-4 px-6 rounded-lg hover:scale-105 transition-transform text-center">
                    👥 Export Utilisateurs Détaillé
                    <p class="text-sm opacity-90 mt-1">Excel avec contributions</p>
                </a>
                
                <!-- Export Activité -->
                <a href="{{ route('admin.export.activity') }}" 
                   class="block w-full bg-gradient-to-r from-purple-500 to-pink-600 text-white font-semibold py-4 px-6 rounded-lg hover:scale-105 transition-transform text-center">
                    📈 Export Activité
                    <p class="text-sm opacity-90 mt-1">CSV d'activité</p>
                </a>
                
                <!-- Export Utilisateurs Simple -->
                <a href="{{ route('admin.export.users') }}" 
                   class="block w-full bg-gradient-to-r from-gray-500 to-gray-600 text-white font-semibold py-4 px-6 rounded-lg hover:scale-105 transition-transform text-center">
                    📋 Export Simple
                    <p class="text-sm opacity-90 mt-1">CSV basique</p>
                </a>
            </div>
            
            <div class="mt-6 p-4 bg-blue-500/10 border border-blue-500/30 rounded-lg">
                <p class="text-blue-300 text-sm text-center">
                    💡 Les exports incluent les données d'activité, contributions et statistiques détaillées des utilisateurs
                </p>
            </div>
        </div>
    </div>
</div>
@endsection 