@extends('layouts.app')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-[#1a1a2e] via-[#16213e] to-[#2d3a8c]">
    <div class="text-center w-full">
        <!-- Logo Section -->
        <div class="mb-5 flex flex-col items-center justify-center">
            <div class="flex items-center justify-center mb-4">
                <div class="bg-white rounded-full p-2 mr-3 flex items-center justify-center" style="width: 60px; height: 60px;">
                    <img src="https://yt3.googleusercontent.com/N6sgOb1BI-mBIuB0N2OMss9hhbI7zNVk9EKYY7QGj-2TOFFWTFpvM6uFmgj0TXsSovAXe1Vwxw=s900-c-k-c0x00ffffff-no-rj" 
                         alt="Etchelast Logo" 
                         style="width: 40px; height: 40px; object-fit: contain;">
                </div>
                <div class="text-left">
                    <p class="mb-0 text-white/70 text-xs">Powered by</p>
                    <h4 class="mb-0 font-bold text-white tracking-wide text-base">ETCHELAST</h4>
                </div>
            </div>
        </div>

        <!-- Main Title -->
        <div class="mb-4">
            <h1 class="font-bold mb-0 text-white" style="font-size: 4rem; line-height: 1.1; font-weight: 700;">
                Bienvenue à<br>
                bord<br>
                les<br>
                <span style="background: linear-gradient(45deg, #f97316, #ec4899); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Etchelastiens !</span>
            </h1>
        </div>

        <!-- Subtitle -->
        <div class="mb-4 mt-8">
            <p class="mb-0 text-lg text-white/80">
                Rejoignez la communauté nutrition qui<br>
                révolutionne votre façon de manger sainement.
            </p>
        </div>

        <!-- Call to Action -->
        <div class="mb-5 mt-10">
            <p class="mb-0 text-base text-white/60">
                Découvrez, comparez et partagez vos trouvailles<br>
                nutrition
            </p>
        </div>

        <!-- Action Buttons -->
        @guest
        <div class="flex flex-col md:flex-row gap-4 justify-center mt-12">
            <a href="{{ route('contribute.index') }}" class="px-8 py-4 rounded-full font-semibold text-white text-lg shadow-xl" style="background: linear-gradient(90deg, #10b981 0%, #3b82f6 100%);">
                💸
            </a>
            <a href="{{ route('login') }}" class="px-8 py-4 rounded-full font-semibold text-white text-lg shadow-xl" style="background: linear-gradient(90deg, #f97316 0%, #ec4899 100%);">
                🍽️
            </a>
        </div>
        @endguest

        @auth
        <div class="flex flex-col md:flex-row gap-4 justify-center mt-12">
            <a href="{{ route('contribute.index') }}" class="px-8 py-4 rounded-full font-semibold text-white text-lg shadow-xl" style="background: linear-gradient(90deg, #10b981 0%, #3b82f6 100%);">
                💸
            </a>
            <a href="{{ route('social.index') }}" class="px-8 py-4 rounded-full font-semibold text-white text-lg shadow-xl" style="background: linear-gradient(90deg, #f97316 0%, #ec4899 100%);">
                🍽️
            </a>
        </div>

        <!-- Quick Search Section for Authenticated Users -->
        <div class="mt-10 pt-4" id="searchSection" style="display: none;">
            <div class="mx-auto max-w-lg bg-black/40 rounded-2xl shadow-2xl p-6 backdrop-blur-lg border border-white/10">
                <h5 class="text-white mb-4 font-bold text-xl">🔍 Recherche rapide de produit</h5>
                <form action="{{ route('products.fetch') }}" method="POST">
                    @csrf
                    <div class="flex mb-3">
                        <input type="text" class="flex-1 px-4 py-3 rounded-l-xl bg-white/10 text-white placeholder-white/60 focus:outline-none focus:ring-2 focus:ring-blue-400" 
                               id="product_code" name="product_code" required 
                               placeholder="Code-barres du produit (ex: 3017620422003)">
                        <button type="submit" class="px-6 py-3 rounded-r-xl font-semibold text-white" style="background: linear-gradient(90deg, #10b981 0%, #3b82f6 100%);">
                            🔍
                        </button>
                    </div>
                </form>
                <div class="text-center mt-2">
                    <button onclick="hideSearchForm()" class="px-6 py-2 rounded-full text-white/80 bg-white/10 border border-white/20 hover:bg-white/20 transition-all">
                        ✕ Fermer
                    </button>
                </div>
            </div>
        </div>
        @endauth
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
</script>
@endsection