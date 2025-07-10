<?php
/**
 * Enhanced Error Analyzer Component
 * Advanced troubleshooting and diagnostic system for CallTrackingMetrics WordPress plugin
 * 
 * Features:
 * - Comprehensive system diagnostics
 * - Security vulnerability scanning
 * - Performance analysis
 * - Automated fix system
 * - Rollback capabilities
 * - Diagnostic history tracking
 */

// Ensure variables are available from parent context
$debugEnabled = $debugEnabled ?? get_option('ctm_debug_enabled', false);
?>

<div class="bg-white rounded-xl shadow-lg border border-gray-200 p-8 mb-8">
    <h3 class="text-xl font-semibold text-gray-800 mb-6 flex items-center">
        <svg class="w-6 h-6 text-red-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
        </svg>
        Enhanced Error Analyzer & Troubleshooter
    </h3>

    <div class="space-y-8">
        <!-- System Diagnostic Categories -->
        <div class="mb-8">
            <h4 class="text-lg font-semibold text-gray-800 mb-6 flex items-center">
                <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 00-2-2z"/>
                </svg>
                System Diagnostic Categories
            </h4>
            
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
                <!-- WordPress Environment -->
                <div class="p-6 bg-blue-50 border border-blue-200 rounded-lg">
                    <h5 class="font-semibold text-blue-800 mb-3 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9v-9m0-9v9"/>
                        </svg>
                        WordPress Environment
                    </h5>
                    <ul class="text-sm text-blue-700 space-y-2">
                        <li>• Version compatibility checks</li>
                        <li>• Database health analysis</li>
                        <li>• Theme compatibility testing</li>
                        <li>• Multisite configuration validation</li>
                    </ul>
                </div>

                <!-- Plugin Integration -->
                <div class="p-6 bg-green-50 border border-green-200 rounded-lg">
                    <h5 class="font-semibold text-green-800 mb-3 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                        Plugin Integration
                    </h5>
                    <ul class="text-sm text-green-700 space-y-2">
                        <li>• Contact Form 7 integration</li>
                        <li>• Gravity Forms compatibility</li>
                        <li>• Plugin conflict detection</li>
                        <li>• API endpoint validation</li>
                    </ul>
                </div>

                <!-- Security & Vulnerability -->
                <div class="p-6 bg-red-50 border border-red-200 rounded-lg">
                    <h5 class="font-semibold text-red-800 mb-3 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                        Security & Vulnerability
                    </h5>
                    <ul class="text-sm text-red-700 space-y-2">
                        <li>• Security headers analysis</li>
                        <li>• File permission security</li>
                        <li>• wp-config.php security scan</li>
                        <li>• Plugin vulnerability detection</li>
                    </ul>
                </div>

                <!-- Performance & Optimization -->
                <div class="p-6 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <h5 class="font-semibold text-yellow-800 mb-3 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        Performance & Optimization
                    </h5>
                    <ul class="text-sm text-yellow-700 space-y-2">
                        <li>• Caching optimization analysis</li>
                        <li>• Database query optimization</li>
                        <li>• Resource loading efficiency</li>
                        <li>• CDN configuration check</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
            <h4 class="text-lg font-semibold text-blue-800 mb-6 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7"/>
                </svg>
                Quick Actions
            </h4>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                <button onclick="runFullDiagnostic()" class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-6 py-3 rounded-lg transition duration-200 text-center">
                    🔍 Run Full Diagnostic
                </button>
                <button onclick="runSecurityScan()" class="bg-red-600 hover:bg-red-700 text-white font-medium px-6 py-3 rounded-lg transition duration-200 text-center">
                    🛡️ Security Vulnerability Scan
                </button>
                <button onclick="runPerformanceAnalysis()" class="bg-green-600 hover:bg-green-700 text-white font-medium px-6 py-3 rounded-lg transition duration-200 text-center">
                    ⚡ Performance Analysis
                </button>
                <button onclick="checkIssue('auto_fix')" class="bg-purple-600 hover:bg-purple-700 text-white font-medium px-6 py-3 rounded-lg transition duration-200 text-center">
                    🔧 Auto-Fix Common Issues
                </button>
                <button onclick="exportDiagnosticReport()" class="bg-gray-600 hover:bg-gray-700 text-white font-medium px-6 py-3 rounded-lg transition duration-200 text-center">
                    📄 Export Diagnostic Report
                </button>
                <button onclick="scheduleHealthCheck()" class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium px-6 py-3 rounded-lg transition duration-200 text-center">
                    📅 Schedule Health Monitoring
                </button>
                <button onclick="showRollbackManager()" class="bg-yellow-600 hover:bg-yellow-700 text-white font-medium px-6 py-3 rounded-lg transition duration-200 text-center">
                    ↩️ Rollback Manager
                </button>
            </div>
        </div>

        <!-- Analysis Results Area -->
        <div class="bg-gray-50 border border-gray-200 rounded-lg p-6">
            <h4 class="text-lg font-semibold text-gray-800 mb-6 flex items-center">
                <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Analysis Results
            </h4>
            <div id="error-analysis" class="min-h-[300px]">
                <div class="text-center py-16">
                    <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                    <h3 class="mt-6 text-xl font-medium text-gray-900">Ready for Analysis</h3>
                    <p class="mt-3 text-gray-500 max-w-md mx-auto">
                        Run a diagnostic to identify and resolve issues with your CallTrackingMetrics integration.
                    </p>
                    <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg max-w-lg mx-auto">
                        <p class="text-sm text-blue-700">
                            💡 <strong>Tip:</strong> Use "Run Full Diagnostic" for a complete system health check
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Status and History Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- System Status -->
            <div class="bg-green-50 border border-green-200 rounded-lg p-6">
                <h4 class="text-lg font-semibold text-green-800 mb-6 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    System Status
                </h4>
                <div class="space-y-4">
                    <div class="flex justify-between items-center p-3 bg-white rounded-lg border border-green-200">
                        <span class="text-gray-700 font-medium">Plugin Status:</span>
                        <span class="font-semibold text-green-600 bg-green-100 px-3 py-1 rounded-full text-sm">Active</span>
                    </div>
                    <div class="flex justify-between items-center p-3 bg-white rounded-lg border border-green-200">
                        <span class="text-gray-700 font-medium">Debug Mode:</span>
                        <span class="font-semibold <?= $debugEnabled ? 'text-green-600 bg-green-100' : 'text-gray-600 bg-gray-100' ?> px-3 py-1 rounded-full text-sm">
                            <?= $debugEnabled ? 'Enabled' : 'Disabled' ?>
                        </span>
                    </div>
                    <div class="flex justify-between items-center p-3 bg-white rounded-lg border border-green-200">
                        <span class="text-gray-700 font-medium">API Connection:</span>
                        <span class="font-semibold <?= get_option('ctm_api_key') ? 'text-green-600 bg-green-100' : 'text-red-600 bg-red-100' ?> px-3 py-1 rounded-full text-sm">
                            <?= get_option('ctm_api_key') ? 'Connected' : 'Not Connected' ?>
                        </span>
                    </div>
                    <div class="flex justify-between items-center p-3 bg-white rounded-lg border border-green-200">
                        <span class="text-gray-700 font-medium">Last Check:</span>
                        <span class="font-semibold text-gray-600 bg-gray-100 px-3 py-1 rounded-full text-sm">
                            <?= get_option('ctm_last_health_check', 'Never') ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Diagnostic History -->
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-6">
                <h4 class="text-lg font-semibold text-gray-800 mb-6 flex items-center justify-between">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Recent Diagnostics
                    </div>
                    <button onclick="clearDiagnosticHistory()" class="text-sm text-gray-500 hover:text-gray-700 bg-gray-200 hover:bg-gray-300 px-3 py-1 rounded transition duration-200">
                        Clear History
                    </button>
                </h4>
                <div id="diagnostic-history" class="space-y-3">
                    <div class="text-center text-gray-500 py-8">
                        <svg class="mx-auto h-12 w-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                        </svg>
                        <p class="text-sm">No recent diagnostics found</p>
                        <p class="text-xs text-gray-400 mt-1">Run a diagnostic to see results here</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> 