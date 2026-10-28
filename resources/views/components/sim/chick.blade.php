@props(['x' => 0, 'y' => 0, 's' => 1, 'flip' => false, 'pant' => false, 'anim' => 'ill-a-peck', 'delay' => 0])
{{-- SVG fragment: flat chick drawn in a 0..60 x 0..56 box, feet at y=56. --}}
<g transform="translate({{ $x }} {{ $y }}) scale({{ $flip ? -$s : $s }} {{ $s }})" {{ $attributes }}>
    <ellipse cx="28" cy="57" rx="18" ry="3" fill="#0b0b2b" opacity=".12"/>
    <g class="o-b {{ $anim }}" style="--d: {{ $delay }}s; --t: 2.4s">
        <ellipse cx="26" cy="38" rx="19" ry="15" fill="#ffd166"/>
        <path d="M14 36 C18 46 30 48 34 40 C28 42 20 41 14 36Z" fill="#ffb347"/>
        <circle cx="40" cy="22" r="11" fill="#ffd166"/>
        <circle cx="43" cy="20" r="1.9" fill="#0b0b2b"/>
        @if ($pant)
            <path d="M50 21 L59 17 L51 24Z" fill="#f97316"/><path d="M50 25 L58 29 L50 27Z" fill="#d9480f"/>
            <path d="M8 30 L0 24 M10 40 L1 42" stroke="#ffb347" stroke-width="3" stroke-linecap="round"/>
        @else
            <path d="M50 21 L58 24 L50 27Z" fill="#f97316"/>
        @endif
    </g>
    <path d="M21 52 V57 M31 52 V57" stroke="#f97316" stroke-width="2.6" stroke-linecap="round"/>
</g>
