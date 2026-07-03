<x-app-layout>
    @php
        $subtotal = $cart->items->sum(fn($item) => $item->price * $item->quantity);
        $coupon = null;
        $discount = 0;
        if (session('coupon_code')) {
            $coupon = \App\Models\Coupon::where('code', session('coupon_code'))->first();
            if ($coupon && $coupon->isValidForAmount($subtotal)) {
                if (in_array($coupon->type, ['percent', 'percentage'], true)) {
                    $discount = round($subtotal * ($coupon->value / 100), 2);
                } else {
                    $discount = (float) $coupon->value;
                }
            } else {
                session()->forget('coupon_code');
            }
        }
    @endphp

    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-semibold text-slate-900">{{ __('ui.cart_title') }}</h1>

        <div class="mt-6">
            @if ($cart->items->isEmpty())
                <div class="gs-card p-6 text-slate-600 text-center py-12">
                    <p class="mb-4">{{ __('ui.cart_empty') }}</p>
                    <a href="{{ route('products.index') }}" class="inline-flex items-center justify-center rounded-full bg-slate-900 px-6 py-2.5 text-sm font-semibold text-white hover:bg-slate-800 transition">
                        Quay lại mua sắm
                    </a>
                </div>
            @else
                <div class="grid gap-6 lg:grid-cols-3">
                    <div class="lg:col-span-2 space-y-4">
                        @foreach ($cart->items as $item)
                            <div class="gs-card flex flex-wrap items-center gap-4 p-4">
                                <img src="{{ $item->product->primaryImage?->image_path ? asset($item->product->primaryImage->image_path) : 'https://placehold.co/120x120' }}" alt="{{ $item->product->name }}" class="h-20 w-20 rounded-xl object-cover">
                                <div class="flex-1">
                                    <p class="text-lg font-semibold text-slate-900">{{ $item->product->name }}</p>
                                    <p class="text-sm text-slate-600">{{ number_format($item->price, 0, ',', '.') }}đ</p>
                                </div>
                                <form method="POST" action="{{ route('cart.update', $item) }}" class="flex items-center gap-2">
                                    @csrf
                                    <input type="number" name="quantity" min="1" value="{{ $item->quantity }}" class="w-20 rounded-xl border-slate-200" />
                                    <button class="rounded-full border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-600">{{ __('ui.update') }}</button>
                                </form>
                                <form method="POST" action="{{ route('cart.remove', $item) }}">
                                    @csrf
                                    <button class="text-sm font-semibold text-rose-500">{{ __('ui.remove') }}</button>
                                </form>
                            </div>
                        @endforeach
                    </div>

                    <div class="space-y-4">
                        <div class="gs-card p-6">
                            <h3 class="text-lg font-semibold text-slate-900">{{ __('ui.summary') }}</h3>
                            <div class="mt-4 space-y-2 text-sm text-slate-600">
                                <div class="flex items-center justify-between">
                                    <span>{{ __('ui.subtotal') }}</span>
                                    <span class="font-semibold text-slate-900">{{ number_format($subtotal, 0, ',', '.') }}đ</span>
                                </div>
                                @if ($discount > 0)
                                    <div class="flex items-center justify-between">
                                        <span>Giảm giá</span>
                                        <span class="font-semibold text-emerald-600">-{{ number_format($discount, 0, ',', '.') }}đ</span>
                                    </div>
                                    <div class="flex items-center justify-between border-t border-slate-200 pt-2">
                                        <span class="font-semibold text-slate-900">Tổng cộng</span>
                                        <span class="text-lg font-bold text-slate-900">{{ number_format(max(0, $subtotal - $discount), 0, ',', '.') }}đ</span>
                                    </div>
                                @endif
                            </div>
                            <a href="{{ route('checkout.index') }}" class="mt-4 inline-flex w-full items-center justify-center rounded-full bg-slate-900 px-4 py-2 text-sm font-semibold text-white">{{ __('ui.checkout_btn') }}</a>
                        </div>

                        <div class="gs-card p-6">
                            <h3 class="text-lg font-semibold text-slate-900">{{ __('ui.coupon_code') }}</h3>
                            <form method="POST" action="{{ route('cart.coupon') }}" class="mt-4 flex gap-2">
                                @csrf
                                <input type="text" name="coupon" value="{{ session('coupon_code') }}" placeholder="{{ __('ui.enter_coupon') }}" class="w-full rounded-xl border-slate-200" />
                                <button class="gs-button" type="submit">{{ __('ui.apply') }}</button>
                            </form>
                            @if (session('coupon_code'))
                                <div class="mt-3 flex items-center justify-between text-xs text-emerald-600 bg-emerald-50 border border-emerald-200 rounded-xl p-3">
                                    <span class="font-medium">Đã áp dụng mã: <strong>{{ session('coupon_code') }}</strong></span>
                                    <span class="font-bold">-{{ number_format($discount, 0, ',', '.') }}đ</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </section>
</x-app-layout>
