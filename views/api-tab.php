<?php
// API Activity tab view
// Data is passed from Options.php: $apiKey, $apiSecret, $apiStatus, $accountInfo
$api_connected = ($apiStatus === 'connected');
?>
<?php // Add nonces for AJAX actions
$ctm_change_api_nonce = wp_create_nonce('ctm_change_api_keys');
$ctm_disable_api_nonce = wp_create_nonce('ctm_disable_api');
?>

<div class="space-y-6">
    
    <?php if ($api_connected && !empty($accountInfo)): ?>
        
        <!-- Account Information Section -->
        <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
            <div class="bg-gradient-to-r from-blue-50 to-green-50 px-6 py-4 border-b border-gray-200 rounded-t-lg flex items-center justify-between">
                <div class="flex items-center">
                    <div class="w-3 h-3 bg-blue-500 rounded-full mr-3"></div>
                    <h2 class="text-lg font-semibold text-gray-800"><?php _e('Account Information', 'call-tracking-metrics'); ?></h2>
                    <span class="ml-2 px-3 py-1 bg-blue-100 text-blue-800 text-xs font-medium rounded-full"><?php _e('Active', 'call-tracking-metrics'); ?></span>
                </div>
                <!-- Move buttons here -->
                <div class="flex gap-2">
                    <button id="ctm-change-api-btn" type="button" class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded font-medium transition text-sm"><?php _e('Change API Keys', 'call-tracking-metrics'); ?></button>
                    <button id="ctm-disable-api-btn" type="button" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded font-medium transition text-sm"><?php _e('Disable API / Start Over', 'call-tracking-metrics'); ?></button>
                </div>
            </div>
            
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Account Details -->
                    <div class="space-y-4">
                        <h3 class="text-md font-medium text-gray-700 border-b pb-2"><?php _e('Account Details', 'call-tracking-metrics'); ?></h3>
                        <?php if (isset($accountInfo['account']) && is_array($accountInfo['account'])): ?>
                            <?php $account = $accountInfo['account']; ?>
                            <div class="space-y-3 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-600"><?php _e('Account Name:', 'call-tracking-metrics'); ?></span>
                                    <span class="font-medium text-gray-800"><?= esc_html($account['name'] ?? 'N/A') ?></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600"><?php _e('Account ID:', 'call-tracking-metrics'); ?></span>
                                    <span class="font-mono text-gray-800"><?= esc_html($account['id'] ?? 'N/A') ?></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600"><?php _e('Status:', 'call-tracking-metrics'); ?></span>
                                    <span class="px-2 py-1 bg-green-100 text-green-800 text-xs font-medium rounded">
                                        <?= esc_html(ucfirst($account['status'] ?? 'active')) ?>
                                    </span>
                                </div>
                                <?php if (!empty($account['phone'])): ?>
                                <div class="flex justify-between">
                                    <span class="text-gray-600"><?php _e('Phone:', 'call-tracking-metrics'); ?></span>
                                    <span class="text-gray-800"><?= esc_html($account['phone']) ?></span>
                                </div>
                                <?php endif; ?>
                                <?php if (!empty($account['address'])): ?>
                                <div class="flex justify-between">
                                    <span class="text-gray-600"><?php _e('Address:', 'call-tracking-metrics'); ?></span>
                                    <span class="text-gray-800"><?= esc_html($account['address']) ?></span>
                                </div>
                                <?php endif; ?>
                                <?php if (!empty($account['plan'])): ?>
                                <div class="flex justify-between">
                                    <span class="text-gray-600"><?php _e('Plan:', 'call-tracking-metrics'); ?></span>
                                    <span class="text-gray-800"><?= esc_html($account['plan']) ?></span>
                                </div>
                                <?php endif; ?>
                                <?php if (!empty($account['usage'])): ?>
                                <div class="flex justify-between">
                                    <span class="text-gray-600"><?php _e('Usage:', 'call-tracking-metrics'); ?></span>
                                    <span class="text-gray-800"><?= esc_html($account['usage']) ?></span>
                                </div>
                                <?php endif; ?>
                                <?php if (!empty($account['website'])): ?>
                                <div class="flex justify-between">
                                    <span class="text-gray-600"><?php _e('Website:', 'call-tracking-metrics'); ?></span>
                                    <span class="text-blue-700 underline"><a href="<?= esc_url($account['website']) ?>" target="_blank" rel="noopener noreferrer"><?= esc_html($account['website']) ?></a></span>
                                </div>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-gray-500 text-sm"><?php _e('Account information not available', 'call-tracking-metrics'); ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <!-- API Information -->
                    <div class="space-y-4">
                        <h3 class="text-md font-medium text-gray-700 border-b pb-2"><?php _e('API Information', 'call-tracking-metrics'); ?></h3>
                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600"><?php _e('API Key:', 'call-tracking-metrics'); ?></span>
                                <span class="font-mono text-gray-800">
                                    <?= esc_html(substr($apiKey ?? '', 0, 8)) ?>••••••••
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600"><?php _e('Base URL:', 'call-tracking-metrics'); ?></span>
                                <span class="text-gray-800 break-all">https://api.calltrackingmetrics.com</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600"><?php _e('Last Connected:', 'call-tracking-metrics'); ?></span>
                                <span class="text-gray-800"><?= date('M j, Y g:i A') ?></span>
                            </div>
                        </div>
                        <!-- Add Change API Keys and Disable API buttons -->
                        <!-- Change API Keys Modal -->
                        <div id="ctm-change-api-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 hidden">
                            <div class="bg-white rounded-lg shadow-lg p-8 max-w-md w-full relative">
                                <h3 class="text-lg font-semibold mb-4"><?php _e('Change API Keys', 'call-tracking-metrics'); ?></h3>
                                <form id="ctm-change-api-form">
                                    <div class="mb-4">
                                        <label for="ctm_new_api_key" class="block text-gray-700 font-medium mb-1"><?php _e('API Key', 'call-tracking-metrics'); ?></label>
                                        <input type="text" id="ctm_new_api_key" name="ctm_new_api_key" class="w-full border border-gray-300 rounded px-3 py-2" required autocomplete="off">
                                    </div>
                                    <div class="mb-4">
                                        <label for="ctm_new_api_secret" class="block text-gray-700 font-medium mb-1"><?php _e('API Secret', 'call-tracking-metrics'); ?></label>
                                        <input type="text" id="ctm_new_api_secret" name="ctm_new_api_secret" class="w-full border border-gray-300 rounded px-3 py-2" required autocomplete="off">
                                    </div>
                                    <div class="flex justify-end gap-2 mt-6">
                                        <button type="button" id="ctm-cancel-change-api" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded"><?php _e('Cancel', 'call-tracking-metrics'); ?></button>
                                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded"><?php _e('Save', 'call-tracking-metrics'); ?></button>
                                    </div>
                                </form>
                                <button id="ctm-close-change-api" class="absolute top-2 right-2 text-gray-400 hover:text-gray-700 text-2xl leading-none">&times;</button>
                            </div>
                        </div>
                        <!-- Disable API Modal -->
                        <div id="ctm-disable-api-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 hidden">
                            <div class="bg-white rounded-lg shadow-lg p-8 max-w-md w-full relative">
                                <h3 class="text-lg font-semibold mb-4 text-red-700"><?php _e('Disable API / Start Over', 'call-tracking-metrics'); ?></h3>
                                <p class="mb-6 text-gray-700"><?php _e('Are you sure you want to disable the API and clear all credentials? This cannot be undone.', 'call-tracking-metrics'); ?></p>
                                <div class="flex justify-end gap-2">
                                    <button type="button" id="ctm-cancel-disable-api" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded"><?php _e('Cancel', 'call-tracking-metrics'); ?></button>
                                    <button type="button" id="ctm-confirm-disable-api" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded"><?php _e('Disable API', 'call-tracking-metrics'); ?></button>
                                </div>
                                <button id="ctm-close-disable-api" class="absolute top-2 right-2 text-gray-400 hover:text-gray-700 text-2xl leading-none">&times;</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Real-time API Connection Monitor -->
        <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
            <div class="bg-gradient-to-r from-green-50 to-blue-50 px-6 py-4 border-b border-gray-200 rounded-t-lg">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div id="ctm-status-indicator" class="w-3 h-3 bg-green-500 rounded-full mr-3 animate-pulse"></div>
                        <h2 class="text-lg font-semibold text-gray-800"><?php _e('Live API Connection Monitor', 'call-tracking-metrics'); ?></h2>
                        <span id="ctm-status-badge" class="ml-2 px-3 py-1 bg-green-100 text-green-800 text-xs font-medium rounded-full"><?php _e('Connected', 'call-tracking-metrics'); ?></span>
                    </div>
                    <div class="text-right">
                        <div id="ctm-countdown" class="text-sm text-gray-600"><?php _e('Next test in 10m', 'call-tracking-metrics'); ?></div>
                        <div id="ctm-last-test" class="text-xs text-gray-500"><?= date('g:i:s A') ?></div>
                    </div>
                </div>
            </div>
            
            <div class="p-6">
                <div class="flex items-center space-x-4 mb-4">
                    <button id="ctm-test-api-btn" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium transition-colors duration-200 flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                        Test Now
                    </button>
                    <button id="ctm-toggle-auto-test" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200 flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Auto-Test: ON
                    </button>
                    <button id="ctm-clear-logs-btn" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200">
                        Clear Logs
                    </button>
                </div>
                
                <!-- Progress Bar -->
                <div id="ctm-progress-container" class="hidden mb-4">
                    <div class="flex justify-between text-sm text-gray-600 mb-2">
                        <span><?php _e('Connection Test Progress', 'call-tracking-metrics'); ?></span>
                        <span id="ctm-progress-percent">0%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                        <div id="ctm-progress-bar" class="bg-blue-600 h-2.5 rounded-full transition-all duration-300" style="width: 0%"></div>
                    </div>
                </div>
                
                <!-- Test Logs -->
                <div id="ctm-test-logs" class="bg-gray-50 border border-gray-200 rounded-lg p-4 h-64 overflow-y-auto font-mono text-sm">
                    <div class="text-gray-500 italic"><?php _e('Click "Test Now" to see real-time logs...', 'call-tracking-metrics'); ?></div>
                </div>
                <!-- Account Summary (shown after successful connection) -->
                <div id="ctm-account-summary" class="hidden mt-4 p-3 bg-green-50 border border-green-200 rounded">
                    <h5 class="font-semibold text-green-800 mb-2 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 15c2.5 0 4.847.655 6.879 1.804M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        <?php _e('Account Summary', 'call-tracking-metrics'); ?>
                    </h5>
                    <div id="ctm-account-details" class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs"></div>
                </div>
                <!-- Technical Details (collapsible) -->
                <div id="ctm-technical-details" class="hidden mt-4">
                    <button type="button" onclick="jQuery('#ctm-tech-details-content').toggleClass('hidden'); jQuery('#ctm-tech-details-icon').toggleClass('rotate-90');" class="flex items-center gap-2 text-xs text-gray-600 hover:text-gray-800 transition">
                        <svg id="ctm-tech-details-icon" class="w-3 h-3 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                        <?php _e('View Technical Details', 'call-tracking-metrics'); ?>
                    </button>
                    <div id="ctm-tech-details-content" class="hidden mt-2 p-3 bg-gray-100 rounded text-xs font-mono overflow-x-auto"></div>
                </div>
            </div>
        </div>
        
        <!-- Connection Statistics -->
        <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200 rounded-t-lg">
                <h2 class="text-lg font-semibold text-gray-800"><?php _e('Connection Statistics', 'call-tracking-metrics'); ?></h2>
                <p class="text-sm text-gray-600 mt-1"><?php _e('Real-time monitoring statistics and performance metrics', 'call-tracking-metrics'); ?></p>
            </div>
            
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div class="text-center p-4 bg-green-50 border border-green-200 rounded-lg">
                        <div class="text-2xl font-bold text-green-600">98.5%</div>
                        <div class="text-sm text-gray-600"><?php _e('Uptime (24h)', 'call-tracking-metrics'); ?></div>
                    </div>
                    <div class="text-center p-4 bg-blue-50 border border-blue-200 rounded-lg">
                        <div class="text-2xl font-bold text-blue-600">156ms</div>
                        <div class="text-sm text-gray-600"><?php _e('Avg Response', 'call-tracking-metrics'); ?></div>
                    </div>
                    <div class="text-center p-4 bg-purple-50 border border-purple-200 rounded-lg">
                        <div class="text-2xl font-bold text-purple-600">247</div>
                        <div class="text-sm text-gray-600"><?php _e('Tests Today', 'call-tracking-metrics'); ?></div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Documentation & Support Links -->
        <div class="bg-white border border-blue-200 rounded-lg shadow-sm mt-8">
            <div class="bg-blue-50 px-6 py-4 border-b border-blue-200 rounded-t-lg flex items-center">
                <svg class="w-5 h-5 text-blue-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 14h.01M16 10h.01M21 12c0 4.418-4.03 8-9 8s-9-3.582-9-8 4.03-8 9-8 9 3.582 9 8z"/></svg>
                <h2 class="text-lg font-semibold text-blue-800"><?php _e('CallTrackingMetrics Documentation & Support', 'call-tracking-metrics'); ?></h2>
            </div>
            <div class="p-6 space-y-3 text-sm">
                <ul class="list-disc list-inside space-y-2">
                    <li><a href="https://calltrackingmetrics.zendesk.com/hc/en-us" target="_blank" rel="noopener noreferrer" class="text-blue-700 underline hover:text-blue-900"><?php _e('CTM Help Center', 'call-tracking-metrics'); ?></a></li>
                    <li><a href="https://calltrackingmetrics.zendesk.com/hc/en-us/categories/200216006-API-Developers" target="_blank" rel="noopener noreferrer" class="text-blue-700 underline hover:text-blue-900"><?php _e('API Documentation', 'call-tracking-metrics'); ?></a></li>
                    <li><a href="https://calltrackingmetrics.zendesk.com/hc/en-us/articles/360050247232-Getting-Started-with-CallTrackingMetrics" target="_blank" rel="noopener noreferrer" class="text-blue-700 underline hover:text-blue-900"><?php _e('Getting Started Guide', 'call-tracking-metrics'); ?></a></li>
                    <li><a href="https://calltrackingmetrics.zendesk.com/hc/en-us/sections/200216016-Troubleshooting" target="_blank" rel="noopener noreferrer" class="text-blue-700 underline hover:text-blue-900"><?php _e('Troubleshooting', 'call-tracking-metrics'); ?></a></li>
                    <li><a href="https://calltrackingmetrics.zendesk.com/hc/en-us/requests/new" target="_blank" rel="noopener noreferrer" class="text-blue-700 underline hover:text-blue-900"><?php _e('Contact Support', 'call-tracking-metrics'); ?></a></li>
                </ul>
                <p class="text-xs text-gray-500 mt-2"><?php _e('For more guides, best practices, and API reference, visit the', 'call-tracking-metrics'); ?> <a href="https://calltrackingmetrics.zendesk.com/hc/en-us" target="_blank" rel="noopener noreferrer" class="text-blue-700 underline"><?php _e('CallTrackingMetrics Help Center', 'call-tracking-metrics'); ?></a>.</p>
            </div>
        </div>
        
    <?php else: ?>
        <!-- Not Connected State -->
        <div class="bg-white border border-red-200 rounded-lg shadow-sm">
            <div class="bg-red-50 px-6 py-4 border-b border-red-200 rounded-t-lg">
                <div class="flex items-center">
                    <div class="w-3 h-3 bg-red-500 rounded-full mr-3"></div>
                    <h2 class="text-lg font-semibold text-gray-800"><?php _e('API Not Connected', 'call-tracking-metrics'); ?></h2>
                </div>
            </div>
            
            <div class="p-6 text-center">
                <svg class="w-16 h-16 mx-auto mb-4 text-red-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.08 15.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
                <h3 class="text-lg font-medium text-gray-800 mb-2"><?php _e('API Connection Required', 'call-tracking-metrics'); ?></h3>
                <p class="text-gray-600 mb-4">
                    <?php _e('You need to configure your API credentials to view account information and API activity.', 'call-tracking-metrics'); ?>
                </p>
                <a href="?page=call-tracking-metrics&tab=general" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    <?php _e('Configure API Settings', 'call-tracking-metrics'); ?>
                </a>
            </div>
        </div>
    <?php endif; ?>
    
</div>

<script>
// Localize script data for API tab
var ctmApiData = {
    api_key: '<?= esc_js($apiKey ?? '') ?>',
    api_secret: '<?= esc_js($apiSecret ?? '') ?>',
    nonce: '<?= wp_create_nonce('ctm_test_api_connection') ?>',
    change_api_nonce: '<?= wp_create_nonce('ctm_change_api_keys') ?>',
    disable_api_nonce: '<?= wp_create_nonce('ctm_disable_api') ?>',
    initial_connected: <?= ($api_connected && !empty($accountInfo)) ? 'true' : 'false' ?>
};
</script> 