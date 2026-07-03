<x-app-layout>
    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="mb-8">
            <h1 class="text-3xl font-semibold text-slate-900">Tin tức &amp; Bài viết</h1>
            <p class="mt-2 text-sm text-slate-600">Cập nhật những thông tin mới nhất về game và phụ kiện</p>
        </div>

        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            @forelse($articles as $article)
            <a href="{{ route('articles.show', $article) }}" class="group block h-full w-full">
                <div class="gs-card gs-article-card overflow-hidden transition-transform hover:scale-105 h-full flex flex-col">
                    @if($article->image_path)
                        <div class="aspect-video overflow-hidden">
                            <img src="{{ asset($article->image_path) }}" alt="{{ $article->title }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform">
                        </div>
                    @else
                        <div class="aspect-video bg-gradient-to-br from-slate-300 to-slate-400 flex items-center justify-center">
                            <i class="fas fa-image text-white text-4xl opacity-50"></i>
                        </div>
                    @endif
                    <div class="p-4 flex flex-col flex-grow">
                        <h3 class="gs-article-title font-semibold text-slate-900 group-hover:text-blue-600 text-lg">{{ $article->title }}</h3>
                        <p class="gs-article-excerpt mt-2 text-sm text-slate-600 flex-grow">{{ $article->excerpt ?? Str::limit($article->content, 100) }}</p>
                        <div class="mt-4 flex items-center justify-between">
                            @if($article->author)
                                <small class="text-xs text-slate-500">{{ $article->author->name }}</small>
                            @endif
                            <small class="text-xs text-slate-400">{{ $article->published_at?->format('d/m/Y') }}</small>
                        </div>
                    </div>
                </div>
            </a>
            @empty
            <div class="col-span-full">
                <div class="gs-card p-8 text-center text-slate-600">
                    <i class="fas fa-inbox text-4xl mb-4 block opacity-50"></i>
                    <p>Chưa có bài viết nào được xuất bản.</p>
                </div>
            </div>
            @endforelse
        </div>

        {{-- Phân trang dạng số --}}
        @if($articles->lastPage() > 1)
        @php
            $currentPage = $articles->currentPage();
            $lastPage    = min($articles->lastPage(), 100);
            $window      = 2;
            $start       = max(1, $currentPage - $window);
            $end         = min($lastPage, $currentPage + $window);
        @endphp
        <nav class="mt-10 flex items-center justify-center gap-1 flex-wrap" aria-label="Phân trang tin tức">

            {{-- Nút Trước --}}
            @if($currentPage > 1)
                <a href="{{ $articles->url($currentPage - 1) }}"
                   class="inline-flex items-center justify-center w-9 h-9 rounded-md border border-slate-300 bg-white text-slate-600 hover:bg-blue-50 hover:border-blue-400 hover:text-blue-600 transition text-base font-medium">
                    &#8592;
                </a>
            @else
                <span class="inline-flex items-center justify-center w-9 h-9 rounded-md border border-slate-200 bg-slate-50 text-slate-300 text-base cursor-not-allowed font-medium">
                    &#8592;
                </span>
            @endif

            {{-- Trang đầu + dấu ... --}}
            @if($start > 1)
                <a href="{{ $articles->url(1) }}"
                   class="inline-flex items-center justify-center w-9 h-9 rounded-md border border-slate-300 bg-white text-slate-700 hover:bg-blue-50 hover:border-blue-400 hover:text-blue-600 transition text-sm">1</a>
                @if($start > 2)
                    <span class="inline-flex items-center justify-center w-9 h-9 text-slate-400 text-sm">…</span>
                @endif
            @endif

            {{-- Các trang trong cửa sổ --}}
            @for($p = $start; $p <= $end; $p++)
                @if($p === $currentPage)
                    <span class="inline-flex items-center justify-center w-9 h-9 rounded-md border border-blue-500 bg-blue-600 text-white font-semibold text-sm shadow-sm">{{ $p }}</span>
                @else
                    <a href="{{ $articles->url($p) }}"
                       class="inline-flex items-center justify-center w-9 h-9 rounded-md border border-slate-300 bg-white text-slate-700 hover:bg-blue-50 hover:border-blue-400 hover:text-blue-600 transition text-sm">{{ $p }}</a>
                @endif
            @endfor

            {{-- Dấu ... + trang cuối --}}
            @if($end < $lastPage)
                @if($end < $lastPage - 1)
                    <span class="inline-flex items-center justify-center w-9 h-9 text-slate-400 text-sm">…</span>
                @endif
                <a href="{{ $articles->url($lastPage) }}"
                   class="inline-flex items-center justify-center w-9 h-9 rounded-md border border-slate-300 bg-white text-slate-700 hover:bg-blue-50 hover:border-blue-400 hover:text-blue-600 transition text-sm">{{ $lastPage }}</a>
            @endif

            {{-- Nút Sau --}}
            @if($currentPage < $lastPage)
                <a href="{{ $articles->url($currentPage + 1) }}"
                   class="inline-flex items-center justify-center w-9 h-9 rounded-md border border-slate-300 bg-white text-slate-600 hover:bg-blue-50 hover:border-blue-400 hover:text-blue-600 transition text-base font-medium">
                    &#8594;
                </a>
            @else
                <span class="inline-flex items-center justify-center w-9 h-9 rounded-md border border-slate-200 bg-slate-50 text-slate-300 text-base cursor-not-allowed font-medium">
                    &#8594;
                </span>
            @endif

        </nav>

        {{-- Thông tin tổng --}}
        <p class="mt-4 text-center text-xs text-slate-400">
            Trang {{ $currentPage }} / {{ $lastPage }}
            &nbsp;·&nbsp;
            Tổng {{ $articles->total() }} bài viết
        </p>
        @endif

    </section>
</x-app-layout>
