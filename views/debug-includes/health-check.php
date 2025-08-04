<?php
/**
 * Plugin Health Check Component
 * Comprehensive system assessment and health monitoring
 */
?>

<div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6">
    <div class="flex items-center justify-between mb-4 gap-4">
        <div class="flex items-center gap-2">
            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <h3 class="text-2xl font-extrabold text-gray-900"><?php _e('Plugin Health Check', 'call-tracking-metrics'); ?></h3>
        </div>
        <div class="flex justify-center my-4">
            <button onclick="exportHealthReport()" class="bg-gray-600 hover:bg-gray-700 !text-white font-medium px-6 py-2 rounded-xl flex items-center gap-2 justify-center transition" type="button">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <?php _e('Export Health Report', 'call-tracking-metrics'); ?>
            </button>
        </div>
    </div>
    
    <div class="space-y-4">
        <!-- Overall Health Score -->
        <div class="bg-gradient-to-r from-green-50 to-blue-50 border border-green-200 rounded-lg p-4">
            <div class="flex items-center justify-between">
                <div>
                    <h4 class="text-lg font-semibold text-green-800"><?php _e('Overall Health Score', 'call-tracking-metrics'); ?></h4>
                    <p class="text-sm text-green-600"><?php _e('System status assessment', 'call-tracking-metrics'); ?></p>
                </div>
                <div class="text-right">
                    <div id="health-score" class="text-3xl font-bold text-green-600">--</div>
                    <div class="text-sm text-green-700"><?php _e('out of 100', 'call-tracking-metrics'); ?></div>
                </div>
            </div>
        </div>

        <!-- Health Check Categories -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- API Configuration -->
            <div class="border border-gray-200 rounded-lg p-4">
                <div class="flex items-center gap-1 mb-2">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                    </svg>
                    <h5 class="text-lg font-bold text-gray-900"><?php _e('API Configuration', 'call-tracking-metrics'); ?></h5>
                </div>
                <div id="health-api" class="space-y-2">
                    <div class="flex items-center justify-between text-sm">
                        <span><?php _e('API Key Configured', 'call-tracking-metrics'); ?></span>
                        <span id="check-api-key" class="health-indicator">⏳</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span><?php _e('API Connection', 'call-tracking-metrics'); ?></span>
                        <span id="check-api-connection" class="health-indicator">⏳</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span><?php _e('Account Access', 'call-tracking-metrics'); ?></span>
                        <span id="check-account-access" class="health-indicator">⏳</span>
                    </div>
                </div>
            </div>

            <!-- Form Integration -->
            <div class="border border-gray-200 rounded-lg p-4">
                <div class="flex items-center gap-1 mb-2">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <h5 class="text-lg font-bold text-gray-900"><?php _e('Form Integration', 'call-tracking-metrics'); ?></h5>
                </div>
                <div id="health-forms" class="space-y-2">
                    <div class="flex items-center justify-between text-sm">
                        <span><?php _e('Contact Form 7', 'call-tracking-metrics'); ?></span>
                        <span id="check-cf7" class="health-indicator">⏳</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span><?php _e('Gravity Forms', 'call-tracking-metrics'); ?></span>
                        <span id="check-gf" class="health-indicator">⏳</span>
                    </div>
                </div>
            </div>

            <!-- Server Requirements -->
            <div class="border border-gray-200 rounded-lg p-4">
                <div class="flex items-center gap-1 mb-2">
                    <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"></path>
                    </svg>
                    <h5 class="text-lg font-bold text-gray-900"><?php _e('Server Requirements', 'call-tracking-metrics'); ?></h5>
                </div>
                <div id="health-server" class="space-y-2">
                    <div class="flex items-center justify-between text-sm">
                        <span><?php _e('PHP Version (7.4+)', 'call-tracking-metrics'); ?></span>
                        <span id="check-php-version" class="health-indicator">⏳</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span><?php _e('cURL Extension', 'call-tracking-metrics'); ?></span>
                        <span id="check-curl" class="health-indicator">⏳</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span><?php _e('SSL Support', 'call-tracking-metrics'); ?></span>
                        <span id="check-ssl" class="health-indicator">⏳</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span><?php _e('Memory Limit', 'call-tracking-metrics'); ?></span>
                        <span id="check-memory" class="health-indicator">⏳</span>
                    </div>
                </div>
            </div>

            <!-- Plugin Status -->
            <div class="border border-gray-200 rounded-lg p-4">
                <div class="flex items-center gap-1 mb-2">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4V2a1 1 0 011-1h8a1 1 0 011 1v2m0 0V1a1 1 0 011-1h2a1 1 0 011 1v3M7 4H5a1 1 0 00-1 1v3m0 0v8a2 2 0 002 2h10a2 2 0 002-2V8m0 0V5a1 1 0 00-1-1h-2M7 4h10"/>
                    </svg>
                    <h5 class="text-lg font-bold text-gray-900"><?php _e('Plugin Status', 'call-tracking-metrics'); ?></h5>
                </div>
                <div id="health-plugin" class="space-y-2">
                    <div class="flex items-center justify-between text-sm">
                        <span><?php _e('Plugin Version', 'call-tracking-metrics'); ?></span>
                        <span id="check-plugin-version" class="health-indicator">⏳</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span><?php _e('Database Tables', 'call-tracking-metrics'); ?></span>
                        <span id="check-database-tables" class="health-indicator">⏳</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span><?php _e('File Permissions', 'call-tracking-metrics'); ?></span>
                        <span id="check-file-permissions" class="health-indicator">⏳</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span><?php _e('Debug Mode', 'call-tracking-metrics'); ?></span>
                        <span id="check-debug-mode" class="health-indicator">⏳</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex gap-3 pt-4 border-t border-gray-200">
            <button onclick="runHealthCheck()" id="health-check-btn" class="bg-green-600 hover:bg-green-700 !text-white font-medium px-6 py-2 rounded-lg transition duration-200 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                </svg>
                <?php _e('Run Health Check', 'call-tracking-metrics'); ?>
            </button>
        </div>

        <!-- Health Recommendations -->
        <div id="health-recommendations" class="hidden mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
            <h5 class="font-semibold text-blue-800 mb-2"><?php _e('Recommendations', 'call-tracking-metrics'); ?></h5>
            <ul id="recommendations-list" class="text-sm text-blue-700 space-y-1"></ul>
        </div>
    </div>
