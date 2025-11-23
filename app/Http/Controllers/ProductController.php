<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function search()
    {
        return view('products.search');
    }

    public function fetch(Request $request)
    {
        $request->validate([
            'product_code' => 'required|string',
        ]);
        // Ici tu pourras ajouter la logique de recherche réelle
        return back()->with('success', 'Recherche pour le code-barres : ' . $request->product_code);
    }
} 