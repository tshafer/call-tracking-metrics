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

<script>
  
function runFullDiagnostic() {
    const analysisDiv = document.getElementById('error-analysis');
    
    analysisDiv.innerHTML = `
        <div class="p-8">
            <div class="text-center mb-6">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div>
                <h5 class="text-lg font-semibold text-gray-800">Running Full System Diagnostic</h5>
                <p class="text-sm text-gray-600">Analyzing all system components and configurations...</p>
            </div>
            <div class="space-y-3">
                <div class="flex items-center justify-between p-3 bg-white rounded border">
                    <span class="text-sm text-gray-700">Checking API connectivity...</span>
                    <div class="animate-pulse w-4 h-4 bg-blue-500 rounded-full"></div>
                </div>
                <div class="flex items-center justify-between p-3 bg-white rounded border">
                    <span class="text-sm text-gray-700">Validating form integrations...</span>
                    <div class="animate-pulse w-4 h-4 bg-blue-500 rounded-full"></div>
                </div>
                <div class="flex items-center justify-between p-3 bg-white rounded border">
                    <span class="text-sm text-gray-700">Testing network configuration...</span>
                    <div class="animate-pulse w-4 h-4 bg-blue-500 rounded-full"></div>
                </div>
                <div class="flex items-center justify-between p-3 bg-white rounded border">
                    <span class="text-sm text-gray-700">Scanning for plugin conflicts...</span>
                    <div class="animate-pulse w-4 h-4 bg-blue-500 rounded-full"></div>
                </div>
            </div>
        </div>
    `;
    
    const formData = new FormData();
    formData.append('action', 'ctm_full_diagnostic');
    formData.append('nonce', '<?= wp_create_nonce('ctm_full_diagnostic') ?>');
    
    fetch('<?= admin_url('admin-ajax.php') ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displayDiagnosticResults(data.data);
            showDebugMessage('Full diagnostic completed successfully', 'success');
        } else {
            analysisDiv.innerHTML = '<div class="p-8 text-center text-red-600">Diagnostic failed to run</div>';
            showDebugMessage('Full diagnostic failed to run', 'error');
        }
    })
    .catch(error => {
        analysisDiv.innerHTML = '<div class="p-8 text-center text-red-600">Network error during diagnostic</div>';
        showDebugMessage('Network error during full diagnostic', 'error');
    });
}  


function displayDiagnosticResults(results) {
    const analysisDiv = document.getElementById('error-analysis');
    
    let html = `
        <div class="p-6">
            <div class="mb-6">
                <h5 class="text-xl font-semibold text-gray-800 mb-2">Diagnostic Results</h5>
                <div class="flex items-center space-x-4 text-sm">
                    <span class="text-green-600">✓ ${results.passed_checks} Passed</span>
                    <span class="text-yellow-600">⚠ ${results.warning_checks} Warnings</span>
                    <span class="text-red-600">✗ ${results.failed_checks} Failed</span>
                </div>
            </div>
    `;
    
    // Display critical issues first
    if (results.critical_issues && results.critical_issues.length > 0) {
        html += `
            <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                <h6 class="font-semibold text-red-800 mb-3 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.08 15.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                    Critical Issues Requiring Immediate Attention
                </h6>
                <div class="space-y-3">
        `;
        
        results.critical_issues.forEach(issue => {
            html += `
                <div class="bg-white p-3 rounded border border-red-200">
                    <div class="font-medium text-red-800 mb-1">${issue.title}</div>
                    <div class="text-sm text-red-700 mb-2">${issue.description}</div>
                    ${issue.auto_fix_available ? 
                        `<button onclick="autoFixIssue('${issue.fix_id}')" class="bg-red-600 hover:bg-red-700 text-white text-xs px-3 py-1 rounded">Auto-Fix</button>` : 
                        '<span class="text-xs text-red-600">Manual fix required</span>'
                    }
                </div>
            `;
        });
        
        html += '</div></div>';
    }
    
    // Display detailed results by category
    if (results.categories) {
        html += '<div class="space-y-6">';
        
        Object.entries(results.categories).forEach(([category, data]) => {
            const statusColor = data.status === 'healthy' ? 'green' : data.status === 'warning' ? 'yellow' : 'red';
            const statusIcon = data.status === 'healthy' ? '✓' : data.status === 'warning' ? '⚠' : '✗';
            
            html += `
                <div class="border border-gray-200 rounded-lg">
                    <div class="p-4 bg-${statusColor}-50 border-b border-${statusColor}-200">
                        <h6 class="font-semibold text-${statusColor}-800 flex items-center">
                            <span class="mr-2">${statusIcon}</span>
                            ${data.title}
                            <span class="ml-auto text-sm">${data.score}/100</span>
                        </h6>
                    </div>
                    <div class="p-4">
                        <p class="text-sm text-gray-600 mb-3">${data.description}</p>
            `;
            
            if (data.issues && data.issues.length > 0) {
                html += '<div class="mb-3"><strong class="text-red-600">Issues:</strong><ul class="mt-1 space-y-1">';
                data.issues.forEach(issue => {
                    html += `<li class="text-sm text-red-700">• ${issue}</li>`;
                });
                html += '</ul></div>';
            }
            
            if (data.recommendations && data.recommendations.length > 0) {
                html += '<div><strong class="text-green-600">Recommendations:</strong><ul class="mt-1 space-y-1">';
                data.recommendations.forEach(rec => {
                    html += `<li class="text-sm text-green-700">• ${rec}</li>`;
                });
                html += '</ul></div>';
            }
            
            html += '</div></div>';
        });
        
        html += '</div>';
    }
    
    html += '</div>';
    
    analysisDiv.innerHTML = html;
}


