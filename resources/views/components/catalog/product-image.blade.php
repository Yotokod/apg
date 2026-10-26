@props(['product', 'size' => 'text-5xl', 'eager' => false])
@php($src = \App\Support\Catalog::imageUrl($product->image))
<div {{ $attributes->class('relative flex items-center justify-center overflow-hidden bg-gradient-to-br from-brand-50 to-brand-100 text-brand-600 dark:from-stone-800 dark:to-stone-700') }}>
    @if ($src)
        <img src="{{ $src }}" alt="{{ $product->tr('name') }}" @unless ($eager) loading="lazy" @endunless decoding="async" class="h-full w-full object-cover">
    @else
        <i class="fa-solid {{ category_icon($product->category) }} {{ $size }}" aria-hidden="true"></i>
    @endif
    {{ $slot }}
</div>
