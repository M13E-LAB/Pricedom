@extends('layouts.app')

@section('title', 'Debug Géolocalisation')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold text-white mb-6">🔍 Debug Géolocalisation</h1>
        
        <div class="bg-white/10 backdrop-blur-lg rounded-lg p-6 mb-6">
            <h2 class="text-xl font-bold text-white mb-4">Tests de recherche par ville</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <!-- Test Paris -->
                <a href="{{ route('prices.search', ['location_osm_address_city' => 'Paris']) }}" 
                   class="bg-blue-500/20 hover:bg-blue-500/30 border border-blue-500/30 rounded-lg p-4 transition-all">
                    <div class="text-blue-400 font-bold mb-2">🇫🇷 Paris</div>
                    <div class="text-white/80 text-sm">Test recherche Paris (auto-France)</div>
                </a>
                
                <!-- Test Paris + France explicite -->
                <a href="{{ route('prices.search', ['location_osm_address_city' => 'Paris', 'location_osm_address_country' => 'France']) }}" 
                   class="bg-green-500/20 hover:bg-green-500/30 border border-green-500/30 rounded-lg p-4 transition-all">
                    <div class="text-green-400 font-bold mb-2">🇫🇷 Paris, France</div>
                    <div class="text-white/80 text-sm">Paris + France explicite</div>
                </a>
                
                <!-- Test Lyon -->
                <a href="{{ route('prices.search', ['location_osm_address_city' => 'Lyon']) }}" 
                   class="bg-purple-500/20 hover:bg-purple-500/30 border border-purple-500/30 rounded-lg p-4 transition-all">
                    <div class="text-purple-400 font-bold mb-2">🇫🇷 Lyon</div>
                    <div class="text-white/80 text-sm">Test recherche Lyon</div>
                </a>
                
                <!-- Test Berlin -->
                <a href="{{ route('prices.search', ['location_osm_address_city' => 'Berlin', 'location_osm_address_country' => 'Germany']) }}" 
                   class="bg-red-500/20 hover:bg-red-500/30 border border-red-500/30 rounded-lg p-4 transition-all">
                    <div class="text-red-400 font-bold mb-2">🇩🇪 Berlin, Allemagne</div>
                    <div class="text-white/80 text-sm">Test recherche Allemagne</div>
                </a>
                
                <!-- Test France uniquement -->
                <a href="{{ route('prices.search', ['location_osm_address_country' => 'France']) }}" 
                   class="bg-yellow-500/20 hover:bg-yellow-500/30 border border-yellow-500/30 rounded-lg p-4 transition-all">
                    <div class="text-yellow-400 font-bold mb-2">🇫🇷 France</div>
                    <div class="text-white/80 text-sm">Tous les prix en France</div>
                </a>
                
                <!-- Test sans filtre géo -->
                <a href="{{ route('prices.search') }}" 
                   class="bg-gray-500/20 hover:bg-gray-500/30 border border-gray-500/30 rounded-lg p-4 transition-all">
                    <div class="text-gray-400 font-bold mb-2">🌍 Monde</div>
                    <div class="text-white/80 text-sm">Tous les prix (sans filtre)</div>
                </a>
            </div>
        </div>
        
        <div class="bg-white/10 backdrop-blur-lg rounded-lg p-6 mb-6">
            <h2 class="text-xl font-bold text-white mb-4">Tests avec produits spécifiques</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Coca Cola à Paris -->
                <a href="{{ route('prices.search', ['product_name' => 'Coca Cola', 'location_osm_address_city' => 'Paris']) }}" 
                   class="bg-red-500/20 hover:bg-red-500/30 border border-red-500/30 rounded-lg p-4 transition-all">
                    <div class="text-red-400 font-bold mb-2">🥤 Coca Cola à Paris</div>
                    <div class="text-white/80 text-sm">Recherche produit + géolocalisation</div>
                </a>
                
                <!-- Pain en France -->
                <a href="{{ route('prices.search', ['product_name' => 'Pain', 'location_osm_address_country' => 'France']) }}" 
                   class="bg-orange-500/20 hover:bg-orange-500/30 border border-orange-500/30 rounded-lg p-4 transition-all">
                    <div class="text-orange-400 font-bold mb-2">🍞 Pain en France</div>
                    <div class="text-white/80 text-sm">Recherche par catégorie + pays</div>
                </a>
            </div>
        </div>
        
        <div class="bg-white/10 backdrop-blur-lg rounded-lg p-6">
            <h2 class="text-xl font-bold text-white mb-4">Informations techniques</h2>
            
            <div class="text-white/80 space-y-2">
                <p><strong>API utilisée :</strong> <code class="bg-black/30 px-2 py-1 rounded">https://prices.openfoodfacts.org/api/v1/prices</code></p>
                <p><strong>Paramètres de géolocalisation (CORRIGÉS) :</strong></p>
                <ul class="list-disc list-inside ml-4 space-y-1">
                    <li><code class="bg-black/30 px-2 py-1 rounded">osm_address_city__like</code> : Recherche par ville ✅</li>
                    <li><code class="bg-black/30 px-2 py-1 rounded">osm_address_country__like</code> : Recherche par pays ✅</li>
                    <li><code class="bg-black/30 px-2 py-1 rounded">osm_name__like</code> : Recherche par nom de magasin ✅</li>
                </ul>
                <p><strong>Améliorations apportées :</strong></p>
                <ul class="list-disc list-inside ml-4 space-y-1">
                    <li>Détection automatique des villes françaises</li>
                    <li>Ajout du filtre pays pour plus de précision</li>
                    <li>Logging des paramètres de recherche</li>
                </ul>
            </div>
        </div>
        
        <div class="mt-6 text-center">
            <a href="{{ route('prices.browse') }}" 
               class="bg-gradient-to-r from-blue-500 to-purple-500 hover:from-blue-600 hover:to-purple-600 text-white px-8 py-3 rounded-lg font-bold transition-all transform hover:scale-105 shadow-lg">
                🔍 Retour à la recherche normale
            </a>
        </div>
    </div>
</div>
@endsection