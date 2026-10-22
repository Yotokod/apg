<div>
    <x-page-hero :title="__('nav.contact')" icon="fa-envelope" />
    <div class="mx-auto grid max-w-5xl gap-6 px-4 py-8 md:grid-cols-2">
        <form wire:submit="send" class="card space-y-3 p-6">
            @if ($sent) <p class="font-semibold text-brand-700"><i class="fa-solid fa-circle-check"></i> {{ __('contact.sent') }}</p> @endif
            @foreach (['name', 'email'] as $f)
                <label class="block text-sm font-medium">{{ __('cart.'.$f) }}<input wire:model="{{ $f }}" class="input mt-1">@error($f)<span class="text-xs text-red-600">{{ $message }}</span>@enderror</label>
            @endforeach
            <label class="block text-sm font-medium">{{ __('contact.message') }}<textarea wire:model="message" rows="5" class="input mt-1"></textarea>@error('message')<span class="text-xs text-red-600">{{ $message }}</span>@enderror</label>
            <button class="btn-primary w-full" wire:loading.attr="disabled">{{ __('contact.send') }}</button>
        </form>
        <div class="card space-y-3 p-6 text-sm">
            <p><i class="fa-solid fa-phone w-5"></i>{{ site('phone') }}</p>
            <p><i class="fa-solid fa-envelope w-5"></i>{{ site('email') }}</p>
            <p><i class="fa-solid fa-location-dot w-5"></i>{{ site('cities') }}</p>
            <a href="https://wa.me/{{ site('whatsapp') }}" target="_blank" rel="noopener" class="btn-primary"><i class="fa-brands fa-whatsapp"></i> WhatsApp</a>
        </div>
    </div>
</div>
