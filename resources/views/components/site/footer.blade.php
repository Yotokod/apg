@php
    use App\Support\Catalog;
    use App\Support\SiteData;
    $cats = SiteData::categories();
    $methods = SiteData::paymentMethods();
    $wa = SiteData::whatsappUrl();
    $linkSet = fn (array $defs) => collect($defs)->filter(fn ($d) => \Illuminate\Support\Facades\Route::has($d[0]))->map(fn ($d) => [route($d[0]), __($d[1])])->all();
    $columns = [
        'footer.learn' => $linkSet([['courses', 'nav.all_courses'], ['simulator', 'nav.simulator'], ['blog', 'nav.blog'], ['gallery', 'nav.gallery']]),
        'footer.company' => $linkSet([['about', 'nav.about'], ['contact', 'nav.contact'], ['faq', 'nav.faq'], ['account', 'nav.account']]),
        'footer.help' => $linkSet([['cgv', 'nav.cgv'], ['confidentialite', 'nav.privacy'], ['mentions-legales', 'nav.legal'], ['cart', 'nav.cart']]),
    ];
    $socials = ['youtube' => 'fa-youtube', 'tiktok' => 'fa-tiktok', 'facebook' => 'fa-facebook', 'instagram' => 'fa-instagram', 'linkedin' => 'fa-linkedin'];
@endphp
<footer class="mt-12 bg-ink-900 text-white">
    <div class="mx-auto max-w-7xl px-4 py-8">
        {{-- Newsletter --}}
        <div class="grid items-center gap-4 rounded-2xl bg-white/5 p-5 md:grid-cols-2">
            <div class="min-w-0">
                <h2 class="text-lg font-extrabold"><i class="fa-solid fa-envelope-open-text mr-2 text-accent-400" aria-hidden="true"></i>{{ __('news.title') }}</h2>
                <p class="mt-1 text-sm text-white/75">{{ __('news.lead') }}</p>
            </div>
            <livewire:newsletter-signup />
        </div>

        <div class="mt-8 grid gap-8 sm:grid-cols-2 lg:grid-cols-6">
            <div class="min-w-0 lg:col-span-2">
                <a href="{{ route('home') }}" wire:navigate class="inline-block rounded-xl bg-white p-2"><img src="{{ site('logo') }}" alt="{{ site('name') }}" width="160" height="48" loading="lazy" class="h-12 w-auto"></a>
                <p class="mt-3 text-sm text-white/75">{{ __('home.lead') }}</p>
                <p class="mt-4 text-xs font-semibold uppercase tracking-wide text-white/60">{{ __('footer.follow') }}</p>
                <div class="mt-2 flex flex-wrap gap-2">
                    @foreach ($socials as $n => $ic)
                        @if (config("site_identity.socials.$n"))
                            <a href="{{ config("site_identity.socials.$n") }}" target="_blank" rel="noopener" aria-label="{{ ucfirst($n) }}" class="flex h-11 w-11 items-center justify-center rounded-xl bg-white/10 text-lg transition-colors hover:bg-brand-600"><i class="fa-brands {{ $ic }}" aria-hidden="true"></i></a>
                        @endif
                    @endforeach
                    @if ($wa)<a href="{{ $wa }}" target="_blank" rel="noopener" aria-label="WhatsApp" class="flex h-11 w-11 items-center justify-center rounded-xl bg-white/10 text-lg transition-colors hover:bg-brand-600"><i class="fa-brands fa-whatsapp" aria-hidden="true"></i></a>@endif
                </div>
            </div>

            <nav aria-label="{{ __('footer.shop') }}" class="min-w-0">
                <h3 class="text-sm font-bold">{{ __('footer.shop') }}</h3>
                <ul class="mt-3 space-y-1 text-sm text-white/75">
                    <li><a href="{{ route('shop') }}" wire:navigate class="inline-block py-1 hover:text-white hover:underline">{{ __('footer.all_products') }}</a></li>
                    @foreach ($cats as $key => $n)
                        <li><a href="{{ route('shop', ['cat' => $key]) }}" wire:navigate class="inline-block max-w-full truncate py-1 hover:text-white hover:underline">{{ Catalog::categoryLabel($key) }}</a></li>
                    @endforeach
                </ul>
            </nav>

            @foreach ($columns as $title => $links)
                <nav aria-label="{{ __($title) }}" class="min-w-0">
                    <h3 class="text-sm font-bold">{{ __($title) }}</h3>
                    <ul class="mt-3 space-y-1 text-sm text-white/75">
                        @foreach ($links as [$url, $text])
                            <li><a href="{{ $url }}" wire:navigate class="inline-block max-w-full truncate py-1 hover:text-white hover:underline">{{ $text }}</a></li>
                        @endforeach
                    </ul>
                </nav>
            @endforeach
        </div>

        {{-- Contact --}}
        <address class="mt-8 grid gap-3 border-t border-white/10 pt-6 text-sm not-italic text-white/80 sm:grid-cols-2 lg:grid-cols-4">
            <p class="flex min-w-0 items-center gap-2"><i class="fa-solid fa-phone w-5 text-accent-400" aria-hidden="true"></i><a href="tel:{{ preg_replace('/[^\d+]/', '', site('phone')) }}" class="truncate hover:underline">{{ site('phone') }}</a></p>
            <p class="flex min-w-0 items-center gap-2"><i class="fa-solid fa-envelope w-5 text-accent-400" aria-hidden="true"></i><a href="mailto:{{ site('email') }}" class="truncate hover:underline">{{ site('email') }}</a></p>
            @if ($wa)<p class="flex min-w-0 items-center gap-2"><i class="fa-brands fa-whatsapp w-5 text-accent-400" aria-hidden="true"></i><a href="{{ $wa }}" target="_blank" rel="noopener" class="truncate hover:underline">{{ __('footer.whatsapp') }}</a></p>@endif
            <p class="flex min-w-0 items-center gap-2"><i class="fa-solid fa-location-dot w-5 text-accent-400" aria-hidden="true"></i><span class="truncate">{{ site('cities') }}</span></p>
        </address>

        @if ($methods)
            <div class="mt-6 flex flex-wrap items-center gap-2 text-xs">
                <span class="font-semibold uppercase tracking-wide text-white/60">{{ __('footer.payments') }}</span>
                @foreach ($methods as $m)
                    <span class="inline-flex items-center gap-1.5 rounded-lg bg-white/10 px-2.5 py-1 font-medium"><i class="fa-solid fa-credit-card text-accent-400" aria-hidden="true"></i>{{ $m }}</span>
                @endforeach
            </div>
        @endif
    </div>

    <div class="border-t border-white/10">
        <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-3 px-4 py-4 text-xs text-white/70">
            <p>© {{ date('Y') }} {{ site('name') }}. {{ __('footer.rights') }}</p>
            <ul class="flex flex-wrap gap-x-4">
                @foreach ($linkSet([['cgv', 'nav.cgv'], ['confidentialite', 'nav.privacy'], ['mentions-legales', 'nav.legal']]) as [$url, $text])
                    <li><a href="{{ $url }}" wire:navigate class="inline-block py-1 hover:text-white hover:underline">{{ $text }}</a></li>
                @endforeach
            </ul>
        </div>
    </div>
</footer>
