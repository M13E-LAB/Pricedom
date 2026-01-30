@extends('layouts.app')

@section('title', 'Résultats de recherche - Prix')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- En-tête avec statistiques -->
    <div class="mb-8">
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 mb-6">
            <div>
                <h1 class="text-3xl font-bold text-white mb-2">
                    🏷️ Résultats de recherche
                </h1>
                <p class="text-white/80">
                    {{ number_format($stats['count']) }} prix trouvés dans notre base de données
                </p>
            </div>
            
            <a href="{{ route('prices.browse') }}" 
               class="bg-gradient-to-r from-gray-600 to-gray-700 hover:from-gray-700 hover:to-gray-800 text-white px-6 py-3 rounded-lg font-bold transition-all transform hover:scale-105 shadow-lg">
                🔍 Nouvelle recherche
            </a>
        </div>

        <!-- Statistiques -->
        @if($stats['count'] > 0)
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-4 mb-8">
            <div class="bg-white/10 backdrop-blur-lg rounded-lg p-4 border border-white/20 text-center">
                <div class="text-2xl font-bold text-yellow-400">{{ number_format($stats['count']) }}</div>
                <div class="text-white/80 text-sm">Prix total</div>
            </div>
            
            <div class="bg-white/10 backdrop-blur-lg rounded-lg p-4 border border-white/20 text-center">
                <div class="text-2xl font-bold text-green-400">{{ number_format($stats['min'], 2) }}€</div>
                <div class="text-white/80 text-sm">Prix min</div>
            </div>
            
            <div class="bg-white/10 backdrop-blur-lg rounded-lg p-4 border border-white/20 text-center">
                <div class="text-2xl font-bold text-red-400">{{ number_format($stats['max'], 2) }}€</div>
                <div class="text-white/80 text-sm">Prix max</div>
            </div>
            
            <div class="bg-white/10 backdrop-blur-lg rounded-lg p-4 border border-white/20 text-center">
                <div class="text-2xl font-bold text-blue-400">{{ number_format($stats['avg'], 2) }}€</div>
                <div class="text-white/80 text-sm">Prix moyen</div>
            </div>
            
            <div class="bg-white/10 backdrop-blur-lg rounded-lg p-4 border border-white/20 text-center">
                <div class="text-2xl font-bold text-purple-400">{{ number_format($stats['stores_count']) }}</div>
                <div class="text-white/80 text-sm">Magasins</div>
            </div>
            
            <div class="bg-white/10 backdrop-blur-lg rounded-lg p-4 border border-white/20 text-center">
                <div class="text-2xl font-bold text-orange-400">{{ number_format($stats['countries_count']) }}</div>
                <div class="text-white/80 text-sm">Pays</div>
            </div>
            
            <div class="bg-white/10 backdrop-blur-lg rounded-lg p-4 border border-white/20 text-center">
                <div class="text-2xl font-bold text-cyan-400">{{ number_format($stats['recent_count']) }}</div>
                <div class="text-white/80 text-sm">Récents</div>
            </div>
        </div>
        @endif
    </div>

    <!-- Filtres actifs -->
    @if(count(array_filter($filters)) > 0)
    <div class="bg-white/5 backdrop-blur-lg rounded-lg p-4 mb-6 border border-white/10">
        <div class="flex flex-wrap items-center gap-2">
            <span class="text-white/80 font-semibold">🏷️ Filtres actifs:</span>
            
            @foreach($filters as $key => $value)
                @if($value)
                    @if($key === 'product_name')
                        <span class="bg-green-500/20 text-green-300 px-3 py-1 rounded-full text-sm border border-green-500/30">
                            ⚡ Recherche intelligente: {{ $value }}
                        </span>
                    @else
                        <span class="bg-yellow-500/20 text-yellow-300 px-3 py-1 rounded-full text-sm border border-yellow-500/30">
                            {{ ucfirst(str_replace('_', ' ', $key)) }}: {{ $value }}
                        </span>
                    @endif
                @endif
            @endforeach
        </div>
    </div>
    @endif

    <!-- Résultats -->
    @if(count($prices) > 0)
        <div class="space-y-4">
            @foreach($prices as $price)
            <div class="bg-white/10 backdrop-blur-lg rounded-lg p-6 border border-white/20 hover:bg-white/15 transition-all">
                <div class="flex flex-col lg:flex-row justify-between items-start gap-4">
                    <!-- Informations produit -->
                    <div class="flex-1">
                        <div class="flex items-start gap-4">
                            <!-- Image du produit (si disponible) -->
                            @if(isset($price['product_image']) && $price['product_image'])
                            <div class="flex-shrink-0">
                                <img src="{{ $price['product_image'] }}" 
                                     alt="{{ $price['product_name'] }}"
                                     class="w-16 h-16 object-cover rounded-lg border border-white/20">
                            </div>
                            @endif
                            
                            <div class="flex-1">
                                <h3 class="text-xl font-bold text-white mb-2">
                                    {{ $price['product_name'] }}
                                </h3>
                                
                                @if($price['product_code'])
                                <div class="text-white/60 text-sm mb-1">
                                    📊 Code: {{ $price['product_code'] }}
                                </div>
                                @endif
                                
                                @if($price['product_quantity'])
                                <div class="text-white/60 text-sm mb-3">
                                    📦 Quantité: {{ $price['product_quantity'] }}{{ $price['product_quantity_unit'] }}
                                </div>
                                @endif
                                
                                <!-- Localisation -->
                                <div class="flex flex-wrap items-center gap-4 text-sm">
                                    <div class="flex items-center gap-1 text-blue-300">
                                        🏪 {{ $price['location']['osm_name'] }}
                                    </div>
                                    
                                    @if($price['location']['osm_address_city'])
                                    <div class="flex items-center gap-1 text-green-300">
                                        🌍 {{ $price['location']['osm_address_city'] }}
                                    </div>
                                    @endif
                                    
                                    @if($price['location']['osm_address_country'])
                                    <div class="flex items-center gap-1 text-purple-300">
                                        🏳️ {{ $price['location']['osm_address_country'] }}
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Prix et date -->
                    <div class="text-right">
                        <div class="text-3xl font-bold text-yellow-400 mb-2">
                            {{ number_format($price['price'], 2) }}€
                        </div>
                        
                        @if($price['price_per'])
                        <div class="text-white/60 text-sm mb-2">
                            par {{ $price['price_per'] }}
                        </div>
                        @endif
                        
                        <div class="text-white/60 text-sm">
                            📅 {{ \Carbon\Carbon::parse($price['date'])->format('d/m/Y') }}
                        </div>
                        
                        @if($price['owner'])
                        <div class="text-white/40 text-xs mt-1">
                            👤 {{ $price['owner'] }}
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Pagination -->
        @if($pages > 1)
        <div class="mt-8 flex justify-center">
            <div class="flex items-center space-x-2">
                @if($page > 1)
                    <a href="{{ route('prices.search', array_merge($filters, ['page' => $page - 1])) }}" 
                       class="bg-white/10 hover:bg-white/20 text-white px-4 py-2 rounded-lg transition-colors">
                        ← Précédent
                    </a>
                @endif
                
                <span class="text-white/80 px-4 py-2">
                    Page {{ $page }} sur {{ $pages }}
                </span>
                
                @if($page < $pages)
                    <a href="{{ route('prices.search', array_merge($filters, ['page' => $page + 1])) }}" 
                       class="bg-white/10 hover:bg-white/20 text-white px-4 py-2 rounded-lg transition-colors">
                        Suivant →
                    </a>
                @endif
            </div>
        </div>
        @endif

    @else
        <!-- Aucun résultat -->
        <div class="text-center py-12">
            <div class="bg-white/10 backdrop-blur-lg rounded-2xl p-8 border border-white/20 max-w-md mx-auto">
                <div class="text-6xl mb-4">🔍</div>
                <h3 class="text-2xl font-bold text-white mb-4">Aucun prix trouvé</h3>
                <p class="text-white/80 mb-6">
                    Aucun prix ne correspond à vos critères de recherche. Essayez de modifier vos filtres.
                </p>
                <a href="{{ route('prices.browse') }}" 
                   class="bg-gradient-to-r from-yellow-500 to-orange-500 hover:from-yellow-600 hover:to-orange-600 text-white px-6 py-3 rounded-lg font-bold transition-all transform hover:scale-105 shadow-lg inline-block">
                    🔍 Nouvelle recherche
                </a>
            </div>
        </div>
    @endif
</div>
@endsection