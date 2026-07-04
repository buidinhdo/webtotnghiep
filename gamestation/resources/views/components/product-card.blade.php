@props(['product', 'showLoginCta' => true, 'showRemoveWishlist' => false, 'showWishlist' => true])

@php
    use Illuminate\Support\Str;
    $primaryImg = $product->primaryImage ?? $product->images->first();
    $image = $primaryImg?->image_path ? asset($primaryImg->image_path) : 'https://placehold.co/600x400?text=GameStation';

    // Lấy ảnh phụ đầu tiên (không phải ảnh chính)
    $allImages = $product->relationLoaded('images') ? $product->images : collect();
    $secondaryImg = $allImages->first(function ($img) use ($primaryImg) {
        return $img->id !== ($primaryImg?->id);
    });
    $hoverImage = $secondaryImg?->image_path ? asset($secondaryImg->image_path) : null;
@endphp

<div class="gs-product-card relative">
    @if($showWishlist)
        @auth
            <form method="POST" action="{{ route('wishlist.toggle', $product) }}" class="absolute top-3 right-3 z-10">
                @csrf
                <button type="submit" class="p-2 rounded-full bg-white/85 hover:bg-white text-rose-500 hover:text-rose-600 shadow transition-all duration-300 transform hover:scale-110 flex items-center justify-center focus:outline-none" title="{{ auth()->user()->wishlist()->where('product_id', $product->id)->exists() ? __('ui.remove_from_wishlist') : __('ui.add_to_wishlist') }}">
                    @if(auth()->user()->wishlist()->where('product_id', $product->id)->exists())
                        <svg class="h-4 w-4 fill-rose-500 text-rose-500" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                        </svg>
                    @else
                        <svg class="h-4 w-4 text-slate-400 hover:text-rose-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                        </svg>
                    @endif
                </button>
            </form>
        @else
            <a href="{{ route('login') }}" class="absolute top-3 right-3 z-10 p-2 rounded-full bg-white/85 hover:bg-white text-slate-400 hover:text-rose-500 shadow transition-all duration-300 transform hover:scale-110 flex items-center justify-center" title="{{ __('ui.add_to_wishlist') }}">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                </svg>
            </a>
        @endauth
    @endif

    <a href="{{ route('products.show', $product) }}" class="gs-product-image gs-product-image--hoverable">
        {{-- Ảnh chính --}}
        <img src="{{ $image }}" alt="{{ $product->name }}" class="gs-product-img gs-product-img--main">
        {{-- Ảnh phụ (hiện khi hover, chỉ render nếu có) --}}
        @if($hoverImage)
            <img src="{{ $hoverImage }}" alt="{{ $product->name }}" class="gs-product-img gs-product-img--hover" loading="lazy">
        @endif
    </a>
    <div class="gs-product-body">
        <h3 class="text-base font-semibold text-slate-900 line-clamp-2 leading-normal">
            <a href="{{ route('products.show', $product) }}">{{ $product->name }}</a>
        </h3>
        @if($product->publisher)
            <div class="mt-1 text-xs text-slate-500">{{ $product->publisher->name }}</div>
        @endif
        <div class="mt-3">
            <span class="text-lg font-bold text-rose-600">{{ number_format($product->price, 0, ',', '.') }}đ</span>
        </div>
    </div>
    <div class="gs-product-actions mt-4 text-center">
        @if($showLoginCta)
            @auth
                <form method="POST" action="{{ route('cart.add') }}" class="flex items-center justify-center gap-2">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <input type="hidden" name="quantity" value="1">
                    <button type="submit" class="gs-button px-4 py-1.5 text-xs font-semibold rounded-lg transition-all text-center">
                        <i class="fas fa-shopping-cart mr-1"></i>Thêm
                    </button>
                    <button type="submit" name="buy_now" value="1" class="inline-block text-center rounded-lg border border-slate-300 px-4 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
                        <i class="fas fa-bolt mr-1"></i>Mua
                    </button>
                </form>
            @else
                <div class="flex justify-center">
                    <a href="{{ route('login', ['next_action' => 'cart_add', 'product_id' => $product->id, 'quantity' => 1]) }}" class="inline-block gs-button px-4 py-1.5 text-xs font-semibold rounded-lg transition-all text-center">
                        <i class="fas fa-sign-in-alt mr-1"></i>Đăng nhập để mua
                    </a>
                </div>
            @endauth
        @endif

        @if($showRemoveWishlist)
            <form method="POST" action="{{ route('wishlist.toggle', $product) }}" class="mt-3 pt-2 border-t border-slate-100">
                @csrf
                <button type="submit" class="w-full text-center py-1.5 text-xs font-semibold text-rose-600 bg-rose-50/50 hover:bg-rose-50 rounded-lg transition-colors flex items-center justify-center gap-1 focus:outline-none">
                    <svg class="h-3.5 w-3.5 fill-rose-600" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                    </svg>
                    {{ __('ui.remove_from_wishlist') }}
                </button>
            </form>
        @endif
    </div>
</div>
