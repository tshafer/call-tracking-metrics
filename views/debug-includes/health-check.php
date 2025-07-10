<?php
/**
 * Plugin Health Check Component
 * Comprehensive system assessment and health monitoring
 */
?>

<div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6">
    <h3 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
        <svg class="w-6 h-6 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        Plugin Health Check
    </h3>
    
    <div class="space-y-4">
        <!-- Overall Health Score -->
        <div class="bg-gradient-to-r from-green-50 to-blue-50 border border-green-200 rounded-lg p-4">
            <div class="flex items-center justify-between">
                <div>
                    <h4 class="text-lg font-semibold text-green-800">Overall Health Score</h4>
                    <p class="text-sm text-green-600">System status assessment</p>
                </div>
                <div class="text-right">
                    <div id="health-score" class="text-3xl font-bold text-green-600">--</div>
                    <div class="text-sm text-green-700">out of 100</div>
                </div>
            </div>
        </div>

        <!-- Health Check Categories -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- API Configuration -->
            <div class="border border-gray-200 rounded-lg p-4">
                <h5 class="font-semibold text-gray-800 mb-3 flex items-center">
                    <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                    </svg>
                    API Configuration
                </h5>
                <div id="health-api" class="space-y-2">
                    <div class="flex items-center justify-between text-sm">
                        <span>API Key Configured</span>
                        <span id="check-api-key" class="health-indicator">⏳</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span>API Connection</span>
                        <span id="check-api-connection" class="health-indicator">⏳</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span>Account Access</span>
                        <span id="check-account-access" class="health-indicator">⏳</span>
                    </div>
                </div>
            </div>

            <!-- Form Integration -->
            <div class="border border-gray-200 rounded-lg p-4">
                <h5 class="font-semibold text-gray-800 mb-3 flex items-center">
                    <svg class="w-5 h-5 text-purple-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Form Integration
                </h5>
                <div id="health-forms" class="space-y-2">
                    <div class="flex items-center justify-between text-sm">
                        <span>Contact Form 7</span>
                        <span id="check-cf7" class="health-indicator">⏳</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span>Gravity Forms</span>
                        <span id="check-gf" class="health-indicator">⏳</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span>Field Mappings</span>
                        <span id="check-field-mappings" class="health-indicator">⏳</span>
                    </div>
                </div>
            </div>

            <!-- Server Requirements -->
            <div class="border border-gray-200 rounded-lg p-4">
                <h5 class="font-semibold text-gray-800 mb-3 flex items-center">
                    <svg class="w-5 h-5 text-orange-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"></path>
                    </svg>
                    Server Requirements
                </h5>
                <div id="health-server" class="space-y-2">
                    <div class="flex items-center justify-between text-sm">
                        <span>PHP Version (7.4+)</span>
                        <span id="check-php-version" class="health-indicator">⏳</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span>cURL Extension</span>
                        <span id="check-curl" class="health-indicator">⏳</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span>SSL Support</span>
                        <span id="check-ssl" class="health-indicator">⏳</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span>Memory Limit</span>
                        <span id="check-memory" class="health-indicator">⏳</span>
                    </div>
                </div>
            </div>

            <!-- Plugin Status -->
            <div class="border border-gray-200 rounded-lg p-4">
                <h5 class="font-semibold text-gray-800 mb-3 flex items-center">
                    <svg class="w-5 h-5 text-red-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4V2a1 1 0 011-1h8a1 1 0 011 1v2m0 0V1a1 1 0 011-1h2a1 1 0 011 1v3M7 4H5a1 1 0 00-1 1v3m0 0v8a2 2 0 002 2h10a2 2 0 002-2V8m0 0V5a1 1 0 00-1-1h-2M7 4h10"/>
                    </svg>
                    Plugin Status
                </h5>
                <div id="health-plugin" class="space-y-2">
                    <div class="flex items-center justify-between text-sm">
                        <span>Plugin Version</span>
                        <span id="check-plugin-version" class="health-indicator">⏳</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span>Database Tables</span>
                        <span id="check-database-tables" class="health-indicator">⏳</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span>File Permissions</span>
                        <span id="check-file-permissions" class="health-indicator">⏳</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span>Debug Mode</span>
                        <span id="check-debug-mode" class="health-indicator">⏳</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex gap-3 pt-4 border-t border-gray-200">
            <button onclick="runHealthCheck()" id="health-check-btn" class="bg-green-600 hover:bg-green-700 text-white font-medium px-6 py-2 rounded-lg transition duration-200 flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Run Health Check
            </button>
            <button onclick="fixCommonIssues()" id="fix-issues-btn" class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2 rounded-lg transition duration-200" disabled>
                Auto-Fix Issues
            </button>
            <button onclick="exportHealthReport()" class="bg-gray-600 hover:bg-gray-700 text-white font-medium px-4 py-2 rounded-lg transition duration-200">
                Export Report
            </button>
        </div>

        <!-- Health Recommendations -->
        <div id="health-recommendations" class="hidden mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
            <h5 class="font-semibold text-blue-800 mb-2">Recommendations</h5>
            <ul id="recommendations-list" class="text-sm text-blue-700 space-y-1"></ul>
        </div>
    </div>
</div>

 