@php
    $strip = fn (?string $t) => preg_replace('/^\s*(module|chapitre|chapter)\s*\d+\s*[:.\-–]\s*/iu', '', (string) $t);
    $free = $course->price == 0;
@endphp
<div>
    <section class="bg-gradient-to-r from-ink-900 via-ink-800 to-brand-800 text-white">
        <div class="mx-auto max-w-7xl px-4 pb-6 pt-3">
            <x-catalog.breadcrumbs :items="[__('nav.courses') => route('courses'), $course->tr('title') => null]" :dark="true" />
            <div class="mt-3 max-w-3xl space-y-2 lg:pr-[22rem]">
                <h1 class="text-2xl font-extrabold leading-tight sm:text-3xl">{{ $course->tr('title') }}</h1>
                <p class="text-sm text-white/90 sm:text-base">{{ $course->tr('description') }}</p>
                <ul class="flex flex-wrap gap-x-4 gap-y-1 pt-1 text-sm font-semibold">
                    <li><i class="fa-solid fa-layer-group" aria-hidden="true"></i> {{ $course->modules->count() }} {{ __('catalog.courses.modules_label') }}</li>
                    <li><i class="fa-solid fa-list-check" aria-hidden="true"></i> {{ $totalLessons }} {{ __('courses.lessons') }}</li>
                    <li><i class="fa-solid fa-circle-check" aria-hidden="true"></i> {{ __('catalog.courses.no_prereq') }}</li>
                </ul>
            </div>
        </div>
    </section>

    <div class="mx-auto grid max-w-7xl items-start gap-6 px-4 py-6 lg:grid-cols-[1fr_20rem]">
        {{-- Purchase / progress card (sticky; first on mobile, pulled up over the hero on desktop) --}}
        <aside class="order-first lg:order-last lg:sticky lg:top-24 lg:-mt-40" aria-label="{{ __('catalog.courses.enrol_card') }}">
            <div class="card space-y-3 p-4 shadow-lg">
                @if ($course->image)
                    <img src="{{ \App\Support\Catalog::imageUrl($course->image) }}" alt="{{ $course->tr('title') }}" loading="lazy" class="aspect-video w-full rounded-xl object-cover">
                @endif
                @if ($enrolled)
                    <x-catalog.progress :value="count($done)" :total="$totalLessons" :label="__('catalog.courses.progress')" />
                    @if ($resume)
                        <a href="{{ route('lesson', $resume->id) }}" wire:navigate class="btn-primary w-full">
                            <i class="fa-solid fa-circle-play" aria-hidden="true"></i> {{ count($done) ? __('catalog.courses.resume') : __('catalog.courses.start') }}
                        </a>
                    @endif
                @else
                    <p class="text-3xl font-extrabold">{{ $free ? __('courses.free') : fcfa($course->price) }}</p>
                    @if ($pending)<p class="rounded-lg bg-amber-100 p-2 text-sm font-semibold text-amber-900"><i class="fa-solid fa-hourglass-half" aria-hidden="true"></i> {{ __('catalog.courses.pending') }}</p>@endif
                    <button type="button" wire:click="enroll" wire:loading.attr="disabled" class="btn-primary w-full">
                        <span wire:loading wire:target="enroll"><i class="fa-solid fa-spinner fa-spin" aria-hidden="true"></i></span>
                        <i class="fa-solid {{ ! auth()->check() ? 'fa-right-to-bracket' : ($free ? 'fa-unlock' : 'fa-envelope') }}" wire:loading.remove wire:target="enroll" aria-hidden="true"></i>
                        {{ ! auth()->check() ? __('catalog.courses.login_to_enroll') : ($free ? __('catalog.courses.enroll_free') : __('catalog.courses.enroll_paid')) }}
                    </button>
                @endif
                <p class="pt-1 text-sm font-bold">{{ __('catalog.courses.included') }}</p>
                <ul class="space-y-1.5 text-sm text-stone-700 dark:text-stone-300">
                    @foreach (['lessons', 'quiz', 'anytime', 'support'] as $k)
                        <li class="flex items-start gap-2"><i class="fa-solid fa-check mt-0.5 text-emerald-700" aria-hidden="true"></i><span>{{ __('catalog.courses.inc_'.$k, ['n' => $totalLessons]) }}</span></li>
                    @endforeach
                </ul>
            </div>
        </aside>

        <div class="min-w-0 space-y-8">
            {{-- What you will learn --}}
            <section aria-labelledby="learn-title" class="card p-4 sm:p-5">
                <h2 id="learn-title" class="mb-3 text-xl font-extrabold">{{ __('catalog.courses.learn') }}</h2>
                <ul class="grid gap-x-6 gap-y-2 text-sm sm:grid-cols-2">
                    @foreach ($learn as $m)
                        <li class="flex items-start gap-2"><i class="fa-solid fa-check mt-0.5 text-emerald-700" aria-hidden="true"></i><span>{{ $strip($m->tr('title')) }}</span></li>
                    @endforeach
                </ul>
            </section>

            {{-- Syllabus --}}
            <section aria-labelledby="syllabus-title" x-data="{ open: [{{ $course->modules->first()?->id }}] }">
                <div class="mb-3 flex flex-wrap items-end justify-between gap-2">
                    <h2 id="syllabus-title" class="text-xl font-extrabold">{{ __('catalog.courses.syllabus') }}</h2>
                    <p class="text-sm text-stone-600 dark:text-stone-400">{{ $course->modules->count() }} {{ __('catalog.courses.modules_label') }} · {{ $totalLessons }} {{ __('courses.lessons') }}</p>
                </div>
                <div class="space-y-2">
                    @foreach ($course->modules as $m)
                        <div class="card overflow-hidden">
                            <button type="button" class="flex min-h-11 w-full items-center justify-between gap-3 px-4 py-2 text-left font-bold"
                                    @click="open.includes({{ $m->id }}) ? open = open.filter(i => i !== {{ $m->id }}) : open.push({{ $m->id }})"
                                    :aria-expanded="open.includes({{ $m->id }})" aria-controls="mod-{{ $m->id }}">
                                <span class="min-w-0 truncate">{{ $loop->iteration }}. {{ $strip($m->tr('title')) }}</span>
                                <span class="flex shrink-0 items-center gap-2 text-xs font-semibold text-stone-600 dark:text-stone-400">
                                    {{ $m->lessons->count() }} {{ __('courses.lessons') }}
                                    <i class="fa-solid fa-chevron-down transition-transform" :class="open.includes({{ $m->id }}) && 'rotate-180'" aria-hidden="true"></i>
                                </span>
                            </button>
                            <ul id="mod-{{ $m->id }}" x-show="open.includes({{ $m->id }})" x-collapse class="border-t border-stone-200 p-2 dark:border-stone-800">
                                @foreach ($m->lessons as $l)
                                    <li>
                                        @if ($enrolled)
                                            <a href="{{ route('lesson', $l->id) }}" wire:navigate class="flex min-h-11 items-center gap-2 rounded-xl px-3 py-2 text-sm hover:bg-stone-100 dark:hover:bg-stone-800">
                                                <i class="fa-solid {{ in_array($l->id, $done) ? 'fa-circle-check text-emerald-700' : 'fa-circle-play text-stone-500' }}" aria-hidden="true"></i><span class="truncate">{{ $l->tr('title') }}</span>
                                            </a>
                                        @else
                                            <span class="flex min-h-11 items-center gap-2 px-3 py-2 text-sm text-stone-600 dark:text-stone-400"><i class="fa-solid fa-lock" aria-hidden="true"></i><span class="truncate">{{ $l->tr('title') }}</span></span>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                </div>
            </section>

            {{-- FAQ --}}
            <section aria-labelledby="faq-title" x-data="{ open: null }">
                <h2 id="faq-title" class="mb-3 text-xl font-extrabold">{{ __('catalog.courses.faq') }}</h2>
                <div class="space-y-2">
                    @foreach (range(1, 4) as $i)
                        <div class="card">
                            <button type="button" class="flex min-h-11 w-full items-center justify-between gap-3 px-4 py-2 text-left text-sm font-bold" @click="open = open === {{ $i }} ? null : {{ $i }}" :aria-expanded="open === {{ $i }}">
                                <span>{{ __('catalog.courses.faq_q'.$i) }}</span><i class="fa-solid fa-chevron-down shrink-0 transition-transform" :class="open === {{ $i }} && 'rotate-180'" aria-hidden="true"></i>
                            </button>
                            <p x-show="open === {{ $i }}" x-collapse class="px-4 pb-3 text-sm text-stone-700 dark:text-stone-300">{{ __('catalog.courses.faq_a'.$i) }}</p>
                        </div>
                    @endforeach
                </div>
            </section>

            @if ($related->isNotEmpty())
                <section aria-labelledby="rel-title">
                    <h2 id="rel-title" class="mb-3 text-xl font-extrabold">{{ __('catalog.courses.related') }}</h2>
                    <div class="grid gap-3 sm:grid-cols-3">
                        @foreach ($related as $r)
                            <a href="{{ route('courses.show', $r->slug) }}" wire:navigate wire:key="rel{{ $r->id }}" class="card flex flex-col gap-1 p-3">
                                <i class="fa-solid fa-book-open-reader text-brand-600" aria-hidden="true"></i>
                                <span class="line-clamp-2 min-h-[2.5rem] text-sm font-bold">{{ $r->tr('title') }}</span>
                                <span class="flex justify-between text-xs text-stone-700 dark:text-stone-300"><span>{{ $r->lessons_count }} {{ __('courses.lessons') }}</span><strong>{{ $r->price ? fcfa($r->price) : __('courses.free') }}</strong></span>
                            </a>
                        @endforeach
                    </div>
                </section>
            @endif
        </div>
    </div>
</div>
