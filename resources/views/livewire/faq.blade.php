<div>
    <x-page-hero :title="__('pages.faq.title')" :subtitle="__('pages.faq.subtitle')" icon="fa-circle-question" />
    <x-pages.breadcrumb :items="[[__('pages.faq.meta_title'), null]]" />
    <script type="application/ld+json">{!! $jsonLd !!}</script>

    <div class="mx-auto max-w-3xl space-y-4 px-4 py-6">
        <div class="relative">
            <label for="faq-search" class="sr-only">{{ __('pages.faq.search') }}</label>
            <i class="fa-solid fa-magnifying-glass pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-stone-500" aria-hidden="true"></i>
            <input id="faq-search" type="search" wire:model.live.debounce.300ms="search" placeholder="{{ __('pages.faq.search') }}" class="input !pl-11">
        </div>

        <div class="flex flex-wrap gap-2" role="group" aria-label="{{ __('pages.faq.title') }}">
            @foreach (['' => __('pages.faq.all')] + $categories as $key => $label)
                <button type="button" wire:click="$set('category', '{{ $key }}')" aria-pressed="{{ $category === (string) $key ? 'true' : 'false' }}"
                        class="min-h-11 rounded-full border px-4 text-sm font-semibold transition {{ $category === (string) $key ? 'border-brand-600 bg-brand-600 text-white' : 'border-stone-300 hover:bg-stone-100 dark:border-stone-700 dark:hover:bg-stone-800' }}">{{ $label }}</button>
            @endforeach
        </div>

        <div wire:loading.class="opacity-60" wire:target="search,category">
            @if (count($items))
                <x-pages.accordion :items="$items" id="faq" wire:key="faq-{{ md5($search.$category) }}" />
            @else
                <p class="card p-6 text-center text-sm text-stone-600 dark:text-stone-300"><i class="fa-regular fa-face-frown mr-1" aria-hidden="true"></i>{{ __('pages.faq.empty') }}</p>
            @endif
        </div>

        <div class="card flex flex-col gap-3 bg-brand-50 p-5 dark:bg-stone-900 sm:flex-row sm:items-center sm:justify-between">
            <div><h2 class="font-extrabold">{{ __('pages.faq.not_found_title') }}</h2><p class="text-sm text-stone-600 dark:text-stone-300">{{ __('pages.faq.not_found_text') }}</p></div>
            <a href="https://wa.me/{{ site('whatsapp') }}" target="_blank" rel="noopener" class="btn-primary shrink-0"><i class="fa-brands fa-whatsapp" aria-hidden="true"></i> {{ __('pages.common.whatsapp') }}</a>
        </div>
    </div>
</div>
