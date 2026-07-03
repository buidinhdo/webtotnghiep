<x-app-layout>
    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-semibold text-slate-900">{{ __('ui.wishlist') }}</h1>

        @if($products->isEmpty())
            <div class="gs-card p-8 text-center text-slate-600 mt-6 bg-white rounded-2xl border border-slate-100 shadow-md">
                <div class="flex flex-col items-center justify-center py-6">
                    <svg class="h-16 w-16 text-slate-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                    </svg>
                    <p class="text-lg font-medium text-slate-500 mb-4">{{ __('ui.no_wishlist') }}</p>
                    <a href="{{ route('products.index') }}" class="gs-button px-6 py-2.5 rounded-full font-semibold transition-all">
                        {{ __('ui.buy_now') }}
                    </a>
                </div>
            </div>
        @else
            <div class="mt-8 grid gap-6 grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
                @foreach($products as $product)
                    <x-product-card :product="$product" :show-login-cta="true" :show-remove-wishlist="true" />
                @endforeach
            </div>
        @endif
    </section>
</x-app-layout>
