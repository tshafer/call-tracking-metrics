<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Add toast container to all admin pages
add_action('admin_footer', function() {
    echo '<div id="ctm-toast-container" style="position: fixed; top: 1.5rem; right: 1.5rem; z-index: 9999;"></div>';
});

// Enqueue toast.js globally for plugin admin pages
add_action('admin_enqueue_scripts', function() {
    wp_enqueue_script(
        'ctm-toast',
        plugins_url('assets/js/toast.js', __FILE__),
        [],
        null,
        true
    );
});

