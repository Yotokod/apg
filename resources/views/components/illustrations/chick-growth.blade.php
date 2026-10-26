@props(['label' => null])
@php $text = $label ?? trim(strip_tags((string) $slot)); @endphp
<svg viewBox="0 0 480 260" xmlns="http://www.w3.org/2000/svg" {{ $attributes->class('ill')->merge($text !== '' ? ['role' => 'img', 'aria-label' => $text] : ['aria-hidden' => 'true']) }}>
    <ellipse cx="240" cy="232" rx="220" ry="10" fill="var(--i-ink)" opacity=".1"/>
    <g class="ill-a-seq o-b" style="--d: 0s; --t: 6s"><x-illustrations.chicken x="40" y="168" s=".62" body="#ffd166" wing="#ffc24d"/></g>
    <path d="M150 190 l14 -10 v20Z M300 170 l14 -10 v20Z" fill="var(--i-or)" opacity=".6"/>
    <g class="ill-a-seq o-b" style="--d: 2s; --t: 6s"><x-illustrations.chicken x="172" y="136" s=".95" body="#ffb347" wing="#f97316"/></g>
    <g class="ill-a-seq o-b" style="--d: 4s; --t: 6s"><x-illustrations.chicken x="300" y="76" s="1.54" body="#d9480f" wing="#9a3412" :rooster="true"/></g>
    <circle cx="68" cy="226" r="3.5" fill="var(--i-or)"/><circle cx="240" cy="226" r="3.5" fill="var(--i-or)"/><circle cx="410" cy="226" r="3.5" fill="var(--i-or)"/>
</svg>
