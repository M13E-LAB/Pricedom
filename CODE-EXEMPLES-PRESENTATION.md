# 💻 CODE EXEMPLES POUR PRÉSENTATION
## Snippets commentés à projeter

---

## 📦 1. CONTROLLER PHP - SCAN DE TICKET

```php
<?php
// app/Http/Controllers/ContributeController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contribution;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ContributeController extends Controller
{
    /**
     * 🎯 OBJECTIF : Recevoir un ticket, l'analyser avec l'IA, sauvegarder les produits
     * 
     * FLOW :
     * 1. Validation de l'image
     * 2. Stockage temporaire
     * 3. Appel du script Python
     * 4. Parsing du résultat JSON
     * 5. Sauvegarde en base de données
     */
    public function scanTicket(Request $request)
    {
        // ✅ ÉTAPE 1 : VALIDATION STRICTE
        $request->validate([
            'ticket_image' => 'required|image|mimes:jpeg,png,jpg|max:10240' // Max 10MB
        ]);

        try {
            // ✅ ÉTAPE 2 : STOCKAGE SÉCURISÉ
            $image = $request->file('ticket_image');
            $imagePath = $image->store('tickets', 'public'); // storage/app/public/tickets/
            $fullPath = storage_path('app/public/' . $imagePath);

            Log::info('📸 Image de ticket stockée', ['path' => $fullPath]);

            // ✅ ÉTAPE 3 : PRÉPARATION DU SCRIPT PYTHON
            $pythonScript = escapeshellarg(base_path('analyze_ticket.py')); // ⚠️ Sécurité : escapeshellarg
            $imageArg = escapeshellarg($fullPath);
            
            // 📝 Prompt détaillé pour l'IA
            $prompt = "Analyse ce ticket de caisse et extrait TOUS les produits avec leurs prix. "
                    . "Retourne UNIQUEMENT un JSON valide au format suivant : "
                    . '{"products": [{"name": "Nom du produit", "price": 3.49, "quantity": 1}]}';
            $promptArg = escapeshellarg($prompt);

            // ✅ ÉTAPE 4 : CONFIGURATION API KEY
            putenv('OPENAI_API_KEY=' . env('OPENAI_API_KEY')); // Passe la clé au Python

            Log::info('🤖 Lancement analyse OpenAI GPT-4o-mini');

            // ✅ ÉTAPE 5 : EXÉCUTION DU SCRIPT PYTHON
            $command = "python3 {$pythonScript} {$imageArg} {$promptArg}";
            $output = shell_exec($command); // ⚠️ Communication inter-langages

            Log::info('📥 Résultat Python reçu', ['output' => substr($output, 0, 200)]);

            // ✅ ÉTAPE 6 : PARSING DU JSON
            $result = json_decode($output, true);

            if (!$result || !isset($result['success']) || !$result['success']) {
                // ❌ Gestion d'erreur
                $errorMessage = $result['error'] ?? 'Erreur inconnue';
                Log::error('❌ Erreur analyse ticket', ['error' => $errorMessage]);
                
                return response()->json([
                    'success' => false,
                    'error' => $errorMessage
                ], 500);
            }

            // ✅ ÉTAPE 7 : EXTRACTION DES PRODUITS
            $content = $result['content'];
            
            // Parsing basique du JSON dans le texte (peut être amélioré avec regex)
            $products = $this->extractProductsFromAIResponse($content);

            // ✅ ÉTAPE 8 : SAUVEGARDE EN BASE DE DONNÉES
            $savedProducts = [];
            foreach ($products as $product) {
                $contribution = Contribution::create([
                    'user_id' => Auth::id(),
                    'product_name' => $product['name'],
                    'price' => $product['price'],
                    'quantity' => $product['quantity'] ?? 1,
                    'contribution_type' => 'scan', // 📷 Type = scan (vs manual)
                    'receipt_image' => $imagePath,
                    'verified' => false
                ]);
                
                $savedProducts[] = $contribution;
            }

            Log::info('✅ Produits sauvegardés', ['count' => count($savedProducts)]);

            // ✅ ÉTAPE 9 : RETOUR JSON AU FRONTEND
            return response()->json([
                'success' => true,
                'message' => count($savedProducts) . ' produit(s) ajouté(s) avec succès',
                'products' => $savedProducts,
                'ai_response' => $content // Pour debug
            ]);

        } catch (\Exception $e) {
            // ❌ GESTION D'ERREUR GLOBALE
            Log::error('❌ Exception scan ticket', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Erreur serveur : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 🔧 Extraction des produits depuis la réponse de l'IA
     * (Parsing basique - peut être amélioré)
     */
    private function extractProductsFromAIResponse($aiResponse)
    {
        // Recherche d'un JSON dans le texte
        if (preg_match('/\{.*"products".*\}/s', $aiResponse, $matches)) {
            $json = json_decode($matches[0], true);
            if ($json && isset($json['products'])) {
                return $json['products'];
            }
        }
        
        // Fallback : retour vide si pas de JSON trouvé
        return [];
    }
}
```

