<?php
/**
 * Plugin Name: FC Magazine Logo Home Link
 * Description: Sends the magazine header logo to the main site instead of the magazine home.
 */

add_filter('generate_logo_href', function () {
    return '/';
});
