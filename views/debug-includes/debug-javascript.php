
<script>

<?php
// Generate system information for JavaScript
$system_info_report = "=== SYSTEM INFORMATION REPORT ===\n";
$system_info_report .= "Generated: " . current_time('Y-m-d H:i:s') . "\n";
$system_info_report .= "Site: " . get_bloginfo('name') . "\n\n";

$system_info_report .= "=== WORDPRESS ENVIRONMENT ===\n";
$system_info_report .= "WordPress Version: " . get_bloginfo('version') . "\n";
$system_info_report .= "Site URL: " . home_url() . "\n";
$system_info_report .= "Admin URL: " . admin_url() . "\n";
$system_info_report .= "WordPress Language: " . get_locale() . "\n";
$system_info_report .= "WordPress Debug: " . (WP_DEBUG ? 'Enabled' : 'Disabled') . "\n";
$system_info_report .= "WordPress Memory Limit: " . WP_MEMORY_LIMIT . "\n";
$system_info_report .= "Multisite: " . (is_multisite() ? 'Yes' : 'No') . "\n\n";

$system_info_report .= "=== SERVER ENVIRONMENT ===\n";
$system_info_report .= "PHP Version: " . PHP_VERSION . "\n";
$system_info_report .= "PHP SAPI: " . php_sapi_name() . "\n";
$system_info_report .= "Server Software: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "\n";
$system_info_report .= "Operating System: " . PHP_OS . "\n";
$system_info_report .= "Memory Limit: " . ini_get('memory_limit') . "\n";
$system_info_report .= "Max Execution Time: " . ini_get('max_execution_time') . "s\n";
$system_info_report .= "Max Input Vars: " . ini_get('max_input_vars') . "\n";
$system_info_report .= "Upload Max Size: " . ini_get('upload_max_filesize') . "\n";
$system_info_report .= "Post Max Size: " . ini_get('post_max_size') . "\n";
$system_info_report .= "Max File Uploads: " . ini_get('max_file_uploads') . "\n\n";

$system_info_report .= "=== DATABASE ===\n";
$system_info_report .= "Database Version: " . $GLOBALS['wpdb']->db_version() . "\n";
$system_info_report .= "Database Host: " . DB_HOST . "\n";
$system_info_report .= "Database Name: " . DB_NAME . "\n";
$system_info_report .= "Database Charset: " . DB_CHARSET . "\n";
$system_info_report .= "Table Prefix: " . $GLOBALS['wpdb']->prefix . "\n\n";

$system_info_report .= "=== PHP EXTENSIONS ===\n";
$system_info_report .= "cURL: " . (function_exists('curl_init') ? 'Available' : 'Missing') . "\n";
$system_info_report .= "OpenSSL: " . (extension_loaded('openssl') ? 'Available' : 'Missing') . "\n";
$system_info_report .= "mbstring: " . (extension_loaded('mbstring') ? 'Available' : 'Missing') . "\n";
$system_info_report .= "GD Library: " . (extension_loaded('gd') ? 'Available' : 'Missing') . "\n";
$system_info_report .= "XML: " . (extension_loaded('xml') ? 'Available' : 'Missing') . "\n";
$system_info_report .= "JSON: " . (extension_loaded('json') ? 'Available' : 'Missing') . "\n";
$system_info_report .= "ZIP: " . (extension_loaded('zip') ? 'Available' : 'Missing') . "\n\n";

$system_info_report .= "=== CALLTRACKINGMETRICS PLUGIN ===\n";
$system_info_report .= "Plugin Version: 2.0\n";
$system_info_report .= "Debug Mode: " . (get_option('ctm_debug_enabled') ? 'Enabled' : 'Disabled') . "\n";
$system_info_report .= "API Key Configured: " . (get_option('ctm_api_key') ? 'Yes' : 'No') . "\n";
$system_info_report .= "CF7 Integration: " . (get_option('ctm_api_cf7_enabled') ? 'Enabled' : 'Disabled') . "\n";
$system_info_report .= "GF Integration: " . (get_option('ctm_api_gf_enabled') ? 'Enabled' : 'Disabled') . "\n\n";

