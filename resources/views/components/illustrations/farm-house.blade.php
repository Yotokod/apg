@props(['label' => null])
@php $text = $label ?? trim(strip_tags((string) $slot)); @endphp
<svg viewBox="0 0 400 300" xmlns="http://www.w3.org/2000/svg" {{ $attributes->class('ill')->merge($text !== '' ? ['role' => 'img', 'aria-label' => $text] : ['aria-hidden' => 'true']) }}>
    <circle cx="200" cy="140" r="130" fill="var(--i-cream)"/>
    <circle class="ill-a-pulse" style="--t: 4s" cx="318" cy="70" r="26" fill="var(--i-amber)"/>
    <path d="M10 258 Q200 232 390 258 V282 H10Z" fill="var(--i-green-l)"/>
    <rect x="57" y="222" width="6" height="38" fill="var(--i-ink)"/>
    <circle class="o-b ill-a-sway" style="--t: 4s" cx="60" cy="204" r="26" fill="var(--i-green)"/>
    <rect x="86" y="130" width="228" height="128" rx="6" fill="#fff"/>
    <rect x="86" y="130" width="228" height="128" rx="6" fill="var(--i-peach)" opacity=".5"/>
    <path d="M70 134 L200 62 L330 134Z" fill="var(--i-or-d)"/>
    <path d="M70 134 L200 62 L330 134 L318 134 L200 74 L82 134Z" fill="var(--i-or)"/>
    <circle cx="200" cy="108" r="22" fill="var(--i-ink)"/>
    <g class="ill-a-spin" style="--t: 1.4s">
        <path d="M200 108 C194 92 206 88 208 98 Z M200 108 C216 102 220 114 210 116 Z M200 108 C206 124 194 128 192 118 Z M200 108 C184 114 180 102 190 100 Z" fill="var(--i-cream)"/>
    </g>
    <circle cx="200" cy="108" r="4" fill="var(--i-or)"/>
    <rect x="104" y="170" width="64" height="40" rx="6" fill="var(--i-ink)"/>
    <rect x="110" y="176" width="52" height="28" rx="3" fill="var(--i-yellow)" opacity=".85"/>
    <rect x="232" y="170" width="64" height="40" rx="6" fill="var(--i-ink)"/>
    <rect x="238" y="176" width="52" height="28" rx="3" fill="var(--i-yellow)" opacity=".85"/>
    <rect x="178" y="198" width="44" height="60" rx="6" fill="var(--i-ink)"/>
    <circle cx="214" cy="230" r="2.5" fill="var(--i-amber)"/>
    <x-illustrations.chicken x="318" y="206" s=".5" :peck="true"/>
    <x-illustrations.chicken x="22" y="222" s=".4" :peck="true" :delay="1.2" body="#ffb347" wing="#f97316"/>
</svg>
