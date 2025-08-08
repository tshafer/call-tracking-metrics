<?php
// Debug tab view with comprehensive logging system
// Variables are passed from the parent context: $debugEnabled, $log

$retention_days = (int) get_option('ctm_log_retention_days', 7);
$auto_cleanup = get_option('ctm_log_auto_cleanup', true);
$email_notifications = get_option('ctm_log_email_notifications', false);
$notification_email = get_option('ctm_log_notification_email', get_option('admin_email'));

// Get log statistics and available dates - OPTIMIZED for performance using new database system
$log_stats = ['total_days' => 0, 'total_entries' => 0, 'total_size' => 0, 'type_counts' => []];
$available_dates = [];

if ($debugEnabled) {
    // Use the new LoggingSystem to get available dates
    if (isset($loggingSystem) && $loggingSystem) {
        $all_dates = $loggingSystem->getAvailableLogDates();
        
        // Only show last 5 days initially for performance
        $available_dates = array_slice($all_dates, 0, 5);
        
        // Calculate statistics only for displayed dates to improve performance
        $total_entries = 0;
        $type_counts = [];
        
        foreach ($available_dates as $date) {
            $logs = $loggingSystem->getLogsForDate($date);
            if (!empty($logs)) {
                $total_entries += count($logs);
                
                // Only count first 100 entries per day for performance
                $sample_logs = array_slice($logs, 0, 100);
                foreach ($sample_logs as $log_entry) {
                    $type = $log_entry['type'] ?? 'unknown';
                    $type_counts[$type] = ($type_counts[$type] ?? 0) + 1;
                }
            }
        }
        
        $log_stats = [
            'total_days' => count($all_dates),
            'total_entries' => $total_entries,
            'total_size' => 0, // Size calculation not needed for database
            'type_counts' => $type_counts,
            'oldest_log' => !empty($all_dates) ? end($all_dates) : null,
            'newest_log' => !empty($all_dates) ? reset($all_dates) : null,
            'total_available_days' => count($all_dates)
        ];
    }
}
?>


<!-- Add Toast Container for notifications -->
<div id="ctm-toast-container" style="position: fixed; top: 1.5rem; right: 1.5rem; z-index: 9999;"></div>


<div class="mb-12">
    <?php 
    // Include debug header component
    include plugin_dir_path(__FILE__) . 'debug-includes/debug-header.php';
    ?>
    <!-- Advanced Debug Features -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <?php include plugin_dir_path(__FILE__) . 'debug-includes/system-info-panel.php'; ?>
        <?php include plugin_dir_path(__FILE__) . 'debug-includes/health-check.php'; ?>
        <?php include plugin_dir_path(__FILE__) . 'debug-includes/performance-monitor.php'; ?>
        <?php include plugin_dir_path(__FILE__) . 'debug-includes/log-settings.php'; ?>
    </div>

    <?php include plugin_dir_path(__FILE__) . 'debug-includes/daily-logs.php'; ?>
</div>

<?php include plugin_dir_path(__FILE__) . 'debug-includes/debug-modals.php'; ?>