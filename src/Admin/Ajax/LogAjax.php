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
        add_action('wp_ajax_ctm_get_form_logs', [$this, 'ajaxGetFormLogs']);
        add_action('wp_ajax_ctm_clear_form_logs', [$this, 'ajaxClearFormLogs']);
        add_action('wp_ajax_ctm_get_form_log_stats', [$this, 'ajaxGetFormLogStats']);
        add_action('wp_ajax_ctm_clear_all_logs', [$this, 'ajaxClearAllLogs']);
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
} 