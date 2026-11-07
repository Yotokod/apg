@php
    /** @var \App\Models\Certificate $certificate */
    $org = site('org.legal_name') ?: site('name');
    $title = site('cert.title') ?: __('certificate.title');
    $revoked = ! $specimen && $certificate->isRevoked();
    $course = (string) $certificate->tr('course_title');
    $date = $certificate->issued_at->locale(app()->getLocale())->isoFormat('LL');
    $hours = $certificate->hours ? trans_choice('certificate.hours', $certificate->hours, ['n' => $certificate->hours]) : null;
    $text = strtr(site('cert.text') ?: __('certificate.default_text'), [
        ':name' => $certificate->student_name, ':course' => $course, ':date' => $date, ':hours' => (string) $certificate->hours,
    ]);
    $director = site('org.director_name');
    $directorTitle = site('org.director_title') ?: __('certificate.director_default_title');
    $signature = $revoked ? null : site('org.signature');
    $stamp = $revoked ? null : site('org.stamp');
    $logo = site('logo');
    $url = $specimen ? url('/certificat/'.$certificate->code) : $certificate->url();
    $status = $specimen ? 'specimen' : ($revoked ? 'revoked' : 'valid');
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ __('certificate.meta_title', ['number' => $certificate->number]) }} | {{ site('name') }}</title>
    <link rel="icon" href="{{ asset('images/favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600;0,700;1,500;1,600&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        :root { --brand: #D9480F; --brand-soft: #fff4ec; --ink: #0B0B2B; --ink-2: #1a1a44; --paper: #fffdf9; --muted: #57534e; --ok: #047857; --bad: #b91c1c; }
        * { box-sizing: border-box; }
        html, body { margin: 0; }
        body { font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif; color: var(--ink); background: radial-gradient(60rem 30rem at 100% -10%, rgb(217 72 15 / .18), transparent), linear-gradient(160deg, #0b0b2b, #1a1a44 60%, #2a1a2b); min-height: 100vh; }
        a { color: inherit; }
        :focus-visible { outline: 2px solid var(--brand); outline-offset: 2px; }
        .wrap { width: min(1120px, 100% - 32px); margin: 0 auto; padding: 16px 0 40px; }

        .toolbar { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; justify-content: space-between; margin-bottom: 12px; }
        .toolbar .actions { display: flex; flex-wrap: wrap; gap: 8px; }
        .btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; min-height: 44px; padding: 8px 16px; border-radius: 12px; border: 1px solid rgb(255 255 255 / .25); background: rgb(255 255 255 / .08); color: #fff; font: 600 14px/1.2 inherit; font-family: inherit; text-decoration: none; cursor: pointer; transition: background .2s, border-color .2s; }
        .btn:hover { background: rgb(255 255 255 / .16); }
        .btn-primary { background: var(--brand); border-color: var(--brand); }
        .btn-primary:hover { background: #b93a0a; }

        .panel { display: grid; gap: 12px; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); align-items: center; background: #fff; border-radius: 18px; padding: 14px 18px; margin-bottom: 14px; box-shadow: 0 10px 30px rgb(0 0 0 / .25); }
        .panel dt { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--muted); }
        .panel dd { margin: 2px 0 0; font-weight: 700; font-size: 15px; overflow-wrap: anywhere; }
        .pill { display: inline-flex; align-items: center; gap: 6px; padding: 4px 12px; border-radius: 999px; font-weight: 800; font-size: 13px; }
        .pill-valid { background: #d1fae5; color: var(--ok); }
        .pill-revoked { background: #fee2e2; color: var(--bad); }
        .pill-specimen { background: #fef3c7; color: #92400e; }
        .banner { display: flex; gap: 10px; align-items: flex-start; border-radius: 14px; padding: 12px 16px; margin-bottom: 14px; font-weight: 700; }
        .banner-revoked { background: #fee2e2; color: #7f1d1d; border: 2px solid var(--bad); }
        .banner-specimen { background: #fef3c7; color: #78350f; }
        .banner-valid { background: rgb(209 250 229 / .95); color: #064e3b; }

        /* ---- Sheet: A4 landscape, every size in cqw so it scales from 375px to print ---- */
        .sheet { container-type: inline-size; position: relative; width: 100%; aspect-ratio: 297 / 210; background: var(--paper); border-radius: 6px; overflow: hidden; box-shadow: 0 30px 60px rgb(0 0 0 / .4); color: var(--ink);
            background-image: radial-gradient(circle at 50% 46%, rgb(217 72 15 / .06), transparent 42%), repeating-radial-gradient(circle at 0 0, transparent 0 14px, rgb(11 11 43 / .018) 14px 15px), repeating-radial-gradient(circle at 100% 100%, transparent 0 14px, rgb(217 72 15 / .02) 14px 15px); }
        .frame { position: absolute; inset: 2.2cqw; border: .45cqw solid var(--ink); }
        .frame::before { content: ''; position: absolute; inset: .55cqw; border: .12cqw solid var(--brand); }
        .frame::after { content: ''; position: absolute; inset: 1.05cqw; border: .06cqw solid rgb(11 11 43 / .35); }
        .corner { position: absolute; width: 5.5cqw; height: 5.5cqw; color: var(--brand); }
        .corner svg { width: 100%; height: 100%; display: block; }
        .c-tl { top: 3cqw; left: 3cqw; } .c-tr { top: 3cqw; right: 3cqw; transform: scaleX(-1); }
        .c-bl { bottom: 3cqw; left: 3cqw; transform: scaleY(-1); } .c-br { bottom: 3cqw; right: 3cqw; transform: scale(-1); }
        .ribbon { position: absolute; top: 2.2cqw; left: 50%; transform: translateX(-50%); width: 34cqw; height: .45cqw; background: linear-gradient(90deg, transparent, var(--brand), transparent); }

        .content { position: absolute; inset: 5cqw 7cqw 7.2cqw; display: flex; flex-direction: column; align-items: center; text-align: center; }
        .brand { display: flex; align-items: center; gap: 1.2cqw; }
        .brand img { height: 5.6cqw; width: auto; object-fit: contain; }
        .brand .org { font-weight: 800; font-size: 1.25cqw; letter-spacing: .32em; text-transform: uppercase; color: var(--ink); text-align: left; }
        .brand .org small { display: block; font-weight: 600; letter-spacing: .12em; color: var(--brand); font-size: .95cqw; margin-top: .2cqw; }
        h1 { font-family: 'Cormorant Garamond', Georgia, serif; font-weight: 700; font-size: 5.2cqw; line-height: 1; margin: 2cqw 0 0; letter-spacing: .02em; color: var(--ink); }
        .divider { display: flex; align-items: center; gap: 1cqw; margin: .9cqw 0 .6cqw; color: var(--brand); }
        .divider span { width: 9cqw; height: .1cqw; background: currentColor; }
        .divider i { font-size: 1cqw; }
        .lead { font-size: 1.3cqw; color: var(--muted); letter-spacing: .04em; margin: 0; }
        .name { font-family: 'Cormorant Garamond', Georgia, serif; font-style: italic; font-weight: 600; font-size: 5.8cqw; line-height: 1.08; margin: .9cqw 0 0; color: var(--brand); padding: 0 2cqw .4cqw; border-bottom: .1cqw solid rgb(11 11 43 / .3); max-width: 80cqw; overflow-wrap: anywhere; }
        .name.is-long { font-size: 3.4cqw; }
        .course { font-family: 'Cormorant Garamond', Georgia, serif; font-weight: 700; font-size: 2.5cqw; line-height: 1.15; margin: .4cqw 0 0; max-width: 72cqw; }
        .text { font-size: 1.35cqw; line-height: 1.55; color: #44403c; max-width: 64cqw; margin: .9cqw 0 0; }
        .facts { display: flex; flex-wrap: wrap; justify-content: center; gap: .6cqw 2.4cqw; margin-top: 1.5cqw; font-size: 1.15cqw; font-weight: 600; color: var(--ink-2); }
        .facts i { color: var(--brand); margin-right: .4cqw; }

        .signs { margin-top: auto; width: 100%; display: grid; grid-template-columns: 1fr 13cqw 1fr; align-items: end; gap: 3cqw; }
        .sign { text-align: center; }
        .sign .zone { height: 7.4cqw; display: flex; align-items: flex-end; justify-content: center; position: relative; }
        .sign .zone img.signature { max-height: 6.6cqw; max-width: 22cqw; object-fit: contain; mix-blend-mode: multiply; }
        .sign .zone img.stamp { max-height: 8.4cqw; max-width: 14cqw; object-fit: contain; mix-blend-mode: multiply; transform: rotate(-9deg); opacity: .92; position: relative; bottom: -.6cqw; }
        .sign .line { border-top: .12cqw solid var(--ink); margin: .4cqw auto 0; width: 70%; }
        .sign .who { font-weight: 800; font-size: 1.15cqw; margin-top: .5cqw; }
        .sign .role { font-size: 1cqw; color: var(--muted); }
        .seal { width: 12cqw; height: 12cqw; justify-self: center; position: relative; }
        .seal svg { width: 100%; height: 100%; display: block; }
        .seal .s-text { position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; color: #fff; font-weight: 800; }
        .seal .s-text b { font-size: 1.9cqw; letter-spacing: .08em; }
        .seal .s-text span { font-size: .75cqw; letter-spacing: .2em; text-transform: uppercase; opacity: .9; }

        .foot { position: absolute; left: 7cqw; right: 7cqw; bottom: 3.6cqw; display: flex; justify-content: space-between; align-items: center; gap: 2cqw; font-size: .85cqw; color: var(--muted); }
        .foot strong { color: var(--ink); }
        .foot .status-valid { color: var(--ok); font-weight: 800; }
        .foot .status-revoked { color: var(--bad); font-weight: 800; }
        .foot .status-specimen { color: #92400e; font-weight: 800; }
        .foot .url { overflow-wrap: anywhere; text-align: right; }
        .footer-note { font-size: .8cqw; color: var(--muted); margin-top: .5cqw; }

        .watermark { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; pointer-events: none; z-index: 5; }
        .watermark span { transform: rotate(-24deg); font-weight: 800; font-size: 12cqw; letter-spacing: .1em; text-transform: uppercase; border: .8cqw solid currentColor; padding: 0 3cqw; border-radius: 2cqw; opacity: .16; }
        .watermark.revoked span { color: var(--bad); opacity: .28; }
        .watermark.specimen span { color: var(--ink); }

        .sr-only { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0 0 0 0); white-space: nowrap; }
        .live { color: #fff; font-size: 13px; min-height: 18px; margin-top: 8px; text-align: right; }

        @media (max-width: 640px) { .toolbar .actions { width: 100%; } .toolbar .actions .btn { flex: 1 1 auto; } }
        @media (prefers-reduced-motion: reduce) { * { transition: none !important; } }

        @page { size: A4 landscape; margin: 0; }
        @media print {
            html, body { background: #fff !important; width: 297mm; height: 210mm; }
            .no-print { display: none !important; }
            .wrap { width: 297mm; padding: 0; margin: 0; }
            .sheet { width: 297mm; height: 210mm; aspect-ratio: auto; border-radius: 0; box-shadow: none; page-break-after: avoid; break-inside: avoid; }
            * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>
<main class="wrap">
    <div class="toolbar no-print">
        <a href="{{ url('/') }}" class="btn"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> {{ __('certificate.back') }}</a>
        <div class="actions">
            <button type="button" class="btn" data-copy="{{ $url }}" data-ok="{{ __('certificate.copied') }}" data-fail="{{ __('certificate.copy_failed') }}"><i class="fa-solid fa-link" aria-hidden="true"></i> {{ __('certificate.copy') }}</button>
            <button type="button" class="btn btn-primary" onclick="window.print()"><i class="fa-solid fa-print" aria-hidden="true"></i> {{ __('certificate.print') }}</button>
        </div>
    </div>
    <p class="live no-print" role="status" aria-live="polite" data-live></p>

    @if ($revoked)
        <div class="banner banner-revoked no-print" role="alert"><i class="fa-solid fa-ban" aria-hidden="true"></i><span>{{ __('certificate.revoked_banner') }} {{ __('certificate.revoked_on', ['date' => $certificate->revoked_at->locale(app()->getLocale())->isoFormat('LL')]) }}</span></div>
    @elseif ($specimen)
        <div class="banner banner-specimen no-print"><i class="fa-solid fa-eye" aria-hidden="true"></i><span>{{ __('certificate.specimen_banner') }}</span></div>
    @else
        <div class="banner banner-valid no-print"><i class="fa-solid fa-circle-check" aria-hidden="true"></i><span>{{ __('certificate.valid_banner', ['org' => $org]) }}</span></div>
    @endif

    <section class="panel no-print" aria-label="{{ __('certificate.verification') }}">
        <dl style="margin:0"><dt>{{ __('certificate.status') }}</dt><dd><span class="pill pill-{{ $status }}"><i class="fa-solid {{ $status === 'valid' ? 'fa-shield-halved' : ($status === 'revoked' ? 'fa-ban' : 'fa-eye') }}" aria-hidden="true"></i> {{ __('certificate.'.$status) }}</span></dd></dl>
        <dl style="margin:0"><dt>{{ __('certificate.student') }}</dt><dd>{{ $certificate->student_name }}</dd></dl>
        <dl style="margin:0"><dt>{{ __('certificate.course') }}</dt><dd>{{ $course }}</dd></dl>
        <dl style="margin:0"><dt>{{ __('certificate.issued') }}</dt><dd>{{ $date }}</dd></dl>
        <dl style="margin:0"><dt>{{ __('certificate.number') }}</dt><dd>{{ $certificate->number }}</dd></dl>
    </section>

    <article class="sheet" aria-label="{{ $title }}">
        <div class="frame" aria-hidden="true"></div>
        <div class="ribbon" aria-hidden="true"></div>
        @foreach (['tl', 'tr', 'bl', 'br'] as $c)
            <div class="corner c-{{ $c }}" aria-hidden="true">
                <svg viewBox="0 0 100 100" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round">
                    <path d="M6 60V6h54" stroke="#0B0B2B" stroke-width="3"/>
                    <path d="M14 44V14h30"/>
                    <path d="M14 14c14 2 22 10 24 24M14 14c2 14 10 22 24 24" />
                    <circle cx="14" cy="14" r="4" fill="currentColor" stroke="none"/>
                    <circle cx="60" cy="6" r="2.6" fill="#0B0B2B" stroke="none"/><circle cx="6" cy="60" r="2.6" fill="#0B0B2B" stroke="none"/>
                </svg>
            </div>
        @endforeach

        @if ($status !== 'valid')
            <div class="watermark {{ $status }}" aria-hidden="true"><span>{{ __('certificate.'.$status) }}</span></div>
        @endif

        <div class="content">
            <div class="brand">
                @if ($logo)<img src="{{ $logo }}" alt="{{ __('certificate.logo_alt', ['org' => $org]) }}">@endif
                <div class="org">{{ $org }}<small>{{ site('tagline') }}</small></div>
            </div>

            <h1>{{ $title }}</h1>
            <div class="divider" aria-hidden="true"><span></span><i class="fa-solid fa-feather-pointed"></i><span></span></div>

            <p class="lead">{{ __('certificate.awarded_to') }}</p>
            <p class="name {{ mb_strlen($certificate->student_name) > 34 ? 'is-long' : '' }}">{{ $certificate->student_name }}</p>
            <p class="lead" style="margin-top:.9cqw">{{ __('certificate.for_course') }}</p>
            <p class="course">{{ $course }}</p>
            <p class="text">{{ $text }}</p>

            <div class="facts">
                <span><i class="fa-regular fa-calendar-check" aria-hidden="true"></i>{{ __('certificate.completed_on') }} {{ $date }}</span>
                @if ($hours)<span><i class="fa-regular fa-clock" aria-hidden="true"></i>{{ $hours }}</span>@endif
                @if ($certificate->score)<span><i class="fa-solid fa-star" aria-hidden="true"></i>{{ __('certificate.score', ['n' => $certificate->score]) }}</span>@endif
            </div>

            <div class="signs">
                <div class="sign">
                    <div class="zone">
                        @if ($signature)<img class="signature" src="{{ $signature }}" alt="{{ __('certificate.signature_alt', ['name' => $director ?: $directorTitle]) }}">@endif
                    </div>
                    <div class="line"></div>
                    @if ($director)<div class="who">{{ $director }}</div>@endif
                    <div class="role">{{ $directorTitle }}</div>
                </div>

                <div class="seal" aria-hidden="true">
                    <svg viewBox="0 0 120 120">
                        <g fill="#D9480F">
                            @for ($k = 0; $k < 24; $k++)
                                <path d="M60 2l5 12h-10z" transform="rotate({{ $k * 15 }} 60 60)"/>
                            @endfor
                        </g>
                        <circle cx="60" cy="60" r="46" fill="#0B0B2B"/>
                        <circle cx="60" cy="60" r="41" fill="none" stroke="#D9480F" stroke-width="1.6"/>
                        <circle cx="60" cy="60" r="36" fill="none" stroke="#fff" stroke-opacity=".35" stroke-dasharray="2 3"/>
                    </svg>
                    <div class="s-text"><b>{{ site('short_name') ?: 'APG' }}</b><span>{{ $certificate->issued_at->year }}</span></div>
                </div>

                <div class="sign">
                    <div class="zone">
                        @if ($stamp)<img class="stamp" src="{{ $stamp }}" alt="{{ __('certificate.stamp_alt', ['org' => $org]) }}">@endif
                    </div>
                    <div class="line"></div>
                    <div class="who">{{ $org }}</div>
                    <div class="role">{{ __('certificate.stamp') }}</div>
                </div>
            </div>
            @if (site('cert.footer'))<p class="footer-note">{{ site('cert.footer') }}</p>@endif
        </div>

        <div class="foot">
            <span>{{ __('certificate.number') }} <strong>{{ $certificate->number }}</strong> · {{ __('certificate.status') }} <span class="status-{{ $status }}">{{ __('certificate.'.$status) }}</span></span>
            <span class="url">{{ __('certificate.verify_at') }} : <strong>{{ $url }}</strong> · {{ __('certificate.short_code') }} <strong>{{ $certificate->shortCode() }}</strong></span>
        </div>
    </article>
</main>
<script>
    document.querySelectorAll('[data-copy]').forEach((btn) => btn.addEventListener('click', async () => {
        const live = document.querySelector('[data-live]');
        try { await navigator.clipboard.writeText(btn.dataset.copy); live.textContent = btn.dataset.ok; }
        catch (e) { live.textContent = btn.dataset.fail + ' ' + btn.dataset.copy; }
    }));
</script>
</body>
</html>
