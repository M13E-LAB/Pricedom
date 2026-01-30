@extends('layouts.app')

@section('title', 'Partager un Repas - Pricedom')

@section('content')
<div class="min-h-screen">
    <div class="container mx-auto px-4 py-8 max-w-2xl">
        <!-- En-tête -->
        <div class="bg-black/30 backdrop-blur-lg rounded-xl border border-white/10 p-6 mb-6">
            <div class="flex items-center space-x-3">
                <a href="{{ route('social.index') }}" 
                   class="text-white/70 hover:text-orange-400 transition-colors p-2 hover:bg-white/10 rounded-lg">
                    ← Retour
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-white">📸 Partager un Repas</h1>
                    <p class="text-white/70 mt-1">Montrez vos créations culinaires à la communauté</p>
                </div>
            </div>
        </div>

        <!-- Formulaire -->
        <div class="bg-black/30 backdrop-blur-lg rounded-xl border border-white/10 p-6">
            <form action="{{ route('social.store') }}" method="POST" enctype="multipart/form-data" id="postForm">
                @csrf
                
                <!-- Upload d'image -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-white mb-2">
                        Photo de votre repas <span class="text-red-400">*</span>
                    </label>
                    
                    <div class="border-2 border-dashed border-white/30 rounded-xl p-6 text-center hover:border-orange-400 transition-colors bg-white/5" 
                         id="dropZone">
                        <input type="file" name="image" id="imageInput" accept="image/*" class="hidden" required>
                        
                        <div id="uploadPrompt">
                            <div class="text-5xl mb-4">📷</div>
                            <p class="text-lg font-medium text-white mb-2">Cliquez pour choisir une photo</p>
                            <p class="text-sm text-white/70">ou glissez-déposez votre image ici</p>
                            <p class="text-xs text-white/50 mt-2">JPG, PNG, GIF - Compression automatique pour gros fichiers</p>
                        </div>
                        
                        <div id="imagePreview" class="hidden">
                            <img id="previewImage" class="max-w-full h-64 object-cover rounded-lg mx-auto">
                            <button type="button" id="removeImage" 
                                    class="mt-3 text-red-400 hover:text-red-300 text-sm bg-red-500/20 px-4 py-2 rounded-lg hover:bg-red-500/30 transition-all">
                                🗑️ Supprimer
                            </button>
                        </div>
                    </div>
                    
                    @error('image')
                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Description -->
                <div class="mb-6">
                    <label for="description" class="block text-sm font-medium text-white mb-2">
                        Description (optionnelle)
                    </label>
                    <textarea name="description" id="description" rows="4" 
                              class="w-full px-4 py-3 bg-white/10 border border-white/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-400 focus:border-transparent text-white placeholder-white/50 backdrop-blur-sm"
                              placeholder="Décrivez votre repas, les ingrédients, votre expérience...">{{ old('description') }}</textarea>
                    <p class="text-xs text-white/60 mt-1">Maximum 500 caractères</p>
                    @error('description')
                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Conseils -->
                <div class="bg-blue-500/20 border border-blue-400/30 rounded-xl p-4 mb-6 backdrop-blur-sm">
                    <h3 class="font-medium text-blue-300 mb-2">💡 Conseils pour une belle photo :</h3>
                    <ul class="text-white/80 text-sm space-y-1">
                        <li>• Utilisez un bon éclairage naturel</li>
                        <li>• Prenez la photo d'en haut pour montrer tous les éléments</li>
                        <li>• Nettoyez autour de l'assiette pour un rendu professionnel</li>
                        <li>• N'hésitez pas à ajouter des détails sur la préparation !</li>
                    </ul>
                </div>

                <!-- Boutons -->
                <div class="flex space-x-4">
                    <button type="submit" 
                            class="flex-1 bg-gradient-to-r from-orange-500 to-pink-500 hover:from-orange-600 hover:to-pink-600 text-white py-3 px-6 rounded-lg font-medium transition-all transform hover:scale-105 shadow-lg">
                        🚀 Partager mon repas
                    </button>
                    <a href="{{ route('social.index') }}" 
                       class="px-6 py-3 border border-white/30 text-white/80 rounded-lg hover:bg-white/10 transition-all">
                        Annuler
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const dropZone = document.getElementById('dropZone');
    const imageInput = document.getElementById('imageInput');
    const uploadPrompt = document.getElementById('uploadPrompt');
    const imagePreview = document.getElementById('imagePreview');
    const previewImage = document.getElementById('previewImage');
    const removeImageBtn = document.getElementById('removeImage');
    const postForm = document.getElementById('postForm');
    
    let compressedFile = null; // Stock le fichier compressé

    // Click to upload
    dropZone.addEventListener('click', function(e) {
        if (e.target !== removeImageBtn) {
            imageInput.click();
        }
    });

    // Drag and drop
    dropZone.addEventListener('dragover', function(e) {
        e.preventDefault();
        dropZone.classList.add('border-orange-400', 'bg-orange-500/20');
    });

    dropZone.addEventListener('dragleave', function(e) {
        e.preventDefault();
        dropZone.classList.remove('border-orange-400', 'bg-orange-500/20');
    });

    dropZone.addEventListener('drop', function(e) {
        e.preventDefault();
        dropZone.classList.remove('border-orange-400', 'bg-orange-500/20');
        
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            handleImageSelect(files[0]);
        }
    });

    // File input change
    imageInput.addEventListener('change', function(e) {
        if (e.target.files.length > 0) {
            handleImageSelect(e.target.files[0]);
        }
    });

    // Remove image
    removeImageBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        imageInput.value = '';
        compressedFile = null;
        uploadPrompt.classList.remove('hidden');
        imagePreview.classList.add('hidden');
    });

    function handleImageSelect(file) {
        if (!file || !file.type.startsWith('image/')) {
            alert('❌ Veuillez sélectionner un fichier image valide (JPG, PNG, GIF)');
            return;
        }

        // Traitement silencieux de l'image

        // Utiliser le compresseur Pricedom
        window.pricedomCompressor.compressImage(file, function(processedFile) {
            compressedFile = processedFile;
            
            // Mettre à jour l'input avec le fichier compressé
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(processedFile);
            imageInput.files = dataTransfer.files;

            // Afficher la prévisualisation
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImage.src = e.target.result;
                uploadPrompt.classList.add('hidden');
                imagePreview.classList.remove('hidden');
                
                // Compression silencieuse - pas d'affichage des détails
            };
            reader.readAsDataURL(processedFile);
        });
    }

    // Fonctions de compression supprimées pour une expérience utilisateur transparente

    // Intercepter la soumission du formulaire pour s'assurer d'utiliser le fichier compressé
    postForm.addEventListener('submit', function(e) {
        if (compressedFile && imageInput.files.length > 0) {
            // S'assurer que le fichier compressé est bien utilisé
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(compressedFile);
            imageInput.files = dataTransfer.files;
        }
    });

    // Character counter for description
    const description = document.getElementById('description');
    const maxLength = 500;
    
    description.addEventListener('input', function() {
        const remaining = maxLength - this.value.length;
        // You can add a character counter here if needed
    });
});
</script>
@endsection 