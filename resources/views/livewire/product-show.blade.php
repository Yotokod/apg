@php
    $out = $product->stock < 1;
    $low = ! $out && $product->stock <= \App\Support\Catalog::LOW_STOCK;
    $unit = trans()->has('catalog.unit.'.$product->unit) ? __('catalog.unit.'.$product->unit) : $product->unit;
    $livestock = \App\Support\Catalog::isLivestock($product->category);
    $tabs = array_keys(array_filter(['description' => true, 'delivery' => true, 'advice' => $livestock]));
@endphp
<div>
    <script type="application/ld+json">{!! $jsonLd !!}</script>
    <x-catalog.breadcrumbs :items="[__('nav.shop') => route('shop'), \App\Support\Catalog::categoryLabel($product->category) => route('shop', ['cat' => $product->category]), $product->tr('name') => null]" />

    <div class="mx-auto max-w-7xl space-y-8 px-4 py-4">
        <div class="grid gap-6 lg:grid-cols-2">
            {{-- Gallery with zoom (click or hover to zoom, move to pan) --}}
            <div x-data="{ zoom: false, x: 50, y: 50 }" class="card overflow-hidden {{ $product->image ? 'cursor-zoom-in' : '' }}">
                <div class="aspect-[4/3] overflow-hidden"
                     @if ($product->image)
                     @click="zoom = ! zoom"
                     @mousemove="const r = $el.getBoundingClientRect(); x = (($event.clientX - r.left) / r.width) * 100; y = (($event.clientY - r.top) / r.height) * 100"
                     @mouseleave="zoom = false"
                     @endif>
                    <x-catalog.product-image :product="$product" size="text-8xl" :eager="true"
                        class="h-full transition-transform duration-200 {{ $out ? 'grayscale' : '' }}"
                        x-bind:style="zoom ? `transform: scale(1.8); transform-origin: ${x}% ${y}%` : ''">
                        <x-catalog.badges :product="$product" :sold="$sold" class="absolute left-3 top-3" />
                    </x-catalog.product-image>
                </div>
            </div>

            {{-- Buy box --}}
            <div class="space-y-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-brand-700 dark:text-brand-500">{{ \App\Support\Catalog::categoryLabel($product->category) }}</p>
                    <h1 class="mt-1 text-2xl font-extrabold leading-tight sm:text-3xl">{{ $product->tr('name') }}</h1>
                </div>
                <div class="flex flex-wrap items-end gap-x-3 gap-y-1">
                    <p class="text-3xl font-extrabold">{{ fcfa($product->price) }}</p>
                    <p class="pb-1 text-sm text-stone-600 dark:text-stone-400">{{ __('catalog.product.per', ['unit' => $unit]) }}</p>
                </div>
                <p class="flex items-center gap-2 text-sm font-semibold {{ $out ? 'text-red-700 dark:text-red-400' : ($low ? 'text-amber-800 dark:text-amber-400' : 'text-emerald-800 dark:text-emerald-400') }}">
                    <i class="fa-solid {{ $out ? 'fa-circle-xmark' : 'fa-circle-check' }}" aria-hidden="true"></i>
                    {{ $out ? __('catalog.product.out') : ($low ? __('catalog.product.low', ['n' => $product->stock]) : __('catalog.product.in_stock')) }}
                </p>

                <div class="flex flex-wrap items-center gap-3">
                    <div class="inline-flex items-center overflow-hidden rounded-xl border border-stone-300 dark:border-stone-700" role="group" aria-label="{{ __('catalog.product.qty') }}">
                        <button type="button" wire:click="step(-1)" @disabled($out || $qty <= 1) class="flex size-11 items-center justify-center hover:bg-stone-100 disabled:opacity-40 dark:hover:bg-stone-800" aria-label="{{ __('catalog.cart.decrease') }}"><i class="fa-solid fa-minus" aria-hidden="true"></i></button>
                        <input type="number" min="1" max="99" wire:model.blur="qty" @disabled($out) class="h-11 w-14 border-x border-stone-300 bg-transparent text-center font-bold dark:border-stone-700" aria-label="{{ __('catalog.product.qty') }}">
                        <button type="button" wire:click="step(1)" @disabled($out || $qty >= 99) class="flex size-11 items-center justify-center hover:bg-stone-100 disabled:opacity-40 dark:hover:bg-stone-800" aria-label="{{ __('catalog.cart.increase') }}"><i class="fa-solid fa-plus" aria-hidden="true"></i></button>
                    </div>
                    <button type="button" wire:click="add" wire:loading.attr="disabled" wire:target="add" @disabled($out) class="btn-primary min-w-0 flex-1">
                        <i class="fa-solid fa-cart-plus" aria-hidden="true"></i> <span class="truncate">{{ __('shop.add') }} · {{ fcfa($product->price * max(1, $qty)) }}</span>
                    </button>
                </div>
                @if ($whatsapp)
                    <a href="{{ $whatsapp }}" target="_blank" rel="noopener noreferrer" class="btn w-full bg-emerald-700 text-white hover:bg-emerald-800">
                        <i class="fa-brands fa-whatsapp text-lg" aria-hidden="true"></i> {{ __('catalog.product.whatsapp') }}
                    </a>
                @endif

                <ul class="grid gap-2 text-sm sm:grid-cols-2">
                    <li class="card flex items-start gap-3 p-3"><i class="fa-solid fa-truck-fast mt-0.5 text-brand-600" aria-hidden="true"></i>
                        <span class="min-w-0"><strong class="block">{{ __('catalog.product.delivery_title') }}</strong><span class="text-stone-600 dark:text-stone-400">{{ __('catalog.product.delivery_short') }}</span></span></li>
                    <li class="card flex items-start gap-3 p-3"><i class="fa-solid fa-wallet mt-0.5 text-brand-600" aria-hidden="true"></i>
                        <span class="min-w-0"><strong class="block">{{ __('catalog.product.payment_title') }}</strong>
                            <span class="text-stone-600 dark:text-stone-400">{{ $payments->isNotEmpty() ? $payments->implode(', ') : __('catalog.product.payment_contact') }}</span></span></li>
                </ul>
            </div>
        </div>

        {{-- Tabs --}}
        <section x-data="{ tab: 'description' }" class="card p-4 sm:p-6">
            <div class="flex gap-1 overflow-x-auto border-b border-stone-200 dark:border-stone-800" role="tablist">
                @foreach ($tabs as $key)
                    <button type="button" role="tab" id="tab-{{ $key }}" aria-controls="panel-{{ $key }}" :aria-selected="tab === '{{ $key }}'" @click="tab = '{{ $key }}'"
                            :class="tab === '{{ $key }}' ? 'border-brand-600 text-brand-700 dark:text-brand-500' : 'border-transparent text-stone-600 dark:text-stone-400'"
                            class="-mb-px min-h-11 shrink-0 border-b-2 px-4 text-sm font-bold">{{ __('catalog.product.tab_'.$key) }}</button>
                @endforeach
            </div>
            <div class="pt-4 text-sm">
                <div id="panel-description" role="tabpanel" aria-labelledby="tab-description" x-show="tab === 'description'" class="prose max-w-none">
                    {!! nl2br(e($product->tr('description') ?: __('catalog.product.no_description'))) !!}
                </div>
                <div id="panel-delivery" role="tabpanel" aria-labelledby="tab-delivery" x-show="tab === 'delivery'" x-cloak class="space-y-3">
                    <ul class="space-y-2">
                        @foreach (['air' => 'fa-plane', 'taxi' => 'fa-taxi', 'transport' => 'fa-bus'] as $mode => $icon)
                            <li class="flex items-start gap-3"><i class="fa-solid {{ $icon }} mt-0.5 w-5 text-center text-brand-600" aria-hidden="true"></i><span>{{ __('catalog.product.delivery_'.$mode) }}</span></li>
                        @endforeach
                    </ul>
                    <p class="text-stone-600 dark:text-stone-400">{{ __('catalog.product.delivery_note') }}</p>
                </div>
                @if ($livestock)
                    <div id="panel-advice" role="tabpanel" aria-labelledby="tab-advice" x-show="tab === 'advice'" x-cloak class="space-y-3">
                        <ul class="list-disc space-y-1.5 pl-5">
                            @foreach (range(1, 4) as $i)<li>{{ __('catalog.product.advice_'.$i) }}</li>@endforeach
                        </ul>
                        <a href="{{ route('courses') }}" wire:navigate class="btn-ghost"><i class="fa-solid fa-graduation-cap" aria-hidden="true"></i> {{ __('catalog.product.advice_cta') }}</a>
                    </div>
                @endif
            </div>
        </section>

        @if ($related->isNotEmpty())
            <section aria-labelledby="related-title">
                <h2 id="related-title" class="mb-3 text-xl font-extrabold">{{ __('catalog.product.related') }}</h2>
                <div class="grid grid-cols-2 gap-3 md:grid-cols-4">
                    @foreach ($related as $r)<x-catalog.product-card :product="$r" :sold="$sold" wire:key="r{{ $r->id }}" />@endforeach
                </div>
            </section>
        @endif
    </div>
</div>
