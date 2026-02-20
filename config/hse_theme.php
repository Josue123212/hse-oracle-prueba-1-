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
            'label' => 'Programs',
            'icon' => 'gmdi-assignment',
        ],
        [
            'label' => 'Training',
            'icon' => 'gmdi-school',
        ],
        [
            'label' => 'Audits',
            'icon' => 'gmdi-fact-check',
        ],
        [
            'label' => 'Documentation',
            'icon' => 'gmdi-description',
        ],
        [
            'label' => 'Communications',
            'icon' => 'gmdi-campaign',
        ],
        [
            'label' => 'Catalogs',
            'icon' => 'gmdi-folder-copy',
        ],
        [
            'label' => 'Security',
            'icon' => 'gmdi-admin-panel-settings',
        ],
    ],
];

