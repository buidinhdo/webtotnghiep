@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center gap-2 px-2 pt-1 border-b-2 border-sky-500 text-sm font-semibold text-slate-900 transition'
            : 'inline-flex items-center gap-2 px-2 pt-1 border-b-2 border-transparent text-sm font-semibold text-slate-600 hover:text-slate-900 hover:border-slate-300 transition';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
