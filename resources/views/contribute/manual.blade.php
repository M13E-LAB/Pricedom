@extends('layouts.app')

@section('title', 'Ajouter un prix manuellement - Pricedom')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-900 py-8 px-4">
    <div class="max-w-2xl mx-auto">
        
        <!-- Header -->
        <div class="text-center mb-8">
            <a href="{{ route('contribute.index') }}" class="inline-flex items-center text-white/60 hover:text-white mb-4 transition-colors">
                ← Retour aux contributions
            </a>
            <h1 class="text-4xl font-bold text-white mb-4">
                <span class="text-transparent bg-gradient-to-r from-orange-400 to-pink-500 bg-clip-text">
                    Ajouter un prix ✍️
                </span>
            </h1>
            <p class="text-xl text-gray-300">Saisis directement les informations d'un produit</p>
            <div class="mt-4 inline-flex items-center bg-orange-500/20 text-orange-400 px-4 py-2 rounded-full">
                <span class="font-semibold">+3 XP par prix ajouté</span>
            </div>
        </div>

        <!-- Formulaire principal -->
        <div class="bg-black/70 border border-white/10 rounded-3xl p-8">
            <form id="manual-contribution-form" method="POST" action="{{ route('contribute.store') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="type" value="manual">
                
                <!-- Nom du produit -->
                <div class="mb-6">
                    <label for="product_name" class="block text-white font-semibold mb-3">
                        📦 Nom du produit *
                    </label>
                    <input type="text" id="product_name" name="product_name" required
                           class="w-full bg-white/10 border border-white/20 rounded-xl px-4 py-3 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all"
                           placeholder="Ex: Pain de mie complet, Lait demi-écrémé 1L...">
                    <p class="text-gray-400 text-sm mt-2">Sois précis pour aider les autres utilisateurs</p>
                </div>

                <!-- Prix -->
                <div class="mb-6">
                    <label for="price" class="block text-white font-semibold mb-3">
                        💰 Prix (en euros) *
                    </label>
                    <div class="relative">
                        <input type="number" id="price" name="price" step="0.01" min="0" required
                               class="w-full bg-white/10 border border-white/20 rounded-xl px-4 py-3 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all"
                               placeholder="0.00">
                        <span class="absolute right-4 top-3 text-gray-400">€</span>
                    </div>
                </div>

                <!-- Marque -->
                <div class="mb-6">
                    <label for="brand" class="block text-white font-semibold mb-3">
                        🏷️ Marque
                    </label>
                    <input type="text" id="brand" name="brand"
                           class="w-full bg-white/10 border border-white/20 rounded-xl px-4 py-3 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all"
                           placeholder="Ex: Monoprix, Carrefour Bio, Bonduelle...">
                </div>

                <!-- Magasin -->
                <div class="mb-6">
                    <label for="store" class="block text-white font-semibold mb-3">
                        🏪 Magasin *
                    </label>
                    <select id="store" name="store" required
                            class="w-full bg-white/10 border border-white/20 rounded-xl px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all">
                        <option value="" class="bg-gray-800">Sélectionne un magasin</option>
                        <option value="Carrefour" class="bg-gray-800">🛒 Carrefour</option>
                        <option value="Leclerc" class="bg-gray-800">🛒 Leclerc</option>
                        <option value="Intermarché" class="bg-gray-800">🛒 Intermarché</option>
                        <option value="Super U" class="bg-gray-800">🛒 Super U</option>
                        <option value="Auchan" class="bg-gray-800">🛒 Auchan</option>
                        <option value="Casino" class="bg-gray-800">🛒 Casino</option>
                        <option value="Monoprix" class="bg-gray-800">🛒 Monoprix</option>
                        <option value="Franprix" class="bg-gray-800">🛒 Franprix</option>
                        <option value="Lidl" class="bg-gray-800">🛒 Lidl</option>
                        <option value="Aldi" class="bg-gray-800">🛒 Aldi</option>
                        <option value="Autre" class="bg-gray-800">🏪 Autre</option>
                    </select>
                </div>

                <!-- Catégorie -->
                <div class="mb-6">
                    <label for="category" class="block text-white font-semibold mb-3">
                        📂 Catégorie
                    </label>
                    <select id="category" name="category"
                            class="w-full bg-white/10 border border-white/20 rounded-xl px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all">
                        <option value="" class="bg-gray-800">Choisir une catégorie (optionnel)</option>
                        <option value="Fruits et légumes" class="bg-gray-800">🥕 Fruits et légumes</option>
                        <option value="Viande et poisson" class="bg-gray-800">🥩 Viande et poisson</option>
                        <option value="Produits laitiers" class="bg-gray-800">🥛 Produits laitiers</option>
                        <option value="Épicerie salée" class="bg-gray-800">🥫 Épicerie salée</option>
                        <option value="Épicerie sucrée" class="bg-gray-800">🍯 Épicerie sucrée</option>
                        <option value="Boissons" class="bg-gray-800">🥤 Boissons</option>
                        <option value="Hygiène et beauté" class="bg-gray-800">🧴 Hygiène et beauté</option>
                        <option value="Entretien" class="bg-gray-800">🧽 Entretien</option>
                        <option value="Autre" class="bg-gray-800">📦 Autre</option>
                    </select>
                </div>

                <!-- Photo du produit (optionnel) -->
                <div class="mb-8">
                    <label for="product_photo" class="block text-white font-semibold mb-3">
                        📸 Photo du produit (optionnel)
                    </label>
                    <div class="relative">
                        <input type="file" id="product_photo" name="product_photo" accept="image/*"
                               class="hidden" onchange="handlePhotoSelectWithCompression(event)">
                        <label for="product_photo" 
                               class="w-full cursor-pointer flex flex-col items-center justify-center border-2 border-dashed border-orange-400/50 rounded-xl p-8 bg-orange-500/5 hover:bg-orange-500/10 transition-all">
                            <div id="photo-placeholder">
                                <span class="text-6xl mb-3 block">📷</span>
                                <span class="text-white font-semibold">Cliquer pour ajouter une photo</span>
                                <span class="text-gray-400 text-sm block mt-1">Aide les autres à identifier le produit</span>
                            </div>
                            <div id="photo-preview" class="hidden">
                                <img id="preview-image" class="max-w-full max-h-48 rounded-lg mb-3" alt="Aperçu">
                                <span class="text-green-400 font-semibold">Photo sélectionnée ✓</span>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Boutons d'action -->
                <div class="flex space-x-4">
                    <button type="submit" 
                            class="flex-1 bg-gradient-to-r from-orange-500 to-pink-600 text-white font-bold py-4 px-6 rounded-xl hover:scale-105 transition-transform text-lg flex items-center justify-center space-x-2">
                        <span>✨</span>
                        <span>Ajouter ce prix</span>
                        <span class="bg-white/20 px-2 py-1 rounded-full text-sm">+3 XP</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Conseils -->
        <div class="mt-8 bg-black/70 border border-white/10 rounded-2xl p-6">
            <h3 class="text-xl font-bold text-white mb-4 flex items-center">
                💡 Conseils pour une bonne contribution
            </h3>
            <div class="space-y-3">
                <div class="flex items-start space-x-3">
                    <span class="text-xl">🎯</span>
                    <div>
                        <h4 class="text-white font-semibold">Sois précis</h4>
                        <p class="text-gray-400 text-sm">Indique la taille, le poids ou la quantité (ex: "1L", "500g", "x6")</p>
                    </div>
                </div>
                <div class="flex items-start space-x-3">
                    <span class="text-xl">💰</span>
                    <div>
                        <h4 class="text-white font-semibold">Prix exact</h4>
                        <p class="text-gray-400 text-sm">Vérifie bien le prix sur ton ticket ou en magasin</p>
                    </div>
                </div>
                <div class="flex items-start space-x-3">
                    <span class="text-xl">📅</span>
                    <div>
                        <h4 class="text-white font-semibold">Prix récent</h4>
                        <p class="text-gray-400 text-sm">Ajoute seulement des prix récents (moins de 7 jours)</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Aperçu des dernières contributions -->
        <div class="mt-8 bg-black/70 border border-white/10 rounded-2xl p-6">
            <h3 class="text-xl font-bold text-white mb-4">📈 Impact de tes contributions</h3>
            <div class="grid grid-cols-3 gap-4 text-center">
                <div class="bg-blue-500/20 rounded-xl p-4">
                    <div class="text-2xl font-bold text-blue-400">+3</div>
                    <div class="text-gray-300 text-sm">XP par produit</div>
                </div>
                <div class="bg-green-500/20 rounded-xl p-4">
                    <div class="text-2xl font-bold text-green-400">📊</div>
                    <div class="text-gray-300 text-sm">Aide la communauté</div>
                </div>
                <div class="bg-purple-500/20 rounded-xl p-4">
                    <div class="text-2xl font-bold text-purple-400">🏆</div>
                    <div class="text-gray-300 text-sm">Débloquer badges</div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let compressedProductPhoto = null;

