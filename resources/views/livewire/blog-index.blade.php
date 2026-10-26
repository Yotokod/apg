<div>
    <x-page-hero :title="__('pages.blog.title')" :subtitle="__('pages.blog.subtitle')" icon="fa-newspaper" />
    <x-pages.breadcrumb :items="[[__('pages.blog.title'), null]]" />

    <div class="mx-auto max-w-7xl space-y-4 px-4 py-6">
        <div class="flex flex-col gap-3 sm:flex-row">
            <div class="relative flex-1">
                <label for="blog-search" class="sr-only">{{ __('pages.blog.search') }}</label>
                <i class="fa-solid fa-magnifying-glass pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-stone-500" aria-hidden="true"></i>
                <input id="blog-search" type="search" wire:model.live.debounce.300ms="search" placeholder="{{ __('pages.blog.search') }}" class="input !pl-11">
            </div>
            <div class="sm:w-64">
                <label for="blog-cat" class="sr-only">{{ __('pages.blog.category') }}</label>
                <select id="blog-cat" wire:model.live="category" class="input">
                    <option value="">{{ __('pages.blog.all') }}</option>
                    @foreach ($categories as $c) <option value="{{ $c }}">{{ $c }}</option> @endforeach
                </select>
            </div>
        </div>

        <div wire:loading.delay wire:target="search,category,gotoPage,nextPage,previousPage" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3" aria-hidden="true">
            @for ($i = 0; $i < 3; $i++) <div class="skeleton h-64"></div> @endfor
        </div>

        <div wire:loading.remove wire:target="search,category,gotoPage,nextPage,previousPage">
            @if ($posts->count())
                <div data-stagger class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($posts as $p)
                        @php $cover = \App\Livewire\BlogShow::coverUrl($p->cover); @endphp
                        <article class="card flex flex-col overflow-hidden" wire:key="post-{{ $p->id }}">
                            <a href="{{ route('blog.show', $p->slug) }}" wire:navigate class="block aspect-[16/9] overflow-hidden bg-brand-50 dark:bg-stone-800" tabindex="-1" aria-hidden="true">
                                @if ($cover) <img src="{{ $cover }}" alt="" loading="lazy" class="h-full w-full object-cover">
                                @else <span class="grid h-full place-items-center text-4xl text-brand-700"><i class="fa-solid {{ category_icon($p->category) }}"></i></span> @endif
                            </a>
                            <div class="flex flex-1 flex-col gap-2 p-4">
                                <div class="flex items-center justify-between gap-2 text-xs text-stone-600 dark:text-stone-300">
                                    @if ($p->category) <span class="truncate rounded-full bg-brand-50 px-2 py-1 font-semibold text-brand-800 dark:bg-stone-800 dark:text-brand-500">{{ $p->category }}</span> @endif
                                    @if ($p->published_at) <time datetime="{{ $p->published_at->toDateString() }}" class="shrink-0">{{ $p->published_at->translatedFormat('d M Y') }}</time> @endif
                                </div>
                                <h2 class="line-clamp-2 text-base font-bold"><a href="{{ route('blog.show', $p->slug) }}" wire:navigate class="hover:underline">{{ $p->tr('title') }}</a></h2>
                                <p class="line-clamp-3 text-sm text-stone-600 dark:text-stone-300">{{ $p->tr('excerpt') }}</p>
                                <a href="{{ route('blog.show', $p->slug) }}" wire:navigate class="mt-auto inline-flex min-h-11 items-center gap-1 text-sm font-semibold text-brand-700 hover:underline dark:text-brand-500">{{ __('pages.common.read_more') }} <i class="fa-solid fa-arrow-right text-xs" aria-hidden="true"></i></a>
                            </div>
                        </article>
                    @endforeach
                </div>
                <div class="mt-6">{{ $posts->links() }}</div>
            @else
                <p class="card p-8 text-center text-sm text-stone-600 dark:text-stone-300"><i class="fa-regular fa-newspaper mr-1" aria-hidden="true"></i>{{ __('pages.blog.empty') }}</p>
            @endif
        </div>
    </div>
</div>
