<?php
/**
 * GeneratePress.
 *
 * Please do not make any edits to this file. All edits should be done in a child theme.
 *
 * @package GeneratePress
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Set our theme version.
define( 'GENERATE_VERSION', '3.5.1' );

if ( ! function_exists( 'generate_setup' ) ) {
	add_action( 'after_setup_theme', 'generate_setup' );
	/**
	 * Sets up theme defaults and registers support for various WordPress features.
	 *
	 * @since 0.1
	 */
	function generate_setup() {
		// Make theme available for translation.
		load_theme_textdomain( 'generatepress' );

		// Add theme support for various features.
		add_theme_support( 'automatic-feed-links' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'post-formats', array( 'aside', 'image', 'video', 'quote', 'link', 'status' ) );
		add_theme_support( 'woocommerce' );
		add_theme_support( 'title-tag' );
		add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'script', 'style' ) );
		add_theme_support( 'customize-selective-refresh-widgets' );
		add_theme_support( 'align-wide' );
		add_theme_support( 'responsive-embeds' );

		$color_palette = generate_get_editor_color_palette();

		if ( ! empty( $color_palette ) ) {
			add_theme_support( 'editor-color-palette', $color_palette );
		}

		add_theme_support(
			'custom-logo',
			array(
				'height' => 70,
				'width' => 350,
				'flex-height' => true,
				'flex-width' => true,
			)
		);

		// Register primary menu.
		register_nav_menus(
			array(
				'primary' => __( 'Primary Menu', 'generatepress' ),
			)
		);

		/**
		 * Set the content width to something large
		 * We set a more accurate width in generate_smart_content_width()
		 */
		global $content_width;
		if ( ! isset( $content_width ) ) {
			$content_width = 1200; /* pixels */
		}

		// Add editor styles to the block editor.
		add_theme_support( 'editor-styles' );

		$editor_styles = apply_filters(
			'generate_editor_styles',
			array(
				'assets/css/admin/block-editor.css',
			)
		);

		add_editor_style( $editor_styles );
	}
}

/**
 * Get all necessary theme files
 */
$theme_dir = get_template_directory();

require $theme_dir . '/inc/theme-functions.php';
require $theme_dir . '/inc/defaults.php';
require $theme_dir . '/inc/class-css.php';
require $theme_dir . '/inc/css-output.php';
require $theme_dir . '/inc/general.php';
require $theme_dir . '/inc/customizer.php';
require $theme_dir . '/inc/markup.php';
require $theme_dir . '/inc/typography.php';
require $theme_dir . '/inc/plugin-compat.php';
require $theme_dir . '/inc/block-editor.php';
require $theme_dir . '/inc/class-typography.php';
require $theme_dir . '/inc/class-typography-migration.php';
require $theme_dir . '/inc/class-html-attributes.php';
require $theme_dir . '/inc/class-theme-update.php';
require $theme_dir . '/inc/class-rest.php';
require $theme_dir . '/inc/deprecated.php';

if ( is_admin() ) {
	require $theme_dir . '/inc/meta-box.php';
	require $theme_dir . '/inc/class-dashboard.php';
}

/**
 * Load our theme structure
 */
require $theme_dir . '/inc/structure/archives.php';
require $theme_dir . '/inc/structure/comments.php';
require $theme_dir . '/inc/structure/featured-images.php';
require $theme_dir . '/inc/structure/footer.php';
require $theme_dir . '/inc/structure/header.php';
require $theme_dir . '/inc/structure/navigation.php';
require $theme_dir . '/inc/structure/post-meta.php';
require $theme_dir . '/inc/structure/sidebars.php';
require $theme_dir . '/inc/structure/search-modal.php';


function external_php_db_connection() {
    static $conn = null;

    if ($conn === null) {
        $conn = new mysqli(
            'localhost',          // DB HOST
            'bpdefxwugd',        // PHP site DB user
            'TGgpxy4d5U',    // PHP site DB password
            'bpdefxwugd'         // PHP site DB name
        );

        if ($conn->connect_error) {
            return false;
        }
    }
    return $conn;
}

