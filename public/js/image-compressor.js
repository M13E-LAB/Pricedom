/**
 * Compresseur d'images JavaScript pour Pricedom
 * Réduit automatiquement les images > 1.5MB pour respecter les limites Railway
 */

class PricedomImageCompressor {
    constructor() {
        this.maxFileSize = 1.5 * 1024 * 1024; // 1.5MB en bytes
        this.quality = 0.8; // Qualité de compression (80%)
        this.maxDimension = 2048; // Dimension maximale (largeur/hauteur)
    }

    /**
     * Compresse une image si nécessaire
     * @param {File} file - Fichier image à compresser
     * @param {Function} callback - Callback avec le fichier compressé
     */
    compressImage(file, callback) {
        // Vérifier si c'est une image
        if (!file.type.startsWith('image/')) {
            callback(file);
            return;
        }

        // Si le fichier est déjà petit, pas de compression
        if (file.size <= this.maxFileSize) {
            callback(file);
            return;
        }

        // Compression silencieuse en cours

        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        const img = new Image();

        img.onload = () => {
            // Calculer les nouvelles dimensions
            let { width, height } = this.calculateDimensions(img.width, img.height);
            
            // Configurer le canvas
            canvas.width = width;
            canvas.height = height;

            // Dessiner l'image redimensionnée
            ctx.drawImage(img, 0, 0, width, height);

            // Convertir en blob avec compression
            canvas.toBlob((blob) => {
                if (blob && blob.size < file.size) {
                    // Créer un nouveau fichier avec le blob compressé
                    const compressedFile = new File([blob], file.name, {
                        type: file.type,
                        lastModified: Date.now()
                    });

                    callback(compressedFile);
                } else {
                    callback(file);
                }
            }, file.type, this.quality);
        };

        img.onerror = () => {
            callback(file);
        };

        // Charger l'image
        const reader = new FileReader();
        reader.onload = (e) => {
            img.src = e.target.result;
        };
        reader.readAsDataURL(file);
    }

    /**
     * Calcule les nouvelles dimensions en préservant le ratio
     */
    calculateDimensions(originalWidth, originalHeight) {
        let width = originalWidth;
        let height = originalHeight;

        // Redimensionner si trop grand
        if (width > this.maxDimension || height > this.maxDimension) {
            if (width > height) {
                height = (height * this.maxDimension) / width;
                width = this.maxDimension;
            } else {
                width = (width * this.maxDimension) / height;
                height = this.maxDimension;
            }
        }

        return { width: Math.round(width), height: Math.round(height) };
    }

    /**
     * Formate la taille de fichier en format lisible
     */
    formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    /**
     * Affiche une prévisualisation de l'image
     */
    showPreview(file, container) {
        const reader = new FileReader();
        reader.onload = (e) => {
            container.innerHTML = `
                <div class="image-preview">
                    <img src="${e.target.result}" style="max-width: 200px; max-height: 200px; border-radius: 8px;">
                    <p><strong>${file.name}</strong></p>
                    <p>Taille: ${this.formatFileSize(file.size)}</p>
                </div>
            `;
        };
        reader.readAsDataURL(file);
    }
}

// Instance globale
window.pricedomCompressor = new PricedomImageCompressor();

/**
 * Fonction utilitaire pour améliorer les inputs de fichier
 */
function enhanceFileInput(inputElement) {
    const previewContainer = document.createElement('div');
    previewContainer.className = 'zyma-preview-container';
    previewContainer.style.cssText = `
        margin-top: 10px;
        padding: 15px;
        border: 2px dashed #3498db;
        border-radius: 8px;
        text-align: center;
        background-color: #f8f9fa;
        display: none;
    `;
    
    inputElement.parentNode.insertBefore(previewContainer, inputElement.nextSibling);

    inputElement.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (!file) {
            previewContainer.style.display = 'none';
            return;
        }

        // Afficher la prévisualisation
        previewContainer.style.display = 'block';
        window.pricedomCompressor.showPreview(file, previewContainer);

        // Compression automatique silencieuse si nécessaire
    });
}

// Auto-amélioration des inputs de fichier au chargement de la page
document.addEventListener('DOMContentLoaded', function() {
    const fileInputs = document.querySelectorAll('input[type="file"][accept*="image"]');
    fileInputs.forEach(enhanceFileInput);
    
    // Compresseur d'images Zyma initialisé silencieusement
}); 