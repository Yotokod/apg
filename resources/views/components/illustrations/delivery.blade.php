@props(['label' => null])
@php $text = $label ?? trim(strip_tags((string) $slot)); @endphp
<svg viewBox="0 0 480 280" xmlns="http://www.w3.org/2000/svg" {{ $attributes->class('ill')->merge($text !== '' ? ['role' => 'img', 'aria-label' => $text] : ['aria-hidden' => 'true']) }}>
    <rect x="20" y="20" width="440" height="240" rx="40" fill="var(--i-cream)"/>
    <g class="ill-a-fly" style="--t: 9s">
        <path d="M0 30 h-34" stroke="#fff" stroke-width="5" stroke-linecap="round"/>
        <path d="M0 30 L32 24 L48 8 H56 L48 28 L62 32 L48 36 L56 54 H48 L32 38Z" fill="var(--i-ink)"/>
        <path d="M32 24 L52 28 L32 32Z" fill="var(--i-or)"/>
    </g>
    <g class="ill-a-float" style="--t: 6s"><ellipse cx="110" cy="78" rx="34" ry="12" fill="#fff"/><ellipse cx="132" cy="70" rx="22" ry="12" fill="#fff"/></g>
    <g class="ill-a-float" style="--d: 2s; --t: 7s"><ellipse cx="380" cy="104" rx="30" ry="10" fill="#fff"/><ellipse cx="398" cy="97" rx="18" ry="10" fill="#fff"/></g>
    <rect x="20" y="224" width="440" height="36" rx="0" fill="var(--i-ink)"/>
    @foreach ([0, 1, 2, 3, 4] as $i)
        <rect class="ill-a-drift" style="--d: {{ -$i * .6 }}s; --t: 3s" x="{{ 20 + $i * 90 }}" y="240" width="40" height="5" rx="2.5" fill="var(--i-amber)" opacity=".75" transform="translate(-60 0)"/>
    @endforeach
    <g class="ill-a-drive" style="--t: .5s">
        <rect x="104" y="116" width="196" height="100" rx="10" fill="#fff"/>
        <rect x="104" y="196" width="196" height="20" fill="var(--i-or)"/>
        <rect x="124" y="82" width="64" height="34" rx="5" fill="var(--i-amber)"/>
        <rect x="196" y="92" width="64" height="24" rx="5" fill="var(--i-or-d)"/>
        <path d="M132 82 v34 M156 82 v34 M180 82 v34" stroke="var(--i-or-d)" stroke-width="3" opacity=".6"/>
        <path d="M300 144 H352 L384 178 V216 H300Z" fill="var(--i-ink)"/>
        <path d="M312 154 H346 L370 178 H312Z" fill="var(--i-cream)"/>
        <rect x="376" y="196" width="10" height="10" rx="3" fill="var(--i-yellow)"/>
        @foreach ([150, 336] as $cx)
            <g><circle cx="{{ $cx }}" cy="218" r="22" fill="var(--i-ink-d)"/>
            <g class="ill-a-spin" style="--t: .8s"><circle cx="{{ $cx }}" cy="218" r="10" fill="var(--i-cream)"/><path d="M{{ $cx }} 208 v20 M{{ $cx - 10 }} 218 h20" stroke="var(--i-or)" stroke-width="3"/></g></g>
        @endforeach
    </g>
</svg>