function runSecurityScan() {
    const analysisDiv = document.getElementById('error-analysis');
    
    analysisDiv.innerHTML = `
        <div class="p-8">
            <div class="text-center mb-6">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-red-600 mx-auto mb-4"></div>
                <h5 class="text-lg font-semibold text-gray-800">Running Security Vulnerability Scan</h5>
                <p class="text-sm text-gray-600">Checking for security issues and vulnerabilities...</p>
            </div>
            <div class="space-y-3 text-sm">
                <div class="flex items-center space-x-2">
                    <div class="w-2 h-2 bg-red-500 rounded-full animate-pulse"></div>
                    <span>Scanning security headers</span>
                </div>
                <div class="flex items-center space-x-2">
                    <div class="w-2 h-2 bg-red-500 rounded-full animate-pulse"></div>
                    <span>Checking file permissions</span>
                </div>
                <div class="flex items-center space-x-2">
                    <div class="w-2 h-2 bg-red-500 rounded-full animate-pulse"></div>
                    <span>Analyzing wp-config.php security</span>
                </div>
                <div class="flex items-center space-x-2">
                    <div class="w-2 h-2 bg-red-500 rounded-full animate-pulse"></div>
                    <span>Scanning for plugin vulnerabilities</span>
                </div>
                <div class="flex items-center space-x-2">
                    <div class="w-2 h-2 bg-red-500 rounded-full animate-pulse"></div>
                    <span>Checking SSL configuration</span>
                </div>
            </div>
        </div>
    `;
    
    const formData = new FormData();
    formData.append('action', 'ctm_security_scan');
    formData.append('nonce', '<?= wp_create_nonce('ctm_security_scan') ?>');
    
    fetch('<?= admin_url('admin-ajax.php') ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displaySecurityScanResults(data.data.results);
            addToDiagnosticHistory('Security Scan', data.data.results.security_score);
            showDebugMessage('Security scan completed', 'success');
        } else {
            analysisDiv.innerHTML = '<div class="p-8 text-center text-red-600">Security scan failed</div>';
            showDebugMessage('Security scan failed', 'error');
        }
    })
    .catch(error => {
        analysisDiv.innerHTML = '<div class="p-8 text-center text-red-600">Network error during security scan</div>';
        showDebugMessage('Network error during security scan', 'error');
    });
}


