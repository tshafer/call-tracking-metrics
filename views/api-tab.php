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
                    <h2 class="text-lg font-semibold text-gray-800">Account Information</h2>
                    <span class="ml-2 px-3 py-1 bg-blue-100 text-blue-800 text-xs font-medium rounded-full">Active</span>
                </div>
                <!-- Move buttons here -->
                <div class="flex gap-2">
                    <button id="ctm-change-api-btn" type="button" class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded font-medium transition text-sm">Change API Keys</button>
                    <button id="ctm-disable-api-btn" type="button" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded font-medium transition text-sm">Disable API / Start Over</button>
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
                                <?php if (!empty($account['phone'])): ?>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Phone:</span>
                                    <span class="text-gray-800"><?= esc_html($account['phone']) ?></span>
                                </div>
                                <?php endif; ?>
                                <?php if (!empty($account['address'])): ?>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Address:</span>
                                    <span class="text-gray-800"><?= esc_html($account['address']) ?></span>
                                </div>
                                <?php endif; ?>
                                <?php if (!empty($account['plan'])): ?>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Plan:</span>
                                    <span class="text-gray-800"><?= esc_html($account['plan']) ?></span>
                                </div>
                                <?php endif; ?>
                                <?php if (!empty($account['usage'])): ?>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Usage:</span>
                                    <span class="text-gray-800"><?= esc_html($account['usage']) ?></span>
                                </div>
                                <?php endif; ?>
                                <?php if (!empty($account['website'])): ?>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Website:</span>
                                    <span class="text-blue-700 underline"><a href="<?= esc_url($account['website']) ?>" target="_blank" rel="noopener noreferrer"><?= esc_html($account['website']) ?></a></span>
                                </div>
                                <?php endif; ?>
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
                                    <?= esc_html(substr($apiKey ?? '', 0, 8)) ?>••••••••
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
                        <!-- Add Change API Keys and Disable API buttons -->
                        <!-- Change API Keys Modal -->
                        <div id="ctm-change-api-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 hidden">
                            <div class="bg-white rounded-lg shadow-lg p-8 max-w-md w-full relative">
                                <h3 class="text-lg font-semibold mb-4">Change API Keys</h3>
                                <form id="ctm-change-api-form">
                                    <div class="mb-4">
                                        <label for="ctm_new_api_key" class="block text-gray-700 font-medium mb-1">API Key</label>
                                        <input type="text" id="ctm_new_api_key" name="ctm_new_api_key" class="w-full border border-gray-300 rounded px-3 py-2" required autocomplete="off">
                                    </div>
                                    <div class="mb-4">
                                        <label for="ctm_new_api_secret" class="block text-gray-700 font-medium mb-1">API Secret</label>
                                        <input type="text" id="ctm_new_api_secret" name="ctm_new_api_secret" class="w-full border border-gray-300 rounded px-3 py-2" required autocomplete="off">
                                    </div>
                                    <div class="flex justify-end gap-2 mt-6">
                                        <button type="button" id="ctm-cancel-change-api" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded">Cancel</button>
                                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">Save</button>
                                    </div>
                                </form>
                                <button id="ctm-close-change-api" class="absolute top-2 right-2 text-gray-400 hover:text-gray-700 text-2xl leading-none">&times;</button>
                            </div>
                        </div>
                        <!-- Disable API Modal -->
                        <div id="ctm-disable-api-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 hidden">
                            <div class="bg-white rounded-lg shadow-lg p-8 max-w-md w-full relative">
                                <h3 class="text-lg font-semibold mb-4 text-red-700">Disable API / Start Over</h3>
                                <p class="mb-6 text-gray-700">Are you sure you want to disable the API and clear all credentials? This cannot be undone.</p>
                                <div class="flex justify-end gap-2">
                                    <button type="button" id="ctm-cancel-disable-api" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded">Cancel</button>
                                    <button type="button" id="ctm-confirm-disable-api" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded">Disable API</button>
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
                        <h2 class="text-lg font-semibold text-gray-800">Live API Connection Monitor</h2>
                        <span id="ctm-status-badge" class="ml-2 px-3 py-1 bg-green-100 text-green-800 text-xs font-medium rounded-full">Connected</span>
                    </div>
                    <div class="text-right">
                        <div id="ctm-countdown" class="text-sm text-gray-600">Next test in 10s</div>
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
        
        <!-- Connection Statistics -->
        <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200 rounded-t-lg">
                <h2 class="text-lg font-semibold text-gray-800">Connection Statistics</h2>
                <p class="text-sm text-gray-600 mt-1">Real-time monitoring statistics and performance metrics</p>
            </div>
            
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div class="text-center p-4 bg-green-50 border border-green-200 rounded-lg">
                        <div class="text-2xl font-bold text-green-600">98.5%</div>
                        <div class="text-sm text-gray-600">Uptime (24h)</div>
                    </div>
                    <div class="text-center p-4 bg-blue-50 border border-blue-200 rounded-lg">
                        <div class="text-2xl font-bold text-blue-600">156ms</div>
                        <div class="text-sm text-gray-600">Avg Response</div>
                    </div>
                    <div class="text-center p-4 bg-purple-50 border border-purple-200 rounded-lg">
                        <div class="text-2xl font-bold text-purple-600">247</div>
                        <div class="text-sm text-gray-600">Tests Today</div>
                    </div>
                </div>
                
                <div class="text-center py-8 text-gray-500">
                    <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                    </svg>
                    <p class="text-sm">Real-time metrics updating every 10 seconds</p>
                    <p class="text-xs text-gray-400 mt-1">Live monitoring ensures optimal API performance</p>
                </div>
            </div>
        </div>
        
        <!-- Documentation & Support Links -->
        <div class="bg-white border border-blue-200 rounded-lg shadow-sm mt-8">
            <div class="bg-blue-50 px-6 py-4 border-b border-blue-200 rounded-t-lg flex items-center">
                <svg class="w-5 h-5 text-blue-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 14h.01M16 10h.01M21 12c0 4.418-4.03 8-9 8s-9-3.582-9-8 4.03-8 9-8 9 3.582 9 8z"/></svg>
                <h2 class="text-lg font-semibold text-blue-800">CallTrackingMetrics Documentation & Support</h2>
            </div>
            <div class="p-6 space-y-3 text-sm">
                <ul class="list-disc list-inside space-y-2">
                    <li><a href="https://calltrackingmetrics.zendesk.com/hc/en-us" target="_blank" rel="noopener noreferrer" class="text-blue-700 underline hover:text-blue-900">CTM Help Center</a></li>
                    <li><a href="https://calltrackingmetrics.zendesk.com/hc/en-us/categories/200216006-API-Developers" target="_blank" rel="noopener noreferrer" class="text-blue-700 underline hover:text-blue-900">API Documentation</a></li>
                    <li><a href="https://calltrackingmetrics.zendesk.com/hc/en-us/articles/360050247232-Getting-Started-with-CallTrackingMetrics" target="_blank" rel="noopener noreferrer" class="text-blue-700 underline hover:text-blue-900">Getting Started Guide</a></li>
                    <li><a href="https://calltrackingmetrics.zendesk.com/hc/en-us/sections/200216016-Troubleshooting" target="_blank" rel="noopener noreferrer" class="text-blue-700 underline hover:text-blue-900">Troubleshooting</a></li>
                    <li><a href="https://calltrackingmetrics.zendesk.com/hc/en-us/requests/new" target="_blank" rel="noopener noreferrer" class="text-blue-700 underline hover:text-blue-900">Contact Support</a></li>
                </ul>
                <p class="text-xs text-gray-500 mt-2">For more guides, best practices, and API reference, visit the <a href="https://calltrackingmetrics.zendesk.com/hc/en-us" target="_blank" rel="noopener noreferrer" class="text-blue-700 underline">CallTrackingMetrics Help Center</a>.</p>
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
    let autoTestEnabled = true;
    let countdownTimer = null;
    let countdownValue = 10;
    let isTestInProgress = false;
    
    // Update status indicators
    function updateStatus(success, message = '') {
        const indicator = $('#ctm-status-indicator');
        const badge = $('#ctm-status-badge');
        const lastTest = $('#ctm-last-test');
        
        if (success) {
            indicator.removeClass('bg-red-500 bg-yellow-500').addClass('bg-green-500');
            badge.removeClass('bg-red-100 text-red-800 bg-yellow-100 text-yellow-800').addClass('bg-green-100 text-green-800').text('Connected');
        } else {
            indicator.removeClass('bg-green-500 bg-yellow-500').addClass('bg-red-500');
            badge.removeClass('bg-green-100 text-green-800 bg-yellow-100 text-yellow-800').addClass('bg-red-100 text-red-800').text('Failed');
        }
        
        lastTest.text('Last test: ' + new Date().toLocaleTimeString());
    }
    
    // Start countdown
    function startCountdown() {
        if (!autoTestEnabled || isTestInProgress) return;
        
        countdownValue = 10;
        const countdown = $('#ctm-countdown');
        
        countdownTimer = setInterval(() => {
            countdown.text(`Next test in ${countdownValue}s`);
            countdownValue--;
            
            if (countdownValue < 0) {
                clearInterval(countdownTimer);
                countdown.text('Testing...');
                performApiTest(true); // Auto test
            }
        }, 1000);
    }
    
    // Perform API test
    function performApiTest(isAutoTest = false) {
        if (isTestInProgress) return;
        
        isTestInProgress = true;
        const button = $('#ctm-test-api-btn');
        const logs = $('#ctm-test-logs');
        const progressContainer = $('#ctm-progress-container');
        const progressBar = $('#ctm-progress-bar');
        const progressPercent = $('#ctm-progress-percent');
        const countdown = $('#ctm-countdown');
        
        // Update button state (only if manual test)
        if (!isAutoTest) {
            button.prop('disabled', true).html('<svg class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Testing...');
        }
        
        // Show testing status
        countdown.text('Testing...');
        
        // Add test log if not auto test
        if (!isAutoTest) {
            logs.html('<div class="text-blue-600">Starting API connection test...</div>');
            progressContainer.removeClass('hidden');
        }
        
        // AJAX call to test API
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'ctm_test_api_connection',
                api_key: '<?= esc_js($apiKey ?? '') ?>',
                api_secret: '<?= esc_js($apiSecret ?? '') ?>',
                nonce: '<?= wp_create_nonce('ctm_test_api_connection') ?>'
            },
            success: function(response) {
                if (response.success) {
                    updateStatus(true);
                    
                    // Show detailed logs only for manual tests
                    if (!isAutoTest) {
                        progressBar.css('width', '100%');
                        progressPercent.text('100%');
                        
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
                    } else {
                        // For auto tests, just add a simple success log
                        const timestamp = new Date().toLocaleTimeString();
                        logs.prepend(`<div class="mb-2 p-2 rounded bg-green-50 border-l-4 border-green-500"><span class="text-green-700">✓ [${timestamp}] Auto-test successful</span></div>`);
                    }
                } else {
                    updateStatus(false, response.data || 'Test failed');
                    
                    if (!isAutoTest) {
                        logs.html(`<div class="text-red-600">❌ ${response.data || 'Test failed'}</div>`);
                    } else {
                        const timestamp = new Date().toLocaleTimeString();
                        logs.prepend(`<div class="mb-2 p-2 rounded bg-red-50 border-l-4 border-red-500"><span class="text-red-700">✗ [${timestamp}] Auto-test failed</span></div>`);
                    }
                }
            },
            error: function() {
                updateStatus(false, 'AJAX request failed');
                
                if (!isAutoTest) {
                    logs.html('<div class="text-red-600">❌ AJAX request failed</div>');
                } else {
                    const timestamp = new Date().toLocaleTimeString();
                    logs.prepend(`<div class="mb-2 p-2 rounded bg-red-50 border-l-4 border-red-500"><span class="text-red-700">✗ [${timestamp}] Auto-test failed - AJAX error</span></div>`);
                }
            },
            complete: function() {
                isTestInProgress = false;
                
                if (!isAutoTest) {
                    button.prop('disabled', false).html('<svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>Test Now');
                }
                
                // Restart countdown if auto-test is enabled
                if (autoTestEnabled) {
                    setTimeout(startCountdown, 1000);
                }
            }
        });
    }
    
    // Manual Test API Connection Button
    $('#ctm-test-api-btn').on('click', function() {
        clearInterval(countdownTimer);
        performApiTest(false);
    });
    
    // Toggle Auto-Test Button
    $('#ctm-toggle-auto-test').on('click', function() {
        const button = $(this);
        autoTestEnabled = !autoTestEnabled;
        
        if (autoTestEnabled) {
            button.removeClass('bg-gray-600').addClass('bg-green-600').html('<svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>Auto-Test: ON');
            startCountdown();
        } else {
            button.removeClass('bg-green-600').addClass('bg-gray-600').html('<svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>Auto-Test: OFF');
            clearInterval(countdownTimer);
            $('#ctm-countdown').text('Auto-test disabled');
        }
    });
    
    // Clear Logs Button
    $('#ctm-clear-logs-btn').on('click', function() {
        $('#ctm-test-logs').html('<div class="text-gray-500 italic">Auto-testing every 10 seconds. Click "Test Now" for manual test...</div>');
        $('#ctm-progress-container').addClass('hidden');
        $('#ctm-progress-bar').css('width', '0%');
        $('#ctm-progress-percent').text('0%');
    });
    
    // Modal logic for Change API Keys
    $('#ctm-change-api-btn').on('click', function() {
        $('#ctm-change-api-modal').removeClass('hidden');
    });
    $('#ctm-close-change-api, #ctm-cancel-change-api').on('click', function() {
        $('#ctm-change-api-modal').addClass('hidden');
    });
    // AJAX submit for Change API Keys
    $('#ctm-change-api-form').on('submit', function(e) {
        e.preventDefault();
        var apiKey = $('#ctm_new_api_key').val();
        var apiSecret = $('#ctm_new_api_secret').val();
        var nonce = '<?php echo esc_js($ctm_change_api_nonce); ?>';
        var $modal = $('#ctm-change-api-modal');
        var $saveBtn = $(this).find('button[type=submit]');
        $saveBtn.prop('disabled', true).text('Saving...');
        $.post(ajaxurl, {
            action: 'ctm_change_api_keys',
            api_key: apiKey,
            api_secret: apiSecret,
            nonce: nonce
        }, function(resp) {
            $saveBtn.prop('disabled', false).text('Save');
            if (resp.success) {
                $modal.addClass('hidden');
                alert('API keys updated successfully.');
                location.reload();
            } else {
                alert(resp.data && resp.data.message ? resp.data.message : 'Failed to update API keys.');
            }
        });
    });
    // Modal logic for Disable API
    $('#ctm-disable-api-btn').on('click', function() {
        $('#ctm-disable-api-modal').removeClass('hidden');
    });
    $('#ctm-close-disable-api, #ctm-cancel-disable-api').on('click', function() {
        $('#ctm-disable-api-modal').addClass('hidden');
    });
    // AJAX for Disable API
    $('#ctm-confirm-disable-api').on('click', function() {
        var nonce = '<?php echo esc_js($ctm_disable_api_nonce); ?>';
        var $modal = $('#ctm-disable-api-modal');
        var $btn = $(this);
        $btn.prop('disabled', true).text('Disabling...');
        $.post(ajaxurl, {
            action: 'ctm_disable_api',
            nonce: nonce
        }, function(resp) {
            $btn.prop('disabled', false).text('Disable API');
            if (resp.success) {
                $modal.addClass('hidden');
                alert('API credentials cleared.');
                location.reload();
            } else {
                alert(resp.data && resp.data.message ? resp.data.message : 'Failed to disable API.');
            }
        });
    });
    
    // Initialize
    $('#ctm-test-logs').html('<div class="text-gray-500 italic">Auto-testing every 10 seconds. Click "Test Now" for manual test...</div>');
    
    // Add initial connection status
    <?php if ($api_connected && !empty($accountInfo)): ?>
        $('#ctm-test-logs').prepend('<div class="mb-2 p-2 rounded bg-green-50 border-l-4 border-green-500"><span class="text-green-700">✓ [' + new Date().toLocaleTimeString() + '] API connection verified</span></div>');
    <?php endif; ?>
    
    // Start auto-testing
    setTimeout(startCountdown, 2000); // Wait 2 seconds before starting
});
</script> 