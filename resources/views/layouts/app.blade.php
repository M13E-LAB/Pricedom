<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Pricedom')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/design-system.css') }}">
    <link rel="stylesheet" href="{{ asset('css/visibility-fix.css') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    
    <!-- FIX MENU MOBILE + STYLE NAVBAR - CSS INLINE -->
    <style>
        /* CACHE LE MENU MOBILE PAR DÉFAUT */
        #mobileMenu {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100vh;
            background: rgba(17, 24, 39, 0.95);
            backdrop-filter: blur(10px);
            z-index: 9999;
            display: none !important;
            opacity: 0 !important;
            visibility: hidden !important;
            transform: translateX(-100%) !important;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        #mobileMenu.active {
            display: flex !important;
            opacity: 1 !important;
            visibility: visible !important;
            transform: translateX(0) !important;
        }

        /* STYLE NAVBAR - FOND BLANC TEXTE NOIR */
        nav.glass-card {
            background: rgba(255, 255, 255, 0.98) !important;
            backdrop-filter: blur(10px) !important;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1) !important;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05) !important;
        }

        nav .text-hero, nav a, nav button, nav span {
            color: #111827 !important;
        }

        nav .nav-link {
            color: #374151 !important;
            font-weight: 600 !important;
        }

        nav .nav-link:hover {
            color: #f97316 !important;
            background: rgba(249, 115, 22, 0.08) !important;
        }

        nav .icon, nav i[data-lucide] {
            color: #374151 !important;
            stroke: #374151 !important;
        }

        nav .nav-link:hover .icon, nav .nav-link:hover i[data-lucide] {
            color: #f97316 !important;
            stroke: #f97316 !important;
        }
    </style>
