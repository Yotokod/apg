@props(['label' => null])
@php $text = $label ?? trim(strip_tags((string) $slot)); @endphp
<svg viewBox="0 0 400 300" xmlns="http://www.w3.org/2000/svg" {{ $attributes->class('ill')->merge($text !== '' ? ['role' => 'img', 'aria-label' => $text] : ['aria-hidden' => 'true']) }}>
    <circle cx="200" cy="150" r="126" fill="var(--i-cream)"/>
    <g class="ill-a-pulse" style="--t: 3s">
        <path d="M250 70 L324 92 V160 C324 208 292 238 250 256 C208 238 176 208 176 160 V92Z" fill="var(--i-green)"/>
        <path d="M250 70 L324 92 V160 C324 208 292 238 250 256Z" fill="#000" opacity=".1"/>
        <path d="M216 158 L242 184 L286 132" stroke="#fff" stroke-width="14" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
    </g>
    <g transform="translate(70 190) rotate(-28)">
        <g class="ill-a-inject" style="--t: 3.2s">
            <rect x="52" y="-12" width="14" height="24" rx="4" fill="var(--i-ink)"/>
            <rect x="64" y="-5" width="30" height="10" rx="3" fill="var(--i-ink-d)"/>
            <rect x="-6" y="-16" width="62" height="32" rx="8" fill="#fff"/>
            <rect x="0" y="-10" width="38" height="20" rx="5" fill="var(--i-or)"/>
            <path d="M12 -16 v8 M24 -16 v6 M36 -16 v8" stroke="var(--i-ink)" stroke-width="2.5" stroke-linecap="round"/>
            <rect x="-14" y="-24" width="8" height="48" rx="4" fill="var(--i-ink)"/>
            <path d="M94 0 H134" stroke="var(--i-ink-d)" stroke-width="3" stroke-linecap="round"/>
        </g>
    </g>
    <path class="ill-a-fall" style="--t: 3.2s" d="M190 138 q7 11 0 18 q-7 -7 0 -18Z" fill="var(--i-or)"/>
    <path class="ill-a-twinkle" style="--d: 1s" d="M330 64 l4 10 10 4 -10 4 -4 10 -4 -10 -10 -4 10 -4Z" fill="var(--i-amber)"/>
    <path class="ill-a-twinkle" style="--d: 2s" d="M110 90 l3 8 8 3 -8 3 -3 8 -3 -8 -8 -3 8 -3Z" fill="var(--i-green)"/>
</svg>
