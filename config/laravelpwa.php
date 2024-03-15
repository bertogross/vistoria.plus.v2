<?php

return [
    'name' => 'LaravelPWA',
    'manifest' => [
        'name' => env('APP_NAME', 'My PWA App'),
        'short_name' => env('APP_NAME', 'My PWA App'),
        'description' => 'Funcionalidades que irão ajudar em suas vistorias',
        'start_url' => '/',
        'background_color' => '#1A1D21',
        'theme_color' => '#1A1D21',
        'display' => 'standalone',
        'orientation'=> 'portrait',
        'status_bar'=> 'black',
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
            '384x384' => [
                'path' => '/build/images/favicons/icon-384x384.png',
                'purpose' => 'any'
            ],
            '512x512' => [
                'path' => '/build/images/favicons/icon-512x512.png',
                'purpose' => 'any'
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
        /*'shortcuts' => [
            [
                'name' => 'Shortcut Link 1',
                'description' => 'Shortcut Link 1 Description',
                'url' => '/shortcutlink1',
                'icons' => [
                    "src" => "/images/favicons/icon-72x72.png",
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
