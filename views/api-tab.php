<?php
// API Activity tab view
$options = get_option('call_tracking_metrics', []);
$api_connected = !empty($options['api_key']) && !empty($options['api_secret']);

// Get API service for account info
if ($api_connected) {
    $api_service = new CTM\Service\ApiService('https://api.calltrackingmetrics.com');
    $accountInfo = $api_service->getAccountInfo($options['api_key'], $options['api_secret']);
}
?>

<div class="space-y-6">
    
    <?php if ($api_connected && !empty($accountInfo)): ?>
        <!-- Account Information Section -->
        <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
            <div class="bg-gradient-to-r from-green-50 to-blue-50 px-6 py-4 border-b border-gray-200 rounded-t-lg">
                <div class="flex items-center">
                    <div class="w-3 h-3 bg-green-500 rounded-full mr-3 animate-pulse"></div>
                    <h2 class="text-lg font-semibold text-gray-800">API Connection Status</h2>
                    <span class="ml-2 px-3 py-1 bg-green-100 text-green-800 text-xs font-medium rounded-full">Connected</span>
                </div>
            </div>
            
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Account Details -->
                    <div class="space-y-4">
                        <h3 class="text-md font-medium text-gray-700 border-b pb-2">Account Details</h3>
                        <?php if (isset($accountInfo['account']) && is_array($accountInfo['account'])): ?>
                            <?php $account = $accountInfo['account']; ?>
                            <div class="space-y-3 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Account Name:</span>
                                    <span class="font-medium text-gray-800"><?= esc_html($account['name'] ?? 'N/A') ?></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Account ID:</span>
                                    <span class="font-mono text-gray-800"><?= esc_html($account['id'] ?? 'N/A') ?></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Email:</span>
                                    <span class="text-gray-800"><?= esc_html($account['email'] ?? 'N/A') ?></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Status:</span>
                                    <span class="px-2 py-1 bg-green-100 text-green-800 text-xs font-medium rounded">
                                        <?= esc_html(ucfirst($account['status'] ?? 'active')) ?>
                                    </span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Timezone:</span>
                                    <span class="text-gray-800"><?= esc_html($account['timezone'] ?? 'N/A') ?></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Created:</span>
                                    <span class="text-gray-800">
                                        <?= isset($account['created_at']) ? esc_html(date('M j, Y', strtotime($account['created_at']))) : 'N/A' ?>
                                    </span>
                                </div>
                            </div>
                        <?php else: ?>
                            <p class="text-gray-500 text-sm">Account information not available</p>
                        <?php endif; ?>
                    </div>
                    
                    <!-- API Information -->
                    <div class="space-y-4">
                        <h3 class="text-md font-medium text-gray-700 border-b pb-2">API Information</h3>
                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600">API Key:</span>
                                <span class="font-mono text-gray-800">
                                    <?= esc_html(substr($options['api_key'], 0, 8)) ?>••••••••
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Base URL:</span>
                                <span class="text-gray-800 break-all">https://api.calltrackingmetrics.com</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Last Connected:</span>
                                <span class="text-gray-800"><?= date('M j, Y g:i A') ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Response Format:</span>
                                <span class="text-gray-800">JSON</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Authentication:</span>
                                <span class="text-gray-800">HTTP Basic Auth</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Quick API Test Section -->
        <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200 rounded-t-lg">
                <h2 class="text-lg font-semibold text-gray-800">API Connection Test</h2>
                <p class="text-sm text-gray-600 mt-1">Test your API connection and view real-time logs</p>
            </div>
            
            <div class="p-6">
                <div class="flex items-center space-x-4 mb-4">
                    <button id="ctm-test-api-btn" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium transition-colors duration-200 flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                        Test API Connection
                    </button>
                    <button id="ctm-clear-logs-btn" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200">
                        Clear Logs
                    </button>
                </div>
                
                <!-- Progress Bar -->
                <div id="ctm-progress-container" class="hidden mb-4">
                    <div class="flex justify-between text-sm text-gray-600 mb-2">
                        <span>Connection Test Progress</span>
                        <span id="ctm-progress-percent">0%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                        <div id="ctm-progress-bar" class="bg-blue-600 h-2.5 rounded-full transition-all duration-300" style="width: 0%"></div>
                    </div>
                </div>
                
                <!-- Test Logs -->
                <div id="ctm-test-logs" class="bg-gray-50 border border-gray-200 rounded-lg p-4 h-64 overflow-y-auto font-mono text-sm">
                    <div class="text-gray-500 italic">Click "Test API Connection" to see real-time logs...</div>
                </div>
            </div>
        </div>
        
        <!-- API Activity History -->
        <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200 rounded-t-lg">
                <h2 class="text-lg font-semibold text-gray-800">Recent API Activity</h2>
                <p class="text-sm text-gray-600 mt-1">Recent API calls and responses</p>
            </div>
            
            <div class="p-6">
                <div class="space-y-3">
                    <!-- Sample API Activity -->
                    <div class="flex items-center justify-between p-3 bg-green-50 border border-green-200 rounded-lg">
                        <div class="flex items-center">
                            <div class="w-2 h-2 bg-green-500 rounded-full mr-3"></div>
                            <div>
                                <div class="font-medium text-sm">GET /api/v1/accounts/</div>
                                <div class="text-xs text-gray-600"><?= date('M j, Y g:i:s A') ?></div>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-sm font-medium text-green-600">200 OK</div>
                            <div class="text-xs text-gray-500">152ms</div>
                        </div>
                    </div>
                    
                    <div class="text-center py-8 text-gray-500">
                        <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        <p class="text-sm">More API activity history coming soon...</p>
                    </div>
                </div>
            </div>
        </div>
        
    <?php else: ?>
        <!-- Not Connected State -->
        <div class="bg-white border border-red-200 rounded-lg shadow-sm">
            <div class="bg-red-50 px-6 py-4 border-b border-red-200 rounded-t-lg">
                <div class="flex items-center">
                    <div class="w-3 h-3 bg-red-500 rounded-full mr-3"></div>
                    <h2 class="text-lg font-semibold text-gray-800">API Not Connected</h2>
                </div>
            </div>
            
            <div class="p-6 text-center">
                <svg class="w-16 h-16 mx-auto mb-4 text-red-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.08 15.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
                <h3 class="text-lg font-medium text-gray-800 mb-2">API Connection Required</h3>
                <p class="text-gray-600 mb-4">
                    You need to configure your API credentials to view account information and API activity.
                </p>
                <a href="?page=call-tracking-metrics&tab=general" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    Configure API Settings
                </a>
            </div>
        </div>
    <?php endif; ?>
    
