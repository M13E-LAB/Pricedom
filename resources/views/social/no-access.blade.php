@extends('layouts.app')

@section('title', 'Rejoignez la Communauté - Zyma')

@section('content')
<div class="min-h-screen flex items-center">
    <div class="container mx-auto px-4 max-w-4xl">
        <div class="bg-black/30 backdrop-blur-lg rounded-xl border border-white/10 p-8 text-center shadow-2xl">
            <!-- Icône principale -->
            <div class="text-8xl mb-6">🍽️</div>
            
            <!-- Titre principal -->
            <h1 class="text-4xl font-bold text-white mb-4 bg-gradient-to-r from-orange-400 to-pink-400 bg-clip-text text-transparent">
                Bienvenue dans la Communauté Zyma !
            </h1>
            
            <!-- Version courte du principe -->
            <p class="text-xl text-white/80 mb-8 font-semibold">
                Montre ce que t'as mangé pour voir ce que les autres ont mangé !
            </p>
            
            <!-- Call to action -->
            <div class="space-y-4">
                <a href="{{ route('social.create') }}" 
                   class="bg-gradient-to-r from-orange-500 to-pink-500 hover:from-orange-600 hover:to-pink-600 text-white px-10 py-4 rounded-xl font-medium text-lg transition-all transform hover:scale-105 inline-block shadow-xl">
                    📸 Partager mon premier repas
                </a>
                
                {{-- <div class="text-sm text-white/60">
                    <p>Ou retournez à la <a href="{{ route('products.search') }}" class="text-orange-400 hover:text-orange-300 hover:underline transition-colors">recherche de produits</a></p>
                </div> --}}
            </div>
        </div>
    </div>
</div>
@endsection 