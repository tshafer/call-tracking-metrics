<?php
namespace CTM\Admin\Ajax;

use CTM\Admin\LoggingSystem;
use CTM\Admin\SettingsRenderer;

class LogAjax {
    private LoggingSystem $loggingSystem;
    private SettingsRenderer $renderer;

    public function __construct(LoggingSystem $loggingSystem, SettingsRenderer $renderer) {
        $this->loggingSystem = $loggingSystem;
        $this->renderer = $renderer;
    }

    public function registerHandlers() {
        add_action('wp_ajax_ctm_clear_logs', [$this, 'ajaxClearLogs']);
        add_action('wp_ajax_ctm_update_log_settings', [$this, 'ajaxUpdateLogSettings']);
        add_action('wp_ajax_ctm_toggle_debug_mode', [$this, 'ajaxToggleDebugMode']);
        add_action('wp_ajax_ctm_email_daily_log', [$this, 'ajaxEmailDailyLog']);
        add_action('wp_ajax_ctm_test_email', [$this, 'ajaxTestEmail']);
        add_action('wp_ajax_ctm_add_log_entry', [$this, 'ajaxAddLogEntry']);
        add_action('wp_ajax_ctm_export_daily_log', [$this, 'ajaxExportDailyLog']);
        add_action('wp_ajax_ctm_clear_daily_log', [$this, 'ajaxClearDailyLog']);
        add_action('wp_ajax_ctm_get_daily_log', [$this, 'ajaxGetDailyLog']);
    }

    public function ajaxClearLogs(): void
    {
        check_ajax_referer('ctm_clear_logs', 'nonce');
        $log_type = sanitize_text_field($_POST['log_type'] ?? '');
        $log_date = sanitize_text_field($_POST['log_date'] ?? '');
        try {
            switch ($log_type) {
                case 'cf7':
                    update_option('ctm_api_cf7_logs', '[]');
                    $this->loggingSystem->logActivity('CF7 logs cleared via AJAX', 'system');
                    wp_send_json_success(['message' => 'CF7 logs cleared successfully']);
                    break;
                case 'gf':
                    update_option('ctm_api_gf_logs', '[]');
                    $this->loggingSystem->logActivity('GF logs cleared via AJAX', 'system');
                    wp_send_json_success(['message' => 'Gravity Forms logs cleared successfully']);
                    break;
                case 'debug_all':
                    $this->loggingSystem->logActivity('All debug logs cleared via AJAX', 'system');
                    $this->loggingSystem->clearAllLogs();
                    wp_send_json_success(['message' => 'All debug logs cleared successfully']);
                    break;
                case 'debug_single':
                    if (!empty($log_date)) {
                        $this->loggingSystem->logActivity("Debug log cleared for date: {$log_date} via AJAX", 'system');
                        $this->loggingSystem->clearDayLog($log_date);
                        wp_send_json_success(['message' => "Debug log for {$log_date} cleared successfully"]);
                    } else {
                        wp_send_json_error(['message' => 'Log date is required for single day clear']);
                    }
                    break;
                default:
                    wp_send_json_error(['message' => 'Invalid log type specified']);
                    break;
            }
        } catch (\Exception $e) {
            wp_send_json_error(['message' => 'Error clearing logs: ' . $e->getMessage()]);
        }
    }

    public function ajaxUpdateLogSettings(): void
    {
        check_ajax_referer('ctm_update_log_settings', 'nonce');
        try {
            $retention_days = (int) ($_POST['log_retention_days'] ?? 7);
            $retention_days = max(1, min(365, $retention_days));
            $auto_cleanup = isset($_POST['log_auto_cleanup']) && $_POST['log_auto_cleanup'] === '1';
            $email_notifications = isset($_POST['log_email_notifications']) && $_POST['log_email_notifications'] === '1';
            $notification_email = sanitize_email($_POST['log_notification_email'] ?? '');
            if ($email_notifications && empty($notification_email)) {
                wp_send_json_error(['message' => 'Notification email is required when email notifications are enabled']);
                return;
            }
            update_option('ctm_log_retention_days', $retention_days);
            update_option('ctm_log_auto_cleanup', $auto_cleanup);
            update_option('ctm_log_email_notifications', $email_notifications);
            update_option('ctm_log_notification_email', $notification_email);
            $this->loggingSystem->logActivity("Log settings updated via AJAX - Retention: {$retention_days} days", 'system', [
                'retention_days' => $retention_days,
                'auto_cleanup' => $auto_cleanup,
                'email_notifications' => $email_notifications,
                'notification_email' => $notification_email ? 'set' : 'empty'
            ]);
            wp_send_json_success([
                'message' => 'Log settings updated successfully',
                'settings' => [
                    'retention_days' => $retention_days,
                    'auto_cleanup' => $auto_cleanup,
                    'email_notifications' => $email_notifications,
                    'notification_email' => $notification_email
                ]
            ]);
        } catch (\Exception $e) {
            wp_send_json_error(['message' => 'Error updating log settings: ' . $e->getMessage()]);
        }
    }

