@extends('layouts.app')

@section('title', 'Scanner un ticket - Pricedom')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-900 py-8 px-4">
    <div class="max-w-4xl mx-auto">
        
        <!-- Header -->
        <div class="text-center mb-8">
            <a href="{{ route('contribute.index') }}" class="inline-flex items-center text-white/60 hover:text-white mb-4 transition-colors">
                ← Retour aux contributions
            </a>
            <h1 class="text-4xl font-bold text-white mb-4">
                <span class="text-transparent bg-gradient-to-r from-green-400 to-blue-500 bg-clip-text">
                    Scanner un ticket 📸
                </span>
            </h1>
            <p class="text-xl text-gray-300">Prends une photo pour extraire automatiquement les prix</p>
            <div class="mt-4 inline-flex items-center bg-green-500/20 text-green-400 px-4 py-2 rounded-full">
                <span class="font-semibold">+5 XP par prix détecté • Analyse automatique</span>
            </div>
        </div>

        <!-- Zone de scan principale -->
        <div class="bg-black/70 border border-white/10 rounded-3xl p-8 mb-8">
            <div class="text-center">
                <!-- Zone de prévisualisation caméra/image -->
                <div id="camera-preview" class="bg-gray-800 border-2 border-dashed border-gray-600 rounded-2xl p-12 mb-6 relative overflow-hidden">
                    <div id="placeholder" class="text-center">
                        <div class="text-8xl mb-4">🔍</div>
                        <h3 class="text-2xl font-bold text-white mb-3">Analyse automatique prête !</h3>
                        <p class="text-gray-400 mb-6">Le système va analyser ton ticket automatiquement</p>
                        
                        <!-- Boutons de capture -->
                        <div class="space-y-4">
                            <button onclick="startCamera()" class="bg-gradient-to-r from-green-500 to-blue-500 text-white font-bold py-4 px-8 rounded-xl hover:scale-105 transition-transform text-lg">
                                📸 Utiliser la caméra
                            </button>
                            <div class="text-gray-500">ou</div>
                            <label for="file-input" class="inline-block bg-gradient-to-r from-purple-500 to-pink-500 text-white font-bold py-4 px-8 rounded-xl hover:scale-105 transition-transform cursor-pointer text-lg">
                                📂 Choisir une photo
                            </label>
                            <input type="file" id="file-input" accept="image/*" class="hidden" onchange="handleFileSelect(event)">
                        </div>
                    </div>

                    <!-- Caméra video element (caché par défaut) -->
                    <video id="camera-video" class="w-full h-auto rounded-xl hidden" autoplay playsinline></video>
                    
                    <!-- Image preview (caché par défaut) -->
                    <img id="image-preview" class="w-full h-auto rounded-xl hidden" alt="Aperçu du ticket">
                    
                    <!-- Overlay de scan -->
                    <div id="scan-overlay" class="absolute inset-0 bg-black/50 flex items-center justify-center hidden">
                        <div class="text-center">
                            <div class="animate-spin text-6xl mb-4">🔍</div>
                            <p class="text-white text-xl font-semibold">Analyse en cours...</p>
                            <p class="text-gray-300" id="scan-status">Extraction des produits et prix</p>
                        </div>
                    </div>
                </div>

                <!-- Boutons d'action -->
                <div id="action-buttons" class="hidden space-x-4">
                    <button onclick="analyzeTicket()" class="bg-gradient-to-r from-green-500 to-emerald-600 text-white font-bold py-3 px-6 rounded-xl hover:scale-105 transition-transform">
                        🔍 Analyser le ticket
                    </button>
                    <button onclick="retakePhoto()" class="bg-gradient-to-r from-gray-600 to-gray-700 text-white font-bold py-3 px-6 rounded-xl hover:scale-105 transition-transform">
                        🔄 Reprendre
                    </button>
                </div>
            </div>
        </div>

        <!-- Conseils pour un bon scan -->
        <div class="bg-black/70 border border-white/10 rounded-2xl p-6 mb-8">
            <h3 class="text-xl font-bold text-white mb-4 flex items-center">
                🔍 Analyse automatique du ticket
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="flex items-start space-x-3">
                    <span class="text-2xl">🔆</span>
                    <div>
                        <h4 class="text-white font-semibold">Photo nette</h4>
                        <p class="text-gray-400 text-sm">Le système a besoin d'un texte lisible</p>
                    </div>
                </div>
                <div class="flex items-start space-x-3">
                    <span class="text-2xl">📏</span>
                    <div>
                        <h4 class="text-white font-semibold">Ticket complet</h4>
                        <p class="text-gray-400 text-sm">Inclus le nom du magasin et la date</p>
                    </div>
                </div>
                <div class="flex items-start space-x-3">
                    <span class="text-2xl">🎯</span>
                    <div>
                        <h4 class="text-white font-semibold">Prix visibles</h4>
                        <p class="text-gray-400 text-sm">Le système détectera tous les produits</p>
                    </div>
                </div>
                <div class="flex items-start space-x-3">
                    <span class="text-2xl">⚡</span>
                    <div>
                        <h4 class="text-white font-semibold">Analyse rapide</h4>
                        <p class="text-gray-400 text-sm">Résultats en quelques secondes</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Résultats du scan (caché par défaut) -->
        <div id="scan-results" class="bg-black/70 border border-white/10 rounded-2xl p-6 hidden">
            <h3 class="text-xl font-bold text-white mb-4 flex items-center">
                🎉 Produits détectés automatiquement
                <span id="points-earned" class="ml-3 bg-gradient-to-r from-yellow-400 to-orange-500 text-black px-3 py-1 rounded-full text-sm font-black"></span>
            </h3>
            
            <!-- Info magasin -->
            <div id="store-info" class="bg-blue-500/20 border border-blue-500/30 rounded-xl p-4 mb-6 hidden">
                <h4 class="text-blue-400 font-semibold mb-2">🏪 Informations magasin</h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                    <div>
                        <span class="text-gray-400">Magasin:</span>
                        <span id="store-name" class="text-white ml-2 font-medium"></span>
                    </div>
                    <div>
                        <span class="text-gray-400">Lieu:</span>
                        <span id="store-location" class="text-white ml-2 font-medium"></span>
                    </div>
                    <div>
                        <span class="text-gray-400">Date:</span>
                        <span id="store-date" class="text-white ml-2 font-medium"></span>
                    </div>
                </div>
            </div>
            
            <div id="detected-products" class="space-y-3 mb-6">
                <!-- Les produits détectés seront ajoutés ici par JavaScript -->
            </div>

            <div class="text-center">
                <button onclick="confirmContributions(event)" class="bg-gradient-to-r from-green-500 to-emerald-600 text-white font-bold py-3 px-8 rounded-xl hover:scale-105 transition-transform text-lg">
                    ✅ Confirmer ces contributions
                </button>
            </div>
        </div>

        <!-- Erreur (caché par défaut) -->
        <div id="scan-error" class="bg-red-500/20 border border-red-500/30 rounded-2xl p-6 hidden">
            <h3 class="text-xl font-bold text-red-400 mb-2 flex items-center">
                ❌ Erreur d'analyse
            </h3>
            <p id="error-message" class="text-gray-300 mb-4"></p>
            <button onclick="retakePhoto()" class="bg-gradient-to-r from-gray-600 to-gray-700 text-white font-bold py-2 px-4 rounded-xl hover:scale-105 transition-transform">
                🔄 Réessayer
            </button>
        </div>
    </div>
