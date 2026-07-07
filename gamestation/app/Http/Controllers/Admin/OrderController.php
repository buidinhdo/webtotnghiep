<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\UserNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderMail;

class OrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');
    }

    public function index()
    {
        $status = request()->query('status');
        $paymentStatus = request()->query('payment_status');
        $paymentMethod = request()->query('payment_method');
        $search = request()->query('search');
        if ($search && str_starts_with($search, '#')) {
            $search = ltrim($search, '#');
        }
        $startDate = request()->query('start_date');
        $endDate = request()->query('end_date');

        $allowedStatuses = ['pending', 'processing', 'shipped', 'completed', 'cancelled'];
        $allowedPaymentStatuses = ['unpaid', 'paid', 'refunded', 'failed', 'pending'];
        $allowedPaymentMethods = ['cod', 'card', 'credit_card'];

        $orders = Order::with('user')
            ->when(in_array($status, $allowedStatuses, true), function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->when(in_array($paymentStatus, $allowedPaymentStatuses, true), function ($query) use ($paymentStatus) {
                $query->where('payment_status', $paymentStatus);
            })
            ->when(in_array($paymentMethod, $allowedPaymentMethods, true), function ($query) use ($paymentMethod) {
                $query->where('payment_method', $paymentMethod);
            })
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    if (is_numeric($search)) {
                        if (str_starts_with($search, '0') || strlen($search) >= 7) {
                            $q->whereHas('user', function ($uq) use ($search) {
                                $uq->where('phone', 'like', "%{$search}%");
                            });
                        } else {
                            $q->where('id', $search);
                        }
                    } else {
                        $q->whereHas('user', function ($uq) use ($search) {
                            $uq->where('name', 'like', "%{$search}%")
                               ->orWhere('email', 'like', "%{$search}%");
                        });
                    }
                });
            })
            ->when($startDate, function ($query) use ($startDate) {
                $query->whereDate('created_at', '>=', $startDate);
            })
            ->when($endDate, function ($query) use ($endDate) {
                $query->whereDate('created_at', '<=', $endDate);
            })
            ->orderBy('id', 'asc')
            ->paginate(15)
            ->appends(request()->query());

        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $order->load('user', 'items.product.primaryImage', 'items.product.images');
        return view('admin.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,processing,shipped,completed,cancelled',
        ]);

        if ($validated['status'] === 'completed') {
            if (! $order->completed_at) {
                $validated['completed_at'] = now();
            }
            $validated['payment_status'] = 'paid';
        }

        $oldStatus = $order->status;
        $order->update($validated);
        $newStatus = $order->status;

        if ($oldStatus !== $newStatus) {
            $statusLabels = [
                'pending' => 'Chờ xử lý',
                'processing' => 'Đang xử lý',
                'shipped' => 'Đang giao hàng',
                'completed' => 'Đã hoàn thành',
                'cancelled' => 'Đã hủy',
            ];

            $oldLabel = $statusLabels[$oldStatus] ?? $oldStatus;
            $newLabel = $statusLabels[$newStatus] ?? $newStatus;

            UserNotification::create([
                'user_id' => $order->user_id,
                'title' => 'Cập nhật trạng thái đơn hàng',
                'body' => "Đơn hàng {$order->id} của bạn đã thay đổi trạng thái từ '{$oldLabel}' sang '{$newLabel}'.",
            ]);

            // Gửi email thông báo cập nhật trạng thái đơn hàng mới cho khách hàng
            try {
                $order->load(['user', 'items']);
                if ($order->user && $order->user->email) {
                    Mail::to($order->user->email)->send(new OrderMail($order));
                }
            } catch (\Exception $e) {
                \Log::error('Gửi mail cập nhật trạng thái đơn hàng thất bại: ' . $e->getMessage());
            }
        }

        return redirect()->route('admin.orders.show', $order)->with('success', 'Trạng thái đơn hàng đã được cập nhật.');
    }
}
