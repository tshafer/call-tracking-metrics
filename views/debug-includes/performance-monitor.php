<?php
/**
 * Performance Monitor Component
 * Real-time performance metrics and monitoring
 */
?>

<div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-xl font-semibold text-gray-800 flex items-center">
            <svg class="w-6 h-6 text-indigo-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
            </svg>
            Performance Monitor
        </h3>
        <div class="flex items-center gap-3">
            <button onclick="toggleAutoRefresh()" id="auto-refresh-btn" class="text-sm bg-indigo-100 hover:bg-indigo-200 text-indigo-800 px-3 py-1 rounded">
                Auto-refresh: OFF
            </button>
            <button onclick="refreshPerformance()" id="refresh-performance-btn" class="text-sm bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded flex">
                Refresh
            </button>
        </div>
    </div>
    
    <div class="space-y-6">
        <!-- Real-time Metrics Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="bg-gradient-to-br from-blue-50 to-blue-100 border border-blue-200 rounded-lg p-3 text-center min-h-[100px] flex flex-col justify-center">
                <div id="memory-usage" class="text-xl font-bold text-blue-600 mb-1">--</div>
                <div class="text-xs text-blue-700 font-medium">Memory Usage</div>
                <div id="memory-percentage" class="text-xs text-blue-600 mt-1 break-words">--</div>
            </div>
            
            <div class="bg-gradient-to-br from-green-50 to-green-100 border border-green-200 rounded-lg p-3 text-center min-h-[100px] flex flex-col justify-center">
                <div id="page-load-time" class="text-xl font-bold text-green-600 mb-1">--</div>
                <div class="text-xs text-green-700 font-medium">Page Load Time</div>
                <div id="load-time-status" class="text-xs text-green-600 mt-1 break-words">--</div>
            </div>
            
            <div class="bg-gradient-to-br from-purple-50 to-purple-100 border border-purple-200 rounded-lg p-3 text-center min-h-[100px] flex flex-col justify-center">
                <div id="db-queries" class="text-xl font-bold text-purple-600 mb-1">--</div>
                <div class="text-xs text-purple-700 font-medium">Database Queries</div>
                <div id="query-time" class="text-xs text-purple-600 mt-1 break-words">--</div>
            </div>
            
            <div class="bg-gradient-to-br from-orange-50 to-orange-100 border border-orange-200 rounded-lg p-3 text-center min-h-[100px] flex flex-col justify-center">
                <div id="api-calls" class="text-xl font-bold text-orange-600 mb-1">--</div>
                <div class="text-xs text-orange-700 font-medium">API Calls (24h)</div>
                <div id="api-response-time" class="text-xs text-orange-600 mt-1 break-words">--</div>
            </div>
        </div>

        <!-- Detailed Performance Metrics -->
        <div class="max-h-96 overflow-y-auto border border-gray-200 rounded-lg">
            <div class="bg-gray-50 px-4 py-3 border-b border-gray-200 sticky top-0">
                <h4 class="font-semibold text-gray-800">Detailed Metrics</h4>
            </div>
            <div id="detailed-metrics" class="p-4 space-y-4">
                <!-- Memory & Processing -->
                <div class="border-b border-gray-100 pb-4">
                    <h5 class="font-medium text-gray-700 mb-3 flex items-center">
                        <svg class="w-4 h-4 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path>
                        </svg>
                        Memory & Processing
                    </h5>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-3 text-sm">
                        <div class="flex justify-between items-center py-1">
                            <span class="text-gray-600 text-xs">Current Memory:</span>
                            <span id="current-memory" class="font-medium text-right break-words">--</span>
                        </div>
                        <div class="flex justify-between items-center py-1">
                            <span class="text-gray-600 text-xs">Peak Memory:</span>
                            <span id="peak-memory" class="font-medium text-right break-words">--</span>
                        </div>
                        <div class="flex justify-between items-center py-1">
                            <span class="text-gray-600 text-xs">Memory Limit:</span>
                            <span id="memory-limit" class="font-medium text-right break-words">--</span>
                        </div>
                        <div class="flex justify-between items-center py-1">
                            <span class="text-gray-600 text-xs">CPU Usage:</span>
                            <span id="cpu-usage" class="font-medium text-right break-words">--</span>
                        </div>
                        <div class="flex justify-between items-center py-1">
                            <span class="text-gray-600 text-xs">Execution Time:</span>
                            <span id="execution-time" class="font-medium text-right break-words">--</span>
                        </div>
                        <div class="flex justify-between items-center py-1">
                            <span class="text-gray-600 text-xs">Time Limit:</span>
                            <span id="time-limit" class="font-medium text-right break-words">--</span>
                        </div>
                    </div>
                </div>

                <!-- Database Performance -->
                <div class="border-b border-gray-100 pb-4">
                    <h5 class="font-medium text-gray-700 mb-3 flex items-center">
                        <svg class="w-4 h-4 text-purple-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/>
                        </svg>
                        Database Performance
                    </h5>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-3 text-sm">
                        <div class="flex justify-between items-center py-1">
                            <span class="text-gray-600 text-xs">Total Queries:</span>
                            <span id="total-queries" class="font-medium text-right break-words">--</span>
                        </div>
                        <div class="flex justify-between items-center py-1">
                            <span class="text-gray-600 text-xs">Query Time:</span>
                            <span id="total-query-time" class="font-medium text-right break-words">--</span>
                        </div>
                        <div class="flex justify-between items-center py-1">
                            <span class="text-gray-600 text-xs">Slow Queries:</span>
                            <span id="slow-queries" class="font-medium text-right break-words">--</span>
                        </div>
                        <div class="flex justify-between items-center py-1">
                            <span class="text-gray-600 text-xs">Cache Hits:</span>
                            <span id="cache-hits" class="font-medium text-right break-words">--</span>
                        </div>
                        <div class="flex justify-between items-center py-1">
                            <span class="text-gray-600 text-xs">Cache Misses:</span>
                            <span id="cache-misses" class="font-medium text-right break-words">--</span>
                        </div>
                        <div class="flex justify-between items-center py-1">
                            <span class="text-gray-600 text-xs">DB Version:</span>
                            <span id="db-version" class="font-medium text-right break-words">--</span>
                        </div>
                    </div>
                </div>

                <!-- Page Load Performance -->
                <div class="border-b border-gray-100 pb-4">
                    <h5 class="font-medium text-gray-700 mb-3 flex items-center">
                        <svg class="w-4 h-4 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Page Load Performance
                    </h5>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-3 text-sm">
                        <div class="flex justify-between items-center py-1">
                            <span class="text-gray-600 text-xs">TTFB:</span>
                            <span id="ttfb" class="font-medium text-right break-words">--</span>
                        </div>
                        <div class="flex justify-between items-center py-1">
                            <span class="text-gray-600 text-xs">DOM Ready:</span>
                            <span id="dom-ready" class="font-medium text-right break-words">--</span>
                        </div>
                        <div class="flex justify-between items-center py-1">
                            <span class="text-gray-600 text-xs">Load Complete:</span>
                            <span id="load-complete" class="font-medium text-right break-words">--</span>
                        </div>
                        <div class="flex justify-between items-center py-1">
                            <span class="text-gray-600 text-xs">Scripts Loaded:</span>
                            <span id="scripts-loaded" class="font-medium text-right break-words">--</span>
                        </div>
                        <div class="flex justify-between items-center py-1">
                            <span class="text-gray-600 text-xs">Styles Loaded:</span>
                            <span id="styles-loaded" class="font-medium text-right break-words">--</span>
                        </div>
                        <div class="flex justify-between items-center py-1">
                            <span class="text-gray-600 text-xs">Images Loaded:</span>
                            <span id="images-loaded" class="font-medium text-right break-words">--</span>
                        </div>
                    </div>
                </div>

                <!-- WordPress Performance -->
                <div class="border-b border-gray-100 pb-4">
                    <h5 class="font-medium text-gray-700 mb-3 flex items-center">
                        <svg class="w-4 h-4 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 10a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z"></path>
                        </svg>
                        WordPress Performance
                    </h5>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-3 text-sm">
                        <div class="flex justify-between items-center py-1">
                            <span class="text-gray-600 text-xs">Active Plugins:</span>
                            <span id="active-plugins" class="font-medium text-right break-words">--</span>
                        </div>
                        <div class="flex justify-between items-center py-1">
                            <span class="text-gray-600 text-xs">Theme Load Time:</span>
                            <span id="theme-load-time" class="font-medium text-right break-words">--</span>
                        </div>
                        <div class="flex justify-between items-center py-1">
                            <span class="text-gray-600 text-xs">Plugin Load Time:</span>
                            <span id="plugin-load-time" class="font-medium text-right break-words">--</span>
                        </div>
                        <div class="flex justify-between items-center py-1">
                            <span class="text-gray-600 text-xs">Admin Queries:</span>
                            <span id="admin-queries" class="font-medium text-right break-words">--</span>
                        </div>
                        <div class="flex justify-between items-center py-1">
                            <span class="text-gray-600 text-xs">Frontend Queries:</span>
                            <span id="frontend-queries" class="font-medium text-right break-words">--</span>
                        </div>
                        <div class="flex justify-between items-center py-1">
                            <span class="text-gray-600 text-xs">Cron Jobs:</span>
                            <span id="cron-jobs" class="font-medium text-right break-words">--</span>
                        </div>
                    </div>
                </div>

                <!-- Real-time Metrics -->
                <div>
                    <h5 class="font-medium text-gray-700 mb-3 flex items-center">
                        <svg class="w-4 h-4 text-red-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                        Real-time Metrics
                    </h5>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-3 text-sm">
                        <div class="flex justify-between items-center py-1">
                            <span class="text-gray-600 text-xs">Server Load:</span>
                            <span id="server-load" class="font-medium text-right break-words">--</span>
                        </div>
                        <div class="flex justify-between items-center py-1">
                            <span class="text-gray-600 text-xs">Disk Usage:</span>
                            <span id="disk-usage" class="font-medium text-right break-words">--</span>
                        </div>
                        <div class="flex justify-between items-center py-1">
                            <span class="text-gray-600 text-xs">Network I/O:</span>
                            <span id="network-io" class="font-medium text-right break-words">--</span>
                        </div>
                        <div class="flex justify-between items-center py-1">
                            <span class="text-gray-600 text-xs">Active Sessions:</span>
                            <span id="active-sessions" class="font-medium text-right break-words">--</span>
                        </div>
                        <div class="flex justify-between items-center py-1">
                            <span class="text-gray-600 text-xs">Error Rate:</span>
                            <span id="error-rate" class="font-medium text-right break-words">--</span>
                        </div>
                        <div class="flex justify-between items-center py-1">
                            <span class="text-gray-600 text-xs">Last Updated:</span>
                            <span id="last-updated" class="font-medium text-right break-words">--</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Performance Alerts -->
        <div id="performance-alerts" class="hidden">
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <h5 class="font-semibold text-yellow-800 mb-2 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.08 15.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                    Performance Alerts
                </h5>
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
document.addEventListener('DOMContentLoaded', function() {
    refreshPerformance();
    // Method 1: Try modern PerformanceNavigationTiming API first
    if (window.performance && window.performance.getEntriesByType) {
        const navEntries = window.performance.getEntriesByType('navigation');
        if (navEntries.length > 0 && navEntries[0].domContentLoadedEventEnd) {
            performanceData.domContentLoaded = navEntries[0].domContentLoadedEventEnd;
            console.log('DOM Ready (Navigation Timing 2):', performanceData.domContentLoaded + 'ms');
        }
    }
    
    // Method 2: Legacy Navigation Timing API
    if (!performanceData.domContentLoaded && window.performance && window.performance.timing) {
        setTimeout(() => {
            let domTiming = 0;
            
            // Try domContentLoadedEventEnd first (most accurate)
            if (window.performance.timing.domContentLoadedEventEnd > 0) {
                domTiming = window.performance.timing.domContentLoadedEventEnd - window.performance.timing.navigationStart;
            }
            // Fallback to domContentLoadedEventStart
            else if (window.performance.timing.domContentLoadedEventStart > 0) {
                domTiming = window.performance.timing.domContentLoadedEventStart - window.performance.timing.navigationStart;
            }
            // Final fallback to current time
            else {
                domTiming = Date.now() - window.performance.timing.navigationStart;
            }
            
            performanceData.domContentLoaded = domTiming > 0 ? domTiming : Date.now() - performanceData.navigationStart;
            console.log('DOM Ready (Navigation Timing 1):', performanceData.domContentLoaded + 'ms');
            storePerformanceMetrics();
        }, 10); // Small delay to ensure timing data is available
    }
    
    // Method 3: Fallback timing measurement
    if (!performanceData.domContentLoaded) {
        performanceData.domContentLoaded = Date.now() - performanceData.navigationStart;
        console.log('DOM Ready (Fallback):', performanceData.domContentLoaded + 'ms');
    }
    
    // Count loaded scripts
    performanceData.scriptsLoaded = document.querySelectorAll('script').length;
    
    // Count loaded stylesheets
    performanceData.stylesLoaded = document.querySelectorAll('link[rel="stylesheet"]').length;
    
    // Store initial performance data
    storePerformanceMetrics();
});

// Measure window load complete
window.addEventListener('load', function() {
    if (window.performance && window.performance.timing) {
        // Wait a bit for loadEventEnd to be available
        setTimeout(() => {
            const loadTiming = window.performance.timing.loadEventEnd - window.performance.timing.navigationStart;
            performanceData.loadComplete = loadTiming > 0 ? loadTiming : Date.now() - performanceData.navigationStart;
            storePerformanceMetrics();
        }, 100);
    } else {
        // Fallback timing
        performanceData.loadComplete = Date.now() - performanceData.navigationStart;
    }
    
    // Count loaded images
    const images = document.querySelectorAll('img');
    let loadedImages = 0;
    images.forEach(img => {
        if (img.complete && img.naturalHeight !== 0) {
            loadedImages++;
        }
    });
    performanceData.imagesLoaded = loadedImages;
    
    // Store final performance data
    storePerformanceMetrics();
});

// Store performance metrics in localStorage
function storePerformanceMetrics() {
    try {
        localStorage.setItem('ctm_performance_metrics', JSON.stringify(performanceData));
    } catch (e) {
        console.log('Could not store performance metrics:', e);
    }
}


 </script>