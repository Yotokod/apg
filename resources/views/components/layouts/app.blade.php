<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" x-data="{ dark: localStorage.theme === 'dark', menu: false }" :class="{ 'dark': dark }"
      x-init="$watch('dark', v => localStorage.theme = v ? 'dark' : 'light')">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? site('name') }}</title>
    <meta name="theme-color" content="{{ site('primary_color') }}">
    <link rel="manifest" href="/manifest.webmanifest">
    <link rel="icon" href="/images/favicon.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <script>if (localStorage.theme === 'dark') document.documentElement.classList.add('dark')</script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex flex-col">
    {{-- Navbar (card style) --}}
    <header class="sticky top-0 z-30 px-3 pt-3">
        <nav class="card mx-auto flex max-w-7xl items-center justify-between gap-3 px-4 py-2" aria-label="Main">
            <a href="{{ route('home') }}" class="flex items-center gap-2 font-extrabold text-brand-700 dark:text-brand-500" wire:navigate>
                <img src="{{ site('logo') }}" alt="{{ site('name') }}" class="h-10 w-auto">
                
            </a>
            <div class="hidden items-center gap-1 md:flex">
                @foreach ([['shop', 'nav.shop', 'fa-store'], ['courses', 'nav.courses', 'fa-graduation-cap'], ['simulator', 'nav.simulator', 'fa-chart-line'], ['contact', 'nav.contact', 'fa-envelope']] as [$r, $k, $i])
                    <a href="{{ route($r) }}" wire:navigate class="rounded-xl px-3 py-2 text-sm font-medium hover:bg-stone-100 dark:hover:bg-stone-800 {{ request()->routeIs($r.'*') ? 'text-brand-700 dark:text-brand-500' : '' }}">
                        <i class="fa-solid {{ $i }} mr-1"></i>{{ __($k) }}
                    </a>
                @endforeach
            </div>
            <div class="flex items-center gap-1">
                <a href="{{ route('cart') }}" wire:navigate class="btn-ghost relative !px-3" aria-label="{{ __('nav.cart') }}">
                    <i class="fa-solid fa-basket-shopping"></i>
                    <livewire:cart-badge />
                </a>
                <a href="{{ route('lang', app()->getLocale() === 'fr' ? 'en' : 'fr') }}" class="btn-ghost !px-3" aria-label="Language">
                    <i class="fa-solid fa-globe"></i> {{ strtoupper(app()->getLocale() === 'fr' ? 'en' : 'fr') }}
                </a>
                <button class="btn-ghost !px-3" @click="dark = !dark" aria-label="{{ __('nav.theme') }}">
                    <i class="fa-solid" :class="dark ? 'fa-sun' : 'fa-moon'"></i>
                </button>
                @auth
                    @if (in_array(auth()->user()->role, ['admin', 'superadmin']))
                        <a href="/admin" class="btn-primary hidden sm:inline-flex"><i class="fa-solid fa-gauge"></i> {{ __('nav.admin') }}</a>
                    @endif
                    <a href="{{ route('account') }}" wire:navigate class="btn-primary hidden sm:inline-flex"><i class="fa-solid fa-user"></i></a>
                @else
                    <a href="{{ route('login') }}" wire:navigate class="btn-primary hidden sm:inline-flex">{{ __('nav.login') }}</a>
                @endauth
                <button class="btn-ghost !px-3 md:hidden" @click="menu = true" aria-label="{{ __('nav.menu') }}"><i class="fa-solid fa-bars"></i></button>
            </div>
        </nav>
    </header>

    {{-- Mobile sidebar --}}
    <div x-show="menu" x-cloak class="fixed inset-0 z-40 md:hidden" @keydown.escape.window="menu = false">
        <div class="absolute inset-0 bg-black/50" @click="menu = false"></div>
        <aside class="card absolute right-3 top-3 bottom-3 flex w-72 max-w-[85vw] flex-col gap-1 p-4">
            <button class="btn-ghost mb-2 self-end !px-3" @click="menu = false" aria-label="{{ __('nav.close') }}"><i class="fa-solid fa-xmark"></i></button>
            @foreach ([['home', 'nav.home', 'fa-house'], ['shop', 'nav.shop', 'fa-store'], ['courses', 'nav.courses', 'fa-graduation-cap'], ['simulator', 'nav.simulator', 'fa-chart-line'], ['contact', 'nav.contact', 'fa-envelope'], ['cart', 'nav.cart', 'fa-basket-shopping']] as [$r, $k, $i])
                <a href="{{ route($r) }}" wire:navigate class="rounded-xl px-3 py-3 font-medium hover:bg-stone-100 dark:hover:bg-stone-800"><i class="fa-solid {{ $i }} mr-2 w-5"></i>{{ __($k) }}</a>
            @endforeach
            @auth
                <a href="{{ route('account') }}" wire:navigate class="btn-primary mt-2"><i class="fa-solid fa-user"></i> {{ __('nav.account') }}</a>
            @else
                <a href="{{ route('login') }}" wire:navigate class="btn-primary mt-2">{{ __('nav.login') }}</a>
            @endauth
        </aside>
    </div>

    <main class="flex-1">{{ $slot }}</main>

    <footer class="mt-16 border-t border-stone-200 bg-white py-10 dark:border-stone-800 dark:bg-stone-900">
        <div class="mx-auto grid max-w-7xl gap-8 px-4 sm:grid-cols-3">
            <div>
                <div class="flex items-center gap-2 font-extrabold text-brand-700 dark:text-brand-500"><img src="{{ site('logo') }}" alt="{{ site('name') }}" class="h-12 w-auto"></div>
                <p class="mt-2 text-sm text-stone-600 dark:text-stone-400">{{ site('tagline') }}</p>
            </div>
            <div class="text-sm">
                <p class="font-semibold">{{ __('footer.contact') }}</p>
                <p class="mt-2"><i class="fa-solid fa-phone w-5"></i>{{ site('phone') }}</p>
                <p><i class="fa-solid fa-envelope w-5"></i>{{ site('email') }}</p>
                <p><i class="fa-solid fa-location-dot w-5"></i>{{ site('cities') }}</p>
            </div>
            <div class="flex items-start gap-3 text-xl">
                @foreach (['youtube' => 'fa-youtube', 'tiktok' => 'fa-tiktok', 'facebook' => 'fa-facebook', 'instagram' => 'fa-instagram', 'linkedin' => 'fa-linkedin'] as $n => $ic)
                    @if (config("site_identity.socials.$n"))
                        <a href="{{ config("site_identity.socials.$n") }}" target="_blank" rel="noopener" aria-label="{{ $n }}" class="hover:text-brand-600"><i class="fa-brands {{ $ic }}"></i></a>
                    @endif
                @endforeach
            </div>
        </div>
        <p class="mt-8 text-center text-xs text-stone-500">© {{ date('Y') }} {{ site('name') }}</p>
    </footer>

    <livewire:chat-widget />
    <div x-data="{ items: [] }" @toast.window="items.push($event.detail); setTimeout(() => items.shift(), 3500)"
         class="fixed left-1/2 top-20 z-50 flex -translate-x-1/2 flex-col gap-2" aria-live="polite">
        <template x-for="t in items"><div class="card px-4 py-3 text-sm font-medium" x-text="t.message"></div></template>
    </div>
    <script>if ('serviceWorker' in navigator) navigator.serviceWorker.register('/sw.js')</script>
</body>
</html>
