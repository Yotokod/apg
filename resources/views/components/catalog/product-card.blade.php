@props(['product', 'sold' => [], 'list' => false])
@php($out = $product->stock < 1)
<article {{ $attributes->class(['card group overflow-hidden', $list ? 'flex flex-row' : 'flex flex-col']) }}>
    <a href="{{ route('product.show', $product->slug) }}" wire:navigate class="{{ $list ? 'w-36 shrink-0 sm:w-48' : '' }} block" tabindex="-1" aria-hidden="true">
        <x-catalog.product-image :product="$product" class="{{ $list ? 'h-full min-h-32' : 'aspect-[4/3]' }} {{ $out ? 'grayscale' : '' }}">
            <x-catalog.badges :product="$product" :sold="$sold" class="absolute left-2 top-2" />
        </x-catalog.product-image>
    </a>
    <div class="flex min-w-0 flex-1 flex-col gap-1 p-3">
        <span class="truncate text-xs font-semibold uppercase tracking-wide text-brand-700 dark:text-brand-500">{{ \App\Support\Catalog::categoryLabel($product->category) }}</span>
        <h3 class="line-clamp-2 min-h-[2.5rem] text-sm font-bold leading-snug">
            <a href="{{ route('product.show', $product->slug) }}" wire:navigate class="hover:text-brand-700">{{ $product->tr('name') }}</a>
        </h3>
        @if ($list)<p class="line-clamp-2 text-sm text-stone-600 dark:text-stone-400">{{ $product->tr('description') }}</p>@endif
        <div class="mt-auto flex items-center justify-between gap-2 pt-1">
            <div class="min-w-0">
                <p class="text-sm font-extrabold leading-tight sm:text-base">{{ fcfa($product->price) }}</p>
                <p class="truncate text-xs text-stone-600 dark:text-stone-400">{{ trans()->has('catalog.unit.'.$product->unit) ? __('catalog.unit.'.$product->unit) : $product->unit }}</p>
            </div>
            <button type="button" wire:click="add({{ $product->id }})" wire:loading.attr="disabled" wire:target="add({{ $product->id }})" @disabled($out)
                    class="btn-primary shrink-0 !px-3" aria-label="{{ __('shop.add') }}: {{ $product->tr('name') }}">
                <i class="fa-solid fa-cart-plus" aria-hidden="true"></i>            </button>
        </div>
    </div>
</article>
