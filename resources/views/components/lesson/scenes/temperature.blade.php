@props(['label' => null])
{{-- Brooding temperature per week at chick height (generic benchmarks, docs/SIMULATOR-SPEC.md). --}}
@php $weeks = [[1, '32–35°', 150], [2, '29–32°', 120], [3, '26–29°', 90], [4, '23–26°', 60]]; @endphp
<svg viewBox="0 0 400 300" xmlns="http://www.w3.org/2000/svg" {{ $attributes->class('ill')->merge($label ? ['role' => 'img', 'aria-label' => $label] : ['aria-hidden' => 'true']) }}>
    <circle cx="200" cy="150" r="130" fill="var(--i-cream)"/>
    <rect x="44" y="52" width="26" height="170" rx="13" fill="#fff" stroke="var(--i-ink)" stroke-width="4"/>
    <rect class="o-b lsn-a-mercury" x="51" y="72" width="12" height="150" rx="6" fill="var(--i-red)"/>
    <circle cx="57" cy="236" r="22" fill="var(--i-red)" stroke="var(--i-ink)" stroke-width="4"/>
    <circle class="ill-a-pulse" style="--t: 2.4s" cx="57" cy="236" r="9" fill="#fff" opacity=".45"/>
    <path d="M110 244 H372" stroke="var(--i-ink)" stroke-width="4" stroke-linecap="round"/>
    @foreach ($weeks as $i => [$n, $range, $h])
        @php $x = 120 + $i * 62; @endphp
        <rect class="o-b lsn-a-bar" style="--d: {{ .25 + $i * .35 }}s" x="{{ $x }}" y="{{ 244 - $h }}" width="46" height="{{ $h }}" rx="8" fill="{{ ['var(--i-red)', 'var(--i-or-d)', 'var(--i-or)', 'var(--i-amber)'][$i] }}"/>
        <text class="lsn-a-in" style="--d: {{ .6 + $i * .35 }}s" x="{{ $x + 23 }}" y="{{ 232 - $h }}" text-anchor="middle" font-size="15" font-weight="800" fill="var(--i-ink-d)">{{ $range }}</text>
        <text x="{{ $x + 23 }}" y="268" text-anchor="middle" font-size="16" font-weight="800" fill="var(--i-ink)">{{ __('lesson.scene.week', ['n' => $n]) }}</text>
    @endforeach
    <g class="ill-a-float" style="--t: 3s">
        <path d="M128 18 H160 L170 36 H118Z" fill="var(--i-ink)"/>
        <circle class="ill-a-twinkle" style="--t: 1.6s" cx="144" cy="44" r="7" fill="var(--i-yellow)"/>
    </g>
    <circle class="ill-a-rise" style="--t: 2.2s" cx="136" cy="44" r="3" fill="var(--i-amber)"/>
    <circle class="ill-a-rise" style="--t: 2.2s; --d: .8s" cx="154" cy="44" r="3" fill="var(--i-amber)"/>
</svg>