</head>
<body class="min-h-screen" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 50%, #020617 100%);">

    <nav class="glass-card sticky top-0 z-50 border-b border-white/20">
        <div class="container mx-auto px-6">
            <div class="flex justify-between items-center h-20">
                <a href="{{ route('products.search') }}" class="text-2xl font-bold text-hero flex items-center space-x-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-orange-400 to-pink-500 rounded-xl flex items-center justify-center shadow-lg">
                        <i data-lucide="shopping-cart" class="w-5 h-5 text-white"></i>
                    </div>
                    <span>PRICEDOM</span>
                </a>
                
                <div class="flex items-center space-x-2">
                    @auth
                        <a href="{{ route('social.index') }}" class="nav-link flex items-center space-x-2">
                            <i data-lucide="users" class="icon"></i>
                            <span class="hidden md:inline">Communauté</span>
                        </a>
                        <a href="{{ route('contribute.index') }}" class="nav-link flex items-center space-x-2">
                            <i data-lucide="plus-circle" class="icon"></i>
                            <span class="hidden md:inline">Contribuer</span>
                        </a>
                        <a href="{{ route('prices.browse') }}" class="nav-link flex items-center space-x-2">
                            <i data-lucide="tag" class="icon"></i>
                            <span class="hidden md:inline">Prix</span>
                        </a>
                        <a href="{{ route('prices.dashboard') }}" class="nav-link flex items-center space-x-2">
                            <i data-lucide="bar-chart-3" class="icon"></i>
                            <span class="hidden md:inline">Analytics</span>
                        </a>
                        <a href="{{ route('league.index') }}" class="nav-link flex items-center space-x-2">
                            <i data-lucide="trophy" class="icon"></i>
                            <span class="hidden md:inline">Ligue</span>
                        </a>
                        <!-- Mobile Menu Button -->
                        <button class="md:hidden nav-link" onclick="toggleMobileMenu()">
                            <i data-lucide="menu" class="icon"></i>
                        </button>

                        <!-- Desktop User Menu -->
                        <div class="hidden md:block relative group">
                            <button class="flex items-center space-x-2 nav-link">
                                <div class="w-8 h-8 bg-gradient-to-br from-orange-400 to-pink-500 rounded-full flex items-center justify-center">
                                    <span class="text-sm font-semibold text-white">{{ substr(Auth::user()->name, 0, 1) }}</span>
                                </div>
                                <span class="hidden lg:inline">{{ Auth::user()->name }}</span>
                                <i data-lucide="chevron-down" class="icon"></i>
                            </button>
                            <div class="absolute right-0 mt-2 w-48 card opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all">
                                <div class="py-1">
                                    @if(Auth::user() && Auth::user()->email === 'maessakhi99@gmail.com')
                                        <a href="{{ route('admin.analytics') }}" class="nav-link w-full text-left flex items-center space-x-2">
                                            <i data-lucide="bar-chart-3" class="icon"></i>
                                            <span>Analytics</span>
                                        </a>
                                    @endif
                                    <a href="{{ route('profile.edit') }}" class="nav-link w-full text-left flex items-center space-x-2">
                                        <i data-lucide="user" class="icon"></i>
                                        <span>Mon profil</span>
                                    </a>
                                    <form method="POST" action="{{ route('logout') }}" class="inline w-full">
                                        @csrf
                                        <button type="submit" class="nav-link w-full text-left flex items-center space-x-2">
                                            <i data-lucide="log-out" class="icon"></i>
                                            <span>Se déconnecter</span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="nav-link flex items-center space-x-2">
                            <i data-lucide="log-in" class="icon"></i>
                            <span class="hidden sm:inline">Connexion</span>
                        </a>
                        <a href="{{ route('register') }}" class="btn-primary flex items-center space-x-2">
                            <i data-lucide="user-plus" class="icon"></i>
                            <span class="hidden sm:inline">S'inscrire</span>
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Mobile Menu Overlay -->
    <div id="mobileMenu" class="mobile-menu">
        <div class="mobile-menu-content safe-area-top">
            <div class="flex justify-between items-center mb-8 px-4">
                <div class="text-2xl font-bold bg-gradient-to-r from-orange-400 to-pink-400 bg-clip-text text-transparent">
                    PRICEDOM
                </div>
                <button onclick="toggleMobileMenu()" class="p-2 rounded-lg hover:bg-white/10 transition-colors">
                    <i data-lucide="x" class="w-6 h-6 text-white"></i>
                </button>
            </div>
            
            @auth
                <div class="space-y-4 w-full">
                    <a href="{{ route('social.index') }}" class="mobile-nav-link">
                        <i data-lucide="users" class="icon mr-3"></i>
                        <span>Communauté</span>
                    </a>
                    <a href="{{ route('contribute.index') }}" class="mobile-nav-link">
                        <i data-lucide="plus-circle" class="icon mr-3"></i>
                        <span>Contribuer</span>
                    </a>
                    <a href="{{ route('prices.browse') }}" class="mobile-nav-link">
                        <i data-lucide="tag" class="icon mr-3"></i>
                        <span>Prix</span>
                    </a>
                    <a href="{{ route('prices.dashboard') }}" class="mobile-nav-link">
                        <i data-lucide="bar-chart-3" class="icon mr-3"></i>
                        <span>Analytics</span>
                    </a>
                    <a href="{{ route('league.index') }}" class="mobile-nav-link">
                        <i data-lucide="trophy" class="icon mr-3"></i>
                        <span>Ligue Healthy</span>
                    </a>
                    <a href="{{ route('profile.edit') }}" class="mobile-nav-link">
                        <i data-lucide="user" class="icon mr-3"></i>
                        <span>Mon profil</span>
                    </a>
                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <button type="submit" class="mobile-nav-link w-full">
                            <i data-lucide="log-out" class="icon mr-3"></i>
                            <span>Se déconnecter</span>
                        </button>
                    </form>
                </div>
            @else
                <div class="space-y-4 w-full">
                    <a href="{{ route('login') }}" class="mobile-nav-link">
                        <i data-lucide="log-in" class="icon mr-3"></i>
                        <span>Connexion</span>
                    </a>
                    <a href="{{ route('register') }}" class="mobile-nav-link">
                        <i data-lucide="user-plus" class="icon mr-3"></i>
                        <span>S'inscrire</span>
                    </a>
                </div>
            @endauth
        </div>
    </div>

    <main class="min-h-screen">
        @yield('content')
    </main>

    <!-- Compresseur d'images Pricedom -->
    <script src="{{ asset('js/image-compressor.js') }}"></script>
    <script src="{{ asset('js/mobile-menu-fix.js') }}"></script>
    
    <script>
        // Initialize Lucide icons
        lucide.createIcons();
        
        // Mobile menu toggle
        function toggleMobileMenu() {
            const mobileMenu = document.getElementById('mobileMenu');
            mobileMenu.classList.toggle('active');
        }
        
        // Close mobile menu when clicking outside
        document.addEventListener('click', function(event) {
            const mobileMenu = document.getElementById('mobileMenu');
            const menuButton = event.target.closest('[onclick="toggleMobileMenu()"]');
            
            if (!menuButton && !mobileMenu.contains(event.target)) {
                mobileMenu.classList.remove('active');
            }
        });
        
        // Close mobile menu on escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                document.getElementById('mobileMenu').classList.remove('active');
            }
        });
    </script>
</body>
</html>
