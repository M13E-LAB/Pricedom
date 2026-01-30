@extends('layouts.app')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-[#1a1a2e] via-[#16213e] to-[#2d3a8c]">
    <div class="w-full max-w-md mx-auto">
        <div class="bg-black/60 border border-white/10 rounded-2xl shadow-2xl p-8">
            <h2 class="text-2xl font-bold text-white mb-8 flex items-center gap-2">
                <span>🔑</span> Connexion <span class="text-orange-400">PRICEDOM</span>
            </h2>
            
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
            
            <form method="POST" action="{{ route('login') }}" class="space-y-6">
                @csrf
                <div>
                    <label for="email" class="block text-white/80 font-medium mb-2">Adresse email</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                        class="w-full px-4 py-3 rounded-lg bg-white/10 text-white placeholder-white/60 border border-white/20 focus:outline-none focus:ring-2 focus:ring-orange-400 transition-all @error('email') border-red-400 @enderror"
                        placeholder="Votre adresse email">
                </div>
                <div>
                    <label for="password" class="block text-white/80 font-medium mb-2">Mot de passe</label>
                    <input type="password" id="password" name="password" required
                        class="w-full px-4 py-3 rounded-lg bg-white/10 text-white placeholder-white/60 border border-white/20 focus:outline-none focus:ring-2 focus:ring-orange-400 transition-all @error('password') border-red-400 @enderror"
                        placeholder="Votre mot de passe">
                </div>
                <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center">
                        <input type="checkbox" id="remember" name="remember" class="rounded border-white/30 bg-white/10 text-orange-500 focus:ring-orange-400">
                        <label for="remember" class="ml-2 text-white/70 text-sm">Se souvenir de moi</label>
                    </div>
                    <a href="{{ route('password.request') }}" class="text-orange-400 hover:text-orange-300 text-sm font-medium hover:underline">
                        Mot de passe oublié ?
                    </a>
                </div>
                <button type="submit" class="w-full py-3 rounded-full font-semibold text-lg text-white shadow-lg bg-gradient-to-r from-orange-500 to-pink-500 hover:from-orange-600 hover:to-pink-600 transition-all transform hover:scale-105">
                    Se connecter
                </button>
            </form>
            <div class="text-center mt-8">
                <p class="text-white/70">Pas encore de compte ?
                    <a href="{{ route('register') }}" class="text-orange-400 hover:underline font-semibold">Créer un compte</a>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection 