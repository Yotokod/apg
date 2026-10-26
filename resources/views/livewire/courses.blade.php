<div>
    <x-page-hero :title="__('nav.courses')" :subtitle="__('courses.subtitle')" icon="fa-graduation-cap" />
    <x-catalog.breadcrumbs :items="[__('nav.courses') => null]" />

    <div class="mx-auto max-w-7xl px-4 py-4">
        <div class="mb-4 flex flex-wrap items-center gap-2">
            <div class="relative min-w-0 flex-1 basis-56">
                <i class="fa-solid fa-magnifying-glass pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-stone-500" aria-hidden="true"></i>
                <input type="search" wire:model.live.debounce.300ms="search" class="input !pl-9" placeholder="{{ __('catalog.courses.search') }}" aria-label="{{ __('catalog.courses.search') }}">
            </div>
            <select wire:model.live="price" class="input !w-auto text-sm" aria-label="{{ __('catalog.courses.price') }}">
                <option value="">{{ __('catalog.courses.price_all') }}</option>
                <option value="free">{{ __('courses.free') }}</option>
                <option value="paid">{{ __('catalog.courses.paid') }}</option>
            </select>
            <select wire:model.live="minModules" class="input !w-auto text-sm" aria-label="{{ __('catalog.courses.modules') }}">
                @foreach (\App\Livewire\Courses::MODULE_STEPS as $n)
                    <option value="{{ $n }}">{{ $n ? __('catalog.courses.modules_min', ['n' => $n]) : __('catalog.courses.modules_any') }}</option>
                @endforeach
            </select>
            @if ($activeFilters)
                <button type="button" wire:click="clearFilters" class="btn-ghost"><i class="fa-solid fa-rotate-left" aria-hidden="true"></i> {{ __('catalog.shop.clear') }}</button>
            @endif
        </div>

        <p class="mb-3 text-sm text-stone-600 dark:text-stone-400" role="status">{{ trans_choice('catalog.courses.results', $courses->total(), ['count' => $courses->total()]) }}</p>

        <div wire:loading.grid wire:target="search,price,minModules,clearFilters,gotoPage,nextPage,previousPage" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3" aria-hidden="true">
            @for ($i = 0; $i < 6; $i++)<div class="skeleton h-72"></div>@endfor
        </div>
        <div wire:loading.remove wire:target="search,price,minModules,clearFilters,gotoPage,nextPage,previousPage" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($courses as $c)
                <a href="{{ route('courses.show', $c->slug) }}" wire:navigate wire:key="c{{ $c->id }}" class="card group flex flex-col overflow-hidden">
                    <div class="relative flex aspect-video items-center justify-center overflow-hidden bg-gradient-to-br from-brand-600 to-ink-900 text-5xl text-white">
                        @if ($c->image)
                            <img src="{{ \App\Support\Catalog::imageUrl($c->image) }}" alt="{{ $c->tr('title') }}" loading="lazy" class="h-full w-full object-cover">
                        @else
                            <i class="fa-solid fa-book-open-reader" aria-hidden="true"></i>
                        @endif
                        <div class="absolute left-2 top-2 flex gap-1">
                            @if ($c->price == 0)<span class="rounded-md bg-emerald-100 px-2 py-0.5 text-xs font-bold text-emerald-900">{{ __('courses.free') }}</span>@endif
                            @if (in_array($c->id, $enrolled))<span class="rounded-md bg-white px-2 py-0.5 text-xs font-bold text-brand-800">{{ __('catalog.courses.enrolled') }}</span>@endif
                        </div>
                    </div>
                    <div class="flex flex-1 flex-col gap-2 p-4">
                        <h3 class="line-clamp-2 min-h-[2.75rem] font-bold leading-snug group-hover:text-brand-700">{{ $c->tr('title') }}</h3>
                        <p class="line-clamp-2 text-sm text-stone-600 dark:text-stone-400">{{ $c->tr('description') }}</p>
                        <p class="flex items-center gap-1.5 text-xs font-semibold text-stone-700 dark:text-stone-300"><i class="fa-solid fa-circle-check text-emerald-700" aria-hidden="true"></i> {{ __('catalog.courses.no_prereq') }}</p>
                        <div class="mt-auto flex items-center justify-between gap-2 border-t border-stone-200 pt-3 text-sm dark:border-stone-800">
                            <span class="flex min-w-0 flex-wrap gap-x-3 text-stone-700 dark:text-stone-300">
                                <span class="whitespace-nowrap"><i class="fa-solid fa-layer-group" aria-hidden="true"></i> {{ $c->modules_count }} {{ __('catalog.courses.modules_label') }}</span>
                                <span class="whitespace-nowrap"><i class="fa-solid fa-list-check" aria-hidden="true"></i> {{ $c->lessons_count }} {{ __('courses.lessons') }}</span>
                            </span>
                            <strong class="shrink-0">{{ $c->price ? fcfa($c->price) : __('courses.free') }}</strong>
                        </div>
                    </div>
                </a>
            @empty
                <div class="card col-span-full flex flex-col items-center gap-2 p-10 text-center">
                    <i class="fa-solid fa-graduation-cap text-4xl text-stone-400" aria-hidden="true"></i>
                    <p class="font-semibold">{{ __('courses.empty') }}</p>
                    @if ($activeFilters)<button type="button" wire:click="clearFilters" class="btn-primary mt-2">{{ __('catalog.shop.clear') }}</button>@endif
                </div>
            @endforelse
        </div>

        @if ($courses->hasPages())<div class="mt-6">{{ $courses->links() }}</div>@endif
    </div>
</div>
