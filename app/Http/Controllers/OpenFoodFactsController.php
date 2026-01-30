<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class OpenFoodFactsController extends Controller
{
    protected $priceApiUrl = 'https://prices.openfoodfacts.org/api/v1/prices';
    protected $productApiUrl = 'https://world.openfoodfacts.org/api/v0/product/';
    protected $locationsApiUrl = 'https://prices.openfoodfacts.org/api/v1/locations';
    protected $productsApiUrl = 'https://prices.openfoodfacts.org/api/v1/products';

    public function index()
    {
        return view('products.search');
    }

    public function fetch(Request $request)
    {
        $request->validate([
            'product_code' => 'required|string'
        ]);
        
        $productCode = $request->input('product_code');

        try {
            // Fetch price data
            $priceResponse = Http::withOptions([
                'verify' => false, // Disable SSL verification
            ])->get($this->priceApiUrl, [
                'product_code' => $productCode
            ]);
            
            // Fetch product data
            $productResponse = Http::withOptions([
                'verify' => false, // Disable SSL verification
            ])->get($this->productApiUrl . $productCode . '.json');

            if ($priceResponse->successful() && $productResponse->successful()) {
                $priceData = $priceResponse->json();
                $productData = $productResponse->json();

                $prices = $this->processPrices($priceData['items']);
                $stats = $this->calculateStats($prices);
                
                $productInfo = $this->getProductInfo($productData);
                
                // Debug data
                // dd([
                //     'priceData' => $priceData,
                //     'productData' => $productData,
                //     'prices' => $prices,
                //     'stats' => $stats,
                //     'productInfo' => $productInfo
                // ]);

                return view('products.show', compact('prices', 'productCode', 'stats', 'productInfo'));
            } else {
                return back()->with('error', 'Failed to fetch product data');
            }
        } catch (\Exception $e) {
            return back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    private function processPrices($items)
    {
        return collect($items)->map(function ($item) {
            return [
                'store' => $item['location']['osm_name'] ?? 'Unknown',
                'price' => $item['price'],
                'date' => $item['date'],
                'location' => ($item['location']['osm_address_city'] ?? '') . ', ' . ($item['location']['osm_address_country'] ?? ''),
            ];
        })->sortByDesc('date')->values()->all();
    }

    private function calculateStats($prices)
    {
        $priceValues = array_column($prices, 'price');
        return [
            'min' => min($priceValues),
            'max' => max($priceValues),
            'avg' => array_sum($priceValues) / count($priceValues),
            'count' => count($priceValues)
        ];
    }

    private function getProductInfo($data)
    {
        $product = $data['product'] ?? [];
        return [
            'product_name' => $product['product_name'] ?? 'Unknown Product',
            'image_url' => $product['image_front_url'] ?? $product['image_url'] ?? 'https://via.placeholder.com/400x400',
            'product_quantity' => $product['quantity'] ?? 'Unknown',
            'product_quantity_unit' => $product['quantity_unit'] ?? ''
        ];
    }

    /**
     * Page principale de consultation des prix
     */
    public function browse()
    {
        return view('prices.browse');
    }

    /**
     * Recherche intelligente dans la base de données de prix
     */
    public function searchPrices(Request $request)
    {
        try {
            $params = [];
            $productCodes = [];
            
            // Si recherche par nom de produit, d'abord trouver les codes-barres correspondants
            if ($request->filled('product_name')) {
                $productCodes = $this->findProductCodesByName($request->product_name);
                
                // Debug: log les codes trouvés
                \Log::info('Recherche intelligente pour: ' . $request->product_name, [
                    'codes_trouves' => $productCodes,
                    'nombre_codes' => count($productCodes)
                ]);
                
                // Si on a trouvé des codes-barres, les utiliser pour la recherche
                if (!empty($productCodes)) {
                    // On va faire plusieurs requêtes pour chaque code-barres
                    return $this->searchByProductCodes($productCodes, $request);
                } else {
                    // Fallback: recherche directe par nom (moins efficace)
                    $params['product_name__icontains'] = $request->product_name;
                }
            }
            
            // Paramètres de recherche standards
            if ($request->filled('product_code')) {
                $params['product_code'] = $request->product_code;
            }
            
            if ($request->filled('location_osm_name')) {
                $params['location_osm_name__icontains'] = $request->location_osm_name;
            }
            
            if ($request->filled('location_osm_address_city')) {
                $params['location_osm_address_city__icontains'] = $request->location_osm_address_city;
            }
            
            if ($request->filled('price_min')) {
                $params['price__gte'] = $request->price_min;
            }
            
            if ($request->filled('price_max')) {
                $params['price__lte'] = $request->price_max;
            }
            
            if ($request->filled('date_from')) {
                $params['date__gte'] = $request->date_from;
            }
            
            if ($request->filled('date_to')) {
                $params['date__lte'] = $request->date_to;
            }

            // Pagination
            $params['page'] = $request->get('page', 1);
            $params['size'] = $request->get('size', 20);
            
            // Tri par date décroissante
            $params['order_by'] = '-date';

            // Appel à l'API Open Prices
            $response = Http::withOptions([
                'verify' => false,
            ])->timeout(30)->get($this->priceApiUrl, $params);

            if ($response->successful()) {
                $data = $response->json();
                
                $prices = $this->processAdvancedPrices($data['items'] ?? []);
                $stats = $this->calculateAdvancedStats($prices);
                
                return view('prices.search-results', [
                    'prices' => $prices,
                    'stats' => $stats,
                    'total' => $data['total'] ?? 0,
                    'page' => $data['page'] ?? 1,
                    'pages' => $data['pages'] ?? 1,
                    'size' => $data['size'] ?? 20,
                    'filters' => $request->all()
                ]);
            } else {
                return back()->with('error', 'Erreur lors de la recherche dans la base de données');
            }
            
        } catch (\Exception $e) {
            return back()->with('error', 'Une erreur est survenue : ' . $e->getMessage());
        }
    }

    /**
     * Traitement avancé des données de prix avec récupération des noms de produits
     */
    private function processAdvancedPrices($items)
    {
        $processedItems = collect($items)->map(function ($item) {
            return [
                'id' => $item['id'] ?? null,
                'product_code' => $item['product_code'] ?? null,
                'product_name' => $item['product_name'] ?? null,
                'product_quantity' => $item['product_quantity'] ?? null,
                'product_quantity_unit' => $item['product_quantity_unit'] ?? null,
                'price' => $item['price'] ?? 0,
                'price_per' => $item['price_per'] ?? null,
                'currency' => $item['currency'] ?? 'EUR',
                'date' => $item['date'] ?? null,
                'location' => [
                    'osm_name' => $item['location']['osm_name'] ?? 'Magasin inconnu',
                    'osm_address_city' => $item['location']['osm_address_city'] ?? '',
                    'osm_address_country' => $item['location']['osm_address_country'] ?? '',
                    'osm_address_postcode' => $item['location']['osm_address_postcode'] ?? '',
                ],
                'proof_id' => $item['proof_id'] ?? null,
                'owner' => $item['owner'] ?? null,
                'created' => $item['created'] ?? null,
            ];
        });

        // Enrichir avec les noms de produits depuis Open Food Facts
        return $this->enrichWithProductNames($processedItems->toArray());
    }

    /**
     * Enrichir les prix avec les noms de produits depuis Open Food Facts
     */
    private function enrichWithProductNames($prices)
    {
        $productCodes = [];
        
        // Collecter tous les codes-barres uniques
        foreach ($prices as $price) {
            if ($price['product_code'] && !in_array($price['product_code'], $productCodes)) {
                $productCodes[] = $price['product_code'];
            }
        }

        // Récupérer les informations produits par batch (limité à 5 pour éviter les timeouts)
        $productNames = [];
        $batchSize = 5;
        $batches = array_chunk(array_slice($productCodes, 0, 20), $batchSize); // Limiter à 20 produits max
        
        foreach ($batches as $batch) {
            foreach ($batch as $code) {
                try {
                    $response = Http::withOptions([
                        'verify' => false,
                    ])->timeout(5)->get($this->productApiUrl . $code . '.json');
                    
                    if ($response->successful()) {
                        $data = $response->json();
                        $product = $data['product'] ?? [];
                        
                        $productInfo = [];
                        
                        if (isset($product['product_name']) && !empty($product['product_name'])) {
                            $productInfo['name'] = $product['product_name'];
                        } elseif (isset($product['product_name_fr']) && !empty($product['product_name_fr'])) {
                            $productInfo['name'] = $product['product_name_fr'];
                        } elseif (isset($product['product_name_en']) && !empty($product['product_name_en'])) {
                            $productInfo['name'] = $product['product_name_en'];
                        }
                        
                        // Récupérer l'image
                        if (isset($product['image_front_small_url']) && !empty($product['image_front_small_url'])) {
                            $productInfo['image'] = $product['image_front_small_url'];
                        } elseif (isset($product['image_front_url']) && !empty($product['image_front_url'])) {
                            $productInfo['image'] = $product['image_front_url'];
                        } elseif (isset($product['image_url']) && !empty($product['image_url'])) {
                            $productInfo['image'] = $product['image_url'];
                        }
                        
                        if (!empty($productInfo)) {
                            $productNames[$code] = $productInfo;
                        }
                    }
                } catch (\Exception $e) {
                    // Ignorer les erreurs pour les produits individuels
                    continue;
                }
                
                // Petite pause pour éviter de surcharger l'API
                usleep(100000); // 0.1 seconde
            }
        }

        // Appliquer les informations récupérées
        foreach ($prices as &$price) {
            if ($price['product_code'] && isset($productNames[$price['product_code']])) {
                $productInfo = $productNames[$price['product_code']];
                
                if (is_array($productInfo)) {
                    if (isset($productInfo['name'])) {
                        $price['product_name'] = $productInfo['name'];
                    }
                    if (isset($productInfo['image'])) {
                        $price['product_image'] = $productInfo['image'];
                    }
                } else {
                    // Rétrocompatibilité si c'est juste une string
                    $price['product_name'] = $productInfo;
                }
            } elseif (empty($price['product_name'])) {
                $price['product_name'] = 'Produit inconnu (Code: ' . ($price['product_code'] ?? 'N/A') . ')';
            }
        }

        return $prices;
    }

    /**
     * Calcul de statistiques avancées
     */
    private function calculateAdvancedStats($prices)
    {
        if (empty($prices)) {
            return [
                'count' => 0,
                'min' => 0,
                'max' => 0,
                'avg' => 0,
                'stores_count' => 0,
                'countries_count' => 0,
                'recent_count' => 0
            ];
        }

        $priceValues = array_column($prices, 'price');
        $stores = array_unique(array_column(array_column($prices, 'location'), 'osm_name'));
        $countries = array_unique(array_column(array_column($prices, 'location'), 'osm_address_country'));
        
        // Prix récents (derniers 30 jours)
        $thirtyDaysAgo = Carbon::now()->subDays(30)->format('Y-m-d');
        $recentPrices = array_filter($prices, function($price) use ($thirtyDaysAgo) {
            return $price['date'] && $price['date'] >= $thirtyDaysAgo;
        });

        return [
            'count' => count($priceValues),
            'min' => min($priceValues),
            'max' => max($priceValues),
            'avg' => array_sum($priceValues) / count($priceValues),
            'stores_count' => count($stores),
            'countries_count' => count($countries),
            'recent_count' => count($recentPrices)
        ];
    }

    /**
     * Recherche des codes-barres de produits par nom dans Open Food Facts
     */
    private function findProductCodesByName($productName)
    {
        try {
            $searchUrl = 'https://world.openfoodfacts.org/cgi/search.pl';
            
            $response = Http::withOptions([
                'verify' => false,
            ])->timeout(10)->get($searchUrl, [
                'search_terms' => $productName,
                'search_simple' => 1,
                'action' => 'process',
                'json' => 1,
                'page_size' => 20
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $products = $data['products'] ?? [];
                
                $codes = [];
                foreach ($products as $product) {
                    if (isset($product['code']) && !empty($product['code'])) {
                        // Vérifier que le nom du produit correspond bien à la recherche
                        $productNameInData = $product['product_name'] ?? '';
                        if ($this->isProductNameMatch($productName, $productNameInData)) {
                            $codes[] = $product['code'];
                        }
                    }
                }
                
                return array_slice($codes, 0, 10); // Limiter à 10 codes max
            }
        } catch (\Exception $e) {
            \Log::warning('Erreur recherche Open Food Facts: ' . $e->getMessage());
        }
        
        return [];
    }

    /**
     * Vérifier si le nom du produit correspond à la recherche (recherche floue)
     */
    private function isProductNameMatch($searchTerm, $productName)
    {
        if (empty($productName)) {
            return false;
        }
        
        $searchTerm = strtolower(trim($searchTerm));
        $productName = strtolower(trim($productName));
        
        // Recherche exacte
        if (strpos($productName, $searchTerm) !== false) {
            return true;
        }
        
        // Recherche par mots-clés
        $searchWords = explode(' ', $searchTerm);
        $matchedWords = 0;
        
        foreach ($searchWords as $word) {
            if (strlen($word) >= 3 && strpos($productName, $word) !== false) {
                $matchedWords++;
            }
        }
        
        // Si au moins 50% des mots correspondent
        return $matchedWords >= (count($searchWords) * 0.5);
    }

    /**
     * Recherche de prix par codes-barres multiples
     */
    private function searchByProductCodes($productCodes, $request)
    {
        $allPrices = [];
        $totalCount = 0;
        
        // Limiter le nombre de codes pour éviter les timeouts
        $limitedCodes = array_slice($productCodes, 0, 5);
        
        foreach ($limitedCodes as $code) {
            try {
                $params = [
                    'product_code' => $code,
                    'size' => 10, // Limiter par code-barres
                    'order_by' => '-date'
                ];
                
                // Ajouter les autres filtres
                if ($request->filled('location_osm_name')) {
                    $params['location_osm_name__icontains'] = $request->location_osm_name;
                }
                
                if ($request->filled('location_osm_address_city')) {
                    $params['location_osm_address_city__icontains'] = $request->location_osm_address_city;
                }
                
                if ($request->filled('price_min')) {
                    $params['price__gte'] = $request->price_min;
                }
                
                if ($request->filled('price_max')) {
                    $params['price__lte'] = $request->price_max;
                }
                
                if ($request->filled('date_from')) {
                    $params['date__gte'] = $request->date_from;
                }
                
                if ($request->filled('date_to')) {
                    $params['date__lte'] = $request->date_to;
                }

                $response = Http::withOptions([
                    'verify' => false,
                ])->timeout(10)->get($this->priceApiUrl, $params);

                if ($response->successful()) {
                    $data = $response->json();
                    $items = $data['items'] ?? [];
                    
                    foreach ($items as $item) {
                        $allPrices[] = $item;
                    }
                    
                    $totalCount += $data['total'] ?? count($items);
                }
                
            } catch (\Exception $e) {
                continue; // Ignorer les erreurs pour des codes individuels
            }
        }
        
        // Trier tous les prix par date décroissante
        usort($allPrices, function($a, $b) {
            return strcmp($b['date'] ?? '', $a['date'] ?? '');
        });
        
        // Limiter à 20 résultats
        $allPrices = array_slice($allPrices, 0, 20);
        
        // Traitement des prix
        $prices = $this->processAdvancedPrices($allPrices);
        $stats = $this->calculateAdvancedStats($prices);
        
        return view('prices.search-results', [
            'prices' => $prices,
            'stats' => $stats,
            'total' => $totalCount,
            'page' => 1,
            'pages' => 1,
            'size' => count($prices),
            'filters' => $request->all()
        ]);
    }
}