</div>

<script>
let currentStream = null;
let capturedImageFile = null;
let detectedData = null;

async function startCamera() {
    try {
        const constraints = {
            video: {
                facingMode: 'environment', // Caméra arrière préférée
                width: { ideal: 1920 },
                height: { ideal: 1080 }
            }
        };
        
        currentStream = await navigator.mediaDevices.getUserMedia(constraints);
        const video = document.getElementById('camera-video');
        const placeholder = document.getElementById('placeholder');
        const actionButtons = document.getElementById('action-buttons');
        
        video.srcObject = currentStream;
        video.style.display = 'block';
        placeholder.style.display = 'none';
        actionButtons.style.display = 'flex';
        
    } catch (error) {
        console.error('Erreur d\'accès à la caméra:', error);
        alert('Impossible d\'accéder à la caméra. Utilisez l\'option "Choisir une photo".');
    }
}

function handleFileSelect(event) {
    const file = event.target.files[0];
    if (file) {
        capturedImageFile = file;
        
        const reader = new FileReader();
        reader.onload = function(e) {
            const img = document.getElementById('image-preview');
            const placeholder = document.getElementById('placeholder');
            const actionButtons = document.getElementById('action-buttons');
            
            img.src = e.target.result;
            img.style.display = 'block';
            placeholder.style.display = 'none';
            actionButtons.style.display = 'flex';
        };
        reader.readAsDataURL(file);
    }
}

