@props(['x' => 0, 'y' => 0, 's' => 1, 'flip' => false, 'body' => '#ffd166', 'wing' => '#ffb347', 'comb' => '#e5383b', 'delay' => 0, 'peck' => false, 'rooster' => false])
{{-- SVG fragment (<g>): chicken drawn in a 0..110 x 0..100 box, feet at y=100. Use inside an <svg>. --}}
<g transform="translate({{ $x }} {{ $y }}) scale({{ $flip ? -$s : $s }} {{ $s }})">
    <g @class(['o-b', 'ill-a-peck' => $peck]) style="--d: {{ $delay }}s; --t: 2.6s">
        @if ($rooster)
            <path d="M22 62 C6 52 4 26 14 14 C22 30 30 40 36 52Z" fill="#0b0b2b"/>
            <path d="M20 66 C2 62 -4 40 4 28 C14 40 22 50 30 58Z" fill="#d9480f"/>
        @else
            <path d="M24 62 C12 56 10 42 16 34 C24 42 30 50 36 56Z" fill="{{ $wing }}"/>
        @endif
        <ellipse cx="50" cy="62" rx="32" ry="26" fill="{{ $body }}"/>
        <path d="M34 60 C42 78 64 78 70 58 C62 62 44 66 34 60Z" fill="{{ $wing }}"/>
        <circle cx="78" cy="36" r="15" fill="{{ $body }}"/>
        <circle cx="72" cy="20" r="{{ $rooster ? 7 : 4 }}" fill="{{ $comb }}"/>
        <circle cx="79" cy="18" r="{{ $rooster ? 8 : 4.5 }}" fill="{{ $comb }}"/>
        <circle cx="86" cy="21" r="{{ $rooster ? 6 : 3.5 }}" fill="{{ $comb }}"/>
        <path d="M91 32 L104 37 L91 42Z" fill="#f97316"/>
        <ellipse cx="90" cy="46" rx="3.5" ry="{{ $rooster ? 7 : 4 }}" fill="{{ $comb }}"/>
        <circle cx="82" cy="33" r="2.4" fill="#0b0b2b"/>
    </g>
    <path d="M44 86 V100 M40 100 H50 M62 86 V100 M58 100 H68" stroke="#f97316" stroke-width="3.5" stroke-linecap="round" fill="none"/>
</g>
