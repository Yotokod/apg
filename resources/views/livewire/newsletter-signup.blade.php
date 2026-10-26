<div>
    @if ($done)
        <p class="flex items-center gap-2 rounded-xl bg-white/10 px-4 py-3 text-sm font-medium text-white" role="status"><i class="fa-solid fa-circle-check text-accent-400" aria-hidden="true"></i>{{ __('news.ok') }}</p>
    @else
        <form wire:submit="subscribe" class="flex flex-col gap-2 sm:flex-row" novalidate>
            <label for="nl-email" class="sr-only">{{ __('news.label') }}</label>
            <input id="nl-email" type="email" wire:model="email" autocomplete="email" placeholder="{{ __('news.placeholder') }}"
                   class="input !border-white/20 !bg-white/10 text-white placeholder:text-white/60 sm:flex-1" aria-describedby="nl-hint" @error('email') aria-invalid="true" @enderror>
            <input type="text" wire:model="website" name="website" tabindex="-1" autocomplete="off" class="hidden" aria-hidden="true">
            <button type="submit" class="btn-primary shrink-0" wire:loading.attr="disabled" wire:target="subscribe">
                <i class="fa-solid fa-spinner fa-spin" wire:loading wire:target="subscribe" aria-hidden="true"></i>
                <i class="fa-solid fa-paper-plane" wire:loading.remove wire:target="subscribe" aria-hidden="true"></i>{{ __('news.cta') }}
            </button>
        </form>
        @error('email')<p class="mt-1.5 text-sm font-medium text-accent-400" role="alert">{{ $message }}</p>@enderror
        <p id="nl-hint" class="mt-1.5 text-xs text-white/70">{{ __('news.hint') }}</p>
    @endif
</div>
