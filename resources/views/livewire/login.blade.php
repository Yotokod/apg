<div class="mx-auto max-w-md px-4 py-12">
    <form wire:submit="login" class="card space-y-3 p-6">
        <h1 class="text-xl font-extrabold">{{ __('nav.login') }}</h1>
        <label class="block text-sm font-medium">{{ __('cart.email') }}<input type="email" wire:model="email" class="input mt-1" autocomplete="email">@error('email')<span class="text-xs text-red-600">{{ $message }}</span>@enderror</label>
        <label class="block text-sm font-medium">{{ __('auth.password') }}<input type="password" wire:model="password" class="input mt-1" autocomplete="current-password">@error('password')<span class="text-xs text-red-600">{{ $message }}</span>@enderror</label>
        <button class="btn-primary w-full" wire:loading.attr="disabled">{{ __('nav.login') }}</button>
        <a href="{{ route('register') }}" wire:navigate class="block text-center text-sm text-brand-700">{{ __('auth.no_account') }}</a>
    </form>
</div>
