<div>
    <section class="hero-sm mx-3 mt-3 rounded-3xl">
        <i class="fa-solid fa-images pointer-events-none absolute -right-4 -top-4 text-[9rem] opacity-10" aria-hidden="true"></i>
        <div class="relative mx-auto w-full max-w-7xl px-5 py-3">
            <nav aria-label="{{ __('site.breadcrumb') }}" class="truncate text-xs text-white/80">
                <a href="{{ route('home') }}" wire:navigate class="hover:underline">{{ __('nav.home') }}</a> <i class="fa-solid fa-chevron-right mx-1 text-[9px]" aria-hidden="true"></i> <span aria-current="page">{{ __('gallery.title') }}</span>
            </nav>
            <h1 class="truncate text-xl font-extrabold sm:text-2xl">{{ __('gallery.title') }}</h1>
            <p class="truncate text-sm text-white/80">{{ __('gallery.subtitle') }}</p>
        </div>
    </section>

    <div class="mx-auto max-w-7xl px-4 py-6">
        <div class="-mx-4 mb-5 flex gap-2 overflow-x-auto px-4 pb-1 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden" role="group" aria-label="{{ __('gallery.filter') }}">
            @php($tabs = collect([['all', __('gallery.all'), $allCount]])->concat(collect($counts)->map(fn ($n, $k) => [$k, trans()->has('gallery.cat.'.$k) ? __('gallery.cat.'.$k) : ucfirst($k), $n])->values()))
            @foreach ($tabs as [$key, $label, $n])
                <button type="button" wire:click="setCat('{{ $key }}')" wire:key="tab-{{ $key }}" aria-pressed="{{ $activeCat === $key ? 'true' : 'false' }}"
                        class="inline-flex min-h-11 shrink-0 items-center gap-2 rounded-full border px-4 text-sm font-semibold transition-colors {{ $activeCat === $key ? 'border-brand-600 bg-brand-600 text-white' : 'border-stone-300 hover:bg-stone-100 dark:border-stone-700 dark:hover:bg-stone-800' }}">
                    {{ $label }}<span class="rounded-full px-1.5 text-xs {{ $activeCat === $key ? 'bg-white/25' : 'bg-stone-100 dark:bg-stone-800' }}">{{ $n }}</span>
                </button>
            @endforeach
        </div>

        <div wire:loading.delay wire:target="setCat" class="columns-2 gap-3 md:columns-3 lg:columns-4" aria-hidden="true">
            @foreach (['aspect-[4/5]', 'aspect-square', 'aspect-[4/3]', 'aspect-[3/4]', 'aspect-square', 'aspect-[4/5]', 'aspect-[4/3]', 'aspect-[3/4]'] as $r)
                <div class="skeleton mb-3 w-full break-inside-avoid {{ $r }}"></div>
            @endforeach
        </div>

        @if ($items->isEmpty())
            <div wire:loading.remove wire:target="setCat" class="card flex flex-col items-center gap-2 p-10 text-center">
                <i class="fa-solid fa-images text-4xl text-brand-600" aria-hidden="true"></i>
                <p class="font-bold">{{ __('gallery.empty') }}</p>
                <p class="text-sm text-stone-600 dark:text-stone-400">{{ __('gallery.empty_d') }}</p>
            </div>
        @else
            <div data-lb-group wire:loading.remove wire:target="setCat" class="columns-2 gap-3 md:columns-3 lg:columns-4" aria-label="{{ __('gallery.grid') }}">
                @foreach ($items as $i => $item)
                    <x-site.gallery-tile :item="$item" :i="$i" wire:key="item-{{ $item->id }}" />
                @endforeach
            </div>
            <div class="mt-4 flex flex-col items-center gap-2">
                <p class="text-xs text-stone-600 dark:text-stone-400">{{ __('gallery.shown', ['shown' => $items->count(), 'total' => $total]) }}</p>
                @if ($total > $items->count())
                    <button type="button" wire:click="loadMore" wire:loading.attr="disabled" wire:target="loadMore" class="btn-primary">
                        <i class="fa-solid fa-spinner fa-spin" wire:loading wire:target="loadMore" aria-hidden="true"></i>
                        <i class="fa-solid fa-plus" wire:loading.remove wire:target="loadMore" aria-hidden="true"></i>{{ __('gallery.more') }}
                    </button>
                @endif
            </div>
        @endif
        <x-site.lightbox />
    </div>
</div>
