@php
    $gaId = (string) site('seo.ga_id');
    $gaId = preg_match('/^(G|UA|AW|GT)-[A-Z0-9-]{4,20}$/', $gaId) ? $gaId : null;
    $pixelId = (string) site('seo.pixel_id');
    $pixelId = preg_match('/^\d{5,20}$/', $pixelId) ? $pixelId : null;
@endphp
@if ($gaId)
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ rawurlencode($gaId) }}"></script>
    <script>window.dataLayer = window.dataLayer || []; function gtag(){dataLayer.push(arguments)} gtag('js', new Date()); gtag('config', @json($gaId));</script>
@endif
@if ($pixelId)
    <script>!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script','https://connect.facebook.net/en_US/fbevents.js');fbq('init', @json($pixelId));fbq('track', 'PageView');</script>
@endif
