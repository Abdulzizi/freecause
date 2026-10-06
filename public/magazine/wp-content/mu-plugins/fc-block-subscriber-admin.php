<?php
/**
 * Plugin Name: FC Block Subscriber Admin
 * Description: Keeps SSO-provisioned subscribers out of wp-admin and hides the admin bar; editorial roles keep full access.
 */

add_action('admin_init', function () {
    if (wp_doing_ajax() || current_user_can('edit_posts') || current_user_can('manage_options')) {
        return;
    }

    wp_safe_redirect(home_url('/'));
    exit;
});

add_filter('show_admin_bar', function ($show) {
    if (current_user_can('edit_posts') || current_user_can('manage_options')) {
        return $show;
    }

    return false;
});

add_filter('allow_password_reset', function ($allow, $userId) {
    if (get_user_meta($userId, 'fc_sso_account', true)) {
        return false;
    }

    return $allow;
}, 10, 2);
