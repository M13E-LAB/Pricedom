@extends('layouts.app')

@section('title', 'Gestion des contributions - Admin')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-900 py-8 px-4">
    <div class="max-w-7xl mx-auto">
        
        <!-- Header -->
        <div class="flex justify-between items-center mb-8">
            <div>
                <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center text-white/60 hover:text-white mb-4 transition-colors">
                    ← Retour au dashboard
                </a>
                <h1 class="text-4xl font-bold text-white">
                    <span class="text-transparent bg-gradient-to-r from-green-400 to-blue-500 bg-clip-text">
                        📋 Gestion des contributions
                    </span>
                </h1>
            </div>
            <button onclick="exportExcelFiltered()" class="bg-gradient-to-r from-green-500 to-emerald-600 text-white font-bold py-3 px-6 rounded-xl hover:scale-105 transition-transform">
                📊 Export Excel
            </button>
        </div>

        <!-- Filtres -->
        <div class="bg-black/70 border border-white/10 rounded-2xl p-6 mb-8">
            <h3 class="text-xl font-bold text-white mb-4">🔍 Filtres</h3>
            <form method="GET" action="{{ route('admin.contributions') }}" class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4">
                <!-- Statut de vérification -->
                <div>
                    <label class="block text-gray-400 text-sm font-medium mb-2">Statut</label>
                    <select name="verified" class="w-full bg-gray-800 border border-gray-600 rounded-xl px-4 py-2 text-white focus:border-blue-500">
                        <option value="all" {{ request('verified') == 'all' ? 'selected' : '' }}>Tous</option>
                        <option value="true" {{ request('verified') == 'true' ? 'selected' : '' }}>Vérifiés</option>
                        <option value="false" {{ request('verified') == 'false' ? 'selected' : '' }}>En attente</option>
                    </select>
                </div>

                <!-- Type de contribution -->
                <div>
                    <label class="block text-gray-400 text-sm font-medium mb-2">Type</label>
                    <select name="contribution_type" class="w-full bg-gray-800 border border-gray-600 rounded-xl px-4 py-2 text-white focus:border-blue-500">
                        <option value="all" {{ request('contribution_type') == 'all' ? 'selected' : '' }}>Tous</option>
                        <option value="scan" {{ request('contribution_type') == 'scan' ? 'selected' : '' }}>🤖 Scan IA</option>
                        <option value="manual" {{ request('contribution_type') == 'manual' ? 'selected' : '' }}>✏️ Manuel</option>
                    </select>
                </div>

                <!-- Magasin -->
                <div>
                    <label class="block text-gray-400 text-sm font-medium mb-2">Magasin</label>
                    <select name="store_name" class="w-full bg-gray-800 border border-gray-600 rounded-xl px-4 py-2 text-white focus:border-blue-500">
                        <option value="">Tous les magasins</option>
                        @foreach($stores as $store)
                        <option value="{{ $store }}" {{ request('store_name') == $store ? 'selected' : '' }}>{{ $store }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Catégorie -->
                <div>
                    <label class="block text-gray-400 text-sm font-medium mb-2">Catégorie</label>
                    <select name="category" class="w-full bg-gray-800 border border-gray-600 rounded-xl px-4 py-2 text-white focus:border-blue-500">
                        <option value="all" {{ request('category') == 'all' ? 'selected' : '' }}>Toutes</option>
                        @foreach($categories as $category)
                        <option value="{{ $category }}" {{ request('category') == $category ? 'selected' : '' }}>{{ $category ?: 'Non catégorisé' }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Date de début -->
                <div>
                    <label class="block text-gray-400 text-sm font-medium mb-2">Date début</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full bg-gray-800 border border-gray-600 rounded-xl px-4 py-2 text-white focus:border-blue-500">
                </div>

                <!-- Date de fin -->
                <div>
                    <label class="block text-gray-400 text-sm font-medium mb-2">Date fin</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full bg-gray-800 border border-gray-600 rounded-xl px-4 py-2 text-white focus:border-blue-500">
                </div>

                <!-- Boutons -->
                <div class="md:col-span-3 lg:col-span-6 flex gap-4 mt-4">
                    <button type="submit" class="bg-blue-500 text-white px-6 py-2 rounded-xl hover:bg-blue-600 transition-colors">
                        🔍 Filtrer
                    </button>
                    <a href="{{ route('admin.contributions') }}" class="bg-gray-600 text-white px-6 py-2 rounded-xl hover:bg-gray-700 transition-colors">
                        🔄 Réinitialiser
                    </a>
                </div>
            </form>
        </div>

        <!-- Résultats -->
        <div class="bg-black/70 border border-white/10 rounded-2xl p-6">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-bold text-white">
                    📊 {{ $contributions->total() }} contributions trouvées
                </h3>
                <div class="text-gray-400 text-sm">
                    Page {{ $contributions->currentPage() }} sur {{ $contributions->lastPage() }}
                </div>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-white/10">
                            <th class="text-left text-gray-400 font-medium py-3">ID</th>
                            <th class="text-left text-gray-400 font-medium py-3">Utilisateur</th>
                            <th class="text-left text-gray-400 font-medium py-3">Produit</th>
                            <th class="text-left text-gray-400 font-medium py-3">Prix</th>
                            <th class="text-left text-gray-400 font-medium py-3">Magasin</th>
                            <th class="text-left text-gray-400 font-medium py-3">Type</th>
                            <th class="text-left text-gray-400 font-medium py-3">Statut</th>
                            <th class="text-left text-gray-400 font-medium py-3">Date</th>
                            <th class="text-left text-gray-400 font-medium py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($contributions as $contribution)
                        <tr class="border-b border-white/5 hover:bg-white/5 transition-colors">
                            <td class="py-3 text-gray-400 text-sm">#{{ $contribution->id }}</td>
                            <td class="py-3">
                                <div>
                                    <div class="text-white font-medium">{{ $contribution->user->name ?? 'Utilisateur supprimé' }}</div>
                                    <div class="text-gray-400 text-sm">{{ $contribution->user->email ?? 'N/A' }}</div>
                                </div>
                            </td>
                            <td class="py-3">
                                <div class="text-white">{{ $contribution->product_name }}</div>
                                @if($contribution->category)
                                <div class="text-blue-400 text-sm">{{ $contribution->category }}</div>
                                @endif
                                @if($contribution->quantity && $contribution->quantity > 1)
                                <div class="text-gray-400 text-sm">Qté: {{ $contribution->quantity }}</div>
                                @endif
                            </td>
                            <td class="py-3">
                                <span class="text-green-400 font-bold">{{ number_format($contribution->price, 2) }} €</span>
                            </td>
                            <td class="py-3">
                                <div class="text-white">{{ $contribution->store_name ?? 'N/A' }}</div>
                                @if($contribution->location)
                                <div class="text-gray-400 text-sm">{{ $contribution->location }}</div>
                                @endif
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
                            <td class="py-3">
                                @if($contribution->verified)
                                <button onclick="toggleVerification({{ $contribution->id }}, false)" class="bg-red-500/20 text-red-400 px-3 py-1 rounded-lg hover:bg-red-500/30 transition-colors text-sm">
                                    ❌ Retirer
                                </button>
                                @else
                                <button onclick="toggleVerification({{ $contribution->id }}, true)" class="bg-green-500/20 text-green-400 px-3 py-1 rounded-lg hover:bg-green-500/30 transition-colors text-sm">
                                    ✅ Vérifier
                                </button>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="py-8 text-center text-gray-400">
                                Aucune contribution trouvée avec ces filtres
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($contributions->hasPages())
            <div class="mt-6 flex justify-center">
                <div class="flex space-x-2">
                    @if($contributions->onFirstPage())
                    <span class="px-4 py-2 bg-gray-800 text-gray-500 rounded-lg">Précédent</span>
                    @else
                    <a href="{{ $contributions->previousPageUrl() }}" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">Précédent</a>
                    @endif

                    @foreach($contributions->getUrlRange(1, $contributions->lastPage()) as $page => $url)
                    @if($page == $contributions->currentPage())
                    <span class="px-4 py-2 bg-blue-500 text-white rounded-lg">{{ $page }}</span>
                    @else
                    <a href="{{ $url }}" class="px-4 py-2 bg-gray-700 text-white rounded-lg hover:bg-gray-600 transition-colors">{{ $page }}</a>
                    @endif
                    @endforeach

                    @if($contributions->hasMorePages())
                    <a href="{{ $contributions->nextPageUrl() }}" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">Suivant</a>
                    @else
                    <span class="px-4 py-2 bg-gray-800 text-gray-500 rounded-lg">Suivant</span>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<script>
async function toggleVerification(contributionId, verified) {
    try {
        const response = await fetch(`/admin/contributions/${contributionId}/verify`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ verified: verified })
        });

        const result = await response.json();

        if (result.success) {
            // Recharger la page pour voir les changements
            window.location.reload();
        } else {
            alert('Erreur lors de la mise à jour');
        }
    } catch (error) {
        console.error('Erreur:', error);
        alert('Erreur de connexion');
    }
}

function exportExcelFiltered() {
    // Construire l'URL avec les paramètres de filtre actuels
    const urlParams = new URLSearchParams(window.location.search);
    urlParams.append('export', 'excel');
    
    window.location.href = '{{ route("admin.export.excel") }}?' + urlParams.toString();
}
</script>
@endsection 