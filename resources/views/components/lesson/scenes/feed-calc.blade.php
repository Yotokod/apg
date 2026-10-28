@props(['label' => null])
{{-- Feed formula example from docs/SIMULATOR-SPEC.md: 500 x 25 g = 12.5 kg/day, 87.5 kg/week, 2 bags of 50 kg. --}}
@php $dec = fn (float $v) => number_format($v, 1, app()->getLocale() === 'fr' ? ',' : '.', ''); @endphp
<svg viewBox="0 0 400 300" xmlns="http://www.w3.org/2000/svg" {{ $attributes->class('ill')->merge($label ? ['role' => 'img', 'aria-label' => $label] : ['aria-hidden' => 'true']) }}>
    <circle cx="200" cy="150" r="130" fill="var(--i-cream)"/>
    <g class="o-b ill-a-sway" style="--t: 4s">
        <path d="M40 98 Q36 84 52 82 H118 Q134 84 130 98 L138 236 Q138 248 124 248 H46 Q32 248 32 236Z" fill="var(--i-amber)"/>
        <path d="M44 82 L56 66 H114 L126 82Z" fill="var(--i-or-d)"/>
        <rect x="52" y="134" width="66" height="44" rx="8" fill="#fff"/>
        <text x="85" y="164" text-anchor="middle" font-size="20" font-weight="800" fill="var(--i-ink-d)">50 kg</text>
    </g>
    @foreach ([150, 166, 182] as $i => $y)
        <circle class="ill-a-fall" style="--d: {{ $i * .45 }}s; --t: 1.6s" cx="{{ 146 + $i * 6 }}" cy="{{ $y }}" r="4" fill="var(--i-amber)"/>
    @endforeach
    <path class="lsn-a-in" d="M150 150 H176 M168 140 L178 150 L168 160" stroke="var(--i-ink)" stroke-width="5" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
    <g font-weight="800" fill="var(--i-ink-d)">
        <text class="lsn-a-in" style="--d: .2s" x="190" y="92" font-size="22">500 × 25 g</text>
        <text class="lsn-a-in" style="--d: .9s" x="190" y="130" font-size="20" fill="var(--i-or-d)">= {{ __('lesson.scene.per_day', ['kg' => $dec(500 * 25 / 1000)]) }}</text>
        <text class="lsn-a-in" style="--d: 1.6s" x="190" y="168" font-size="20">{{ __('lesson.scene.week_total', ['kg' => $dec(500 * 25 * 7 / 1000)]) }}</text>
    </g>
    <g class="lsn-a-pop" style="--d: 2.3s">
        <rect x="186" y="186" width="182" height="44" rx="22" fill="var(--i-green)"/>
        <text x="277" y="214" text-anchor="middle" font-size="17" font-weight="800" fill="#fff">{{ __('lesson.scene.bags', ['n' => 2, 'size' => 50]) }}</text>
    </g>
    <x-illustrations.chicken x="300" y="236" s=".42" :peck="true"/>
    <x-illustrations.chicken x="250" y="240" s=".36" :peck="true" :delay="1" body="#fff" wing="#f3dcc6"/>
</svg>