$system_info_report .= "=== THEME & PLUGINS ===\n";
$system_info_report .= "Active Theme: " . wp_get_theme()->get('Name') . "\n";
$system_info_report .= "Theme Version: " . wp_get_theme()->get('Version') . "\n";
$system_info_report .= "Active Plugins: " . count(get_option('active_plugins', [])) . "\n";
$system_info_report .= "Contact Form 7: " . (class_exists('WPCF7_ContactForm') ? 'Installed' : 'Not Installed') . "\n";
$system_info_report .= "Gravity Forms: " . (class_exists('GFAPI') ? 'Installed' : 'Not Installed') . "\n\n";

$system_info_report .= "=== CURRENT PERFORMANCE ===\n";
$system_info_report .= "Memory Usage: " . size_format(memory_get_usage(true)) . "\n";
$system_info_report .= "Peak Memory: " . size_format(memory_get_peak_usage(true)) . "\n";
$system_info_report .= "Database Queries: " . get_num_queries() . "\n";
$system_info_report .= "Admin Email: " . get_option('admin_email') . "\n";
$system_info_report .= "Timezone: " . (get_option('timezone_string') ?: 'UTC') . "\n\n";

$system_info_report .= "=== END REPORT ===";
?>

function copySystemInfo() {
    const button = document.getElementById('copy-system-btn');
    const originalText = button.innerHTML;
    
    console.log('Copy button clicked'); // Debug log
    
    // Show loading state
    button.innerHTML = '<svg class="w-4 h-4 mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>Copying...';
    button.disabled = true;
    
    const systemInfo = <?= json_encode($system_info_report) ?>;
    
    console.log('System info length:', systemInfo ? systemInfo.length : 'undefined'); // Debug log
    
    // Check if clipboard API is available
    if (navigator.clipboard && window.isSecureContext) {
        console.log('Using modern clipboard API'); // Debug log
        navigator.clipboard.writeText(systemInfo).then(() => {
            console.log('Clipboard write successful'); // Debug log
            // Success state
            button.innerHTML = '<svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>Copied!';
            button.classList.remove('bg-blue-600', 'hover:bg-blue-700');
            button.classList.add('bg-green-600', 'hover:bg-green-700');
            
            showDebugMessage('System information copied to clipboard!', 'success');
            
            // Reset button after 3 seconds
            setTimeout(() => {
                button.innerHTML = originalText;
                button.classList.remove('bg-green-600', 'hover:bg-green-700');
                button.classList.add('bg-blue-600', 'hover:bg-blue-700');
                button.disabled = false;
            }, 3000);
        }).catch((error) => {
            console.error('Clipboard API failed:', error); // Debug log
            fallbackCopyToClipboard();
        });
    } else {
        console.log('Using fallback clipboard method'); // Debug log
        fallbackCopyToClipboard();
    }
    
    function fallbackCopyToClipboard() {
        try {
            // Fallback for older browsers or non-secure contexts
            const textArea = document.createElement('textarea');
            textArea.value = systemInfo;
            textArea.style.position = 'fixed';
            textArea.style.left = '-999999px';
            textArea.style.top = '-999999px';
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            
            const successful = document.execCommand('copy');
            document.body.removeChild(textArea);
            
            if (successful) {
                console.log('Fallback copy successful'); // Debug log
                // Success state
                button.innerHTML = '<svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>Copied!';
                button.classList.remove('bg-blue-600', 'hover:bg-blue-700');
                button.classList.add('bg-green-600', 'hover:bg-green-700');
                
                showDebugMessage('System information copied to clipboard!', 'success');
                
                // Reset button after 3 seconds
                setTimeout(() => {
                    button.innerHTML = originalText;
                    button.classList.remove('bg-green-600', 'hover:bg-green-700');
                    button.classList.add('bg-blue-600', 'hover:bg-blue-700');
                    button.disabled = false;
                }, 3000);
            } else {
                throw new Error('execCommand copy failed');
            }
        } catch (error) {
            console.error('Fallback copy failed:', error); // Debug log
            // Show error state
            button.innerHTML = '<svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>Failed';
            button.classList.remove('bg-blue-600', 'hover:bg-blue-700');
            button.classList.add('bg-red-600', 'hover:bg-red-700');
            
            showDebugMessage('Failed to copy to clipboard. Please copy the information manually from the display above.', 'error');
            
            // Reset button after 3 seconds
            setTimeout(() => {
                button.innerHTML = originalText;
                button.classList.remove('bg-red-600', 'hover:bg-red-700');
                button.classList.add('bg-blue-600', 'hover:bg-blue-700');
                button.disabled = false;
            }, 3000);
        }
    }
}