</div>

<script>
jQuery(document).ready(function($) {
    // Test API Connection Button
    $('#ctm-test-api-btn').on('click', function() {
        const button = $(this);
        const logs = $('#ctm-test-logs');
        const progressContainer = $('#ctm-progress-container');
        const progressBar = $('#ctm-progress-bar');
        const progressPercent = $('#ctm-progress-percent');
        
        // Reset state
        button.prop('disabled', true).html('<svg class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Testing...');
        logs.html('<div class="text-blue-600">Starting API connection test...</div>');
        progressContainer.removeClass('hidden');
        
        // AJAX call to test API
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'ctm_test_api_connection',
                nonce: '<?= wp_create_nonce('ctm_test_api') ?>'
            },
            success: function(response) {
                if (response.success) {
                    // Update progress to 100%
                    progressBar.css('width', '100%');
                    progressPercent.text('100%');
                    
                    // Show success logs
                    let logHtml = '';
                    if (response.data.logs) {
                        response.data.logs.forEach(log => {
                            let bgClass = 'bg-gray-50';
                            let textClass = 'text-gray-700';
                            let icon = '•';
                            
                            if (log.includes('✓') || log.includes('Success')) {
                                bgClass = 'bg-green-50 border-l-4 border-green-500';
                                textClass = 'text-green-700';
                                icon = '✓';
                            } else if (log.includes('✗') || log.includes('Error')) {
                                bgClass = 'bg-red-50 border-l-4 border-red-500';
                                textClass = 'text-red-700';
                                icon = '✗';
                            } else if (log.includes('→') || log.includes('Sending')) {
                                bgClass = 'bg-blue-50 border-l-4 border-blue-500';
                                textClass = 'text-blue-700';
                                icon = '→';
                            }
                            
                            logHtml += `<div class="mb-2 p-2 rounded ${bgClass}"><span class="${textClass}">${icon} ${log}</span></div>`;
                        });
                    }
                    logs.html(logHtml);
                    
                    // Show duration if available
                    if (response.data.duration) {
                        logs.append(`<div class="mt-3 p-2 bg-gray-100 rounded text-center"><span class="text-gray-600">Total test duration: ${response.data.duration}ms</span></div>`);
                    }
                    
                    setTimeout(() => {
                        location.reload(); // Refresh to show updated account info
                    }, 2000);
                } else {
                    logs.html(`<div class="text-red-600">❌ ${response.data || 'Test failed'}</div>`);
                }
            },
            error: function() {
                logs.html('<div class="text-red-600">❌ AJAX request failed</div>');
            },
            complete: function() {
                button.prop('disabled', false).html('<svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>Test API Connection');
            }
        });
    });
    
    // Clear Logs Button
    $('#ctm-clear-logs-btn').on('click', function() {
        $('#ctm-test-logs').html('<div class="text-gray-500 italic">Click "Test API Connection" to see real-time logs...</div>');
        $('#ctm-progress-container').addClass('hidden');
        $('#ctm-progress-bar').css('width', '0%');
        $('#ctm-progress-percent').text('0%');
    });
});
</script> 