<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>📊 Dashboard Prix - Zyma</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-900">
    {{-- Navigation --}}
    <nav class="bg-black/20 backdrop-blur-lg border-b border-white/10 sticky top-0 z-50">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center h-16">
                <a href="{{ route('products.search') }}" class="text-2xl font-bold bg-gradient-to-r from-orange-400 to-pink-400 bg-clip-text text-transparent">
                    ZYMA
                </a>
                
                <div class="flex items-center space-x-4">
                    <a href="{{ route('social.index') }}" class="text-white/80 hover:text-orange-400 transition-colors">🍽️ Communauté</a>
                    <a href="{{ route('contribute.index') }}" class="text-white/80 hover:text-orange-400 transition-colors">🎁 Contribuer</a>
                    <a href="{{ route('prices.dashboard') }}" class="text-orange-400 font-bold">📊 Dashboard</a>
                    <div class="text-white/90">👋 {{ Auth::user()->name }}</div>
                </div>
            </div>
        </div>
    </nav>

    <main class="container mx-auto px-4 py-8">
        {{-- Header --}}
        <div class="mb-8 flex justify-between items-center">
            <div>
                <h1 class="text-4xl font-bold text-white mb-2">📊 Dashboard des Prix</h1>
                <p class="text-white/70">Statistiques et analyse des contributions de prix</p>
            </div>
            <a href="{{ route('prices.export') }}" class="bg-gradient-to-r from-green-500 to-emerald-500 hover:from-green-600 hover:to-emerald-600 text-white px-6 py-3 rounded-xl font-bold flex items-center space-x-2 transition-all transform hover:scale-105 shadow-lg">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <span>Exporter Excel</span>
            </a>
        </div>

        {{-- Statistiques globales --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            {{-- Total Contributions --}}
            <div class="bg-gradient-to-br from-orange-500/20 to-pink-500/20 backdrop-blur-lg border border-white/10 rounded-2xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-white/60 text-sm mb-1">Total Contributions</p>
                        <p class="text-3xl font-bold text-white">{{ number_format($totalContributions) }}</p>
                    </div>
                    <div class="text-4xl">🎯</div>
                </div>
            </div>

            {{-- Utilisateurs Actifs --}}
            <div class="bg-gradient-to-br from-blue-500/20 to-cyan-500/20 backdrop-blur-lg border border-white/10 rounded-2xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-white/60 text-sm mb-1">Utilisateurs Actifs</p>
                        <p class="text-3xl font-bold text-white">{{ number_format($totalUsers) }}</p>
                    </div>
                    <div class="text-4xl">👥</div>
                </div>
            </div>

            {{-- Prix Moyen --}}
            <div class="bg-gradient-to-br from-green-500/20 to-emerald-500/20 backdrop-blur-lg border border-white/10 rounded-2xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-white/60 text-sm mb-1">Prix Moyen</p>
                        <p class="text-3xl font-bold text-white">{{ number_format($averagePrice, 2) }}€</p>
                    </div>
                    <div class="text-4xl">💰</div>
                </div>
            </div>

            {{-- Valeur Totale --}}
            <div class="bg-gradient-to-br from-purple-500/20 to-pink-500/20 backdrop-blur-lg border border-white/10 rounded-2xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-white/60 text-sm mb-1">Valeur Totale</p>
                        <p class="text-3xl font-bold text-white">{{ number_format($totalValue, 2) }}€</p>
                    </div>
                    <div class="text-4xl">💎</div>
                </div>
            </div>
        </div>

        {{-- Graphiques --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            {{-- Contributions par jour --}}
            <div class="bg-black/40 backdrop-blur-lg border border-white/10 rounded-2xl p-6">
                <h3 class="text-xl font-bold text-white mb-4">📈 Contributions (7 derniers jours)</h3>
                <canvas id="contributionsByDayChart"></canvas>
            </div>

            {{-- Contributions par type --}}
            <div class="bg-black/40 backdrop-blur-lg border border-white/10 rounded-2xl p-6">
                <h3 class="text-xl font-bold text-white mb-4">📊 Type de Contributions</h3>
                <canvas id="contributionsByTypeChart"></canvas>
            </div>
        </div>

        {{-- Top Produits et Magasins --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            {{-- Top Produits --}}
            <div class="bg-black/40 backdrop-blur-lg border border-white/10 rounded-2xl p-6">
                <h3 class="text-xl font-bold text-white mb-4">🏆 Top 10 Produits</h3>
                <div class="space-y-3">
                    @foreach($topProducts as $product)
                    <div class="flex items-center justify-between bg-white/5 rounded-lg p-3 hover:bg-white/10 transition-all">
                        <div class="flex-1">
                            <p class="text-white font-medium">{{ $product->product_name }}</p>
                            <p class="text-white/60 text-sm">{{ $product->count }} contributions • Moy: {{ number_format($product->avg_price, 2) }}€</p>
                        </div>
                        <div class="text-2xl">🥇</div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Top Magasins --}}
            <div class="bg-black/40 backdrop-blur-lg border border-white/10 rounded-2xl p-6">
                <h3 class="text-xl font-bold text-white mb-4">🏪 Top 5 Magasins</h3>
                <div class="space-y-3">
                    @foreach($topStores as $store)
                    <div class="flex items-center justify-between bg-white/5 rounded-lg p-3 hover:bg-white/10 transition-all">
                        <div class="flex-1">
                            <p class="text-white font-medium">{{ $store->store_name }}</p>
                            <p class="text-white/60 text-sm">{{ $store->count }} contributions</p>
                        </div>
                        <div class="text-2xl">🏪</div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Contributions Récentes --}}
        <div class="bg-black/40 backdrop-blur-lg border border-white/10 rounded-2xl p-6">
            <h3 class="text-xl font-bold text-white mb-4">🕐 Contributions Récentes</h3>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-white/10">
                            <th class="text-left text-white/60 font-medium p-3">Produit</th>
                            <th class="text-left text-white/60 font-medium p-3">Prix</th>
                            <th class="text-left text-white/60 font-medium p-3">Magasin</th>
                            <th class="text-left text-white/60 font-medium p-3">Utilisateur</th>
                            <th class="text-left text-white/60 font-medium p-3">Date</th>
                            <th class="text-left text-white/60 font-medium p-3">Type</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentContributions as $contribution)
                        <tr class="border-b border-white/5 hover:bg-white/5 transition-all">
                            <td class="text-white p-3">{{ $contribution->product_name }}</td>
                            <td class="text-green-400 font-bold p-3">{{ number_format($contribution->price, 2) }}€</td>
                            <td class="text-white/70 p-3">{{ $contribution->store_name ?? 'N/A' }}</td>
                            <td class="text-white/70 p-3">{{ $contribution->user->name }}</td>
                            <td class="text-white/60 text-sm p-3">{{ $contribution->created_at->diffForHumans() }}</td>
                            <td class="p-3">
                                @if($contribution->contribution_type === 'scan')
                                    <span class="bg-orange-500/20 text-orange-400 px-2 py-1 rounded-lg text-xs">📷 Scan</span>
                                @else
                                    <span class="bg-blue-500/20 text-blue-400 px-2 py-1 rounded-lg text-xs">✏️ Manuel</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    {{-- Scripts pour les graphiques --}}
    <script>
        // Contributions par jour
        const contributionsByDayCtx = document.getElementById('contributionsByDayChart').getContext('2d');
        new Chart(contributionsByDayCtx, {
            type: 'line',
            data: {
                labels: @json($contributionsByDay->pluck('date')),
                datasets: [{
                    label: 'Contributions',
                    data: @json($contributionsByDay->pluck('count')),
                    borderColor: 'rgb(251, 146, 60)',
                    backgroundColor: 'rgba(251, 146, 60, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        labels: { color: 'white' }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { color: 'white' },
                        grid: { color: 'rgba(255, 255, 255, 0.1)' }
                    },
                    x: {
                        ticks: { color: 'white' },
                        grid: { color: 'rgba(255, 255, 255, 0.1)' }
                    }
                }
            }
        });

        // Contributions par type
        const contributionsByTypeCtx = document.getElementById('contributionsByTypeChart').getContext('2d');
        new Chart(contributionsByTypeCtx, {
            type: 'doughnut',
            data: {
                labels: @json($contributionsByType->pluck('contribution_type')),
                datasets: [{
                    data: @json($contributionsByType->pluck('count')),
                    backgroundColor: [
                        'rgba(251, 146, 60, 0.8)',
                        'rgba(59, 130, 246, 0.8)'
                    ],
                    borderColor: [
                        'rgb(251, 146, 60)',
                        'rgb(59, 130, 246)'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { color: 'white' }
                    }
                }
            }
        });
    </script>
</body>
</html>


