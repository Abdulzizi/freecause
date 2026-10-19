<?php

// Adsterra banner slots for the petition page. IDs/keys live here (env-overridable)
// so they can change without a template deploy. Set ADS_ENABLED=false to kill all slots.
return [
    // master switch -- disables every slot at once.
    // Default OFF: the site's strict CSP must allow bicea.org (script-src + frame-src)
    // and the inline atOptions blocks before ads can render; flipping this on without
    // that would show empty reserved boxes. Enable via admin once CSP is extended.
    'enabled' => env('ADS_ENABLED', false),

    // network script host (Adsterra). Centralized so a domain change is one edit.
    'host' => env('ADS_HOST', 'https://bicea.org'),

    // per-slot config. key = Adsterra zone key, w/h = reserved box (min-height guards layout shift).
    // native = Adsterra "native" container format; iframe = fixed-size banner.
    'slots' => [
        'native' => [
            'format' => 'native',
            'key' => env('ADS_SLOT_NATIVE', '60d01e95a4f4308d02fffe57071f7aa1'),
            'path' => '21', // native uses /21/ path on the network
            'w' => 704,
            'h' => 250, // reserved min-height; native content is fluid
        ],
        'leaderboard' => [ // 728x90
            'format' => 'iframe',
            'key' => env('ADS_SLOT_LEADERBOARD', 'd3909061830cb1805f550fa7fd5e9689'),
            'path' => '22',
            'w' => 728,
            'h' => 90,
        ],
        'banner468' => [ // 468x60
            'format' => 'iframe',
            'key' => env('ADS_SLOT_BANNER468', '9e328ceffc56f1342bfcb82f1f9338a0'),
            'path' => '22',
            'w' => 468,
            'h' => 60,
        ],
        'rectangle' => [ // 300x250
            'format' => 'iframe',
            'key' => env('ADS_SLOT_RECTANGLE', '149263154fdc6bd87533aa26c2711be5'),
            'path' => '22',
            'w' => 300,
            'h' => 250,
        ],
        'mobile' => [ // 320x50
            'format' => 'iframe',
            'key' => env('ADS_SLOT_MOBILE', '07fe88a02737dfc240f220bce1eca4cb'),
            'path' => '22',
            'w' => 320,
            'h' => 50,
        ],
        'mobile_feed' => [ // 300x100 compact in-feed
            'format' => 'iframe',
            'key' => env('ADS_SLOT_MOBILE_FEED', ''),
            'path' => '22',
            'w' => 300,
            'h' => 100,
        ],
        'mobile_inline' => [ // 320x50 inline interstitial-ish
            'format' => 'iframe',
            'key' => env('ADS_SLOT_MOBILE_INLINE', ''),
            'path' => '22',
            'w' => 320,
            'h' => 50,
        ],
        'mobile_mini' => [ // 300x50 compact strip
            'format' => 'iframe',
            'key' => env('ADS_SLOT_MOBILE_MINI', ''),
            'path' => '22',
            'w' => 300,
            'h' => 50,
        ],
        'mobile_strip' => [ // 320x50 overflow strip
            'format' => 'iframe',
            'key' => env('ADS_SLOT_MOBILE_STRIP', ''),
            'path' => '22',
            'w' => 320,
            'h' => 50,
        ],
        'mobile_native_small' => [ // 320x100 compact native
            'format' => 'native',
            'key' => env('ADS_SLOT_MOBILE_NATIVE_SMALL', ''),
            'path' => '21',
            'w' => 320,
            'h' => 100,
        ],
        'mobile_banner' => [ // 320x50 banner
            'format' => 'iframe',
            'key' => env('ADS_SLOT_MOBILE_BANNER', ''),
            'path' => '22',
            'w' => 320,
            'h' => 50,
        ],
        'mobile_card' => [ // 300x100 card native
            'format' => 'native',
            'key' => env('ADS_SLOT_MOBILE_CARD', ''),
            'path' => '21',
            'w' => 300,
            'h' => 100,
        ],
        'mobile_float' => [ // 320x50 floating banner
            'format' => 'iframe',
            'key' => env('ADS_SLOT_MOBILE_FLOAT', ''),
            'path' => '22',
            'w' => 320,
            'h' => 50,
        ],
        // 160x300 skyscraper -- the client's first emailed snippet duplicated the
        // 320x50 code by mistake; this key is the corrected embed for the 160x300 zone.
        'skyscraper' => [ // 160x300
            'format' => 'iframe',
            'key' => env('ADS_SLOT_SKYSCRAPER', '888932ea1f0a9f1aac53eb56d7f9155e'),
            'path' => '22',
            'w' => 160,
            'h' => 300,
        ],
        'skyscraper_left' => [ // 160x300
            'format' => 'iframe',
            'key' => env('ADS_SLOT_SKYSCRAPER_LEFT', ''),
            'path' => '22',
            'w' => 160,
            'h' => 300,
        ],
    ],

    'placements' => [
        'anchor' => ['mobile'],
        'float' => ['mobile_float', 'mobile'],
        'infeed' => ['mobile_feed', 'mobile_card', 'mobile_native_small'],
        'inline' => ['mobile_inline', 'mobile_mini', 'mobile_banner'],
        'page_top' => ['leaderboard'],
        'page_mid' => ['banner468', 'rectangle', 'native'],
    ],
];
