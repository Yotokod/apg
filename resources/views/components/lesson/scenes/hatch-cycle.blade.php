@props(['label' => null])
{{-- Incubation funnel (generic example): 1 000 eggs loaded, 90 % fertile = 900, 85 % hatch on fertile eggs = 765 chicks. --}}
@php $steps = [['1 000', 'loaded', 'var(--i-shell)'], ['900', 'fertile', 'var(--i-amber)'], ['765', 'hatched', 'var(--i-yellow)']]; @endphp
<svg viewBox="0 0 400 300" xmlns="http://www.w3.org/2000/svg" {{ $attributes->class('ill')->merge($label ? ['role' => 'img', 'aria-label' => $label] : ['aria-hidden' => 'true']) }}>
    <circle cx="200" cy="150" r="130" fill="var(--i-cream)"/>
    @foreach ($steps as $i => [$n, $key, $fill])
        @php $cx = 66 + $i * 134; @endphp
        <g class="lsn-a-pop" style="--d: {{ $i * .9 }}s">
            <circle cx="{{ $cx }}" cy="128" r="42" fill="#fff" stroke="var(--i-ink)" stroke-width="4"/>
            @if ($i < 2)
                <ellipse class="ill-a-wobble" style="--t: 3s; --d: {{ $i }}s" cx="{{ $cx }}" cy="130" rx="19" ry="25" fill="{{ $fill }}" stroke="var(--i-or-d)" stroke-width="3"/>
            @else
                <g class="ill-a-float" style="--t: 2.6s">
                    <circle cx="{{ $cx }}" cy="132" r="23" fill="var(--i-yellow)"/>
                    <circle cx="{{ $cx - 9 }}" cy="126" r="4" fill="var(--i-ink-d)"/><circle cx="{{ $cx + 9 }}" cy="126" r="4" fill="var(--i-ink-d)"/>
                    <path d="M{{ $cx - 6 }} 136 H{{ $cx + 6 }} L{{ $cx }} 146Z" fill="var(--i-or)"/>
                </g>
            @endif
            <text x="{{ $cx }}" y="210" text-anchor="middle" font-size="24" font-weight="800" fill="var(--i-ink-d)">{{ $n }}</text>
            <text x="{{ $cx }}" y="234" text-anchor="middle" font-size="15" font-weight="700" fill="var(--i-ink)">{{ __('lesson.scene.'.$key) }}</text>
        </g>
        @if ($i < 2)
            <g class="lsn-a-in" style="--d: {{ .5 + $i * .9 }}s">
                <path d="M{{ $cx + 50 }} 128 H{{ $cx + 84 }}" stroke="var(--i-or-d)" stroke-width="5" stroke-linecap="round"/>
                <circle class="lsn-a-move" cx="{{ $cx + 54 }}" cy="128" r="5" fill="var(--i-or)"/>
                <text x="{{ $cx + 67 }}" y="82" text-anchor="middle" font-size="14" font-weight="800" fill="var(--i-or-d)">{{ $i === 0 ? '90 %' : '85 %' }}</text>
            </g>
        @endif
    @endforeach
</svg>
