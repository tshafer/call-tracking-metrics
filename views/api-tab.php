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
        
        <!-- Header Section -->
        <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
            <div class="px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-6 h-6 bg-blue-500 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-xl font-semibold text-gray-900"><?php _e('API Dashboard', 'call-tracking-metrics'); ?></h1>
                            <p class="text-gray-600 text-sm"><?php _e('Monitor your Call Tracking Metrics API connection and account status', 'call-tracking-metrics'); ?></p>
                        </div>
                    </div>
                    <div class="flex space-x-2">
                        <button id="ctm-change-api-btn" class="bg-blue-50 hover:bg-blue-100 text-blue-700 px-4 py-2 rounded-lg font-medium transition-all duration-200 flex items-center space-x-2 border border-blue-200">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <span><?php _e('Change Keys', 'call-tracking-metrics'); ?></span>
                        </button>
                        <button id="ctm-disable-api-btn" class="bg-red-50 hover:bg-red-100 text-red-700 px-4 py-2 rounded-lg font-medium transition-all duration-200 flex items-center space-x-2 border border-red-200">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18.364 5.636M5.636 18.364l12.728-12.728"/>
                            </svg>
                            <span><?php _e('Disable API', 'call-tracking-metrics'); ?></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dashboard Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            
            <!-- Account Information Card -->
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center space-x-3">
                        <div class="w-6 h-6 bg-blue-500 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900"><?php _e('Account Information', 'call-tracking-metrics'); ?></h3>
                        <span class="ml-auto px-2 py-1 bg-green-100 text-green-800 text-xs font-medium rounded-full"><?php _e('Active', 'call-tracking-metrics'); ?></span>
                    </div>
                </div>
                
                <div class="p-6">
                    <?php if (isset($accountInfo['account']) && is_array($accountInfo['account'])): ?>
                        <?php $account = $accountInfo['account']; ?>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center py-2">
                                <span class="text-gray-600 text-sm"><?php _e('Account Name', 'call-tracking-metrics'); ?></span>
                                <span class="font-medium text-gray-900"><?= esc_html($account['name'] ?? 'N/A') ?></span>
                            </div>
                            <div class="flex justify-between items-center py-2">
                                <span class="text-gray-600 text-sm"><?php _e('Account ID', 'call-tracking-metrics'); ?></span>
                                <span class="font-mono font-medium text-gray-900"><?= esc_html($account['id'] ?? 'N/A') ?></span>
                            </div>
                            <div class="flex justify-between items-center py-2">
                                <span class="text-gray-600 text-sm"><?php _e('Status', 'call-tracking-metrics'); ?></span>
                                <span class="px-2 py-1 bg-green-100 text-green-800 text-xs font-medium rounded-full flex items-center space-x-1">
                                    <div class="w-1.5 h-1.5 bg-green-500 rounded-full"></div>
                                    <span><?= esc_html(ucfirst($account['status'] ?? 'active')) ?></span>
                                </span>
                            </div>
                            <?php if (!empty($account['phone'])): ?>
                            <div class="flex justify-between items-center py-2">
                                <span class="text-gray-600 text-sm"><?php _e('Phone', 'call-tracking-metrics'); ?></span>
                                <span class="font-medium text-gray-900"><?= esc_html($account['phone']) ?></span>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($account['website'])): ?>
                            <div class="flex justify-between items-center py-2">
                                <span class="text-gray-600 text-sm"><?php _e('Website', 'call-tracking-metrics'); ?></span>
                                <a href="<?= esc_url($account['website']) ?>" target="_blank" class="text-blue-600 hover:text-blue-800 underline font-medium text-sm"><?= esc_html($account['website']) ?></a>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($account['plan'])): ?>
                            <div class="flex justify-between items-center py-2">
                                <span class="text-gray-600 text-sm"><?php _e('Plan', 'call-tracking-metrics'); ?></span>
                                <span class="font-medium text-gray-900"><?= esc_html($account['plan']) ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-8">
                            <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <p class="text-gray-500 text-sm"><?php _e('Account information not available', 'call-tracking-metrics'); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- API Connection Card -->
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center space-x-3">
                        <div class="w-6 h-6 bg-gray-500 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900"><?php _e('API Connection', 'call-tracking-metrics'); ?></h3>
                        <span class="ml-auto px-2 py-1 bg-green-100 text-green-800 text-xs font-medium rounded-full"><?php _e('Connected', 'call-tracking-metrics'); ?></span>
                    </div>
                </div>
                
                <div class="p-6">
                    <div class="space-y-3">
                        <div class="flex justify-between items-center py-2">
                            <span class="text-gray-600 text-sm"><?php _e('API Key', 'call-tracking-metrics'); ?></span>
                            <span class="font-mono font-medium text-gray-900 bg-gray-50 px-2 py-1 rounded text-sm"><?= esc_html(substr($apiKey ?? '', 0, 8)) ?>••••••••</span>
                        </div>
                        <div class="flex justify-between items-center py-2">
                            <span class="text-gray-600 text-sm"><?php _e('Base URL', 'call-tracking-metrics'); ?></span>
                            <span class="font-mono font-medium text-gray-900 text-sm break-all"><?= esc_html(ctm_get_api_url()) ?></span>
                        </div>
                        <div class="flex justify-between items-center py-2">
                            <span class="text-gray-600 text-sm"><?php _e('Last Connected', 'call-tracking-metrics'); ?></span>
                            <span class="font-medium text-gray-900"><?= date('M j, Y g:i A') ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Connection Monitor Card - Full Width -->
        <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-6 h-6 bg-green-500 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900"><?php _e('Connection Monitor', 'call-tracking-metrics'); ?></h3>
                        <span id="ctm-status-badge" class="px-2 py-1 bg-green-100 text-green-800 text-xs font-medium rounded-full"><?php _e('Connected', 'call-tracking-metrics'); ?></span>
                    </div>
                    <div class="text-right">
                        <div id="ctm-countdown" class="text-sm font-medium text-gray-900"><?php _e('Next test in 10m', 'call-tracking-metrics'); ?></div>
                        <div id="ctm-last-test" class="text-xs text-gray-500"><?= date('g:i:s A') ?></div>
                    </div>
                </div>
            </div>
            
            <div class="p-6">
                <div class="flex flex-wrap gap-3 mb-6">
                    <button id="ctm-test-api-btn" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-all duration-200 flex items-center space-x-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span><?php _e('Test Connection', 'call-tracking-metrics'); ?></span>
                    </button>
                    <button id="ctm-toggle-auto-test" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition-all duration-200 flex items-center space-x-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span><?php _e('Auto-Test: OFF', 'call-tracking-metrics'); ?></span>
                    </button>
                </div>
                
                <!-- Progress Bar -->
                <div id="ctm-progress-container" class="hidden mb-6">
                    <div class="flex justify-between text-sm text-gray-600 mb-2">
                        <span class="font-medium"><?php _e('Connection Test Progress', 'call-tracking-metrics'); ?></span>
                        <span id="ctm-progress-percent" class="font-semibold">0%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
                        <div id="ctm-progress-bar" class="bg-blue-600 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                    </div>
                </div>
                
                <!-- Test Logs -->
                <div id="ctm-test-logs" class="bg-gray-50 border border-gray-200 rounded-lg p-4 h-48 overflow-y-auto font-mono text-sm">
                    <div class="text-gray-500 italic"><?php _e('Click "Test Connection" to see real-time logs...', 'call-tracking-metrics'); ?></div>
                </div>
            </div>
        </div>

        <!-- Account Summary Card -->
        <div id="ctm-account-summary" class="hidden bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center space-x-3">
                    <div class="w-6 h-6 bg-green-500 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900"><?php _e('Test Results', 'call-tracking-metrics'); ?></h3>
                    <span class="ml-auto px-2 py-1 bg-green-100 text-green-800 text-xs font-medium rounded-full"><?php _e('Success', 'call-tracking-metrics'); ?></span>
                </div>
            </div>
            
            <div class="p-6">
                <div id="ctm-account-details" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <!-- Populated by JavaScript -->
                </div>
            </div>
        </div>

        <!-- Technical Details Card -->
        <div id="ctm-technical-details" class="hidden bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-6 h-6 bg-gray-500 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900"><?php _e('Technical Details', 'call-tracking-metrics'); ?></h3>
                    </div>
                    <button type="button" id="ctm-toggle-technical" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <svg class="w-5 h-5 transform transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                </div>
            </div>
            
            <div id="ctm-technical-content" class="hidden p-6">
                <div id="ctm-tech-details-content" class="space-y-6">
                    <!-- Populated by JavaScript -->
                </div>
            </div>
        </div>

        <!-- Modals -->
        <!-- Change API Keys Modal -->
        <div id="ctm-change-api-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm hidden">
            <div class="bg-white rounded-lg shadow-xl p-6 max-w-md w-full mx-4 relative border border-gray-200">
                <div class="flex items-center space-x-3 mb-6">
                    <div class="w-6 h-6 bg-blue-500 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900"><?php _e('Change API Keys', 'call-tracking-metrics'); ?></h3>
                </div>
                <form id="ctm-change-api-form">
                    <div class="mb-4">
                        <label for="ctm_new_api_key" class="block text-gray-700 font-medium mb-2"><?php _e('API Key', 'call-tracking-metrics'); ?></label>
                        <input type="text" id="ctm_new_api_key" name="ctm_new_api_key" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all" required autocomplete="off">
                    </div>
                    <div class="mb-6">
                        <label for="ctm_new_api_secret" class="block text-gray-700 font-medium mb-2"><?php _e('API Secret', 'call-tracking-metrics'); ?></label>
                        <input type="text" id="ctm_new_api_secret" name="ctm_new_api_secret" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all" required autocomplete="off">
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" id="ctm-cancel-change-api" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg font-medium transition-all duration-200 flex items-center space-x-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            <span><?php _e('Cancel', 'call-tracking-metrics'); ?></span>
                        </button>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-all duration-200 flex items-center space-x-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span><?php _e('Save Changes', 'call-tracking-metrics'); ?></span>
                        </button>
                    </div>
                </form>
                <button id="ctm-close-change-api" class="absolute top-4 right-4 text-gray-400 hover:text-gray-700 text-xl leading-none transition-colors">&times;</button>
            </div>
        </div>

        <!-- Disable API Modal -->
        <div id="ctm-disable-api-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm hidden">
            <div class="bg-white rounded-lg shadow-xl p-6 max-w-md w-full mx-4 relative border border-gray-200">
                <div class="flex items-center space-x-3 mb-6">
                    <div class="w-6 h-6 bg-red-500 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18.364 5.636M5.636 18.364l12.728-12.728"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-red-700"><?php _e('Disable API', 'call-tracking-metrics'); ?></h3>
                </div>
                <div class="bg-red-50 rounded-lg p-4 border border-red-200 mb-6">
                    <div class="flex items-start space-x-3">
                        <svg class="w-5 h-5 text-red-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.08 15.5c-.77.833.192 2.5 1.732 2.5z"/>
                        </svg>
                        <p class="text-red-800 text-sm leading-relaxed"><?php _e('Are you sure you want to disable the API and clear all credentials? This action cannot be undone.', 'call-tracking-metrics'); ?></p>
                    </div>
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" id="ctm-cancel-disable-api" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg font-medium transition-all duration-200 flex items-center space-x-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        <span><?php _e('Cancel', 'call-tracking-metrics'); ?></span>
                    </button>
                    <button type="button" id="ctm-confirm-disable-api" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium transition-all duration-200 flex items-center space-x-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        <span><?php _e('Disable API', 'call-tracking-metrics'); ?></span>
                    </button>
                </div>
                <button id="ctm-close-disable-api" class="absolute top-4 right-4 text-gray-400 hover:text-gray-700 text-xl leading-none transition-colors">&times;</button>
            </div>
        </div>
        
    <?php else: ?>
        <!-- Not Connected State -->
        <div class="bg-white border border-red-200 rounded-lg shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-red-200">
                <div class="flex items-center space-x-3">
                    <div class="w-6 h-6 bg-red-500 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.08 15.5c-.77.833.192 2.5 1.732 2.5z"/>
                        </svg>
                    </div>
                    <h2 class="text-lg font-semibold text-gray-900"><?php _e('API Not Connected', 'call-tracking-metrics'); ?></h2>
                </div>
            </div>
            
            <div class="p-6 text-center">
                <div class="w-16 h-16 mx-auto mb-4 bg-red-100 rounded-lg flex items-center justify-center">
                    <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.08 15.5c-.77.833.192 2.5 1.732 2.5z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2"><?php _e('API Connection Required', 'call-tracking-metrics'); ?></h3>
                <p class="text-gray-600 mb-6 text-sm leading-relaxed max-w-md mx-auto">
                    <?php _e('You need to configure your API credentials to view account information and API activity.', 'call-tracking-metrics'); ?>
                </p>
                <a href="?page=call-tracking-metrics&tab=general" class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-all duration-200">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
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