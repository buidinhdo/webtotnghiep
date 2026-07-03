<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');
    }

    public function index()
    {
        $banners = Banner::orderBy('order_column')->get();
        return view('admin.banners.index', compact('banners'));
    }

    public function create()
    {
        return view('admin.banners.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        if ($request->file('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . $file->getClientOriginalName();
            
            // Create target folder if not exists
            if (!is_dir(public_path('images/banners'))) {
                mkdir(public_path('images/banners'), 0755, true);
            }
            
            $file->move(public_path('images/banners'), $filename);

            $maxOrder = Banner::max('order_column') ?? 0;

            Banner::create([
                'image_path' => 'images/banners/' . $filename,
                'order_column' => $maxOrder + 1,
                'is_active' => true,
            ]);

            return redirect()->route('admin.banners.index')->with('success', 'Banner đã được thêm.');
        }

        return back()->with('error', 'Lỗi khi tải ảnh.');
    }

    public function edit(Banner $banner)
    {
        return view('admin.banners.edit', compact('banner'));
    }

    public function update(Request $request, Banner $banner)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        if ($request->file('image')) {
            $file = $request->file('image');
            $newFilename = time() . '_' . $file->getClientOriginalName();
            
            // Move new file
            $file->move(public_path('images/banners'), $newFilename);

            // Delete old file
            $oldPath = public_path($banner->image_path);
            if (file_exists($oldPath) && is_file($oldPath)) {
                unlink($oldPath);
            }

            // Update database
            $banner->update([
                'image_path' => 'images/banners/' . $newFilename,
            ]);
            
            return redirect()->route('admin.banners.index')->with('success', 'Banner đã được cập nhật.');
        }

        return back()->with('error', 'Lỗi khi tải ảnh.');
    }

    public function updateOrder(Request $request, Banner $banner)
    {
        $validated = $request->validate([
            'order' => 'required|integer|min:1|max:9999',
        ]);

        $banner->update([
            'order_column' => (int) $validated['order'],
        ]);

        return redirect()->route('admin.banners.index')->with('success', 'Đã cập nhật thứ tự banner.');
    }

    public function destroy(Banner $banner)
    {
        $path = public_path($banner->image_path);
        if (file_exists($path) && is_file($path)) {
            unlink($path);
        }

        $banner->delete();

        return redirect()->route('admin.banners.index')->with('success', 'Banner đã được xóa.');
    }
}
