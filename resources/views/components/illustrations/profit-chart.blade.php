@props(['label' => null])
@php
    $text = $label ?? trim(strip_tags((string) $slot));
    $bars = [60, 96, 82, 134, 176];
@endphp
<svg viewBox="0 0 400 300" xmlns="http://www.w3.org/2000/svg" {{ $attributes->class('ill')->merge($text !== '' ? ['role' => 'img', 'aria-label' => $text] : ['aria-hidden' => 'true']) }}>
    <rect x="30" y="30" width="340" height="240" rx="28" fill="var(--i-cream)"/>
    <path d="M62 80 H340 M62 130 H340 M62 180 H340" stroke="var(--i-peach)" stroke-width="3" stroke-dasharray="2 8" stroke-linecap="round"/>
    <path d="M62 236 H340" stroke="var(--i-ink)" stroke-width="4" stroke-linecap="round" opacity=".5"/>
    @foreach ($bars as $i => $h)
        <rect class="o-b ill-a-grow" style="--d: {{ $i * .22 }}s; --t: 4.5s" x="{{ 74 + $i * 52 }}" y="{{ 236 - $h }}" width="34" height="{{ $h }}" rx="8" fill="{{ $i === 4 ? 'var(--i-or)' : ($i % 2 ? 'var(--i-amber)' : 'var(--i-peach)') }}"/>
    @endforeach
    <path class="ill-a-pulse" style="--t: 3s" d="M290 70 l30 -22 M320 48 h-18 M320 48 v18" stroke="var(--i-green)" stroke-width="6" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
    <g class="ill-a-float" style="--t: 3s">
        <g class="ill-a-flip" style="--t: 3s">
            <circle cx="92" cy="86" r="26" fill="var(--i-amber)"/>
            <circle cx="92" cy="86" r="19" fill="none" stroke="var(--i-or)" stroke-width="3"/>
            <path d="M86 98 V74 H100 M86 86 H97" stroke="var(--i-or-d)" stroke-width="5" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
        </g>
    </g>
    <path class="ill-a-twinkle" style="--d: .6s" d="M142 62 l3 8 8 3 -8 3 -3 8 -3 -8 -8 -3 8 -3Z" fill="var(--i-yellow)"/>
</svg>