// Email System Information
function emailSystemInfo() {
    document.getElementById('email-system-modal')?.classList.remove('hidden');
}

function hideEmailSystemForm() {
    document.getElementById('email-system-modal')?.classList.add('hidden');
}

// Handle email system info form submission
document.getElementById('email-system-form')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const button = document.getElementById('send-system-email-btn');
    const originalText = button.textContent;
    
    button.disabled = true;
    button.textContent = 'Sending...';
    
    const formData = new FormData();
    formData.append('action', 'ctm_email_system_info');
    formData.append('email_to', document.getElementById('system_email_to').value);
    formData.append('subject', document.getElementById('system_email_subject').value);
    formData.append('message', document.getElementById('system_email_message').value);
    formData.append('nonce', '<?= wp_create_nonce('ctm_email_system_info') ?>');
    
    fetch('<?= admin_url('admin-ajax.php') ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showDebugMessage('System information email sent successfully!', 'success');
            hideEmailSystemForm();
        } else {
            showDebugMessage('Failed to send email: ' + (data.data?.message || 'Unknown error'), 'error');
        }
    })
    .catch(error => {
        showDebugMessage('Network error while sending email', 'error');
    })
    .finally(() => {
        button.disabled = false;
        button.textContent = originalText;
    });
});

// Close system email modal when clicking outside
document.getElementById('email-system-modal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        hideEmailSystemForm();
    }
});

// API Request Simulator
function simulateApiRequest() {
    const endpoint = document.getElementById('api-endpoint').value;
    const method = document.getElementById('api-method').value;
    const button = document.getElementById('simulate-btn');
    const responseDiv = document.getElementById('api-response');
    const responseContent = document.getElementById('api-response-content');
    
    button.disabled = true;
    button.textContent = 'Sending...';
    responseDiv.classList.add('hidden');
    
    const formData = new FormData();
    formData.append('action', 'ctm_simulate_api_request');
    formData.append('endpoint', endpoint);
    formData.append('method', method);
    formData.append('nonce', '<?= wp_create_nonce('ctm_simulate_api_request') ?>');
    
    fetch('<?= admin_url('admin-ajax.php') ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        responseDiv.classList.remove('hidden');
        responseContent.textContent = JSON.stringify(data, null, 2);
        
        if (data.success) {
            showDebugMessage('API request completed successfully', 'success');
        } else {
            showDebugMessage('API request failed: ' + (data.data?.message || 'Unknown error'), 'error');
        }
    })
    .catch(error => {
        responseDiv.classList.remove('hidden');
        responseContent.textContent = 'Error: ' + error.message;
        showDebugMessage('Network error during API simulation', 'error');
    })
    .finally(() => {
        button.disabled = false;
        button.textContent = 'Send Test Request';
    });
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
                'Field Mappings': 'check-field-mappings',
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
            
            // Enable/disable auto-fix button based on issues found
            const autoFixButton = document.getElementById('fix-issues-btn');
            if (autoFixButton) {
                if (failedChecks > 0 || warningChecks > 0) {
                    autoFixButton.disabled = false;
                    autoFixButton.classList.remove('opacity-50', 'cursor-not-allowed');
                    autoFixButton.classList.add('hover:bg-blue-700');
                } else {
                    autoFixButton.disabled = true;
                    autoFixButton.classList.add('opacity-50', 'cursor-not-allowed');
                    autoFixButton.classList.remove('hover:bg-blue-700');
                }
            }
            
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

