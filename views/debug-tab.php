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

<div class="mb-12">
    <?php 
    // Include debug header component
    include plugin_dir_path(__FILE__) . 'debug-includes/debug-header.php';
    ?>

    <?php if ($debugEnabled): ?>
    <!-- Advanced Debug Features -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        
        <!-- Feature 1: System Information Panel -->
        <?php //include plugin_dir_path(__FILE__) . 'debug-includes/system-info-panel.php'; ?>

        <!-- Feature 2: API Request Simulator -->
        <?php //include plugin_dir_path(__FILE__) . 'debug-includes/api-simulator.php'; ?>

        <!-- Feature 3: Plugin Health Check -->
        <?php //include plugin_dir_path(__FILE__) . 'debug-includes/health-check.php'; ?>

        <!-- Feature 4: Performance Monitor -->
        <?php //include plugin_dir_path(__FILE__) . 'debug-includes/performance-monitor.php'; ?>
    </div>

    <?php include plugin_dir_path(__FILE__) . 'debug-includes/error-analyzer.php'; ?>

    <!-- Log Settings -->
    <?php include plugin_dir_path(__FILE__) . 'debug-includes/log-settings.php'; ?>

    <!-- Daily Logs -->
    <?php include plugin_dir_path(__FILE__) . 'debug-includes/daily-logs.php'; ?>
    <?php else: ?>
    <!-- Debug Disabled State -->
    <?php include plugin_dir_path(__FILE__) . 'debug-includes/debug-disabled.php'; ?>
    <?php endif; ?>
</div>

<?php //include plugin_dir_path(__FILE__) . 'debug-includes/debug-modals.php'; ?>

<?php 
// Include consolidated debug JavaScript
include plugin_dir_path(__FILE__) . 'debug-includes/debug-javascript.php';
?>