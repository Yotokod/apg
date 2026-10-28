@props(['label' => null])
{{-- Biosecurity: shield over the house, rodents and wild birds kept out. --}}
<svg viewBox="0 0 400 300" xmlns="http://www.w3.org/2000/svg" {{ $attributes->class('ill')->merge($label ? ['role' => 'img', 'aria-label' => $label] : ['aria-hidden' => 'true']) }}>
    <circle cx="200" cy="150" r="130" fill="var(--i-cream)"/>
    <path d="M30 252 Q200 232 370 252 V272 H30Z" fill="var(--i-green-l)"/>
    <g class="lsn-a-pop" style="--d: .1s">
        <path d="M200 40 L290 74 V146 Q290 216 200 252 Q110 216 110 146 V74Z" fill="var(--i-green)"/>
        <path d="M200 58 L272 86 V146 Q272 202 200 232 Q128 202 128 146 V86Z" fill="#fff" opacity=".2"/>
    </g>
    <g class="lsn-a-pop" style="--d: .6s">
        <path d="M152 150 L200 112 L248 150 V204 H152Z" fill="#fff"/>
        <path d="M144 154 L200 108 L256 154" stroke="var(--i-or-d)" stroke-width="10" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
        <rect x="188" y="170" width="24" height="34" rx="4" fill="var(--i-or)"/>
    </g>
    <circle class="ill-a-pulse" style="--t: 2.2s" cx="276" cy="78" r="22" fill="#fff"/>
    <path d="M265 78 L273 87 L289 70" stroke="var(--i-green)" stroke-width="6" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
    <g class="ill-a-float lsn-a-in" style="--d: 1.2s; --t: 3.4s">
        <ellipse cx="62" cy="214" rx="24" ry="14" fill="#8d8aa6"/><circle cx="84" cy="206" r="9" fill="#8d8aa6"/>
        <path d="M38 216 Q22 222 26 236" stroke="#8d8aa6" stroke-width="4" fill="none" stroke-linecap="round"/>
        <circle cx="62" cy="212" r="34" fill="none" stroke="var(--i-red)" stroke-width="6"/><path d="M38 188 L86 236" stroke="var(--i-red)" stroke-width="6"/>
    </g>
    <g class="ill-a-float lsn-a-in" style="--d: 1.7s; --t: 3s">
        <path d="M314 96 Q330 80 346 96 Q330 90 314 96Z M330 96 Q346 76 362 92" fill="var(--i-ink)" stroke="var(--i-ink)" stroke-width="4" stroke-linejoin="round"/>
        <circle cx="338" cy="92" r="34" fill="none" stroke="var(--i-red)" stroke-width="6"/><path d="M314 68 L362 116" stroke="var(--i-red)" stroke-width="6"/>
    </g>
</svg>
