<div>
    <x-page-hero :title="__('pages.contact.title')" :subtitle="__('pages.contact.subtitle')" icon="fa-envelope" />
    <x-pages.breadcrumb :items="[[__('pages.contact.title'), null]]" />

    <div class="mx-auto grid max-w-7xl gap-6 px-4 py-6 lg:grid-cols-[1.2fr_1fr]">
        {{-- Form --}}
        <section class="card p-5 sm:p-6" aria-labelledby="form-h">
            <h2 id="form-h" class="text-lg font-extrabold">{{ __('pages.contact.form_title') }}</h2>
            @if ($sent)
                <div class="mt-4 space-y-3 text-center" role="status">
                    <span class="mx-auto grid size-14 place-items-center rounded-full bg-green-100 text-2xl text-green-700"><i class="fa-solid fa-circle-check" aria-hidden="true"></i></span>
                    <p class="font-bold">{{ __('pages.contact.sent_title') }}</p>
                    <p class="text-sm text-stone-600 dark:text-stone-300">{{ __('pages.contact.sent_text') }}</p>
                    <div class="flex flex-wrap justify-center gap-2">
                        <a href="https://wa.me/{{ site('whatsapp') }}" target="_blank" rel="noopener" class="btn-primary"><i class="fa-brands fa-whatsapp" aria-hidden="true"></i> {{ __('pages.common.whatsapp') }}</a>
                        <button type="button" wire:click="resetForm" class="btn-ghost">{{ __('pages.contact.new_message') }}</button>
                    </div>
                </div>
            @else
                <form wire:submit="send" class="mt-4 grid gap-3 sm:grid-cols-2" novalidate>
                    <div class="absolute -left-[9999px] size-0 overflow-hidden" aria-hidden="true">
                        <label>Website<input type="text" wire:model="website" tabindex="-1" autocomplete="off"></label>
                    </div>
                    @foreach ([['name', 'text', 'name'], ['email', 'email', 'email'], ['phone', 'tel', 'tel']] as [$f, $type, $auto])
                        <div>
                            <label for="c-{{ $f }}" class="mb-1 block text-sm font-medium">{{ __('pages.contact.'.$f) }}</label>
                            <input id="c-{{ $f }}" type="{{ $type }}" wire:model="{{ $f }}" autocomplete="{{ $auto }}" class="input" @error($f) aria-invalid="true" aria-describedby="c-{{ $f }}-e" @enderror>
                            @error($f)<p id="c-{{ $f }}-e" class="mt-1 text-xs text-red-700 dark:text-red-400">{{ $message }}</p>@enderror
                        </div>
                    @endforeach
                    <div>
                        <label for="c-subject" class="mb-1 block text-sm font-medium">{{ __('pages.contact.subject') }}</label>
                        <select id="c-subject" wire:model="subject" class="input">
                            <option value=""></option>
                            @foreach (__('pages.contact.subjects') as $k => $label) <option value="{{ $k }}">{{ $label }}</option> @endforeach
                        </select>
                        @error('subject')<p class="mt-1 text-xs text-red-700 dark:text-red-400">{{ $message }}</p>@enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label for="c-message" class="mb-1 block text-sm font-medium">{{ __('pages.contact.message') }}</label>
                        <textarea id="c-message" wire:model="message" rows="5" class="input" @error('message') aria-invalid="true" aria-describedby="c-message-e" @enderror></textarea>
                        @error('message')<p id="c-message-e" class="mt-1 text-xs text-red-700 dark:text-red-400">{{ $message }}</p>@enderror
                    </div>
                    <button class="btn-primary sm:col-span-2" wire:loading.attr="disabled" wire:target="send">
                        <span wire:loading.remove wire:target="send"><i class="fa-solid fa-paper-plane" aria-hidden="true"></i> {{ __('pages.contact.send') }}</span>
                        <span wire:loading wire:target="send"><i class="fa-solid fa-spinner fa-spin" aria-hidden="true"></i> {{ __('pages.contact.sending') }}</span>
                    </button>
                </form>
            @endif
        </section>

        {{-- Contact cards --}}
        <aside class="space-y-3" data-stagger>
            @php
                $cards = [
                    ['fa-brands fa-whatsapp', 'cards_whatsapp', site('phone'), 'https://wa.me/'.site('whatsapp'), true],
                    ['fa-solid fa-phone', 'cards_phone', site('phone'), 'tel:'.preg_replace('/\s+/', '', site('phone')), false],
                    ['fa-solid fa-envelope', 'cards_email', site('email'), 'mailto:'.site('email'), false],
                    ['fa-solid fa-location-dot', 'cards_address', site('address'), \App\Support\Identity::safeLink(site('maps_url')), true],
                ];
                $hours = site('hours');
            @endphp
            @foreach ($cards as [$icon, $label, $value, $href, $external])
                <div class="card flex items-center gap-3 p-4">
                    <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-brand-50 text-brand-700 dark:bg-stone-800 dark:text-brand-500"><i class="{{ $icon }}" aria-hidden="true"></i></span>
                    <div class="min-w-0">
                        <p class="text-xs font-bold uppercase tracking-wide text-stone-600 dark:text-stone-300">{{ __('pages.contact.'.$label) }}</p>
                        @if ($href) <a href="{{ $href }}" @if ($external) target="_blank" rel="noopener" @endif class="block min-h-6 truncate font-semibold hover:underline">{{ $value }}</a>
                        @else <p class="truncate font-semibold">{{ $value }}</p> @endif
                    </div>
                </div>
            @endforeach

            @if ($hours)
                <div class="card flex items-start gap-3 p-4">
                    <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-brand-50 text-brand-700 dark:bg-stone-800 dark:text-brand-500"><i class="fa-regular fa-clock" aria-hidden="true"></i></span>
                    <div class="min-w-0">
                        <p class="text-xs font-bold uppercase tracking-wide text-stone-600 dark:text-stone-300">{{ __('identity.contact.hours') }}</p>
                        <p class="whitespace-pre-line font-semibold">{{ $hours }}</p>
                    </div>
                </div>
            @endif

            <div class="card p-4">
                <h2 class="font-extrabold">{{ __('pages.contact.cities_title') }}</h2>
                <ul class="mt-2 flex flex-wrap gap-2">
                    @foreach ($cities as $city)
                        <li class="inline-flex min-h-11 items-center gap-2 rounded-xl bg-brand-50 px-3 text-sm font-semibold text-brand-800 dark:bg-stone-800 dark:text-brand-500"><i class="fa-solid fa-location-dot" aria-hidden="true"></i>{{ $city }}</li>
                    @endforeach
                </ul>
                <p class="mt-3 flex items-start gap-2 text-sm text-stone-600 dark:text-stone-300"><i class="fa-solid fa-earth-africa mt-0.5 text-brand-700 dark:text-brand-500" aria-hidden="true"></i>{{ __('pages.contact.worldwide') }}</p>
            </div>
        </aside>
    </div>

    <section class="mx-auto max-w-3xl px-4 pb-6" aria-labelledby="cfaq-h">
        <x-pages.section-title :title="__('pages.contact.faq_title')" id="cfaq-h" />
        <x-pages.accordion :items="$faq" id="cfaq" />
        <a href="{{ route('faq') }}" wire:navigate class="mt-2 inline-flex min-h-11 items-center gap-1 text-sm font-semibold text-brand-700 hover:underline dark:text-brand-500">{{ __('pages.common.faq') }} <i class="fa-solid fa-arrow-right text-xs" aria-hidden="true"></i></a>
    </section>
</div>
