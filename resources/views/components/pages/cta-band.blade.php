@props(['title' => null, 'text' => null, 'primary' => null, 'primaryUrl' => null])
<section data-reveal {{ $attributes->class('mx-auto my-8 max-w-7xl px-4') }}>
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-brand-700 to-ink-900 px-5 py-6 text-white sm:px-8">
        <i class="fa-solid fa-feather-pointed pointer-events-none absolute -right-3 -top-3 text-[8rem] opacity-10" aria-hidden="true"></i>
        <div class="relative flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="min-w-0">
                <h2 class="text-xl font-extrabold sm:text-2xl">{{ $title ?? __('pages.common.cta_title') }}</h2>
                <p class="mt-1 text-sm text-white/90">{{ $text ?? __('pages.common.cta_text') }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="https://wa.me/{{ site('whatsapp') }}" target="_blank" rel="noopener" class="btn bg-white text-ink-900 hover:bg-stone-100"><i class="fa-brands fa-whatsapp" aria-hidden="true"></i> {{ __('pages.common.whatsapp') }}</a>
                <a href="{{ $primaryUrl ?? route('contact') }}" wire:navigate class="btn border border-white/60 text-white hover:bg-white/10">{{ $primary ?? __('pages.common.contact_us') }}</a>
            </div>
        </div>
    </div>
</section>
