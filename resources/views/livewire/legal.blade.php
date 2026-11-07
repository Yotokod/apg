<div>
    <x-page-hero :title="$page['title']" :subtitle="$page['subtitle']" icon="fa-scale-balanced" />
    <x-pages.breadcrumb :items="[[$page['title'], null]]" />

    <div @class(['mx-auto grid max-w-7xl gap-6 px-4 py-6', 'lg:grid-cols-[16rem_1fr]' => ! $custom])>
        @unless ($custom)
            <nav aria-label="{{ __('pages.common.toc') }}" class="lg:sticky lg:top-24 lg:self-start">
                <div class="card p-3">
                    <p class="px-2 pb-1 text-xs font-bold uppercase tracking-wide text-stone-600 dark:text-stone-300">{{ __('pages.common.toc') }}</p>
                    <ol class="max-h-72 space-y-0.5 overflow-y-auto">
                        @foreach ($sections as $i => $s)
                            <li><a href="#s{{ $i + 1 }}" class="flex min-h-11 items-center gap-2 rounded-lg px-2 text-sm hover:bg-stone-100 dark:hover:bg-stone-800"><span class="font-bold text-brand-700 dark:text-brand-500">{{ $i + 1 }}.</span><span class="truncate">{{ $s['title'] }}</span></a></li>
                        @endforeach
                    </ol>
                </div>
            </nav>
        @endunless

        <article class="card min-w-0 space-y-5 p-5 sm:p-6">
            @if ($custom)
                <div class="prose max-w-none text-sm dark:prose-invert [&_a]:underline [&_h2]:mt-5 [&_h2]:text-lg [&_h2]:font-extrabold [&_h3]:mt-4 [&_h3]:font-bold [&_li]:ml-5 [&_ol]:list-decimal [&_p]:mt-2 [&_ul]:list-disc" data-legal-custom>{!! $custom !!}</div>
            @else
                <p class="flex items-start gap-2 rounded-xl border border-amber-300 bg-amber-50 p-3 text-sm text-amber-900 dark:border-amber-700 dark:bg-amber-950/40 dark:text-amber-200" role="note" data-legal-draft>
                    <i class="fa-solid fa-triangle-exclamation mt-0.5" aria-hidden="true"></i><span>{{ __('identity.legal.draft_notice') }}</span>
                </p>
                <p class="text-sm text-stone-600 dark:text-stone-300"><i class="fa-regular fa-calendar mr-1" aria-hidden="true"></i>{{ __('pages.common.updated', ['date' => $updated]) }}</p>
                @foreach ($sections as $i => $s)
                    <section id="s{{ $i + 1 }}" class="scroll-mt-24">
                        <h2 class="text-lg font-extrabold"><span class="text-brand-700 dark:text-brand-500">{{ $i + 1 }}.</span> {{ $s['title'] }}</h2>
                        <p class="prose mt-1 text-sm">{{ $s['body'] }}</p>
                    </section>
                @endforeach
            @endif
            <p class="border-t border-stone-200 pt-4 text-sm dark:border-stone-800"><i class="fa-solid fa-envelope mr-1 text-brand-700 dark:text-brand-500" aria-hidden="true"></i><a href="mailto:{{ site('email') }}" class="font-semibold underline">{{ site('email') }}</a></p>
        </article>
    </div>
</div>
