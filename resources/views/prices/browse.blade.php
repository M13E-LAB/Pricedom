@extends('layouts.app')

@section('title', 'Explorer les Prix - Open Prices')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- En-tête -->
    <div class="text-center mb-12">
        <h1 class="text-4xl font-bold text-white mb-4">
            🏷️ Explorer les Prix
        </h1>
        <p class="text-xl text-white/80 max-w-2xl mx-auto">
            Consultez notre base de données avec des millions de prix de produits alimentaires du monde entier
        </p>
    </div>

    <!-- Formulaire de recherche -->
    <div class="bg-white/10 backdrop-blur-lg rounded-2xl p-8 mb-8 border border-white/20">
        <form action="{{ route('prices.search') }}" method="GET" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Recherche par produit -->
                <div>
                    <label class="block text-white font-semibold mb-2">
                        🔍 Nom du produit
                        <span class="text-xs text-yellow-300 font-normal ml-2">⚡ Recherche intelligente</span>
                    </label>
                    <input 
                        type="text" 
                        name="product_name" 
                        placeholder="Ex: Coca Cola, Pain de mie, Nutella..."
                        class="w-full px-4 py-3 rounded-lg bg-white/10 border border-white/30 text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-transparent"
                        value="{{ request('product_name') }}"
                    >
                    <p class="text-xs text-white/60 mt-1">
                        💡 Notre système trouve automatiquement les produits correspondants
                    </p>
                </div>

                <!-- Code-barres -->
                <div>
                    <label class="block text-white font-semibold mb-2">
                        📊 Code-barres
                    </label>
                    <input 
                        type="text" 
                        name="product_code" 
                        placeholder="Ex: 3017620422003"
                        class="w-full px-4 py-3 rounded-lg bg-white/10 border border-white/30 text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-transparent"
                        value="{{ request('product_code') }}"
                    >
                </div>

                <!-- Magasin -->
                <div>
                    <label class="block text-white font-semibold mb-2">
                        🏪 Magasin
                    </label>
                    <input 
                        type="text" 
                        name="location_osm_name" 
                        placeholder="Ex: Carrefour, Leclerc..."
                        class="w-full px-4 py-3 rounded-lg bg-white/10 border border-white/30 text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-transparent"
                        value="{{ request('location_osm_name') }}"
                    >
                </div>

                <!-- Ville -->
                <div>
                    <label class="block text-white font-semibold mb-2">
                        🌍 Ville
                    </label>
                    <input 
                        type="text" 
                        name="location_osm_address_city" 
                        placeholder="Ex: Paris, Lyon, Marseille..."
                        class="w-full px-4 py-3 rounded-lg bg-white/10 border border-white/30 text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-transparent"
                        value="{{ request('location_osm_address_city') }}"
                    >
                </div>

                <!-- Pays -->
                <div>
                    <label class="block text-white font-semibold mb-2">
                        🏳️ Pays
                    </label>
                    <select 
                        name="location_osm_address_country" 
                        class="w-full px-4 py-3 rounded-lg bg-white/10 border border-white/30 text-white focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-transparent"
                    >
                        <option value="">Tous les pays</option>
                        <option value="France" {{ request('location_osm_address_country') === 'France' ? 'selected' : '' }}>🇫🇷 France</option>
                        <option value="Germany" {{ request('location_osm_address_country') === 'Germany' ? 'selected' : '' }}>🇩🇪 Allemagne</option>
                        <option value="Spain" {{ request('location_osm_address_country') === 'Spain' ? 'selected' : '' }}>🇪🇸 Espagne</option>
                        <option value="Italy" {{ request('location_osm_address_country') === 'Italy' ? 'selected' : '' }}>🇮🇹 Italie</option>
                        <option value="Belgium" {{ request('location_osm_address_country') === 'Belgium' ? 'selected' : '' }}>🇧🇪 Belgique</option>
                        <option value="Switzerland" {{ request('location_osm_address_country') === 'Switzerland' ? 'selected' : '' }}>🇨🇭 Suisse</option>
                        <option value="Austria" {{ request('location_osm_address_country') === 'Austria' ? 'selected' : '' }}>🇦🇹 Autriche</option>
                    </select>
                </div>

                <!-- Prix minimum -->
                <div>
                    <label class="block text-white font-semibold mb-2">
                        💰 Prix min (€)
                    </label>
                    <input 
                        type="number" 
                        name="price_min" 
                        step="0.01"
                        placeholder="Ex: 1.50"
                        class="w-full px-4 py-3 rounded-lg bg-white/10 border border-white/30 text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-transparent"
                        value="{{ request('price_min') }}"
                    >
                </div>

                <!-- Prix maximum -->
                <div>
                    <label class="block text-white font-semibold mb-2">
                        💰 Prix max (€)
                    </label>
                    <input 
                        type="number" 
                        name="price_max" 
                        step="0.01"
                        placeholder="Ex: 10.00"
                        class="w-full px-4 py-3 rounded-lg bg-white/10 border border-white/30 text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-transparent"
                        value="{{ request('price_max') }}"
                    >
                </div>
            </div>

            <!-- Filtres de date -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-white font-semibold mb-2">
                        📅 Date de début
                    </label>
                    <input 
                        type="date" 
                        name="date_from" 
                        class="w-full px-4 py-3 rounded-lg bg-white/10 border border-white/30 text-white focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-transparent"
                        value="{{ request('date_from') }}"
                    >
                </div>

                <div>
                    <label class="block text-white font-semibold mb-2">
                        📅 Date de fin
                    </label>
                    <input 
                        type="date" 
                        name="date_to" 
                        class="w-full px-4 py-3 rounded-lg bg-white/10 border border-white/30 text-white focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-transparent"
                        value="{{ request('date_to') }}"
                    >
                </div>
            </div>

            <!-- Boutons -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <button 
                    type="submit" 
                    class="bg-gradient-to-r from-yellow-500 to-orange-500 hover:from-yellow-600 hover:to-orange-600 text-white px-8 py-3 rounded-lg font-bold transition-all transform hover:scale-105 shadow-lg"
                >
                    🔍 Rechercher les prix
                </button>
                
                <a 
                    href="{{ route('prices.browse') }}" 
                    class="bg-gradient-to-r from-gray-600 to-gray-700 hover:from-gray-700 hover:to-gray-800 text-white px-8 py-3 rounded-lg font-bold transition-all transform hover:scale-105 shadow-lg text-center"
                >
                    🔄 Réinitialiser
                </a>
                
                <a 
                    href="{{ route('prices.coverage') }}" 
                    class="bg-gradient-to-r from-blue-500 to-purple-500 hover:from-blue-600 hover:to-purple-600 text-white px-6 py-3 rounded-lg font-medium transition-all transform hover:scale-105 shadow-lg text-center"
                >
                    🌍 Couverture géographique
                </a>
            </div>
        </form>
    </div>

    <!-- Exemples de recherche -->
    <div class="bg-white/5 backdrop-blur-lg rounded-2xl p-8 border border-white/10">
        <h2 class="text-2xl font-bold text-white mb-6">💡 Exemples de recherche</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <a href="{{ route('prices.search', ['product_name' => 'coca cola']) }}" 
               class="bg-gradient-to-r from-red-500/20 to-red-600/20 hover:from-red-500/30 hover:to-red-600/30 border border-red-500/30 rounded-lg p-4 transition-all transform hover:scale-105">
                <div class="text-red-400 font-bold mb-2">🥤 Coca Cola</div>
                <div class="text-white/80 text-sm">Voir tous les prix du Coca Cola</div>
            </a>

            <a href="{{ route('prices.search', ['location_osm_name' => 'carrefour']) }}" 
               class="bg-gradient-to-r from-blue-500/20 to-blue-600/20 hover:from-blue-500/30 hover:to-blue-600/30 border border-blue-500/30 rounded-lg p-4 transition-all transform hover:scale-105">
                <div class="text-blue-400 font-bold mb-2">🏪 Carrefour</div>
                <div class="text-white/80 text-sm">Prix dans les magasins Carrefour</div>
            </a>

            <a href="{{ route('prices.search', ['location_osm_address_city' => 'paris']) }}" 
               class="bg-gradient-to-r from-green-500/20 to-green-600/20 hover:from-green-500/30 hover:to-green-600/30 border border-green-500/30 rounded-lg p-4 transition-all transform hover:scale-105">
                <div class="text-green-400 font-bold mb-2">🌍 Paris</div>
                <div class="text-white/80 text-sm">Prix des produits à Paris</div>
            </a>

            <a href="{{ route('prices.search', ['price_max' => '2']) }}" 
               class="bg-gradient-to-r from-yellow-500/20 to-yellow-600/20 hover:from-yellow-500/30 hover:to-yellow-600/30 border border-yellow-500/30 rounded-lg p-4 transition-all transform hover:scale-105">
                <div class="text-yellow-400 font-bold mb-2">💰 Moins de 2€</div>
                <div class="text-white/80 text-sm">Produits à petit prix</div>
            </a>

            <a href="{{ route('prices.search', ['product_name' => 'pain']) }}" 
               class="bg-gradient-to-r from-orange-500/20 to-orange-600/20 hover:from-orange-500/30 hover:to-orange-600/30 border border-orange-500/30 rounded-lg p-4 transition-all transform hover:scale-105">
                <div class="text-orange-400 font-bold mb-2">🍞 Pain</div>
                <div class="text-white/80 text-sm">Prix du pain en France</div>
            </a>

            <a href="{{ route('prices.search', ['date_from' => now()->subDays(7)->format('Y-m-d')]) }}" 
               class="bg-gradient-to-r from-purple-500/20 to-purple-600/20 hover:from-purple-500/30 hover:to-purple-600/30 border border-purple-500/30 rounded-lg p-4 transition-all transform hover:scale-105">
                <div class="text-purple-400 font-bold mb-2">📅 Cette semaine</div>
                <div class="text-white/80 text-sm">Prix ajoutés récemment</div>
            </a>
        </div>
    </div>

</div>
@endsection