**🎤 POINTS À EXPLIQUER EN PRÉSENTATION :**
1. ✅ **Sécurité** : `escapeshellarg()` pour éviter injection de commandes
2. ✅ **Validation** : Image obligatoire, max 10MB, formats jpeg/png/jpg uniquement
3. ✅ **Communication PHP ↔ Python** : `shell_exec()` + JSON pour l'échange
4. ✅ **Gestion d'erreur** : Try/catch + logs détaillés
5. ✅ **Traçabilité** : `Log::info()` à chaque étape

---

## 🐍 2. SCRIPT PYTHON - ANALYSE IA

```python
#!/usr/bin/env python3
# analyze_ticket.py
"""
🎯 OBJECTIF : Analyser une image de ticket avec OpenAI GPT-4o-mini (Vision)

INPUTS :
  - sys.argv[1] : Chemin de l'image
  - sys.argv[2] : Prompt pour l'IA
  - ENV['OPENAI_API_KEY'] : Clé API OpenAI

OUTPUT :
  - JSON sur stdout : {"success": true/false, "content": "...", "error": "..."}
"""

import sys
import json
import os
import base64
import requests

def analyze_ticket(image_path, prompt):
    """
    🔍 Analyse un ticket de caisse avec OpenAI GPT-4o-mini
    
    Args:
        image_path (str): Chemin vers l'image du ticket
        prompt (str): Instructions pour l'IA
        
    Returns:
        dict: {"success": bool, "content": str, "error": str}
    """
    
    # ✅ ÉTAPE 1 : VÉRIFICATION DE LA CLÉ API
    api_key = os.getenv('OPENAI_API_KEY')
    if not api_key:
        return {
            'success': False, 
            'error': 'OPENAI_API_KEY manquant dans l\'environnement'
        }

    try:
        # ✅ ÉTAPE 2 : LECTURE ET ENCODAGE DE L'IMAGE EN BASE64
        # Pourquoi base64 ? Car l'API OpenAI attend les images en format data URI
        with open(image_path, 'rb') as image_file:
            image_bytes = image_file.read()
            image_data = base64.b64encode(image_bytes).decode('utf-8')
        
        print(f"[DEBUG] Image encodée : {len(image_data)} caractères base64", file=sys.stderr)

        # ✅ ÉTAPE 3 : CONSTRUCTION DE LA REQUÊTE OPENAI
        headers = {
            "Content-Type": "application/json",
            "Authorization": f"Bearer {api_key}"
        }
        
        url = "https://api.openai.com/v1/chat/completions"

        # 📦 PAYLOAD : Format Chat Completion avec Vision
        payload = {
            "model": "gpt-4o-mini",  # 🚀 Modèle vision multimodal (texte + image)
            "messages": [
                {
                    "role": "user",
                    "content": [
                        {
                            "type": "text", 
                            "text": prompt  # 📝 Instructions pour l'IA
                        },
                        {
                            "type": "image_url",
                            "image_url": {
                                # 🖼️ Image encodée en data URI
                                "url": f"data:image/jpeg;base64,{image_data}"
                            }
                        }
                    ]
                }
            ],
            "max_tokens": 1500,      # 📊 Limite de tokens (sortie)
            "temperature": 0.1       # 🎯 Peu de créativité = plus précis
        }

        print(f"[DEBUG] Envoi requête à OpenAI...", file=sys.stderr)

        # ✅ ÉTAPE 4 : APPEL API OPENAI
        response = requests.post(
            url, 
            headers=headers, 
            json=payload,
            timeout=60  # ⏱️ Timeout de 60 secondes
        )
        
        # ⚠️ Vérification du code HTTP
        response.raise_for_status()  # Lève une exception si 4xx/5xx

        # ✅ ÉTAPE 5 : EXTRACTION DU RÉSULTAT
        response_json = response.json()
        
        print(f"[DEBUG] Réponse OpenAI reçue", file=sys.stderr)
        
        if 'choices' in response_json and response_json['choices']:
            result_text = response_json['choices'][0]['message']['content']
            
            # ✅ Succès !
            return {
                'success': True, 
                'content': result_text
            }
        else:
            # ❌ Réponse vide ou malformée
            return {
                'success': False, 
                'error': 'Aucune réponse valide d\'OpenAI'
            }

    except requests.exceptions.RequestException as e:
        # ❌ Erreur HTTP (timeout, connexion, etc.)
        return {
            'success': False, 
            'error': f"Erreur API OpenAI: {str(e)}"
        }
    except FileNotFoundError:
        # ❌ Image introuvable
        return {
            'success': False, 
            'error': f"Image non trouvée : {image_path}"
        }
    except Exception as e:
        # ❌ Erreur générique
        return {
            'success': False, 
            'error': f"Erreur inattendue : {str(e)}"
        }


# 🚀 POINT D'ENTRÉE DU SCRIPT
if __name__ == '__main__':
    # ✅ VÉRIFICATION DES ARGUMENTS
    if len(sys.argv) < 3:
        error_result = {
            'success': False, 
            'error': 'Usage: python analyze_ticket.py <image_path> <prompt>'
        }
        print(json.dumps(error_result))
        sys.exit(1)

    # 📥 RÉCUPÉRATION DES ARGUMENTS
    image_path = sys.argv[1]
    prompt = sys.argv[2]
    
    print(f"[DEBUG] Analyse de : {image_path}", file=sys.stderr)
    
    # 🔍 ANALYSE DU TICKET
    result = analyze_ticket(image_path, prompt)
    
    # 📤 OUTPUT JSON SUR STDOUT (lu par PHP)
    print(json.dumps(result))  # ⚠️ Très important : stdout uniquement JSON !
```

