<?php

return [
    'font_family' => env('HSE_UI_FONT_FAMILY', 'Inria Sans'),

    'brand' => [
        'logo_path' => env('HSE_UI_LOGO_PATH', 'Logo-2.png'),
        'favicon_path' => env('HSE_UI_FAVICON_PATH', 'oracle-logo.png'),
        'alt' => env('HSE_UI_BRAND_ALT', 'ORACLE PERU S.A.C.'),
        'tagline' => env('HSE_UI_BRAND_TAGLINE', 'Gestión GHSE'),
    ],

    'colors' => [
        'primary' => env('HSE_UI_PRIMARY_COLOR', 'Blue'),
    ],

    'sidebar' => [
        'inject_brand_footer' => env('HSE_UI_SIDEBAR_BRAND_FOOTER', true),
        'compact_logo_path' => env('HSE_UI_COMPACT_LOGO_PATH', null),
    ],

    'navigation_groups' => [
        [
            'label' => 'Gestión de Elementos',
            'icon' => 'gmdi-layers',
        ],
        [
            'label' => 'Evaluaciones y Auditorías',
            'icon' => 'gmdi-fact-check',
        ],
        [
            'label' => 'Organización y Ubicaciones',
            'icon' => 'gmdi-location-city',
        ],
        [
            'label' => 'Usuarios y Comunicación',
            'icon' => 'gmdi-groups',
        ],
        [
            'label' => 'Capacitación',
            'icon' => 'gmdi-school',
        ],
        [
            'label' => 'Reportes y Seguimiento',
            'icon' => 'gmdi-insights',
        ],
        [
            'label' => 'Sistema',
            'icon' => 'gmdi-settings',
        ],
    ],
];