function handlePhotoSelectWithCompression(event) {
    const file = event.target.files[0];
    const placeholder = document.getElementById('photo-placeholder');
    const preview = document.getElementById('photo-preview');
    const previewImage = document.getElementById('preview-image');
    const input = event.target;
    
    if (!file) {
        placeholder.style.display = 'block';
        preview.style.display = 'none';
        compressedProductPhoto = null;
        return;
    }

    if (!file.type.startsWith('image/')) {
        alert('❌ Veuillez sélectionner un fichier image valide (JPG, PNG, GIF)');
        input.value = '';
        return;
    }

    // Traitement silencieux de l'image

    // Utiliser le compresseur Pricedom
    window.pricedomCompressor.compressImage(file, function(processedFile) {
        compressedProductPhoto = processedFile;
        
        // Mettre à jour l'input avec le fichier compressé
        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(processedFile);
        input.files = dataTransfer.files;

        // Afficher la prévisualisation
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImage.src = e.target.result;
            placeholder.style.display = 'none';
            preview.style.display = 'block';
            
            // Affichage simple sans détails techniques
            const confirmationText = preview.querySelector('span');
            confirmationText.innerHTML = 'Photo sélectionnée ✓';
        };
        reader.readAsDataURL(processedFile);

        // Restaurer le placeholder original pour les prochaines sélections
        placeholder.innerHTML = `
            <span class="text-6xl mb-3 block">📷</span>
            <span class="text-white font-semibold">Cliquer pour ajouter une photo</span>
            <span class="text-gray-400 text-sm block mt-1">Aide les autres à identifier le produit</span>
        `;
    });
}

