<div x-data="{ drawer: false }" @keydown.escape.window="drawer = false">
    <x-page-hero :title="__('nav.shop')" :subtitle="__('shop.subtitle')" icon="fa-store" />
    <x-catalog.breadcrumbs :items="[__('nav.shop') => null]" />

    <div class="mx-auto max-w-7xl px-4 py-4">
        {{-- Toolbar --}}
        <div class="mb-3 flex flex-wrap items-center gap-2">
            <div class="relative min-w-0 flex-1 basis-56">
                <i class="fa-solid fa-magnifying-glass pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-stone-500" aria-hidden="true"></i>
                <input type="search" wire:model.live.debounce.300ms="search" class="input !pl-9" placeholder="{{ __('shop.search') }}" aria-label="{{ __('shop.search') }}">
            </div>
            <button type="button" @click="drawer = true" class="btn-ghost lg:hidden" aria-haspopup="dialog">
                <i class="fa-solid fa-sliders" aria-hidden="true"></i> {{ __('catalog.shop.filters') }}
                @if ($activeFilters)<span class="rounded-full bg-brand-600 px-1.5 text-xs text-white">{{ $activeFilters }}</span>@endif
            </button>
            <label class="flex items-center gap-2 text-sm font-medium">
                <span class="sr-only sm:not-sr-only">{{ __('catalog.shop.sort') }}</span>
                <select wire:model.live="sort" class="input !w-auto !py-1.5 text-sm" aria-label="{{ __('catalog.shop.sort') }}">
                    @foreach (\App\Livewire\Shop::SORTS as $s)<option value="{{ $s }}">{{ __('catalog.shop.sort_'.$s) }}</option>@endforeach
                </select>
            </label>
            <div class="inline-flex overflow-hidden rounded-xl border border-stone-300 dark:border-stone-700" role="group" aria-label="{{ __('catalog.shop.view') }}">
                @foreach (['grid' => 'fa-table-cells-large', 'list' => 'fa-list'] as $mode => $icon)
                    <button type="button" wire:click="$set('view','{{ $mode }}')" aria-pressed="{{ $view === $mode ? 'true' : 'false' }}" aria-label="{{ __('catalog.shop.view_'.$mode) }}"
                            class="flex size-11 items-center justify-center {{ $view === $mode ? 'bg-brand-600 text-white' : 'hover:bg-stone-100 dark:hover:bg-stone-800' }}"><i class="fa-solid {{ $icon }}" aria-hidden="true"></i></button>
                @endforeach
            </div>
        </div>

        {{-- Category chips --}}
        <div class="mb-3 flex gap-2 overflow-x-auto pb-1" role="group" aria-label="{{ __('catalog.shop.category') }}">
            <button type="button" wire:click="$set('category','')" aria-pressed="{{ $category === '' ? 'true' : 'false' }}" class="btn shrink-0 {{ $category === '' ? 'bg-brand-600 text-white' : 'btn-ghost' }}">{{ __('shop.all') }}</button>
            @foreach ($categories as $c)
                <button type="button" wire:click="$set('category', @js($c))" aria-pressed="{{ $category === $c ? 'true' : 'false' }}" class="btn shrink-0 {{ $category === $c ? 'bg-brand-600 text-white' : 'btn-ghost' }}">
                    <i class="fa-solid {{ category_icon($c) }}" aria-hidden="true"></i> {{ \App\Support\Catalog::categoryLabel($c) }}
                </button>
            @endforeach
        </div>

        <div class="grid gap-5 lg:grid-cols-[16rem_1fr]">
            {{-- Filters: sidebar on desktop, drawer on mobile --}}
            <div x-show="drawer" x-cloak class="fixed inset-0 z-40 bg-black/50 lg:hidden" @click="drawer = false" aria-hidden="true"></div>
            <aside class="fixed inset-y-0 left-0 z-50 w-80 max-w-[90vw] -translate-x-full overflow-y-auto bg-white p-4 shadow-xl transition-transform dark:bg-stone-900 lg:static lg:z-auto lg:w-auto lg:max-w-none lg:translate-x-0 lg:overflow-visible lg:bg-transparent lg:p-0 lg:shadow-none lg:dark:bg-transparent"
                   :class="drawer && '!translate-x-0'" aria-label="{{ __('catalog.shop.filters') }}">
                <div class="card space-y-4 p-4 lg:sticky lg:top-24">
                    <div class="flex items-center justify-between">
                        <h2 class="font-extrabold"><i class="fa-solid fa-filter text-brand-600" aria-hidden="true"></i> {{ __('catalog.shop.filters') }}</h2>
                        <button type="button" @click="drawer = false" class="flex size-11 items-center justify-center rounded-xl hover:bg-stone-100 dark:hover:bg-stone-800 lg:hidden" aria-label="{{ __('nav.close') }}"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>
                    </div>
                    <fieldset>
                        <legend class="mb-2 text-sm font-bold">{{ __('catalog.shop.price') }} ({{ site('currency', 'FCFA') }})</legend>
                        <div class="flex items-center gap-2">
                            <input type="number" min="0" step="1000" inputmode="numeric" wire:model.live.debounce.500ms="minPrice" class="input !px-3" placeholder="0" aria-label="{{ __('catalog.shop.price_min') }}">
                            <span aria-hidden="true">–</span>
                            <input type="number" min="0" step="1000" inputmode="numeric" wire:model.live.debounce.500ms="maxPrice" class="input !px-3" placeholder="{{ $priceMax }}" aria-label="{{ __('catalog.shop.price_max') }}">
                        </div>
                    </fieldset>
                    <label class="flex min-h-11 cursor-pointer items-center gap-3 text-sm font-semibold">
                        <input type="checkbox" wire:model.live="inStock" class="size-5 accent-brand-600"> {{ __('catalog.shop.in_stock') }}
                    </label>
                    <div class="flex gap-2">
                        <button type="button" wire:click="clearFilters" @disabled(! $activeFilters) class="btn-ghost flex-1"><i class="fa-solid fa-rotate-left" aria-hidden="true"></i> {{ __('catalog.shop.clear') }}</button>
                        <button type="button" @click="drawer = false" class="btn-primary flex-1 lg:hidden">{{ __('catalog.shop.show_results', ['n' => $products->total()]) }}</button>
                    </div>
                </div>
            </aside>

            <section aria-live="polite">
                <p class="mb-3 text-sm text-stone-600 dark:text-stone-400" role="status">{{ trans_choice('catalog.shop.results', $products->total(), ['count' => $products->total()]) }}</p>

                <div wire:loading.grid wire:target="search,category,minPrice,maxPrice,inStock,sort,clearFilters,gotoPage,nextPage,previousPage" class="grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-4" aria-hidden="true">
                    @for ($i = 0; $i < 8; $i++)<div class="skeleton h-64"></div>@endfor
                </div>
                <div wire:loading.remove wire:target="search,category,minPrice,maxPrice,inStock,sort,clearFilters,gotoPage,nextPage,previousPage"
                     class="grid gap-3 {{ $view === 'list' ? 'grid-cols-1' : 'grid-cols-2 md:grid-cols-3 xl:grid-cols-4' }}">
                    @forelse ($products as $p)
                        <x-catalog.product-card :product="$p" :sold="$sold" :list="$view === 'list'" wire:key="p{{ $p->id }}" />
                    @empty
                        <div class="card col-span-full flex flex-col items-center gap-2 p-10 text-center">
                            <i class="fa-solid fa-magnifying-glass-minus text-4xl text-stone-400" aria-hidden="true"></i>
                            <p class="font-semibold">{{ __('shop.empty') }}</p>
                            <p class="text-sm text-stone-600 dark:text-stone-400">{{ __('catalog.shop.empty_hint') }}</p>
                            <button type="button" wire:click="clearFilters" class="btn-primary mt-2">{{ __('catalog.shop.clear') }}</button>
                        </div>
                    @endforelse
                </div>

                @if ($products->hasPages())<div class="mt-6">{{ $products->links() }}</div>@endif
            </section>
        </div>
    </div>
</div>
