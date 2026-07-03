<x-app-layout>

    <section class="gs-hero-full">
        <div class="px-0 pt-6">
            <div class="gs-card gs-hero-banner gs-hero-banner--full">
                <div class="gs-slider gs-slider--hero" data-slider data-interval="3800" id="heroBannerSlider">
                    <div class="gs-slider-track" data-slider-track>
                        @forelse ($banners as $banner)
                            <div class="gs-slide">
                                <img src="{{ $banner }}" alt="Banner" class="gs-hero-image">
                            </div>
                        @empty
                            <div class="gs-slide">
                                <img src="https://placehold.co/1200x500?text=GameStation" alt="Banner" class="gs-hero-image">
                            </div>
                        @endforelse
                    </div>
                    <div class="gs-slider-dots" id="heroBannerDots">
                        @for ($i = 0; $i < max(1, count($banners)); $i++)
                            <span class="gs-dot {{ $i === 0 ? 'is-active' : '' }}" data-slide="{{ $i }}"></span>
                        @endfor
                    </div>
                </div>
                <div class="gs-hero-overlay">
                    <span class="gs-kicker">GameStation Official</span>
                    <h1 class="gs-hero-title">{{ __('ui.hero_title') }}</h1>
                    <p class="gs-hero-subtitle">{{ __('ui.hero_subtitle') }}</p>
                    <div class="mt-4 flex flex-wrap gap-3">
                        <a href="{{ route('products.index') }}" class="gs-button">{{ __('ui.buy_now') }}</a>
                        <a href="{{ route('contact', [
                            'subject' => __('ui.consult_subject'),
                            'message' => __('ui.consult_message')
                        ]) }}" class="gs-button gs-button--ghost">{{ __('ui.quick_consult') }}</a>
                    </div>
                </div>
                <div class="gs-hero-controls" style="z-index:60; pointer-events:none;">
                    <button type="button" class="gs-hero-button gs-hero-button-prev" data-slider-prev aria-label="Lùi" style="pointer-events:auto;">&#8249;</button>
                    <button type="button" class="gs-hero-button gs-hero-button-next" data-slider-next aria-label="Tiến" style="pointer-events:auto;">&#8250;</button>
                </div>
            </div>
        </div>
    </section>

    <section class="gs-section">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="gs-section-header">
            <div class="gs-title">
                <div>
                    <p class="gs-section-kicker">{{ __('ui.products') }}</p>
                    <h2 class="gs-section-title">{{ __('ui.new_products') }}</h2>
                </div>
            </div>
            <a href="{{ route('products.index') }}" class="gs-link">{{ __('ui.view_more') }}</a>
        </div>
        <div class="mt-6 gs-product-slider-shell">
        <div class="gs-slider gs-slider--products" data-slider data-interval="3000">
            <div class="gs-slider-track" data-slider-track>
                @forelse ($latest as $product)
                    <div class="gs-slide">
                        <x-product-card :product="$product" :show-login-cta="false" />
                    </div>
                @empty
                    <div class="gs-slide">
                        <div class="gs-card p-6 text-slate-600">{{ __('ui.loading_products') }}</div>
                    </div>
                @endforelse
            </div>
        </div>
        <div class="gs-product-slider-controls" data-slider-controls>
            <button type="button" class="gs-slider-button gs-product-slider-arrow gs-product-slider-arrow-prev" data-slider-prev aria-label="Trước">&#8249;</button>
            <button type="button" class="gs-slider-button gs-product-slider-arrow gs-product-slider-arrow-next" data-slider-next aria-label="Tiếp">&#8250;</button>
        </div>
        <!-- product pager removed per request -->
        </div>
    </section>

    <section class="gs-section">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="gs-section-header">
            <div class="gs-title">
                <div>
                    <p class="gs-section-kicker">{{ __('ui.featured') }}</p>
                    <h2 class="gs-section-title">{{ __('ui.featured_products') }}</h2>
                </div>
            </div>
            <a href="{{ route('products.index') }}" class="gs-link">{{ __('ui.view_more') }}</a>
        </div>
        <div class="mt-6 gs-product-slider-shell">
        <div class="gs-slider gs-slider--products" data-slider data-interval="3000">
            <div class="gs-slider-track" data-slider-track>
                @forelse ($featured as $product)
                    <div class="gs-slide">
                        <x-product-card :product="$product" :show-login-cta="false" />
                    </div>
                @empty
                    <div class="gs-slide">
                        <div class="gs-card p-6 text-slate-600">{{ __('ui.loading_products') }}</div>
                    </div>
                @endforelse
            </div>
        </div>
        <div class="gs-product-slider-controls" data-slider-controls>
            <button type="button" class="gs-slider-button gs-product-slider-arrow gs-product-slider-arrow-prev" data-slider-prev aria-label="Trước">&#8249;</button>
            <button type="button" class="gs-slider-button gs-product-slider-arrow gs-product-slider-arrow-next" data-slider-next aria-label="Tiếp">&#8250;</button>
        </div>
        <!-- product pager removed per request -->
        </div>
    </section>

    <section class="gs-section">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="gs-section-header">
            <div class="gs-title">
                <div>
                    <p class="gs-section-kicker">PlayStation 4</p>
                    <h2 class="gs-section-title">PS4</h2>
                </div>
            </div>
            <a href="{{ route('products.index', ['category' => 'ps4']) }}" class="gs-link">{{ __('ui.view_more') }}</a>
        </div>
        <div class="mt-6 gs-product-slider-shell">
        <div class="gs-slider gs-slider--products" data-slider data-interval="3000">
            <div class="gs-slider-track" data-slider-track>
                @forelse ($ps4 as $product)
                    <div class="gs-slide">
                        <x-product-card :product="$product" :show-login-cta="false" />
                    </div>
                @empty
                    <div class="gs-slide">
                        <div class="gs-card p-6 text-slate-600">{{ __('ui.loading_products') }}</div>
                    </div>
                @endforelse
            </div>
        </div>
        <div class="gs-product-slider-controls" data-slider-controls>
            <button type="button" class="gs-slider-button gs-product-slider-arrow gs-product-slider-arrow-prev" data-slider-prev aria-label="Trước">&#8249;</button>
            <button type="button" class="gs-slider-button gs-product-slider-arrow gs-product-slider-arrow-next" data-slider-next aria-label="Tiếp">&#8250;</button>
        </div>
        <!-- product pager removed per request -->
        </div>
    </section>

    <section class="gs-section">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="gs-section-header">
            <div class="gs-title">
                <div>
                    <p class="gs-section-kicker">PlayStation 5</p>
                    <h2 class="gs-section-title">PS5</h2>
                </div>
            </div>
            <a href="{{ route('products.index', ['category' => 'ps5']) }}" class="gs-link">{{ __('ui.view_more') }}</a>
        </div>
        <div class="mt-6 gs-product-slider-shell">
        <div class="gs-slider gs-slider--products" data-slider data-interval="3000">
            <div class="gs-slider-track" data-slider-track>
                @forelse ($ps5 as $product)
                    <div class="gs-slide">
                        <x-product-card :product="$product" :show-login-cta="false" />
                    </div>
                @empty
                    <div class="gs-slide">
                        <div class="gs-card p-6 text-slate-600">{{ __('ui.loading_products') }}</div>
                    </div>
                @endforelse
            </div>
        </div>
        <div class="gs-product-slider-controls" data-slider-controls>
            <button type="button" class="gs-slider-button gs-product-slider-arrow gs-product-slider-arrow-prev" data-slider-prev aria-label="Trước">&#8249;</button>
            <button type="button" class="gs-slider-button gs-product-slider-arrow gs-product-slider-arrow-next" data-slider-next aria-label="Tiếp">&#8250;</button>
        </div>
        <!-- product pager removed per request -->
        </div>
    </section>

    <section class="gs-section">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="gs-section-header">
            <div class="gs-title">
                <div>
                    <p class="gs-section-kicker">Nintendo</p>
                    <h2 class="gs-section-title">Nintendo Switch</h2>
                </div>
            </div>
            <a href="{{ route('products.index', ['category' => 'switch']) }}" class="gs-link">{{ __('ui.view_more') }}</a>
        </div>
        <div class="mt-6 gs-product-slider-shell">
        <div class="gs-slider gs-slider--products" data-slider data-interval="3000">
            <div class="gs-slider-track" data-slider-track>
                @forelse ($switch as $product)
                    <div class="gs-slide">
                        <x-product-card :product="$product" :show-login-cta="false" />
                    </div>
                @empty
                    <div class="gs-slide">
                        <div class="gs-card p-6 text-slate-600">{{ __('ui.loading_products') }}</div>
                    </div>
                @endforelse
            </div>
        </div>
        <div class="gs-product-slider-controls" data-slider-controls>
            <button type="button" class="gs-slider-button gs-product-slider-arrow gs-product-slider-arrow-prev" data-slider-prev aria-label="Trước">&#8249;</button>
            <button type="button" class="gs-slider-button gs-product-slider-arrow gs-product-slider-arrow-next" data-slider-next aria-label="Tiếp">&#8250;</button>
        </div>
        <!-- product pager removed per request -->
        </div>
    </section>

    <!-- News & Articles Section -->
    <section class="gs-section">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="gs-section-header">
                <div class="gs-title">
                    <div>
                        <p class="gs-section-kicker">{{ __('ui.news') }}</p>
                        <h2 class="gs-section-title">{{ __('ui.news_articles') }}</h2>
                    </div>
                </div>
                <a href="{{ route('articles.index') }}" class="gs-link">{{ __('ui.view_more') }}</a>
            </div>
            <div class="mt-6 gs-slider gs-slider--articles" data-slider data-interval="5000">
                <div class="gs-slider-track" data-slider-track>
                    @forelse($articles as $article)
                    <div class="gs-slide">
                        <a href="{{ route('articles.show', $article) }}" class="gs-card gs-article-card block h-full w-full overflow-hidden transition-transform hover:scale-105 text-decoration-none group">
                            @if($article->image_path)
                                <div class="aspect-video overflow-hidden">
                                    <img src="{{ asset($article->image_path) }}" alt="{{ $article->title }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform">
                                </div>
                            @else
                                <div class="aspect-video bg-gradient-to-br from-sky-400 to-sky-600 flex items-center justify-center">
                                    <i class="fas fa-image text-white text-3xl opacity-50"></i>
                                </div>
                            @endif
                            <div class="p-4 flex flex-1 flex-col">
                                <h3 class="gs-article-title font-semibold text-slate-900 group-hover:text-blue-600">{{ $article->title }}</h3>
                                <p class="gs-article-excerpt mt-2 text-sm text-slate-600">{{ $article->excerpt ?? Str::limit($article->content, 80) }}</p>
                                <p class="mt-3 text-xs text-slate-400">{{ $article->published_at?->format('d/m/Y') }}</p>
                            </div>
                        </a>
                    </div>
                    @empty
                    <div class="gs-slide">
                        <div class="gs-card p-6 text-center text-slate-600">{{ __('ui.no_articles') }}</div>
                    </div>
                    @endforelse
                </div>
                <div class="gs-slider-pager gs-slider-pager--articles" data-slider-pager></div>
            </div>
        </div>
    </section>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('[data-slider]').forEach((slider) => {
                const track = slider.querySelector('[data-slider-track]');
                const slides = slider.querySelectorAll('.gs-slide');
                const isProductSlider = slider.classList.contains('gs-slider--products');
                const controlsHost = isProductSlider
                    ? slider.nextElementSibling && slider.nextElementSibling.matches('[data-slider-controls]')
                        ? slider.nextElementSibling
                        : null
                    : slider;
                const prevButton = (controlsHost ? controlsHost.querySelector('[data-slider-prev]') : null)
                    || slider.querySelector('[data-slider-prev]')
                    || (slider.parentElement ? slider.parentElement.querySelector('[data-slider-controls] [data-slider-prev]') : null)
                    || (slider.parentElement ? slider.parentElement.querySelector('.gs-hero-controls [data-slider-prev]') : null)
                    || (slider.nextElementSibling && slider.nextElementSibling.matches('[data-slider-controls]') ? slider.nextElementSibling.querySelector('[data-slider-prev]') : null);

                const nextButton = (controlsHost ? controlsHost.querySelector('[data-slider-next]') : null)
                    || slider.querySelector('[data-slider-next]')
                    || (slider.parentElement ? slider.parentElement.querySelector('[data-slider-controls] [data-slider-next]') : null)
                    || (slider.parentElement ? slider.parentElement.querySelector('.gs-hero-controls [data-slider-next]') : null)
                    || (slider.nextElementSibling && slider.nextElementSibling.matches('[data-slider-controls]') ? slider.nextElementSibling.querySelector('[data-slider-next]') : null);
                const pager = isProductSlider
                    ? controlsHost && controlsHost.nextElementSibling && controlsHost.nextElementSibling.matches('[data-slider-pager]')
                        ? controlsHost.nextElementSibling
                        : null
                    : slider.nextElementSibling && slider.nextElementSibling.matches('[data-slider-pager]')
                        ? slider.nextElementSibling
                        : slider.parentElement.querySelector('[data-slider-pager]');
                const dots = slider.querySelectorAll('.gs-dot');
                if (!track || slides.length === 0) return;

                let index = 0;
                const interval = Number(slider.dataset.interval || 3500);
                let timerId = null;
                let pagerButtons = [];

                const getMaxIndex = () => {
                    const width = slides[0].offsetWidth || 1;
                    const perView = Math.max(1, Math.round(slider.offsetWidth / width));
                    return Math.max(0, slides.length - perView);
                };

                const renderPager = () => {
                    if (!pager) return;

                    const maxIndex = getMaxIndex();
                    pager.innerHTML = '';
                    pagerButtons = [];

                    // Article slider: one dot per article (slide) with title tooltip
                    if (slider.classList.contains('gs-slider--articles')) {
                        pager.hidden = false;
                        for (let i = 0; i < slides.length; i++) {
                            const button = document.createElement('button');
                            button.type = 'button';
                            button.className = 'gs-slider-pager-dot';
                            const titleEl = slides[i].querySelector('.gs-article-title');
                            const titleText = titleEl ? titleEl.textContent.trim() : `Bài ${i + 1}`;
                            button.title = titleText;
                            button.setAttribute('aria-label', `Đi tới bài viết ${i + 1}: ${titleText}`);
                            button.addEventListener('click', (e) => {
                                    e.stopPropagation();
                                    index = Math.min(i, maxIndex);
                                    update();
                                    restartAutoPlay();
                                });
                            pager.appendChild(button);
                            pagerButtons.push(button);
                        }
                        return;
                    }

                    // Default pager behaviour: one dot per page/group
                    if (maxIndex < 0) {
                        pager.hidden = true;
                        return;
                    }

                    pager.hidden = false;
                    for (let i = 0; i <= maxIndex; i++) {
                        const button = document.createElement('button');
                        button.type = 'button';
                        button.className = 'gs-slider-pager-dot';
                        button.setAttribute('aria-label', isProductSlider ? `Đi tới trang sản phẩm ${i + 1}` : `Đi tới nhóm sản phẩm ${i + 1}`);
                        button.addEventListener('click', (e) => {
                            e.stopPropagation();
                            index = i;
                            update();
                            restartAutoPlay();
                        });
                        pager.appendChild(button);
                        pagerButtons.push(button);
                    }
                };

                const update = () => {
                    const width = slides[0].offsetWidth || 1;
                    const maxIndex = getMaxIndex();
                    index = Math.max(0, Math.min(index, maxIndex));
                    track.style.transform = `translateX(-${index * width}px)`;
                    
                    // Update dots
                    dots.forEach((dot, i) => {
                        dot.classList.toggle('is-active', i === index);
                    });

                    if (slider.classList.contains('gs-slider--articles')) {
                        if (pagerButtons.length !== slides.length) renderPager();
                    } else {
                        if (pagerButtons.length !== maxIndex + 1) {
                            renderPager();
                        }
                    }

                    pagerButtons.forEach((button, i) => {
                        button.classList.toggle('is-active', i === index);
                    });
                };

                const autoPlay = () => {
                    const maxIndex = getMaxIndex();
                    if (maxIndex < 1) return;
                    index = index >= maxIndex ? 0 : index + 1;
                    update();
                };

                const restartAutoPlay = () => {
                    // Autoplay for hero and product sliders; skip articles
                    if (slider.classList.contains('gs-slider--articles')) return;
                    if (timerId) clearInterval(timerId);
                    timerId = setInterval(autoPlay, interval);
                };

                if (prevButton) {
                    prevButton.addEventListener('click', () => {
                        index = Math.max(0, index - 1);
                        update();
                        restartAutoPlay();
                    });
                }

                if (nextButton) {
                    nextButton.addEventListener('click', () => {
                        const maxIndex = getMaxIndex();
                        index = Math.min(maxIndex, index + 1);
                        update();
                        restartAutoPlay();
                    });
                }

                // Dots click handler
                dots.forEach((dot, i) => {
                    dot.addEventListener('click', () => {
                        index = i;
                        update();
                        restartAutoPlay();
                    });
                });

                update();
                renderPager();
                window.addEventListener('resize', () => {
                    renderPager();
                    update();
                });

                // Autoplay for hero and product sliders (skip articles) with hover pause
                if (!slider.classList.contains('gs-slider--articles')) {
                    restartAutoPlay();
                    slider.addEventListener('mouseenter', () => {
                        if (timerId) clearInterval(timerId);
                    });
                    slider.addEventListener('mouseleave', restartAutoPlay);

                    // Ensure autoplay starts even when layout is still settling.
                    setTimeout(() => {
                        update();
                        restartAutoPlay();
                    }, 200);
                }
            });
        });
    </script>
</x-app-layout>
