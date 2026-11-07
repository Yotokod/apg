@php
    $wa = \App\Support\SiteData::whatsappUrl();
    $themeCss = \App\Support\Identity::themeCss();
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>{{ __('identity.maintenance.title') }} | {{ site('name') }}</title>
    <link rel="icon" href="{{ site('favicon', '/images/favicon.png') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <script>if (localStorage.theme === 'dark') document.documentElement.classList.add('dark')</script>
    @vite(['resources/css/app.css'])
    @if ($themeCss)<style>{!! $themeCss !!}</style>@endif
</head>
<body class="grid min-h-screen place-items-center bg-stone-50 px-4 py-10 dark:bg-stone-950">
    <main class="card w-full max-w-lg p-6 text-center sm:p-8" data-maintenance>
        <img src="{{ site('logo') }}" alt="{{ site('name') }}" width="160" height="48" class="mx-auto h-12 w-auto">
        <span class="mx-auto mt-6 grid size-16 place-items-center rounded-2xl bg-brand-50 text-2xl text-brand-700 dark:bg-stone-800 dark:text-brand-500">
            <i class="fa-solid fa-screwdriver-wrench" aria-hidden="true"></i>
        </span>
        <h1 class="mt-4 text-2xl font-extrabold text-ink-900 dark:text-white">{{ __('identity.maintenance.heading') }}</h1>
        <p class="mt-2 whitespace-pre-line text-stone-600 dark:text-stone-300">{{ $message }}</p>
        <div class="mt-6 flex flex-wrap justify-center gap-2">
            @if ($wa)<a href="{{ $wa }}" target="_blank" rel="noopener" class="btn-primary"><i class="fa-brands fa-whatsapp" aria-hidden="true"></i> {{ __('identity.maintenance.whatsapp') }}</a>@endif
            @if (site('email'))<a href="mailto:{{ site('email') }}" class="btn-ghost"><i class="fa-solid fa-envelope" aria-hidden="true"></i> {{ __('identity.maintenance.email') }}</a>@endif
            <a href="{{ url()->current() }}" class="btn-ghost"><i class="fa-solid fa-rotate-right" aria-hidden="true"></i> {{ __('identity.maintenance.retry') }}</a>
        </div>
        <p class="mt-6 text-xs text-stone-500 dark:text-stone-400">{{ __('identity.maintenance.code') }}</p>
    </main>
</body>
</html>