**🎤 POINTS À EXPLIQUER EN PRÉSENTATION :**
1. ✅ **Base64** : Format requis par OpenAI pour les images
2. ✅ **GPT-4o-mini** : Modèle multimodal (texte + image), moins cher que GPT-4
3. ✅ **Temperature = 0.1** : Peu de créativité → résultats plus déterministes
4. ✅ **Timeout** : 60 secondes pour éviter blocage infini
5. ✅ **Communication JSON** : Le PHP lit le stdout du Python

---

## 🎨 3. FRONTEND JAVASCRIPT - UPLOAD & AFFICHAGE

```javascript
// resources/views/contribute/scan.blade.php
/**
 * 🎯 OBJECTIF : Gérer l'upload de ticket et l'affichage des résultats
 * 
 * FLOW :
 * 1. User sélectionne/capture une image
 * 2. Compression côté client (optionnel)
 * 3. Upload vers le backend via fetch()
 * 4. Affichage loader pendant l'analyse
 * 5. Affichage des résultats ou erreur
 */

// 📸 CAPTURE OU UPLOAD D'IMAGE
const imageInput = document.getElementById('imageInput');
const previewImg = document.getElementById('previewImg');
let capturedImageFile = null;

// Prévisualisation de l'image
imageInput.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        capturedImageFile = file;
        
        // 🖼️ Affichage de la preview
        const reader = new FileReader();
        reader.onload = function(event) {
            previewImg.src = event.target.result;
            previewImg.style.display = 'block';
        };
        reader.readAsDataURL(file);
    }
});

// 🚀 ANALYSE DU TICKET
async function analyzeTicket() {
    // ✅ VALIDATION : Image obligatoire
    if (!capturedImageFile) {
        showError('⚠️ Veuillez d\'abord sélectionner ou capturer une image');
        return;
    }

    // 🔄 AFFICHAGE DU LOADER
    const overlay = document.getElementById('loadingOverlay');
    const resultsDiv = document.getElementById('results');
    
    overlay.style.display = 'flex';
    resultsDiv.innerHTML = '';

    try {
        // ✅ CRÉATION DU FORMDATA
        const formData = new FormData();
        formData.append('ticket_image', capturedImageFile);

        console.log('📤 Envoi de l\'image au backend...');

        // ✅ REQUÊTE FETCH VERS LARAVEL
        const response = await fetch('{{ route("contribute.scan-ticket") }}', {
            method: 'POST',
            body: formData,
            headers: {
                // 🔒 CSRF Token (sécurité Laravel)
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });

        console.log('📥 Réponse reçue, status:', response.status);

        // ✅ PARSING JSON
        const result = await response.json();

        // ✅ MASQUAGE DU LOADER
        overlay.style.display = 'none';

        // ✅ TRAITEMENT DU RÉSULTAT
        if (result.success) {
            console.log('✅ Succès !', result);
            displayProducts(result.products, result.ai_response);
        } else {
            console.error('❌ Erreur backend:', result.error);
            showError(result.error || 'Erreur lors de l\'analyse');
        }

    } catch (error) {
        // ❌ ERREUR RÉSEAU OU PARSING
        console.error('❌ Erreur fetch:', error);
        overlay.style.display = 'none';
        showError('Erreur de connexion. Vérifiez votre connexion internet.');
    }
}

// 📊 AFFICHAGE DES PRODUITS EXTRAITS
function displayProducts(products, aiResponse) {
    const resultsDiv = document.getElementById('results');
    
    if (!products || products.length === 0) {
        resultsDiv.innerHTML = `
            <div class="bg-yellow-500/20 border border-yellow-500/50 rounded-lg p-4">
                <p class="text-yellow-300">⚠️ Aucun produit détecté sur ce ticket</p>
                <details class="mt-2">
                    <summary class="cursor-pointer text-yellow-400">Voir la réponse de l'IA</summary>
                    <pre class="text-xs text-white/70 mt-2 overflow-auto">${aiResponse}</pre>
                </details>
            </div>
        `;
        return;
    }

    // ✅ CONSTRUCTION DU HTML DES PRODUITS
    let html = `
        <div class="bg-green-500/20 border border-green-500/50 rounded-lg p-4 mb-4">
            <p class="text-green-300 font-bold">✅ ${products.length} produit(s) détecté(s)</p>
        </div>
        <div class="space-y-3">
    `;

    products.forEach((product, index) => {
        html += `
            <div class="bg-white/5 rounded-lg p-4 hover:bg-white/10 transition-all">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <h4 class="text-white font-medium">${escapeHtml(product.product_name)}</h4>
                        <p class="text-white/60 text-sm">
                            Quantité : ${product.quantity || 1}
                        </p>
                    </div>
                    <div class="text-right">
                        <p class="text-2xl font-bold text-green-400">
                            ${parseFloat(product.price).toFixed(2)}€
                        </p>
                    </div>
                </div>
            </div>
        `;
    });

    html += `
        </div>
        <div class="mt-6 text-center">
            <button onclick="window.location.href='{{ route('prices.dashboard') }}'" 
                    class="bg-gradient-to-r from-orange-500 to-pink-500 text-white px-8 py-3 rounded-lg hover:scale-105 transition-transform">
                📊 Voir le Dashboard
            </button>
        </div>
    `;

    resultsDiv.innerHTML = html;
}

// ❌ AFFICHAGE D'ERREUR
function showError(message) {
    const resultsDiv = document.getElementById('results');
    resultsDiv.innerHTML = `
        <div class="bg-red-500/20 border border-red-500/50 rounded-lg p-4">
            <p class="text-red-300">❌ ${escapeHtml(message)}</p>
            <button onclick="location.reload()" 
                    class="mt-4 bg-red-500/30 hover:bg-red-500/50 text-white px-4 py-2 rounded-lg transition-all">
                🔄 Réessayer
            </button>
        </div>
    `;
}

// 🔒 ÉCHAPPEMENT HTML (Sécurité XSS)
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
```

