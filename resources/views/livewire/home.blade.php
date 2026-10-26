@php
    use App\Support\SiteData;
    $wa = SiteData::whatsappUrl(__('wa.msg'));
    $steps = [['chick-growth', 'home.s1'], ['farm-house', 'home.s2'], ['delivery', 'home.s3'], ['feeding', 'home.s4']];
    $faqs = range(1, 5);
    $galleryUrl = SiteData::url('gallery');
    $blogUrl = SiteData::url('blog');
    $faqUrl = SiteData::url('faq');
    $visibleStats = collect($stats)->filter(fn ($s) => $s[1] > 0);
@endphp
<div>
    {{-- Hero --}}
    <section class="relative mx-3 mt-3 overflow-hidden rounded-3xl bg-gradient-to-br from-ink-900 via-ink-800 to-brand-700 text-white">
        <div class="relative mx-auto grid max-w-7xl items-center gap-4 px-5 py-6 sm:px-8 md:grid-cols-[1.2fr_1fr] lg:max-h-[400px]">
            <div class="flex min-w-0 flex-col gap-3">
                <span class="w-fit max-w-full truncate rounded-full bg-white/15 px-3 py-1 text-xs font-semibold backdrop-blur"><i class="fa-solid fa-location-dot" aria-hidden="true"></i> {{ site('cities') }}</span>
                <h1 class="text-2xl font-extrabold leading-tight sm:text-4xl">{{ site('tagline') }}</h1>
                <p class="max-w-xl text-sm text-white/85 sm:text-base">{{ __('home.lead') }}</p>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('shop') }}" wire:navigate class="btn-primary"><i class="fa-solid fa-store" aria-hidden="true"></i> {{ __('home.cta_shop') }}</a>
                    @if ($wa)<a href="{{ $wa }}" target="_blank" rel="noopener" class="btn bg-white/15 text-white backdrop-blur hover:bg-white/25"><i class="fa-brands fa-whatsapp" aria-hidden="true"></i> {{ __('home.cta_wa') }}</a>@endif
                </div>
                <ul class="flex flex-wrap gap-2 text-xs font-medium">
                    @foreach ([['fa-truck-fast', 'home.chip1'], ['fa-shield-heart', 'home.chip2'], ['fa-graduation-cap', 'home.chip3']] as [$i, $k])
                        <li class="inline-flex items-center gap-1.5 rounded-full bg-white/10 px-3 py-1"><i class="fa-solid {{ $i }} text-accent-400" aria-hidden="true"></i>{{ __($k) }}</li>
                    @endforeach
                </ul>
            </div>
            <div class="flex min-w-0 justify-center">
                <x-illustrations.hero-farm class="h-auto max-h-[240px] w-full max-w-sm md:max-h-[340px]" :label="__('home.hero_alt')" />
            </div>
        </div>
    </section>

    <div class="mx-auto max-w-7xl space-y-10 px-4 py-8">
        <x-site.trust-bar />

        @if (count($categories))
        <section data-reveal>
            <x-site.section-heading :title="__('home.cats')" :sub="__('home.cats_sub')" :href="route('shop')" :cta="__('home.see_all')" />
            <x-site.category-tiles :categories="$categories" />
        </section>
        @endif

        @if ($products->isNotEmpty())
        <section data-reveal>
            <x-site.section-heading :title="__('home.featured')" :sub="__('home.featured_sub')" :href="route('shop')" :cta="__('home.see_all')" />
            <div data-stagger class="grid grid-cols-2 gap-3 md:grid-cols-3 lg:grid-cols-4">
                @foreach ($products as $p)
                    <x-site.product-mini-card :product="$p" wire:key="p-{{ $p->id }}" />
                @endforeach
            </div>
        </section>
        @endif

        {{-- How it works --}}
        <section data-reveal>
            <x-site.section-heading :title="__('home.how')" :sub="__('home.how_sub')" />
            <ol data-stagger class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($steps as $n => [$ill, $key])
                    <li class="card flex flex-col gap-2 p-4">
                        <div class="flex items-center gap-2">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-brand-600 text-sm font-extrabold text-white">{{ $n + 1 }}</span>
                            <h3 class="truncate font-bold">{{ __($key) }}</h3>
                        </div>
                        <x-dynamic-component :component="'illustrations.'.$ill" class="mx-auto h-32 w-full max-w-[200px]" :label="__($key)" />
                        <p class="text-sm text-stone-600 dark:text-stone-400">{{ __($key.'_d') }}</p>
                    </li>
                @endforeach
            </ol>
        </section>

        @if ($courses->isNotEmpty())
        <section data-reveal>
            <x-site.section-heading :title="__('home.courses')" :sub="__('home.courses_sub')" :href="route('courses')" :cta="__('home.all_courses')" />
            <div data-stagger class="grid gap-4 md:grid-cols-3">
                @foreach ($courses as $c)
                    @php($img = SiteData::imageUrl($c->image))
                    <a href="{{ SiteData::url('courses.show', ['slug' => $c->slug]) ?? route('courses') }}" wire:navigate wire:key="c-{{ $c->id }}" class="card group flex flex-col overflow-hidden">
                        <span class="flex aspect-video items-center justify-center overflow-hidden bg-gradient-to-br from-brand-50 to-brand-100 text-4xl text-brand-600 dark:from-stone-800 dark:to-stone-700">
                            @if ($img)<img src="{{ $img }}" alt="{{ $c->tr('title') }}" loading="lazy" decoding="async" width="640" height="360" class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105">
                            @else<i class="fa-solid fa-book-open-reader" aria-hidden="true"></i>@endif
                        </span>
                        <span class="flex flex-1 flex-col gap-1 p-4">
                            <span class="truncate font-bold">{{ $c->tr('title') }}</span>
                            <span class="line-clamp-2 text-sm text-stone-600 dark:text-stone-400">{{ $c->tr('description') }}</span>
                            <span class="mt-auto flex items-center justify-between gap-2 pt-2 text-sm">
                                <span class="inline-flex items-center gap-1.5 text-stone-600 dark:text-stone-400"><i class="fa-solid fa-circle-play text-brand-600" aria-hidden="true"></i>{{ trans_choice('home.n_lessons', $c->lessons_count, ['count' => $c->lessons_count]) }}</span>
                                <strong>{{ $c->price > 0 ? fcfa($c->price) : __('home.free') }}</strong>
                            </span>
                        </span>
                    </a>
                @endforeach
            </div>
        </section>
        @endif

        {{-- Simulator --}}
        <section data-reveal class="card grid items-center gap-4 bg-gradient-to-r from-brand-50 to-white p-5 sm:grid-cols-[1fr_auto] md:grid-cols-[auto_1fr_auto] dark:from-stone-900 dark:to-stone-900">
            <x-illustrations.profit-chart class="mx-auto h-28 w-40" :label="__('home.sim_title')" />
            <div class="min-w-0"><h2 class="text-xl font-extrabold">{{ __('home.sim_title') }}</h2><p class="mt-1 text-sm text-stone-600 dark:text-stone-400">{{ __('home.sim_desc') }}</p></div>
            <a href="{{ route('simulator') }}" wire:navigate class="btn-primary justify-self-start sm:justify-self-end"><i class="fa-solid fa-chart-line" aria-hidden="true"></i> {{ __('home.sim_cta') }}</a>
        </section>

        {{-- Stats --}}
        @if ($visibleStats->isNotEmpty())
        <section data-reveal class="rounded-3xl bg-ink-900 p-5 text-white">
            <div class="flex flex-wrap justify-center gap-x-4 gap-y-4 text-center">
                @foreach ($visibleStats as [$i, $n, $k])
                    <div class="min-w-0 basis-[40%] sm:basis-[28%] lg:basis-[16%]"><i class="fa-solid {{ $i }} text-accent-400" aria-hidden="true"></i><p class="text-2xl font-extrabold" data-count="{{ $n }}">{{ $n }}</p><p class="truncate text-xs text-white/75">{{ __($k) }}</p></div>
                @endforeach
            </div>
        </section>
        @endif

        {{-- Gallery --}}
        @if ($gallery->isNotEmpty())
        <section data-reveal>
            <x-site.section-heading :title="__('home.gallery')" :sub="__('home.gallery_sub')" :href="$galleryUrl" :cta="$galleryUrl ? __('home.gallery_all') : null" />
            <div data-lb-group class="columns-2 gap-3 md:columns-3 lg:columns-4">
                @foreach ($gallery as $i => $g)
                    <x-site.gallery-tile :item="$g" :i="$i" wire:key="g-{{ $g->id }}" />
                @endforeach
            </div>
            @if ($galleryUrl)
                <div class="mt-2 text-center"><a href="{{ $galleryUrl }}" wire:navigate class="btn-ghost"><i class="fa-solid fa-images" aria-hidden="true"></i> {{ __('home.gallery_all') }}</a></div>
            @endif
            <x-site.lightbox />
        </section>
        @endif

        {{-- Blog --}}
        @if ($posts->isNotEmpty())
        <section data-reveal>
            <x-site.section-heading :title="__('home.blog')" :sub="__('home.blog_sub')" :href="$blogUrl" :cta="$blogUrl ? __('home.blog_all') : null" />
            <div data-stagger class="grid gap-4 md:grid-cols-3">
                @foreach ($posts as $post)
                    @php($cover = SiteData::imageUrl($post->cover))
                    <a href="{{ SiteData::url('blog.show', ['slug' => $post->slug]) ?? $blogUrl ?? '#' }}" wire:navigate wire:key="post-{{ $post->id }}" class="card group flex flex-col overflow-hidden">
                        <span class="flex aspect-video items-center justify-center overflow-hidden bg-gradient-to-br from-brand-50 to-brand-100 text-4xl text-brand-600 dark:from-stone-800 dark:to-stone-700">
                            @if ($cover)<img src="{{ $cover }}" alt="{{ $post->tr('title') }}" loading="lazy" decoding="async" width="640" height="360" class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105">
                            @else<i class="fa-solid fa-newspaper" aria-hidden="true"></i>@endif
                        </span>
                        <span class="flex flex-1 flex-col gap-1 p-4">
                            @if ($post->published_at)<time datetime="{{ $post->published_at->toDateString() }}" class="text-xs text-stone-600 dark:text-stone-400">{{ $post->published_at->translatedFormat('d M Y') }}</time>@endif
                            <span class="line-clamp-2 font-bold leading-snug">{{ $post->tr('title') }}</span>
                            <span class="line-clamp-2 text-sm text-stone-600 dark:text-stone-400">{{ $post->tr('excerpt') }}</span>
                            <span class="mt-auto pt-2 text-sm font-semibold text-brand-700 dark:text-brand-500">{{ __('home.read') }} <i class="fa-solid fa-arrow-right text-xs" aria-hidden="true"></i></span>
                        </span>
                    </a>
                @endforeach
            </div>
        </section>
        @endif

        {{-- Testimonials --}}
        @if ($testimonials->isNotEmpty())
        <section data-reveal>
            <x-site.section-heading :title="__('home.testimonials')" :sub="__('home.testi_sub')" />
            <x-site.testimonial-slider :items="$testimonials" />
        </section>
        @endif

        @if ($plans->isNotEmpty())
        <section data-reveal>
            <x-site.section-heading :title="__('home.plans')" />
            <div class="grid gap-4 sm:grid-cols-3">
                @foreach ($plans as $pl)
                    <div class="card space-y-2 p-6"><h3 class="font-bold">{{ $pl->tr('name') }}</h3><p class="text-2xl font-extrabold">{{ fcfa($pl->price) }}<span class="text-sm font-normal text-stone-600 dark:text-stone-400">/{{ $pl->period }}</span></p>
                        <ul class="space-y-1 text-sm">@foreach ($pl->features ?? [] as $f)<li><i class="fa-solid fa-check text-brand-600" aria-hidden="true"></i> {{ $f }}</li>@endforeach</ul></div>
                @endforeach
            </div>
        </section>
        @endif

        {{-- FAQ --}}
        <section data-reveal x-data="{ open: 1 }">
            <x-site.section-heading :title="__('home.faq')" :sub="__('home.faq_sub')" :href="$faqUrl" :cta="$faqUrl ? __('home.faq_more') : null" />
            <div class="space-y-2">
                @foreach ($faqs as $n)
                    <div class="card">
                        <h3>
                            <button type="button" @click="open = open === {{ $n }} ? 0 : {{ $n }}" :aria-expanded="open === {{ $n }}" aria-controls="faq-{{ $n }}"
                                    class="flex min-h-12 w-full items-center justify-between gap-3 px-4 py-3 text-left text-sm font-bold">
                                <span class="min-w-0">{{ __('home.q'.$n) }}</span>
                                <i class="fa-solid fa-chevron-down shrink-0 text-xs transition-transform" :class="open === {{ $n }} && 'rotate-180'" aria-hidden="true"></i>
                            </button>
                        </h3>
                        <p id="faq-{{ $n }}" x-show="open === {{ $n }}" x-collapse x-cloak class="px-4 pb-4 text-sm text-stone-600 dark:text-stone-400">{{ __('home.a'.$n) }}</p>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- Final CTA --}}
        <section data-reveal class="overflow-hidden rounded-3xl bg-gradient-to-r from-brand-600 to-brand-700 p-6 text-white sm:p-8">
            <div class="flex flex-col items-start gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0"><h2 class="text-xl font-extrabold sm:text-2xl">{{ __('home.final_title') }}</h2><p class="mt-1 text-sm text-white/90">{{ __('home.final_sub') }}</p></div>
                <div class="flex flex-wrap gap-3">
                    @if ($wa)<a href="{{ $wa }}" target="_blank" rel="noopener" class="btn bg-white text-brand-700 hover:bg-brand-50"><i class="fa-brands fa-whatsapp" aria-hidden="true"></i> {{ __('home.cta_wa') }}</a>@endif
                    <a href="{{ route('contact') }}" wire:navigate class="btn bg-ink-900 text-white hover:bg-ink-800"><i class="fa-solid fa-envelope" aria-hidden="true"></i> {{ __('home.final_contact') }}</a>
                </div>
            </div>
        </section>
    </div>
</div>