function runPerformanceAnalysis() {
    const analysisDiv = document.getElementById('error-analysis');
    
    analysisDiv.innerHTML = `
        <div class="p-8">
            <div class="text-center mb-6">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-green-600 mx-auto mb-4"></div>
                <h5 class="text-lg font-semibold text-gray-800">Running Performance Analysis</h5>
                <p class="text-sm text-gray-600">Analyzing site performance and optimization opportunities...</p>
            </div>
            <div class="space-y-3 text-sm">
                <div class="flex items-center space-x-2">
                    <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                    <span>Analyzing caching configuration</span>
                </div>
                <div class="flex items-center space-x-2">
                    <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                    <span>Checking database query performance</span>
                </div>
                <div class="flex items-center space-x-2">
                    <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                    <span>Analyzing resource loading times</span>
                </div>
                <div class="flex items-center space-x-2">
                    <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                    <span>Checking CDN configuration</span>
                </div>
                <div class="flex items-center space-x-2">
                    <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                    <span>Measuring page load metrics</span>
                </div>
            </div>
        </div>
    `;
    
    const formData = new FormData();
    formData.append('action', 'ctm_performance_analysis');
    formData.append('nonce', '<?= wp_create_nonce('ctm_performance_analysis') ?>');
    
    fetch('<?= admin_url('admin-ajax.php') ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displayPerformanceAnalysisResults(data.data.results);
            addToDiagnosticHistory('Performance Analysis', data.data.results.performance_score);
            showDebugMessage('Performance analysis completed', 'success');
        } else {
            analysisDiv.innerHTML = '<div class="p-8 text-center text-red-600">Performance analysis failed</div>';
            showDebugMessage('Performance analysis failed', 'error');
        }
    })
    .catch(error => {
        analysisDiv.innerHTML = '<div class="p-8 text-center text-red-600">Network error during performance analysis</div>';
        showDebugMessage('Network error during performance analysis', 'error');
    });
}

function scheduleHealthCheck() {
    showDebugMessage('Opening health monitoring scheduler...', 'info');
    
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
    modal.innerHTML = `
        <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
            <h5 class="text-lg font-semibold text-gray-800 mb-4">Schedule Health Monitoring</h5>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Monitoring Frequency</label>
                    <select id="monitoring-frequency" class="w-full p-2 border border-gray-300 rounded">
                        <option value="hourly">Every Hour</option>
                        <option value="daily" selected>Daily</option>
                        <option value="weekly">Weekly</option>
                        <option value="monthly">Monthly</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email Notifications</label>
                    <input type="email" id="notification-email" placeholder="admin@example.com" class="w-full p-2 border border-gray-300 rounded">
                </div>
                <div class="flex items-center space-x-2">
                    <input type="checkbox" id="critical-only" class="rounded">
                    <label for="critical-only" class="text-sm text-gray-700">Only notify for critical issues</label>
                </div>
            </div>
            <div class="flex space-x-3 mt-6">
                <button onclick="saveHealthMonitoring()" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                    Save Settings
                </button>
                <button onclick="closeModal()" class="px-4 py-2 border border-gray-300 rounded hover:bg-gray-50">
                    Cancel
                </button>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    modal.onclick = (e) => {
        if (e.target === modal) closeModal();
    };
    
    window.closeModal = () => {
        document.body.removeChild(modal);
        delete window.closeModal;
        delete window.saveHealthMonitoring;
    };
    
    window.saveHealthMonitoring = () => {
        const frequency = document.getElementById('monitoring-frequency').value;
        const email = document.getElementById('notification-email').value;
        const criticalOnly = document.getElementById('critical-only').checked;
        
        const formData = new FormData();
        formData.append('action', 'ctm_schedule_health_check');
        formData.append('frequency', frequency);
        formData.append('email', email);
        formData.append('critical_only', criticalOnly ? '1' : '0');
        formData.append('nonce', '<?= wp_create_nonce('ctm_schedule_health_check') ?>');
        
        fetch('<?= admin_url('admin-ajax.php') ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showDebugMessage('Health monitoring scheduled successfully', 'success');
            } else {
                showDebugMessage('Failed to schedule health monitoring', 'error');
            }
            closeModal();
        })
        .catch(error => {
            showDebugMessage('Network error while scheduling', 'error');
            closeModal();
        });
    };
}


function showRollbackManager() {
    const rollbacks = JSON.parse(localStorage.getItem('ctm_rollback_history') || '[]');
    
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
    modal.innerHTML = `
        <div class="bg-white rounded-lg p-6 max-w-2xl w-full mx-4 max-h-96 overflow-y-auto">
            <h5 class="text-lg font-semibold text-gray-800 mb-4">Rollback Manager</h5>
            <div class="space-y-3">
                ${rollbacks.length === 0 ? 
                    '<div class="text-center text-gray-500 py-8">No rollback points available</div>' :
                    rollbacks.map(rollback => `
                        <div class="flex items-center justify-between p-3 bg-gray-50 border border-gray-200 rounded">
                            <div>
                                <div class="font-medium text-gray-800">${rollback.description}</div>
                                <div class="text-sm text-gray-600">${new Date(rollback.timestamp).toLocaleString()}</div>
                            </div>
                            <button onclick="executeRollback('${rollback.id}')" class="bg-red-600 hover:bg-red-700 text-white text-sm px-3 py-1 rounded">
                                Rollback
                            </button>
                        </div>
                    `).join('')
                }
            </div>
            <div class="flex space-x-3 mt-6">
                <button onclick="clearRollbackHistory()" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded">
                    Clear History
                </button>
                <button onclick="closeRollbackModal()" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                    Close
                </button>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    window.closeRollbackModal = () => {
        document.body.removeChild(modal);
        delete window.closeRollbackModal;
        delete window.executeRollback;
        delete window.clearRollbackHistory;
    };
    
    window.executeRollback = (rollbackId) => {
        if (!confirm('Are you sure you want to rollback this change? This will reverse the automated fix.')) {
            return;
        }
        
        const formData = new FormData();
        formData.append('action', 'ctm_execute_rollback');
        formData.append('rollback_id', rollbackId);
        formData.append('nonce', '<?= wp_create_nonce('ctm_execute_rollback') ?>');
        
        fetch('<?= admin_url('admin-ajax.php') ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showDebugMessage('Rollback executed successfully', 'success');
                closeRollbackModal();
                
                // Refresh analysis
                setTimeout(() => {
                    if (typeof runFullDiagnostic === 'function') {
                        runFullDiagnostic();
                    }
                }, 2000);
            } else {
                showDebugMessage(`Rollback failed: ${data.data.error}`, 'error');
            }
        })
        .catch(error => {
            showDebugMessage('Network error during rollback', 'error');
        });
    };
    
    window.clearRollbackHistory = () => {
        if (confirm('Are you sure you want to clear all rollback history?')) {
            localStorage.removeItem('ctm_rollback_history');
            showDebugMessage('Rollback history cleared', 'info');
            closeRollbackModal();
        }
    };
}