**🎤 POINTS À EXPLIQUER EN PRÉSENTATION :**
1. ✅ **FormData** : Pour upload de fichiers multipart
2. ✅ **CSRF Token** : Sécurité Laravel obligatoire
3. ✅ **Async/Await** : Code asynchrone lisible
4. ✅ **UX** : Loader pendant l'attente, feedback visuel
5. ✅ **Sécurité XSS** : `escapeHtml()` sur le contenu user

---

## 📊 4. DASHBOARD CONTROLLER - STATISTIQUES

```php
<?php
// app/Http/Controllers/DashboardController.php

namespace App\Http\Controllers;

use App\Models\Contribution;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * 📊 Dashboard des prix avec statistiques et graphiques
     */
    public function index()
    {
        // ✅ STATISTIQUES GLOBALES (requêtes optimisées)
        
        // 1️⃣ Total de contributions
        $totalContributions = Contribution::count();
        
        // 2️⃣ Nombre d'utilisateurs actifs (distincts)
        $totalUsers = Contribution::distinct('user_id')->count();
        
        // 3️⃣ Prix moyen
        $averagePrice = Contribution::avg('price');
        
        // 4️⃣ Valeur totale (prix × quantité)
        $totalValue = Contribution::sum(DB::raw('price * quantity'));
        
        // ✅ CONTRIBUTIONS RÉCENTES (avec relation Eloquent)
        $recentContributions = Contribution::with('user')  // Eager loading !
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        // ✅ TOP PRODUITS (agrégation)
        $topProducts = Contribution::select(
                'product_name',
                DB::raw('COUNT(*) as count'),
                DB::raw('AVG(price) as avg_price')
            )
            ->groupBy('product_name')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();
        
        // ✅ TOP MAGASINS
        $topStores = Contribution::select(
                'store_name',
                DB::raw('COUNT(*) as count')
            )
            ->whereNotNull('store_name')
            ->groupBy('store_name')
            ->orderBy('count', 'desc')
            ->limit(5)
            ->get();
        
        // ✅ CONTRIBUTIONS PAR CATÉGORIE
        $contributionsByCategory = Contribution::select(
                'category',
                DB::raw('COUNT(*) as count')
            )
            ->whereNotNull('category')
            ->groupBy('category')
            ->orderBy('count', 'desc')
            ->get();
        
        // ✅ CONTRIBUTIONS PAR TYPE (scan vs manual)
        $contributionsByType = Contribution::select(
                'contribution_type',
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('contribution_type')
            ->get();
        
        // ✅ ÉVOLUTION SUR 7 JOURS
        $contributionsByDay = Contribution::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();
        
        // ✅ RETOUR VERS LA VUE BLADE
        return view('dashboard.index', compact(
            'totalContributions',
            'totalUsers',
            'averagePrice',
            'totalValue',
            'recentContributions',
            'topProducts',
            'topStores',
            'contributionsByCategory',
            'contributionsByType',
            'contributionsByDay'
        ));
    }
}
```

