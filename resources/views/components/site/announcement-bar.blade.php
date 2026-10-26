@php($wa = \App\Support\SiteData::whatsappUrl())
<div x-data="{ show: true, i: 0, t: null,
        init() { try { if (sessionStorage.getItem('apg.ann') === '1') this.show = false } catch (e) {}
                 this.t = setInterval(() => { this.i = (this.i + 1) % 2 }, 5000) },
        close() { this.show = false; try { sessionStorage.setItem('apg.ann', '1') } catch (e) {} } }"
     x-show="show" x-cloak
     class="bg-ink-900 text-white" role="region" aria-label="{{ __('site.ann1') }}">
    <div class="mx-auto flex h-10 max-w-7xl items-center gap-3 px-4 text-xs sm:text-sm">
        <span class="w-8 shrink-0" aria-hidden="true"></span>
        <div class="relative min-w-0 flex-1 text-center">
            <p x-show="i === 0" x-transition.opacity class="truncate font-medium"><i class="fa-solid fa-plane-departure mr-1 text-accent-400" aria-hidden="true"></i>{{ __('site.ann1') }}</p>
            <p x-show="i === 1" x-cloak x-transition.opacity class="truncate font-medium">
                <i class="fa-brands fa-whatsapp mr-1 text-accent-400" aria-hidden="true"></i>
                @if ($wa)<a href="{{ $wa }}" target="_blank" rel="noopener" class="underline-offset-2 hover:underline">{{ __('site.ann2', ['phone' => site('phone')]) }}</a>
                @else {{ __('site.ann2', ['phone' => site('phone')]) }} @endif
            </p>
        </div>
        <button type="button" @click="close()" class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg hover:bg-white/10" aria-label="{{ __('site.ann_close') }}"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>
    </div>
</div>
