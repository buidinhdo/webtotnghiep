@extends('layouts.app')

@section('title', $publisher->name)

@section('content')
<div class="max-w-7xl mx-auto px-4 py-10">
    <div class="mb-6">
        <a href="{{ route('publishers.index') }}" class="text-sm text-slate-600 hover:underline">&larr; Back to publishers</a>
    </div>

    <h1 class="text-2xl font-bold mb-2">{{ $publisher->name }}</h1>
    @if($publisher->slug)
        <p class="text-sm text-slate-500 mb-6">{{ $publisher->slug }}</p>
    @endif

    @if($products->count())
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            @foreach($products as $product)
                @include('components.product-card', ['product' => $product])
            @endforeach
        </div>

        <div class="mt-6">
            {{ $products->links() }}
        </div>
    @else
        <p>Chưa có sản phẩm cho nhà phát hành này.</p>
    @endif
</div>
@endsection