// Auto-Fix Common Issues
function fixCommonIssues() {
    const button = document.getElementById('fix-issues-btn');
    
    if (!button) {
        showDebugMessage('Fix issues button not found', 'error');
        return;
    }
    
    // Check if there are any failed health checks to fix
    const failedIndicators = document.querySelectorAll('.health-indicator.text-red-600');
    const warningIndicators = document.querySelectorAll('.health-indicator.text-yellow-600');
    
    if (failedIndicators.length === 0 && warningIndicators.length === 0) {
        showDebugMessage('No issues detected to fix. Run health check first.', 'info');
        return;
    }
    
    button.disabled = true;
    button.innerHTML = `
        <svg class="w-4 h-4 mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
        </svg>
        Fixing Issues...
    `;
    
    showDebugMessage('Starting automatic issue resolution...', 'info');
    
    const formData = new FormData();
    formData.append('action', 'ctm_auto_fix_issues');
    formData.append('nonce', '<?= wp_create_nonce('ctm_auto_fix_issues') ?>');
    
    fetch('<?= admin_url('admin-ajax.php') ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const fixes = data.data.fixes;
            const fixedCount = fixes.filter(f => f.status === 'fixed').length;
            const skippedCount = fixes.filter(f => f.status === 'skipped').length;
            const failedCount = fixes.filter(f => f.status === 'failed').length;
            
            // Show detailed results
            let resultMessage = `Auto-fix completed: ${fixedCount} fixed`;
            if (skippedCount > 0) resultMessage += `, ${skippedCount} skipped`;
            if (failedCount > 0) resultMessage += `, ${failedCount} failed`;
            
            const messageType = failedCount > 0 ? 'warning' : fixedCount > 0 ? 'success' : 'info';
            showDebugMessage(resultMessage, messageType);
            
            // Show detailed fix results
            if (fixes.length > 0) {
                displayFixResults(fixes);
            }
            
            // Re-run health check to update status
            if (fixedCount > 0) {
                setTimeout(() => {
                    showDebugMessage('Re-running health check to verify fixes...', 'info');
                    runHealthCheck();
                }, 2000);
            }
        } else {
            const errorMessage = data.data?.message || 'Auto-fix failed to run';
            showDebugMessage(errorMessage, 'error');
        }
    })
    .catch(error => {
        console.error('Auto-fix error:', error);
        showDebugMessage('Network error during auto-fix', 'error');
    })
    .finally(() => {
        button.disabled = false;
        button.innerHTML = 'Auto-Fix Issues';
    });
}

function displayFixResults(fixes) {
    // Create a results panel
    const resultsDiv = document.createElement('div');
    resultsDiv.className = 'mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg';
    resultsDiv.innerHTML = `
        <h5 class="font-semibold text-blue-800 mb-3">Auto-Fix Results</h5>
        <div class="space-y-2">
            ${fixes.map(fix => `
                <div class="flex items-center justify-between text-sm">
                    <span class="text-blue-700">${fix.issue}</span>
                    <span class="px-2 py-1 rounded text-xs font-medium ${
                        fix.status === 'fixed' ? 'bg-green-100 text-green-800' :
                        fix.status === 'skipped' ? 'bg-yellow-100 text-yellow-800' :
                        'bg-red-100 text-red-800'
                    }">
                        ${fix.status.toUpperCase()}
                    </span>
                </div>
                ${fix.message ? `<div class="text-xs text-blue-600 ml-4">${fix.message}</div>` : ''}
            `).join('')}
        </div>
    `;
    
    // Insert after health check section
    const healthCheckDiv = document.querySelector('.bg-white.rounded-xl.shadow-lg.border.border-gray-200.p-6');
    if (healthCheckDiv && healthCheckDiv.parentNode) {
        // Remove any existing results
        const existingResults = healthCheckDiv.parentNode.querySelector('.mt-4.p-4.bg-blue-50');
        if (existingResults) {
            existingResults.remove();
        }
        
        healthCheckDiv.parentNode.insertBefore(resultsDiv, healthCheckDiv.nextSibling);
        
        // Auto-remove after 10 seconds
        setTimeout(() => {
            if (resultsDiv.parentNode) {
                resultsDiv.style.transition = 'opacity 0.5s ease';
                resultsDiv.style.opacity = '0';
                setTimeout(() => resultsDiv.remove(), 500);
            }
        }, 10000);
    }
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
                ${status === 'fail' || status === 'warning' ? `
                    <button onclick="fixSpecificIssue('${checkName}'); this.closest('.fixed').remove();" 
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                        Try Auto-Fix
                    </button>
                ` : ''}
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
        'Field Mappings': 'Configure field mappings in the Field Mapping tab to connect form fields to CTM.',
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

