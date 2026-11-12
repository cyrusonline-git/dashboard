<?php

/*
|--------------------------------------------------------------------------
| Boels CORE — koppeling
|--------------------------------------------------------------------------
| Deze app logt NIET zelf in. Boels CORE (databasehub.sorai.nl) is de
| identity provider: de bezoeker deelt zijn CORE-sessiecookie over
| *.sorai.nl, en deze app stuurt die cookie door naar GET /api/me en
| GET /api/access/{slug} (cookie-relay). CORE is leidend voor rollen.
*/

return [
    'url' => rtrim(env('CORE_URL', 'https://databasehub.sorai.nl'), '/'),
    'slug' => env('CORE_APP_SLUG', 'dashboard'),
    'app_url' => rtrim(env('APP_URL', 'https://dashboard.sorai.nl'), '/'),

    // Hoe lang (seconden) het CORE-antwoord in de eigen sessie wordt bewaard
    'cache_seconds' => (int) env('CORE_CACHE_SECONDS', 300),

    // Rollen van deze app zoals ze in CORE (Beheer → Applicaties) staan.
    // Slug => weergavenaam. Volgorde = volgorde op het rolkeuzescherm.
    // Iedereen ziet zijn EIGEN gegevens; de rol bepaalt welk overzicht erbij hoort.
    // CORE schrijft streepjes (area-manager); hier underscores (CoreSso::rollen vertaalt).
    'rollen' => [
        'binnendienst' => 'Binnendienst',
        'werkplaats'   => 'Werkplaats',
        'servicedesk'  => 'Servicedesk',
        'expeditie'    => 'Expeditie',
        'area_manager' => 'Area Manager',
        'bu_manager'   => 'BU Manager',
        'vlootmanager' => 'Vlootmanager',
        'admin'        => 'Beheerder',
    ],

    // Rollen die naast het eigen overzicht ook een team zien (depot/area uit CORE)
    'team_rollen' => ['area_manager', 'bu_manager', 'admin'],

    // Alleen lokaal ontwikkelen (APP_ENV=local): nep-gebruiker met alle rollen,
    // zodat de app zonder CORE-cookie te testen is. Nooit op de server zetten.
    'dev_fake_user' => env('CORE_DEV_FAKE_USER', false),
];
