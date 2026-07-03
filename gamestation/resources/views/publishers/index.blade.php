@extends('layouts.app')

@section('title', 'Nhà phát hành')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-10">
    <h1 class="text-2xl font-bold mb-6">Nhà phát hành</h1>

    @if($publishers->count())
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @foreach($publishers as $pub)
                <a href="{{ route('publishers.show', $pub) }}" class="block p-4 bg-white rounded shadow hover:shadow-lg">
                    <div class="text-lg font-semibold">{{ $pub->name }}</div>
                </a>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $publishers->links() }}
        </div>
    @else
        <p>Chưa có nhà phát hành.</p>
    @endif
</div>
@endsection
