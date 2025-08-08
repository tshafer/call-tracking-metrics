<?php
/**
 * Log AJAX Handler
 * 
 * This file contains the LogAjax class which handles AJAX requests related to
 * logging operations including log retrieval, clearing logs, and log management.
 * 
 * @package     CallTrackingMetrics
 * @subpackage  Admin\Ajax
 * @author      CallTrackingMetrics Team
 * @copyright   2024 CallTrackingMetrics
 * @license     GPL-2.0+
 * @version     2.0.0
 * @link        https://calltrackingmetrics.com
 * @since       1.0.0
 */

namespace CTM\Admin\Ajax;

use CTM\Admin\LoggingSystem;

class LogAjax {
    private LoggingSystem $loggingSystem;

    public function __construct(LoggingSystem $loggingSystem) {
        $this->loggingSystem = $loggingSystem;
    }

    public function registerHandlers() {
        add_action('wp_ajax_ctm_get_form_logs', [$this, 'ajaxGetFormLogs']);
        add_action('wp_ajax_ctm_clear_form_logs', [$this, 'ajaxClearFormLogs']);
        add_action('wp_ajax_ctm_get_form_log_stats', [$this, 'ajaxGetFormLogStats']);
        add_action('wp_ajax_ctm_clear_all_logs', [$this, 'ajaxClearAllLogs']);
        add_action('wp_ajax_ctm_get_recent_errors', [$this, 'ajaxGetRecentErrors']);
        add_action('wp_ajax_ctm_get_error_rate_stats', [$this, 'ajaxGetErrorRateStats']);
        add_action('wp_ajax_ctm_update_log_settings', [$this, 'ajaxUpdateLogSettings']);
    }

    /**
     * Get form-specific logs
     * 
     * @since 2.0.0
     */
    public function ajaxGetFormLogs(): void
    {
        check_ajax_referer('ctm_form_logs', 'nonce');
        
        $form_type = sanitize_text_field($_POST['form_type'] ?? '');
        $form_id = (int) ($_POST['form_id'] ?? 0);
        
        if (empty($form_type) || empty($form_id)) {
            wp_send_json_error(['message' => 'Form type and form ID are required']);
        }
        
        try {
            $logs = $this->loggingSystem->getFormLogs($form_type, $form_id);
            wp_send_json_success([
                'logs' => $logs,
                'count' => count($logs)
            ]);
        } catch (\Exception $e) {
            wp_send_json_error(['message' => 'Failed to get form logs: ' . $e->getMessage()]);
        }
    }

    /**
     * Clear form-specific logs
     * 
     * @since 2.0.0
     */
    public function ajaxClearFormLogs(): void
    {
        check_ajax_referer('ctm_form_logs', 'nonce');
        
        $form_type = sanitize_text_field($_POST['form_type'] ?? '');
        $form_id = (int) ($_POST['form_id'] ?? 0);
        
        if (empty($form_type) || empty($form_id)) {
            wp_send_json_error(['message' => 'Form type and form ID are required']);
        }
        
        try {
            $this->loggingSystem->clearFormLogs($form_type, $form_id);
            wp_send_json_success(['message' => 'Form logs cleared successfully']);
        } catch (\Exception $e) {
            wp_send_json_error(['message' => 'Failed to clear form logs: ' . $e->getMessage()]);
        }
    }

    /**
     * Get form log statistics
     * 
     * @since 2.0.0
     */
    public function ajaxGetFormLogStats(): void
    {
        check_ajax_referer('ctm_form_logs', 'nonce');
        
        try {
            $stats = $this->loggingSystem->getFormLogStatistics();
            wp_send_json_success($stats);
        } catch (\Exception $e) {
            wp_send_json_error(['message' => 'Failed to get form log statistics: ' . $e->getMessage()]);
        }
    }

    /**
     * Clear all logs (daily logs, form logs, and log history)
     * 
     * @since 2.0.0
     */
    public function ajaxClearAllLogs(): void
    {
        check_ajax_referer('ctm_clear_all_logs', 'nonce');
        
        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Insufficient permissions to clear all logs']);
        }
        
        try {
            // Log the action before clearing (so it gets recorded)
            $this->loggingSystem->logActivity('All logs cleared via AJAX request', 'system');
            
            // Clear all logs
            $this->loggingSystem->clearAllLogs();
            
            wp_send_json_success([
                'message' => 'All logs cleared successfully',
                'timestamp' => current_time('mysql')
            ]);
        } catch (\Exception $e) {
            wp_send_json_error(['message' => 'Failed to clear all logs: ' . $e->getMessage()]);
        }
    }

    /**
     * Get recent error logs for display
     * 
     * @since 2.0.0
     */
    public function ajaxGetRecentErrors(): void
    {
        check_ajax_referer('ctm_form_logs', 'nonce');
        
        try {
            $limit = (int) ($_POST['limit'] ?? 10);
            $errors = $this->loggingSystem->getRecentErrors($limit);
            
            wp_send_json_success([
                'errors' => $errors,
                'count' => count($errors)
            ]);
        } catch (\Exception $e) {
            wp_send_json_error(['message' => 'Failed to get recent errors: ' . $e->getMessage()]);
        }
    }

    /**
     * Get error rate statistics
     * 
     * @since 2.0.0
     */
    public function ajaxGetErrorRateStats(): void
    {
        check_ajax_referer('ctm_form_logs', 'nonce');
        
        try {
            $stats = $this->loggingSystem->getErrorRateStats();
            wp_send_json_success($stats);
        } catch (\Exception $e) {
            wp_send_json_error(['message' => 'Failed to get error rate stats: ' . $e->getMessage()]);
        }
    }

    /**
     * Update log settings
     * 
     * @since 2.0.0
     */
    public function ajaxUpdateLogSettings(): void
    {
        check_ajax_referer('ctm_update_log_settings', 'nonce');
        
        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Insufficient permissions to update log settings']);
        }
        
        try {
            // Get form data
            $retention_days = (int) ($_POST['log_retention_days'] ?? 30);
            $notification_email = sanitize_email($_POST['log_notification_email'] ?? '');
            $auto_cleanup = (bool) ($_POST['log_auto_cleanup'] ?? false);
            $email_notifications = (bool) ($_POST['log_email_notifications'] ?? false);
            
            // Validate retention days
            if ($retention_days < 1 || $retention_days > 365) {
                wp_send_json_error(['message' => 'Retention days must be between 1 and 365']);
            }
            
            // Validate email if notifications are enabled
            if ($email_notifications && empty($notification_email)) {
                wp_send_json_error(['message' => 'Email address is required when notifications are enabled']);
            }
            
            // Update WordPress options
            update_option('ctm_log_retention_days', $retention_days);
            update_option('ctm_log_notification_email', $notification_email);
            update_option('ctm_log_auto_cleanup', $auto_cleanup);
            update_option('ctm_log_email_notifications', $email_notifications);
            
            // Log the settings update
            $this->loggingSystem->logActivity('Log settings updated via AJAX', 'system');
            
            wp_send_json_success([
                'message' => 'Log settings updated successfully',
                'settings' => [
                    'retention_days' => $retention_days,
                    'notification_email' => $notification_email,
                    'auto_cleanup' => $auto_cleanup,
                    'email_notifications' => $email_notifications
                ]
            ]);
        } catch (\Exception $e) {
            wp_send_json_error(['message' => 'Failed to update log settings: ' . $e->getMessage()]);
        }
    }
} 