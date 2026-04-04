@extends('layouts.app')

@section('title', 'Dashboard - Pricedom')

@section('content')
<div class="min-h-screen p-4 md:p-6">
    <div class="container mx-auto max-w-7xl">
        
        <!-- Header -->
        <div class="mb-8">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-display-2 font-bold text-white mb-2">
                        Tableau de bord
                    </h1>
                    <p class="text-white/70">
                        Bienvenue, {{ Auth::user()->name }}. Voici un aperçu de votre activité.
                    </p>
                </div>
                <div class="mt-4 md:mt-0">
                    <button class="btn-primary inline-flex items-center space-x-2">
                        <i data-lucide="plus" class="icon"></i>
                        <span>Nouvelle contribution</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-white/60 text-sm font-medium">Contributions</p>
                        <p class="text-2xl font-bold text-white">{{ $userStats['contributions'] ?? 0 }}</p>
                    </div>
                    <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-emerald-600 rounded-lg flex items-center justify-center">
                        <i data-lucide="plus-circle" class="icon text-white"></i>
                    </div>
                </div>
                <div class="mt-4 flex items-center text-sm">
                    <span class="text-green-400">+12%</span>
                    <span class="text-white/60 ml-2">ce mois</span>
                </div>
            </div>

            <div class="card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-white/60 text-sm font-medium">Posts sociaux</p>
                        <p class="text-2xl font-bold text-white">{{ $userStats['posts'] ?? 0 }}</p>
                    </div>
                    <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-cyan-600 rounded-lg flex items-center justify-center">
                        <i data-lucide="users" class="icon text-white"></i>
                    </div>
                </div>
                <div class="mt-4 flex items-center text-sm">
                    <span class="text-blue-400">+8%</span>
                    <span class="text-white/60 ml-2">ce mois</span>
                </div>
            </div>

            <div class="card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-white/60 text-sm font-medium">Économies</p>
                        <p class="text-2xl font-bold text-white">{{ number_format($userStats['savings'] ?? 0, 2) }}€</p>
                    </div>
                    <div class="w-12 h-12 bg-gradient-to-br from-orange-500 to-pink-600 rounded-lg flex items-center justify-center">
                        <i data-lucide="piggy-bank" class="icon text-white"></i>
                    </div>
                </div>
                <div class="mt-4 flex items-center text-sm">
                    <span class="text-orange-400">+25%</span>
                    <span class="text-white/60 ml-2">ce mois</span>
                </div>
            </div>

            <div class="card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-white/60 text-sm font-medium">Badges</p>
                        <p class="text-2xl font-bold text-white">{{ $userStats['badges'] ?? 0 }}</p>
                    </div>
                    <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-lg flex items-center justify-center">
                        <i data-lucide="award" class="icon text-white"></i>
                    </div>
                </div>
                <div class="mt-4 flex items-center text-sm">
                    <span class="text-purple-400">+2</span>
                    <span class="text-white/60 ml-2">nouveaux</span>
                </div>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Recent Activity -->
            <div class="lg:col-span-2">
                <div class="card">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-heading-2 font-semibold text-white">Activité récente</h2>
                        <button class="text-orange-400 hover:text-orange-300 transition-colors">
                            Voir tout
                        </button>
                    </div>
                    
                    <div class="space-y-4">
                        <!-- Activity Item -->
                        <div class="flex items-start space-x-4 p-4 bg-white/5 rounded-lg">
                            <div class="w-10 h-10 bg-green-500/20 rounded-lg flex items-center justify-center flex-shrink-0">
                                <i data-lucide="plus-circle" class="icon text-green-400"></i>
                            </div>
                            <div class="flex-1">
                                <p class="text-white font-medium">Nouvelle contribution ajoutée</p>
                                <p class="text-white/60 text-sm">Pommes Bio - Carrefour - 2.50€/kg</p>
                                <p class="text-white/40 text-xs mt-1">Il y a 2 heures</p>
                            </div>
                        </div>

                        <div class="flex items-start space-x-4 p-4 bg-white/5 rounded-lg">
                            <div class="w-10 h-10 bg-blue-500/20 rounded-lg flex items-center justify-center flex-shrink-0">
                                <i data-lucide="heart" class="icon text-blue-400"></i>
                            </div>
                            <div class="flex-1">
                                <p class="text-white font-medium">Post aimé par la communauté</p>
                                <p class="text-white/60 text-sm">Votre salade healthy a reçu 15 likes</p>
                                <p class="text-white/40 text-xs mt-1">Il y a 4 heures</p>
                            </div>
                        </div>

                        <div class="flex items-start space-x-4 p-4 bg-white/5 rounded-lg">
                            <div class="w-10 h-10 bg-purple-500/20 rounded-lg flex items-center justify-center flex-shrink-0">
                                <i data-lucide="award" class="icon text-purple-400"></i>
                            </div>
                            <div class="flex-1">
                                <p class="text-white font-medium">Nouveau badge débloqué</p>
                                <p class="text-white/60 text-sm">Contributeur actif - 50 contributions</p>
                                <p class="text-white/40 text-xs mt-1">Hier</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-8">
                
                <!-- Quick Actions -->
                <div class="card">
                    <h3 class="text-heading-2 font-semibold text-white mb-4">Actions rapides</h3>
                    <div class="space-y-3">
                        <a href="{{ route('contribute.scan') }}" class="w-full btn-primary text-left flex items-center space-x-3">
                            <i data-lucide="scan" class="icon"></i>
                            <span>Scanner un ticket</span>
                        </a>
                        <a href="{{ route('social.create') }}" class="w-full btn-secondary text-left flex items-center space-x-3">
                            <i data-lucide="camera" class="icon"></i>
                            <span>Partager un repas</span>
                        </a>
                        <a href="{{ route('prices.browse') }}" class="w-full btn-secondary text-left flex items-center space-x-3">
                            <i data-lucide="search" class="icon"></i>
                            <span>Rechercher des prix</span>
                        </a>
                    </div>
                </div>

                <!-- Trending Products -->
                <div class="card">
                    <h3 class="text-heading-2 font-semibold text-white mb-4">Produits tendance</h3>
                    <div class="space-y-3">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-gradient-to-br from-orange-400 to-pink-500 rounded-lg flex items-center justify-center">
                                <span class="text-xs font-bold text-white">1</span>
                            </div>
                            <div class="flex-1">
                                <p class="text-white font-medium text-sm">Avocats Bio</p>
                                <p class="text-white/60 text-xs">+15% de recherches</p>
                            </div>
                        </div>
                        
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-gradient-to-br from-green-400 to-blue-500 rounded-lg flex items-center justify-center">
                                <span class="text-xs font-bold text-white">2</span>
                            </div>
                            <div class="flex-1">
                                <p class="text-white font-medium text-sm">Quinoa</p>
                                <p class="text-white/60 text-xs">+12% de recherches</p>
                            </div>
                        </div>
                        
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-gradient-to-br from-purple-400 to-pink-500 rounded-lg flex items-center justify-center">
                                <span class="text-xs font-bold text-white">3</span>
                            </div>
                            <div class="flex-1">
                                <p class="text-white font-medium text-sm">Lait d'amande</p>
                                <p class="text-white/60 text-xs">+8% de recherches</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Achievement Progress -->
                <div class="card">
                    <h3 class="text-heading-2 font-semibold text-white mb-4">Progrès</h3>
                    <div class="space-y-4">
                        <div>
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-white/80 text-sm">Contributeur Expert</span>
                                <span class="text-white/60 text-xs">75/100</span>
                            </div>
                            <div class="w-full bg-white/10 rounded-full h-2 progress-container">
                                <div class="bg-gradient-to-r from-orange-500 to-pink-500 h-2 rounded-full progress-bar" style="width: 75%"></div>
                            </div>
                        </div>
                        
                        <div>
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-white/80 text-sm">Influenceur Social</span>
                                <span class="text-white/60 text-xs">45/50</span>
                            </div>
                            <div class="w-full bg-white/10 rounded-full h-2 progress-container">
                                <div class="bg-gradient-to-r from-blue-500 to-cyan-500 h-2 rounded-full progress-bar" style="width: 90%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Initialize Lucide icons
    lucide.createIcons();
</script>
@endsection