</div>
<script>



// Export Health Report
function exportHealthReport() {
    const button = event.target;
    const originalText = button.textContent;
    
    button.disabled = true;
    button.textContent = 'Generating...';
    
    // Collect current health check data
    const healthData = {
        timestamp: new Date().toISOString(),
        overall_score: document.getElementById('health-score')?.textContent || 'N/A',
        checks: []
    };
    
    // Collect individual check results
    const checkElements = document.querySelectorAll('.health-indicator');
    checkElements.forEach(element => {
        const checkName = element.parentElement?.querySelector('span')?.textContent || 'Unknown';
        const status = element.textContent === '✓' ? 'pass' : 
                     element.textContent === '⚠' ? 'warning' : 
                     element.textContent === '✗' ? 'fail' : 'pending';
        const message = element.title || '';
        
        healthData.checks.push({
            name: checkName,
            status: status,
            message: message
        });
    });
    
    // Get system information from the correct elements
    const getSystemInfo = () => {
        // Get WordPress version from system info panel
        const wpVersionElement = document.querySelector('[data-metric="wp_version"] .text-2xl');
        const wpVersion = wpVersionElement?.textContent || 'N/A';
        
        // Get PHP version from system info panel
        const phpVersionElement = document.querySelector('[data-metric="php_version"] .text-2xl');
        const phpVersion = phpVersionElement?.textContent || 'N/A';
        
        // Get memory usage from system info panel
        const memoryElement = document.querySelector('[data-metric="memory_usage"] .text-2xl');
        const memoryUsage = memoryElement?.textContent || 'N/A';
        
        // Plugin version is hardcoded as 2.0 in the system
        const pluginVersion = '2.0';
        
        return {
            wordpress_version: wpVersion,
            php_version: phpVersion,
            plugin_version: pluginVersion,
            memory_usage: memoryUsage
        };
    };
    
    const systemInfo = getSystemInfo();
    
    // Generate downloadable report
    const reportContent = `
Call Tracking Metrics - Health Report
Generated: ${new Date().toLocaleString()}

Overall Health Score: ${healthData.overall_score}/100

Detailed Check Results:
${healthData.checks.map(check => 
    `${check.name}: ${check.status.toUpperCase()}${check.message ? ' - ' + check.message : ''}`
).join('\n')}

System Information:
- WordPress Version: ${systemInfo.wordpress_version}
- PHP Version: ${systemInfo.php_version}
- Plugin Version: ${systemInfo.plugin_version}
- Memory Usage: ${systemInfo.memory_usage}

Report generated by Call Tracking Metrics Plugin
    `.trim();
    
    // Create and download file
    const blob = new Blob([reportContent], { type: 'text/plain' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `ctm-health-report-${new Date().toISOString().split('T')[0]}.txt`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);
    
    button.disabled = false;
    button.textContent = originalText;
    showDebugMessage('Health report exported successfully', 'success');
}

// Plugin Health Check
function runHealthCheck() {
    const button = document.getElementById('health-check-btn');
    
    if (!button) {
        showDebugMessage('Health check button not found', 'error');
        return;
    }
    
    button.disabled = true;
    button.innerHTML = `
        <svg class="w-4 h-4 mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
        </svg>
        Running Checks...
    `;
    
    // Reset all health indicators to loading state
    const healthIndicators = document.querySelectorAll('.health-indicator');
    healthIndicators.forEach(indicator => {
        indicator.textContent = '⏳';
        indicator.className = 'health-indicator text-blue-600';
    });
    
    const formData = new FormData();
    formData.append('action', 'ctm_health_check');
    formData.append('nonce', '<?= wp_create_nonce('ctm_health_check') ?>');
    
    fetch('<?= admin_url('admin-ajax.php') ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const checks = data.data.checks;
            
            // Update individual check indicators
            const checkMapping = {
                'API Key': 'check-api-key',
                'API Connection': 'check-api-connection',
                'Account Access': 'check-account-access',
                'Contact Form 7': 'check-cf7',
                'Gravity Forms': 'check-gf',
                'PHP Version': 'check-php-version',
                'cURL Extension': 'check-curl',
                'SSL Support': 'check-ssl',
                'Memory Limit': 'check-memory',
                'Plugin Version': 'check-plugin-version',
                'Database Tables': 'check-database-tables',
                'File Permissions': 'check-file-permissions',
                'Debug Mode': 'check-debug-mode'
            };
            
            checks.forEach(check => {
                const elementId = checkMapping[check.name];
                if (elementId) {
                    const element = document.getElementById(elementId);
                    if (element) {
                        const icon = check.status === 'pass' ? '✓' : 
                                   check.status === 'warning' ? '⚠' : '✗';
                        const colorClass = check.status === 'pass' ? 'text-green-600' : 
                                         check.status === 'warning' ? 'text-yellow-600' : 'text-red-600';
                        
                        element.textContent = icon;
                        element.className = `health-indicator ${colorClass} cursor-help`;
                        
                        // Enhanced tooltip with status-specific information
                        let tooltipText = check.message;
                        if (check.status === 'fail') {
                            tooltipText = `❌ FAILED: ${check.message}`;
                        } else if (check.status === 'warning') {
                            tooltipText = `⚠️ WARNING: ${check.message}`;
                        } else if (check.status === 'pass') {
                            tooltipText = `✅ PASSED: ${check.message}`;
                        }
                        
                        element.title = tooltipText;
                        
                        // Add click handler for mobile/better UX
                        element.onclick = function(e) {
                            e.preventDefault();
                            showDetailedCheckInfo(check.name, check.status, check.message);
                        };
                        
                        // Add hover styling for better indication
                        element.style.cursor = 'help';
                    }
                }
            });
            
            // Update overall health score
            const healthScore = document.getElementById('health-score');
            if (healthScore) {
                const passedChecks = checks.filter(c => c.status === 'pass').length;
                const totalChecks = checks.length;
                const score = Math.round((passedChecks / totalChecks) * 100);
                
                console.log(`Health Score Calculation: ${passedChecks}/${totalChecks} = ${score}%`);
                
                healthScore.textContent = score;
                healthScore.className = score >= 80 ? 'text-3xl font-bold text-green-600' :
                                       score >= 60 ? 'text-3xl font-bold text-yellow-600' :
                                       'text-3xl font-bold text-red-600';
            } else {
                console.log('Health score element not found');
            }
            
            const failedChecks = checks.filter(c => c.status === 'fail').length;
            const warningChecks = checks.filter(c => c.status === 'warning').length;
            
            if (failedChecks === 0 && warningChecks === 0) {
                showDebugMessage('All health checks passed!', 'success');
            } else if (failedChecks > 0) {
                showDebugMessage(`Health check completed with ${failedChecks} failures and ${warningChecks} warnings`, 'error');
            } else {
                showDebugMessage(`Health check completed with ${warningChecks} warnings`, 'warning');
            }
        } else {
            showDebugMessage('Health check failed to run', 'error');
        }
    })
    .catch(error => {
        console.error('Health check error:', error);
        showDebugMessage('Network error during health check', 'error');
    })
    .finally(() => {
        button.disabled = false;
        button.innerHTML = `
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            Run Health Check
        `;
    });
}



