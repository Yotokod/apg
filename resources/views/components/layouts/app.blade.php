@php
    use App\Support\Catalog;
    use App\Support\SiteData;
    use Illuminate\Support\Facades\Route;

    $metaTitle = $title ?? site('name');
    $metaDesc = $description ?? __('site.meta_desc');
    $metaImage = url($image ?? site('logo'));

    $shopItems = [[route('shop'), __('nav.all_shop'), 'fa-store']];
    foreach (SiteData::categories() as $key => $n) {
        $shopItems[] = [route('shop', ['cat' => $key]), Catalog::categoryLabel($key), category_icon($key)];
    }
    $courseItems = [[route('courses'), __('nav.all_courses'), 'fa-graduation-cap']];
    foreach (\App\Models\Course::where('is_active', true)->orderBy('id')->take(4)->get() as $c) {
        $courseItems[] = [Route::has('courses.show') ? route('courses.show', $c->slug) : route('courses'), $c->tr('title'), 'fa-book-open-reader'];
    }
    $discoverItems = collect([['about', 'nav.about', 'fa-circle-info'], ['gallery', 'nav.gallery', 'fa-images'], ['blog', 'nav.blog', 'fa-newspaper'], ['faq', 'nav.faq', 'fa-circle-question'], ['simulator', 'nav.simulator', 'fa-chart-line']])
        ->filter(fn ($d) => Route::has($d[0]))->map(fn ($d) => [route($d[0]), __($d[1]), $d[2]])->values()->all();
    $menus = [
        ['key' => 'shop', 'label' => __('nav.shop'), 'icon' => 'fa-store', 'items' => $shopItems, 'active' => request()->routeIs('shop*', 'product.*')],
        ['key' => 'courses', 'label' => __('nav.courses'), 'icon' => 'fa-graduation-cap', 'items' => $courseItems, 'active' => request()->routeIs('courses*', 'lesson*')],
        ['key' => 'discover', 'label' => __('nav.discover'), 'icon' => 'fa-compass', 'items' => $discoverItems, 'active' => request()->routeIs('about', 'gallery', 'blog*', 'faq', 'simulator')],
    ];
    $isAdmin = auth()->check() && in_array(auth()->user()->role, ['admin', 'superadmin']);
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" x-data="{ dark: localStorage.theme === 'dark', menu: false, acc: null }" :class="{ 'dark': dark }"
      x-init="$watch('dark', v => localStorage.theme = v ? 'dark' : 'light')">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $metaTitle }}</title>
    <meta name="description" content="{{ $metaDesc }}">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ site('name') }}">
    <meta property="og:title" content="{{ $metaTitle }}">
    <meta property="og:description" content="{{ $metaDesc }}">
    <meta property="og:image" content="{{ $metaImage }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:locale" content="{{ app()->getLocale() === 'fr' ? 'fr_FR' : 'en_US' }}">
    <meta name="twitter:card" content="summary">
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
    <a href="#main" class="sr-only z-[80] rounded-xl bg-brand-600 px-4 py-2 text-white focus:not-sr-only focus:fixed focus:left-3 focus:top-3">{{ __('site.skip') }}</a>

    <x-site.announcement-bar />

    {{-- Navbar (card style) --}}
    <header class="sticky top-0 z-30 px-3 pt-3">
        <nav class="card mx-auto flex max-w-7xl items-center justify-between gap-3 px-3 py-2" aria-label="{{ __('nav.main') }}">
            <a href="{{ route('home') }}" class="flex shrink-0 items-center" wire:navigate aria-label="{{ site('name') }}">
                <img src="{{ site('logo') }}" alt="{{ site('name') }}" width="120" height="40" class="h-10 w-auto max-[359px]:h-8">
            </a>
            <div class="hidden items-center gap-1 lg:flex">
                @foreach ($menus as $m)
                    <x-site.nav-dropdown :label="$m['label']" :icon="$m['icon']" :items="$m['items']" :active="$m['active']" />
                @endforeach
                <a href="{{ route('contact') }}" wire:navigate class="flex min-h-11 items-center gap-1.5 rounded-xl px-3 py-2 text-sm font-medium transition-colors hover:bg-stone-100 dark:hover:bg-stone-800 {{ request()->routeIs('contact') ? 'text-brand-700 dark:text-brand-500' : '' }}">
                    <i class="fa-solid fa-envelope" aria-hidden="true"></i>{{ __('nav.contact') }}
                </a>
            </div>
            <div class="flex items-center gap-1">
                <a href="{{ route('cart') }}" wire:navigate class="btn-ghost relative !px-3" aria-label="{{ __('nav.cart') }}">
                    <i class="fa-solid fa-basket-shopping" aria-hidden="true"></i>
                    <livewire:cart-badge />
                </a>
                <a href="{{ route('lang', app()->getLocale() === 'fr' ? 'en' : 'fr') }}" class="btn-ghost !px-3" aria-label="{{ __('nav.language') }}">
                    <i class="fa-solid fa-globe" aria-hidden="true"></i><span class="max-[359px]:sr-only"> {{ strtoupper(app()->getLocale() === 'fr' ? 'en' : 'fr') }}</span>
                </a>
                <button type="button" class="btn-ghost !px-3" @click="dark = !dark" aria-label="{{ __('nav.theme') }}">
                    <i class="fa-solid" :class="dark ? 'fa-sun' : 'fa-moon'" aria-hidden="true"></i>
                </button>
                @auth
                    @if ($isAdmin)
                        <a href="/admin" class="btn-accent hidden xl:inline-flex"><i class="fa-solid fa-gauge" aria-hidden="true"></i> {{ __('nav.admin') }}</a>
                    @endif
                    <a href="{{ route('account') }}" wire:navigate class="btn-primary hidden !px-3 sm:inline-flex" aria-label="{{ __('nav.account') }}"><i class="fa-solid fa-user" aria-hidden="true"></i></a>
                @else
                    <a href="{{ route('login') }}" wire:navigate class="btn-primary hidden sm:inline-flex">{{ __('nav.login') }}</a>
                @endauth
                <button type="button" class="btn-ghost !px-3 lg:hidden" @click="menu = true" aria-label="{{ __('nav.menu') }}" :aria-expanded="menu"><i class="fa-solid fa-bars" aria-hidden="true"></i></button>
            </div>
        </nav>
    </header>

    {{-- Mobile sidebar --}}
    <div x-show="menu" x-cloak class="fixed inset-0 z-[60] lg:hidden" @keydown.escape.window="menu = false" role="dialog" aria-modal="true" aria-label="{{ __('nav.menu') }}">
        <div class="absolute inset-0 bg-black/50" @click="menu = false"></div>
        <aside class="card absolute bottom-3 right-3 top-3 flex w-80 max-w-[88vw] flex-col gap-1 overflow-y-auto p-4"
               x-show="menu" x-transition:enter="transition duration-200" x-transition:enter-start="translate-x-8 opacity-0" x-transition:enter-end="translate-x-0 opacity-100">
            <div class="mb-2 flex items-center justify-between">
                <img src="{{ site('logo') }}" alt="{{ site('name') }}" width="96" height="32" class="h-8 w-auto">
                <button type="button" class="btn-ghost !px-3" @click="menu = false" aria-label="{{ __('nav.close') }}"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>
            </div>
            <a href="{{ route('home') }}" wire:navigate @click="menu = false" class="flex min-h-11 items-center gap-3 rounded-xl px-3 py-2 font-medium hover:bg-stone-100 dark:hover:bg-stone-800"><i class="fa-solid fa-house w-5" aria-hidden="true"></i>{{ __('nav.home') }}</a>
            @foreach ($menus as $m)
                <div>
                    <button type="button" @click="acc = acc === '{{ $m['key'] }}' ? null : '{{ $m['key'] }}'" :aria-expanded="acc === '{{ $m['key'] }}'" aria-label="{{ $m['label'] }}"
                            class="flex min-h-11 w-full items-center gap-3 rounded-xl px-3 py-2 text-left font-medium hover:bg-stone-100 dark:hover:bg-stone-800">
                        <i class="fa-solid {{ $m['icon'] }} w-5" aria-hidden="true"></i><span class="flex-1 truncate">{{ $m['label'] }}</span>
                        <i class="fa-solid fa-chevron-down text-xs transition-transform" :class="acc === '{{ $m['key'] }}' && 'rotate-180'" aria-hidden="true"></i>
                    </button>
                    <ul x-show="acc === '{{ $m['key'] }}'" x-cloak class="ml-5 space-y-0.5 border-l border-stone-200 pl-2 dark:border-stone-700">
                        @foreach ($m['items'] as [$url, $text, $ic])
                            <li><a href="{{ $url }}" wire:navigate @click="menu = false" class="flex min-h-11 items-center gap-3 rounded-xl px-3 py-2 text-sm hover:bg-stone-100 dark:hover:bg-stone-800"><i class="fa-solid {{ $ic }} w-4 text-brand-600" aria-hidden="true"></i><span class="truncate">{{ $text }}</span></a></li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
            <a href="{{ route('contact') }}" wire:navigate @click="menu = false" class="flex min-h-11 items-center gap-3 rounded-xl px-3 py-2 font-medium hover:bg-stone-100 dark:hover:bg-stone-800"><i class="fa-solid fa-envelope w-5" aria-hidden="true"></i>{{ __('nav.contact') }}</a>
            <a href="{{ route('cart') }}" wire:navigate @click="menu = false" class="flex min-h-11 items-center gap-3 rounded-xl px-3 py-2 font-medium hover:bg-stone-100 dark:hover:bg-stone-800"><i class="fa-solid fa-basket-shopping w-5" aria-hidden="true"></i>{{ __('nav.cart') }}</a>
            @auth
                @if ($isAdmin)<a href="/admin" class="btn-accent mt-2"><i class="fa-solid fa-gauge" aria-hidden="true"></i> {{ __('nav.admin') }}</a>@endif
                <a href="{{ route('account') }}" wire:navigate class="btn-primary mt-2"><i class="fa-solid fa-user" aria-hidden="true"></i> {{ __('nav.account') }}</a>
            @else
                <a href="{{ route('login') }}" wire:navigate class="btn-primary mt-2">{{ __('nav.login') }}</a>
            @endauth
        </aside>
    </div>

    @isset($breadcrumb)
        <nav aria-label="{{ __('site.breadcrumb') }}" class="mx-auto w-full max-w-7xl px-4 pt-3 text-sm text-stone-600 dark:text-stone-400">{{ $breadcrumb }}</nav>
    @endisset

    <main id="main" class="flex-1">{{ $slot }}</main>

    <x-site.footer />

    <x-site.whatsapp-float />
    <x-site.back-to-top />
    <livewire:chat-widget />
    <div x-data="{ items: [] }" @toast.window="items.push($event.detail); setTimeout(() => items.shift(), 3500)"
         class="fixed left-1/2 top-20 z-[75] flex -translate-x-1/2 flex-col gap-2" aria-live="polite">
        <template x-for="t in items"><div class="card px-4 py-3 text-sm font-medium" x-text="t.message"></div></template>
    </div>
    <script>if ('serviceWorker' in navigator) navigator.serviceWorker.register('/sw.js')</script>
</body>
</html>
