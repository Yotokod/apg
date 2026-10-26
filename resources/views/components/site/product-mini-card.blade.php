@props(['product'])
@php($img = \App\Support\SiteData::imageUrl($product->image))
@php($url = \App\Support\SiteData::url('product.show', ['slug' => $product->slug]) ?? route('shop', ['cat' => $product->category]))
<a href="{{ $url }}" wire:navigate {{ $attributes->class('card group flex flex-col overflow-hidden') }}>
    <span class="relative flex aspect-[4/3] items-center justify-center overflow-hidden bg-gradient-to-br from-brand-50 to-brand-100 text-4xl text-brand-600 dark:from-stone-800 dark:to-stone-700">
        @if ($img)<img src="{{ $img }}" alt="{{ $product->tr('name') }}" loading="lazy" decoding="async" width="400" height="300" class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105">
        @else<i class="fa-solid {{ category_icon($product->category) }}" aria-hidden="true"></i>@endif
    </span>
    <span class="flex flex-1 flex-col gap-1 p-3">
        <span class="truncate text-xs font-semibold uppercase tracking-wide text-brand-700 dark:text-brand-500">{{ \App\Support\Catalog::categoryLabel($product->category) }}</span>
        <span class="line-clamp-2 min-h-[2.5rem] text-sm font-bold leading-snug">{{ $product->tr('name') }}</span>
        <span class="mt-auto truncate pt-1 text-base font-extrabold">{{ fcfa($product->price) }}</span>
    </span>
</a>
