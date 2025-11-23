@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-900 to-blue-900 py-8 px-4">
    <div class="max-w-7xl mx-auto">
        <!-- Header with Navigation to Specialized Dashboards -->
        <div class="mb-8 flex justify-between items-center">
            <div>
                <h1 class="text-4xl font-bold text-white mb-2">
                    📊 Analytics Dashboard
                </h1>
                <p class="text-gray-300">Vue d'ensemble générale - {{ Auth::user()->email }}</p>
            </div>
            <div class="flex space-x-4">
                <a href="{{ route('admin.prices') }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors">
                    💰 Dashboard Prix
                </a>
                <a href="{{ route('admin.users') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                    👥 Dashboard Utilisateurs
                </a>
            </div>
        </div>

        <!-- Quick Access Cards to Specialized Dashboards -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <!-- Prix Dashboard Access -->
            <a href="{{ route('admin.prices') }}" class="block">
                <div class="bg-gradient-to-r from-green-500/20 to-emerald-600/20 border border-green-400/30 rounded-xl p-6 hover:scale-105 transition-transform">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-xl font-semibold text-white mb-2">💰 Analytics Prix</h3>
                            <p class="text-green-300">Revenus, prix moyens, statistiques par magasin</p>
                            <div class="mt-4 flex items-center space-x-4">
                                <span class="bg-green-500/20 text-green-400 px-3 py-1 rounded text-sm">
                                    {{ number_format($stats['total_revenue'] ?? 0, 2) }}€ Total
                                </span>
                                <span class="bg-emerald-500/20 text-emerald-400 px-3 py-1 rounded text-sm">
                                    {{ number_format($stats['average_price'] ?? 0, 2) }}€ Moyen
                                </span>
                            </div>
                        </div>
                        <div class="text-4xl">📈</div>
                    </div>
                </div>
            </a>

            <!-- Users Dashboard Access -->
            <a href="{{ route('admin.users') }}" class="block">
                <div class="bg-gradient-to-r from-blue-500/20 to-cyan-600/20 border border-blue-400/30 rounded-xl p-6 hover:scale-105 transition-transform">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-xl font-semibold text-white mb-2">👥 Analytics Utilisateurs</h3>
                            <p class="text-blue-300">Activité, inscriptions, top contributeurs</p>
                            <div class="mt-4 flex items-center space-x-4">
                                <span class="bg-blue-500/20 text-blue-400 px-3 py-1 rounded text-sm">
                                    {{ $stats['total_users'] ?? 0 }} Utilisateurs
                                </span>
                                <span class="bg-cyan-500/20 text-cyan-400 px-3 py-1 rounded text-sm">
                                    {{ $stats['active_today'] ?? 0 }} Actifs
                                </span>
                            </div>
                        </div>
                        <div class="text-4xl">👤</div>
                    </div>
                </div>
            </a>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Total Users -->
            <div class="bg-black/70 border border-white/10 rounded-xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-400 text-sm">Utilisateurs Total</p>
                        <p class="text-3xl font-bold text-white">{{ $stats['total_users'] ?? 0 }}</p>
                    </div>
                    <div class="bg-blue-500/20 p-3 rounded-lg">
                        <span class="text-blue-400 text-2xl">👥</span>
                    </div>
                </div>
                <div class="mt-4 flex items-center">
                    <span class="text-green-400 text-sm">+{{ $stats['new_users_week'] ?? 0 }}</span>
                    <span class="text-gray-400 text-sm ml-2">cette semaine</span>
                </div>
            </div>

            <!-- Total Contributions -->
            <div class="bg-black/70 border border-white/10 rounded-xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-400 text-sm">Contributions Total</p>
                        <p class="text-3xl font-bold text-white">{{ $stats['total_contributions'] ?? 0 }}</p>
                    </div>
                    <div class="bg-green-500/20 p-3 rounded-lg">
                        <span class="text-green-400 text-2xl">🛒</span>
                    </div>
                </div>
                <div class="mt-4 flex items-center">
                    <span class="text-green-400 text-sm">+{{ $stats['contributions_week'] ?? 0 }}</span>
                    <span class="text-gray-400 text-sm ml-2">cette semaine</span>
                </div>
            </div>

            <!-- Total Revenue -->
            <div class="bg-black/70 border border-white/10 rounded-xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-400 text-sm">Revenus Total</p>
                        <p class="text-3xl font-bold text-white">{{ number_format($stats['total_revenue'] ?? 0, 2) }}€</p>
                    </div>
                    <div class="bg-yellow-500/20 p-3 rounded-lg">
                        <span class="text-yellow-400 text-2xl">💰</span>
                    </div>
                </div>
                <div class="mt-4 flex items-center">
                    <span class="text-green-400 text-sm">+{{ number_format($stats['revenue_week'] ?? 0, 2) }}€</span>
                    <span class="text-gray-400 text-sm ml-2">cette semaine</span>
                </div>
            </div>

            <!-- Average Price -->
            <div class="bg-black/70 border border-white/10 rounded-xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-400 text-sm">Prix Moyen</p>
                        <p class="text-3xl font-bold text-white">{{ number_format($stats['average_price'] ?? 0, 2) }}€</p>
                    </div>
                    <div class="bg-purple-500/20 p-3 rounded-lg">
                        <span class="text-purple-400 text-2xl">📊</span>
                    </div>
                </div>
                <div class="mt-4 flex items-center">
                    <span class="text-blue-400 text-sm">Max: {{ number_format($stats['highest_price'] ?? 0, 2) }}€</span>
                </div>
            </div>
        </div>

        <!-- ✨ NOUVELLE SECTION: Statistiques de Prix Détaillées -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <!-- Contributions Today -->
            <div class="bg-gradient-to-r from-green-500/20 to-emerald-600/20 border border-green-400/30 rounded-xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-green-300 text-sm">Aujourd'hui</p>
                        <p class="text-2xl font-bold text-white">{{ $stats['contributions_today'] ?? 0 }} produits</p>
                        <p class="text-green-400 font-semibold">{{ number_format($stats['revenue_today'] ?? 0, 2) }}€</p>
                    </div>
                    <div class="bg-green-500/30 p-3 rounded-lg">
                        <span class="text-green-300 text-2xl">📈</span>
                    </div>
                </div>
            </div>

            <!-- Verified vs Pending -->
            <div class="bg-gradient-to-r from-blue-500/20 to-cyan-600/20 border border-blue-400/30 rounded-xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-blue-300 text-sm">Vérification</p>
                        <p class="text-lg font-bold text-white">✅ {{ $stats['verified_contributions'] ?? 0 }}</p>
                        <p class="text-orange-400 font-semibold">⏳ {{ $stats['pending_contributions'] ?? 0 }}</p>
                    </div>
                    <div class="bg-blue-500/30 p-3 rounded-lg">
                        <span class="text-blue-300 text-2xl">✅</span>
                    </div>
                </div>
            </div>

            <!-- Revenue Growth -->
            <div class="bg-gradient-to-r from-purple-500/20 to-pink-600/20 border border-purple-400/30 rounded-xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-purple-300 text-sm">Croissance Revenus</p>
                        <p class="text-2xl font-bold text-white">{{ number_format($stats['revenue_week'] ?? 0, 2) }}€</p>
                        <p class="text-purple-400 text-sm">cette semaine</p>
                    </div>
                    <div class="bg-purple-500/30 p-3 rounded-lg">
                        <span class="text-purple-300 text-2xl">💎</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Revenue Chart -->
            <div class="bg-black/70 border border-white/10 rounded-xl p-6">
                <h3 class="text-xl font-semibold text-white mb-4">💰 Revenus des 7 derniers jours</h3>
                <div class="h-64 flex items-end justify-between space-x-2">
                    @foreach($stats['daily_revenue'] ?? [] as $day => $revenue)
                    <div class="flex flex-col items-center">
                        <div class="bg-gradient-to-t from-yellow-500 to-orange-500 rounded-t-lg" 
                             style="height: {{ $revenue > 0 ? ($revenue / max($stats['daily_revenue'])) * 200 : 5 }}px; width: 40px;">
                        </div>
                        <span class="text-xs text-gray-400 mt-2">{{ $day }}</span>
                        <span class="text-xs text-white">{{ number_format($revenue, 2) }}€</span>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Top Actions -->
            <div class="bg-black/70 border border-white/10 rounded-xl p-6">
                <h3 class="text-xl font-semibold text-white mb-4">🔥 Actions populaires</h3>
                <div class="space-y-4">
                    @foreach($stats['top_actions'] ?? [] as $action)
                    <div class="flex items-center justify-between p-3 bg-white/5 rounded-lg">
                        <div class="flex items-center">
                            <span class="text-2xl mr-3">{{ $action['icon'] }}</span>
                            <div>
                                <p class="text-white font-medium">{{ $action['name'] }}</p>
                                <p class="text-gray-400 text-sm">{{ $action['description'] }}</p>
                            </div>
                        </div>
                        <span class="text-blue-400 font-semibold">{{ $action['count'] }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Top Stores & Categories -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Top Stores -->
            <div class="bg-black/70 border border-white/10 rounded-xl p-6">
                <h3 class="text-xl font-semibold text-white mb-4">🏪 Top Magasins par Revenus</h3>
                <div class="space-y-3">
                    @foreach($stats['top_stores'] ?? [] as $store)
                    <div class="flex items-center justify-between p-3 bg-white/5 rounded-lg">
                        <div>
                            <p class="text-white font-medium">{{ $store->store_name }}</p>
                            <p class="text-gray-400 text-sm">{{ $store->total_contributions }} contributions</p>
                        </div>
                        <span class="text-green-400 font-semibold">{{ number_format($store->total_revenue, 2) }}€</span>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Top Categories -->
            <div class="bg-black/70 border border-white/10 rounded-xl p-6">
                <h3 class="text-xl font-semibold text-white mb-4">📦 Top Catégories par Prix Moyen</h3>
                <div class="space-y-3">
                    @foreach($stats['top_categories'] ?? [] as $category)
                    <div class="flex items-center justify-between p-3 bg-white/5 rounded-lg">
                        <div>
                            <p class="text-white font-medium">{{ $category->category }}</p>
                            <p class="text-gray-400 text-sm">{{ $category->total_contributions }} produits</p>
                        </div>
                        <span class="text-purple-400 font-semibold">{{ number_format($category->avg_price, 2) }}€</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Export Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Recent Users -->
            <div class="bg-black/70 border border-white/10 rounded-xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-semibold text-white">👤 Utilisateurs récents</h3>
                    <span class="text-gray-400 text-sm">{{ count($recent_users ?? []) }} utilisateurs</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-white/10">
                                <th class="text-left text-gray-400 text-sm py-3">Utilisateur</th>
                                <th class="text-left text-gray-400 text-sm py-3">Email</th>
                                <th class="text-left text-gray-400 text-sm py-3">Inscription</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recent_users ?? [] as $user)
                            <tr class="border-b border-white/5">
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
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center text-gray-400 py-8">Aucun utilisateur trouvé</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Export Section -->
            <div class="bg-black/70 border border-white/10 rounded-xl p-6">
                <h3 class="text-xl font-semibold text-white mb-4">📊 Exports</h3>
                <div class="space-y-4">
                    <!-- Contributions Export -->
                    <a href="{{ route('admin.export.excel') }}" 
                       class="block w-full bg-gradient-to-r from-green-500 to-emerald-600 text-white font-semibold py-3 px-4 rounded-lg hover:scale-105 transition-transform text-center">
                        💰 Exporter les Contributions
                    </a>
                    
                    <!-- Users Export -->
                    <a href="{{ route('admin.export.users') }}" 
                       class="block w-full bg-gradient-to-r from-blue-500 to-cyan-600 text-white font-semibold py-3 px-4 rounded-lg hover:scale-105 transition-transform text-center">
                        📋 Exporter les utilisateurs
                    </a>
                    
                    <!-- Activity Export -->
                    <a href="{{ route('admin.export.activity') }}" 
                       class="block w-full bg-gradient-to-r from-purple-500 to-pink-600 text-white font-semibold py-3 px-4 rounded-lg hover:scale-105 transition-transform text-center">
                        📈 Exporter l'activité
                    </a>
                    
                    <!-- Full Report -->
                    <a href="{{ route('admin.export.full') }}" 
                       class="block w-full bg-gradient-to-r from-orange-500 to-red-600 text-white font-semibold py-3 px-4 rounded-lg hover:scale-105 transition-transform text-center">
                        📊 Rapport complet
                    </a>
                </div>
                
                <div class="mt-6 p-4 bg-white/5 rounded-lg">
                    <p class="text-gray-400 text-xs">
                        💡 Les exports sont générés en temps réel et incluent toutes les données disponibles.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 