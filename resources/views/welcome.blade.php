<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenue sur Pricedom - Votre communauté nutrition</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gradient-to-br from-blue-900 via-gray-900 to-black min-h-screen text-white">
    <!-- Navigation -->
    <nav class="absolute top-0 left-0 right-0 p-6">
        <div class="container mx-auto flex justify-between items-center">
            <div class="text-2xl font-bold bg-gradient-to-r from-orange-400 to-pink-500 bg-clip-text text-transparent">
                PRICEDOM
            </div>
            <div class="space-x-4">
                <a href="{{ route('login') }}" 
                   class="text-white/70 hover:text-white transition-colors px-4 py-2 rounded-lg hover:bg-white/10">
                    Connexion
                </a>
                <a href="{{ route('register') }}" 
                   class="bg-gradient-to-r from-orange-500 to-pink-500 hover:from-orange-600 hover:to-pink-600 px-6 py-2 rounded-lg font-medium transition-all transform hover:scale-105 shadow-lg">
                    Inscription
                </a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="min-h-screen flex items-center justify-center px-4">
        <div class="container mx-auto max-w-6xl">
            <div class="grid md:grid-cols-2 gap-12 items-center">
                <!-- Texte -->
                <div class="space-y-8">
                    <h1 class="text-5xl md:text-6xl font-bold leading-tight">
                        Bienvenue à bord
                        <span class="block mt-2">les</span>
                        <span class="bg-gradient-to-r from-orange-400 to-pink-500 bg-clip-text text-transparent">
                            Etchelastiens !
                        </span>
                    </h1>
                    <p class="text-xl text-white/70">
                        Rejoignez la communauté nutrition qui révolutionne votre façon de manger sainement.
                    </p>
                    <p class="text-lg text-white/60">
                        Découvrez, comparez et partagez vos trouvailles nutrition
                    </p>
                    <div class="flex flex-wrap gap-4">
                        <a href="{{ route('register') }}" 
                           class="bg-gradient-to-r from-orange-500 to-pink-500 hover:from-orange-600 hover:to-pink-600 px-8 py-4 rounded-xl font-medium transition-all transform hover:scale-105 shadow-lg text-lg">
                            Commencer l'aventure
                        </a>
                        <a href="{{ route('login') }}" 
                           class="bg-white/10 hover:bg-white/20 border border-white/10 px-8 py-4 rounded-xl font-medium transition-all text-lg">
                            J'ai déjà un compte
                        </a>
                    </div>
                </div>

                <!-- Image/Features -->
                <div class="relative">
                    <div class="bg-gradient-to-br from-orange-500/20 via-pink-500/20 to-purple-500/20 rounded-3xl p-1">
                        <div class="bg-black/40 backdrop-blur-xl rounded-3xl p-6 space-y-6">
                            <!-- Feature Cards -->
                            <div class="grid gap-4">
                                <div class="bg-white/5 rounded-xl p-4 backdrop-blur-sm border border-white/10">
                                    <h3 class="font-semibold text-lg mb-2 text-orange-400">🔍 Analyse Nutritionnelle</h3>
                                    <p class="text-white/70">Obtenez une analyse détaillée de vos repas avec des conseils personnalisés</p>
                                </div>
                                <div class="bg-white/5 rounded-xl p-4 backdrop-blur-sm border border-white/10">
                                    <h3 class="font-semibold text-lg mb-2 text-pink-400">💚 Score Santé</h3>
                                    <p class="text-white/70">Évaluez la qualité nutritionnelle de vos repas avec notre score santé unique</p>
                                </div>
                                <div class="bg-white/5 rounded-xl p-4 backdrop-blur-sm border border-white/10">
                                    <h3 class="font-semibold text-lg mb-2 text-purple-400">👥 Communauté Active</h3>
                                    <p class="text-white/70">Partagez vos repas, commentez et inspirez-vous des autres membres</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Decorative Elements -->
                    <div class="absolute -z-10 top-1/2 right-0 transform translate-x-1/2 -translate-y-1/2 w-72 h-72 bg-orange-500 rounded-full blur-3xl opacity-20"></div>
                    <div class="absolute -z-10 bottom-0 left-0 transform -translate-x-1/2 w-72 h-72 bg-pink-500 rounded-full blur-3xl opacity-20"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="absolute bottom-0 left-0 right-0 p-6">
        <div class="container mx-auto text-center text-white/50 text-sm">
            Powered by ETCHELAST © {{ date('Y') }} - Tous droits réservés
        </div>
    </footer>
</body>
</html> 