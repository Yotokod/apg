@props(['product', 'sold' => []])
@php($badges = \App\Support\Catalog::badges($product, $sold))
@if ($badges)
    <div {{ $attributes->class('flex flex-wrap gap-1') }}>
        @foreach ($badges as $b)
            <span class="rounded-md px-2 py-0.5 text-xs font-bold {{ $b['tone'] }}">{{ __('catalog.badge.'.$b['key']) }}</span>
        @endforeach
    </div>
@endif
