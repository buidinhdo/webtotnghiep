<x-app-layout>
    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between">
            <h1 class="text-3xl font-semibold text-slate-900">Đơn hàng của bạn</h1>
            <a href="{{ route('products.index') }}" class="text-sm font-semibold text-sky-600">Mua tiếp</a>
        </div>

        <div class="mt-6 space-y-4">
            @forelse ($orders as $order)
                <div class="gs-card p-6">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div>
                            <p class="text-sm text-slate-500">Đơn hàng {{ $order->id }}</p>
                            <p class="text-lg font-semibold text-slate-900">{{ number_format($order->total, 0, ',', '.') }}đ</p>
                        </div>
                        <div class="text-sm text-slate-600">
                            <p>Trạng thái: <span class="font-semibold text-slate-900">{{ $order->status_label }}</span></p>
                            <p>Thanh toán: <span class="font-semibold text-slate-900">{{ $order->payment_status_label }}</span></p>
                        </div>
                        <a href="{{ route('orders.show', $order) }}" class="text-sm font-semibold text-sky-600">Xem chi tiết</a>
                    </div>
                </div>
            @empty
                <div class="gs-card p-6 text-slate-600">Chưa có đơn hàng nào.</div>
            @endforelse
        </div>

        <div class="mt-8">
            {{ $orders->links() }}
        </div>
    </section>
</x-app-layout>
