@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-900 to-green-900 py-8 px-4">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="mb-8 flex justify-between items-center">
            <div>
                <h1 class="text-4xl font-bold text-white mb-2">
                    💰 Dashboard Prix
                </h1>
                <p class="text-gray-300">Analyse complète des prix et revenus - {{ Auth::user()->email }}</p>
            </div>
            <div class="flex space-x-4">
                <a href="{{ route('admin.analytics') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition-colors">
                    📊 Analytics Général
                </a>
                <a href="{{ route('admin.users') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                    👥 Dashboard Utilisateurs
                </a>
            </div>
        </div>

        <!-- Statistiques Principales Prix -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Total Revenue -->
            <div class="bg-black/70 border border-green-500/30 rounded-xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-green-300 text-sm">Revenus Total</p>
                        <p class="text-3xl font-bold text-white">{{ number_format($priceStats['total_revenue'] ?? 0, 2) }}€</p>
                    </div>
                    <div class="bg-green-500/20 p-3 rounded-lg">
                        <span class="text-green-400 text-2xl">💰</span>
                    </div>
                </div>
                <div class="mt-4 flex items-center">
                    <span class="text-green-400 text-sm">+{{ number_format($priceStats['revenue_week'] ?? 0, 2) }}€</span>
                    <span class="text-gray-400 text-sm ml-2">cette semaine</span>
                </div>
            </div>

            <!-- Prix Moyen -->
            <div class="bg-black/70 border border-blue-500/30 rounded-xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-blue-300 text-sm">Prix Moyen</p>
                        <p class="text-3xl font-bold text-white">{{ number_format($priceStats['average_price'] ?? 0, 2) }}€</p>
                    </div>
                    <div class="bg-blue-500/20 p-3 rounded-lg">
                        <span class="text-blue-400 text-2xl">📊</span>
                    </div>
                </div>
                <div class="mt-4 flex items-center">
                    <span class="text-blue-400 text-sm">Max: {{ number_format($priceStats['highest_price'] ?? 0, 2) }}€</span>
                </div>
            </div>

            <!-- Contributions Total -->
            <div class="bg-black/70 border border-purple-500/30 rounded-xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-purple-300 text-sm">Contributions</p>
                        <p class="text-3xl font-bold text-white">{{ $priceStats['total_contributions'] ?? 0 }}</p>
                    </div>
                    <div class="bg-purple-500/20 p-3 rounded-lg">
                        <span class="text-purple-400 text-2xl">🛒</span>
                    </div>
                </div>
                <div class="mt-4 flex items-center">
                    <span class="text-purple-400 text-sm">+{{ $priceStats['contributions_week'] ?? 0 }}</span>
                    <span class="text-gray-400 text-sm ml-2">cette semaine</span>
                </div>
            </div>

            <!-- Prix Médian -->
            <div class="bg-black/70 border border-orange-500/30 rounded-xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-orange-300 text-sm">Prix Médian</p>
                        <p class="text-3xl font-bold text-white">{{ number_format($priceStats['median_price'] ?? 0, 2) }}€</p>
                    </div>
                    <div class="bg-orange-500/20 p-3 rounded-lg">
                        <span class="text-orange-400 text-2xl">🎯</span>
                    </div>
                </div>
                <div class="mt-4 flex items-center">
                    <span class="text-orange-400 text-sm">Min: {{ number_format($priceStats['lowest_price'] ?? 0, 2) }}€</span>
                </div>
            </div>
        </div>

        <!-- Revenus par Période -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <!-- Aujourd'hui -->
            <div class="bg-gradient-to-r from-green-500/20 to-emerald-600/20 border border-green-400/30 rounded-xl p-6">
                <div class="text-center">
                    <h3 class="text-green-300 text-lg font-semibold mb-2">📅 Aujourd'hui</h3>
                    <p class="text-3xl font-bold text-white mb-2">{{ number_format($priceStats['revenue_today'] ?? 0, 2) }}€</p>
                    <p class="text-green-400">{{ $priceStats['contributions_today'] ?? 0 }} contributions</p>
                </div>
            </div>

            <!-- Cette Semaine -->
            <div class="bg-gradient-to-r from-blue-500/20 to-cyan-600/20 border border-blue-400/30 rounded-xl p-6">
                <div class="text-center">
                    <h3 class="text-blue-300 text-lg font-semibold mb-2">📊 Cette Semaine</h3>
                    <p class="text-3xl font-bold text-white mb-2">{{ number_format($priceStats['revenue_week'] ?? 0, 2) }}€</p>
                    <p class="text-blue-400">{{ $priceStats['contributions_week'] ?? 0 }} contributions</p>
                </div>
            </div>

            <!-- Ce Mois -->
            <div class="bg-gradient-to-r from-purple-500/20 to-pink-600/20 border border-purple-400/30 rounded-xl p-6">
                <div class="text-center">
                    <h3 class="text-purple-300 text-lg font-semibold mb-2">📈 Ce Mois</h3>
                    <p class="text-3xl font-bold text-white mb-2">{{ number_format($priceStats['revenue_month'] ?? 0, 2) }}€</p>
                    <p class="text-purple-400">{{ $priceStats['contributions_month'] ?? 0 }} contributions</p>
                </div>
            </div>
        </div>

        <!-- Graphique Revenus & Distribution des Prix -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Graphique Revenus 30 jours -->
            <div class="bg-black/70 border border-white/10 rounded-xl p-6">
                <h3 class="text-xl font-semibold text-white mb-4">💰 Revenus des 30 derniers jours</h3>
                <div class="h-64 flex items-end justify-between space-x-1 overflow-x-auto">
                    @php $maxRevenue = max($priceStats['daily_revenue'] ?? [1]); @endphp
                    @foreach($priceStats['daily_revenue'] ?? [] as $day => $revenue)
                    <div class="flex flex-col items-center min-w-0 flex-1">
                        <div class="bg-gradient-to-t from-green-500 to-emerald-400 rounded-t-lg" 
                             style="height: {{ $revenue > 0 ? ($revenue / $maxRevenue) * 200 : 2 }}px; width: 100%;">
                        </div>
                        <span class="text-xs text-gray-400 mt-1 transform rotate-45 origin-left">{{ $day }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Distribution des Prix -->
            <div class="bg-black/70 border border-white/10 rounded-xl p-6">
                <h3 class="text-xl font-semibold text-white mb-4">📊 Distribution des Prix</h3>
                <div class="space-y-3">
                    @php $maxCount = max($priceRanges); @endphp
                    @foreach($priceRanges as $range => $count)
                    <div class="flex items-center justify-between">
                        <span class="text-gray-300 w-16">{{ $range }}</span>
                        <div class="flex-1 mx-4">
                            <div class="bg-gray-700 rounded-full h-3">
                                <div class="bg-gradient-to-r from-blue-500 to-purple-500 h-3 rounded-full" 
                                     style="width: {{ $maxCount > 0 ? ($count / $maxCount) * 100 : 0 }}%"></div>
                            </div>
                        </div>
                        <span class="text-white font-semibold w-12 text-right">{{ $count }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Top Magasins & Catégories -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Top Magasins par Revenus -->
            <div class="bg-black/70 border border-white/10 rounded-xl p-6">
                <h3 class="text-xl font-semibold text-white mb-4">🏪 Top Magasins par Revenus</h3>
                <div class="space-y-3">
                    @foreach($topStores as $index => $store)
                    <div class="flex items-center justify-between p-3 bg-white/5 rounded-lg">
                        <div class="flex items-center">
                            <span class="text-2xl mr-3">
                                @if($index == 0) 🥇
                                @elseif($index == 1) 🥈
                                @elseif($index == 2) 🥉
                                @else {{ $index + 1 }}.
                                @endif
                            </span>
                            <div>
                                <p class="text-white font-medium">{{ $store->store_name }}</p>
                                <p class="text-gray-400 text-sm">{{ $store->total_contributions }} contributions • Moy: {{ number_format($store->avg_price, 2) }}€</p>
                            </div>
                        </div>
                        <span class="text-green-400 font-bold">{{ number_format($store->total_revenue, 2) }}€</span>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Top Catégories -->
            <div class="bg-black/70 border border-white/10 rounded-xl p-6">
                <h3 class="text-xl font-semibold text-white mb-4">📦 Top Catégories par Prix Moyen</h3>
                <div class="space-y-3">
                    @foreach($topCategories as $index => $category)
                    <div class="flex items-center justify-between p-3 bg-white/5 rounded-lg">
                        <div class="flex items-center">
                            <span class="text-2xl mr-3">
                                @if($index == 0) 🥇
                                @elseif($index == 1) 🥈
                                @elseif($index == 2) 🥉
                                @else {{ $index + 1 }}.
                                @endif
                            </span>
                            <div>
                                <p class="text-white font-medium">{{ $category->category }}</p>
                                <p class="text-gray-400 text-sm">{{ $category->total_contributions }} produits • Total: {{ number_format($category->total_revenue, 2) }}€</p>
                            </div>
                        </div>
                        <span class="text-purple-400 font-bold">{{ number_format($category->avg_price, 2) }}€</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Section Vérification -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <!-- Revenus Vérifiés -->
            <div class="bg-black/70 border border-green-500/30 rounded-xl p-6">
                <h3 class="text-xl font-semibold text-white mb-4">✅ Revenus Vérifiés</h3>
                <div class="text-center">
                    <p class="text-4xl font-bold text-green-400 mb-2">{{ number_format($priceStats['verified_revenue'] ?? 0, 2) }}€</p>
                    <p class="text-gray-300">{{ $priceStats['verified_contributions'] ?? 0 }} contributions vérifiées</p>
                    <div class="mt-4 bg-green-500/20 rounded-lg p-3">
                        <p class="text-green-300 text-sm">
                            {{ $priceStats['total_contributions'] > 0 ? round(($priceStats['verified_contributions'] / $priceStats['total_contributions']) * 100, 1) : 0 }}% du total
                        </p>
                    </div>
                </div>
            </div>

            <!-- Revenus En Attente -->
            <div class="bg-black/70 border border-orange-500/30 rounded-xl p-6">
                <h3 class="text-xl font-semibold text-white mb-4">⏳ Revenus en Attente</h3>
                <div class="text-center">
                    <p class="text-4xl font-bold text-orange-400 mb-2">{{ number_format($priceStats['pending_revenue'] ?? 0, 2) }}€</p>
                    <p class="text-gray-300">{{ $priceStats['pending_contributions'] ?? 0 }} contributions en attente</p>
                    <div class="mt-4 bg-orange-500/20 rounded-lg p-3">
                        <p class="text-orange-300 text-sm">
                            {{ $priceStats['total_contributions'] > 0 ? round(($priceStats['pending_contributions'] / $priceStats['total_contributions']) * 100, 1) : 0 }}% du total
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Exports Spécialisés Prix -->
        <div class="bg-black/70 border border-white/10 rounded-xl p-6">
            <h3 class="text-xl font-semibold text-white mb-6">📊 Exports Prix</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Export Prix Détaillé -->
                <a href="{{ route('admin.export.prices') }}" 
                   class="block w-full bg-gradient-to-r from-green-500 to-emerald-600 text-white font-semibold py-4 px-6 rounded-lg hover:scale-105 transition-transform text-center">
                    💰 Export Prix Détaillé
                    <p class="text-sm opacity-90 mt-1">Excel avec tous les prix</p>
                </a>
                
                <!-- Export Statistiques Magasins -->
                <a href="{{ route('admin.export.stores-stats') }}" 
                   class="block w-full bg-gradient-to-r from-blue-500 to-cyan-600 text-white font-semibold py-4 px-6 rounded-lg hover:scale-105 transition-transform text-center">
                    🏪 Stats Magasins
                    <p class="text-sm opacity-90 mt-1">CSV par magasin</p>
                </a>
                
                <!-- Export Contributions -->
                <a href="{{ route('admin.export.excel') }}" 
                   class="block w-full bg-gradient-to-r from-purple-500 to-pink-600 text-white font-semibold py-4 px-6 rounded-lg hover:scale-105 transition-transform text-center">
                    📋 Export Contributions
                    <p class="text-sm opacity-90 mt-1">Excel complet</p>
                </a>
            </div>
            
            <div class="mt-6 p-4 bg-green-500/10 border border-green-500/30 rounded-lg">
                <p class="text-green-300 text-sm text-center">
                    💡 Les exports incluent toutes les données de prix, statistiques par magasin et analyses de revenus
                </p>
            </div>
        </div>
    </div>
</div>
@endsection 