// Show detailed check information
function showDetailedCheckInfo(checkName, status, message) {
    // Create modal overlay
    const modalOverlay = document.createElement('div');
    modalOverlay.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
    modalOverlay.style.zIndex = '9999';
    
    // Determine colors based on status
    const statusColors = {
        'pass': { bg: 'bg-green-50', border: 'border-green-200', text: 'text-green-800', icon: '✅' },
        'warning': { bg: 'bg-yellow-50', border: 'border-yellow-200', text: 'text-yellow-800', icon: '⚠️' },
        'fail': { bg: 'bg-red-50', border: 'border-red-200', text: 'text-red-800', icon: '❌' }
    };
    
    const colors = statusColors[status] || statusColors['fail'];
    
    // Create modal content
    modalOverlay.innerHTML = `
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4 p-6">
            <div class="flex items-start justify-between mb-4">
                <div class="flex items-center">
                    <div class="text-2xl mr-3">${colors.icon}</div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">${checkName}</h3>
                        <span class="inline-block px-2 py-1 text-xs font-medium rounded-full ${colors.bg} ${colors.text} ${colors.border} border">
                            ${status.toUpperCase()}
                        </span>
                    </div>
                </div>
                <button onclick="this.closest('.fixed').remove()" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <div class="mb-6">
                <h4 class="font-medium text-gray-900 mb-2">Details:</h4>
                <p class="text-sm text-gray-700 leading-relaxed">${message}</p>
            </div>
            
            ${status === 'fail' || status === 'warning' ? `
                <div class="mb-6 p-4 ${colors.bg} ${colors.border} border rounded-lg">
                    <h4 class="font-medium ${colors.text} mb-2">Recommended Actions:</h4>
                    <div class="text-sm ${colors.text}">
                        ${getRecommendedActions(checkName, status)}
                    </div>
                </div>
            ` : ''}
            
            <div class="flex justify-end space-x-3">
                <button onclick="this.closest('.fixed').remove()" 
                        class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                    Close
                </button>
            </div>
        </div>
    `;
    
    // Close modal when clicking overlay
    modalOverlay.addEventListener('click', function(e) {
        if (e.target === modalOverlay) {
            modalOverlay.remove();
        }
    });
    
    // Add to page
    document.body.appendChild(modalOverlay);
}


