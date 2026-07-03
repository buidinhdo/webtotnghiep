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
