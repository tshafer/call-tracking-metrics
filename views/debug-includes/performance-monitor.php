<?php
/**
 * Performance Monitor Component
 * Real-time performance metrics and monitoring
 * OPTIMIZED for reduced resource usage
 */
?>

<div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6">
    <div class="flex items-center justify-between mb-4 gap-4">
        <div class="flex items-center gap-2">
            <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
            </svg>
            <h3 class="text-2xl font-extrabold text-gray-900"><?php _e('Performance Monitor', 'call-tracking-metrics'); ?></h3>
        </div>
        <div class="flex justify-center my-4 gap-2">
            <button onclick="toggleAutoRefresh()" id="auto-refresh-btn" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium px-4 py-2 rounded-xl transition text-sm flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <?php _e('Auto-refresh: OFF', 'call-tracking-metrics'); ?>
            </button>
            <button onclick="refreshPerformance()" id="refresh-performance-btn" class="bg-gray-600 hover:bg-gray-700 !text-white font-medium px-6 py-2 rounded-xl flex items-center gap-2 transition text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                <?php _e('Refresh', 'call-tracking-metrics'); ?>
            </button>
        </div>
    </div>
    
    <div class="space-y-6">
        <!-- Core Metrics Grid - OPTIMIZED to show only essential metrics -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="bg-gradient-to-br from-blue-50 to-blue-100 border border-blue-200 rounded-lg p-3 text-center min-h-[100px] flex flex-col justify-center">
                <div id="memory-usage" class="text-xl font-bold text-blue-600 mb-1">--</div>
                <div class="text-xs text-blue-700 font-medium"><?php _e('Memory Usage', 'call-tracking-metrics'); ?></div>
                <div id="memory-percentage" class="text-xs text-blue-600 mt-1 break-words">--</div>
            </div>
            
            <div class="bg-gradient-to-br from-green-50 to-green-100 border border-green-200 rounded-lg p-3 text-center min-h-[100px] flex flex-col justify-center">
                <div id="page-load-time" class="text-xl font-bold text-green-600 mb-1">--</div>
                <div class="text-xs text-green-700 font-medium"><?php _e('Page Load Time', 'call-tracking-metrics'); ?></div>
                <div id="load-time-status" class="text-xs text-green-600 mt-1 break-words">--</div>
            </div>
            
            <div class="bg-gradient-to-br from-purple-50 to-purple-100 border border-purple-200 rounded-lg p-3 text-center min-h-[100px] flex flex-col justify-center">
                <div id="db-queries" class="text-xl font-bold text-purple-600 mb-1">--</div>
                <div class="text-xs text-purple-700 font-medium"><?php _e('Database Queries', 'call-tracking-metrics'); ?></div>
                <div id="query-time" class="text-xs text-purple-600 mt-1 break-words">--</div>
            </div>
            
            <div class="bg-gradient-to-br from-orange-50 to-orange-100 border border-orange-200 rounded-lg p-3 text-center min-h-[100px] flex flex-col justify-center">
                <div id="api-calls" class="text-xl font-bold text-orange-600 mb-1">--</div>
                <div class="text-xs text-orange-700 font-medium"><?php _e('API Calls (24h)', 'call-tracking-metrics'); ?></div>
                <div id="api-response-time" class="text-xs text-orange-600 mt-1 break-words">--</div>
            </div>
        </div>

        <!-- Essential Performance Metrics - OPTIMIZED to show only critical metrics -->
        <div class="max-h-64 overflow-y-auto border border-gray-200 rounded-lg">
            <div class="bg-gray-50 px-4 py-3 border-b border-gray-200 sticky top-0">
                <h4 class="font-semibold text-gray-800"><?php _e('Essential Metrics', 'call-tracking-metrics'); ?></h4>
                <p class="text-xs text-gray-600 mt-1"><?php _e('Key performance indicators only', 'call-tracking-metrics'); ?></p>
            </div>
            <div id="detailed-metrics" class="p-4 space-y-4">
                <!-- Memory & Processing - OPTIMIZED -->
                <div class="border-b border-gray-100 pb-4">
                    <div class="flex items-center gap-1 mb-2">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path>
                        </svg>
                        <h5 class="text-lg font-bold text-gray-900"><?php _e('Memory & Processing', 'call-tracking-metrics'); ?></h5>
                    </div>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-3 text-sm">
                        <div class="flex justify-between items-center py-1">
                            <span class="text-gray-600 text-xs"><?php _e('Current Memory:', 'call-tracking-metrics'); ?></span>
                            <span id="current-memory" class="font-medium text-right break-words">--</span>
                        </div>
                        <div class="flex justify-between items-center py-1">
                            <span class="text-gray-600 text-xs"><?php _e('Memory Limit:', 'call-tracking-metrics'); ?></span>
                            <span id="memory-limit" class="font-medium text-right break-words">--</span>
                        </div>
                        <div class="flex justify-between items-center py-1">
                            <span class="text-gray-600 text-xs"><?php _e('Execution Time:', 'call-tracking-metrics'); ?></span>
                            <span id="execution-time" class="font-medium text-right break-words">--</span>
                        </div>
                        <div class="flex justify-between items-center py-1">
                            <span class="text-gray-600 text-xs"><?php _e('Time Limit:', 'call-tracking-metrics'); ?></span>
                            <span id="time-limit" class="font-medium text-right break-words">--</span>
                        </div>
                    </div>
                </div>

                <!-- Database Performance - OPTIMIZED -->
                <div class="border-b border-gray-100 pb-4">
                    <div class="flex items-center gap-1 mb-2">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path>
                        </svg>
                        <h5 class="text-lg font-bold text-gray-900"><?php _e('Database Performance', 'call-tracking-metrics'); ?></h5>
                    </div>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-3 text-sm">
                        <div class="flex justify-between items-center py-1">
                            <span class="text-gray-600 text-xs"><?php _e('Total Queries:', 'call-tracking-metrics'); ?></span>
                            <span id="total-queries" class="font-medium text-right break-words">--</span>
                        </div>
                        <div class="flex justify-between items-center py-1">
                            <span class="text-gray-600 text-xs"><?php _e('Query Time:', 'call-tracking-metrics'); ?></span>
                            <span id="total-query-time" class="font-medium text-right break-words">--</span>
                        </div>
                        <div class="flex justify-between items-center py-1">
                            <span class="text-gray-600 text-xs"><?php _e('Slow Queries:', 'call-tracking-metrics'); ?></span>
                            <span id="slow-queries" class="font-medium text-right break-words">--</span>
                        </div>
                        <div class="flex justify-between items-center py-1">
                            <span class="text-gray-600 text-xs"><?php _e('DB Version:', 'call-tracking-metrics'); ?></span>
                            <span id="db-version" class="font-medium text-right break-words">--</span>
                        </div>
                    </div>
                </div>

                <!-- System Health - OPTIMIZED -->
                <div>
                    <div class="flex items-center gap-1 mb-2">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <h5 class="text-lg font-bold text-gray-900"><?php _e('System Health', 'call-tracking-metrics'); ?></h5>
                    </div>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-3 text-sm">
                        <div class="flex justify-between items-center py-1">
                            <span class="text-gray-600 text-xs"><?php _e('Server Load:', 'call-tracking-metrics'); ?></span>
                            <span id="server-load" class="font-medium text-right break-words">--</span>
                        </div>
                        <div class="flex justify-between items-center py-1">
                            <span class="text-gray-600 text-xs"><?php _e('Error Rate:', 'call-tracking-metrics'); ?></span>
                            <span id="error-rate" class="font-medium text-right break-words">--</span>
                        </div>
                        <div class="flex justify-between items-center py-1">
                            <span class="text-gray-600 text-xs"><?php _e('PHP Version:', 'call-tracking-metrics'); ?></span>
                            <span id="php-version" class="font-medium text-right break-words">--</span>
                        </div>
                        <div class="flex justify-between items-center py-1">
                            <span class="text-gray-600 text-xs"><?php _e('WordPress Version:', 'call-tracking-metrics'); ?></span>
                            <span id="wp-version" class="font-medium text-right break-words">--</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Performance Alerts -->
        <div id="performance-alerts" class="space-y-2">
            <!-- Alerts will be populated by JavaScript -->
        </div>
    </div>
