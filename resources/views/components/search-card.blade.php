@props(['product' => null, 'price' => null, 'store' => null])

<div class="card card-hover group cursor-pointer">
    <div class="flex items-start space-x-4">
        <!-- Product Image -->
        <div class="w-16 h-16 bg-gradient-to-br from-orange-400 to-pink-500 rounded-lg flex items-center justify-center flex-shrink-0">
            @if($product && isset($product['image_url']))
                <img src="{{ $product['image_url'] }}" alt="{{ $product['name'] ?? 'Produit' }}" class="w-full h-full object-cover rounded-lg">
            @else
                <i data-lucide="package" class="icon text-white"></i>
            @endif
        </div>
        
        <!-- Product Info -->
        <div class="flex-1 min-w-0">
            <h3 class="font-semibold text-white truncate group-hover:text-orange-400 transition-colors">
                {{ $product['name'] ?? 'Nom du produit' }}
            </h3>
            <p class="text-sm text-white/60 truncate">
                {{ $product['brand'] ?? 'Marque inconnue' }}
            </p>
            
            @if($product && isset($product['nutriscore_grade']))
                <div class="flex items-center space-x-2 mt-2">
                    <span class="text-xs px-2 py-1 rounded-full font-medium
                        @if($product['nutriscore_grade'] === 'a') bg-green-500/20 text-green-400
                        @elseif($product['nutriscore_grade'] === 'b') bg-lime-500/20 text-lime-400
                        @elseif($product['nutriscore_grade'] === 'c') bg-yellow-500/20 text-yellow-400
                        @elseif($product['nutriscore_grade'] === 'd') bg-orange-500/20 text-orange-400
                        @else bg-red-500/20 text-red-400
                        @endif">
                        Nutri-Score {{ strtoupper($product['nutriscore_grade']) }}
                    </span>
                </div>
            @endif
        </div>
        
        <!-- Price Info -->
        <div class="text-right flex-shrink-0">
            @if($price)
                <div class="text-lg font-bold text-green-400">
                    {{ number_format($price['price'], 2) }}€
                </div>
                <div class="text-xs text-white/60">
                    {{ $store ?? 'Magasin' }}
                </div>
                <div class="text-xs text-white/40">
                    {{ isset($price['date']) ? \Carbon\Carbon::parse($price['date'])->diffForHumans() : 'Récent' }}
                </div>
            @else
                <div class="text-sm text-white/60">
                    Prix non disponible
                </div>
            @endif
        </div>
    </div>
    
    <!-- Action Buttons (appear on hover) -->
    <div class="mt-4 flex space-x-2 opacity-0 group-hover:opacity-100 transition-opacity">
        <button class="btn-secondary flex-1 py-2 text-sm">
            <i data-lucide="eye" class="icon mr-2"></i>
            Voir détails
        </button>
        <button class="btn-primary py-2 px-4 text-sm">
            <i data-lucide="heart" class="icon"></i>
        </button>
    </div>
</div>