// Get recommended actions for specific checks
function getRecommendedActions(checkName, status) {
    const recommendations = {
        'API Key Configured': 'Go to the General tab and enter your CallTrackingMetrics API key and secret.',
        'API Connection': 'Check your internet connection and verify your API credentials are correct.',
        'Account Access': 'Ensure your API key has proper permissions in your CallTrackingMetrics account.',
        'Contact Form 7': 'Install and activate the Contact Form 7 plugin, or disable CF7 integration in settings.',
        'Gravity Forms': 'Install and activate Gravity Forms, or disable GF integration in settings.',
        'PHP Version (7.4+)': 'Contact your hosting provider to upgrade PHP to version 7.4 or higher.',
        'cURL Extension': 'Contact your hosting provider to enable the cURL PHP extension.',
        'SSL Support': 'Ensure your server has SSL/TLS support enabled for secure API communications.',
        'Memory Limit': 'Contact your hosting provider to increase PHP memory limit to 256MB or higher.',
        'Plugin Version': 'Update the CallTrackingMetrics plugin to the latest version.',
        'Database Tables': 'Deactivate and reactivate the plugin to recreate missing database tables.',
        'File Permissions': 'Check that the WordPress uploads directory has proper write permissions.',
        'Debug Mode': 'Enable debug mode in the Debug tab for better troubleshooting and logging.'
    };
    
    return recommendations[checkName] || 'Please contact support for assistance with this issue.';
}


document.addEventListener('DOMContentLoaded', function() {
    runHealthCheck();
});

</script>
 