function capturePhoto() {
    const video = document.getElementById('camera-video');
    
    if (video.style.display !== 'none' && currentStream) {
        // Capturer depuis la caméra
        const canvas = document.createElement('canvas');
        const context = canvas.getContext('2d');
        
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        context.drawImage(video, 0, 0);
        
        // Convertir en blob
        canvas.toBlob(function(blob) {
            capturedImageFile = new File([blob], 'ticket.jpg', { type: 'image/jpeg' });
        }, 'image/jpeg', 0.8);
        
        // Arrêter la caméra
        if (currentStream) {
            currentStream.getTracks().forEach(track => track.stop());
            currentStream = null;
        }
    }
    
    // Démarrer l'analyse après un petit délai pour s'assurer que le fichier est prêt
    setTimeout(() => {
        analyzeTicket();
    }, 500);
}

async function analyzeTicket() {
    if (!capturedImageFile) {
        alert('Aucune image sélectionnée');
        return;
    }

    const overlay = document.getElementById('scan-overlay');
    const actionButtons = document.getElementById('action-buttons');
    const statusElement = document.getElementById('scan-status');
    const errorDiv = document.getElementById('scan-error');
    const resultsDiv = document.getElementById('scan-results');
    
    // Cacher les erreurs précédentes
    errorDiv.style.display = 'none';
    resultsDiv.style.display = 'none';
    
    overlay.style.display = 'flex';
    actionButtons.style.display = 'none';
    
    try {
        statusElement.textContent = 'Envoi vers le système...';
        
        const formData = new FormData();
        formData.append('ticket_image', capturedImageFile);
        
        const response = await fetch('{{ route("contribute.scan-ticket") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });
        
        const result = await response.json();
        
        overlay.style.display = 'none';
        
        if (result.success && result.data) {
            detectedData = result.data;
            displayResults(result.data);
        } else {
            showError(result.message || 'Erreur lors de l\'analyse du ticket');
        }
        
    } catch (error) {
        console.error('Erreur:', error);
        overlay.style.display = 'none';
        showError('Erreur de connexion. Vérifiez votre connexion internet.');
    }
}

function displayResults(data) {
    const resultsDiv = document.getElementById('scan-results');
    const pointsSpan = document.getElementById('points-earned');
    const productsDiv = document.getElementById('detected-products');
    const storeInfoDiv = document.getElementById('store-info');
    
    resultsDiv.style.display = 'block';
    
    // Afficher les infos magasin si disponibles
    if (data.store_info) {
        const storeNameEl = document.getElementById('store-name');
        const storeLocationEl = document.getElementById('store-location');
        const storeDateEl = document.getElementById('store-date');
        
        storeNameEl.textContent = data.store_info.name || 'Non détecté';
        storeLocationEl.textContent = data.store_info.location || 'Non détecté';
        storeDateEl.textContent = data.store_info.date || 'Non détecté';
        
        storeInfoDiv.style.display = 'block';
    }
    
    // Calculer les points
    const totalPoints = (data.products?.length || 0) * 5;
    pointsSpan.textContent = `+${totalPoints} XP`;
    
    // Afficher les produits
    productsDiv.innerHTML = '';
    
    if (data.products && data.products.length > 0) {
        data.products.forEach((product, index) => {
            const productDiv = document.createElement('div');
            productDiv.className = 'bg-white/5 rounded-xl p-4 flex justify-between items-center border border-white/10';
            productDiv.innerHTML = `
                <div class="flex-1">
                    <h4 class="text-white font-semibold">${product.name}</h4>
                    <div class="flex items-center space-x-4 text-sm text-gray-400 mt-1">
                        <span>Quantité: ${product.quantity || 1}</span>
                        ${product.category ? `<span class="bg-blue-500/20 text-blue-400 px-2 py-1 rounded">${product.category}</span>` : ''}
                    </div>
                </div>
                <div class="text-right">
                    <span class="text-green-400 font-bold text-lg">${parseFloat(product.price).toFixed(2)} €</span>
                </div>
            `;
            productsDiv.appendChild(productDiv);
        });
        
        // Ajouter le total si disponible
        if (data.total) {
            const totalDiv = document.createElement('div');
            totalDiv.className = 'bg-green-500/20 border border-green-500/30 rounded-xl p-4 flex justify-between items-center font-bold';
            totalDiv.innerHTML = `
                <span class="text-white text-lg">Total ticket</span>
                <span class="text-green-400 text-xl">${parseFloat(data.total).toFixed(2)} €</span>
            `;
            productsDiv.appendChild(totalDiv);
        }
    } else {
        productsDiv.innerHTML = '<p class="text-gray-400 text-center py-4">Aucun produit détecté dans ce ticket</p>';
    }
}

function showError(message) {
    const errorDiv = document.getElementById('scan-error');
    const errorMessage = document.getElementById('error-message');
    
    errorMessage.textContent = message;
    errorDiv.style.display = 'block';
}

async function confirmContributions(event) {
    if (!detectedData || !detectedData.products || detectedData.products.length === 0) {
        alert('Aucun produit à confirmer');
        return;
    }
    
    try {
        console.log('🛒 Données à envoyer:', detectedData);
        
        const formData = {
            products: detectedData.products,
            store_name: detectedData.store_info?.name || null,
            location: detectedData.store_info?.location || null
        };
        
        console.log('📦 FormData:', formData);
        
        // Récupérer le bouton de manière plus sûre
        const button = event ? event.target : document.querySelector('.confirm-button');
        const originalText = button ? button.textContent : '✅ Confirmer ces contributions';
        
        if (button) {
            button.textContent = '⏳ Enregistrement...';
            button.disabled = true;
        }
        
        const response = await fetch('{{ route("contribute.store-bulk") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(formData)
        });
        
        console.log('📡 Réponse HTTP:', response.status, response.statusText);
        
        if (!response.ok) {
            const errorText = await response.text();
            console.error('❌ Erreur HTTP:', errorText);
            throw new Error(`Erreur ${response.status}: ${response.statusText}`);
        }
        
        const result = await response.json();
        console.log('✅ Résultat:', result);
        
        // Restaurer le bouton
        if (button) {
            button.textContent = originalText;
            button.disabled = false;
        }
        
        if (result.success) {
            // Afficher le succès et rediriger
            alert(`🎉 ${result.contributions_count} contributions ajoutées avec succès ! +${result.contributions_count * 5} XP`);
            
            // Afficher les nouveaux badges s'il y en a
            if (result.new_badges && result.new_badges.length > 0) {
                const badgeNames = result.new_badges.map(b => b.name).join(', ');
                alert(`🏆 Nouveau(x) badge(s) débloqué(s): ${badgeNames}`);
            }
            
            window.location.href = '{{ route("contribute.index") }}';
        } else {
            alert('❌ Erreur lors de l\'enregistrement: ' + (result.message || 'Erreur inconnue'));
        }
        
    } catch (error) {
        console.error('❌ Erreur:', error);
        
        // Restaurer le bouton en cas d'erreur
        const button = event ? event.target : document.querySelector('.confirm-button');
        if (button) {
            button.textContent = '✅ Confirmer ces contributions';
            button.disabled = false;
        }
        
        alert('❌ Erreur de connexion lors de l\'enregistrement: ' + error.message);
    }
}

function retakePhoto() {
    // Réinitialiser l'interface
    document.getElementById('placeholder').style.display = 'block';
    document.getElementById('camera-video').style.display = 'none';
    document.getElementById('image-preview').style.display = 'none';
    document.getElementById('action-buttons').style.display = 'none';
    document.getElementById('scan-results').style.display = 'none';
    document.getElementById('scan-error').style.display = 'none';
    document.getElementById('scan-overlay').style.display = 'none';
    
    // Réinitialiser les variables
    capturedImageFile = null;
    detectedData = null;
    
    // Arrêter la caméra si active
    if (currentStream) {
        currentStream.getTracks().forEach(track => track.stop());
        currentStream = null;
    }
    
    // Réinitialiser l'input file
    document.getElementById('file-input').value = '';
}
</script>
@endsection 