<footer class="gs-footer">
    <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="grid gap-8 md:grid-cols-4">
            <div>
                <div class="flex items-center gap-2">
                    <x-application-logo class="h-9 w-auto" />
                    <span class="text-lg font-extrabold tracking-wide">GameStation</span>
                </div>
                <p class="mt-4 text-sm text-slate-300">
                    {{ \App\Models\Setting::get('store_description', __('ui.footer_desc')) }}
                </p>
                <p class="mt-4 text-sm text-slate-300">Hotline: {{ \App\Models\Setting::get('store_hotline', '0900 000 000') }}</p>
                <p class="text-sm text-slate-300">Email: {{ \App\Models\Setting::get('store_email', 'support@gamestation.test') }}</p>
            </div>
            <div>
                <h3 class="gs-footer-title">{{ __('ui.category') }}</h3>
                <ul class="mt-4 space-y-2 text-sm">
                    <li><a href="{{ route('products.index', ['category' => 'ps5']) }}" class="gs-footer-link">PlayStation 5</a></li>
                    <li><a href="{{ route('products.index', ['category' => 'ps4']) }}" class="gs-footer-link">PlayStation 4</a></li>
                    <li><a href="{{ route('products.index', ['category' => 'switch']) }}" class="gs-footer-link">Nintendo Switch</a></li>
                </ul>
            </div>
            <div>
                <h3 class="gs-footer-title">{{ __('ui.support') }}</h3>
                <ul class="mt-4 space-y-2 text-sm">
                    <li><a href="{{ route('contact') }}" class="gs-footer-link">{{ __('ui.contact') }}</a></li>
                    <li><a href="{{ route('products.index') }}" class="gs-footer-link">{{ __('ui.products') }}</a></li>
                    <li><a href="{{ route('orders.index') }}" class="gs-footer-link">{{ __('ui.orders') }}</a></li>
                </ul>
            </div>
            <div>
                <h3 class="gs-footer-title">{{ __('ui.store_info') }}</h3>
                <p class="mt-4 text-sm text-slate-300">HCM: {{ \App\Models\Setting::get('store_address_hcm', '123 Nguyễn Huệ, TP. HCM') }}</p>
                <p class="mt-2 text-sm text-slate-300">HN: {{ \App\Models\Setting::get('store_address_hn', '88 Consoles Road, Hai Bà Trưng') }}</p>
                <div class="mt-4 flex gap-3">
                    <span class="gs-footer-badge">{{ __('ui.fast_delivery') }}</span>
                    <span class="gs-footer-badge">{{ __('ui.genuine') }}</span>
                </div>
            </div>
        </div>
        <div class="mt-10 border-t border-slate-700/60 pt-6 text-xs text-slate-400">
            {{ __('ui.copyright') }}
        </div>
    </div>
</footer>
