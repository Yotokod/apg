<div>
    <x-page-hero :title="__('pages.about.title')" :subtitle="__('pages.about.subtitle')" icon="fa-feather-pointed" />
    <x-pages.breadcrumb :items="[[__('pages.about.title'), null]]" />

    <div class="mx-auto max-w-7xl space-y-10 px-4 py-6">
        {{-- Story --}}
        <section class="grid items-center gap-6 md:grid-cols-2" data-reveal aria-labelledby="story-h">
            <div class="space-y-3 text-sm leading-relaxed text-stone-600 dark:text-stone-300 sm:text-base">
                <h2 id="story-h" class="text-xl font-extrabold text-ink-900 dark:text-white sm:text-2xl">{{ __('pages.about.story_title') }}</h2>
                @foreach (['story_1', 'story_2', 'story_3'] as $k)
                    <p>{{ __('pages.about.'.$k, ['name' => site('name')]) }}</p>
                @endforeach
            </div>
            @php($photos = \App\Models\GalleryItem::where('category', 'poulets')->where('position', '<', 0)->whereIn('image', ['gallery/apg-reproducteur-cuivre-1.jpg', 'gallery/apg-reproducteur-creme-1.jpg', 'gallery/apg-reproducteur-gris-1.jpg'])->get())
            @if ($photos->count())
                <div class="grid grid-cols-3 gap-2" data-stagger>
                    @foreach ($photos as $ph)
                        <img src="{{ \App\Support\SiteData::imageUrl($ph->image) }}" alt="{{ $ph->tr('alt') }}" loading="lazy" width="400" height="533" class="aspect-[3/4] w-full rounded-2xl object-cover shadow-sm {{ $loop->index === 1 ? 'mt-6' : '' }}">
                    @endforeach
                </div>
            @else
                <x-pages.illu name="farm-house" icon="fa-house-chimney" class="mx-auto max-w-md" />
            @endif
        </section>

        {{-- Real counts only --}}
        @if (count($stats))
            <section data-stagger class="grid grid-cols-2 gap-3 lg:grid-cols-4" aria-label="{{ __('pages.about.stats_products') }}">
                @foreach ($stats as [$icon, $value, $label])
                    <x-pages.stat-counter :icon="$icon" :value="$value" :label="__($label)" />
                @endforeach
            </section>
        @endif

        {{-- Mission --}}
        <section data-reveal class="card flex flex-col gap-3 bg-brand-50 p-5 dark:bg-stone-900 sm:flex-row sm:items-center">
            <span class="grid size-12 shrink-0 place-items-center rounded-xl bg-brand-600 text-white"><i class="fa-solid fa-bullseye" aria-hidden="true"></i></span>
            <div>
                <h2 class="text-lg font-extrabold">{{ __('pages.about.mission_title') }}</h2>
                <p class="text-sm text-stone-700 dark:text-stone-300">{{ __('pages.about.mission_text') }}</p>
            </div>
        </section>

        {{-- Values --}}
        <section aria-labelledby="values-h">
            <x-pages.section-title :title="__('pages.about.values_title')" id="values-h" />
            <div data-stagger class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                @foreach (__('pages.about.values') as $v)
                    <div class="card p-4">
                        <span class="grid size-11 place-items-center rounded-xl bg-brand-50 text-brand-700 dark:bg-stone-800 dark:text-brand-500"><i class="fa-solid {{ $v['icon'] }}" aria-hidden="true"></i></span>
                        <h3 class="mt-2 font-bold">{{ $v['title'] }}</h3>
                        <p class="text-sm text-stone-600 dark:text-stone-300">{{ $v['text'] }}</p>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- Know-how --}}
        <section aria-labelledby="know-h">
            <x-pages.section-title :title="__('pages.about.know_title')" id="know-h" />
            <div data-stagger class="grid gap-3 md:grid-cols-3">
                @foreach (__('pages.about.know') as $k)
                    <article class="card overflow-hidden">
                        <x-pages.illu :name="$k['illu']" :icon="$k['icon']" class="rounded-none" />
                        <div class="p-4">
                            <h3 class="font-bold">{{ $k['title'] }}</h3>
                            <p class="text-sm text-stone-600 dark:text-stone-300">{{ $k['text'] }}</p>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>

        {{-- Team: generic roles only --}}
        <section aria-labelledby="team-h">
            <x-pages.section-title :title="__('pages.about.team_title')" id="team-h" />
            <div data-stagger class="grid grid-cols-2 gap-3 lg:grid-cols-4">
                @foreach (__('pages.about.team') as $t)
                    <x-pages.team-card :icon="$t['icon']" :role="$t['role']" :text="$t['text']" />
                @endforeach
            </div>
        </section>

        {{-- Presence + delivery steps --}}
        <section class="grid gap-4 lg:grid-cols-2" aria-labelledby="presence-h">
            <div class="card p-5" data-reveal>
                <h2 id="presence-h" class="text-lg font-extrabold">{{ __('pages.about.presence_title') }}</h2>
                <p class="mt-1 text-sm text-stone-600 dark:text-stone-300">{{ __('pages.about.presence_text') }}</p>
                <ul class="mt-3 flex flex-wrap gap-2">
                    @foreach ($cities as $city)
                        <li class="inline-flex min-h-11 items-center gap-2 rounded-xl bg-brand-50 px-3 text-sm font-semibold text-brand-800 dark:bg-stone-800 dark:text-brand-500"><i class="fa-solid fa-location-dot" aria-hidden="true"></i>{{ $city }}</li>
                    @endforeach
                </ul>
            </div>
            <div class="card p-5" data-reveal>
                <h2 class="text-lg font-extrabold">{{ __('pages.about.delivery_title') }}</h2>
                <ol class="mt-3 space-y-3">
                    @foreach (__('pages.about.steps') as $n => $s)
                        <li class="flex items-start gap-3">
                            <span class="grid size-8 shrink-0 place-items-center rounded-full bg-brand-600 text-sm font-bold text-white">{{ $n + 1 }}</span>
                            <div class="min-w-0"><p class="font-semibold"><i class="fa-solid {{ $s['icon'] }} mr-1 text-brand-700 dark:text-brand-500" aria-hidden="true"></i>{{ $s['title'] }}</p><p class="text-sm text-stone-600 dark:text-stone-300">{{ $s['text'] }}</p></div>
                        </li>
                    @endforeach
                </ol>
            </div>
        </section>
    </div>

    <x-pages.cta-band />
</div>
