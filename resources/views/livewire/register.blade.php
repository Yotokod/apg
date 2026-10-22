<div class="mx-auto max-w-md px-4 py-12">
    <form wire:submit="register" class="card space-y-3 p-6">
        <h1 class="text-xl font-extrabold">{{ __('auth.register') }}</h1>
        @foreach ([['name', 'cart.name', 'text'], ['email', 'cart.email', 'email'], ['phone', 'cart.phone', 'tel'], ['password', 'auth.password', 'password']] as [$f, $k, $type])
            <label class="block text-sm font-medium">{{ __($k) }}<input type="{{ $type }}" wire:model="{{ $f }}" class="input mt-1">@error($f)<span class="text-xs text-red-600">{{ $message }}</span>@enderror</label>
        @endforeach
        <button class="btn-primary w-full" wire:loading.attr="disabled">{{ __('auth.register') }}</button>
        <a href="{{ route('login') }}" wire:navigate class="block text-center text-sm text-brand-700">{{ __('auth.have_account') }}</a>
    </form>
</div>