add_shortcode('petition_categories', function () {

    $db = external_php_db_connection();
    if (!$db) return '';

    $db->set_charset('utf8mb4');

    $sql = "
        SELECT id, name
        FROM petition_categories
        WHERE prefix = 'ws_'
        ORDER BY id ASC
    ";

    $result = $db->query($sql);
    if (!$result || $result->num_rows === 0) return '';

    ob_start(); ?>
    
    <div class="petition-category-grid">
        <?php while ($row = $result->fetch_assoc()):
            $slug = sanitize_title($row['name']);
           $url = 'https://www.freecause.com/petitions/category-' . $slug . '-' . $row['id'];

        ?>
            <div class="petition-category-card">
                <a href="<?php echo esc_url($url); ?>">
                    <?php echo esc_html($row['name']); ?>
                </a>
            </div>
        <?php endwhile; ?>
    </div>

    <?php
    return ob_get_clean();
});

add_shortcode('featured_petition_random', function () {

    $db = external_php_db_connection();
    if (!$db) return '<strong>Debug:</strong> Could not connect to the database.';

    $db->set_charset('utf8mb4');

    // 🔥 IDs to pick from
    $ids = [80460, 80106, 75241];

    // Pick one random ID on every page load
    $random_id = $ids[array_rand($ids)];

    // Fetch only that petition
    $sql = "
        SELECT *
        FROM petitions
        WHERE id = " . intval($random_id) . "
        LIMIT 1
    ";

    $result = $db->query($sql);

    if (!$result) {
        return '<strong>Debug:</strong> Query failed. Error: ' . $db->error;
    }

    if ($result->num_rows === 0) {
        return '<strong>Debug:</strong> No petition found with ID ' . $random_id;
    }

    $featured = $result->fetch_object();

    // ---- URL ---- 
$slug = $featured->slug; // your slug column
$url  = "https://www.freecause.com/petition/{$slug}/{$featured->id}/";


    // ---- Image ----
    $pic = !empty($featured->pic)
        ? "https://www.freecause.com/pics/en_US/" . rawurlencode($featured->pic)
        : '';

    // ---- Progress ----
    $progress = ($featured->goal > 0)
        ? ($featured->signatures_count / $featured->goal) * 100
        : 0;

    ob_start(); ?>

    <div class="row featured-row">
        <div class="col-lg-12 mb-25">
            <div class="card featured-box">
                <div class="card-body">

                    <div class="row featured-box-inner vc_row wpb_row vc_inner vc_row-fluid">
                        <div class="col-sm-6 mb-4 wpb_column vc_column_container vc_col-sm-6">
							<div class="vc_column-inner">
								
                            <?php if ($pic): ?>
                                <img src="<?php echo esc_url($pic); ?>" class="img-fluid"
                                     alt="<?php echo esc_attr($featured->title); ?>">
                            <?php endif; ?>
								
							</div>
                        </div>

                        <div class="col-sm-6 mb-4 wpb_column vc_column_container vc_col-sm-6">
							<div class="vc_column-inner">
                            <h5><?php echo esc_html($featured->title); ?></h5>

                            <p>
                                <strong>Petition target:</strong>
                                <?php echo esc_html($featured->target ?? ''); ?>
                            </p>

                            <p>
                                <?php echo esc_html(wp_trim_words(strip_tags($featured->text), 80)); ?>…
                            </p>
								</div>
                        </div>

<!--                         <a href="<?php echo esc_url($url); ?>">Read more</a> -->
                    </div>

                    <div class="goal-progress mb-3">
                        <div class="progress mb-2">
                            <div class="progress-bar"
                                 style="width: <?php echo esc_attr($progress); ?>%;"></div>
                        </div>

                        <div class="d-flex justify-content-between text-muted">
                            <span><?php echo number_format($featured->signatures_count); ?> signatures</span>
                            <span>Goal: <?php echo number_format($featured->goal); ?></span>
                        </div>
                    </div>

                    <a href="<?php echo esc_url($url); ?>" class=" custom-btn-clsbtn btn-primary vc_general vc_btn3 vc_btn3-size-md vc_btn3-shape-rounded vc_btn3-style-modern vc_btn3-color-grey">
                        Read more
                    </a>

                </div>
            </div>
        </div>
    </div>

    <?php
    return ob_get_clean();
});