// Validation en temps réel
document.getElementById('product_name').addEventListener('input', function() {
    validateForm();
});

document.getElementById('price').addEventListener('input', function() {
    validateForm();
});

document.getElementById('store').addEventListener('change', function() {
    validateForm();
});

function validateForm() {
    const productName = document.getElementById('product_name').value.trim();
    const price = document.getElementById('price').value;
    const store = document.getElementById('store').value;
    const submitButton = document.querySelector('button[type="submit"]');
    
    const isValid = productName.length >= 3 && price > 0 && store !== '';
    
    if (isValid) {
        submitButton.classList.remove('opacity-50', 'cursor-not-allowed');
        submitButton.disabled = false;
    } else {
        submitButton.classList.add('opacity-50', 'cursor-not-allowed');
        submitButton.disabled = true;
    }
}

// Animation de soumission et gestion compression
document.getElementById('manual-contribution-form').addEventListener('submit', function(e) {
    const submitButton = document.querySelector('button[type="submit"]');
    
    // S'assurer d'utiliser le fichier compressé
    if (compressedProductPhoto) {
        const photoInput = document.getElementById('product_photo');
        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(compressedProductPhoto);
        photoInput.files = dataTransfer.files;
    }
    
    // Animation de soumission
    submitButton.innerHTML = `
        <span class="animate-spin">⚡</span>
        <span>Ajout en cours...</span>
    `;
    submitButton.disabled = true;
});

// Initialiser la validation
validateForm();
</script>
@endsection 