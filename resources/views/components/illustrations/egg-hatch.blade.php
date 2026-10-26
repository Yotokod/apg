@props(['label' => null])
@php $text = $label ?? trim(strip_tags((string) $slot)); @endphp
<svg viewBox="0 0 400 300" xmlns="http://www.w3.org/2000/svg" {{ $attributes->class('ill')->merge($text !== '' ? ['role' => 'img', 'aria-label' => $text] : ['aria-hidden' => 'true']) }}>
    <circle cx="200" cy="150" r="124" fill="var(--i-cream)"/>
    <circle cx="200" cy="150" r="94" fill="var(--i-peach)" opacity=".55"/>
    <ellipse cx="200" cy="262" rx="88" ry="12" fill="var(--i-ink)" opacity=".12"/>
    <g class="o-b ill-a-wobble" style="--t: 5s">
        <g class="ill-a-peek rest-peek" style="--t: 5s">
            <circle cx="200" cy="168" r="40" fill="var(--i-yellow)"/>
            <path d="M184 132 q6 -14 10 0 M198 128 q6 -14 10 0" stroke="var(--i-amber)" stroke-width="5" stroke-linecap="round" fill="none"/>
            <circle cx="186" cy="164" r="5" fill="var(--i-ink-d)"/><circle cx="214" cy="164" r="5" fill="var(--i-ink-d)"/>
            <circle cx="187.5" cy="162.5" r="1.6" fill="#fff"/><circle cx="215.5" cy="162.5" r="1.6" fill="#fff"/>
            <path d="M193 175 L207 175 L200 187Z" fill="var(--i-or)"/>
            <circle cx="172" cy="178" r="6" fill="var(--i-or)" opacity=".35"/><circle cx="228" cy="178" r="6" fill="var(--i-or)" opacity=".35"/>
        </g>
        <path d="M130 172 L150 187 L170 170 L195 190 L220 170 L245 188 L270 172 Q272 250 200 256 Q128 250 130 172Z" fill="var(--i-shell)"/>
        <path d="M130 182 L130 172 L150 187 L170 170 L195 190 L220 170 L245 188 L270 172 L270 182 Q268 250 200 256 Q132 250 130 182Z" fill="#f3dcc6" opacity=".45"/>
        <circle cx="158" cy="222" r="4" fill="var(--i-or)" opacity=".4"/><circle cx="232" cy="230" r="5" fill="var(--i-or)" opacity=".4"/><circle cx="206" cy="214" r="3" fill="var(--i-amber)" opacity=".6"/>
        <g class="ill-a-cap rest-cap" style="--t: 5s">
            <path d="M130 172 L150 187 L170 170 L195 190 L220 170 L245 188 L270 172 Q272 92 200 86 Q128 92 130 172Z" fill="var(--i-shell)"/>
            <path d="M130 172 Q128 92 200 86 Q150 100 150 187Z" fill="#f3dcc6" opacity=".5"/>
            <circle cx="176" cy="122" r="5" fill="var(--i-or)" opacity=".4"/><circle cx="228" cy="132" r="4" fill="var(--i-amber)" opacity=".7"/>
        </g>
    </g>
    <path class="ill-a-twinkle" style="--d: 1.8s" d="M300 90 l4 10 10 4 -10 4 -4 10 -4 -10 -10 -4 10 -4Z" fill="var(--i-amber)"/>
    <path class="ill-a-twinkle" style="--d: 2.4s" d="M92 110 l3 8 8 3 -8 3 -3 8 -3 -8 -8 -3 8 -3Z" fill="var(--i-or)"/>
    <path class="ill-a-twinkle" style="--d: 3s" d="M316 190 l3 7 7 3 -7 3 -3 7 -3 -7 -7 -3 7 -3Z" fill="var(--i-yellow)"/>
</svg>