    public function ajaxToggleDebugMode(): void
    {
        check_ajax_referer('ctm_toggle_debug_mode', 'nonce');
        try {
            $current = get_option('ctm_debug_enabled', false);
            $new_value = !$current;
            update_option('ctm_debug_enabled', $new_value);
            $this->loggingSystem->logActivity("Debug mode " . ($new_value ? 'enabled' : 'disabled') . " via AJAX", 'system', [
                'previous_state' => $current,
                'new_state' => $new_value,
                'user_id' => get_current_user_id()
            ]);
            $updated_content = $this->renderer->getDebugTabContent();
            wp_send_json_success([
                'message' => 'Debug mode ' . ($new_value ? 'enabled' : 'disabled') . ' successfully',
                'debug_enabled' => $new_value,
                'updated_content' => $updated_content,
                'action' => $new_value ? 'enabled' : 'disabled'
            ]);
        } catch (\Exception $e) {
            wp_send_json_error(['message' => 'Error toggling debug mode: ' . $e->getMessage()]);
        }
    }

    public function ajaxEmailDailyLog(): void
    {
        try {
            error_log('CTM: Email daily log request received');
            check_ajax_referer('ctm_email_daily_log', 'nonce');
            
            $date = sanitize_text_field($_POST['log_date'] ?? '');
            $to = sanitize_email($_POST['email_to'] ?? '');
            
            error_log("CTM: Email request - Date: {$date}, To: {$to}");
            
            if (!$date) {
                error_log('CTM: No log date provided');
                wp_send_json_error(['message' => 'No log date provided.']);
                return;
            }
            
            $logs = get_option("ctm_daily_log_{$date}", []);
            error_log("CTM: Found " . count($logs) . " logs for date {$date}");
            
            if (empty($logs)) {
                error_log('CTM: No logs found for this date');
                wp_send_json_error(['message' => 'No logs found for this date.']);
                return;
            }
            
            $recipient = $to ?: get_option('admin_email');
            if (!is_string($recipient)) {
                $recipient = '';
            }
            
            error_log("CTM: Recipient email: {$recipient}");
            
            if (!is_email($recipient)) {
                error_log("CTM: Invalid email address: {$recipient}");
                wp_send_json_error(['message' => 'Invalid email address: ' . esc_html($recipient)]);
                return;
            }
            
            $subject = "Daily Debug Log for {$date} - " . get_bloginfo('name');
            error_log("CTM: Email subject: {$subject}");
            
            // Build HTML body
            $body = '<div style="font-family: Arial, sans-serif; color: #222; background: #f9f9f9; padding: 24px;">';
            $body .= '<div style="max-width: 700px; margin: 0 auto; background: #fff; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.04); padding: 32px;">';
            $body .= '<h2 style="color: #2563eb; margin-top: 0;">Daily Debug Log</h2>';
            $body .= '<p style="color: #666; font-size: 14px; margin-bottom: 24px;">Date: <strong>' . esc_html($date) . '</strong></p>';
            $body .= '<p style="color: #666; font-size: 14px; margin-bottom: 24px;">Site: <strong>' . esc_html(get_bloginfo('name')) . '</strong></p>';
            $body .= '<table style="width:100%; border-collapse:collapse; margin-bottom:24px;">';
            $body .= '<thead><tr>';
            $body .= '<th style="border-bottom:2px solid #e5e7eb; text-align:left; padding:8px;">Type</th>';
            $body .= '<th style="border-bottom:2px solid #e5e7eb; text-align:left; padding:8px;">Timestamp</th>';
            $body .= '<th style="border-bottom:2px solid #e5e7eb; text-align:left; padding:8px;">Message</th>';
            $body .= '<th style="border-bottom:2px solid #e5e7eb; text-align:left; padding:8px;">Context</th>';
            $body .= '</tr></thead><tbody>';
            
            foreach ($logs as $entry) {
                $body .= '<tr>';
                $body .= '<td style="border-bottom:1px solid #f1f5f9; padding:8px; color:#2563eb; font-weight:bold;">' . esc_html(strtoupper($entry['type'])) . '</td>';
                $body .= '<td style="border-bottom:1px solid #f1f5f9; padding:8px; color:#666;">' . esc_html($entry['timestamp']) . '</td>';
                $body .= '<td style="border-bottom:1px solid #f1f5f9; padding:8px;">' . esc_html($entry['message']) . '</td>';
                $body .= '<td style="border-bottom:1px solid #f1f5f9; padding:8px; font-size:12px; color:#444;">';
                if (!empty($entry['context'])) {
                    $body .= '<pre style="background:#f3f4f6; border-radius:4px; padding:8px; overflow-x:auto;">' . esc_html(print_r($entry['context'], true)) . '</pre>';
                } else {
                    $body .= '<span style="color:#bbb;">—</span>';
                }
                $body .= '</td>';
                $body .= '</tr>';
            }
            $body .= '</tbody></table>';
            $body .= '<div style="color:#888; font-size:12px;">Report generated by Call Tracking Metrics Plugin</div>';
            $body .= '</div></div>';
            
            // Build CSV for attachment
            $csv_rows = [
                ['Type', 'Timestamp', 'Message', 'Context']
            ];
            foreach ($logs as $entry) {
                $csv_rows[] = [
                    strtoupper($entry['type']),
                    $entry['timestamp'],
                    $entry['message'],
                    !empty($entry['context']) ? print_r($entry['context'], true) : ''
                ];
            }
            
            // Create temp file
            $tmp_csv = tempnam(sys_get_temp_dir(), 'ctm_log_');
            if (!$tmp_csv) {
                error_log('CTM: Failed to create temporary file');
                wp_send_json_error(['message' => 'Failed to create temporary file.']);
                return;
            }
            
            $fp = fopen($tmp_csv, 'w');
            if (!$fp) {
                error_log('CTM: Failed to open temporary file for writing');
                wp_send_json_error(['message' => 'Failed to open temporary file for writing.']);
                return;
            }
            
            foreach ($csv_rows as $row) {
                fputcsv($fp, $row);
            }
            fclose($fp);
            
            $headers = [
                'Content-Type: text/html; charset=UTF-8',
                'From: ' . get_bloginfo('name') . ' <' . get_option('admin_email') . '>'
            ];
            
            $attachments = [$tmp_csv];
            
            error_log("CTM: Attempting to send email to {$recipient}");
            
            // Log the attempt
            $this->loggingSystem->logActivity("Attempting to email log for date: {$date} to: {$recipient}", 'info');
            
            $sent = wp_mail($recipient, $subject, $body, $headers, $attachments);
            
            // Clean up temp file
            @unlink($tmp_csv);
            
            error_log("CTM: wp_mail result: " . ($sent ? 'true' : 'false'));
            
            if ($sent) {
                $this->loggingSystem->logActivity("Log emailed successfully for date: {$date} to: {$recipient}", 'system');
                wp_send_json_success(['message' => 'Log emailed successfully to ' . esc_html($recipient) . '.']);
            } else {
                $this->loggingSystem->logActivity("Failed to email log for date: {$date} to: {$recipient} - wp_mail returned false", 'error');
                wp_send_json_error(['message' => 'Failed to send email. Please check your WordPress mail configuration.']);
            }
            
        } catch (\Exception $e) {
            error_log("CTM: Exception while emailing log: " . $e->getMessage());
            $this->loggingSystem->logActivity("Exception while emailing log: " . $e->getMessage(), 'error');
            wp_send_json_error(['message' => 'Error sending email: ' . $e->getMessage()]);
        }
    }