**🎤 POINTS À EXPLIQUER EN PRÉSENTATION :**
1. ✅ **Eloquent ORM** : Abstraction DB élégante
2. ✅ **Eager Loading** : `with('user')` pour éviter N+1 queries
3. ✅ **Agrégations SQL** : `COUNT()`, `AVG()`, `SUM()` pour performances
4. ✅ **Compact()** : Passe toutes les variables à la vue

---

## 🎨 5. VUE BLADE - GRAPHIQUE CHART.JS

```html
<!-- resources/views/dashboard/index.blade.php -->

{{-- 📈 GRAPHIQUE DES CONTRIBUTIONS PAR JOUR --}}
<div class="bg-black/40 backdrop-blur-lg border border-white/10 rounded-2xl p-6">
    <h3 class="text-xl font-bold text-white mb-4">📈 Contributions (7 derniers jours)</h3>
    
    {{-- Canvas pour Chart.js --}}
    <canvas id="contributionsByDayChart"></canvas>
</div>

{{-- 📊 SCRIPT CHART.JS --}}
<script>
    // ✅ RÉCUPÉRATION DU CONTEXTE CANVAS
    const ctx = document.getElementById('contributionsByDayChart').getContext('2d');
    
    // ✅ DONNÉES DEPUIS BLADE (PHP → JavaScript)
    const labels = @json($contributionsByDay->pluck('date'));    // ['2025-01-10', '2025-01-11', ...]
    const data = @json($contributionsByDay->pluck('count'));     // [5, 12, 8, ...]
    
    // ✅ CRÉATION DU GRAPHIQUE
    new Chart(ctx, {
        type: 'line',  // 📈 Type : ligne
        data: {
            labels: labels,
            datasets: [{
                label: 'Contributions',
                data: data,
                
                // 🎨 STYLE
                borderColor: 'rgb(251, 146, 60)',           // Orange
                backgroundColor: 'rgba(251, 146, 60, 0.1)', // Orange translucide
                tension: 0.4,     // Courbe lisse
                fill: true,       // Remplissage sous la courbe
                pointRadius: 5,   // Taille des points
                pointHoverRadius: 8  // Taille au survol
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            
            // 🎨 LÉGENDE
            plugins: {
                legend: {
                    labels: { 
                        color: 'white',
                        font: { size: 14 }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleColor: '#fb923c',
                    bodyColor: 'white'
                }
            },
            
            // 📊 AXES
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { 
                        color: 'white',
                        stepSize: 1  // Incréments de 1
                    },
                    grid: { 
                        color: 'rgba(255, 255, 255, 0.1)'  // Grille subtile
                    }
                },
                x: {
                    ticks: { 
                        color: 'white' 
                    },
                    grid: { 
                        color: 'rgba(255, 255, 255, 0.1)' 
                    }
                }
            }
        }
    });
</script>
```

