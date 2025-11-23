<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class OpenFoodFactsController extends Controller
{
    protected $priceApiUrl = 'https://prices.openfoodfacts.org/api/v1/prices';
    protected $productApiUrl = 'https://world.openfoodfacts.org/api/v0/product/';

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
}