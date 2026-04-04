@extends('layouts.app')

@section('title', 'Debug API Response')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-6xl mx-auto">
        <h1 class="text-3xl font-bold text-white mb-6">🔍 Debug API Response</h1>
        
        <div class="bg-white/10 backdrop-blur-lg rounded-lg p-6 mb-6">
            <h2 class="text-xl font-bold text-white mb-4">Test direct de l'API</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <a href="{{ route('debug.api-test', ['country' => 'France']) }}" 
                   class="bg-blue-500/20 hover:bg-blue-500/30 border border-blue-500/30 rounded-lg p-4 transition-all">
                    <div class="text-blue-400 font-bold mb-2">🇫🇷 Test France</div>
                    <div class="text-white/80 text-sm">API directe avec osm_address_country__like=France</div>
                </a>
                
                <a href="{{ route('debug.api-test', ['country' => 'Germany']) }}" 
                   class="bg-red-500/20 hover:bg-red-500/30 border border-red-500/30 rounded-lg p-4 transition-all">
                    <div class="text-red-400 font-bold mb-2">🇩🇪 Test Allemagne</div>
                    <div class="text-white/80 text-sm">API directe avec osm_address_country__like=Germany</div>
                </a>
            </div>
        </div>
        
        <div class="mt-6 text-center">
            <a href="{{ route('prices.browse') }}" 
               class="bg-gradient-to-r from-blue-500 to-purple-500 hover:from-blue-600 hover:to-purple-600 text-white px-8 py-3 rounded-lg font-bold transition-all transform hover:scale-105 shadow-lg">
                🔍 Retour à la recherche
            </a>
        </div>
    </div>
</div>
@endsection