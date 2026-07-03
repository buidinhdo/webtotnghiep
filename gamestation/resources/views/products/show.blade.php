<x-app-layout>
    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        @php
            $detailRows = [];

            if (!empty($product->detailed_description)) {
                foreach (preg_split('/\r\n|\r|\n/', trim($product->detailed_description)) as $line) {
                    $line = trim($line);

                    if ($line === '') {
                        continue;
                    }

                    $label = null;
                    $value = $line;

                    if (str_contains($line, "\t")) {
                        [$label, $value] = array_map('trim', explode("\t", $line, 2));
                    } elseif (str_contains($line, ':')) {
                        [$label, $value] = array_map('trim', explode(':', $line, 2));
                    }

                    if (!empty($label)) {
                        $detailRows[] = [$label, $value];
                    }
                }
            }
        @endphp

        <!-- Breadcrumb -->
        <nav class="mb-8 flex" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ route('home') }}" class="inline-flex items-center text-sm font-medium text-slate-600 hover:text-slate-900">
                        <svg class="mr-2 h-4 w-4" fill="currentColor" viewBox="0 0 20 20"><path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path></svg>
                        {{ __('ui.home') }}
                    </a>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="h-5 w-5 text-slate-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L12.586 8 7.293 2.707a1 1 0 011.414-1.414l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                        <a href="{{ route('products.index') }}" class="ml-1 text-sm font-medium text-slate-600 hover:text-slate-900 md:ml-2">{{ __('ui.products') }}</a>
                    </div>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <svg class="h-5 w-5 text-slate-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L12.586 8 7.293 2.707a1 1 0 011.414-1.414l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                        <span class="ml-1 text-sm font-medium text-slate-500 md:ml-2">{{ $product->name }}</span>
                    </div>
                </li>
            </ol>
        </nav>

        <!-- Main Product Section -->
        <div class="grid gap-8 lg:grid-cols-3 mb-8">
            <!-- Product Images Section (Left) -->
            <div class="lg:col-span-2">
                <div class="gs-card overflow-hidden bg-white">
                    <!-- Main Image -->
                    <div class="relative bg-slate-50 aspect-square">
                        @php
                            $mainImage = $product->primaryImage ?? $product->images->first();
                        @endphp
                        <img id="mainProductImage" 
                             src="{{ $mainImage ? asset($mainImage->image_path) : 'https://placehold.co/600x600?text=No+Image' }}" 
                             alt="{{ $product->name }}" 
                             class="h-full w-full object-cover">
                        
                        @if($product->stock <= 0)
                            <div class="absolute inset-0 bg-black/50 flex items-center justify-center">
                                <span class="text-2xl font-bold text-white">{{ __('ui.out_of_stock') }}</span>
                            </div>
                        @endif
                    </div>
                    
                    <!-- Image Thumbnails -->
                    @if($product->images->count() > 0)
                        <div class="p-4">
                            <div class="flex gap-2 overflow-x-auto pb-2">
                                @foreach($product->images as $image)
                                    <button onclick="document.getElementById('mainProductImage').src = '{{ asset($image->image_path) }}'; this.classList.add('ring-2', 'ring-sky-500'); document.querySelectorAll('.thumbnail-btn').forEach(b => b !== this && b.classList.remove('ring-2', 'ring-sky-500'));"
                                            class="thumbnail-btn flex-shrink-0 h-24 w-24 rounded-lg border-2 border-slate-200 overflow-hidden hover:border-sky-500 transition-colors {{ $loop->first ? 'ring-2 ring-sky-500' : '' }}">
                                        <img src="{{ asset($image->image_path) }}" alt="{{ $product->name }}" class="h-full w-full object-cover">
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Product Info Sidebar (Right) -->
            <div>
                <!-- Category Badge -->
                <div class="mb-4 flex items-center gap-2">
                    @if($product->category)
                        <span class="inline-block bg-sky-100 text-sky-700 px-4 py-2 rounded-full text-xs font-semibold uppercase tracking-wide">
                            {{ $product->category->name }}
                        </span>
                    @endif
                    <span class="inline-block bg-purple-100 text-purple-700 px-4 py-2 rounded-full text-xs font-semibold uppercase tracking-wide">
                        {{ strtoupper($product->platform ?? 'N/A') }}
                    </span>
                </div>

                <!-- Title -->
                <h1 class="text-3xl font-bold text-slate-900 mb-4 leading-tight">
                    {{ $product->name }}
                </h1>

                <!-- Rating -->
                @if($product->reviews->count() > 0)
                    <div class="mb-6 flex items-center gap-3">
                        @php
                            $avgRating = $product->reviews->avg('rating');
                            $ratingCount = $product->reviews->count();
                        @endphp
                        <div class="flex items-center">
                            @for($i = 1; $i <= 5; $i++)
                                <svg class="h-5 w-5 {{ $i <= round($avgRating) ? 'text-yellow-400' : 'text-slate-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                </svg>
                            @endfor
                        </div>
                        <span class="text-sm text-slate-600">
                            <span class="font-semibold text-slate-900">{{ number_format($avgRating, 1) }}</span>
                            ({{ trans_choice('ui.review_count', $ratingCount, ['count' => $ratingCount]) }})
                        </span>
                    </div>
                @endif

                <!-- Short Description -->
                @if($product->short_description)
                    <p class="text-slate-600 mb-6 leading-relaxed">
                        {{ $product->short_description }}
                    </p>
                @endif

                <!-- Price Section -->
                <div class="mb-8 rounded-lg bg-gradient-to-r from-sky-50 to-blue-50 p-6 border border-sky-200">
                    <div class="mb-4">
                        <span class="text-sm font-semibold text-slate-600 uppercase tracking-wider">{{ __('ui.price') }}</span>
                        <div class="mt-2 flex items-baseline gap-2">
                            <span class="text-4xl font-bold text-slate-900">
                                {{ number_format($product->price, 0, ',', '.') }}
                            </span>
                            <span class="text-lg font-semibold text-slate-600">đ</span>
                        </div>
                    </div>
                    
                    <!-- Stock Status -->
                    <div class="flex items-center justify-between pt-4 border-t border-sky-200">
                        <span class="text-sm text-slate-600">{{ __('ui.stock') }}:</span>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold {{ $product->stock > 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $product->stock > 0 ? trans_choice('ui.stock_count', $product->stock, ['count' => $product->stock]) : __('ui.out_of_stock') }}
                        </span>
                    </div>
                </div>

                <!-- Add to Cart & Buy Now -->
                @auth
                    @if($product->stock > 0)
                        <form method="POST" action="{{ route('cart.add') }}" class="space-y-3 mb-6">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                            
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">{{ __('ui.quantity') }}</label>
                                <div class="flex items-center gap-1 rounded-lg border-2 border-slate-300 bg-white p-1">
                                    <button type="button" class="h-10 w-10 rounded-md hover:bg-slate-100 transition-colors font-semibold text-slate-600"
                                             onclick="let input = document.querySelector('input[name=quantity]'); input.value = Math.max(1, parseInt(input.value) - 1);">−</button>
                                    <input type="number" name="quantity" min="1" max="{{ $product->stock }}" value="1" 
                                           class="flex-1 text-center border-0 px-2 py-2 text-lg font-semibold focus:outline-none">
                                    <button type="button" class="h-10 w-10 rounded-md hover:bg-slate-100 transition-colors font-semibold text-slate-600"
                                             onclick="let input = document.querySelector('input[name=quantity]'); input.value = Math.min({{ $product->stock }}, parseInt(input.value) + 1);">+</button>
                                </div>
                            </div>
                            
                            <!-- Add to Cart Button -->
                            <button class="w-full gs-button py-3 text-center font-semibold rounded-lg transition-all hover:shadow-lg" type="submit">
                                <i class="fas fa-shopping-cart mr-2"></i>{{ __('ui.add_to_cart') }}
                            </button>
                            
                            <!-- Buy Now Button -->
                            <button type="submit" name="buy_now" value="1" class="block w-full text-center mt-2 rounded-lg border-2 border-slate-300 px-5 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
                                <i class="fas fa-bolt mr-2"></i>{{ __('ui.buy_now') }}
                            </button>

                            <!-- Quick contact -->
                            <a href="{{ route('contact', ['product_id' => $product->id]) }}" class="block w-full text-center mt-2 rounded-lg border-2 border-slate-200 px-5 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-50 transition-colors">
                                <i class="fas fa-comment-dots mr-2"></i>{{ __('ui.quick_consult') }}
                            </a>

                            <!-- Wishlist Button -->
                            <button type="submit" formaction="{{ route('wishlist.toggle', $product) }}" class="w-full text-center mt-2 rounded-lg border-2 {{ auth()->user()->wishlist()->where('product_id', $product->id)->exists() ? 'border-rose-300 bg-rose-50 text-rose-600 hover:bg-rose-100 hover:border-rose-400' : 'border-slate-200 text-slate-500 hover:bg-slate-50 hover:text-rose-500 hover:border-rose-300' }} px-5 py-3 text-sm font-semibold transition-colors flex items-center justify-center gap-2 focus:outline-none">
                                @if(auth()->user()->wishlist()->where('product_id', $product->id)->exists())
                                    <svg class="h-5 w-5 fill-rose-600 text-rose-600" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                                    </svg>
                                    {{ __('ui.remove_from_wishlist') }}
                                @else
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                                    </svg>
                                    {{ __('ui.add_to_wishlist') }}
                                @endif
                            </button>
                        </form>
                    @else
                        <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded">
                            <p class="text-sm font-semibold text-red-700">{{ __('ui.out_of_stock') }}</p>
                        </div>
                    @endif
                    @else
                    <div class="mb-6 space-y-3">
                        <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg">
                            <p class="text-sm text-blue-900 mb-3">{{ __('ui.login_to_buy') }}</p>
                            <a href="{{ route('login', ['next_action' => 'cart_add', 'product_id' => $product->id, 'quantity' => 1]) }}" class="w-full block text-center gs-button py-3 font-semibold rounded-lg transition-all hover:shadow-lg">
                                <i class="fas fa-sign-in-alt mr-2"></i>{{ __('ui.login_btn_cart') }}
                            </a>
                            
                            <a href="{{ route('login', ['next_action' => 'buy_now', 'product_id' => $product->id, 'quantity' => 1]) }}" class="w-full block text-center mt-2 rounded-lg border-2 border-blue-300 px-5 py-3 text-sm font-semibold text-blue-700 hover:bg-blue-50 transition-colors">
                                <i class="fas fa-bolt mr-2"></i>{{ __('ui.login_btn_buy') }}
                            </a>

                            <a href="{{ route('contact', ['product_id' => $product->id]) }}" class="w-full block text-center mt-2 rounded-lg border-2 border-transparent px-5 py-3 text-sm font-semibold text-sky-700 hover:bg-sky-50 transition-colors">
                                <i class="fas fa-comment-dots mr-2"></i>{{ __('ui.quick_consult') }}
                            </a>

                            <a href="{{ route('login') }}" class="w-full block text-center mt-2 rounded-lg border-2 border-slate-200 text-slate-500 hover:bg-slate-50 hover:text-rose-500 hover:border-rose-300 px-5 py-3 text-sm font-semibold transition-colors flex items-center justify-center gap-2" title="{{ __('ui.add_to_wishlist') }}">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                                </svg>
                                {{ __('ui.add_to_wishlist') }}
                            </a>
                        </div>
                    </div>
                @endauth

            </div>
        </div>

        <!-- Tabs Section -->
        <div class="mt-8 mb-12">
            <!-- Tab Navigation -->
            <div class="mb-6 border-b border-slate-200">
                <div class="flex gap-8 overflow-x-auto">
                    <button class="tab-button active px-4 py-3 font-semibold text-slate-900 border-b-2 border-sky-600 transition-colors"
                            onclick="switchTab(event, 'description')">
                        {{ __('ui.specs_tab') }}
                    </button>
                    <button class="tab-button px-4 py-3 font-semibold text-slate-600 hover:text-slate-900 border-b-2 border-transparent transition-colors"
                            onclick="switchTab(event, 'reviews')">
                        {{ __('ui.reviews_tab', ['count' => $product->reviews->count()]) }}
                    </button>
                </div>
            </div>

            <!-- Description Tab -->
            <div id="description-tab" class="tab-content">
                <div class="gs-card bg-white p-6 md:p-8">
                    @if(count($detailRows))
                        <div class="mx-auto w-full overflow-x-auto rounded-xl border border-slate-300">
                            <table class="w-full border-collapse bg-white">
                                <tbody>
                                    @foreach($detailRows as [$label, $value])
                                        <tr>
                                            <th scope="row" class="w-[30%] border border-slate-300 bg-slate-50 px-5 py-4 text-left text-lg font-semibold text-slate-900">
                                                {{ $label }}
                                            </th>
                                            <td class="border border-slate-300 px-5 py-4 text-lg text-slate-800 whitespace-pre-wrap">
                                                {{ $value }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-center text-slate-500 py-8">{{ __('ui.no_specs') }}</p>
                    @endif
                </div>
            </div>

            <!-- Reviews Tab -->
            <div id="reviews-tab" class="tab-content hidden">
                <div class="gs-card p-8 bg-white">
                    <!-- Reviews List -->
                    <div class="mb-8 space-y-6">
                        @forelse($product->reviews as $review)
                            <div class="border-b border-slate-200 pb-6 last:border-b-0">
                                <div class="mb-3 flex items-start justify-between">
                                    <div>
                                        <p class="font-semibold text-slate-900">{{ $review->user->name ?? __('ui.user') }}</p>
                                        <div class="mt-1 flex items-center gap-3">
                                            <div class="flex items-center">
                                                @for($i = 1; $i <= 5; $i++)
                                                    <svg class="h-4 w-4 {{ $i <= $review->rating ? 'text-yellow-400' : 'text-slate-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                                    </svg>
                                                @endfor
                                            </div>
                                            <span class="text-xs text-slate-500">{{ $review->created_at?->format('d/m/Y') }}</span>
                                        </div>
                                    </div>
                                </div>
                                <p class="text-slate-700 leading-relaxed">{{ $review->comment }}</p>

                                <!-- Admin Reply -->
                                @if($review->admin_reply)
                                    <div class="mt-4 rounded-lg bg-blue-50 border-l-4 border-blue-500 p-4">
                                        <p class="text-xs font-semibold text-blue-900 uppercase tracking-wider mb-2">{{ __('ui.admin_reply') }}</p>
                                        <p class="text-sm text-blue-800">{{ $review->admin_reply }}</p>
                                        <p class="text-xs text-blue-600 mt-2">{{ $review->admin_replied_at?->format('d/m/Y H:i') }}</p>
                                    </div>
                                @endif
                            </div>
                        @empty
                            <p class="py-8 text-center text-slate-500">{{ __('ui.no_reviews') }}</p>
                        @endforelse
                    </div>

                    <!-- Add Review Form -->
                    @auth
                        <div class="border-t border-slate-200 pt-8">
                            <h3 class="mb-6 text-lg font-semibold text-slate-900">{{ __('ui.write_review') }}</h3>
                            <form method="POST" action="{{ route('reviews.store', $product) }}" class="space-y-4">
                                @csrf
                                
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-3">{{ __('ui.write_review') }}</label>
                                    <select name="rating" class="w-full rounded-lg border-2 border-slate-300 px-4 py-2 text-base focus:border-sky-500 focus:outline-none">
                                        <option value="5" selected>⭐⭐⭐⭐⭐ 5 sao - Tuyệt vời!</option>
                                        <option value="4">⭐⭐⭐⭐ 4 sao - Rất tốt</option>
                                        <option value="3">⭐⭐⭐ 3 sao - Tốt</option>
                                        <option value="2">⭐⭐ 2 sao - Bình thường</option>
                                        <option value="1">⭐ 1 sao - Không tốt</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-3">{{ __('ui.your_comment') }}</label>
                                    <textarea name="comment" rows="5" 
                                              class="w-full rounded-lg border-2 border-slate-300 px-4 py-2 text-base focus:border-sky-500 focus:outline-none resize-none"
                                              placeholder="{{ __('ui.comment_placeholder') }}">{{ old('comment') }}</textarea>
                                    @error('comment')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <button type="submit" class="gs-button px-8 py-3 font-semibold rounded-lg transition-all hover:shadow-lg">
                                    {{ __('ui.submit_review') }}
                                </button>
                            </form>
                        </div>
                    @else
                        <div class="border-t border-slate-200 pt-8">
                            <p class="text-center text-slate-600">
                                <a href="{{ route('login') }}" class="font-semibold text-sky-600 hover:text-sky-700">{{ __('ui.login') }}</a> 
                                {{ __('ui.login_to_review') }}
                            </p>
                        </div>
                    @endauth
                </div>
            </div>
        </div>

        <!-- Related Products Slider -->
        @if($related->count() > 0)
            <div class="mt-16 mb-14">
                <h2 class="text-center font-extrabold text-slate-900" style="font-size: 28px; line-height: 1.25; margin-bottom: 48px;">{{ __('ui.related_products') }}</h2>

                <div class="gs-slider gs-slider--products gs-slider--detail" data-slider>
                    <div class="gs-slider-viewport">
                        <div class="gs-slider-track" data-slider-track>
                            @foreach($related as $item)
                                <div class="gs-slide">
                                    <x-product-card :product="$item" />
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="gs-slider-controls">
                        <button type="button" class="gs-slider-button" data-slider-prev aria-label="Trước">&#8249;</button>
                        <button type="button" class="gs-slider-button" data-slider-next aria-label="Tiếp">&#8250;</button>
                    </div>
                </div>
            </div>
        @endif
    </section>

    <style>
        .scrollbar-hide::-webkit-scrollbar {
            display: none;
        }
        .scrollbar-hide {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        
        .tab-button {
            cursor: pointer;
        }
        
        .tab-button.active {
            color: #0f172a;
        }

        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
    </style>

    <script>
        function switchTab(event, tabName) {
            event.preventDefault();

            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.add('hidden');
            });

            // Remove active class from all buttons
            document.querySelectorAll('.tab-button').forEach(button => {
                button.classList.remove('active');
                button.classList.add('text-slate-600', 'hover:text-slate-900', 'border-transparent');
                button.classList.remove('text-slate-900', 'border-sky-600');
            });

            // Show selected tab
            const selectedTab = document.getElementById(tabName + '-tab');
            if (selectedTab) {
                selectedTab.classList.remove('hidden');
            }

            // Add active class to clicked button
            event.target.classList.add('active');
            event.target.classList.remove('text-slate-600', 'hover:text-slate-900', 'border-transparent');
            event.target.classList.add('text-slate-900', 'border-sky-600', 'border-b-2');
        }

        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('[data-slider]').forEach((slider) => {
                const track = slider.querySelector('[data-slider-track]');
                const slides = slider.querySelectorAll('.gs-slide');
                const prevButton = slider.querySelector('[data-slider-prev]');
                const nextButton = slider.querySelector('[data-slider-next]');

                if (!track || slides.length === 0) return;

                let index = 0;

                const getMaxIndex = () => {
                    const width = slides[0].offsetWidth || 1;
                    const perView = Math.max(1, Math.round(slider.offsetWidth / width));
                    return Math.max(0, slides.length - perView);
                };

                const update = () => {
                    const width = slides[0].offsetWidth || 1;
                    const maxIndex = getMaxIndex();
                    index = Math.max(0, Math.min(index, maxIndex));
                    track.style.transform = `translateX(-${index * width}px)`;
                };

                if (prevButton) {
                    prevButton.addEventListener('click', () => {
                        index = Math.max(0, index - 1);
                        update();
                    });
                }

                if (nextButton) {
                    nextButton.addEventListener('click', () => {
                        const maxIndex = getMaxIndex();
                        index = Math.min(maxIndex, index + 1);
                        update();
                    });
                }

                update();
                window.addEventListener('resize', update);
            });
        });

        // Open tab based on URL query parameter or hash, fallback to first tab
        window.addEventListener('load', () => {
            const hash = window.location.hash;
            const urlParams = new URLSearchParams(window.location.search);
            const tab = urlParams.get('tab');
            
            if (hash === '#reviews' || tab === 'reviews') {
                const reviewBtn = Array.from(document.querySelectorAll('.tab-button')).find(btn => {
                    const attr = btn.getAttribute('onclick');
                    return attr && attr.includes('reviews');
                });
                if (reviewBtn) {
                    reviewBtn.click();
                    setTimeout(() => {
                        const reviewsTab = document.getElementById('reviews-tab');
                        if (reviewsTab) {
                            reviewsTab.scrollIntoView({ behavior: 'smooth' });
                        }
                    }, 150);
                    return;
                }
            }

            const firstButton = document.querySelector('.tab-button');
            if (firstButton) {
                firstButton.click();
            }
        });
    </script>

    <script>
        // Robust slider init for product detail: find controls even when positioned outside DOM
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.gs-slider[data-slider]').forEach((slider) => {
                const track = slider.querySelector('[data-slider-track]');
                const slides = Array.from(slider.querySelectorAll('.gs-slide'));
                if (!track || slides.length === 0) return;

                let index = 0;
                const interval = Number(slider.dataset.interval || 3000);
                let timer = null;

                const getPerView = () => {
                    const slideWidth = slides[0].offsetWidth || 1;
                    return Math.max(1, Math.round(slider.offsetWidth / slideWidth));
                };

                const getMaxIndex = () => Math.max(0, slides.length - getPerView());

                const update = () => {
                    const width = slides[0].offsetWidth || 1;
                    const maxIndex = getMaxIndex();
                    index = Math.max(0, Math.min(index, maxIndex));
                    track.style.transform = `translateX(-${index * width}px)`;
                };

                const restart = () => {
                    if (timer) clearInterval(timer);
                    if (slider.classList.contains('gs-slider--products')) {
                        timer = setInterval(() => {
                            const max = getMaxIndex();
                            index = index >= max ? 0 : index + 1;
                            update();
                        }, interval);
                    }
                };

                // Robust control lookup: inside slider, in sibling controls, or parent
                const findControl = (selector) => {
                    return slider.querySelector(selector)
                        || (slider.parentElement ? slider.parentElement.querySelector(selector) : null)
                        || document.querySelector(selector);
                };

                const prev = findControl('[data-slider-prev]');
                const next = findControl('[data-slider-next]');

                if (prev) prev.addEventListener('click', (e) => { e.stopPropagation(); index = Math.max(0, index - 1); update(); restart(); });
                if (next) next.addEventListener('click', (e) => { e.stopPropagation(); index = Math.min(getMaxIndex(), index + 1); update(); restart(); });

                slider.addEventListener('mouseenter', () => { if (timer) clearInterval(timer); });
                slider.addEventListener('mouseleave', restart);

                window.addEventListener('resize', () => { update(); });

                // initial layout
                setTimeout(() => { update(); restart(); }, 100);
            });
        });
    </script>
</x-app-layout>
