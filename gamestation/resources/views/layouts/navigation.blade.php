<nav x-data="{ open: false }" class="gs-nav">
    <div class="gs-topbar">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-2 text-xs sm:px-6 lg:px-8">
            <p>Hotline: {{ \App\Models\Setting::get('store_hotline', '0900 000 000') }}</p>
            <div class="flex items-center gap-4">
                <span>{{ __('ui.support_24') }}</span>
                <span>{{ __('ui.fast_ship') }}</span>
                <span class="text-slate-500">|</span>
                <div class="flex items-center gap-2">
                    <a href="{{ route('lang.switch', 'vi') }}" class="{{ App::getLocale() === 'vi' ? 'font-bold text-sky-400' : 'text-slate-300 hover:text-white' }} transition">VI</a>
                    <span class="text-slate-500">/</span>
                    <a href="{{ route('lang.switch', 'en') }}" class="{{ App::getLocale() === 'en' ? 'font-bold text-sky-400' : 'text-slate-300 hover:text-white' }} transition">EN</a>
                </div>
            </div>
        </div>
    </div>
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center gap-8">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('home') }}" class="flex items-center gap-2">
                        <x-application-logo class="block h-9 w-auto" />
                        <span class="text-lg font-extrabold tracking-wide">GameStation</span>
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden items-center gap-4 sm:flex">
                    <x-nav-link :href="route('home')" :active="request()->routeIs('home')">
                        {{ __('ui.home') }}
                    </x-nav-link>
                    <x-nav-link :href="route('products.index')" :active="request()->routeIs('products.*')">
                        {{ __('ui.products') }}
                    </x-nav-link>
                    <x-nav-link :href="route('articles.index')" :active="request()->routeIs('articles.*')">
                        {{ __('ui.news') }}
                    </x-nav-link>
                    <x-nav-link :href="route('contact')" :active="request()->routeIs('contact')">
                        {{ __('ui.contact') }}
                    </x-nav-link>
                </div>
            </div>

            <div class="hidden items-center gap-3 lg:flex">
                <div class="relative" x-data="{
                    search: '{{ request('search') }}',
                    suggestions: [],
                    loading: false,
                    showDropdown: false,
                    fetchSuggestions() {
                        if (this.search.trim().length < 2) {
                            this.suggestions = [];
                            this.showDropdown = false;
                            return;
                        }
                        this.loading = true;
                        fetch(`/api/products/search?search=${encodeURIComponent(this.search)}`)
                            .then(res => res.json())
                            .then(data => {
                                this.suggestions = data;
                                this.showDropdown = this.suggestions.length > 0;
                                this.loading = false;
                            })
                            .catch(() => {
                                this.loading = false;
                            });
                    }
                }" @click.away="showDropdown = false">
                    <form method="GET" action="{{ route('products.index') }}" class="gs-search">
                        <svg class="h-4 w-4 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35m1.35-5.15a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z" />
                        </svg>
                        <input type="search" name="search" x-model="search" @input.debounce.300ms="fetchSuggestions()" @focus="showDropdown = suggestions.length > 0" placeholder="{{ __('ui.search') }}" class="gs-search-input" autocomplete="off">
                    </form>
                    
                    <!-- Suggestions Dropdown -->
                    <div x-show="showDropdown" 
                         class="absolute left-0 right-0 mt-2 bg-white border border-slate-200 rounded-2xl shadow-xl z-50 overflow-hidden w-80"
                         style="display: none;"
                         x-transition>
                        <ul class="divide-y divide-slate-100 max-h-80 overflow-y-auto">
                            <template x-for="item in suggestions" :key="item.id">
                                <li>
                                    <a :href="item.url" class="flex items-center gap-3 px-4 py-3 hover:bg-slate-50 transition text-slate-700 no-underline">
                                        <template x-if="item.image_url">
                                            <img :src="item.image_url" :alt="item.name" class="w-10 h-10 object-cover rounded-md border border-slate-100 flex-shrink-0">
                                        </template>
                                        <template x-if="!item.image_url">
                                            <div class="w-10 h-10 bg-slate-100 rounded-md border border-slate-100 flex items-center justify-center flex-shrink-0">
                                                <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                </svg>
                                            </div>
                                        </template>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-semibold text-slate-900 truncate" x-text="item.name"></p>
                                            <p class="text-xs text-sky-600 font-bold mt-0.5" x-text="item.price_formatted"></p>
                                        </div>
                                    </a>
                                </li>
                            </template>
                        </ul>
                    </div>
                </div>
                @auth
                    <!-- Notification Icon -->
                    <div class="relative" x-data="{ notificationOpen: false }">
                        <button @click="notificationOpen = !notificationOpen" class="gs-icon-button relative" aria-label="{{ __('ui.notification') }}">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0018 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                            @if(Auth::user()->userNotifications()->whereNull('read_at')->count() > 0)
                                <span class="absolute top-0 right-0 h-2 w-2 bg-red-500 rounded-full"></span>
                            @endif
                        </button>

                        <!-- Notification Dropdown -->
                        <div @click.away="notificationOpen = false"
                             x-show="notificationOpen"
                             class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg z-50 max-h-96 overflow-y-auto">
                            <div class="p-4 border-b border-slate-200">
                                <h3 class="font-semibold text-slate-900">{{ __('ui.notification') }}</h3>
                            </div>
                            @php
                                $notifications = Auth::user()->userNotifications()->latest()->take(10)->get();
                            @endphp
                            @if($notifications->isEmpty())
                                <div class="p-8 text-center text-slate-500 text-sm">{{ __('ui.no_notif') }}</div>
                            @else
                                @foreach($notifications as $notification)
                                    <form method="POST" action="{{ route('notifications.read', $notification) }}" style="display:inline;">
                                        @csrf
                                        <button type="submit" class="block w-full text-left p-4 hover:bg-slate-50 border-b border-slate-100 {{ $notification->read_at ? 'opacity-60' : 'bg-blue-50' }}">
                                            <p class="font-semibold text-slate-900 text-sm">{{ $notification->title }}</p>
                                            <p class="text-slate-600 text-xs mt-1 whitespace-pre-line">{{ $notification->body }}</p>
                                            <p class="text-slate-400 text-xs mt-2">{{ $notification->created_at->diffForHumans() }}</p>
                                        </button>
                                    </form>
                                @endforeach
                            @endif
                            @if($notifications->count() > 0)
                                <div class="p-3 border-t border-slate-200 text-center">
                                    <a href="{{ route('notifications.index') }}" class="text-sm text-sky-600 hover:text-sky-700 font-medium">{{ __('ui.view_all') }}</a>
                                </div>
                            @endif
                        </div>
                    </div>
                @endauth

                {{-- Wishlist Icon --}}
                @auth
                    <a href="{{ route('wishlist.index') }}" class="gs-icon-button relative" aria-label="{{ __('ui.wishlist') }}" title="{{ __('ui.wishlist') }}">
                        <svg class="h-5 w-5 text-slate-700 hover:text-rose-500 transition" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                        </svg>
                        @php
                            $wishlistCount = Auth::user()->wishlist()->count();
                        @endphp
                        @if($wishlistCount > 0)
                            <span class="absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-red-500 text-[10px] font-bold text-white">{{ $wishlistCount }}</span>
                        @endif
                    </a>
                @endauth

                <a href="{{ route('cart.index') }}" class="gs-icon-button" aria-label="{{ __('ui.cart') }}">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.6 3M7 13h10l3-7H6.6M7 13l-1.5 7h13L17 13M7 13h10" />
                    </svg>
                </a>
                @auth
                    <!-- Lucky Spin Icon Button -->
                    <a href="{{ route('lucky-spin.index') }}" class="gs-icon-button relative text-slate-700 hover:text-slate-900" aria-label="{{ __('ui.lucky_spin') }}" title="{{ __('ui.lucky_spin') }}">
                        <!-- Custom slow spin animation on a wheel SVG icon -->
                        <svg class="h-5 w-5 animate-[spin_10s_linear_infinite]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="12" cy="12" r="9" />
                            <path d="M12 3v18M3 12h18M5.636 5.636l12.728 12.728M5.636 18.364L18.364 5.636" stroke-linecap="round" />
                        </svg>
                        @php
                            $hasSpunToday = \App\Models\UserNotification::where('user_id', auth()->id())
                                ->where('title', 'Vòng quay may mắn')
                                ->where('created_at', '>=', \Carbon\Carbon::today())
                                ->exists();
                        @endphp
                        @if(!$hasSpunToday)
                            <span class="absolute top-0 right-0 h-2 w-2 bg-red-500 rounded-full animate-ping"></span>
                            <span class="absolute top-0 right-0 h-2 w-2 bg-red-500 rounded-full"></span>
                        @endif
                    </a>
                @endauth
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                @auth
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-slate-600 bg-white/70 hover:text-slate-900 focus:outline-none transition ease-in-out duration-150">
                                <div>{{ Auth::user()->name }}</div>
                                <div class="ms-1">
                                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <x-dropdown-link :href="route('profile.edit')">
                                {{ __('ui.profile') }}
                            </x-dropdown-link>

                            <x-dropdown-link :href="route('orders.index')">
                                {{ __('ui.orders') }}
                            </x-dropdown-link>

                            <!-- Authentication -->
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')"
                                        onclick="event.preventDefault();
                                                    this.closest('form').submit();">
                                    {{ __('ui.logout') }}
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                @else
                    <a href="{{ route('login') }}" class="text-sm font-semibold text-slate-700 hover:text-slate-900">{{ __('ui.login') }}</a>
                    <a href="{{ route('register') }}" class="ms-4 gs-button">{{ __('ui.register') }}</a>
                @endauth
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="px-4 pt-3">
            <div class="relative" x-data="{
                search: '{{ request('search') }}',
                suggestions: [],
                loading: false,
                showDropdown: false,
                fetchSuggestions() {
                    if (this.search.trim().length < 2) {
                        this.suggestions = [];
                        this.showDropdown = false;
                        return;
                    }
                    this.loading = true;
                    fetch(`/api/products/search?search=${encodeURIComponent(this.search)}`)
                        .then(res => res.json())
                        .then(data => {
                            this.suggestions = data;
                            this.showDropdown = this.suggestions.length > 0;
                            this.loading = false;
                        })
                        .catch(() => {
                            this.loading = false;
                        });
                }
            }" @click.away="showDropdown = false">
                <form method="GET" action="{{ route('products.index') }}" class="gs-search">
                    <svg class="h-4 w-4 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35m1.35-5.15a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z" />
                    </svg>
                    <input type="search" name="search" x-model="search" @input.debounce.300ms="fetchSuggestions()" @focus="showDropdown = suggestions.length > 0" placeholder="{{ __('ui.search') }}" class="gs-search-input" autocomplete="off">
                    <button type="submit" class="gs-search-button">{{ __('ui.search') }}</button>
                </form>
                
                <!-- Suggestions Dropdown -->
                <div x-show="showDropdown" 
                     class="absolute left-0 right-0 mt-2 bg-white border border-slate-200 rounded-2xl shadow-xl z-50 overflow-hidden w-full"
                     style="display: none;"
                     x-transition>
                    <ul class="divide-y divide-slate-100 max-h-80 overflow-y-auto">
                        <template x-for="item in suggestions" :key="item.id">
                            <li>
                                <a :href="item.url" class="flex items-center gap-3 px-4 py-3 hover:bg-slate-50 transition text-slate-700 no-underline">
                                    <template x-if="item.image_url">
                                        <img :src="item.image_url" :alt="item.name" class="w-10 h-10 object-cover rounded-md border border-slate-100 flex-shrink-0">
                                    </template>
                                    <template x-if="!item.image_url">
                                        <div class="w-10 h-10 bg-slate-100 rounded-md border border-slate-100 flex items-center justify-center flex-shrink-0">
                                            <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
                                        </div>
                                    </template>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-semibold text-slate-900 truncate" x-text="item.name"></p>
                                        <p class="text-xs text-sky-600 font-bold mt-0.5" x-text="item.price_formatted"></p>
                                    </div>
                                </a>
                            </li>
                        </template>
                    </ul>
                </div>
            </div>
        </div>
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('home')" :active="request()->routeIs('home')">
                {{ __('ui.home') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('products.index')" :active="request()->routeIs('products.*')">
                {{ __('ui.products') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('articles.index')" :active="request()->routeIs('articles.*')">
                {{ __('ui.news') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('contact')" :active="request()->routeIs('contact')">
                {{ __('ui.contact') }}
            </x-responsive-nav-link>
            @auth
                <x-responsive-nav-link :href="route('wishlist.index')" :active="request()->routeIs('wishlist.*')">
                    {{ __('ui.wishlist') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('cart.index')" :active="request()->routeIs('cart.*')">
                    {{ __('ui.cart') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('notifications.index')" :active="request()->routeIs('notifications.*')">
                    {{ __('ui.notification') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('lucky-spin.index')" :active="request()->routeIs('lucky-spin.*')">
                    {{ __('ui.lucky_spin') }}
                </x-responsive-nav-link>
            @endauth
        </div>

        <!-- Responsive Settings Options -->
        @auth
            <div class="pt-4 pb-1 border-t border-gray-200">
                <div class="px-4">
                    <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                    <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
                </div>

                <div class="mt-3 space-y-1">
                    <x-responsive-nav-link :href="route('profile.edit')">
                        {{ __('ui.profile') }}
                    </x-responsive-nav-link>

                    <x-responsive-nav-link :href="route('orders.index')">
                        {{ __('ui.orders') }}
                    </x-responsive-nav-link>


                    <!-- Authentication -->
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <x-responsive-nav-link :href="route('logout')"
                                onclick="event.preventDefault();
                                            this.closest('form').submit();">
                            {{ __('ui.logout') }}
                        </x-responsive-nav-link>
                    </form>
                </div>
            </div>
        @else
            <div class="pt-4 pb-4 border-t border-gray-200 space-y-2 px-4">
                <a href="{{ route('login') }}" class="block text-sm font-semibold text-slate-700">{{ __('ui.login') }}</a>
                <a href="{{ route('register') }}" class="block text-sm font-semibold text-sky-600">{{ __('ui.register') }}</a>
            </div>
        @endauth
    </div>
</nav>