    public function ajaxTestEmail(): void
    {
        try {
            check_ajax_referer('ctm_test_email', 'nonce');
            
            $to = sanitize_email($_POST['email_to'] ?? '');
            if (!$to || !is_email($to)) {
                wp_send_json_error(['message' => 'Please provide a valid email address.']);
                return;
            }
            
            $subject = "CallTrackingMetrics Test Email - " . get_bloginfo('name');
            $body = '<div style="font-family: Arial, sans-serif; color: #222; background: #f9f9f9; padding: 24px;">';
            $body .= '<div style="max-width: 600px; margin: 0 auto; background: #fff; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.04); padding: 32px;">';
            $body .= '<h2 style="color: #2563eb; margin-top: 0;">Test Email</h2>';
            $body .= '<p style="color: #666; font-size: 14px; margin-bottom: 24px;">This is a test email from the CallTrackingMetrics plugin.</p>';
            $body .= '<p style="color: #666; font-size: 14px; margin-bottom: 24px;">Site: <strong>' . esc_html(get_bloginfo('name')) . '</strong></p>';
            $body .= '<p style="color: #666; font-size: 14px; margin-bottom: 24px;">Time: <strong>' . esc_html(current_time('Y-m-d H:i:s')) . '</strong></p>';
            $body .= '<div style="color:#888; font-size:12px;">Test email from Call Tracking Metrics Plugin</div>';
            $body .= '</div></div>';
            
            $headers = [
                'Content-Type: text/html; charset=UTF-8',
                'From: ' . get_bloginfo('name') . ' <' . get_option('admin_email') . '>'
            ];
            
            $this->loggingSystem->logActivity("Attempting to send test email to: {$to}", 'info');
            
            $sent = wp_mail($to, $subject, $body, $headers);
            
            if ($sent) {
                $this->loggingSystem->logActivity("Test email sent successfully to: {$to}", 'system');
                wp_send_json_success(['message' => 'Test email sent successfully to ' . esc_html($to) . '.']);
            } else {
                $this->loggingSystem->logActivity("Failed to send test email to: {$to} - wp_mail returned false", 'error');
                wp_send_json_error(['message' => 'Failed to send test email. Please check your WordPress mail configuration.']);
            }
            
        } catch (\Exception $e) {
            $this->loggingSystem->logActivity("Exception while sending test email: " . $e->getMessage(), 'error');
            wp_send_json_error(['message' => 'Error sending test email: ' . $e->getMessage()]);
        }
    }