function addToDiagnosticHistory(type, score) {
    const historyDiv = document.getElementById('diagnostic-history');
    if (!historyDiv) return;
    
    const now = new Date();
    const timeStr = now.toLocaleTimeString();
    
    // Remove "no diagnostics" message if present
    const noDataMsg = historyDiv.querySelector('.text-center');
    if (noDataMsg) {
        noDataMsg.remove();
    }
    
    const scoreColor = score >= 80 ? 'text-green-600' : score >= 60 ? 'text-yellow-600' : 'text-red-600';
    
    const historyItem = document.createElement('div');
    historyItem.className = 'flex items-center justify-between p-2 bg-white border border-gray-200 rounded text-sm';
    historyItem.innerHTML = `
        <div>
            <span class="font-medium">${type}</span>
            <span class="text-gray-500 ml-2">${timeStr}</span>
        </div>
        <span class="font-semibold ${scoreColor}">${score}/100</span>
    `;
    
    historyDiv.insertBefore(historyItem, historyDiv.firstChild);
    
    // Keep only last 5 items
    const items = historyDiv.querySelectorAll('.flex');
    if (items.length > 5) {
        items[items.length - 1].remove();
    }
}


function displayPerformanceAnalysisResults(results) {
    const analysisDiv = document.getElementById('error-analysis');
    
    let html = `
        <div class="p-6">
            <div class="mb-6">
                <h5 class="text-xl font-semibold text-gray-800 mb-2">Performance Analysis Results</h5>
                <div class="flex items-center space-x-4">
                    <div class="text-2xl font-bold ${results.performance_score >= 80 ? 'text-green-600' : results.performance_score >= 60 ? 'text-yellow-600' : 'text-red-600'}">
                        ${results.performance_score}/100
                    </div>
                    <div class="text-sm text-gray-600">Performance Score</div>
                </div>
            </div>
    `;
    
    if (results.metrics) {
        const cacheHitRate = results.metrics.cache_hit_rate;
        const cacheHitRateDisplay = (typeof cacheHitRate === 'number' && !isNaN(cacheHitRate)) ? `${cacheHitRate}%` : 'N/A';
        html += `
            <div class="grid grid-cols-2 gap-4 mb-6">
                <div class="p-3 bg-blue-50 border border-blue-200 rounded">
                    <div class="text-sm text-blue-600">Page Load Time</div>
                    <div class="text-lg font-semibold text-blue-800">${results.metrics.load_time}ms</div>
                </div>
                <div class="p-3 bg-purple-50 border border-purple-200 rounded">
                    <div class="text-sm text-purple-600">Database Queries</div>
                    <div class="text-lg font-semibold text-purple-800">${results.metrics.db_queries}</div>
                </div>
                <div class="p-3 bg-green-50 border border-green-200 rounded">
                    <div class="text-sm text-green-600">Memory Usage</div>
                    <div class="text-lg font-semibold text-green-800">${results.metrics.memory_usage}MB</div>
                </div>
                <div class="p-3 bg-orange-50 border border-orange-200 rounded">
                    <div class="text-sm text-orange-600">Cache Hit Rate</div>
                    <div class="text-lg font-semibold text-orange-800">${cacheHitRateDisplay}</div>
                </div>
            </div>
        `;
    }
    
    if (results.optimizations) {
        html += `
            <div class="space-y-4">
                <h6 class="font-semibold text-green-600">⚡ Performance Optimizations</h6>
                <div class="space-y-2">
        `;
        
        results.optimizations.forEach(opt => {
            html += `<div class="p-3 bg-green-50 border border-green-200 rounded text-sm text-green-700">• ${opt}</div>`;
        });
        
        html += '</div></div>';
    }
    
    html += '</div>';
    analysisDiv.innerHTML = html;
}


