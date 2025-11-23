@extends('layouts.app')

@section('title', 'Admin Dashboard - Zyma')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-900 py-8 px-4">
    <div class="max-w-7xl mx-auto">
        
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-white mb-4">
                <span class="text-transparent bg-gradient-to-r from-green-400 to-blue-500 bg-clip-text">
                    🔧 Admin Dashboard
                </span>
            </h1>
            <p class="text-xl text-gray-300">Gestion des contributions et analyse des données</p>
        </div>

        <!-- Statistiques principales -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Total contributions -->
            <div class="bg-black/70 border border-white/10 rounded-2xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-400 text-sm font-medium">Total contributions</p>
                        <p class="text-3xl font-bold text-white">{{ number_format($stats['total_contributions']) }}</p>
                    </div>
                    <div class="p-3 bg-green-500/20 rounded-xl">
                        <span class="text-green-400 text-2xl">📊</span>
                    </div>
                </div>
            </div>

            <!-- En attente -->
            <div class="bg-black/70 border border-white/10 rounded-2xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-400 text-sm font-medium">En attente</p>
                        <p class="text-3xl font-bold text-yellow-400">{{ number_format($stats['pending_contributions']) }}</p>
                    </div>
                    <div class="p-3 bg-yellow-500/20 rounded-xl">
                        <span class="text-yellow-400 text-2xl">⏳</span>
                    </div>
                </div>
            </div>

            <!-- Vérifiées -->
            <div class="bg-black/70 border border-white/10 rounded-2xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-400 text-sm font-medium">Vérifiées</p>
                        <p class="text-3xl font-bold text-green-400">{{ number_format($stats['verified_contributions']) }}</p>
                    </div>
                    <div class="p-3 bg-green-500/20 rounded-xl">
                        <span class="text-green-400 text-2xl">✅</span>
                    </div>
                </div>
            </div>

            <!-- Total utilisateurs -->
            <div class="bg-black/70 border border-white/10 rounded-2xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-400 text-sm font-medium">Utilisateurs</p>
                        <p class="text-3xl font-bold text-blue-400">{{ number_format($stats['total_users']) }}</p>
                    </div>
                    <div class="p-3 bg-blue-500/20 rounded-xl">
                        <span class="text-blue-400 text-2xl">👥</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistiques secondaires -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <!-- Scan vs Manuel -->
            <div class="bg-black/70 border border-white/10 rounded-2xl p-6">
                <h3 class="text-xl font-bold text-white mb-4">🤖 Types de contribution</h3>
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-400">Scan IA</span>
                        <span class="text-green-400 font-bold">{{ number_format($stats['scan_contributions']) }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-400">Manuel</span>
                        <span class="text-blue-400 font-bold">{{ number_format($stats['manual_contributions']) }}</span>
                    </div>
                </div>
            </div>

            <!-- Revenus total -->
            <div class="bg-black/70 border border-white/10 rounded-2xl p-6">
                <h3 class="text-xl font-bold text-white mb-4">💰 Revenus totaux</h3>
                <p class="text-3xl font-bold text-green-400">{{ number_format($stats['total_revenue'], 2) }} €</p>
            </div>

            <!-- Actions rapides -->
            <div class="bg-black/70 border border-white/10 rounded-2xl p-6">
                <h3 class="text-xl font-bold text-white mb-4">⚡ Actions rapides</h3>
                <div class="space-y-2">
                    <a href="{{ route('admin.contributions') }}" class="block bg-blue-500/20 text-blue-400 px-4 py-2 rounded-xl hover:bg-blue-500/30 transition-colors text-center">
                        Gérer contributions
                    </a>
                    <button onclick="exportExcel()" class="w-full bg-green-500/20 text-green-400 px-4 py-2 rounded-xl hover:bg-green-500/30 transition-colors">
                        Export Excel
                    </button>
                </div>
            </div>
        </div>

        <!-- Statistiques par magasin -->
        @if($storeStats->count() > 0)
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <div class="bg-black/70 border border-white/10 rounded-2xl p-6">
                <h3 class="text-xl font-bold text-white mb-4">🏪 Top magasins</h3>
                <div class="space-y-3">
                    @foreach($storeStats as $store)
                    <div class="flex justify-between items-center">
                        <div>
                            <span class="text-white font-medium">{{ $store->store_name }}</span>
                            <span class="text-gray-400 text-sm block">{{ $store->total_contributions }} contributions</span>
                        </div>
                        <span class="text-green-400 font-bold">{{ number_format($store->total_amount, 2) }} €</span>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Statistiques par catégorie -->
            <div class="bg-black/70 border border-white/10 rounded-2xl p-6">
                <h3 class="text-xl font-bold text-white mb-4">📦 Catégories populaires</h3>
                <div class="space-y-3">
                    @foreach($categoryStats as $category)
                    <div class="flex justify-between items-center">
                        <div>
                            <span class="text-white font-medium">{{ $category->category ?: 'Non catégorisé' }}</span>
                            <span class="text-gray-400 text-sm block">{{ $category->total_contributions }} produits</span>
                        </div>
                        <span class="text-blue-400 font-bold">{{ number_format($category->avg_price, 2) }} € moy.</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <!-- Contributions récentes -->
        <div class="bg-black/70 border border-white/10 rounded-2xl p-6">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-bold text-white">📋 Contributions récentes</h3>
                <a href="{{ route('admin.contributions') }}" class="text-blue-400 hover:text-blue-300 font-medium">
                    Voir toutes →
                </a>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-white/10">
                            <th class="text-left text-gray-400 font-medium py-3">Utilisateur</th>
                            <th class="text-left text-gray-400 font-medium py-3">Produit</th>
                            <th class="text-left text-gray-400 font-medium py-3">Prix</th>
                            <th class="text-left text-gray-400 font-medium py-3">Type</th>
                            <th class="text-left text-gray-400 font-medium py-3">Statut</th>
                            <th class="text-left text-gray-400 font-medium py-3">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentContributions as $contribution)
                        <tr class="border-b border-white/5">
                            <td class="py-3">
                                <div>
                                    <div class="text-white font-medium">{{ $contribution->user->name ?? 'Utilisateur supprimé' }}</div>
                                    <div class="text-gray-400 text-sm">{{ $contribution->user->email ?? 'N/A' }}</div>
                                </div>
                            </td>
                            <td class="py-3">
                                <div class="text-white">{{ $contribution->product_name }}</div>
                                @if($contribution->store_name)
                                <div class="text-gray-400 text-sm">{{ $contribution->store_name }}</div>
                                @endif
                            </td>
                            <td class="py-3">
                                <span class="text-green-400 font-bold">{{ number_format($contribution->price, 2) }} €</span>
                            </td>
                            <td class="py-3">
                                @if($contribution->contribution_type === 'scan')
                                <span class="bg-green-500/20 text-green-400 px-2 py-1 rounded text-sm">🤖 Scan</span>
                                @else
                                <span class="bg-blue-500/20 text-blue-400 px-2 py-1 rounded text-sm">✏️ Manuel</span>
                                @endif
                            </td>
                            <td class="py-3">
                                @if($contribution->verified)
                                <span class="bg-green-500/20 text-green-400 px-2 py-1 rounded text-sm">✅ Vérifié</span>
                                @else
                                <span class="bg-yellow-500/20 text-yellow-400 px-2 py-1 rounded text-sm">⏳ En attente</span>
                                @endif
                            </td>
                            <td class="py-3 text-gray-400 text-sm">
                                {{ $contribution->created_at->format('d/m/Y H:i') }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function exportExcel() {
    window.location.href = '{{ route("admin.export.excel") }}';
}
</script>
@endsection 