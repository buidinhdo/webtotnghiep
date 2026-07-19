<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Article;
use App\Models\Banner;

class HomeController extends Controller
{
    public function index()
    {
        $banners = Banner::where('is_active', true)
            ->orderBy('order_column')
            ->get()
            ->map(fn ($banner) => '/' . $banner->image_path)
            ->values();

        $categoryMap = Category::whereIn('slug', ['ps4', 'ps5', 'switch'])->pluck('id', 'slug');

        $featured = Product::with(['primaryImage', 'images', 'category'])
            ->where('is_active', true)
            ->where('featured', true)
            ->latest()
            ->take(10)
            ->get();

        $cutoffProduct = Product::where('name', 'like', '%007 First Light%')->first();
        
        $latestQuery = Product::with(['primaryImage', 'images', 'category'])
            ->where('is_active', true)
            ->latest();

        if ($cutoffProduct) {
            $latest = $latestQuery->where('created_at', '>=', $cutoffProduct->created_at)->get();
        } else {
            $latest = $latestQuery->take(15)->get();
        }

        // Reorder products in the latest list as requested
        $borderlands = $latest->first(fn($p) => str_contains($p->name, 'Borderlands 4 Deluxe Edition'));
        $yakuza = $latest->first(fn($p) => str_contains($p->name, 'Yakuza 0: Director'));
        $doubleOSeven = $latest->first(fn($p) => str_contains($p->name, '007 First Light'));

        $latest = $latest->filter(function($p) {
            return !str_contains($p->name, 'Borderlands 4 Deluxe Edition')
                && !str_contains($p->name, 'Yakuza 0: Director')
                && !str_contains($p->name, '007 First Light');
        });

        // Move 007 First Light to the beginning (replacing top position)
        if ($doubleOSeven) {
            $latest->prepend($doubleOSeven);
        }

        // Place Borderlands 4 Deluxe and Yakuza 0 at the end
        if ($borderlands) {
            $latest->push($borderlands);
        }
        if ($yakuza) {
            $latest->push($yakuza);
        }

        $latest = $latest->values();

        $ps4 = Product::with(['primaryImage', 'images', 'category'])
            ->where('is_active', true)
            ->when($categoryMap->get('ps4'), fn ($query, $categoryId) => $query->where('category_id', $categoryId))
            ->latest()
            ->take(100)
            ->get();

        $ps5 = Product::with(['primaryImage', 'images', 'category'])
            ->where('is_active', true)
            ->when($categoryMap->get('ps5'), fn ($query, $categoryId) => $query->where('category_id', $categoryId))
            ->latest()
            ->take(100)
            ->get();

        $switch = Product::with(['primaryImage', 'images', 'category'])
            ->where('is_active', true)
            ->when($categoryMap->get('switch'), fn ($query, $categoryId) => $query->where('category_id', $categoryId))
            ->latest()
            ->take(100)
            ->get();

        $articles = Article::where('is_published', true)
            ->latest('published_at')
            ->take(6)
            ->get();

        return view('home', compact('banners', 'featured', 'latest', 'ps4', 'ps5', 'switch', 'articles'));
    }
}