function exportDiagnosticReport() {
    showDebugMessage('Generating diagnostic report...', 'info');
    
    const formData = new FormData();
    formData.append('action', 'ctm_export_diagnostic_report');
    formData.append('nonce', '<?= wp_create_nonce('ctm_export_diagnostic_report') ?>');
    
    fetch('<?= admin_url('admin-ajax.php') ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Create download link
            const blob = new Blob([data.data.report], { type: 'text/plain' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `ctm-diagnostic-report-${new Date().toISOString().slice(0, 10)}.txt`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);
            
            showDebugMessage('Diagnostic report exported successfully', 'success');
        } else {
            showDebugMessage('Failed to export diagnostic report', 'error');
        }
    })
    .catch(error => {
        showDebugMessage('Network error during export', 'error');
    });
}


function checkIssue(issueType) {
    const analysisDiv = document.getElementById('error-analysis');
    
    analysisDiv.innerHTML = `
        <div class="p-8">
            <div class="text-center mb-6">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto mb-4"></div>
                <h5 class="text-lg font-semibold text-gray-800">Analyzing Issue</h5>
                <p class="text-sm text-gray-600">Running comprehensive analysis...</p>
            </div>
        </div>
    `;
    
    const formData = new FormData();
    formData.append('action', 'ctm_analyze_issue');
    formData.append('issue_type', issueType);
    formData.append('nonce', '<?= wp_create_nonce('ctm_analyze_issue') ?>');
    
    fetch('<?= admin_url('admin-ajax.php') ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displayIssueAnalysis(data.data.analysis);
            showDebugMessage('Issue analysis completed', 'success');
        } else {
            analysisDiv.innerHTML = '<div class="p-8 text-center text-red-600">Analysis failed</div>';
            showDebugMessage('Issue analysis failed', 'error');
        }
    })
    .catch(error => {
        analysisDiv.innerHTML = '<div class="p-8 text-center text-red-600">Network error</div>';
        showDebugMessage('Network error during analysis', 'error');
    });
}


