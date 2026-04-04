@extends('layouts.app')

@section('title', 'Couverture Géographique')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold text-white mb-6">🌍 Couverture Géographique</h1>
        
        <div class="bg-white/10 backdrop-blur-lg rounded-lg p-6 mb-6">
            <h2 class="text-xl font-bold text-white mb-4">📊 Données disponibles par pays</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
                <!-- France -->
                <div class="bg-blue-500/20 border border-blue-500/30 rounded-lg p-4">
                    <div class="text-blue-400 font-bold mb-2">🇫🇷 France</div>
                    <div class="text-white/80 text-sm mb-2">Villes principales :</div>
                    <ul class="text-white/70 text-sm space-y-1">
                        <li>• Grenoble (données riches)</li>
                        <li>• Lyon (quelques données)</li>
                        <li>• Marseille (données limitées)</li>
                    </ul>
                    <div class="mt-3">
                        <a href="{{ route('prices.search', ['location_osm_address_country' => 'France']) }}" 
                           class="text-blue-300 hover:text-blue-200 text-sm underline">
                            Voir tous les prix en France →
                        </a>
                    </div>
                </div>
                
                <!-- Allemagne -->
                <div class="bg-red-500/20 border border-red-500/30 rounded-lg p-4">
                    <div class="text-red-400 font-bold mb-2">🇩🇪 Allemagne</div>
                    <div class="text-white/80 text-sm mb-2">Villes principales :</div>
                    <ul class="text-white/70 text-sm space-y-1">
                        <li>• Berlin (données moyennes)</li>
                        <li>• München (quelques données)</li>
                        <li>• Hamburg (données limitées)</li>
                    </ul>
                    <div class="mt-3">
                        <a href="{{ route('prices.search', ['location_osm_address_country' => 'Germany']) }}" 
                           class="text-red-300 hover:text-red-200 text-sm underline">
                            Voir tous les prix en Allemagne →
                        </a>
                    </div>
                </div>
                
                <!-- Canada -->
                <div class="bg-green-500/20 border border-green-500/30 rounded-lg p-4">
                    <div class="text-green-400 font-bold mb-2">🇨🇦 Canada</div>
                    <div class="text-white/80 text-sm mb-2">Villes principales :</div>
                    <ul class="text-white/70 text-sm space-y-1">
                        <li>• Montréal (données moyennes)</li>
                        <li>• Toronto (quelques données)</li>
                        <li>• Vancouver (données limitées)</li>
                    </ul>
                    <div class="mt-3">
                        <a href="{{ route('prices.search', ['location_osm_address_country' => 'Canada']) }}" 
                           class="text-green-300 hover:text-green-200 text-sm underline">
                            Voir tous les prix au Canada →
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="bg-yellow-500/20 border border-yellow-500/50 rounded-lg p-6 mb-6">
            <h2 class="text-xl font-bold text-yellow-300 mb-4">⚠️ Limitations importantes</h2>
            
            <div class="text-yellow-100 space-y-3">
                <p><strong>🏙️ Paris :</strong> Très peu de données disponibles dans Open Food Facts. Les recherches "Paris" peuvent retourner des résultats d'autres villes.</p>
                
                <p><strong>🌍 Couverture inégale :</strong> La base de données Open Food Facts dépend des contributions communautaires. Certaines villes ont plus de données que d'autres.</p>
                
                <p><strong>📅 Données récentes :</strong> Les prix peuvent dater de plusieurs mois. Vérifiez toujours la date des relevés.</p>
                
                <p><strong>🏪 Magasins :</strong> Tous les magasins ne sont pas représentés. La couverture varie selon les régions.</p>
            </div>
        </div>
        
        <div class="bg-white/10 backdrop-blur-lg rounded-lg p-6 mb-6">
            <h2 class="text-xl font-bold text-white mb-4">💡 Conseils de recherche</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h3 class="text-lg font-semibold text-green-400 mb-3">✅ Recherches efficaces</h3>
                    <ul class="text-white/80 space-y-2">
                        <li>• Rechercher par pays uniquement</li>
                        <li>• Utiliser des noms de magasins (Carrefour, Leclerc)</li>
                        <li>• Chercher des produits spécifiques avec code-barres</li>
                        <li>• Privilégier Grenoble pour la France</li>
                    </ul>
                </div>
                
                <div>
                    <h3 class="text-lg font-semibold text-red-400 mb-3">❌ Recherches moins efficaces</h3>
                    <ul class="text-white/80 space-y-2">
                        <li>• Rechercher "Paris" spécifiquement</li>
                        <li>• Chercher des villes très spécifiques</li>
                        <li>• Filtrer par prix très précis</li>
                        <li>• Recherches trop restrictives</li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="bg-white/10 backdrop-blur-lg rounded-lg p-6">
            <h2 class="text-xl font-bold text-white mb-4">🚀 Contribuer aux données</h2>
            
            <div class="text-white/80 space-y-3">
                <p>Open Food Facts est un projet collaboratif. Vous pouvez contribuer en :</p>
                
                <ul class="list-disc list-inside ml-4 space-y-2">
                    <li>Ajoutant des prix via l'application mobile Open Food Facts</li>
                    <li>Scannant des tickets de caisse</li>
                    <li>Photographiant des produits en magasin</li>
                    <li>Complétant les informations produits</li>
                </ul>
                
                <div class="mt-4">
                    <a href="https://world.openfoodfacts.org" 
                       target="_blank"
                       class="bg-gradient-to-r from-green-500 to-blue-500 hover:from-green-600 hover:to-blue-600 text-white px-6 py-3 rounded-lg font-bold transition-all transform hover:scale-105 shadow-lg inline-block">
                        🌍 Visiter Open Food Facts
                    </a>
                </div>
            </div>
        </div>
        
        <div class="mt-6 text-center">
            <a href="{{ route('prices.browse') }}" 
               class="bg-gradient-to-r from-blue-500 to-purple-500 hover:from-blue-600 hover:to-purple-600 text-white px-8 py-3 rounded-lg font-bold transition-all transform hover:scale-105 shadow-lg">
                🔍 Retour à la recherche
            </a>
        </div>
    </div>
</div>
@endsection