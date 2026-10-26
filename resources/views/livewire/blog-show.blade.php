<div>
    <x-page-hero :title="$title" :subtitle="$post->category" icon="fa-newspaper" />
    <x-pages.breadcrumb :items="[[__('pages.blog.title'), route('blog')], [$title, null]]" />
    <script type="application/ld+json">{!! $jsonLd !!}</script>

    <article class="mx-auto max-w-3xl space-y-4 px-4 py-6">
        <header class="flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-stone-600 dark:text-stone-300">
            @if ($post->published_at) <span><i class="fa-regular fa-calendar mr-1" aria-hidden="true"></i><time datetime="{{ $post->published_at->toDateString() }}">{{ __('pages.blog.published_on', ['date' => $post->published_at->translatedFormat('d F Y')]) }}</time></span> @endif
            <span><i class="fa-regular fa-clock mr-1" aria-hidden="true"></i>{{ __('pages.blog.min_read', ['minutes' => $minutes]) }}</span>
        </header>

        @if ($cover)
            <img src="{{ $cover }}" alt="{{ __('pages.blog.cover_alt', ['title' => $title]) }}" loading="lazy" class="aspect-[16/9] w-full rounded-2xl object-cover">
        @endif

        <div class="prose max-w-none [&_a]:font-semibold [&_a]:text-brand-700 [&_a]:underline [&_blockquote]:border-l-4 [&_blockquote]:border-brand-600 [&_blockquote]:pl-4 [&_h2]:mt-6 [&_h2]:text-xl [&_h2]:font-extrabold [&_h3]:mt-4 [&_h3]:text-lg [&_h3]:font-bold [&_img]:h-auto [&_img]:max-w-full [&_img]:rounded-xl [&_ol]:list-decimal [&_ol]:pl-6 [&_p]:my-3 [&_table]:block [&_table]:overflow-x-auto [&_ul]:list-disc [&_ul]:pl-6">{!! $body !!}</div>

        <div class="flex flex-wrap items-center gap-2 border-t border-stone-200 pt-4 dark:border-stone-800" x-data="{ copied: false }">
            <span class="text-sm font-semibold">{{ __('pages.blog.share') }}</span>
            <a class="btn-ghost !px-3" target="_blank" rel="noopener noreferrer" href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($url) }}" aria-label="{{ __('pages.blog.share_facebook') }}"><i class="fa-brands fa-facebook-f" aria-hidden="true"></i></a>
            <a class="btn-ghost !px-3" target="_blank" rel="noopener noreferrer" href="https://twitter.com/intent/tweet?url={{ urlencode($url) }}&text={{ urlencode($title) }}" aria-label="{{ __('pages.blog.share_x') }}"><i class="fa-brands fa-x-twitter" aria-hidden="true"></i></a>
            <a class="btn-ghost !px-3" target="_blank" rel="noopener noreferrer" href="https://wa.me/?text={{ urlencode($title.' '.$url) }}" aria-label="{{ __('pages.blog.share_whatsapp') }}"><i class="fa-brands fa-whatsapp" aria-hidden="true"></i></a>
            <button type="button" class="btn-ghost" @click="navigator.clipboard?.writeText(@js($url)); copied = true; setTimeout(() => copied = false, 2000)">
                <i class="fa-solid" :class="copied ? 'fa-check' : 'fa-link'" aria-hidden="true"></i><span x-text="copied ? @js(__('pages.blog.link_copied')) : @js(__('pages.blog.copy_link'))">{{ __('pages.blog.copy_link') }}</span>
            </button>
        </div>
        <a href="{{ route('blog') }}" wire:navigate class="inline-flex min-h-11 items-center gap-1 text-sm font-semibold text-brand-700 hover:underline dark:text-brand-500"><i class="fa-solid fa-arrow-left text-xs" aria-hidden="true"></i> {{ __('pages.blog.back') }}</a>
    </article>

    @if ($related->count())
        <section class="mx-auto max-w-7xl px-4 pb-4" aria-labelledby="related-h">
            <x-pages.section-title :title="__('pages.blog.related')" id="related-h" />
            <div data-stagger class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($related as $r)
                    @php $rc = \App\Livewire\BlogShow::coverUrl($r->cover); @endphp
                    <article class="card flex gap-3 overflow-hidden p-3" wire:key="rel-{{ $r->id }}">
                        <span class="grid size-20 shrink-0 place-items-center overflow-hidden rounded-xl bg-brand-50 text-brand-700 dark:bg-stone-800" aria-hidden="true">
                            @if ($rc) <img src="{{ $rc }}" alt="" loading="lazy" class="h-full w-full object-cover"> @else <i class="fa-solid {{ category_icon($r->category) }} text-2xl"></i> @endif
                        </span>
                        <div class="min-w-0">
                            <h3 class="line-clamp-2 font-bold"><a href="{{ route('blog.show', $r->slug) }}" wire:navigate class="hover:underline">{{ $r->tr('title') }}</a></h3>
                            @if ($r->published_at) <time datetime="{{ $r->published_at->toDateString() }}" class="text-xs text-stone-600 dark:text-stone-300">{{ $r->published_at->translatedFormat('d M Y') }}</time> @endif
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    <x-pages.cta-band :title="__('pages.blog.cta_title')" :text="__('pages.blog.cta_text')" :primary="__('pages.common.courses')" :primary-url="route('courses')" />
</div>
