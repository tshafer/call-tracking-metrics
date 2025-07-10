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
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
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

 