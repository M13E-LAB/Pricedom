@extends('layouts.app')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-[#1a1a2e] via-[#16213e] to-[#2d3a8c]">
    <div class="w-full max-w-md mx-auto">
        <div class="bg-black/60 border border-white/10 rounded-2xl shadow-2xl p-8">
            <h2 class="text-2xl font-bold text-white mb-8 flex items-center gap-2">
                <span>🚀</span> Créer un compte <span class="text-orange-400">ZYMA</span>
            </h2>
            @if ($errors->any())
                <div class="bg-red-500/20 border border-red-400/30 text-red-200 px-4 py-3 rounded-lg mb-6">
                    <ul class="mb-0 list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <form method="POST" action="{{ route('register') }}" class="space-y-6">
                @csrf
                <div>
                    <label for="name" class="block text-white/80 font-medium mb-2">Nom complet</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required
                        class="w-full px-4 py-3 rounded-lg bg-white/10 text-white placeholder-white/60 border border-white/20 focus:outline-none focus:ring-2 focus:ring-orange-400 transition-all @error('name') border-red-400 @enderror"
                        placeholder="Votre nom complet">
                </div>
                <div>
                    <label for="email" class="block text-white/80 font-medium mb-2">Adresse email</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required
                        class="w-full px-4 py-3 rounded-lg bg-white/10 text-white placeholder-white/60 border border-white/20 focus:outline-none focus:ring-2 focus:ring-orange-400 transition-all @error('email') border-red-400 @enderror"
                        placeholder="Votre adresse email">
                </div>
                <div>
                    <label for="password" class="block text-white/80 font-medium mb-2">Mot de passe</label>
                    <input type="password" id="password" name="password" required
                        class="w-full px-4 py-3 rounded-lg bg-white/10 text-white placeholder-white/60 border border-white/20 focus:outline-none focus:ring-2 focus:ring-orange-400 transition-all @error('password') border-red-400 @enderror"
                        placeholder="Choisissez un mot de passe">
                    <div class="text-xs text-white/60 mt-1">Minimum 8 caractères</div>
                </div>
                <div>
                    <label for="password_confirmation" class="block text-white/80 font-medium mb-2">Confirmer le mot de passe</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required
                        class="w-full px-4 py-3 rounded-lg bg-white/10 text-white placeholder-white/60 border border-white/20 focus:outline-none focus:ring-2 focus:ring-orange-400 transition-all"
                        placeholder="Confirmez le mot de passe">
                </div>
                <button type="submit" class="w-full py-3 rounded-full font-semibold text-lg text-white shadow-lg bg-gradient-to-r from-orange-500 to-pink-500 hover:from-orange-600 hover:to-pink-600 transition-all transform hover:scale-105">
                    Créer mon compte
                </button>
            </form>
            <div class="text-center mt-8">
                <p class="text-white/70">Déjà un compte ?
                    <a href="{{ route('login') }}" class="text-orange-400 hover:underline font-semibold">Se connecter</a>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection 