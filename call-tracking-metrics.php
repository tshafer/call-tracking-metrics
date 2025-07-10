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

add_action('wp_ajax_ctm_email_daily_log', function() {
    check_ajax_referer('ctm_email_daily_log', 'nonce');
    $date = sanitize_text_field($_POST['log_date'] ?? '');
    $to = sanitize_email($_POST['to'] ?? '');
    if (!$date) {
        wp_send_json_error(['message' => 'No log date provided.']);
    }
    $logs = get_option("ctm_daily_log_{$date}", []);
    if (empty($logs)) {
        wp_send_json_error(['message' => 'No logs found for this date.']);
    }
    $recipient = $to ?: get_option('admin_email');
    if (!is_email($recipient)) {
        wp_send_json_error(['message' => 'Invalid email address.']);
    }
    $subject = "Daily Debug Log for {$date}";
    $body = '';
    foreach ($logs as $entry) {
        $body .= strtoupper($entry['type']) . ' | ' . $entry['timestamp'] . ' | ' . $entry['message'] . "\n";
        if (!empty($entry['context'])) {
            $body .= print_r($entry['context'], true) . "\n";
        }
        $body .= "\n";
    }
    $headers = ['Content-Type: text/plain; charset=UTF-8'];
    $sent = wp_mail($recipient, $subject, $body, $headers);
    if ($sent) {
        wp_send_json_success(['message' => 'Log emailed to ' . esc_html($recipient) . '.']);
    } else {
        wp_send_json_error(['message' => 'Failed to send email.']);
    }
});

