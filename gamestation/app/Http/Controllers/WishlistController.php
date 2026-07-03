<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $products = $request->user()->wishlist()->with(['primaryImage', 'images'])->latest()->get();

        return view('wishlist.index', compact('products'));
    }

    public function toggle(Request $request, Product $product)
    {
        $user = $request->user();
        
        $exists = $user->wishlist()->where('product_id', $product->id)->exists();
        
        if ($exists) {
            $user->wishlist()->detach($product->id);
            $message = __('ui.wishlist_removed');
        } else {
            $user->wishlist()->attach($product->id);
            $message = __('ui.wishlist_added');
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'in_wishlist' => !$exists,
                'message' => $message,
                'count' => $user->wishlist()->count()
            ]);
        }

        return redirect()->back()->with('success', $message);
    }
}
