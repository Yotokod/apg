<?php

// Single source of truth for the brand identity. Override any key from
// Admin > Settings (stored in the `settings` table). Duplicate the project
// for another company by changing only this file / those settings.
return [
    'name' => 'Africa Poulets Goliath',
    'short_name' => 'APG',
    'tagline' => ['fr' => 'Élevez grand avec le poulet Goliath', 'en' => 'Grow big with the Goliath chicken'],
    'logo' => '/images/logo-main.png',
    'primary_color' => '#D9480F',
    'accent_color' => '#0B0B2B',
    'phone' => '+229 67 16 00 53',
    'whatsapp' => '22967160053',
    'email' => 'contact@africa-poulets-goliath.com',
    'address' => 'Porto-Novo, Bénin',
    'cities' => 'Cotonou, Porto-Novo, Parakou',
    'currency' => 'FCFA',
    'socials' => [
        'youtube' => 'https://www.youtube.com/@africapouletgoliath',
        'tiktok' => 'https://www.tiktok.com/@africapouletgoliath',
        'facebook' => 'https://www.facebook.com/africapouletgoliath',
        'instagram' => '',
        'linkedin' => '',
    ],
    'app_mode' => 'one_market', // one_market | marketplace
    'home_page' => 'landing',   // landing | shop | courses
    'ai_provider' => 'anthropic',
    'locales' => ['fr', 'en'],
];
