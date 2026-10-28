@props(['label' => null])
{{-- Generic density benchmarks: 4 adults/m², 20-25 chicks/m² in the brooding area. --}}
<svg viewBox="0 0 400 300" xmlns="http://www.w3.org/2000/svg" {{ $attributes->class('ill')->merge($label ? ['role' => 'img', 'aria-label' => $label] : ['aria-hidden' => 'true']) }}>
    <circle cx="200" cy="150" r="130" fill="var(--i-cream)"/>
    <rect x="30" y="70" width="180" height="160" rx="14" fill="var(--i-green-l)" opacity=".55"/>
    <rect x="30" y="70" width="180" height="160" rx="14" fill="none" stroke="var(--i-ink)" stroke-width="4" stroke-dasharray="10 8"/>
    <text x="120" y="56" text-anchor="middle" font-size="16" font-weight="800" fill="var(--i-ink)">1 m²</text>
    @foreach ([[38, 82], [118, 82], [38, 152], [118, 152]] as $i => [$x, $y])
        <g class="lsn-a-pop" style="--d: {{ .3 + $i * .35 }}s"><x-illustrations.chicken :x="$x" :y="$y" s=".62" :peck="true" :delay="$i * .6"/></g>
    @endforeach
    <g class="lsn-a-in" style="--d: 1.8s">
        <rect x="56" y="244" width="128" height="34" rx="17" fill="var(--i-or-d)"/>
        <text x="120" y="267" text-anchor="middle" font-size="16" font-weight="800" fill="#fff">{{ __('lesson.scene.per_m2', ['n' => 4]) }} {{ __('lesson.scene.adults') }}</text>
    </g>
    <rect x="240" y="100" width="130" height="120" rx="12" fill="var(--i-peach)" stroke="var(--i-ink)" stroke-width="4" stroke-dasharray="8 7"/>
    @for ($i = 0; $i < 20; $i++)
        <circle class="lsn-a-pop" style="--d: {{ 2 + $i * .06 }}s" cx="{{ 256 + ($i % 5) * 25 }}" cy="{{ 122 + intdiv($i, 5) * 26 }}" r="9" fill="var(--i-yellow)" stroke="var(--i-amber)" stroke-width="2"/>
    @endfor
    <g class="lsn-a-in" style="--d: 3.4s">
        <rect x="220" y="244" width="174" height="34" rx="17" fill="var(--i-ink)"/>
        <text x="307" y="267" text-anchor="middle" font-size="14" font-weight="800" fill="#fff">{{ __('lesson.scene.per_m2', ['n' => '20–25']) }} {{ __('lesson.scene.chicks') }}</text>
    </g>
</svg>
