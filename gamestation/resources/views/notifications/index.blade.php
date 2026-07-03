<x-app-layout>
    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-semibold text-slate-900">Thông báo</h1>

        <div class="mt-6 space-y-4">
            @forelse ($notifications as $notification)
                @php
                    $orderId = null;
                    if (preg_match('/Đơn hàng #?(\d+)/', $notification->body, $matches)) {
                        $orderId = $matches[1];
                    }
                    $product = null;
                    if (preg_match('/\(Mã SP:\s*(\d+)\)/', $notification->body, $matches)) {
                        $product = \App\Models\Product::find($matches[1]);
                    } elseif (preg_match('/Sản phẩm "([^"]+)"/', $notification->body, $matches)) {
                        $productName = $matches[1];
                        $product = \App\Models\Product::where('name', $productName)->first();
                    }
                @endphp
                <div class="gs-card p-5 {{ $notification->read_at ? 'opacity-70' : '' }} hover:shadow-md transition">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex-1">
                            <p class="text-lg font-semibold text-slate-900">{{ $notification->title }}</p>
                            <p class="mt-2 text-sm text-slate-600 whitespace-pre-line">{{ $notification->body }}</p>
                            <p class="mt-2 text-xs text-slate-400">{{ $notification->created_at?->format('d/m/Y H:i') }}</p>
                            
                            @if ($orderId)
                                <div class="mt-3">
                                    @if (!$notification->read_at)
                                        <form method="POST" action="{{ route('notifications.read', $notification) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="text-sm font-semibold text-sky-600 hover:text-sky-700">Xem chi tiết đơn hàng {{ $orderId }} →</button>
                                        </form>
                                    @else
                                        <a href="{{ route('orders.show', $orderId) }}" class="text-sm font-semibold text-sky-600 hover:text-sky-700">Xem chi tiết đơn hàng {{ $orderId }} →</a>
                                    @endif
                                </div>
                            @endif

                            @if ($product)
                                <div class="mt-3">
                                    @if (!$notification->read_at)
                                        <form method="POST" action="{{ route('notifications.read', $notification) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="text-sm font-semibold text-sky-600 hover:text-sky-700">Xem phản hồi đánh giá →</button>
                                        </form>
                                    @else
                                        <a href="{{ route('products.show', ['product' => $product, 'tab' => 'reviews']) }}" class="text-sm font-semibold text-sky-600 hover:text-sky-700">Xem phản hồi đánh giá →</a>
                                    @endif
                                </div>
                            @endif
                        </div>
                        
                        @if (!$notification->read_at && !$orderId && !$product)
                            <form method="POST" action="{{ route('notifications.read', $notification) }}">
                                @csrf
                                <button class="text-xs font-semibold text-sky-600">Đánh dấu đã đọc</button>
                            </form>
                        @endif
                    </div>
                </div>
            @empty
                <div class="gs-card p-6 text-slate-600">Chưa có thông báo nào.</div>
            @endforelse
        </div>

        <div class="mt-8">
            {{ $notifications->links() }}
        </div>
    </section>
</x-app-layout>
