@extends('layouts.app')

@section('content')
<div class="min-h-screen relative overflow-hidden" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 50%, #020617 100%);">
    <!-- Background Elements -->
    <div class="absolute inset-0 overflow-hidden">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-gradient-to-br from-orange-400/20 to-pink-500/20 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-gradient-to-tr from-pink-400/20 to-orange-500/20 rounded-full blur-3xl"></div>
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-gradient-to-r from-orange-300/10 to-pink-300/10 rounded-full blur-3xl"></div>
    </div>

    <div class="relative z-10 min-h-screen flex items-center justify-center px-4">
        <div class="text-center w-full max-w-4xl">
            <!-- Logo Section Premium -->
            <div class="mb-12 flex flex-col items-center justify-center animate-fade-in-up">
                <div class="bg-white/10 backdrop-blur-xl rounded-3xl border border-white/20 shadow-2xl p-6 mb-8">
                    <div class="flex items-center justify-center space-x-4">
                        <div class="w-16 h-16 bg-gradient-to-br from-orange-400 to-pink-500 rounded-2xl p-3 flex items-center justify-center shadow-lg relative overflow-hidden">
                            <img src="https://yt3.googleusercontent.com/N6sgOb1BI-mBIuB0N2OMss9hhbI7zNVk9EKYY7QGj-2TOFFWTFpvM6uFmgj0TXsSovAXe1Vwxw=s900-c-k-c0x00ffffff-no-rj" 
                                 alt="Etchelast Logo" 
                                 class="w-10 h-10 object-contain relative z-10">
                            <div class="absolute inset-0 bg-gradient-to-br from-white/20 to-transparent"></div>
                        </div>
                        <div class="text-left">
                            <div class="inline-flex items-center space-x-2 bg-orange-500/20 backdrop-blur-sm px-4 py-2 rounded-full mb-2 border border-orange-400/30">
                                <i data-lucide="sparkles" class="w-4 h-4 text-orange-300"></i>
                                <span class="text-sm font-medium text-orange-200">Powered by</span>
                            </div>
                            <h4 class="text-2xl font-bold tracking-wide bg-gradient-to-r from-orange-300 to-pink-300 bg-clip-text text-transparent">ETCHELAST</h4>
                        </div>
                    </div>
                </div>
            </div>


            <!-- Hero Content -->
            <div class="mb-12 animate-fade-in-up" style="animation-delay: 0.2s;">
                <h1 class="text-5xl md:text-6xl lg:text-7xl font-bold mb-6 leading-tight text-white">
                    The Food Intelligence
                    <br>
                    <span class="bg-gradient-to-r from-orange-400 via-pink-400 to-orange-500 bg-clip-text text-transparent">& Nutrition AI-powered platform</span>
                </h1>
                
                <div class="bg-white/10 backdrop-blur-xl rounded-2xl border border-white/20 shadow-2xl p-6 mb-8 max-w-2xl mx-auto">
                    <p class="text-lg md:text-xl text-white/90 leading-relaxed font-medium">
                        Rejoignez la communauté nutrition qui révolutionne votre façon de manger sainement.
                        <span class="text-base md:text-lg text-white/70 block mt-3">
                            Découvrez, comparez et partagez vos trouvailles nutrition
                        </span>
                    </p>
                </div>
            </div>

            <!-- Stats Section Premium -->
            <div class="mb-12 animate-fade-in-up" style="animation-delay: 0.4s;">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-6 max-w-3xl mx-auto">
                    <div class="bg-gradient-to-br from-white/15 to-white/5 backdrop-blur-xl rounded-2xl border border-white/20 shadow-2xl p-6 hover:scale-105 transition-transform">
                        <div class="text-4xl font-bold bg-gradient-to-r from-orange-400 to-pink-400 bg-clip-text text-transparent mb-2">10K+</div>
                        <div class="text-sm font-semibold text-white/80 uppercase tracking-wide">Produits</div>
                    </div>
                    <div class="bg-gradient-to-br from-white/15 to-white/5 backdrop-blur-xl rounded-2xl border border-white/20 shadow-2xl p-6 hover:scale-105 transition-transform">
                        <div class="text-4xl font-bold bg-gradient-to-r from-orange-400 to-pink-400 bg-clip-text text-transparent mb-2">50K+</div>
                        <div class="text-sm font-semibold text-white/80 uppercase tracking-wide">Prix</div>
                    </div>
                    <div class="bg-gradient-to-br from-white/15 to-white/5 backdrop-blur-xl rounded-2xl border border-white/20 shadow-2xl p-6 hover:scale-105 transition-transform">
                        <div class="text-4xl font-bold bg-gradient-to-r from-orange-400 to-pink-400 bg-clip-text text-transparent mb-2">100+</div>
                        <div class="text-sm font-semibold text-white/80 uppercase tracking-wide">Magasins</div>
                    </div>
                </div>
            </div>

            <!-- CTA Buttons Premium -->
            @guest
            <div class="flex flex-col sm:flex-row justify-center gap-6 animate-fade-in-up" style="animation-delay: 0.6s;">
                <a href="{{ route('register') }}" class="btn-primary text-lg px-10 py-4 inline-flex items-center justify-center space-x-3">
                    <i data-lucide="rocket" class="w-5 h-5"></i>
                    <span>Commencer l'aventure</span>
                </a>
                <a href="{{ route('login') }}" class="btn-secondary text-lg px-10 py-4 inline-flex items-center justify-center space-x-3">
                    <i data-lucide="log-in" class="w-5 h-5"></i>
                    <span>J'ai déjà un compte</span>
                </a>
            </div>
            @endguest

            @auth
            <div class="flex flex-col sm:flex-row justify-center gap-6 animate-fade-in-up" style="animation-delay: 0.6s;">
                <a href="{{ route('contribute.index') }}" class="btn-primary text-lg px-10 py-4 inline-flex items-center justify-center space-x-3">
                    <i data-lucide="plus-circle" class="w-5 h-5"></i>
                    <span>Contribuer aux prix</span>
                </a>
                <a href="{{ route('social.index') }}" class="btn-secondary text-lg px-10 py-4 inline-flex items-center justify-center space-x-3">
                    <i data-lucide="users" class="w-5 h-5"></i>
                    <span>Communauté</span>
                </a>
            </div>
            @endauth

        <!-- Quick Search Section Amélioré pour les utilisateurs connectés -->
        <div class="mt-12 pt-4" id="searchSection" style="display: none;">
            <div class="mx-auto max-w-lg card animate-fade-in-up">
                <div class="flex items-center space-x-3 mb-6">
                    <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-cyan-500 rounded-lg flex items-center justify-center">
                        <i data-lucide="search" class="icon text-white"></i>
                    </div>
                    <h5 class="text-white font-bold text-xl">Recherche rapide</h5>
                </div>
                
                <form action="{{ route('products.fetch') }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="relative">
                        <input type="text" 
                               class="form-input w-full pl-12" 
                               id="product_code" 
                               name="product_code" 
                               required 
                               placeholder="Code-barres du produit (ex: 3017620422003)">
                        <i data-lucide="barcode" class="absolute left-4 top-1/2 transform -translate-y-1/2 icon text-white/60"></i>
                    </div>
                    
                    <div class="flex space-x-3">
                        <button type="submit" class="btn-primary flex-1 flex items-center justify-center space-x-2">
                            <i data-lucide="search" class="icon"></i>
                            <span>Rechercher</span>
                        </button>
                        <button type="button" onclick="hideSearchForm()" class="btn-secondary px-4">
                            <i data-lucide="x" class="icon"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="fixed top-0 right-0 p-3 z-50">
        <div class="rounded-xl shadow-lg px-6 py-4 text-white font-semibold" style="background: linear-gradient(90deg, #10b981 0%, #3b82f6 100%);">
            ✅ {{ session('success') }}
        </div>
    </div>
@endif

<script>
// Initialize Lucide icons
lucide.createIcons();

function showSearchForm() {
    const searchSection = document.getElementById('searchSection');
    searchSection.style.display = 'block';
    searchSection.style.opacity = '0';
    searchSection.style.transform = 'translateY(20px)';
    setTimeout(() => {
        searchSection.style.transition = 'all 0.3s ease';
        searchSection.style.opacity = '1';
        searchSection.style.transform = 'translateY(0)';
        document.getElementById('product_code').focus();
    }, 10);
}

function hideSearchForm() {
    const searchSection = document.getElementById('searchSection');
    searchSection.style.transition = 'all 0.3s ease';
    searchSection.style.opacity = '0';
    searchSection.style.transform = 'translateY(20px)';
    setTimeout(() => {
        searchSection.style.display = 'none';
    }, 300);
}

// Animation au chargement
document.addEventListener('DOMContentLoaded', function() {
    // Réinitialiser les icônes après le chargement
    setTimeout(() => {
        lucide.createIcons();
    }, 100);
});
</script>
@endsection