function displayIssueAnalysis(analysis) {
    const analysisDiv = document.getElementById('error-analysis');
    
    let html = `
        <div class="p-6">
            <h5 class="text-xl font-semibold text-gray-800 mb-4">Issue Analysis Results</h5>
    `;
    
    if (analysis.issues && analysis.issues.length > 0) {
        html += '<div class="space-y-4">';
        
        analysis.issues.forEach(issue => {
            const severityColor = issue.severity === 'critical' ? 'red' : issue.severity === 'warning' ? 'yellow' : 'blue';
            
            html += `
                <div class="border border-${severityColor}-200 rounded-lg p-4 bg-${severityColor}-50">
                    <h6 class="font-semibold text-${severityColor}-800 mb-2">${issue.title}</h6>
                    <p class="text-sm text-${severityColor}-700 mb-3">${issue.description}</p>
                    
                    ${issue.solution ? `
                        <div class="bg-white p-3 rounded border border-${severityColor}-200">
                            <strong class="text-${severityColor}-800">Recommended Solution:</strong>
                            <p class="text-sm text-${severityColor}-700 mt-1">${issue.solution}</p>
                        </div>
                    ` : ''}
                </div>
            `;
        });
        
        html += '</div>';
    } else {
        html += '<div class="text-center py-8 text-green-600">No issues detected in this category.</div>';
    }
    
    html += '</div>';
    
    analysisDiv.innerHTML = html;
}

function clearDiagnosticHistory() {
    const historyDiv = document.getElementById('diagnostic-history');
    if (historyDiv) {
        historyDiv.innerHTML = '<div class="text-center text-gray-500 text-sm py-4">No recent diagnostics found</div>';
        showDebugMessage('Diagnostic history cleared', 'info');
    }
}


function displaySecurityScanResults(results) {
    const analysisDiv = document.getElementById('error-analysis');
    
    let html = `
        <div class="p-6">
            <div class="mb-6">
                <h5 class="text-xl font-semibold text-gray-800 mb-2">Security Scan Results</h5>
                <div class="flex items-center space-x-4">
                    <div class="text-2xl font-bold ${results.security_score >= 80 ? 'text-green-600' : results.security_score >= 60 ? 'text-yellow-600' : 'text-red-600'}">
                        ${results.security_score}/100
                    </div>
                    <div class="text-sm text-gray-600">Security Score</div>
                </div>
            </div>
    `;
    
    if (results.vulnerabilities && results.vulnerabilities.length > 0) {
        html += `
            <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                <h6 class="font-semibold text-red-800 mb-3">🚨 Security Vulnerabilities Found</h6>
                <div class="space-y-2">
        `;
        
        results.vulnerabilities.forEach(vuln => {
            html += `
                <div class="bg-white p-3 rounded border border-red-200">
                    <div class="font-medium text-red-800">${vuln.title}</div>
                    <div class="text-sm text-red-700">${vuln.description}</div>
                    <div class="text-xs text-red-600 mt-1">Severity: ${vuln.severity}</div>
                </div>
            `;
        });
        
        html += '</div></div>';
    }
    
    if (results.recommendations) {
        html += `
            <div class="space-y-4">
                <h6 class="font-semibold text-green-600">🛡️ Security Recommendations</h6>
                <div class="space-y-2">
        `;
        
        results.recommendations.forEach(rec => {
            html += `<div class="p-3 bg-green-50 border border-green-200 rounded text-sm text-green-700">• ${rec}</div>`;
        });
        
        html += '</div></div>';
    }
    
    html += '</div>';
    analysisDiv.innerHTML = html;
}


// Initialize email field requirement state
document.addEventListener('DOMContentLoaded', function() {
    const emailNotifications = document.getElementById('log_email_notifications');
    if (emailNotifications && emailNotifications.checked) {
        emailNotifications.dispatchEvent(new Event('change'));
    }
    
    // Initialize performance monitoring on page load
    pageLoadStart = performance.now();
    setTimeout(refreshPerformance, 1000);
    
    // Load diagnostic history from localStorage if available
    const savedHistory = localStorage.getItem('ctm_diagnostic_history');
    if (savedHistory) {
        try {
            const history = JSON.parse(savedHistory);
            history.forEach(item => {
                addToDiagnosticHistory(item.type, item.score);
            });
        } catch (e) {
            console.log('Failed to load diagnostic history');
        }
    }
    
    // Auto-run health check on page load
    setTimeout(() => {
        const healthCheckButton = document.getElementById('health-check-btn');
        if (healthCheckButton) {
            console.log('Auto-running health check on page load...');
            runHealthCheck();
        } else {
            console.log('Health check button not found - may not be on debug tab');
        }
    }, 2000); // Wait 2 seconds after page load to ensure everything is ready
});
</script>