<div>
    <section class="relative mx-3 mt-3 overflow-hidden rounded-3xl bg-gradient-to-br from-ink-900 via-ink-800 to-brand-700 text-white">
        <i class="fa-solid fa-drumstick-bite pointer-events-none absolute -bottom-10 right-4 text-[14rem] opacity-10" aria-hidden="true"></i>
        <div class="relative mx-auto flex min-h-[22rem] max-w-7xl flex-col justify-center gap-4 px-6 py-10 sm:min-h-[24rem]">
            <span class="w-fit rounded-full bg-white/15 px-3 py-1 text-xs font-semibold backdrop-blur"><i class="fa-solid fa-location-dot"></i> {{ site('cities') }}</span>
            <h1 class="max-w-2xl text-3xl font-extrabold leading-tight sm:text-5xl">{{ site('tagline') }}</h1>
            <p class="max-w-xl text-white/85">{{ __('home.lead') }}</p>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('shop') }}" wire:navigate class="btn-primary"><i class="fa-solid fa-store"></i> {{ __('home.cta_shop') }}</a>
                <a href="{{ route('courses') }}" wire:navigate class="btn bg-white/15 text-white backdrop-blur hover:bg-white/25"><i class="fa-solid fa-graduation-cap"></i> {{ __('home.cta_courses') }}</a>
            </div>
        </div>
    </section>

    <div class="mx-auto -mt-8 grid max-w-5xl grid-cols-3 gap-3 px-4">
        @foreach ([['fa-egg', $stats[0], 'home.stat1'], ['fa-book-open-reader', $stats[1], 'home.stat2'], ['fa-circle-play', $stats[2], 'home.stat3']] as [$i, $n, $k])
            <div class="card relative z-10 p-4 text-center"><i class="fa-solid {{ $i }} text-brand-600"></i><p class="text-xl font-extrabold" data-count="{{ $n }}">0</p><p class="truncate text-xs text-stone-500">{{ __($k) }}</p></div>
        @endforeach
    </div>

    <div class="mx-auto max-w-7xl space-y-14 px-4 py-12">
        <section data-stagger class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ([['fa-truck-fast', 'home.f1'], ['fa-shield-heart', 'home.f2'], ['fa-graduation-cap', 'home.f3'], ['fa-earth-africa', 'home.f4']] as [$i, $k])
                <div class="card p-5"><i class="fa-solid {{ $i }} text-2xl text-brand-600"></i><h3 class="mt-3 font-bold">{{ __($k) }}</h3><p class="mt-1 text-sm text-stone-600 dark:text-stone-400">{{ __($k.'_d') }}</p></div>
            @endforeach
        </section>

        <section data-reveal>
            <div class="mb-4 flex items-end justify-between"><h2 class="text-2xl font-extrabold">{{ __('home.products') }}</h2><a href="{{ route('shop') }}" wire:navigate class="text-sm font-semibold text-brand-700">{{ __('home.see_all') }} →</a></div>
            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($products as $p)
                    <a href="{{ route('shop', ['cat' => $p->category]) }}" wire:navigate class="card group flex items-center gap-4 p-4">
                        <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-brand-100 text-2xl text-brand-600 transition-colors group-hover:bg-brand-600 group-hover:text-white dark:bg-stone-800"><i class="fa-solid {{ category_icon($p->category) }}"></i></span>
                        <span class="min-w-0"><span class="line-clamp-2 font-bold leading-snug">{{ $p->tr('name') }}</span><span class="mt-1 block font-extrabold text-brand-700 dark:text-brand-500">{{ fcfa($p->price) }}</span></span>
                    </a>
                @endforeach
            </div>
        </section>

        <section data-reveal class="card flex flex-col items-center gap-4 bg-gradient-to-r from-brand-50 to-white p-8 text-center sm:flex-row sm:text-left dark:from-stone-900 dark:to-stone-900">
            <i class="fa-solid fa-chart-line text-5xl text-accent-500"></i>
            <div class="flex-1"><h2 class="text-xl font-extrabold">{{ __('home.sim_title') }}</h2><p class="text-stone-600 dark:text-stone-400">{{ __('home.sim_desc') }}</p></div>
            <a href="{{ route('simulator') }}" wire:navigate class="btn-primary">{{ __('home.sim_cta') }}</a>
        </section>

        @if ($courses->isNotEmpty())
        <section data-reveal>
            <h2 class="mb-4 text-2xl font-extrabold">{{ __('home.courses') }}</h2>
            <div class="grid gap-5 sm:grid-cols-3">
                @foreach ($courses as $c)
                    <a href="{{ route('courses.show', $c->slug) }}" wire:navigate class="card p-5"><i class="fa-solid fa-book-open-reader text-brand-600"></i><p class="mt-2 truncate font-bold">{{ $c->tr('title') }}</p><p class="line-clamp-2 text-sm text-stone-600 dark:text-stone-400">{{ $c->tr('description') }}</p></a>
                @endforeach
            </div>
        </section>
        @endif

        @if ($testimonials->isNotEmpty())
        <section data-reveal>
            <h2 class="mb-4 text-2xl font-extrabold">{{ __('home.testimonials') }}</h2>
            <div class="grid gap-5 md:grid-cols-3">
                @foreach ($testimonials as $t)
                    <figure class="card flex flex-col gap-3 p-6">
                        <i class="fa-solid fa-quote-left text-2xl text-accent-500" aria-hidden="true"></i>
                        <blockquote class="line-clamp-5 text-sm leading-relaxed text-stone-700 dark:text-stone-300">{{ $t->text }}</blockquote>
                        <figcaption class="mt-auto flex items-center gap-3 pt-2">
                            <span class="flex h-10 w-10 items-center justify-center rounded-full bg-brand-600 font-bold text-white">{{ mb_substr($t->name, 0, 1) }}</span>
                            <span class="text-sm"><strong class="block">{{ $t->name }}</strong><span class="text-stone-500">{{ $t->country }}</span></span>
                        </figcaption>
                    </figure>
                @endforeach
            </div>
        </section>
        @endif

        @if ($plans->isNotEmpty())
        <section data-reveal>
            <h2 class="mb-4 text-2xl font-extrabold">{{ __('home.plans') }}</h2>
            <div class="grid gap-5 sm:grid-cols-3">
                @foreach ($plans as $pl)
                    <div class="card space-y-2 p-6"><h3 class="font-bold">{{ $pl->tr('name') }}</h3><p class="text-2xl font-extrabold">{{ fcfa($pl->price) }}<span class="text-sm font-normal text-stone-500">/{{ $pl->period }}</span></p>
                        <ul class="space-y-1 text-sm">@foreach ($pl->features ?? [] as $f)<li><i class="fa-solid fa-check text-brand-600"></i> {{ $f }}</li>@endforeach</ul></div>
                @endforeach
            </div>
        </section>
        @endif
    </div>
</div>