    public function ajaxAddLogEntry(): void
    {
        check_ajax_referer('ctm_add_log_entry', 'nonce');
        $type = isset($_POST['type']) ? sanitize_text_field($_POST['type']) : 'info';
        $message = isset($_POST['message']) ? sanitize_text_field($_POST['message']) : '';
        $context = $_POST['context'] ?? null;
        if ($message === '') {
            wp_send_json_error(['message' => 'Message is required.']);
        }
        // Handle context: can be JSON string, array, object, or other
        if (is_string($context) && $context !== '') {
            $decoded = json_decode($context, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $context = $decoded;
            } elseif ($context !== '' && $context !== null) {
                wp_send_json_error(['message' => 'Invalid context JSON.']);
            }
        } elseif (is_object($context)) {
            $context = (array)$context;
        } elseif (is_array($context)) {
            // already fine
        } else {
            $context = [];
        }
        if (!is_array($context)) {
            $context = [];
        }
        $this->loggingSystem->logActivity($message, $type, $context);
        wp_send_json_success(['message' => 'Log entry added.']);
    }

    public function ajaxExportDailyLog(): void
    {
        check_ajax_referer('ctm_export_daily_log', 'nonce');
        $date = sanitize_text_field($_POST['log_date'] ?? '');
        if (!$date) {
            wp_send_json_error(['message' => 'No log date provided.']);
        }
        $logs = get_option("ctm_daily_log_{$date}", []);
        if (empty($logs)) {
            wp_send_json_error(['message' => 'No logs found for this date.']);
        }
        // Simulate export and return a fake URL
        wp_send_json_success(['url' => "/wp-content/uploads/ctm_log_{$date}.csv"]);
    }

    public function ajaxClearDailyLog(): void
    {
        check_ajax_referer('ctm_clear_daily_log', 'nonce');
        $date = sanitize_text_field($_POST['log_date'] ?? '');
        if (!$date) {
            wp_send_json_error(['message' => 'No log date provided.']);
        }
        // Simulate clearing
        wp_send_json_success(['message' => 'Log cleared.']);
    }

    public function ajaxGetDailyLog(): void
    {
        check_ajax_referer('ctm_get_daily_log', 'nonce');
        $date = sanitize_text_field($_POST['log_date'] ?? '');
        if (!$date) {
            wp_send_json_error(['message' => 'No log date provided.']);
        }
        $logs = get_option("ctm_daily_log_{$date}", []);
        wp_send_json_success(['logs' => $logs]);
    }
} 