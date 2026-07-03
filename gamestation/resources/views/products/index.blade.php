<x-app-layout>
    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
            <div class="xl:max-w-md">
                <h1 class="text-3xl font-semibold text-slate-900">{{ __('ui.all_products') }}</h1>
                <p class="mt-2 text-sm text-slate-600">{{ __('ui.filter_subtitle') }}</p>
            </div>
            <form method="GET" class="w-full xl:flex-1 gs-card p-4">
                <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-6">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('ui.search_placeholder') }}" class="w-full rounded-xl border-slate-200" />
                    <select name="category" class="w-full rounded-xl border-slate-200">
                        <option value="">{{ __('ui.category') }}</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->slug }}" @selected(request('category') === $category->slug)>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    <select name="platform" class="w-full rounded-xl border-slate-200">
                        <option value="">{{ __('ui.platform') }}</option>
                        <option value="ps4" @selected(request('platform') === 'ps4')>PS4</option>
                        <option value="ps5" @selected(request('platform') === 'ps5')>PS5</option>
                        <option value="switch" @selected(request('platform') === 'switch')>Switch</option>
                    </select>
                    <select name="publisher" class="w-full rounded-xl border-slate-200">
                        <option value="">{{ __('ui.publisher') }}</option>
                        @foreach($publishers as $pub)
                            <option value="{{ $pub->id }}" @selected(request('publisher') == $pub->id)>{{ $pub->name }}</option>
                        @endforeach
                    </select>
                    <select name="genre" class="w-full rounded-xl border-slate-200">
                        <option value="">{{ __('ui.genre') }}</option>
                        @foreach($genres as $g)
                            <option value="{{ $g }}" @selected(request('genre') === $g)>{{ $g }}</option>
                        @endforeach
                    </select>
                    <select name="sort" class="w-full rounded-xl border-slate-200">
                        <option value="">{{ __('ui.newest') }}</option>
                        <option value="price_asc" @selected(request('sort') === 'price_asc')>{{ __('ui.price_asc') }}</option>
                        <option value="price_desc" @selected(request('sort') === 'price_desc')>{{ __('ui.price_desc') }}</option>
                        <option value="bestseller" @selected(request('sort') === 'bestseller')>{{ __('ui.bestsellers') }}</option>
                    </select>
                </div>
                <div class="mt-3 grid gap-3 md:grid-cols-2 xl:grid-cols-2">
                    <input type="number" name="min_price" value="{{ request('min_price') }}" placeholder="{{ __('ui.price_from') }}" class="w-full rounded-xl border-slate-200" />
                    <input type="number" name="max_price" value="{{ request('max_price') }}" placeholder="{{ __('ui.price_to') }}" class="w-full rounded-xl border-slate-200" />
                </div>
                <div class="mt-3">
                    <select name="esrb" class="w-full rounded-xl border-slate-200">
                        <option value="">{{ __('ui.esrb_rating') }}</option>
                        <option value="E" @selected(request('esrb') === 'E')>E - Everyone</option>
                        <option value="E10" @selected(request('esrb') === 'E10')>E10 - Everyone 10+</option>
                        <option value="T" @selected(request('esrb') === 'T')>T - Teen</option>
                        <option value="M" @selected(request('esrb') === 'M')>M - Mature</option>
                        <option value="AO" @selected(request('esrb') === 'AO')>AO - Adults Only</option>
                    </select>
                </div>
                <div class="mt-4 flex items-center justify-between">
                    <button class="gs-button" type="submit">{{ __('ui.apply') }}</button>
                    <a href="{{ route('products.index') }}" class="text-sm font-semibold text-slate-600">{{ __('ui.clear_filters') }}</a>
                </div>
            </form>
        </div>

        <div class="mt-8 grid gap-6 md:grid-cols-2 lg:grid-cols-4">
            @forelse ($products as $product)
                <x-product-card :product="$product" :show-login-cta="false" />
            @empty
                <div class="gs-card p-6 text-slate-600">{{ __('ui.no_products') }}</div>
            @endforelse
        </div>

        <div class="mt-8">
            {{ $products->links() }}
        </div>
    </section>
</x-app-layout>
