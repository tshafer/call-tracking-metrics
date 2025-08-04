<?php
namespace CTM\Admin;

/**
 * Handles debug logging and log management functionality
 */
class LoggingSystem
{
    /**
     * Check if debug mode is enabled
     */
    public function isDebugEnabled(): bool
    {
        return (bool) \get_option('ctm_debug_enabled', false);
    }

    /**
     * Enhanced logging system with daily logs and categorization
     */
    public function logActivity(string $message, string $type = 'info', array $context = []): void
    {
        if (!self::isDebugEnabled()) {
            return; // Only log when debug mode is enabled
        }

        $log_entry = [
            'timestamp' => \current_time('mysql'),
            'type' => $type, // info, error, warning, debug, api, config, system
            'message' => $message,
            'context' => $context,
            'user_id' => \get_current_user_id(),
            'ip_address' => $this->getUserIP(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
            'memory_usage' => memory_get_usage(true),
            'memory_peak' => memory_get_peak_usage(true)
        ];

        $this->writeToLog($log_entry);
        
        // Auto-cleanup old logs
        $this->cleanupOldLogs();
    }

    /**
     * Write log entry to daily log file
     */
    private function writeToLog(array $log_entry): void
    {
        $log_date = date('Y-m-d');
        $daily_logs = \get_option("ctm_daily_log_{$log_date}", []);
        
        if (!is_array($daily_logs)) {
            $daily_logs = [];
        }
        
        $daily_logs[] = $log_entry;
        
        // Keep only last 1000 entries per day to prevent memory issues
        if (count($daily_logs) > 1000) {
            $daily_logs = array_slice($daily_logs, -1000);
        }
        
        \update_option("ctm_daily_log_{$log_date}", $daily_logs);
        
        // Update log index
        $this->updateLogIndex($log_date);
    }

    /**
     * Update the log index to track available log dates
     */
    private function updateLogIndex(string $log_date): void
    {
        $log_index = \get_option('ctm_log_index', []);
        if (!is_array($log_index)) {
            $log_index = [];
        }
        
        if (!in_array($log_date, $log_index)) {
            $log_index[] = $log_date;
            // Keep index sorted
            sort($log_index);
            \update_option('ctm_log_index', $log_index);
        }
    }

    /**
     * Get all available log dates
     */
    public function getAvailableLogDates(): array
    {
        $log_index = \get_option('ctm_log_index', []);
        return is_array($log_index) ? array_reverse($log_index) : [];
    }

    /**
     * Get logs for a specific date
     */
    public function getLogsForDate(string $date): array
    {
        $logs = \get_option("ctm_daily_log_{$date}", []);
        return is_array($logs) ? $logs : [];
    }

    /**
     * Clear logs for a specific date
     */
    public function clearDayLog(string $date): void
    {
        \delete_option("ctm_daily_log_{$date}");
        
        // Update log index
        $log_index = \get_option('ctm_log_index', []);
        if (is_array($log_index)) {
            $log_index = array_filter($log_index, function($d) use ($date) {
                return $d !== $date;
            });
            \update_option('ctm_log_index', array_values($log_index));
        }
    }

    /**
     * Clear all logs
     */
    public function clearAllLogs(): void
    {
        $log_index = \get_option('ctm_log_index', []);
        if (is_array($log_index)) {
            foreach ($log_index as $date) {
                \delete_option("ctm_daily_log_{$date}");
            }
        }
        
        \delete_option('ctm_log_index');
        \delete_option('ctm_debug_log'); // Clear old format logs too
    }

    /**
     * Email log for a specific date
     */
    public function emailLog(string $date, string $email_to): bool
    {
        $logs = $this->getLogsForDate($date);
        
        if (empty($logs)) {
            return false;
        }

        $site_name = \get_bloginfo('name');
        $subject = "CTM Debug Log for {$date} - {$site_name}";
        
        $message = "Debug log for {$date}\n";
        $message .= "Site: {$site_name}\n";
        $message .= "Generated: " . \current_time('mysql') . "\n";
        $message .= str_repeat('=', 60) . "\n\n";
        
        foreach ($logs as $entry) {
            $message .= "[{$entry['timestamp']}] [{$entry['type']}] {$entry['message']}\n";
            
            if (!empty($entry['context'])) {
                $message .= "Context: " . print_r($entry['context'], true) . "\n";
            }
            
            $message .= "User: " . ($entry['user_id'] ? \get_userdata($entry['user_id'])->user_login : 'Anonymous') . "\n";
            $message .= "IP: {$entry['ip_address']}\n";
            $message .= "Memory: " . \size_format($entry['memory_usage']) . " (Peak: " . \size_format($entry['memory_peak']) . ")\n";
            $message .= str_repeat('-', 40) . "\n\n";
        }

        $headers = [
            'Content-Type: text/plain; charset=UTF-8',
            'From: ' . \get_option('admin_email')
        ];

        return \wp_mail($email_to, $subject, $message, $headers);
    }

    /**
     * Auto-cleanup old logs based on retention settings
     */
    private function cleanupOldLogs(): void
    {
        if (!\get_option('ctm_log_auto_cleanup', true)) {
            return;
        }

        $retention_days = (int) \get_option('ctm_log_retention_days', 7);
        $cutoff_date = date('Y-m-d', strtotime("-{$retention_days} days"));
        
        $log_index = \get_option('ctm_log_index', []);
        if (!is_array($log_index)) {
            return;
        }

        $updated = false;
        foreach ($log_index as $key => $date) {
            if ($date < $cutoff_date) {
                \delete_option("ctm_daily_log_{$date}");
                unset($log_index[$key]);
                $updated = true;
            }
        }

        if ($updated) {
            \update_option('ctm_log_index', array_values($log_index));
        }
    }

    /**
     * Get user IP address
     */
    private function getUserIP(): string
    {
        $ip_keys = ['HTTP_CF_CONNECTING_IP', 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];
        
        foreach ($ip_keys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = $_SERVER[$key];
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    }

    /**
     * Get log statistics
     */
    public function getLogStatistics(): array
    {
        $log_index = $this->getAvailableLogDates();
        $total_entries = 0;
        $total_size = 0;
        $type_counts = [];
        
        foreach ($log_index as $date) {
            $logs = $this->getLogsForDate($date);
            $total_entries += count($logs);
            $total_size += strlen(serialize($logs));
            
            foreach ($logs as $log) {
                $type = $log['type'] ?? 'unknown';
                $type_counts[$type] = ($type_counts[$type] ?? 0) + 1;
            }
        }
        
        return [
            'total_days' => count($log_index),
            'total_entries' => $total_entries,
            'total_size' => $total_size,
            'type_counts' => $type_counts,
            'oldest_log' => !empty($log_index) ? end($log_index) : null,
            'newest_log' => !empty($log_index) ? reset($log_index) : null
        ];
    }

    /**
     * Initialize the logging system (call this once from main plugin file)
     */
    public function initializeLoggingSystem(): void
    {
        if (!\wp_next_scheduled('ctm_daily_log_cleanup')) {
            \wp_schedule_event(time(), 'daily', 'ctm_daily_log_cleanup');
        }
        \add_action('ctm_daily_log_cleanup', [$this, 'performScheduledLogCleanup']);
    }

    /**
     * Handle plugin activation - set up logging defaults
     */
    public static function onPluginActivation(): void
    {
        // Set default log settings if not already set
        if (\get_option('ctm_log_retention_days') === false) {
            \update_option('ctm_log_retention_days', 7);
        }
        
        if (\get_option('ctm_log_auto_cleanup') === false) {
            \update_option('ctm_log_auto_cleanup', true);
        }
        
        if (\get_option('ctm_log_email_notifications') === false) {
            \update_option('ctm_log_email_notifications', false);
        }
        
        if (\get_option('ctm_log_notification_email') === false) {
            \update_option('ctm_log_notification_email', \get_option('admin_email'));
        }
        
        // Initialize logging system
        self::initializeLoggingSystemStatic();
        
        // Log activation
        self::logActivityStatic('CTM Plugin activated', 'system', [
            'wp_version' => get_bloginfo('version'),
            'php_version' => PHP_VERSION,
            'memory_limit' => ini_get('memory_limit')
        ]);
    }

    /**
     * Handle plugin deactivation - clean up scheduled tasks
     */
    public static function onPluginDeactivation(): void
    {
        // Remove scheduled cleanup
        \wp_clear_scheduled_hook('ctm_daily_log_cleanup');
        
        // Log plugin deactivation
        self::logActivityStatic('CTM Plugin deactivated', 'system');
    }

    /**
     * Static version of initializeLoggingSystem for activation hook
     */
    private static function initializeLoggingSystemStatic(): void
    {
        if (!\wp_next_scheduled('ctm_daily_log_cleanup')) {
            \wp_schedule_event(time(), 'daily', 'ctm_daily_log_cleanup');
        }
    }

    /**
     * Static version of logActivity for activation hook
     */
    private static function logActivityStatic(string $message, string $type = 'info', array $context = []): void
    {
        // Check if debug mode is enabled
        if (!(bool) \get_option('ctm_debug_enabled', false)) {
            return; // Only log when debug mode is enabled
        }

        $log_entry = [
            'timestamp' => \current_time('mysql'),
            'type' => $type,
            'message' => $message,
            'context' => $context,
            'user_id' => \get_current_user_id(),
            'ip_address' => self::getUserIPStatic(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
            'memory_usage' => memory_get_usage(true),
            'memory_peak' => memory_get_peak_usage(true)
        ];

        self::writeToLogStatic($log_entry);
    }

    /**
     * Static version of writeToLog for activation hook
     */
    private static function writeToLogStatic(array $log_entry): void
    {
        $log_date = date('Y-m-d');
        $daily_logs = \get_option("ctm_daily_log_{$log_date}", []);
        
        if (!is_array($daily_logs)) {
            $daily_logs = [];
        }
        
        $daily_logs[] = $log_entry;
        
        // Keep only last 1000 entries per day to prevent memory issues
        if (count($daily_logs) > 1000) {
            $daily_logs = array_slice($daily_logs, -1000);
        }
        
        \update_option("ctm_daily_log_{$log_date}", $daily_logs);
        
        // Update log index
        self::updateLogIndexStatic($log_date);
    }

    /**
     * Static version of updateLogIndex for activation hook
     */
    private static function updateLogIndexStatic(string $log_date): void
    {
        $log_index = \get_option('ctm_log_index', []);
        if (!is_array($log_index)) {
            $log_index = [];
        }
        
        if (!in_array($log_date, $log_index)) {
            $log_index[] = $log_date;
            // Keep index sorted
            sort($log_index);
            \update_option('ctm_log_index', $log_index);
        }
    }

    /**
     * Static version of getUserIP for activation hook
     */
    private static function getUserIPStatic(): string
    {
        $ip_keys = ['HTTP_CF_CONNECTING_IP', 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];
        
        foreach ($ip_keys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = $_SERVER[$key];
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    }

    /**
     * Perform scheduled log cleanup
     */
    public function performScheduledLogCleanup(): void
    {
        $this->performInstanceLogCleanup();
    }

    /**
     * Instance method for log cleanup
     */
    protected function performInstanceLogCleanup(): void
    {
        if (!\get_option('ctm_log_auto_cleanup', true)) {
            return;
        }

        $retention_days = (int) \get_option('ctm_log_retention_days', 7);
        $cutoff_date = date('Y-m-d', strtotime("-{$retention_days} days"));
        
        $log_index = \get_option('ctm_log_index', []);
        if (!is_array($log_index)) {
            return;
        }

        $cleaned_count = 0;
        $cleaned_size = 0;
        
        foreach ($log_index as $key => $date) {
            if ($date < $cutoff_date) {
                $log_data = \get_option("ctm_daily_log_{$date}", []);
                $cleaned_size += strlen(serialize($log_data));
                
                \delete_option("ctm_daily_log_{$date}");
                unset($log_index[$key]);
                $cleaned_count++;
            }
        }

        if ($cleaned_count > 0) {
            \update_option('ctm_log_index', array_values($log_index));
            
            // Log cleanup activity
            $this->logActivity('Automatic log cleanup completed', 'system', [
                'cleaned_days' => $cleaned_count,
                'cleaned_size' => \size_format($cleaned_size),
                'retention_days' => $retention_days,
                'cutoff_date' => $cutoff_date
            ]);
            
            // Send email notification if enabled
            if (\get_option('ctm_log_email_notifications', false)) {
                $this->sendCleanupNotification($cleaned_count, $cleaned_size, $retention_days);
            }
        }
    }

    /**
     * Send email notification about log cleanup
     */
    private function sendCleanupNotification(int $cleaned_count, int $cleaned_size, int $retention_days): void
    {
        $notification_email = \get_option('ctm_log_notification_email', \get_option('admin_email'));
        if (empty($notification_email)) {
            return;
        }

        $site_name = \get_bloginfo('name');
        $subject = "CTM Log Cleanup Report - {$site_name}";
        
        $message = "Call Tracking Metrics Plugin - Log Cleanup Report\n\n";
        $message .= "Site: {$site_name}\n";
        $message .= "Date: " . \current_time('Y-m-d H:i:s') . "\n";
        $message .= str_repeat('=', 50) . "\n\n";
        
        $message .= "Cleanup Summary:\n";
        $message .= "- Days cleaned: {$cleaned_count}\n";
        $message .= "- Data cleaned: " . \size_format($cleaned_size) . "\n";
        $message .= "- Retention period: {$retention_days} days\n\n";
        
        $remaining_stats = $this->getLogStatistics();
        $message .= "Remaining Logs:\n";
        $message .= "- Total days: {$remaining_stats['total_days']}\n";
        $message .= "- Total entries: " . number_format($remaining_stats['total_entries']) . "\n";
        $message .= "- Storage size: " . \size_format($remaining_stats['total_size']) . "\n\n";
        
        $message .= "This is an automated notification from the CTM plugin.\n";
        $message .= "You can adjust log settings in the WordPress admin panel.\n";

        $headers = [
            'Content-Type: text/plain; charset=UTF-8',
            'From: ' . \get_option('admin_email')
        ];

        \wp_mail($notification_email, $subject, $message, $headers);
    }

    /**
     * Legacy debug logging method for backwards compatibility
     */
    public function logDebug($message): void
    {
        $this->logActivity(
            is_string($message) ? $message : print_r($message, true),
            'debug'
        );
    }
} 