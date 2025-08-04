<?php
/**
 * Performance Monitor Component
 * Real-time performance metrics and monitoring
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
            <button onclick="toggleAutoRefresh()" id="auto-refresh-btn" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium px-4 py-2 rounded-xl transition text-sm"><?php _e('Auto-refresh: OFF', 'call-tracking-metrics'); ?></button>
            <button onclick="refreshPerformance()" id="refresh-performance-btn" class="bg-gray-600 hover:bg-gray-700 text-white font-medium px-6 py-2 rounded-xl flex items-center gap-2 transition text-sm">
                <span class="text-lg">&rarr;</span>
                <span><?php _e('Refresh', 'call-tracking-metrics'); ?></span>
            </button>
        </div>
    </div>
    
    <div class="space-y-6">
        <!-- Real-time Metrics Grid -->
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

        <!-- Detailed Performance Metrics -->
        <div class="max-h-96 overflow-y-auto border border-gray-200 rounded-lg">
            <div class="bg-gray-50 px-4 py-3 border-b border-gray-200 sticky top-0">
                <h4 class="font-semibold text-gray-800"><?php _e('Detailed Metrics', 'call-tracking-metrics'); ?></h4>
            </div>
            <div id="detailed-metrics" class="p-4 space-y-4">
                <!-- Memory & Processing -->
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
                            <span class="text-gray-600 text-xs"><?php _e('Peak Memory:', 'call-tracking-metrics'); ?></span>
                            <span id="peak-memory" class="font-medium text-right break-words">--</span>
                        </div>
                        <div class="flex justify-between items-center py-1">
                            <span class="text-gray-600 text-xs"><?php _e('Memory Limit:', 'call-tracking-metrics'); ?></span>
                            <span id="memory-limit" class="font-medium text-right break-words">--</span>
                        </div>
                        <div class="flex justify-between items-center py-1">
                            <span class="text-gray-600 text-xs"><?php _e('CPU Usage:', 'call-tracking-metrics'); ?></span>
                            <span id="cpu-usage" class="font-medium text-right break-words">--</span>
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

                <!-- Database Performance -->
                <div class="border-b border-gray-100 pb-4">
                    <div class="flex items-center gap-1 mb-2">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/>
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
                            <span class="text-gray-600 text-xs"><?php _e('Cache Hits:', 'call-tracking-metrics'); ?></span>
                            <span id="cache-hits" class="font-medium text-right break-words">--</span>
                        </div>
                        <div class="flex justify-between items-center py-1">
                            <span class="text-gray-600 text-xs"><?php _e('Cache Misses:', 'call-tracking-metrics'); ?></span>
                            <span id="cache-misses" class="font-medium text-right break-words">--</span>
                        </div>
                        <div class="flex justify-between items-center py-1">
                            <span class="text-gray-600 text-xs"><?php _e('DB Version:', 'call-tracking-metrics'); ?></span>
                            <span id="db-version" class="font-medium text-right break-words">--</span>
                        </div>
                    </div>
                </div>

                <!-- Page Load Performance -->
                <div class="border-b border-gray-100 pb-4">
                    <div class="flex items-center gap-1 mb-2">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <h5 class="text-lg font-bold text-gray-900"><?php _e('Page Load Performance', 'call-tracking-metrics'); ?></h5>
                    </div>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-3 text-sm">
                        <div class="flex justify-between items-center py-1">
                            <span class="text-gray-600 text-xs"><?php _e('TTFB:', 'call-tracking-metrics'); ?></span>
                            <span id="ttfb" class="font-medium text-right break-words">--</span>
                        </div>
                        <div class="flex justify-between items-center py-1">
                            <span class="text-gray-600 text-xs"><?php _e('DOM Ready:', 'call-tracking-metrics'); ?></span>
                            <span id="dom-ready" class="font-medium text-right break-words">--</span>
                        </div>
                        <div class="flex justify-between items-center py-1">
                            <span class="text-gray-600 text-xs"><?php _e('Load Complete:', 'call-tracking-metrics'); ?></span>
                            <span id="load-complete" class="font-medium text-right break-words">--</span>
                        </div>
                        <div class="flex justify-between items-center py-1">
                            <span class="text-gray-600 text-xs"><?php _e('Scripts Loaded:', 'call-tracking-metrics'); ?></span>
                            <span id="scripts-loaded" class="font-medium text-right break-words">--</span>
                        </div>
                        <div class="flex justify-between items-center py-1">
                            <span class="text-gray-600 text-xs"><?php _e('Styles Loaded:', 'call-tracking-metrics'); ?></span>
                            <span id="styles-loaded" class="font-medium text-right break-words">--</span>
                        </div>
                        <div class="flex justify-between items-center py-1">
                            <span class="text-gray-600 text-xs"><?php _e('Images Loaded:', 'call-tracking-metrics'); ?></span>
                            <span id="images-loaded" class="font-medium text-right break-words">--</span>
                        </div>
                    </div>
                </div>

                <!-- WordPress Performance -->
                <div class="border-b border-gray-100 pb-4">
                    <div class="flex items-center gap-1 mb-2">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 10a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z"></path>
                        </svg>
                        <h5 class="text-lg font-bold text-gray-900"><?php _e('WordPress Performance', 'call-tracking-metrics'); ?></h5>
                    </div>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-3 text-sm">
                        <div class="flex justify-between items-center py-1">
                            <span class="text-gray-600 text-xs"><?php _e('Active Plugins:', 'call-tracking-metrics'); ?></span>
                            <span id="active-plugins" class="font-medium text-right break-words">--</span>
                        </div>
                        <div class="flex justify-between items-center py-1">
                            <span class="text-gray-600 text-xs"><?php _e('Theme Load Time:', 'call-tracking-metrics'); ?></span>
                            <span id="theme-load-time" class="font-medium text-right break-words">--</span>
                        </div>
                        <div class="flex justify-between items-center py-1">
                            <span class="text-gray-600 text-xs"><?php _e('Plugin Load Time:', 'call-tracking-metrics'); ?></span>
                            <span id="plugin-load-time" class="font-medium text-right break-words">--</span>
                        </div>
                        <div class="flex justify-between items-center py-1">
                            <span class="text-gray-600 text-xs"><?php _e('Admin Queries:', 'call-tracking-metrics'); ?></span>
                            <span id="admin-queries" class="font-medium text-right break-words">--</span>
                        </div>
                        <div class="flex justify-between items-center py-1">
                            <span class="text-gray-600 text-xs"><?php _e('Frontend Queries:', 'call-tracking-metrics'); ?></span>
                            <span id="frontend-queries" class="font-medium text-right break-words">--</span>
                        </div>
                        <div class="flex justify-between items-center py-1">
                            <span class="text-gray-600 text-xs"><?php _e('Cron Jobs:', 'call-tracking-metrics'); ?></span>
                            <span id="cron-jobs" class="font-medium text-right break-words">--</span>
                        </div>
                    </div>
                </div>

                <!-- Real-time Metrics -->
                <div>
                    <div class="flex items-center gap-1 mb-2">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                        <h5 class="text-lg font-bold text-gray-900"><?php _e('Real-time Metrics', 'call-tracking-metrics'); ?></h5>
                    </div>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-3 text-sm">
                        <div class="flex justify-between items-center py-1">
                            <span class="text-gray-600 text-xs"><?php _e('Server Load:', 'call-tracking-metrics'); ?></span>
                            <span id="server-load" class="font-medium text-right break-words">--</span>
                        </div>
                        <div class="flex justify-between items-center py-1">
                            <span class="text-gray-600 text-xs"><?php _e('Disk Usage:', 'call-tracking-metrics'); ?></span>
                            <span id="disk-usage" class="font-medium text-right break-words">--</span>
                        </div>
                        <div class="flex justify-between items-center py-1">
                            <span class="text-gray-600 text-xs"><?php _e('Network I/O:', 'call-tracking-metrics'); ?></span>
                            <span id="network-io" class="font-medium text-right break-words">--</span>
                        </div>
                        <div class="flex justify-between items-center py-1">
                            <span class="text-gray-600 text-xs"><?php _e('Active Sessions:', 'call-tracking-metrics'); ?></span>
                            <span id="active-sessions" class="font-medium text-right break-words">--</span>
                        </div>
                        <div class="flex justify-between items-center py-1">
                            <span class="text-gray-600 text-xs"><?php _e('Error Rate:', 'call-tracking-metrics'); ?></span>
                            <span id="error-rate" class="font-medium text-right break-words">--</span>
                        </div>
                        <div class="flex justify-between items-center py-1">
                            <span class="text-gray-600 text-xs"><?php _e('Last Updated:', 'call-tracking-metrics'); ?></span>
                            <span id="last-updated" class="font-medium text-right break-words">--</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Performance Alerts -->
        <div id="performance-alerts" class="hidden">
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <h5 class="font-semibold text-yellow-800 mb-2 flex items-center"><?php _e('Performance Alerts', 'call-tracking-metrics'); ?></h5>
                <ul id="alerts-list" class="text-sm text-yellow-700 space-y-1"></ul>
            </div>
        </div>
    </div>
</div>

 <script>

// Performance Monitor
let pageLoadStart = performance.now();
let autoRefreshInterval = null;
let autoRefreshEnabled = false;

    function toggleAutoRefresh() {
        const button = document.getElementById('auto-refresh-btn');
        
        if (!button) return;
        
        if (autoRefreshEnabled) {
            // Disable auto-refresh
            clearInterval(autoRefreshInterval);
            autoRefreshInterval = null;
            autoRefreshEnabled = false;
            button.textContent = 'Auto: OFF';
            button.classList.remove('bg-green-600', 'hover:bg-green-700');
            button.classList.add('bg-gray-600', 'hover:bg-gray-700');
            showDebugMessage('Auto-refresh disabled', 'info');
        } else {
            // Enable auto-refresh
            autoRefreshInterval = setInterval(refreshPerformance, 30000); // Refresh every 30 seconds
            autoRefreshEnabled = true;
            button.textContent = 'Auto: ON';
            button.classList.remove('bg-gray-600', 'hover:bg-gray-700');
            button.classList.add('bg-green-600', 'hover:bg-green-700');
            showDebugMessage('Auto-refresh enabled (30s intervals)', 'success');
        }
    }


    // Enhanced refresh performance function
    function refreshPerformance() {
        const button = document.getElementById('refresh-performance-btn');
        if (button) {
            button.disabled = true;
            button.textContent = 'Refreshing...';
        }

        // Get stored client-side metrics
        const clientMetrics = getStoredPerformanceMetrics();

        const formData = new FormData();
        formData.append('action', 'ctm_get_performance_metrics');
        formData.append('nonce', '<?= wp_create_nonce('ctm_get_performance_metrics') ?>');
        if (clientMetrics) {
            formData.append('client_metrics', JSON.stringify(clientMetrics));
        }

        fetch('<?= admin_url('admin-ajax.php') ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(response => {
            if (response.success) {
                const data = response.data;
                
                // Update top metrics cards
                updateElement('memory-usage', data.memory_usage || '--');
                updateElement('memory-percentage', data.memory_percentage || '--');
                updateElement('page-load-time', data.page_load_time || '--');
                updateElement('load-time-status', data.server_response || '--');
                updateElement('db-queries', data.db_queries || '--');
                updateElement('query-time', data.query_time || '--');
                updateElement('api-calls', data.api_calls || '--');
                updateElement('api-response-time', data.api_response_time || '--');
                
                // Memory & Processing
                updateElement('current-memory', data.current_memory || '--');
                updateElement('peak-memory', data.peak_memory || '--');
                updateElement('memory-limit', data.memory_limit || '--');
                updateElement('cpu-usage', data.cpu_usage || '--');
                updateElement('execution-time', data.execution_time || '--');
                updateElement('time-limit', data.time_limit || '--');
                
                // Database Performance
                updateElement('total-queries', data.total_queries || '--');
                updateElement('total-query-time', data.total_query_time || '--');
                updateElement('slow-queries', data.slow_queries || '--');
                updateElement('cache-hits', data.cache_hits || '--');
                updateElement('cache-misses', data.cache_misses || '--');
                updateElement('db-version', data.db_version || '--');
                
                // Page Load Performance (enhanced with client-side data)
                updateElement('ttfb', data.ttfb || '--');
                updateElement('dom-ready', data.dom_ready || '--');
                updateElement('load-complete', data.load_complete || '--');
                updateElement('scripts-loaded', data.scripts_loaded || '--');
                updateElement('styles-loaded', data.styles_loaded || '--');
                updateElement('images-loaded', data.images_loaded || '--');
                
                // WordPress Performance
                updateElement('active-plugins', data.active_plugins || '--');
                updateElement('theme-load-time', data.theme_load_time || '--');
                updateElement('plugin-load-time', data.plugin_load_time || '--');
                updateElement('admin-queries', data.admin_queries || '--');
                updateElement('frontend-queries', data.frontend_queries || '--');
                updateElement('cron-jobs', data.cron_jobs || '--');
                
                // Real-time Metrics
                updateElement('server-load', data.server_load || '--');
                updateElement('disk-usage', data.disk_usage || '--');
                updateElement('network-io', data.network_io || '--');
                updateElement('active-sessions', data.active_sessions || '--');
                updateElement('error-rate', data.error_rate || '--');
                updateElement('last-updated', data.last_updated || '--');
                
                showDebugMessage('Performance metrics updated successfully!', 'success');
            } else {
                showDebugMessage('Failed to refresh performance metrics: ' + (response.data?.message || 'Unknown error'), 'error');
            }
        })
        .catch(error => {
            console.error('Performance refresh error:', error);
            showDebugMessage('Failed to refresh performance metrics. Please try again.', 'error');
        })
        .finally(() => {
            if (button) {
                button.disabled = false;
                button.textContent = 'Refresh';
            }
        });
    }

    // Get stored performance metrics
    function getStoredPerformanceMetrics() {
        try {
            const stored = localStorage.getItem('ctm_performance_metrics');
            return stored ? JSON.parse(stored) : null;
        } catch (e) {
            console.log('Could not retrieve performance metrics:', e);
            return null;
        }
    }


// Performance measurement functionality
let performanceData = {
    navigationStart: 0,
    domContentLoaded: 0,
    loadComplete: 0,
    scriptsLoaded: 0,
    stylesLoaded: 0,
    imagesLoaded: 0
};

// Capture navigation start time with modern API support
if (window.performance) {
    if (window.performance.timeOrigin) {
        // Modern browsers - use timeOrigin
        performanceData.navigationStart = window.performance.timeOrigin;
    } else if (window.performance.timing) {
        // Legacy browsers - use timing.navigationStart
        performanceData.navigationStart = window.performance.timing.navigationStart;
    } else {
        // Ultimate fallback
        performanceData.navigationStart = Date.now();
    }
} else {
    // Fallback for browsers without Performance API
    performanceData.navigationStart = Date.now();
}

// Measure DOM Content Loaded with multiple fallback methods
// Removed automatic measurement on page load - only measure when manually triggered

// Store performance metrics in localStorage
function storePerformanceMetrics() {
    try {
        localStorage.setItem('ctm_performance_metrics', JSON.stringify(performanceData));
    } catch (e) {
        console.log('Could not store performance metrics:', e);
    }
}


 </script>