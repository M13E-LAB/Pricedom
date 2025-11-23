@extends('layouts.app')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-[#1a1a2e] via-[#16213e] to-[#2d3a8c]">
    <div class="w-full max-w-md mx-auto">
        <div class="bg-black/60 border border-white/10 rounded-2xl shadow-2xl p-8">
            <h2 class="text-2xl font-bold text-white mb-8 flex items-center gap-2">
                <span>🔐</span> Réinitialiser le mot de passe
            </h2>
            
            <div class="text-white/70 text-sm mb-6">
                Entrez votre adresse email et nous vous enverrons un lien pour réinitialiser votre mot de passe.
            </div>
            
            @if (session('status'))
                <div class="bg-green-500/20 border border-green-400/30 text-green-200 px-4 py-3 rounded-lg mb-6">
                    {{ session('status') }}
                </div>
            @endif
            
            @if ($errors->any())
                <div class="bg-red-500/20 border border-red-400/30 text-red-200 px-4 py-3 rounded-lg mb-6">
                    <ul class="mb-0 list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            
            <form method="POST" action="{{ route('password.email') }}" class="space-y-6">
                @csrf
                <div>
                    <label for="email" class="block text-white/80 font-medium mb-2">Adresse email</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                        class="w-full px-4 py-3 rounded-lg bg-white/10 text-white placeholder-white/60 border border-white/20 focus:outline-none focus:ring-2 focus:ring-orange-400 transition-all @error('email') border-red-400 @enderror"
                        placeholder="Votre adresse email">
                </div>
                
                <button type="submit" class="w-full py-3 rounded-full font-semibold text-lg text-white shadow-lg bg-gradient-to-r from-orange-500 to-pink-500 hover:from-orange-600 hover:to-pink-600 transition-all transform hover:scale-105">
                    Envoyer le lien de réinitialisation
                </button>
            </form>
            
            <div class="text-center mt-8">
                <a href="{{ route('login') }}" class="text-orange-400 hover:underline font-semibold">
                    ← Retour à la connexion
                </a>
            </div>
        </div>
    </div>
</div>
@endsection 