// Fix specific issue
function fixSpecificIssue(checkName) {
    showDebugMessage(`Attempting to fix: ${checkName}...`, 'info');
    
    // For now, trigger the general auto-fix
    // In the future, this could be enhanced to fix specific issues
    fixCommonIssues();
}

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

// Error Analyzer JavaScript Functions

function toggleIssueCategory(categoryId) {
    const category = document.getElementById(categoryId);
    const chevron = document.getElementById(categoryId + '-chevron');
    
    if (category.classList.contains('hidden')) {
        category.classList.remove('hidden');
        chevron.style.transform = 'rotate(180deg)';
    } else {
        category.classList.add('hidden');
        chevron.style.transform = 'rotate(0deg)';
    }
}


function autoFixIssue(fixId) {
    if (!confirm('Are you sure you want to apply this automated fix? This action cannot be undone without a manual rollback.')) {
        return;
    }
    
    showDebugMessage('Applying automated fix...', 'info');
    
    const formData = new FormData();
    formData.append('action', 'ctm_auto_fix_issue');
    formData.append('fix_id', fixId);
    formData.append('nonce', '<?= wp_create_nonce('ctm_auto_fix_issue') ?>');
    
    fetch('<?= admin_url('admin-ajax.php') ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showDebugMessage(`Auto-fix applied successfully: ${data.data.description}`, 'success');
            
            // Show rollback option if available
            if (data.data.rollback_available) {
                setTimeout(() => {
                    if (confirm('Auto-fix applied successfully. Would you like to create a rollback point for this change?')) {
                        createRollbackPoint(fixId, data.data.rollback_data);
                    }
                }, 2000);
            }
            
            // Refresh the analysis to show updated status
            setTimeout(() => {
                if (typeof runFullDiagnostic === 'function') {
                    runFullDiagnostic();
                }
            }, 3000);
        } else {
            showDebugMessage(`Auto-fix failed: ${data.data.error}`, 'error');
        }
    })
    .catch(error => {
        showDebugMessage('Network error during auto-fix', 'error');
    });
}

function createRollbackPoint(fixId, rollbackData) {
    const formData = new FormData();
    formData.append('action', 'ctm_create_rollback_point');
    formData.append('fix_id', fixId);
    formData.append('rollback_data', JSON.stringify(rollbackData));
    formData.append('nonce', '<?= wp_create_nonce('ctm_create_rollback_point') ?>');
    
    fetch('<?= admin_url('admin-ajax.php') ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showDebugMessage('Rollback point created successfully', 'success');
            updateRollbackHistory(fixId, rollbackData);
        } else {
            showDebugMessage('Failed to create rollback point', 'error');
        }
    })
    .catch(error => {
        showDebugMessage('Network error creating rollback point', 'error');
    });
}

function updateRollbackHistory(fixId, rollbackData) {
    // Store rollback information in localStorage for quick access
    const rollbacks = JSON.parse(localStorage.getItem('ctm_rollback_history') || '[]');
    rollbacks.unshift({
        id: fixId,
        timestamp: new Date().toISOString(),
        description: rollbackData.description || 'Auto-fix rollback point',
        data: rollbackData
    });
    
    // Keep only last 10 rollback points
    if (rollbacks.length > 10) {
        rollbacks.splice(10);
    }
    
    localStorage.setItem('ctm_rollback_history', JSON.stringify(rollbacks));
}


function refreshSystemInfo() {
    const button = document.getElementById('refresh-system-btn');
    const originalText = button.textContent;
    
    // Show loading state
    button.disabled = true;
    button.innerHTML = `
        <svg class="w-4 h-4 mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
        </svg>
        Refreshing...
    `;
    
    showDebugMessage('Refreshing system information...', 'info');
    
    const formData = new FormData();
    formData.append('action', 'ctm_refresh_system_info');
    formData.append('nonce', '<?= wp_create_nonce('ctm_refresh_system_info') ?>');
    
    fetch('<?= admin_url('admin-ajax.php') ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update system info panels with fresh data
            if (data.data && data.data.system_info) {
                updateSystemInfoDisplay(data.data.system_info);
            }
            showDebugMessage('System information refreshed successfully', 'success');
        } else {
            const errorMessage = (data && data.data && data.data.message) || 
                                (data && data.message) || 
                                'Unknown error occurred';
            showDebugMessage('Failed to refresh system info: ' + errorMessage, 'error');
        }
    })
    .catch(error => {
        console.error('System info refresh error:', error);
        showDebugMessage('Error refreshing system information: ' + error.message, 'error');
    })
    .finally(() => {
        // Restore button state
        button.disabled = false;
        button.innerHTML = `
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
            </svg>
            Refresh System Info
        `;
    });
}

