<x-app-layout>
    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <a href="{{ route('articles.index') }}" class="inline-flex items-center text-slate-600 hover:text-slate-900 mb-6">
            <i class="fas fa-arrow-left mr-2"></i> Quay lại
        </a>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2">
                @if($article->image_path)
                    <img src="{{ asset($article->image_path) }}" class="w-full rounded-lg mb-6 shadow-lg" alt="{{ $article->title }}" style="max-height: 500px; object-fit: cover;">
                @endif

                <h1 class="text-4xl font-bold text-slate-900 mb-4">{{ $article->title }}</h1>

                <div class="flex items-center gap-4 text-slate-600 mb-6 pb-6 border-b">
                    @if($article->author)
                        <div class="flex items-center gap-2">
                            <i class="fas fa-user"></i>
                            <span>{{ $article->author->name }}</span>
                        </div>
                    @endif
                    <div class="flex items-center gap-2">
                        <i class="fas fa-calendar"></i>
                        <span>{{ $article->published_at?->format('d/m/Y') }}</span>
                    </div>
                </div>

                <div class="prose prose-lg max-w-none text-slate-700 leading-relaxed mb-8">
                    {!! nl2br(e($article->content)) !!}
                </div>

                <div class="bg-slate-50 rounded-lg p-6 mt-8">
                    <h3 class="font-semibold text-slate-900 mb-4">Chia sẻ bài viết</h3>
                    @php($shareUrl = urlencode(url()->current()))
                    <div class="flex gap-3">
                        <a href="https://www.facebook.com/sharer/sharer.php?u={{ $shareUrl }}" target="_blank" rel="noopener" aria-label="Chia sẻ Facebook" class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-blue-600 text-white hover:bg-blue-700 transition">
                            <svg viewBox="0 0 24 24" aria-hidden="true" class="w-5 h-5 fill-current">
                                <path d="M13.5 22v-8.4h2.8l.4-3.3h-3.2V8.2c0-1 .3-1.6 1.7-1.6h1.6V3.6c-.7-.1-1.8-.2-3-.2-3 0-5 1.8-5 5v1.9H7v3.3h2.8V22h3.7z"/>
                            </svg>
                        </a>
                        <a href="https://zalo.me/share?url={{ $shareUrl }}" target="_blank" rel="noopener" aria-label="Chia sẻ Zalo" class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-[#0068ff] text-white hover:opacity-90 transition">
                            <span class="text-[11px] font-bold leading-none tracking-tight">Zalo</span>
                        </a>
                        <a href="https://www.messenger.com/share?link={{ $shareUrl }}" target="_blank" rel="noopener" aria-label="Chia sẻ Messenger" class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-[#0084ff] text-white hover:opacity-90 transition">
                            <svg viewBox="0 0 24 24" aria-hidden="true" class="w-5 h-5 fill-current">
                                <path d="M12 2C6.5 2 2 6.1 2 11.2c0 2.9 1.5 5.4 3.8 7.1V22l3.5-1.9c.9.2 1.8.3 2.7.3 5.5 0 10-4.1 10-9.2S17.5 2 12 2zm1.1 12.4-2.7-2.9-5.2 2.9 5.7-6.1 2.8 2.9 5.1-2.9-5.7 6.1z"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-1">
                @if($relatedArticles->count() > 0)
                <div class="gs-card p-6 mb-6">
                    <h3 class="font-semibold text-slate-900 mb-4">Bài viết liên quan</h3>
                    <div class="space-y-4">
                        @foreach($relatedArticles as $related)
                        <a href="{{ route('articles.show', $related) }}" class="group block hover:opacity-70 transition">
                            @if($related->image_path)
                                <img src="{{ asset($related->image_path) }}" class="w-full rounded mb-2 object-cover" alt="{{ $related->title }}" style="height: 120px;">
                            @endif
                            <h4 class="font-medium text-slate-900 text-sm group-hover:text-blue-600">{{ $related->title }}</h4>
                            <p class="text-xs text-slate-500 mt-1">{{ $related->published_at?->format('d/m/Y') }}</p>
                        </a>
                        @endforeach
                    </div>
                </div>
                @endif

                <div class="gs-card p-6">
                    <h3 class="font-semibold text-slate-900 mb-4">Thông tin</h3>
                    <div class="space-y-3 text-sm text-slate-600">
                        <div>
                            <span class="font-medium text-slate-900">Tác giả:</span>
                            {{ $article->author->name ?? 'Tác giả' }}
                        </div>
                        <div>
                            <span class="font-medium text-slate-900">Ngày đăng:</span>
                            {{ $article->published_at?->format('d/m/Y') }}
                        </div>
                        <div>
                            <span class="font-medium text-slate-900">Cập nhật:</span>
                            {{ $article->updated_at?->format('d/m/Y') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-app-layout>
