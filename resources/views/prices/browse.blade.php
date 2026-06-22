@extends('layouts.app')

@section('title', 'Explorer les Prix - Open Prices')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-7xl">
    <!-- En-tête amélioré -->
    <div class="text-center mb-10">
        <div class="inline-block">
            <h1 class="text-5xl md:text-6xl font-bold text-white mb-3 flex items-center justify-center gap-3">
                <span class="text-6xl">🏷️</span>
                <span>Explorer les Prix</span>
            </h1>
            <div class="h-1 bg-gradient-to-r from-yellow-400 via-orange-500 to-pink-500 rounded-full"></div>
        </div>
        <p class="text-lg md:text-xl text-white/70 max-w-3xl mx-auto mt-6 leading-relaxed">
            Accédez à des millions de prix de produits alimentaires du monde entier grâce à Open Prices
        </p>
    </div>

    <!-- Formulaire de recherche amélioré -->
    <div class="bg-gradient-to-br from-white/15 to-white/5 backdrop-blur-xl rounded-3xl p-6 md:p-10 mb-10 border border-white/20 shadow-2xl">
        <form action="{{ route('prices.search') }}" method="GET" class="space-y-8">
            <!-- Champs principaux -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                <!-- Recherche par produit -->
                <div class="space-y-2">
                    <label class="flex items-center gap-2 text-white font-semibold text-sm">
                        <span class="text-xl">🔍</span>
                        <span>Nom du produit</span>
                        <span class="text-xs text-yellow-300 font-normal bg-yellow-500/20 px-2 py-0.5 rounded-full">⚡ Intelligent</span>
                    </label>
                    <input 
                        type="text" 
                        name="product_name" 
                        placeholder="Ex: Coca Cola, Pain de mie, Nutella..."
                        class="w-full px-4 py-3.5 rounded-xl bg-white/10 border-2 border-white/20 text-white placeholder-white/40 focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400 transition-all"
                        value="{{ request('product_name') }}"
                    >
                </div>

                <!-- Code-barres -->
                <div class="space-y-2">
                    <label class="flex items-center gap-2 text-white font-semibold text-sm">
                        <span class="text-xl">📊</span>
                        <span>Code-barres</span>
                    </label>
                    <input 
                        type="text" 
                        name="product_code" 
                        placeholder="Ex: 3017620422003"
                        class="w-full px-4 py-3.5 rounded-xl bg-white/10 border-2 border-white/20 text-white placeholder-white/40 focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400 transition-all"
                        value="{{ request('product_code') }}"
                    >
                </div>

                <!-- Magasin -->
                <div class="space-y-2">
                    <label class="flex items-center gap-2 text-white font-semibold text-sm">
                        <span class="text-xl">🏪</span>
                        <span>Magasin</span>
                    </label>
                    <input 
                        type="text" 
                        name="location_osm_name" 
                        placeholder="Ex: Carrefour, Leclerc..."
                        class="w-full px-4 py-3.5 rounded-xl bg-white/10 border-2 border-white/20 text-white placeholder-white/40 focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400 transition-all"
                        value="{{ request('location_osm_name') }}"
                    >
                </div>

                <!-- Ville -->
                <div class="space-y-2">
                    <label class="flex items-center gap-2 text-white font-semibold text-sm">
                        <span class="text-xl">🌍</span>
                        <span>Ville</span>
                    </label>
                    <input 
                        type="text" 
                        name="location_osm_address_city" 
                        placeholder="Ex: Paris, Lyon, Marseille..."
                        class="w-full px-4 py-3.5 rounded-xl bg-white/10 border-2 border-white/20 text-white placeholder-white/40 focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400 transition-all"
                        value="{{ request('location_osm_address_city') }}"
                    >
                </div>

                <!-- Pays -->
                <div class="space-y-2">
                    <label class="flex items-center gap-2 text-white font-semibold text-sm">
                        <span class="text-xl">🏳️</span>
                        <span>Pays</span>
                    </label>
                    <select 
                        name="location_osm_address_country" 
                        class="w-full px-4 py-3.5 rounded-xl bg-white/10 border-2 border-white/20 text-white focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400 transition-all"
                    >
                        <option value="" class="bg-gray-900">Tous les pays</option>
                        <option value="France" {{ request('location_osm_address_country') === 'France' ? 'selected' : '' }} class="bg-gray-900">🇫🇷 France</option>
                        <option value="Germany" {{ request('location_osm_address_country') === 'Germany' ? 'selected' : '' }} class="bg-gray-900">🇩🇪 Allemagne</option>
                        <option value="Spain" {{ request('location_osm_address_country') === 'Spain' ? 'selected' : '' }} class="bg-gray-900">🇪🇸 Espagne</option>
                        <option value="Italy" {{ request('location_osm_address_country') === 'Italy' ? 'selected' : '' }} class="bg-gray-900">🇮🇹 Italie</option>
                        <option value="Belgium" {{ request('location_osm_address_country') === 'Belgium' ? 'selected' : '' }} class="bg-gray-900">🇧🇪 Belgique</option>
                        <option value="Switzerland" {{ request('location_osm_address_country') === 'Switzerland' ? 'selected' : '' }} class="bg-gray-900">🇨🇭 Suisse</option>
                        <option value="Austria" {{ request('location_osm_address_country') === 'Austria' ? 'selected' : '' }} class="bg-gray-900">🇦🇹 Autriche</option>
                    </select>
                </div>

                <!-- Prix minimum -->
                <div class="space-y-2">
                    <label class="flex items-center gap-2 text-white font-semibold text-sm">
                        <span class="text-xl">💰</span>
                        <span>Prix min (€)</span>
                    </label>
                    <input 
                        type="number" 
                        name="price_min" 
                        step="0.01"
                        placeholder="Ex: 1.50"
                        class="w-full px-4 py-3.5 rounded-xl bg-white/10 border-2 border-white/20 text-white placeholder-white/40 focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400 transition-all"
                        value="{{ request('price_min') }}"
                    >
                </div>

                <!-- Prix maximum -->
                <div class="space-y-2">
                    <label class="flex items-center gap-2 text-white font-semibold text-sm">
                        <span class="text-xl">💰</span>
                        <span>Prix max (€)</span>
                    </label>
                    <input 
                        type="number" 
                        name="price_max" 
                        step="0.01"
                        placeholder="Ex: 10.00"
                        class="w-full px-4 py-3.5 rounded-xl bg-white/10 border-2 border-white/20 text-white placeholder-white/40 focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400 transition-all"
                        value="{{ request('price_max') }}"
                    >
                </div>
            </div>

            <!-- Filtres de date -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 pt-4 border-t border-white/10">
                <div class="space-y-2">
                    <label class="flex items-center gap-2 text-white font-semibold text-sm">
                        <span class="text-xl">📅</span>
                        <span>Date de début</span>
                    </label>
                    <input 
                        type="date" 
                        name="date_from" 
                        class="w-full px-4 py-3.5 rounded-xl bg-white/10 border-2 border-white/20 text-white focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400 transition-all"
                        value="{{ request('date_from') }}"
                    >
                </div>

                <div class="space-y-2">
                    <label class="flex items-center gap-2 text-white font-semibold text-sm">
                        <span class="text-xl">📅</span>
                        <span>Date de fin</span>
                    </label>
                    <input 
                        type="date" 
                        name="date_to" 
                        class="w-full px-4 py-3.5 rounded-xl bg-white/10 border-2 border-white/20 text-white focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400 transition-all"
                        value="{{ request('date_to') }}"
                    >
                </div>
            </div>

            <!-- Boutons d'action améliorés -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center pt-6">
                <button 
                    type="submit" 
                    class="group relative bg-gradient-to-r from-yellow-500 via-orange-500 to-orange-600 hover:from-yellow-600 hover:via-orange-600 hover:to-orange-700 text-white px-10 py-4 rounded-xl font-bold transition-all transform hover:scale-105 shadow-2xl hover:shadow-yellow-500/50 flex items-center justify-center gap-3 text-lg"
                >
                    <span class="text-2xl group-hover:scale-110 transition-transform">🔍</span>
                    <span>Rechercher les prix</span>
                </button>
                
                <a 
                    href="{{ route('prices.browse') }}" 
                    class="group bg-gradient-to-r from-gray-600 to-gray-700 hover:from-gray-700 hover:to-gray-800 text-white px-8 py-4 rounded-xl font-bold transition-all transform hover:scale-105 shadow-xl flex items-center justify-center gap-3 text-lg"
                >
                    <span class="text-2xl group-hover:rotate-180 transition-transform duration-300">🔄</span>
                    <span>Réinitialiser</span>
                </a>
                
                <a 
                    href="{{ route('prices.coverage') }}" 
                    class="group bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white px-8 py-4 rounded-xl font-semibold transition-all transform hover:scale-105 shadow-xl hover:shadow-blue-500/50 flex items-center justify-center gap-3 text-lg"
                >
                    <span class="text-2xl group-hover:scale-110 transition-transform">🌍</span>
                    <span>Couverture géographique</span>
                </a>
            </div>
        </form>
    </div>

    <!-- Exemples de recherche améliorés -->
    <div class="bg-gradient-to-br from-white/10 to-white/5 backdrop-blur-xl rounded-3xl p-6 md:p-10 border border-white/10 shadow-2xl">
        <div class="flex items-center gap-3 mb-8">
            <span class="text-4xl">💡</span>
            <h2 class="text-3xl font-bold text-white">Exemples de recherche</h2>
        </div>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
            <a href="{{ route('prices.search', ['product_name' => 'coca cola']) }}" 
               class="group relative overflow-hidden bg-gradient-to-br from-red-500/25 to-red-600/15 hover:from-red-500/40 hover:to-red-600/30 border-2 border-red-500/40 hover:border-red-400 rounded-2xl p-6 transition-all duration-300 transform hover:scale-105 hover:shadow-2xl hover:shadow-red-500/30">
                <div class="flex items-center gap-3 mb-3">
                    <span class="text-4xl group-hover:scale-110 transition-transform">🥤</span>
                    <div class="text-red-300 font-bold text-lg">Coca Cola</div>
                </div>
                <div class="text-white/70 text-sm">Voir tous les prix du Coca Cola</div>
                <div class="absolute top-0 right-0 w-20 h-20 bg-red-400/10 rounded-full blur-2xl group-hover:bg-red-400/20 transition-all"></div>
            </a>

            <a href="{{ route('prices.search', ['location_osm_name' => 'carrefour']) }}" 
               class="group relative overflow-hidden bg-gradient-to-br from-blue-500/25 to-blue-600/15 hover:from-blue-500/40 hover:to-blue-600/30 border-2 border-blue-500/40 hover:border-blue-400 rounded-2xl p-6 transition-all duration-300 transform hover:scale-105 hover:shadow-2xl hover:shadow-blue-500/30">
                <div class="flex items-center gap-3 mb-3">
                    <span class="text-4xl group-hover:scale-110 transition-transform">🏪</span>
                    <div class="text-blue-300 font-bold text-lg">Carrefour</div>
                </div>
                <div class="text-white/70 text-sm">Prix dans les magasins Carrefour</div>
                <div class="absolute top-0 right-0 w-20 h-20 bg-blue-400/10 rounded-full blur-2xl group-hover:bg-blue-400/20 transition-all"></div>
            </a>

            <a href="{{ route('prices.search', ['location_osm_address_city' => 'paris']) }}" 
               class="group relative overflow-hidden bg-gradient-to-br from-green-500/25 to-green-600/15 hover:from-green-500/40 hover:to-green-600/30 border-2 border-green-500/40 hover:border-green-400 rounded-2xl p-6 transition-all duration-300 transform hover:scale-105 hover:shadow-2xl hover:shadow-green-500/30">
                <div class="flex items-center gap-3 mb-3">
                    <span class="text-4xl group-hover:scale-110 transition-transform">🌍</span>
                    <div class="text-green-300 font-bold text-lg">Paris</div>
                </div>
                <div class="text-white/70 text-sm">Prix des produits à Paris</div>
                <div class="absolute top-0 right-0 w-20 h-20 bg-green-400/10 rounded-full blur-2xl group-hover:bg-green-400/20 transition-all"></div>
            </a>

            <a href="{{ route('prices.search', ['price_max' => '2']) }}" 
               class="group relative overflow-hidden bg-gradient-to-br from-yellow-500/25 to-yellow-600/15 hover:from-yellow-500/40 hover:to-yellow-600/30 border-2 border-yellow-500/40 hover:border-yellow-400 rounded-2xl p-6 transition-all duration-300 transform hover:scale-105 hover:shadow-2xl hover:shadow-yellow-500/30">
                <div class="flex items-center gap-3 mb-3">
                    <span class="text-4xl group-hover:scale-110 transition-transform">💰</span>
                    <div class="text-yellow-300 font-bold text-lg">Moins de 2€</div>
                </div>
                <div class="text-white/70 text-sm">Produits à petit prix</div>
                <div class="absolute top-0 right-0 w-20 h-20 bg-yellow-400/10 rounded-full blur-2xl group-hover:bg-yellow-400/20 transition-all"></div>
            </a>

            <a href="{{ route('prices.search', ['product_name' => 'pain']) }}" 
               class="group relative overflow-hidden bg-gradient-to-br from-orange-500/25 to-orange-600/15 hover:from-orange-500/40 hover:to-orange-600/30 border-2 border-orange-500/40 hover:border-orange-400 rounded-2xl p-6 transition-all duration-300 transform hover:scale-105 hover:shadow-2xl hover:shadow-orange-500/30">
                <div class="flex items-center gap-3 mb-3">
                    <span class="text-4xl group-hover:scale-110 transition-transform">🍞</span>
                    <div class="text-orange-300 font-bold text-lg">Pain</div>
                </div>
                <div class="text-white/70 text-sm">Prix du pain en France</div>
                <div class="absolute top-0 right-0 w-20 h-20 bg-orange-400/10 rounded-full blur-2xl group-hover:bg-orange-400/20 transition-all"></div>
            </a>

            <a href="{{ route('prices.search', ['date_from' => now()->subDays(7)->format('Y-m-d')]) }}" 
               class="group relative overflow-hidden bg-gradient-to-br from-purple-500/25 to-purple-600/15 hover:from-purple-500/40 hover:to-purple-600/30 border-2 border-purple-500/40 hover:border-purple-400 rounded-2xl p-6 transition-all duration-300 transform hover:scale-105 hover:shadow-2xl hover:shadow-purple-500/30">
                <div class="flex items-center gap-3 mb-3">
                    <span class="text-4xl group-hover:scale-110 transition-transform">📅</span>
                    <div class="text-purple-300 font-bold text-lg">Cette semaine</div>
                </div>
                <div class="text-white/70 text-sm">Prix ajoutés récemment</div>
                <div class="absolute top-0 right-0 w-20 h-20 bg-purple-400/10 rounded-full blur-2xl group-hover:bg-purple-400/20 transition-all"></div>
            </a>
        </div>
    </div>

</div>
@endsection