<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Exception;

class CloudflareR2Service
{
    protected $disk;

    public function __construct()
    {
        $this->disk = Storage::disk('r2');
    }

    /**
     * Upload une image et retourne l'URL
     */
    public function uploadImage(UploadedFile $file, string $directory = 'images'): string
    {
        // Générer un nom unique pour le fichier
        $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $path = $directory . '/' . $filename;

        // Upload vers R2
        $result = $this->disk->putFileAs($directory, $file, $filename, 'public');

        if (!$result) {
            throw new Exception('Failed to upload image to R2');
        }

        return $this->getPublicUrl($path);
    }

    /**
     * Upload du contenu brut
     */
    public function uploadContent(string $content, string $path): bool
    {
        return $this->disk->put($path, $content);
    }

    /**
     * Obtenir l'URL publique d'un fichier
     */
    public function getPublicUrl(string $path): string
    {
        // Utiliser l'URL publique configurée si disponible
        $publicUrl = env('R2_PUBLIC_URL');
        
        if ($publicUrl) {
            return rtrim($publicUrl, '/') . '/' . ltrim($path, '/');
        }
        
        // Fallback : essayer d'utiliser l'URL du disk
        try {
            $url = $this->disk->url($path);
            // Si l'URL contient r2.cloudflarestorage.com, on doit la remplacer par une URL publique
            // Utiliser la route proxy locale
            if (strpos($url, 'r2.cloudflarestorage.com') !== false) {
                // Retourner une URL qui passera par un proxy local
                return env('APP_URL', 'http://localhost') . '/r2-image/' . urlencode($path);
            }
            return $url;
        } catch (Exception $e) {
            // Si tout échoue, retourner URL proxy
            return env('APP_URL', 'http://localhost') . '/r2-image/' . urlencode($path);
        }
    }

    /**
     * Vérifier si un fichier existe
     */
    public function exists(string $path): bool
    {
        return $this->disk->exists($path);
    }

    /**
     * Supprimer un fichier
     */
    public function delete(string $path): bool
    {
        return $this->disk->delete($path);
    }

    /**
     * Obtenir le contenu d'un fichier
     */
    public function getContent(string $path): string
    {
        return $this->disk->get($path);
    }

    /**
     * Lister les fichiers dans un dossier
     */
    public function listFiles(string $directory = ''): array
    {
        return $this->disk->allFiles($directory);
    }

    /**
     * Obtenir la taille d'un fichier
     */
    public function getFileSize(string $path): int
    {
        return $this->disk->size($path);
    }

    /**
     * Upload multiple images
     */
    public function uploadMultipleImages(array $files, string $directory = 'images'): array
    {
        $urls = [];
        
        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $urls[] = $this->uploadImage($file, $directory);
            }
        }
        
        return $urls;
    }

    /**
     * Upload une image de profil utilisateur
     */
    public function uploadUserAvatar(UploadedFile $file, int $userId): string
    {
        $directory = "avatars/user_{$userId}";
        
        // Supprimer l'ancien avatar s'il existe
        $oldAvatars = $this->listFiles($directory);
        foreach ($oldAvatars as $oldAvatar) {
            $this->delete($oldAvatar);
        }
        
        return $this->uploadImage($file, $directory);
    }

    /**
     * Upload une image de produit
     */
    public function uploadProductImage(UploadedFile $file, string $productId = null): string
    {
        $directory = $productId ? "products/{$productId}" : 'products';
        return $this->uploadImage($file, $directory);
    }

    /**
     * Nettoyer les anciens fichiers temporaires
     */
    public function cleanupTempFiles(int $olderThanDays = 7): array
    {
        $deleted = [];
        $tempFiles = $this->listFiles('temp');
        
        foreach ($tempFiles as $file) {
            try {
                $lastModified = $this->disk->lastModified($file);
                $cutoffTime = now()->subDays($olderThanDays)->timestamp;
                
                if ($lastModified < $cutoffTime) {
                    if ($this->delete($file)) {
                        $deleted[] = $file;
                    }
                }
            } catch (Exception $e) {
                // Ignorer les erreurs de métadonnées
            }
        }
        
        return $deleted;
    }

    /**
     * Obtenir les statistiques du bucket
     */
    public function getStats(): array
    {
        try {
            $allFiles = $this->listFiles();
            $totalSize = 0;
            $fileTypes = [];
            
            foreach ($allFiles as $file) {
                try {
                    $size = $this->getFileSize($file);
                    $totalSize += $size;
                    
                    $extension = pathinfo($file, PATHINFO_EXTENSION);
                    $fileTypes[$extension] = ($fileTypes[$extension] ?? 0) + 1;
                } catch (Exception $e) {
                    // Ignorer les erreurs de métadonnées
                }
            }
            
            return [
                'total_files' => count($allFiles),
                'total_size_bytes' => $totalSize,
                'total_size_mb' => round($totalSize / 1024 / 1024, 2),
                'file_types' => $fileTypes,
            ];
        } catch (Exception $e) {
            return [
                'error' => $e->getMessage(),
            ];
        }
    }
} 