</div>

<script>
    let autoRefreshInterval = null;
    let autoRefreshEnabled = false;
    let refreshCount = 0;
    const MAX_REFRESH_COUNT = 10; // Limit auto-refresh to prevent excessive resource usage

    function toggleAutoRefresh() {
        const btn = document.getElementById('auto-refresh-btn');
        
        if (autoRefreshEnabled) {
            // Disable auto-refresh
            if (autoRefreshInterval) {
                clearInterval(autoRefreshInterval);
                autoRefreshInterval = null;
            }
            autoRefreshEnabled = false;
            btn.textContent = '<?php _e('Auto-refresh: OFF', 'call-tracking-metrics'); ?>';
            btn.classList.remove('bg-green-200', 'hover:bg-green-300');
            btn.classList.add('bg-gray-200', 'hover:bg-gray-300');
        } else {
            // Enable auto-refresh with longer interval (30 seconds instead of 10)
            autoRefreshEnabled = true;
            refreshCount = 0;
            btn.textContent = '<?php _e('Auto-refresh: ON', 'call-tracking-metrics'); ?>';
            btn.classList.remove('bg-gray-200', 'hover:bg-gray-300');
            btn.classList.add('bg-green-200', 'hover:bg-green-300');
            
            // Start auto-refresh with 30-second interval (OPTIMIZED)
            autoRefreshInterval = setInterval(() => {
                refreshCount++;
                if (refreshCount >= MAX_REFRESH_COUNT) {
                    // Stop auto-refresh after 10 cycles to prevent resource abuse
                    toggleAutoRefresh();
                    ctmShowToast('Auto-refresh stopped after 10 cycles to conserve resources', 'info');
                    return;
                }
                refreshPerformance();
            }, 30000); // 30 seconds instead of 10
            
            // Initial refresh
            refreshPerformance();
        }
    }

    function refreshPerformance() {
        const btn = document.getElementById('refresh-performance-btn');
        const originalText = btn.textContent;
        btn.disabled = true;
        btn.textContent = '<?php _e('Refreshing...', 'call-tracking-metrics'); ?>';

        // Get client-side metrics
        const clientMetrics = {
            pageLoadTime: performance.now(),
            memoryUsage: performance.memory ? performance.memory.usedJSHeapSize : null,
            timestamp: Date.now()
        };

        const formData = new FormData();
        formData.append('action', 'ctm_get_performance_metrics');
        formData.append('nonce', '<?= wp_create_nonce('ctm_get_performance_metrics') ?>');
        formData.append('client_metrics', JSON.stringify(clientMetrics));

        fetch('<?= admin_url('admin-ajax.php') ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updatePerformanceMetrics(data.data);
                updatePerformanceAlerts(data.data);
            } else {
                console.error('Failed to get performance metrics:', data.data);
            }
        })
        .catch(error => {
            console.error('Error refreshing performance:', error);
        })
        .finally(() => {
            btn.disabled = false;
            btn.textContent = originalText;
        });
    }

    function updatePerformanceMetrics(data) {
        // Update core metrics
        updateElement('memory-usage', data.memory_usage || '--');
        updateElement('memory-percentage', data.memory_percentage || '--');
        updateElement('page-load-time', data.page_load_time || '--');
        updateElement('load-time-status', data.server_response || '--');
        updateElement('db-queries', data.db_queries || '--');
        updateElement('query-time', data.query_time || '--');
        updateElement('api-calls', data.api_calls || '--');
        updateElement('api-response-time', data.api_response_time || '--');
        
        // Update detailed metrics
        updateElement('current-memory', data.current_memory || '--');
        updateElement('memory-limit', data.memory_limit || '--');
        updateElement('execution-time', data.execution_time || '--');
        updateElement('time-limit', data.time_limit || '--');
        updateElement('total-queries', data.total_queries || '--');
        updateElement('total-query-time', data.total_query_time || '--');
        updateElement('slow-queries', data.slow_queries || '--');
        updateElement('db-version', data.db_version || '--');
        updateElement('server-load', data.server_load || '--');
        updateElement('error-rate', data.error_rate || '--');
        updateElement('php-version', data.php_version || '--');
        updateElement('wp-version', data.wp_version || '--');
    }

    function updatePerformanceAlerts(data) {
        const alertsContainer = document.getElementById('performance-alerts');
        alertsContainer.innerHTML = '';

        const alerts = [];

        // Memory alerts
        if (data.memory_percentage && parseFloat(data.memory_percentage) > 80) {
            alerts.push({
                type: 'warning',
                message: 'High memory usage detected',
                icon: '⚠️'
            });
        }

        // Query time alerts
        if (data.query_time && data.query_time !== 'N/A') {
            const queryTime = parseFloat(data.query_time);
            if (queryTime > 1000) { // More than 1 second
                alerts.push({
                    type: 'error',
                    message: 'Slow database queries detected',
                    icon: '🐌'
                });
            }
        }

        // Page load time alerts
        if (data.page_load_time && data.page_load_time !== 'N/A') {
            const loadTime = parseFloat(data.page_load_time);
            if (loadTime > 3000) { // More than 3 seconds
                alerts.push({
                    type: 'error',
                    message: 'Slow page load time detected',
                    icon: '⏱️'
                });
            }
        }

        // Error rate alerts
        if (data.error_rate && data.error_rate.includes('High')) {
            alerts.push({
                type: 'error',
                message: 'High error rate detected',
                icon: '❌'
            });
        }

        // Display alerts
        alerts.forEach(alert => {
            const alertDiv = document.createElement('div');
            alertDiv.className = `p-3 rounded-lg border-l-4 ${
                alert.type === 'error' ? 'bg-red-50 border-red-400 text-red-700' :
                alert.type === 'warning' ? 'bg-yellow-50 border-yellow-400 text-yellow-700' :
                'bg-blue-50 border-blue-400 text-blue-700'
            }`;
            alertDiv.innerHTML = `
                <div class="flex items-center">
                    <span class="text-lg mr-2">${alert.icon}</span>
                    <span class="font-medium">${alert.message}</span>
                </div>
            `;
            alertsContainer.appendChild(alertDiv);
        });

        // Show "all good" message if no alerts
        if (alerts.length === 0) {
            const goodDiv = document.createElement('div');
            goodDiv.className = 'p-3 rounded-lg border-l-4 bg-green-50 border-green-400 text-green-700';
            goodDiv.innerHTML = `
                <div class="flex items-center">
                    <span class="text-lg mr-2">✅</span>
                    <span class="font-medium">All performance metrics are within normal ranges</span>
                </div>
            `;
            alertsContainer.appendChild(goodDiv);
        }
    }

    function updateElement(id, value) {
        const element = document.getElementById(id);
        if (element) {
            element.textContent = value;
        }
    }

    // Initial load
    document.addEventListener('DOMContentLoaded', function() {
        refreshPerformance();
    });
</script>