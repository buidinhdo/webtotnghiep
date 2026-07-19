<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Category;
use App\Models\Genre;
use App\Models\Publisher;
use App\Models\EsrbRating;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');
    }

    public function index()
    {
        $category = request()->query('category');
        $categoryId = request()->query('category_id');
        $publisherId = request()->query('publisher_id');
        $genre = request()->query('genre');
        $esrb = request()->query('esrb');

        $categories = Category::orderBy('name')->get();
        $publishers = Publisher::orderBy('name')->get();
        $genres = Genre::where('is_active', true)->orderBy('name')->get();
        $esrbRatings = EsrbRating::where('is_active', true)->orderBy('code')->get();

        $allowedCategorySlugs = ['ps4', 'ps5', 'switch'];

        $products = Product::with(['category', 'publisher', 'images', 'primaryImage'])
            ->when($categoryId && ctype_digit((string) $categoryId), function ($query) use ($categoryId) {
                $query->where('category_id', $categoryId);
            })
            ->when(!empty($category) && in_array($category, $allowedCategorySlugs, true), function ($query) use ($category) {
                $query->whereHas('category', function ($categoryQuery) use ($category) {
                    $categoryQuery->where('slug', $category);
                });
            })
            ->when($publisherId && ctype_digit((string) $publisherId), function ($query) use ($publisherId) {
                $query->where('publisher_id', $publisherId);
            })
            ->when(!empty($genre), function ($query) use ($genre) {
                $query->where('genre', $genre);
            })
            ->when(!empty($esrb), function ($query) use ($esrb) {
                $query->where('esrb', $esrb);
            })
            ->paginate(15)
            ->appends(request()->query());

        return view('admin.products.index', compact('products', 'categories', 'publishers', 'genres', 'esrbRatings'));
    }

    public function create()
    {
        $categories = Category::all();
        $publishers = \App\Models\Publisher::all();
        $genres = Genre::where('is_active', true)->orderBy('name')->get();
        return view('admin.products.create', compact('categories', 'publishers', 'genres'));
    }

    public function store(Request $request)
    {
        if ($request->has('images') || $request->hasFile('images')) {
            $images = array_filter($request->file('images') ?? [], function ($file) {
                return $file !== null && $file instanceof \Illuminate\Http\UploadedFile && $file->isValid();
            });
            if (empty($images)) {
                $request->request->remove('images');
                $request->files->remove('images');
            } else {
                $request->files->set('images', $images);
            }
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'short_description' => 'nullable|string',
            'detailed_description' => 'nullable|string|max:65535',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'esrb' => 'nullable|string|in:EC,E,E10+,T,M,AO,RP',
            'platform' => 'nullable|string',
            'genre' => 'nullable|string',
            'release_date' => 'nullable|date',
            'players' => 'nullable|string',
            'publisher_id' => 'nullable|exists:publishers,id',
            'primary_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);
        // Filter out empty strings and null values
        $createData = array_filter($validated, fn($value) => $value !== '' && $value !== null);

        // Create slug from name
        $createData['slug'] = Str::slug($validated['name']);
        // Ensure SKU exists to satisfy DB unique non-null constraint
        if (!isset($createData['sku']) || $createData['sku'] === '') {
            $createData['sku'] = 'SKU-' . strtoupper(Str::random(8));
        }

        // Map publisher_id if present
        if (isset($validated['publisher_id'])) {
            $createData['publisher_id'] = $validated['publisher_id'];
        }

        try {
            $product = Product::create($createData);
        } catch (\Exception $e) {
            \Log::error('Product create failed: ' . $e->getMessage());
            return back()->withInput()->withErrors(['error' => 'Tạo sản phẩm thất bại: ' . $e->getMessage()]);
        }

        // Handle primary image
        if ($request->hasFile('primary_image')) {
            $image = $request->file('primary_image');
            $filename = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('images/products'), $filename);
            
            ProductImage::create([
                'product_id' => $product->id,
                'image_path' => 'images/products/' . $filename,
                'is_primary' => true,
            ]);
        }

        // Handle multiple images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $filename = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('images/products'), $filename);
                
                ProductImage::create([
                    'product_id' => $product->id,
                    'image_path' => 'images/products/' . $filename,
                    'is_primary' => false,
                ]);
            }
        }

        return redirect()->route('admin.products.index')->with('success', 'Sản phẩm đã được thêm.');
    }

    public function edit(Product $product)
    {
        $product->load('images', 'primaryImage');
        $categories = Category::all();
        $publishers = \App\Models\Publisher::all();
        $genres = Genre::where('is_active', true)->orderBy('name')->get();
        return view('admin.products.edit', compact('product', 'categories', 'publishers', 'genres'));
    }

    public function update(Request $request, Product $product)
    {
        if ($request->has('images') || $request->hasFile('images')) {
            $images = array_filter($request->file('images') ?? [], function ($file) {
                return $file !== null && $file instanceof \Illuminate\Http\UploadedFile && $file->isValid();
            });
            if (empty($images)) {
                $request->request->remove('images');
                $request->files->remove('images');
            } else {
                $request->files->set('images', $images);
            }
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'short_description' => 'nullable|string',
            'detailed_description' => 'nullable|string|max:65535',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'esrb' => 'nullable|string|in:EC,E,E10+,T,M,AO,RP',
            'platform' => 'nullable|string',
            'genre' => 'nullable|string',
            'release_date' => 'nullable|date',
            'players' => 'nullable|string',
            'publisher_id' => 'nullable|exists:publishers,id',
            'primary_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        // Filter out empty strings and null values (keep only non-empty values)
        $updateData = array_filter($validated, fn($value) => $value !== '' && $value !== null);

        // Ensure descriptions from the request are preserved even if array_filter would remove them
        if ($request->has('short_description')) {
            $updateData['short_description'] = $request->input('short_description');
        }
        if ($request->has('detailed_description')) {
            $updateData['detailed_description'] = $request->input('detailed_description');
        }

        // Map publisher_id if present
        if ($request->has('publisher_id')) {
            $updateData['publisher_id'] = $request->input('publisher_id');
        }

            try {
                $product->update($updateData);
            } catch (\Exception $e) {
                \Log::error('Product update failed: '.$e->getMessage());
                return back()->withInput()->withErrors(['error' => 'Cập nhật sản phẩm thất bại: ' . $e->getMessage()]);
            }

        // Handle primary image replacement
        if ($request->hasFile('primary_image')) {
            // Delete old primary image
            $oldPrimary = $product->images()->where('is_primary', true)->first();
            if ($oldPrimary) {
                if (file_exists(public_path($oldPrimary->image_path))) {
                    unlink(public_path($oldPrimary->image_path));
                }
                $oldPrimary->delete();
            }

            $image = $request->file('primary_image');
            $filename = time() . '_' . $image->getClientOriginalName();
            $image->move(public_path('images/products'), $filename);
            
            ProductImage::create([
                'product_id' => $product->id,
                'image_path' => 'images/products/' . $filename,
                'is_primary' => true,
            ]);
        }

        // Handle additional images
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $filename = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
                $image->move(public_path('images/products'), $filename);
                
                ProductImage::create([
                    'product_id' => $product->id,
                    'image_path' => 'images/products/' . $filename,
                    'is_primary' => false,
                ]);
            }
        }

        $redirectParams = request()->query();
        if (empty($redirectParams['page']) && $request->has('page')) {
            $redirectParams['page'] = $request->input('page');
        }

        return redirect()->route('admin.products.index', $redirectParams)->with('success', 'Sản phẩm đã được cập nhật.');
    }

    public function destroy(Product $product)
    {
        // Delete all associated images
        foreach ($product->images as $image) {
            if (file_exists(public_path($image->image_path))) {
                unlink(public_path($image->image_path));
            }
            $image->delete();
        }

        $product->delete();
        $redirectParams = request()->query();
        if (empty($redirectParams['page']) && request()->has('page')) {
            $redirectParams['page'] = request()->input('page');
        }

        return redirect()->route('admin.products.index', $redirectParams)->with('success', 'Sản phẩm đã được xóa.');
    }

    public function deleteImage(Product $product, ProductImage $image)
    {
        if ($image->product_id !== $product->id) {
            if (request()->expectsJson()) {
                return response()->json(['message' => 'Ảnh không thuộc sản phẩm này.'], 422);
            }

            return back()->withErrors(['error' => 'Ảnh không thuộc sản phẩm này.']);
        }

        if ($image->is_primary) {
            if (request()->expectsJson()) {
                return response()->json(['message' => 'Không thể xóa ảnh chính từ đây.'], 422);
            }

            return back()->withErrors(['error' => 'Không thể xóa ảnh chính từ đây.']);
        }

        if ($image->image_path && file_exists(public_path($image->image_path))) {
            unlink(public_path($image->image_path));
        }

        $image->delete();

        if (request()->expectsJson()) {
            return response()->json(['message' => 'Ảnh phụ đã được xóa.']);
        }

        return back()->with('success', 'Ảnh phụ đã được xóa.');
    }

    public function deleteAllImages(Product $product)
    {
        $images = $product->images()->where('is_primary', false)->get();

        if ($images->isEmpty()) {
            if (request()->expectsJson()) {
                return response()->json(['message' => 'Không có ảnh phụ để xóa.'], 422);
            }

            return back()->withErrors(['error' => 'Không có ảnh phụ để xóa.']);
        }

        foreach ($images as $image) {
            if ($image->image_path && file_exists(public_path($image->image_path))) {
                unlink(public_path($image->image_path));
            }

            $image->delete();
        }

        if (request()->expectsJson()) {
            return response()->json(['message' => 'Tất cả ảnh phụ đã được xóa.']);
        }

        return back()->with('success', 'Tất cả ảnh phụ đã được xóa.');
    }
}
