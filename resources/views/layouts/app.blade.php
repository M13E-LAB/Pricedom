<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Zyma')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-900">
    <nav class="bg-black/20 backdrop-blur-lg border-b border-white/10 sticky top-0 z-50">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center h-16">
                <a href="{{ route('products.search') }}" class="text-2xl font-bold bg-gradient-to-r from-orange-400 to-pink-400 bg-clip-text text-transparent">
                    ZYMA
                </a>
                
                <div class="flex items-center space-x-4">
                    @auth
                        <a href="{{ route('social.index') }}" class="text-white/80 hover:text-orange-400 transition-colors px-3 py-2 rounded-lg hover:bg-white/10">
                            🍽️ Communauté
                        </a>
                        <a href="{{ route('contribute.index') }}" class="text-white/80 hover:text-green-400 transition-colors px-3 py-2 rounded-lg hover:bg-white/10">
                            💸 Contribuer
                        </a>
                        <a href="{{ route('prices.dashboard') }}" class="text-white/80 hover:text-blue-400 transition-colors px-3 py-2 rounded-lg hover:bg-white/10">
                            📊 Dashboard
                        </a>
                        <div class="relative group">
                            <button class="flex items-center space-x-2 text-white/80 hover:text-orange-400 transition-colors px-3 py-2 rounded-lg hover:bg-white/10">
                                <span>👋 {{ Auth::user()->name }}</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            <div class="absolute right-0 mt-2 w-48 bg-black/80 backdrop-blur-lg rounded-lg shadow-xl border border-white/10 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all">
                                <div class="py-1">
                                    @if(Auth::user() && Auth::user()->email === 'maessakhi99@gmail.com')
                                        <a href="{{ route('admin.analytics') }}" class="w-full text-left px-4 py-2 text-white/80 hover:bg-white/10 hover:text-green-400 transition-colors block">
                                            📊 Analytics
                                        </a>
                                    @endif
                                    <a href="{{ route('profile.edit') }}" class="w-full text-left px-4 py-2 text-white/80 hover:bg-white/10 hover:text-blue-400 transition-colors block">
                                        👤 Mon profil
                                    </a>
                                    <form method="POST" action="{{ route('logout') }}" class="inline w-full">
                                        @csrf
                                        <button type="submit" class="w-full text-left px-4 py-2 text-white/80 hover:bg-white/10 hover:text-orange-400 transition-colors">
                                            🚪 Se déconnecter
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="text-white/80 hover:text-orange-400 transition-colors px-3 py-2 rounded-lg hover:bg-white/10">🔑 Connexion</a>
                        <a href="{{ route('register') }}" class="bg-gradient-to-r from-orange-500 to-pink-500 hover:from-orange-600 hover:to-pink-600 text-white px-6 py-2 rounded-lg transition-all transform hover:scale-105">🚀 S'inscrire</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <main class="min-h-screen">
        @yield('content')
    </main>

    <!-- Compresseur d'images Zyma -->
    <script src="{{ asset('js/image-compressor.js') }}"></script>
</body>
</html>
