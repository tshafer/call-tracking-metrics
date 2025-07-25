<?php
// Debug tab view with comprehensive logging system
// Variables are passed from the parent context: $debugEnabled, $log

$retention_days = (int) get_option('ctm_log_retention_days', 7);
$auto_cleanup = get_option('ctm_log_auto_cleanup', true);
$email_notifications = get_option('ctm_log_email_notifications', false);
$notification_email = get_option('ctm_log_notification_email', get_option('admin_email'));

// Get log statistics and available dates
$log_stats = ['total_days' => 0, 'total_entries' => 0, 'total_size' => 0, 'type_counts' => []];
$available_dates = [];

if ($debugEnabled) {
    // Get log index directly from WordPress options
    $log_index = get_option('ctm_log_index', []);
    if (is_array($log_index)) {
        $available_dates = array_reverse($log_index);
        
        // Calculate statistics
        $total_entries = 0;
        $total_size = 0;
        $type_counts = [];
        
        foreach ($log_index as $date) {
            $logs = get_option("ctm_daily_log_{$date}", []);
            if (is_array($logs)) {
                $total_entries += count($logs);
                $total_size += strlen(serialize($logs));
                
                foreach ($logs as $log_entry) {
                    $type = $log_entry['type'] ?? 'unknown';
                    $type_counts[$type] = ($type_counts[$type] ?? 0) + 1;
                }
            }
        }
        
        $log_stats = [
            'total_days' => count($log_index),
            'total_entries' => $total_entries,
            'total_size' => $total_size,
            'type_counts' => $type_counts,
            'oldest_log' => !empty($log_index) ? end($log_index) : null,
            'newest_log' => !empty($log_index) ? reset($log_index) : null
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