function updateSystemInfoDisplay(systemInfo) {
    // Update the metric cards
    if (systemInfo.php_version) {
        const phpCard = document.querySelector('[data-metric="php_version"]');
        if (phpCard) {
            phpCard.querySelector('.text-2xl').textContent = systemInfo.php_version;
        }
    }
    
    if (systemInfo.wp_version) {
        const wpCard = document.querySelector('[data-metric="wp_version"]');
        if (wpCard) {
            wpCard.querySelector('.text-2xl').textContent = systemInfo.wp_version;
        }
    }
    
    if (systemInfo.memory_usage) {
        const memCard = document.querySelector('[data-metric="memory_usage"]');
        if (memCard) {
            memCard.querySelector('.text-2xl').textContent = systemInfo.memory_usage;
        }
    }
    
    if (systemInfo.db_queries) {
        const dbCard = document.querySelector('[data-metric="db_queries"]');
        if (dbCard) {
            dbCard.querySelector('.text-2xl').textContent = systemInfo.db_queries;
        }
    }
    
    // Update detailed information sections
    if (systemInfo.wordpress_env) {
        updateWordPressEnvironment(systemInfo.wordpress_env);
    }
    
    if (systemInfo.server_env) {
        updateServerEnvironment(systemInfo.server_env);
    }
    
    if (systemInfo.database_info) {
        updateDatabaseInfo(systemInfo.database_info);
    }
}

function updateWordPressEnvironment(wpEnv) {
    // Update WordPress environment details
    const wpSection = document.querySelector('[data-section="wordpress-env"]');
    if (wpSection && wpEnv) {
        Object.keys(wpEnv).forEach(key => {
            const element = wpSection.querySelector(`[data-field="${key}"]`);
            if (element) {
                element.textContent = wpEnv[key];
            }
        });
    }
}

function updateServerEnvironment(serverEnv) {
    // Update server environment details
    const serverSection = document.querySelector('[data-section="server-env"]');
    if (serverSection && serverEnv) {
        Object.keys(serverEnv).forEach(key => {
            const element = serverSection.querySelector(`[data-field="${key}"]`);
            if (element) {
                element.textContent = serverEnv[key];
            }
        });
    }
}

function updateDatabaseInfo(dbInfo) {
    // Update database information
    const dbSection = document.querySelector('[data-section="database-info"]');
    if (dbSection && dbInfo) {
        Object.keys(dbInfo).forEach(key => {
            const element = dbSection.querySelector(`[data-field="${key}"]`);
            if (element) {
                element.textContent = dbInfo[key];
            }
        });
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

// Helper function to update element content
function updateElement(elementId, value) {
    const element = document.getElementById(elementId);
    if (element && value !== undefined) {
        element.textContent = value;
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

function showDebugMessage(message, type = 'info') {
    // Create message element
    const messageDiv = document.createElement('div');
    messageDiv.className = `p-4 mb-4 rounded-lg border-l-4 ${
        type === 'success' ? 'bg-green-50 border-green-400 text-green-700' :
        type === 'error' ? 'bg-red-50 border-red-400 text-red-700' :
        'bg-blue-50 border-blue-400 text-blue-700'
    }`;
    
    messageDiv.innerHTML = `
        <div class="flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                ${type === 'success' ? 
                    '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>' :
                    type === 'error' ?
                    '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>' :
                    '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>'
                }
            </svg>
            <span class="font-medium">${message}</span>
        </div>
    `;
    
    // Insert at top of debug container
    const container = document.querySelector('.mb-12');
    if (container) {
        container.insertBefore(messageDiv, container.firstChild);
    }
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        messageDiv.style.transition = 'opacity 0.5s ease';
        messageDiv.style.opacity = '0';
        setTimeout(() => {
            if (messageDiv.parentNode) {
                messageDiv.parentNode.removeChild(messageDiv);
            }
        }, 500);
    }, 5000);
}
</script> 