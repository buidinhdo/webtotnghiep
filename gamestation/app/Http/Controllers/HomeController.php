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

        // Fetch Macross and Ghostrunner manually since they are older than cutoff date
        $macross = Product::with(['primaryImage', 'images', 'category'])
            ->where('name', 'like', '%Macross Shooting Insight%')
            ->first();
        $ghostrunner = Product::with(['primaryImage', 'images', 'category'])
            ->where('name', 'like', '%Ghostrunner%')
            ->first();

        // Reorder products in the latest list as requested
        $doubleOSeven = $latest->first(fn($p) => str_contains($p->name, '007 First Light'));
        $mortal = $latest->first(fn($p) => str_contains($p->name, 'Mortal Kombat 11: Ultimate Edition'));
        $legend = $latest->first(fn($p) => str_contains($p->name, 'Trails Through Daybreak – Deluxe Edition II'));

        $latest = $latest->filter(function($p) {
            return !str_contains($p->name, 'Borderlands 4 Deluxe Edition')
                && !str_contains($p->name, 'Yakuza 0: Director')
                && !str_contains($p->name, '007 First Light')
                && !str_contains($p->name, 'Mortal Kombat 11: Ultimate Edition')
                && !str_contains($p->name, 'Trails Through Daybreak – Deluxe Edition II')
                && !str_contains($p->name, 'Naruto X Boruto Ultimate Ninja Storm')
                && !str_contains($p->name, 'Dragon Ball Xenoverse 2');
        });

        // Reconstruct the collection list
        $reordered = collect();

        // Position 1: 007 First Light
        if ($doubleOSeven) {
            $reordered->push($doubleOSeven);
        }

        // Position 2: First remaining product
        $firstRemaining = $latest->shift();
        if ($firstRemaining) {
            $reordered->push($firstRemaining);
        }

        // Position 3: Mortal Kombat 11
        if ($mortal) {
            $reordered->push($mortal);
        }

        // Position 4: The Legend of Heroes
        if ($legend) {
            $reordered->push($legend);
        }

        // Merge other remaining products
        $reordered = $reordered->merge($latest);

        // Place Macross and Ghostrunner at the end
        if ($macross) {
            $reordered->push($macross);
        }
        if ($ghostrunner) {
            $reordered->push($ghostrunner);
        }

        $latest = $reordered->values();

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
