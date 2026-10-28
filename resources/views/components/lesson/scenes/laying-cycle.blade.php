@props(['label' => null])
{{-- Laying formula (generic example): eggs = live hens x laying rate, e.g. 400 x 80 % = 320 / day. --}}
<svg viewBox="0 0 400 300" xmlns="http://www.w3.org/2000/svg" {{ $attributes->class('ill')->merge($label ? ['role' => 'img', 'aria-label' => $label] : ['aria-hidden' => 'true']) }}>
    <circle cx="200" cy="150" r="130" fill="var(--i-cream)"/>
    <path d="M40 196 Q120 176 200 196 L190 232 Q120 248 50 232Z" fill="var(--i-amber)"/>
    <path d="M44 198 Q120 184 196 198" stroke="var(--i-or-d)" stroke-width="5" fill="none" stroke-linecap="round"/>
    <x-illustrations.chicken x="56" y="98" s="1.1" body="#fff" wing="#f3dcc6"/>
    <rect x="216" y="168" width="160" height="70" rx="12" fill="var(--i-or-d)"/>
    @for ($i = 0; $i < 8; $i++)
        <ellipse class="lsn-a-pop" style="--d: {{ .3 + $i * .3 }}s" cx="{{ 236 + ($i % 4) * 40 }}" cy="{{ 188 + intdiv($i, 4) * 30 }}" rx="12" ry="15" fill="var(--i-shell)"/>
    @endfor
    <g font-weight="800">
        <text class="lsn-a-in" style="--d: .4s" x="296" y="78" text-anchor="middle" font-size="20" fill="var(--i-ink-d)">{{ __('lesson.scene.hens', ['n' => 400]) }} × 80 %</text>
        <g class="lsn-a-pop" style="--d: 2.8s">
            <rect x="216" y="96" width="160" height="42" rx="21" fill="var(--i-green)"/>
            <text x="296" y="123" text-anchor="middle" font-size="17" fill="#fff">{{ __('lesson.scene.eggs_day', ['n' => 320]) }}</text>
        </g>
    </g>
</svg>
