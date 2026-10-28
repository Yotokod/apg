{{-- One SVG stage, one <g> per scene key (sim_steps.scene). Flat 2–3 tone shapes, palette orange / ink / cream / amber / green.
     Animations are CSS (illustrations.css + sim.css), restarted each time a scene group is shown; reduced motion = final pose. --}}
@php
    $chickSpots = [[300, 236], [350, 248], [250, 252], [400, 232], [210, 230], [450, 256], [330, 280], [170, 270], [500, 236], [390, 286], [260, 292], [540, 268]];
    $huddle = [[286, 250, .9], [318, 246, .95], [350, 252, .9], [300, 266, 1], [334, 268, 1], [268, 268, .85], [366, 270, .85], [318, 284, 1.05]];
@endphp
<svg viewBox="0 0 640 360" xmlns="http://www.w3.org/2000/svg" class="ill sim-stage-svg" preserveAspectRatio="xMidYMid meet" aria-hidden="true" focusable="false">
    <defs>
        <linearGradient id="sim-sky" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#ffe3d0"/><stop offset="1" stop-color="#fff4ec"/></linearGradient>
        <radialGradient id="sim-glow"><stop offset="0" stop-color="#ffd166" stop-opacity=".9"/><stop offset="1" stop-color="#ffd166" stop-opacity="0"/></radialGradient>
        <radialGradient id="sim-glow-hot"><stop offset="0" stop-color="#e5383b" stop-opacity=".75"/><stop offset="1" stop-color="#f97316" stop-opacity="0"/></radialGradient>
        <clipPath id="sim-bag-clip"><path d="M440 120 Q430 100 452 92 H568 Q590 100 580 120 L592 300 Q592 316 576 316 H444 Q428 316 428 300Z"/></clipPath>
    </defs>

    {{-- Outdoor backdrop --}}
    <rect width="640" height="360" fill="url(#sim-sky)"/>
    <circle class="ill-a-pulse" style="--t: 5s" cx="560" cy="64" r="30" fill="var(--i-yellow)"/>
    <g class="ill-a-drift" style="--t: 38s"><ellipse cx="60" cy="70" rx="44" ry="14" fill="#fff"/><ellipse cx="86" cy="60" rx="26" ry="14" fill="#fff"/></g>
    <g class="ill-a-drift" style="--t: 52s; --d: -20s"><ellipse cx="40" cy="120" rx="34" ry="10" fill="#fff" opacity=".8"/></g>
    <path d="M0 268 Q320 244 640 268 V360 H0Z" fill="var(--i-green-l)"/>
    <path d="M0 300 Q320 284 640 300 V360 H0Z" fill="var(--i-green)" opacity=".35"/>

    {{-- Interior backdrop (brooding / incubation scenes) --}}
    <g display="none" :display="(['thermo', 'huddle', 'panting', 'incubator'].includes(sceneKey)) ? null : 'none'">
        <rect width="640" height="360" fill="#fff4ec"/>
        <rect y="0" width="640" height="18" fill="var(--i-ink)"/>
        <rect x="70" y="18" width="12" height="232" fill="var(--i-peach)"/><rect x="558" y="18" width="12" height="232" fill="var(--i-peach)"/>
        <rect x="430" y="60" width="96" height="64" rx="6" fill="#cfe8f7"/><path d="M478 60 V124 M430 92 H526" stroke="#fff" stroke-width="5"/>
        <rect y="250" width="640" height="110" fill="#f3dcc6"/>
        @foreach ([[40, 290], [120, 320], [210, 300], [300, 334], [420, 306], [520, 326], [600, 296]] as [$lx, $ly])
            <path d="M{{ $lx }} {{ $ly }} l14 -4" stroke="#e2bf98" stroke-width="3" stroke-linecap="round"/>
        @endforeach
    </g>

    {{-- farm --}}
    <g display="none" :display="(sceneKey === 'farm') ? null : 'none'">
        <x-illustrations.farm-house x="300" y="28" style="width: 330px; height: 248px"/>
        @foreach ([20, 60, 100, 140, 180, 220] as $fx)<rect x="{{ $fx }}" y="236" width="8" height="48" rx="2" fill="var(--i-or-d)"/>@endforeach
        <rect x="16" y="248" width="220" height="7" rx="3" fill="var(--i-or)"/><rect x="16" y="266" width="220" height="7" rx="3" fill="var(--i-or)"/>
        <x-illustrations.chicken x="60" y="222" s=".66" :peck="true"/>
        <x-illustrations.chicken x="250" y="236" s=".56" :flip="true" :peck="true" :delay="1.2" body="#ffb347" wing="#f97316"/>
        <x-illustrations.chicken x="140" y="250" s=".6" :rooster="true" :peck="true" :delay=".6" body="#fff" wing="#f3dcc6"/>
    </g>

    {{-- chicks: count follows the flock slider --}}
    <g display="none" :display="(sceneKey === 'chicks') ? null : 'none'">
        <g class="sim-pop" style="--d: .1s">
            <path d="M40 220 H190 V290 H40Z" fill="var(--i-or-d)"/>
            <path d="M40 220 L22 196 H172 L190 220Z" fill="var(--i-or)"/>
            <path d="M190 220 L210 200 V270 L190 290Z" fill="#9a3412"/>
            <rect x="60" y="244" width="110" height="18" rx="4" fill="#fff" opacity=".85"/>
            <rect x="70" y="250" width="56" height="6" rx="3" fill="var(--i-or-d)"/>
            @foreach ([[60, 186], [100, 180], [140, 186]] as $k => [$hx, $hy])
                <x-sim.chick :x="$hx" :y="$hy" s=".7" anim="ill-a-peek" :delay="$k * .4"/>
            @endforeach
        </g>
        @foreach ($chickSpots as $i => [$cx, $cy])
            <x-sim.chick :x="$cx" :y="$cy - 56" s=".8" :flip="$i % 3 === 0" :delay="$i * .23" ::display="(chicksShown > {{ $i }}) ? null : 'none'"/>
        @endforeach
    </g>

    {{-- house: walls rise, roof drops --}}
    <g display="none" :display="(sceneKey === 'house') ? null : 'none'">
        <rect x="130" y="270" width="380" height="12" rx="3" fill="var(--i-ink)"/>
        <g class="sim-build o-b">
            <rect x="150" y="170" width="340" height="100" fill="#fff"/>
            <rect x="150" y="170" width="340" height="100" fill="var(--i-peach)" opacity=".6"/>
            @foreach ([180, 260, 380, 440] as $wx)<rect x="{{ $wx }}" y="196" width="34" height="26" rx="4" fill="#cfe8f7"/>@endforeach
            <rect x="304" y="206" width="40" height="64" rx="4" fill="var(--i-or-d)"/>
        </g>
        <g class="sim-drop"><path d="M128 176 L320 104 L512 176Z" fill="var(--i-or-d)"/><path d="M128 176 L320 104 L512 176 L498 176 L320 116 L142 176Z" fill="var(--i-or)"/></g>
        <g class="ill-a-sway" style="--t: 1.4s; transform-origin: 560px 300px"><rect x="556" y="236" width="8" height="64" rx="3" fill="var(--i-ink)"/><rect x="540" y="226" width="40" height="18" rx="4" fill="var(--i-ink-d)"/></g>
        <path d="M150 312 H490" stroke="var(--i-ink)" stroke-width="3" stroke-dasharray="8 6"/>
        <path d="M150 302 V322 M490 302 V322" stroke="var(--i-ink)" stroke-width="3"/>
        @foreach ([[40, 276], [64, 276], [52, 262]] as [$bx, $by])<rect x="{{ $bx }}" y="{{ $by }}" width="22" height="13" rx="2" fill="var(--i-or)"/>@endforeach
    </g>

    {{-- site: drained ground, roof, breeze --}}
    <g display="none" :display="(sceneKey === 'site') ? null : 'none'">
        <x-illustrations.farm-house x="150" y="26" style="width: 340px; height: 255px"/>
        <path d="M120 296 Q320 316 540 296" stroke="#7cc0e8" stroke-width="8" fill="none" stroke-linecap="round"/>
        @foreach ([[40, 150, 0], [30, 190, .8], [50, 230, 1.6]] as [$wx, $wy, $wd])
            <path class="sim-breeze" style="--d: {{ $wd }}s" d="M{{ $wx }} {{ $wy }} q20 -10 40 0 t40 0" stroke="var(--i-ink)" stroke-opacity=".35" stroke-width="4" fill="none" stroke-linecap="round"/>
        @endforeach
        <g class="sim-pop" style="--d: .4s"><circle cx="560" cy="250" r="26" fill="var(--i-green)"/><path d="M547 250 l9 9 l17 -18" stroke="#fff" stroke-width="6" fill="none" stroke-linecap="round" stroke-linejoin="round"/></g>
    </g>

    {{-- heat lamp shared by brooding scenes --}}
    <g display="none" :display="(['thermo', 'huddle', 'panting'].includes(sceneKey)) ? null : 'none'">
        <path d="M320 18 V96" stroke="var(--i-ink)" stroke-width="3"/>
        <circle class="ill-a-pulse" style="--t: 2.6s" cx="320" cy="150" r="120" :fill="sceneKey === 'panting' ? 'url(#sim-glow-hot)' : 'url(#sim-glow)'"/>
        <path d="M292 96 H348 L362 128 H278Z" fill="var(--i-ink)"/>
        <circle cx="320" cy="132" r="12" :fill="sceneKey === 'panting' ? 'var(--i-red)' : 'var(--i-amber)'"/>
    </g>

    {{-- thermo: mercury rises to week-1 level --}}
    <g display="none" :display="(sceneKey === 'thermo') ? null : 'none'">
        <rect x="498" y="70" width="34" height="180" rx="17" fill="#fff" stroke="var(--i-ink)" stroke-width="4"/>
        @foreach (range(0, 6) as $tk)<path d="M532 {{ 92 + $tk * 22 }} h12" stroke="var(--i-ink)" stroke-width="3"/>@endforeach
        <rect class="sim-fill o-b" x="507" y="86" width="16" height="164" rx="8" fill="var(--i-red)" :style="`transform: scaleY(${thermoLevel})`"/>
        <circle cx="515" cy="262" r="24" fill="var(--i-red)" stroke="var(--i-ink)" stroke-width="4"/>
        @foreach ([[180, 220], [250, 262], [390, 258], [440, 218], [150, 286], [330, 290]] as $i => [$cx, $cy])
            <x-sim.chick :x="$cx" :y="$cy" s=".85" :flip="$i % 2 === 1" :delay="$i * .3"/>
        @endforeach
    </g>

    {{-- huddle: chicks piled under the lamp, cold draught --}}
    <g display="none" :display="(sceneKey === 'huddle') ? null : 'none'">
        @foreach ([[96, 170, 0], [86, 214, .6], [104, 256, 1.2]] as [$wx, $wy, $wd])
            <path class="sim-breeze" style="--d: {{ $wd }}s" d="M{{ $wx }} {{ $wy }} q24 -12 48 0 t48 0" stroke="#7cc0e8" stroke-width="5" fill="none" stroke-linecap="round"/>
        @endforeach
        <g class="sim-shiver">
            @foreach ($huddle as $i => [$cx, $cy, $sc])
                <x-sim.chick :x="$cx - 20" :y="$cy - 40" :s="$sc" :flip="$i % 2 === 0" anim="" />
            @endforeach
        </g>
    </g>

    {{-- panting: chicks far from the heat, beaks open, heat waves --}}
    <g display="none" :display="(sceneKey === 'panting') ? null : 'none'">
        @foreach ([270, 320, 370] as $i => $hx)
            <path class="ill-a-rise" style="--d: {{ $i * .5 }}s; --t: 2.2s" d="M{{ $hx }} 230 q-10 -14 0 -28 t0 -28" stroke="var(--i-red)" stroke-width="4" fill="none" stroke-linecap="round" opacity=".7"/>
        @endforeach
        @foreach ([[60, 230, true], [120, 266, true], [90, 290, true], [470, 236, false], [530, 270, false], [500, 296, false]] as $i => [$cx, $cy, $fl])
            <x-sim.chick :x="$fl ? $cx + 50 : $cx" :y="$cy - 30" s=".85" :flip="$fl" :pant="true" anim="ill-a-pulse" :delay="$i * .2"/>
        @endforeach
    </g>

    {{-- feed: trough + bag filled to the selected ration's need --}}
    <g display="none" :display="(sceneKey === 'feed') ? null : 'none'">
        <x-illustrations.feeding x="0" y="40" style="width: 400px; height: 300px"/>
        @foreach ([470, 510, 550] as $i => $gx)<circle class="ill-a-fall" style="--d: {{ $i * .45 }}s; --t: 1.6s" cx="{{ $gx }}" cy="58" r="5" fill="var(--i-amber)"/>@endforeach
        <path d="M440 120 Q430 100 452 92 H568 Q590 100 580 120 L592 300 Q592 316 576 316 H444 Q428 316 428 300Z" fill="#fff" stroke="var(--i-ink)" stroke-width="4"/>
        <g clip-path="url(#sim-bag-clip)">
            <rect class="sim-fill o-b" x="420" y="92" width="180" height="226" fill="var(--i-amber)" :style="`transform: scaleY(${bagFill})`"/>
        </g>
        <rect x="446" y="176" width="128" height="40" rx="8" fill="var(--i-or-d)"/>
        <path d="M486 206 q24 -26 48 0 M510 186 V214" stroke="#fff" stroke-width="5" fill="none" stroke-linecap="round"/>
        <path d="M452 92 Q510 80 568 92" stroke="var(--i-ink)" stroke-width="4" fill="none"/>
    </g>

    {{-- vaccine / biosecurity --}}
    <g display="none" :display="(sceneKey === 'vaccine') ? null : 'none'">
        <x-illustrations.vaccination x="120" y="30" style="width: 400px; height: 300px"/>
        <x-illustrations.chicken x="30" y="236" s=".55" :peck="true"/>
        <x-illustrations.chicken x="610" y="236" s=".55" :flip="true" :peck="true" :delay=".8" body="#ffb347" wing="#f97316"/>
    </g>

    {{-- alert: sudden deaths --}}
    <g display="none" :display="(sceneKey === 'alert') ? null : 'none'">
        <rect width="640" height="360" fill="var(--i-ink-d)" opacity=".28"/>
        <x-illustrations.chicken x="90" y="226" s=".6" :peck="true" body="#f3dcc6" wing="#e2bf98"/>
        <g transform="translate(250 280) rotate(-90)"><x-illustrations.chicken x="-20" y="0" s=".55" body="#e2bf98" wing="#c9a27a"/></g>
        <g class="ill-a-pulse" style="--t: 1.2s"><path d="M320 60 L392 190 H248Z" fill="var(--i-red)" stroke="#fff" stroke-width="6" stroke-linejoin="round"/>
            <rect x="314" y="100" width="12" height="52" rx="6" fill="#fff"/><circle cx="320" cy="170" r="7" fill="#fff"/></g>
        <g class="ill-a-wobble" style="--t: 1.6s"><rect x="470" y="170" width="70" height="120" rx="14" fill="var(--i-ink)"/><rect x="480" y="186" width="50" height="78" rx="6" fill="#cfe8f7"/><circle cx="505" cy="276" r="6" fill="#fff"/></g>
    </g>

    {{-- growth (broilers) --}}
    <g display="none" :display="(sceneKey === 'growth') ? null : 'none'">
        <x-illustrations.chick-growth x="20" y="70" style="width: 600px; height: 325px"/>
    </g>

    {{-- eggs: tray fills with the laying rate --}}
    <g display="none" :display="(sceneKey === 'eggs') ? null : 'none'">
        <path d="M40 270 Q120 300 200 270 L190 300 Q120 320 50 300Z" fill="#c9a27a"/>
        <x-illustrations.chicken x="56" y="186" s=".9" :peck="true"/>
        <rect x="290" y="196" width="300" height="96" rx="12" fill="#e2bf98"/>
        @foreach (range(0, 11) as $i)
            @php $ex = 318 + ($i % 6) * 48; $ey = 224 + intdiv($i, 6) * 40; @endphp
            <circle cx="{{ $ex }}" cy="{{ $ey + 6 }}" r="17" fill="#c9a27a"/>
            <ellipse class="sim-pop" style="--d: {{ $i * .12 }}s" cx="{{ $ex }}" cy="{{ $ey }}" rx="13" ry="17" fill="var(--i-shell)" stroke="#e2bf98" stroke-width="2" :display="(eggsShown > {{ $i }}) ? null : 'none'"/>
        @endforeach
    </g>

    {{-- incubation --}}
    <g display="none" :display="(sceneKey === 'incubator') ? null : 'none'">
        <x-illustrations.incubator x="10" y="50" style="width: 320px; height: 240px"/>
        <x-illustrations.egg-hatch x="320" y="50" style="width: 320px; height: 240px"/>
    </g>

    {{-- sale --}}
    <g display="none" :display="(sceneKey === 'truck') ? null : 'none'">
        <x-illustrations.delivery x="40" y="30" style="width: 560px; height: 327px"/>
    </g>

    {{-- report / ending --}}
    <g display="none" :display="(sceneKey === 'report') ? null : 'none'">
        <x-illustrations.profit-chart x="40" y="40" style="width: 360px; height: 270px"/>
        @foreach (range(0, 5) as $i)
            <g class="sim-pop" style="--d: {{ .2 + $i * .15 }}s"><ellipse cx="510" cy="{{ 286 - $i * 18 }}" rx="46" ry="14" fill="var(--i-or-d)"/><ellipse cx="510" cy="{{ 280 - $i * 18 }}" rx="46" ry="14" fill="var(--i-amber)"/></g>
        @endforeach
    </g>
</svg>
