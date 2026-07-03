<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use App\Models\UserNotification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $this->syncContactReplyNotifications($request->user());

        $notifications = UserNotification::where('user_id', $request->user()->id)
            ->latest()
            ->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    public function markRead(Request $request, UserNotification $notification)
    {
        if ($notification->user_id !== $request->user()->id) {
            abort(403);
        }

        $notification->update(['read_at' => now()]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        // Redirect to order details if it's an order notification (supporting optional '#')
        if (preg_match('/Đơn hàng #?(\d+)/', $notification->body, $matches) || preg_match('/Đơn hàng #?(\d+)/', $notification->title, $matches)) {
            $orderId = $matches[1];
            return redirect()->route('orders.show', $orderId);
        }

        // Redirect to product reviews if it's a review reply notification
        if (preg_match('/\(Mã SP:\s*(\d+)\)/', $notification->body, $matches)) {
            $productId = $matches[1];
            $product = \App\Models\Product::find($productId);
            if ($product) {
                return redirect()->route('products.show', ['product' => $product, 'tab' => 'reviews']);
            }
        } elseif (preg_match('/Sản phẩm "([^"]+)"/', $notification->body, $matches)) {
            $productName = $matches[1];
            $product = \App\Models\Product::where('name', $productName)->first();
            if ($product) {
                return redirect()->route('products.show', ['product' => $product, 'tab' => 'reviews']);
            }
        }

        // Redirect to lucky spin page if it's a spin notification
        if ($notification->title === 'Vòng quay may mắn') {
            if (preg_match('/trúng\s+([^:]+):\s*([A-Z0-9-]+)/u', $notification->body, $matches)) {
                $prizeName = trim($matches[1]);
                $couponCode = trim($matches[2]);
                $prizeName = mb_strtoupper(mb_substr($prizeName, 0, 1, 'UTF-8'), 'UTF-8') . mb_substr($prizeName, 1, null, 'UTF-8');

                return redirect()->route('lucky-spin.index', [
                    'prize_name' => $prizeName,
                    'coupon_code' => $couponCode,
                    'is_win' => 1
                ]);
            }

            return redirect()->route('lucky-spin.index', [
                'prize_name' => 'Chúc bạn may mắn lần sau',
                'is_win' => 0
            ]);
        }

        return back()->with('success', 'Đã đánh dấu thông báo là đã đọc.');
    }

    public function markAllAsRead(Request $request)
    {
        $request->user()->userNotifications()->whereNull('read_at')->update(['read_at' => now()]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Đã đánh dấu tất cả thông báo là đã đọc.');
    }

    private function syncContactReplyNotifications($user): void
    {
        $email = mb_strtolower(trim((string) $user->email));

        $contacts = ContactMessage::query()
            ->whereNotNull('admin_reply')
            ->where('status', 'replied')
            ->where(function ($q) use ($user, $email) {
                $q->where('user_id', $user->id)
                    ->orWhereRaw('LOWER(email) = ?', [$email]);
            })
            ->latest('admin_replied_at')
            ->get();

        foreach ($contacts as $contact) {
            $exists = $user->userNotifications()
                ->where('title', 'Phản hồi từ quản lý liên hệ')
                ->where('body', 'like', "Liên hệ #{$contact->id}%")
                ->exists();

            if ($exists) {
                continue;
            }

            $user->userNotifications()->create([
                'title' => 'Phản hồi từ quản lý liên hệ',
                'body' => "Liên hệ #{$contact->id}\nChủ đề: {$contact->subject}\n\nNội dung phản hồi: {$contact->admin_reply}",
            ]);
        }
    }
}
