<?php

namespace App\Http\Controllers;

use App\Models\Publisher;
use App\Models\Product;
use Illuminate\Http\Request;

class PublisherController extends Controller
{
    public function index()
    {
        $publishers = Publisher::orderBy('name')->paginate(24);
        return view('publishers.index', compact('publishers'));
    }

    public function show(Publisher $publisher, Request $request)
    {
        $query = Product::with('primaryImage')
            ->where('publisher_id', $publisher->id)
            ->where('is_active', true);

        $products = $query->paginate(12)->withQueryString();

        return view('publishers.show', compact('publisher', 'products'));
    }
}
