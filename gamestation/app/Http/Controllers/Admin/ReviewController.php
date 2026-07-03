<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');
    }

    public function index()
    {
        $reviews = Review::with('product', 'user')->paginate(15);
        return view('admin.reviews.index', compact('reviews'));
    }

    public function show(Review $review)
    {
        $review->load('product.primaryImage', 'product.images', 'user');
        return view('admin.reviews.show', compact('review'));
    }

    public function reply(Request $request, Review $review)
    {
        $validated = $request->validate([
            'reply' => 'required|string|min:5',
        ]);

        $review->update([
            'admin_reply' => $validated['reply'],
            'admin_replied_at' => now(),
        ]);

        // Create notification for the user (use userNotifications relation)
        if ($review->user) {
            $review->user->userNotifications()->create([
                'title' => 'Admin đã trả lời đánh giá của bạn',
                'body' => "Sản phẩm \"{$review->product->name}\" (Mã SP: {$review->product_id}): {$validated['reply']}",
            ]);
        }

        return redirect()->route('admin.reviews.show', $review)->with('success', 'Phản hồi đã được gửi.');
    }

    public function destroy(Review $review)
    {
        $review->delete();
        return redirect()->route('admin.reviews.index')->with('success', 'Đánh giá đã được xóa.');
    }
}
