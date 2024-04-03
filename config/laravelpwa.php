<?php
//https://github.com/silviolleite/laravel-pwa
return [
    'name' => 'LaravelPWA',
    'manifest' => [
        'name' => env('APP_NAME', 'My PWA App'),
        'short_name' => env('APP_NAME', 'My PWA App'),
        'description' => 'Funcionalidades que irão ajudar em suas vistorias',
        'start_url' => env('APP_URL') . '/',
        'id' => '/',
        'scope' => '/',
        'background_color' => '#563CFF',
        'theme_color' => '#563CFF',
        'display' => 'standalone',
        'orientation' => 'any',
        'status_bar' => 'black',
        'categories' => 'busines',
        'icons' => [
            '72x72' => [
                'path' => '/build/images/favicons/icon-72x72.png',
                'purpose' => 'any'
            ],
            '96x96' => [
                'path' => '/build/images/favicons/icon-96x96.png',
                'purpose' => 'any'
            ],
            '128x128' => [
                'path' => '/build/images/favicons/icon-128x128.png',
                'purpose' => 'any'
            ],
            '144x144' => [
                'path' => '/build/images/favicons/icon-144x144.png',
                'purpose' => 'any'
            ],
            '152x152' => [
                'path' => '/build/images/favicons/icon-152x152.png',
                'purpose' => 'any'
            ],
            '192x192' => [
                'path' => '/build/images/favicons/icon-192x192.png',
                'purpose' => 'any'
            ],
            '196x196' => [
                'path' => '/build/images/favicons/icon-196x196.png',
                'purpose' => 'maskable'
            ],
            '384x384' => [
                'path' => '/build/images/favicons/icon-384x384.png',
                'purpose' => 'any'
            ],
            '512x512' => [
                'path' => '/build/images/favicons/icon-512x512.png',
                'purpose' => 'maskable'
            ],
        ],
        'splash' => [
            '640x1136' => '/build/images/webview/splash-640x1136.png',
            '750x1334' => '/build/images/webview/splash-750x1334.png',
            '828x1792' => '/build/images/webview/splash-828x1792.png',
            '1125x2436' => '/build/images/webview/splash-1125x2436.png',
            '1242x2208' => '/build/images/webview/splash-1242x2208.png',
            '1242x2688' => '/build/images/webview/splash-1242x2688.png',
            '1536x2048' => '/build/images/webview/splash-1536x2048.png',
            '1668x2224' => '/build/images/webview/splash-1668x2224.png',
            '1668x2388' => '/build/images/webview/splash-1668x2388.png',
            '2048x2732' => '/build/images/webview/splash-2048x2732.png',
        ],
        /*'screenshots' => [
            '1280x800' => [
                'path' => '/build/images/webview/screenshot-1280x800.png',
                'platform' => 'wide'
            ],
            '800x1280' => [
                'path' => '/build/images/webview/screenshot-800x1280.png',
                'platform' => 'wide'
            ],
            '750x1334' => [
                'path' => '/build/images/webview/screenshot-750x1334.png',
                'platform' => 'wide'
            ],

        ],*/
        'screenshots' => [
            [
                'src' => '/build/images/webview/screenshot-1280x800.png',
                'sizes' => '1280x800',
                'type' => 'image/png'
            ],
            [
                'src' => '/build/images/webview/screenshot-800x1280.png',
                'sizes' => '800x1280'
            ],
            [
                'src' => '/build/images/webview/screenshot-750x1334.png',
                'sizes' => '750x1334'
            ]
        ],
        /*'shortcuts' => [
            [
                'name' => 'Shortcut Link 1',
                'description' => 'Shortcut Link 1 Description',
                'url' => '/shortcutlink1',
                'icons' => [
                    "src" => "/images/favicons/icon-72x72.png',
                    "purpose" => "any"
                ]
            ],
            [
                'name' => 'Shortcut Link 2',
                'description' => 'Shortcut Link 2 Description',
                'url' => '/shortcutlink2'
            ]
        ],*/
        'custom' => []
    ]
];