add_shortcode('recent_activities', function() {
    $db = external_php_db_connection();
    if (!$db) return '<p>DB connection failed</p>';
    $db->set_charset('utf8mb4');

    // Fetch latest 20 signatures
    $sql = "SELECT * FROM signatures ORDER BY dt DESC LIMIT 20";
    $result = $db->query($sql);
    if (!$result) return '<p>Query failed: ' . $db->error . '</p>';

    if ($result->num_rows === 0) return '<p>No recent activities found</p>';

    ob_start(); ?>
    <div class="most-grid">
        <ul class="recent-activities most-listing">
            <?php while ($row = $result->fetch_assoc()): ?>
                <?php
                    // Petition title & slug
                    $petition_id = intval($row['petition_id']);
                    $petition_title = '';
                    $petition_slug = '';
                    if ($petition_id) {
                        $p_res = $db->query("SELECT title, slug FROM petitions WHERE id = $petition_id LIMIT 1");
                        if ($p_res && $p_res->num_rows) {
                            $p_row = $p_res->fetch_assoc();
                            $petition_title = $p_row['title'];
                            $petition_slug = $p_row['slug'];
                        }
                    }

                    // User name
                    $user_name = 'Anonymous';
                    $user_id = intval($row['user_id']);
                    if ($user_id) {
                        $u_res = $db->query("SELECT name, surname FROM users WHERE id = $user_id LIMIT 1");
                        if ($u_res && $u_res->num_rows) {
                            $u_row = $u_res->fetch_assoc();
                            $user_name = trim(($u_row['name'] ?? '') . ' ' . ($u_row['surname'] ?? ''));
                            if (!$user_name) $user_name = 'Anonymous';
                        }
                    }

                    // Petition URL
                    $petition_url = "https://www.freecause.com/petition/" . rawurlencode($petition_slug) . "/" . $petition_id . "/";

                    // Time in days
                    $dt_timestamp = strtotime($row['dt']);
$current_timestamp = current_time('timestamp');

$seconds_diff = $current_timestamp - $dt_timestamp;
$days_diff = floor($seconds_diff / DAY_IN_SECONDS);

if ($days_diff < 1) {
    $time_text = 'Today';
} elseif ($days_diff === 1) {
    $time_text = '1 day ago';
} else {
    $time_text = $days_diff . ' days ago';
}

                ?>
                <li>
                    <a href="<?php echo esc_url($petition_url); ?>">
                        <div class="d-flex justify-content-between recent-activities-times mb-1">
                            <div class="user-name-with-status">
                                <p class="fs-14">
                                    <span class="user-name"><?php echo esc_html($user_name); ?></span>
                                    <strong>has signed</strong>
                                </p>
                            </div>
                            <div class="action-time">
                                <span class="red fs-14"><?php echo esc_html($time_text); ?></span>
                            </div>
                        </div>
                        <p class="fs-14"><?php echo esc_html($petition_title); ?></p>
                    </a>
                </li>
            <?php endwhile; ?>
        </ul>
    </div>
    <?php

    return ob_get_clean();
});

add_action('rest_api_init', function () {
    register_rest_route('global/v2', '/footer', [
        'methods'  => 'GET',
        'callback' => 'get_global_footer_html',
    ]);
});

function get_global_footer_html() {

    // 🚫 HARD NO-CACHE (server / CDN / browser)
    nocache_headers();
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('Expires: 0');

    ob_start();

    // ✅ ONLY exact file (no WP footer, no hooks)
    require get_stylesheet_directory() . '/template-parts/footer/footer-global.php';

    $footer_html = ob_get_clean();

    return [
        'html' => $footer_html,
        'time' => time() // 🔍 DEBUG: har hit pe change hona chahiye
    ];
}
