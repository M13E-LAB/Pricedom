<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pricedom - Votre Intelligence Alimentaire</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/design-system.css') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
</head>
<body style="background: var(--bg-primary);" class="min-h-screen text-white overflow-x-hidden">
    
    <!-- Navigation -->
    <nav class="fixed top-0 left-0 right-0 z-50 bg-black/20 backdrop-blur-lg border-b border-white/10">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center h-16">
                <div class="text-2xl font-bold bg-gradient-to-r from-orange-400 to-pink-400 bg-clip-text text-transparent">
                    PRICEDOM
                </div>
                <div class="flex items-center space-x-4">
                    <a href="{{ route('login') }}" class="nav-link">
                        <i data-lucide="log-in" class="icon"></i>
                        <span class="ml-2">Connexion</span>
                    </a>
                    <a href="{{ route('register') }}" class="btn-primary">
                        Commencer
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="min-h-screen flex items-center justify-center px-4 pt-16">
        <div class="container mx-auto max-w-7xl">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                
                <!-- Content -->
                <div class="space-y-8 animate-fade-in-up">
                    <div class="inline-flex items-center space-x-2 bg-gradient-to-r from-orange-500/20 to-pink-500/20 px-4 py-2 rounded-full border border-orange-500/30">
                        <i data-lucide="sparkles" class="icon text-orange-400"></i>
                        <span class="text-sm font-medium text-orange-300">Powered by AI</span>
                    </div>
                    
                    <h1 class="text-display-1 font-bold leading-tight">
                        Votre 
                        <span class="bg-gradient-to-r from-orange-400 to-pink-500 bg-clip-text text-transparent">
                            Intelligence
                        </span>
                        <br>Alimentaire
                    </h1>
                    
                    <p class="text-body-lg text-white/70 max-w-lg">
                        Découvrez, comparez et partagez les meilleurs prix alimentaires. 
                        Rejoignez une communauté qui révolutionne votre façon de consommer.
                    </p>
                    
                    <!-- Stats -->
                    <div class="flex flex-wrap gap-6 pt-4">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-orange-400">10K+</div>
                            <div class="text-sm text-white/60">Produits</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-pink-400">5K+</div>
                            <div class="text-sm text-white/60">Utilisateurs</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-purple-400">50K+</div>
                            <div class="text-sm text-white/60">Prix comparés</div>
                        </div>
                    </div>
                    
                    <!-- CTA Buttons -->
                    <div class="flex flex-col sm:flex-row gap-4 pt-4">
                        <a href="{{ route('register') }}" class="btn-primary inline-flex items-center justify-center space-x-2">
                            <i data-lucide="rocket" class="icon"></i>
                            <span>Commencer gratuitement</span>
                        </a>
                        <a href="#features" class="btn-secondary inline-flex items-center justify-center space-x-2">
                            <i data-lucide="play-circle" class="icon"></i>
                            <span>Voir la démo</span>
                        </a>
                    </div>
                </div>

                <!-- Visual -->
                <div class="relative">
                    <!-- Main Card -->
                    <div class="card card-hover relative z-10">
                        <div class="space-y-6">
                            <!-- Header -->
                            <div class="flex items-center justify-between">
                                <h3 class="text-heading-2 font-semibold">Analyse Nutritionnelle</h3>
                                <div class="bg-green-500/20 text-green-400 px-3 py-1 rounded-full text-sm font-medium">
                                    Score A+
                                </div>
                            </div>
                            
                            <!-- Product Info -->
                            <div class="flex items-center space-x-4">
                                <div class="w-16 h-16 bg-gradient-to-br from-orange-400 to-pink-500 rounded-lg flex items-center justify-center">
                                    <i data-lucide="apple" class="icon-lg text-white"></i>
                                </div>
                                <div>
                                    <h4 class="font-semibold">Pommes Bio</h4>
                                    <p class="text-white/60 text-sm">Fruits & Légumes</p>
                                </div>
                            </div>
                            
                            <!-- Nutrition Stats -->
                            <div class="grid grid-cols-3 gap-4">
                                <div class="text-center">
                                    <div class="text-lg font-bold text-green-400">85</div>
                                    <div class="text-xs text-white/60">Nutri-Score</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-lg font-bold text-blue-400">2.5€</div>
                                    <div class="text-xs text-white/60">Prix moyen</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-lg font-bold text-purple-400">12</div>
                                    <div class="text-xs text-white/60">Magasins</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Floating Elements -->
                    <div class="absolute -top-4 -right-4 w-20 h-20 bg-orange-500 rounded-full blur-2xl opacity-60 animate-pulse"></div>
                    <div class="absolute -bottom-4 -left-4 w-16 h-16 bg-pink-500 rounded-full blur-2xl opacity-60 animate-pulse" style="animation-delay: 1s;"></div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-20 px-4">
        <div class="container mx-auto max-w-6xl">
            <div class="text-center mb-16">
                <h2 class="text-display-2 font-bold mb-4">
                    Pourquoi choisir 
                    <span class="bg-gradient-to-r from-orange-400 to-pink-500 bg-clip-text text-transparent">
                        Pricedom ?
                    </span>
                </h2>
                <p class="text-body-lg text-white/70 max-w-2xl mx-auto">
                    Une plateforme complète qui combine 
                    communauté active et données en temps réel.
                </p>
            </div>
            
            <div class="grid md:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="card card-hover text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-orange-500 to-pink-500 rounded-2xl flex items-center justify-center mx-auto mb-6">
                        <i data-lucide="brain" class="icon-lg text-white"></i>
                    </div>
                    <h3 class="text-heading-2 font-semibold mb-4">IA Nutritionnelle</h3>
                    <p class="text-white/70">
                        Analyse automatique des valeurs nutritionnelles avec des recommandations personnalisées basées sur l'IA.
                    </p>
                </div>
                
                <!-- Feature 2 -->
                <div class="card card-hover text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-green-500 to-blue-500 rounded-2xl flex items-center justify-center mx-auto mb-6">
                        <i data-lucide="trending-up" class="icon-lg text-white"></i>
                    </div>
                    <h3 class="text-heading-2 font-semibold mb-4">Prix en Temps Réel</h3>
                    <p class="text-white/70">
                        Comparez les prix de milliers de produits alimentaires mis à jour en continu par notre communauté.
                    </p>
                </div>
                
                <!-- Feature 3 -->
                <div class="card card-hover text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-purple-500 to-pink-500 rounded-2xl flex items-center justify-center mx-auto mb-6">
                        <i data-lucide="users" class="icon-lg text-white"></i>
                    </div>
                    <h3 class="text-heading-2 font-semibold mb-4">Communauté Active</h3>
                    <p class="text-white/70">
                        Partagez vos découvertes, échangez des conseils et inspirez-vous des autres membres de la communauté.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20 px-4">
        <div class="container mx-auto max-w-4xl text-center">
            <div class="card">
                <h2 class="text-display-2 font-bold mb-6">
                    Prêt à révolutionner votre alimentation ?
                </h2>
                <p class="text-body-lg text-white/70 mb-8 max-w-2xl mx-auto">
                    Rejoignez des milliers d'utilisateurs qui ont déjà transformé leur façon de consommer grâce à Pricedom.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="{{ route('register') }}" class="btn-primary inline-flex items-center justify-center space-x-2">
                        <i data-lucide="user-plus" class="icon"></i>
                        <span>Créer un compte gratuit</span>
                    </a>
                    <a href="{{ route('login') }}" class="btn-secondary inline-flex items-center justify-center space-x-2">
                        <i data-lucide="log-in" class="icon"></i>
                        <span>J'ai déjà un compte</span>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="py-8 px-4 border-t border-white/10">
        <div class="container mx-auto text-center">
            <div class="flex items-center justify-center space-x-2 mb-4">
                <div class="text-xl font-bold bg-gradient-to-r from-orange-400 to-pink-400 bg-clip-text text-transparent">
                    PRICEDOM
                </div>
                <span class="text-white/60">×</span>
                <span class="text-white/60">ETCHELAST</span>
            </div>
            <p class="text-white/50 text-sm">
                © {{ date('Y') }} Pricedom. Tous droits réservés.
            </p>
        </div>
    </footer>

    <script>
        // Initialize Lucide icons
        lucide.createIcons();
        
        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });
    </script>
</body>
</html>