**🎤 POINTS À EXPLIQUER EN PRÉSENTATION :**
1. ✅ **@json()** : Blade directive pour passer PHP → JavaScript
2. ✅ **Chart.js** : Librairie légère pour graphiques interactifs
3. ✅ **Responsive** : S'adapte automatiquement à la taille de l'écran
4. ✅ **Customisation** : Couleurs, styles, tooltips personnalisés

---

## 🗃️ 6. MODÈLE ELOQUENT - CONTRIBUTION

```php
<?php
// app/Models/Contribution.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contribution extends Model
{
    use HasFactory;

    /**
     * 📝 ATTRIBUTS ASSIGNABLES EN MASSE (Mass Assignment)
     * ⚠️ Sécurité : Seuls ces champs peuvent être remplis via create()
     */
    protected $fillable = [
        'user_id',
        'product_name',
        'product_barcode',
        'price',
        'quantity',
        'category',
        'store_name',
        'location',
        'notes',
        'receipt_image',
        'contribution_type',  // 'scan' ou 'manual'
        'verified'
    ];

    /**
     * 🔄 TYPE CASTING AUTOMATIQUE
     * Laravel convertit automatiquement les types
     */
    protected $casts = [
        'price' => 'decimal:2',      // Prix avec 2 décimales
        'verified' => 'boolean',     // Booléen (0/1 → true/false)
        'quantity' => 'integer'      // Entier
    ];

    /**
     * 🔗 RELATION : Une contribution appartient à un utilisateur
     * Usage : $contribution->user->name
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 🏷️ SCOPE : Contributions vérifiées uniquement
     * Usage : Contribution::verified()->get()
     */
    public function scopeVerified($query)
    {
        return $query->where('verified', true);
    }

    /**
     * 📷 SCOPE : Contributions scannées (avec IA)
     * Usage : Contribution::scanned()->get()
     */
    public function scopeScanned($query)
    {
        return $query->where('contribution_type', 'scan');
    }

    /**
     * 💰 ACCESSOR : Prix formaté pour affichage
     * Usage : $contribution->formatted_price → "3,45 €"
     */
    public function getFormattedPriceAttribute()
    {
        return number_format($this->price, 2, ',', ' ') . ' €';
    }

    /**
     * 📅 ACCESSOR : Date formatée en français
     * Usage : $contribution->formatted_date → "il y a 2 heures"
     */
    public function getFormattedDateAttribute()
    {
        return $this->created_at->diffForHumans();
    }
}
```

**🎤 POINTS À EXPLIQUER EN PRÉSENTATION :**
1. ✅ **Mass Assignment** : `$fillable` pour sécurité
2. ✅ **Casts** : Type casting automatique
3. ✅ **Relations** : Eloquent gère les jointures SQL
4. ✅ **Scopes** : Méthodes réutilisables pour requêtes
5. ✅ **Accessors** : Propriétés virtuelles calculées

---

**Tous ces snippets sont prêts à être projetés pendant ta présentation !** 🚀

Tu peux aussi les utiliser comme base pour tes explications techniques.

