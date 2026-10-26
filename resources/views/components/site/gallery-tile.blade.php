@props(['item', 'i' => 0])
@php
    $ratios = ['aspect-[4/5]', 'aspect-square', 'aspect-[4/3]', 'aspect-[3/4]', 'aspect-[5/4]', 'aspect-[3/4]'];
    $src = \App\Support\SiteData::imageUrl($item->image);
    $title = $item->tr('title');
    $alt = $item->tr('alt') ?: $title ?: __('gallery.cat.'.$item->category);
@endphp
<button type="button" data-lb-src="{{ $src }}" data-lb-alt="{{ $alt }}" data-lb-title="{{ $title ?: $alt }}"
        @click="$dispatch('lightbox-open', { el: $el })" aria-label="{{ __('gallery.view') }}: {{ $alt }}"
        {{ $attributes->class(['group relative mb-3 block w-full break-inside-avoid overflow-hidden rounded-2xl bg-stone-200 dark:bg-stone-800', $ratios[$i % count($ratios)]]) }}>
    <img src="{{ $src }}" alt="{{ $alt }}" loading="lazy" decoding="async" width="600" height="750" class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105">
    <span class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/70 to-transparent p-3 pt-8 text-left text-xs font-semibold text-white opacity-0 transition-opacity group-hover:opacity-100 group-focus-visible:opacity-100">
        <span class="block truncate">{{ $title ?: $alt }}</span>
    </span>
</button>
