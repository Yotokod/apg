@props(['label' => null])
@php $text = $label ?? trim(strip_tags((string) $slot)); @endphp
<svg viewBox="0 0 640 480" xmlns="http://www.w3.org/2000/svg" {{ $attributes->class('ill')->merge($text !== '' ? ['role' => 'img', 'aria-label' => $text] : ['aria-hidden' => 'true']) }}>
    <defs>
        <linearGradient id="hf-sky" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#ffe3d0"/><stop offset="1" stop-color="#fff4ec"/></linearGradient>
        <linearGradient id="hf-sun" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#ffd166"/><stop offset="1" stop-color="#f97316"/></linearGradient>
        <linearGradient id="hf-body" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#f97316"/><stop offset="1" stop-color="#b93a0a"/></linearGradient>
    </defs>
    <rect width="640" height="480" rx="40" fill="url(#hf-sky)"/>
    <g class="ill-a-spin" style="--t: 60s" opacity=".5">
        @foreach (range(0, 11) as $i)
            <path d="M150 52 v-14" stroke="#ffb347" stroke-width="6" stroke-linecap="round" transform="rotate({{ $i * 30 }} 150 130)"/>
        @endforeach
    </g>
    <circle class="ill-a-pulse" style="--t: 5s" cx="150" cy="130" r="46" fill="url(#hf-sun)"/>
    <g class="ill-a-float" style="--t: 9s"><ellipse cx="470" cy="96" rx="52" ry="16" fill="#fff"/><ellipse cx="496" cy="84" rx="30" ry="16" fill="#fff"/></g>
    <g class="ill-a-float" style="--d: 3s; --t: 11s" opacity=".8"><ellipse cx="300" cy="64" rx="36" ry="11" fill="#fff"/><ellipse cx="318" cy="56" rx="20" ry="11" fill="#fff"/></g>
    <path d="M0 330 Q120 270 260 320 T520 300 T640 320 V480 H0Z" fill="#a7d98c"/>
    <path d="M0 380 Q160 330 320 372 T640 360 V480 H0Z" fill="#7cc576"/>
    <path d="M0 430 Q200 396 400 424 T640 416 V480 H0Z" fill="#4fa65f"/>
    <g fill="#fff" opacity=".9"><path d="M60 410 v-28 M84 414 v-28 M108 416 v-28 M52 394 H116 M52 406 H116" stroke="#fff" stroke-width="6" stroke-linecap="round"/></g>
    {{-- rooster --}}
    <g class="o-b ill-a-pulse" style="--t: 4s">
        <path d="M236 480 C236 380 282 336 346 326 C410 320 466 372 470 480Z" fill="url(#hf-body)"/>
        <path d="M262 480 C264 420 296 384 346 376 C396 372 438 410 442 480Z" fill="#d9480f" opacity=".7"/>
        @foreach ([0, 1, 2, 3] as $i)
            <path d="M{{ 276 + $i * 40 }} 404 q20 22 40 0 q-4 30 -20 38 q-18 -8 -20 -38Z" fill="#ffb347" opacity=".85"/>
        @endforeach
        <path d="M278 296 C262 270 270 230 300 214 C292 244 300 270 318 290Z" fill="#b93a0a"/>
        <path d="M300 214 C282 190 292 160 318 150 C316 176 322 196 336 206Z" fill="#1a1a44"/>
        <circle cx="352" cy="252" r="84" fill="#f97316"/>
        <ellipse cx="380" cy="270" rx="52" ry="56" fill="#ffe3d0"/>
        <path class="o-b ill-a-sway" style="--t: 3s" d="M300 190 a24 24 0 1 1 20 -34 a28 28 0 1 1 40 -10 a24 24 0 1 1 34 22 a18 18 0 1 1 6 28 Z" fill="#e5383b"/>
        <path d="M430 246 L494 270 L432 296Z" fill="#ffb347"/>
        <path d="M432 280 L470 274 L436 304Z" fill="#f59e0b"/>
        <ellipse class="ill-a-sway" style="--t: 2.6s" cx="436" cy="326" rx="15" ry="28" fill="#e5383b"/>
        <circle cx="402" cy="240" r="11" fill="#0b0b2b"/><circle cx="406" cy="236" r="3.6" fill="#fff"/>
        <path d="M382 220 q18 -12 36 -2" stroke="#b93a0a" stroke-width="5" stroke-linecap="round" fill="none"/>
    </g>
    @foreach ([[80, 250, 26, 0], [560, 230, 20, 1.5], [540, 340, 28, 3], [180, 330, 18, 4.5]] as [$x, $y, $r, $d])
        <g class="ill-a-float" style="--d: {{ $d }}s; --t: {{ 5 + $d / 2 }}s">
            <ellipse cx="{{ $x }}" cy="{{ $y }}" rx="{{ $r }}" ry="{{ $r * 1.28 }}" fill="#fffaf3"/>
            <ellipse cx="{{ $x - $r * .3 }}" cy="{{ $y - $r * .4 }}" rx="{{ $r * .25 }}" ry="{{ $r * .4 }}" fill="#fff" opacity=".8"/>
            <circle cx="{{ $x + $r * .3 }}" cy="{{ $y + $r * .5 }}" r="{{ $r * .16 }}" fill="#f97316" opacity=".4"/>
        </g>
    @endforeach
    <path class="ill-a-twinkle" style="--d: .5s" d="M560 150 l5 12 12 5 -12 5 -5 12 -5 -12 -12 -5 12 -5Z" fill="#ffb347"/>
    <path class="ill-a-twinkle" style="--d: 1.8s" d="M60 190 l4 10 10 4 -10 4 -4 10 -4 -10 -10 -4 10 -4Z" fill="#f97316"/>
</svg>
