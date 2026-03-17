<?php
/**
 * List of demos json
 *
 * @package Pubnews
 * @since 1.0.0
 */
$demos_array = array(
    'pubnews-one' => [
        'name' => 'Default',
        'type' => 'free',
        'buy_url'=> 'https://blazethemes.com/theme/pubnews-pro/',
        'external_url' => 'https://preview.blazethemes.com/import-files/pubnews/pubnews-one.zip',
        'image' => 'https://blazethemes.com/wp-content/uploads/2024/07/free-one.jpg',
        'preview_url' => 'https://preview.blazethemes.com/pubnews-free-one/',
        'menu_array' => [
            'menu-1' => 'Header Menu',
            'menu-2' => 'Bottom Menu'
        ],
        'home_slug' => '',
        'blog_slug' => '',
        'plugins' => [],
        'tags' => [
            'free'  =>  esc_html__( 'Free', 'pubnews' ),
            'blog'  =>  esc_html__( 'Blog', 'pubnews' )
        ]
    ],
    'pubnews-two' => [
        'name' => 'Two',
        'type' => 'free',
        'buy_url'=> 'https://blazethemes.com/theme/pubnews-pro/',
        'external_url' => 'https://preview.blazethemes.com/import-files/pubnews/pubnews-two.zip',
        'image' => 'https://blazethemes.com/wp-content/uploads/2024/07/free-two.jpg',
        'preview_url' => 'https://preview.blazethemes.com/pubnews-free-two/',
        'menu_array' => [
            'menu-1' => 'Header Menu',
            'menu-2' => 'Bottom Menu'
        ],
        'home_slug' => '',
        'blog_slug' => '',
        'plugins' => [],
        'tags' => [
            'free'  =>  esc_html__( 'Free', 'pubnews' ),
            'pets'  =>  esc_html__( 'Pets', 'pubnews' )
        ]
    ],
    'pubnews-three' => [
        'name' => 'Three',
        'type' => 'free',
        'buy_url'=> 'https://blazethemes.com/theme/pubnews-pro/',
        'external_url' => 'https://preview.blazethemes.com/import-files/pubnews/pubnews-three.zip',
        'image' => 'https://blazethemes.com/wp-content/uploads/2024/07/free-three.jpg',
        'preview_url' => 'https://preview.blazethemes.com/pubnews-free-three/',
        'menu_array' => [
            'menu-1' => 'header-menu'
        ],
        'home_slug' => '',
        'blog_slug' => '',
        'plugins' => [],
        'tags' => [
            'free'  =>  esc_html__( 'Free', 'pubnews' ),
            'blog'  =>  esc_html__( 'Blog', 'pubnews' )
        ]
    ],
    'pubnews-elementor-one' => [
        'name' => 'Elementor One',
        'type' => 'free',
        'buy_url'=> 'https://blazethemes.com/theme/pubnews-pro/',
        'external_url' => 'https://preview.blazethemes.com/import-files/pubnews/pubnews-elementor-one.zip',
        'image' => 'https://blazethemes.com/wp-content/uploads/2024/07/free-one-elemento.jpg',
        'preview_url' => 'https://preview.blazethemes.com/pubnews-elementor-one/',
        'menu_array' => [],
        'home_slug' => 'pubnews-elementor-free-home-one',
        'blog_slug' => '',
        'plugins' => [
            'elementor' => array(
                'name' => 'Elementor',
                'source' => 'wordpress',
                'required' => true,
                'file_path' => 'elementor/elementor.php'
            ),
            'news-kit-elementor-addons' => array(
                'name' => 'News Kit Elementor Addons',
                'source' => 'wordpress',
                'required' => true,
                'file_path' => 'news-kit-elementor-addons/news-kit-elementor-addons.php'
            )
        ],
        'pagebuilder' => array(
            'elementor' => 'Elementor'
        ),
        'tags' => [
            'free'  =>  esc_html__( 'Free', 'pubnews' ),
            'blog'  =>  esc_html__( 'Blog', 'pubnews' ),
            'elementor'  =>  esc_html__( 'Elementor', 'pubnews' )
        ]
    ],
    'pubnews-pro-one' => [
        'name' => 'Default',
        'type' => 'pro',
        'buy_url'=> 'https://blazethemes.com/theme/pubnews-pro/',
        'external_url' => 'https://preview.blazethemes.com/import-files/pubnews/pubnews-pro-one.zip',
        'image' => 'https://blazethemes.com/wp-content/uploads/2024/07/pro-one-1.png',
        'preview_url' => 'https://preview.blazethemes.com/pubnews-pro-one/',
        'menu_array' => [
            'menu-2' => 'Header Menu',
            'menu-3' => 'Bottom Menu'
        ],
        'home_slug' => '',
        'blog_slug' => '',
        'plugins' => [],
        'tags' => [
            'pro'  =>  esc_html__( 'Pro', 'pubnews' ),
            'blog'  =>  esc_html__( 'Blog', 'pubnews' )
        ]
    ],
    'pubnews-pro-two' => [
        'name' => 'Two',
        'type' => 'pro',
        'buy_url'=> 'https://blazethemes.com/theme/pubnews-pro/',
        'external_url' => 'https://preview.blazethemes.com/import-files/pubnews/pubnews-pro-two.zip',
        'image' => 'https://blazethemes.com/wp-content/uploads/2024/07/pro-two.png',
        'preview_url' => 'https://preview.blazethemes.com/pubnews-pro-two/',
        'menu_array' => [
            'menu-2' => 'Header Menu',
            'menu-3' => 'Bottom Menu'
        ],
        'home_slug' => '',
        'blog_slug' => '',
        'plugins' => [],
        'tags' => [
            'pro'  =>  esc_html__( 'Pro', 'pubnews' ),
            'blog'  =>  esc_html__( 'Blog', 'pubnews' )
        ]
    ],
    'pubnews-pro-three' => [
        'name' => 'Three',
        'type' => 'pro',
        'buy_url'=> 'https://blazethemes.com/theme/pubnews-pro/',
        'external_url' => 'https://preview.blazethemes.com/import-files/pubnews/pubnews-pro-three.zip',
        'image' => 'https://blazethemes.com/wp-content/uploads/2024/07/pro-three.png',
        'preview_url' => 'https://preview.blazethemes.com/pubnews-pro-three/',
        'menu_array' => [
            'menu-2' => 'header-menu',
            'menu-3' => 'footer-menu'
        ],
        'home_slug' => '',
        'blog_slug' => '',
        'plugins' => [],
        'tags' => [
            'pro'  =>  esc_html__( 'Pro', 'pubnews' ),
            'blog'  =>  esc_html__( 'Blog', 'pubnews' ),
            'food'  =>  esc_html__( 'Food', 'pubnews' )
        ]
    ],
    'pubnews-pro-four' => [
        'name' => 'Four',
        'type' => 'pro',
        'buy_url'=> 'https://blazethemes.com/theme/pubnews-pro/',
        'external_url' => 'https://preview.blazethemes.com/import-files/pubnews/pubnews-pro-four.zip',
        'image' => 'https://blazethemes.com/wp-content/uploads/2024/07/pro-four.jpg',
        'preview_url' => 'https://preview.blazethemes.com/pubnews-pro-four/',
        'menu_array' => [
            'menu-2' => 'header-menu'
        ],
        'home_slug' => '',
        'blog_slug' => '',
        'plugins' => [],
        'tags' => [
            'pro'  =>  esc_html__( 'Pro', 'pubnews' ),
            'blog'  =>  esc_html__( 'Blog', 'pubnews' ),
            'gadgets'  =>  esc_html__( 'Gadgets', 'pubnews' )
        ]
    ],
    'pubnews-pro-five' => [
        'name' => 'Five',
        'type' => 'pro',
        'buy_url'=> 'https://blazethemes.com/theme/pubnews-pro/',
        'external_url' => 'https://preview.blazethemes.com/import-files/pubnews/pubnews-pro-five.zip',
        'image' => 'https://blazethemes.com/wp-content/uploads/2024/07/pro-five.jpg',
        'preview_url' => 'https://preview.blazethemes.com/pubnews-pro-five/',
        'menu_array' => [
            'menu-2' => 'header-menu',
            'menu-3' => 'footer-menu'
        ],
        'home_slug' => '',
        'blog_slug' => '',
        'plugins' => [],
        'tags' => [
            'pro'  =>  esc_html__( 'Pro', 'pubnews' ),
            'blog'  =>  esc_html__( 'Blog', 'pubnews' )
        ]
    ],
    'pubnews-pro-six' => [
        'name' => 'Six',
        'type' => 'pro',
        'buy_url'=> 'https://blazethemes.com/theme/pubnews-pro/',
        'external_url' => 'https://preview.blazethemes.com/import-files/pubnews/pubnews-pro-six.zip',
        'image' => 'https://blazethemes.com/wp-content/uploads/2024/07/pro-six.jpg',
        'preview_url' => 'https://preview.blazethemes.com/pubnews-pro-six/',
        'menu_array' => [
            'menu-2' => 'Header Menu',
            'menu-3' => 'Bottom Menu',
        ],
        'home_slug' => '',
        'blog_slug' => '',
        'plugins' => [],
        'tags' => [
            'pro'  =>  esc_html__( 'Pro', 'pubnews' ),
            'blog'  =>  esc_html__( 'Blog', 'pubnews' )
        ]
    ],
    'pubnews-elementor-pro-one' => [
        'name' => 'Elementor',
        'type' => 'pro',
        'buy_url'=> 'https://blazethemes.com/theme/pubnews-pro/',
        'external_url' => 'https://preview.blazethemes.com/import-files/pubnews/pubnews-elementor-pro-one.zip',
        'image' => 'https://blazethemes.com/wp-content/uploads/2024/07/pro-one-elementor.jpg',
        'preview_url' => 'https://preview.blazethemes.com/pubnews-elementor-pro-one/',
        'menu_array' => [],
        'home_slug' => 'pubnews-elementor-pro-home-one',
        'blog_slug' => '',
        'plugins' => [
            'elementor' => array(
                'name' => 'Elementor',
                'source' => 'wordpress',
                'file_path' => 'elementor/elementor.php'
            ),
            'news-kit-elementor-addons' => array(
                'name' => 'News Kit Elementor Addons',
                'source' => 'wordpress',
                'file_path' => 'news-kit-elementor-addons/news-kit-elementor-addons.php'
            )
        ],
        'pagebuilder' => array(
            'elementor' => 'Elementor'
        ),
        'tags' => [
            'pro'   =>  esc_html__( 'Pro', 'pubnews' ),
            'blog'  =>  esc_html__( 'Blog', 'pubnews' ),
            'elementor'  =>  esc_html__( 'Elementor', 'pubnews' )
        ]
    ],
    'pubnews-elementor-pro-two' => [
        'name' => 'Elementor',
        'type' => 'pro',
        'buy_url'=> 'https://blazethemes.com/theme/pubnews-pro/',
        'external_url' => 'https://preview.blazethemes.com/import-files/pubnews/pubnews-elementor-pro-two.zip',
        'image' => 'https://blazethemes.com/wp-content/uploads/2024/07/pro-two-elementor.jpg',
        'preview_url' => 'https://preview.blazethemes.com/pubnews-elementor-pro-two/',
        'menu_array' => [],
        'home_slug' => 'pubnews-elementor-pro-home-two',
        'blog_slug' => '',
        'plugins' => [
            'elementor' => array(
                'name' => 'Elementor',
                'source' => 'wordpress',
                'file_path' => 'elementor/elementor.php'
            ),
            'news-kit-elementor-addons' => array(
                'name' => 'News Kit Elementor Addons',
                'source' => 'wordpress',
                'file_path' => 'news-kit-elementor-addons/news-kit-elementor-addons.php'
            )
        ],
        'pagebuilder' => array(
            'elementor' => 'Elementor'
        ),
        'tags' => [
            'pro'   =>  esc_html__( 'Pro', 'pubnews' ),
            'blog'  =>  esc_html__( 'Blog', 'pubnews' ),
            'elementor'  =>  esc_html__( 'Elementor', 'pubnews' )
        ]
    ]
);
return apply_filters( 'pubnews__demos_array_filter', $demos_array );