@extends('layouts.app')

@section('title', 'Mon Profil - Pricedom')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-900">
    <div class="bg-black/70 border border-white/10 rounded-2xl shadow-2xl p-10 w-full max-w-lg flex flex-col items-center">
        <h1 class="text-3xl font-bold text-white mb-6 text-center">
            👤 Mon profil
        </h1>

        <!-- Messages de succès -->
        @if(session('success'))
            <div class="w-full p-4 bg-green-500/20 border border-green-400 rounded-xl text-green-300 text-center mb-4">
                ✅ {{ session('success') }}
            </div>
        @endif

        <!-- Messages d'erreur -->
        @if($errors->any())
            <div class="w-full p-4 bg-red-500/20 border border-red-400 rounded-xl text-red-300 mb-4">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form class="w-full flex flex-col gap-6" enctype="multipart/form-data" method="POST" action="{{ route('profile.update') }}">
            @csrf
            @method('PUT')
            <!-- Photo de profil -->
            <div class="flex flex-col items-center gap-2">
                <div class="w-28 h-28 rounded-full bg-gradient-to-r from-orange-400 to-pink-500 flex items-center justify-center overflow-hidden border-4 border-white/20 shadow-lg mb-2">
                    @if($user->avatar)
                        <img src="{{ $user->avatar }}" alt="Photo de profil" class="object-cover w-full h-full">
                    @else
                        <span class="text-5xl text-white/80">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                    @endif
                </div>
                <label for="profile_picture" class="text-sm text-blue-300 cursor-pointer hover:underline">Changer la photo</label>
                <input id="profile_picture" name="profile_picture" type="file" accept="image/*" class="hidden">
            </div>
            <!-- Nom -->
            <div>
                <label class="block text-white/80 font-medium mb-2">Nom</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full px-4 py-3 rounded-xl bg-white/10 text-white placeholder-white/60 border border-white/20 focus:outline-none focus:ring-2 focus:ring-orange-400 transition-all">
            </div>
            <!-- Tag -->
            <div>
                <label class="block text-white/80 font-medium mb-2">Tag (ex: @pseudo)</label>
                <input type="text" name="tag" value="{{ old('tag', $user->tag ?? '') }}" placeholder="@pseudo" class="w-full px-4 py-3 rounded-xl bg-white/10 text-white placeholder-white/60 border border-white/20 focus:outline-none focus:ring-2 focus:ring-pink-400 transition-all">
            </div>
            <!-- Email (readonly) -->
            <div>
                <label class="block text-white/80 font-medium mb-2">Adresse email</label>
                <input type="email" value="{{ $user->email }}" readonly class="w-full px-4 py-3 rounded-xl bg-white/10 text-white/60 border border-white/20 cursor-not-allowed">
            </div>
            <button type="submit" class="w-full py-3 rounded-xl bg-gradient-to-r from-orange-400 to-pink-500 text-white text-lg font-semibold shadow-lg hover:scale-105 transition-transform mt-2">Enregistrer les modifications</button>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const profilePictureInput = document.getElementById('profile_picture');
    const avatarImg = document.querySelector('.w-28.h-28 img');
    const avatarContainer = document.querySelector('.w-28.h-28');
    const form = document.querySelector('form');
    
    let compressedAvatar = null;

    profilePictureInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        
        if (!file) {
            compressedAvatar = null;
            return;
        }

        if (!file.type.startsWith('image/')) {
            alert('❌ Veuillez sélectionner un fichier image valide (JPG, PNG, GIF)');
            e.target.value = '';
            return;
        }

        // Traitement silencieux de l'avatar

        // Utiliser le compresseur Pricedom
        window.pricedomCompressor.compressImage(file, function(processedFile) {
            compressedAvatar = processedFile;
            
            // Mettre à jour l'input avec le fichier compressé
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(processedFile);
            profilePictureInput.files = dataTransfer.files;

            // Afficher la prévisualisation
            const reader = new FileReader();
            reader.onload = function(e) {
                avatarContainer.innerHTML = `
                    <img src="${e.target.result}" alt="Photo de profil" class="object-cover w-full h-full">
                `;
                
                // Compression silencieuse terminée
            };
            reader.readAsDataURL(processedFile);
        });
    });

    // S'assurer d'utiliser le fichier compressé lors de la soumission
    form.addEventListener('submit', function(e) {
        if (compressedAvatar) {
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(compressedAvatar);
            profilePictureInput.files = dataTransfer.files;
        }
    });
});
</script>
@endsection 