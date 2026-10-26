@props(['label' => null])
@php $text = $label ?? trim(strip_tags((string) $slot)); @endphp
<svg viewBox="0 0 400 300" xmlns="http://www.w3.org/2000/svg" {{ $attributes->class('ill')->merge($text !== '' ? ['role' => 'img', 'aria-label' => $text] : ['aria-hidden' => 'true']) }}>
    <circle cx="200" cy="140" r="124" fill="var(--i-cream)"/>
    <path d="M20 246 Q200 226 380 246 V270 H20Z" fill="var(--i-green-l)"/>
    <x-illustrations.chicken x="34" y="150" s="1" :peck="true" :delay="0"/>
    <x-illustrations.chicken x="380" y="152" s=".9" :flip="true" :peck="true" :delay="1.1" body="#ffb347" wing="#f97316"/>
    <x-illustrations.chicken x="196" y="170" s=".62" :peck="true" :delay="2" body="#fff" wing="#f3dcc6"/>
    <path d="M118 196 H282 L264 244 H136Z" fill="var(--i-or-d)"/>
    <path d="M112 190 H288 V202 H112Z" fill="var(--i-or)"/>
    <path d="M128 190 Q150 164 176 184 Q200 156 224 184 Q250 164 272 190Z" fill="var(--i-amber)"/>
    <rect x="168" y="214" width="64" height="8" rx="4" fill="#fff" opacity=".35"/>
    @foreach ([160, 196, 232] as $i => $x)
        <circle class="ill-a-fall" style="--d: {{ $i * .5 }}s; --t: 1.8s" cx="{{ $x }}" cy="150" r="4" fill="var(--i-amber)"/>
    @endforeach
    <circle cx="130" cy="244" r="3" fill="var(--i-amber)"/><circle cx="154" cy="250" r="3" fill="var(--i-amber)"/><circle cx="262" cy="248" r="3" fill="var(--i-amber)"/>
</svg>
