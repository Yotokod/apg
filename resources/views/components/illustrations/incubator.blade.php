@props(['label' => null])
@php $text = $label ?? trim(strip_tags((string) $slot)); @endphp
<svg viewBox="0 0 400 300" xmlns="http://www.w3.org/2000/svg" {{ $attributes->class('ill')->merge($text !== '' ? ['role' => 'img', 'aria-label' => $text] : ['aria-hidden' => 'true']) }}>
    <circle cx="200" cy="150" r="128" fill="var(--i-cream)"/>
    <rect x="96" y="56" width="208" height="206" rx="26" fill="var(--i-ink)"/>
    <rect x="112" y="150" width="176" height="96" rx="14" fill="var(--i-peach)"/>
    <path d="M112 198 H288" stroke="var(--i-ink)" stroke-width="4" opacity=".25"/>
    @foreach ([0, 1] as $r)
        @foreach ([0, 1, 2, 3] as $c)
            <ellipse class="o-b ill-a-sway" style="--d: {{ ($r + $c) * .3 }}s; --t: 2.4s" cx="{{ 140 + $c * 40 }}" cy="{{ 176 + $r * 46 }}" rx="13" ry="17" fill="{{ ($r + $c) % 2 ? 'var(--i-shell)' : 'var(--i-yellow)' }}"/>
        @endforeach
    @endforeach
    <circle cx="154" cy="102" r="30" fill="var(--i-cream)"/>
    <path d="M130 102 a24 24 0 0 1 48 0" stroke="var(--i-or)" stroke-width="6" fill="none" stroke-linecap="round" opacity=".5"/>
    <path class="o-b ill-a-needle" style="--t: 4s" d="M154 102 L154 80" stroke="var(--i-or-d)" stroke-width="4" stroke-linecap="round"/>
    <circle cx="154" cy="102" r="5" fill="var(--i-ink)"/>
    <rect x="200" y="78" width="84" height="48" rx="10" fill="var(--i-ink-d)"/>
    <rect x="210" y="90" width="44" height="7" rx="3.5" fill="var(--i-amber)"/><rect x="210" y="106" width="30" height="7" rx="3.5" fill="var(--i-green-l)"/>
    <circle class="ill-a-pulse" style="--t: 1.6s" cx="268" cy="94" r="5" fill="var(--i-red)"/>
    <path class="ill-a-rise" style="--t: 2.4s" d="M322 190 q8 14 0 22 q-8 -8 0 -22Z" fill="#60a5fa"/>
    <path class="ill-a-rise" style="--d: 1.2s; --t: 2.4s" d="M338 204 q8 14 0 22 q-8 -8 0 -22Z" fill="#60a5fa"/>
    @foreach ([70, 82, 94] as $i => $x)
        <path class="ill-a-rise" style="--d: {{ $i * .6 }}s; --t: 2.4s" d="M{{ $x }} 214 q-8 -10 0 -20 q8 -10 0 -20" stroke="var(--i-or)" stroke-width="4" stroke-linecap="round" fill="none"/>
    @endforeach
    <rect x="122" y="256" width="20" height="16" rx="4" fill="var(--i-ink)"/><rect x="258" y="256" width="20" height="16" rx="4" fill="var(--i-ink)"/>
</svg>
