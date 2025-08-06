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
        <div class="bg-white border border-gray-200 rounded-xl shadow-lg overflow-hidden">
            <div class="bg-gradient-to-r from-blue-500 to-cyan-500 px-6 py-4 flex items-center justify-between">
                <div class="flex items-center">
                    <div class="w-3 h-3 bg-white rounded-full mr-3 shadow-sm"></div>
                    <h2 class="text-lg font-bold text-white"><?php _e('Account Information', 'call-tracking-metrics'); ?></h2>
                    <span class="ml-3 px-3 py-1 bg-white/20 backdrop-blur-sm text-white text-xs font-semibold rounded-full border border-white/30"><?php _e('Active', 'call-tracking-metrics'); ?></span>
                </div>
                <!-- Action buttons -->
                <div class="flex gap-3">
                    <button id="ctm-change-api-btn" type="button" class="bg-white/20 hover:bg-white/30 backdrop-blur-sm text-white px-4 py-2 rounded-lg font-medium transition-all duration-200 flex items-center gap-2 border border-white/30">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <?php _e('Change API Keys', 'call-tracking-metrics'); ?>
                    </button>
                    <button id="ctm-disable-api-btn" type="button" class="bg-red-500/20 hover:bg-red-500/30 backdrop-blur-sm text-white px-4 py-2 rounded-lg font-medium transition-all duration-200 flex items-center gap-2 border border-red-300/30">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18.364 5.636M5.636 18.364l12.728-12.728"/>
                        </svg>
                        <?php _e('Disable API', 'call-tracking-metrics'); ?>
                    </button>
                </div>
            </div>
            
            <div class="p-6">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Account Details -->
                    <div class="space-y-4">
                        <div class="flex items-center gap-2 mb-4">
                            <div class="w-2 h-8 bg-gradient-to-b from-blue-500 to-cyan-500 rounded-full"></div>
                            <h3 class="text-lg font-semibold text-gray-800"><?php _e('Account Details', 'call-tracking-metrics'); ?></h3>
                        </div>
                        <?php if (isset($accountInfo['account']) && is_array($accountInfo['account'])): ?>
                            <?php $account = $accountInfo['account']; ?>
                            <div class="space-y-3">
                                <div class="bg-gray-50 rounded-lg p-4 border border-gray-100">
                                    <div class="flex justify-between items-center">
                                        <span class="text-gray-600 font-medium"><?php _e('Account Name:', 'call-tracking-metrics'); ?></span>
                                        <span class="font-semibold text-gray-800"><?= esc_html($account['name'] ?? 'N/A') ?></span>
                                    </div>
                                </div>
                                <div class="bg-gray-50 rounded-lg p-4 border border-gray-100">
                                    <div class="flex justify-between items-center">
                                        <span class="text-gray-600 font-medium"><?php _e('Account ID:', 'call-tracking-metrics'); ?></span>
                                        <span class="font-mono font-semibold text-gray-800"><?= esc_html($account['id'] ?? 'N/A') ?></span>
                                    </div>
                                </div>
                                <div class="bg-green-50 rounded-lg p-4 border border-green-100">
                                    <div class="flex justify-between items-center">
                                        <span class="text-gray-600 font-medium"><?php _e('Status:', 'call-tracking-metrics'); ?></span>
                                        <span class="px-3 py-1 bg-green-100 text-green-800 text-sm font-semibold rounded-full flex items-center gap-1">
                                            <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                                            <?= esc_html(ucfirst($account['status'] ?? 'active')) ?>
                                        </span>
                                    </div>
                                </div>
                                <?php if (!empty($account['phone'])): ?>
                                <div class="bg-gray-50 rounded-lg p-4 border border-gray-100">
                                    <div class="flex justify-between items-center">
                                        <span class="text-gray-600 font-medium"><?php _e('Phone:', 'call-tracking-metrics'); ?></span>
                                        <span class="text-gray-800"><?= esc_html($account['phone']) ?></span>
                                    </div>
                                </div>
                                <?php endif; ?>
                                <?php if (!empty($account['address'])): ?>
                                <div class="bg-gray-50 rounded-lg p-4 border border-gray-100">
                                    <div class="flex justify-between items-center">
                                        <span class="text-gray-600 font-medium"><?php _e('Address:', 'call-tracking-metrics'); ?></span>
                                        <span class="text-gray-800"><?= esc_html($account['address']) ?></span>
                                    </div>
                                </div>
                                <?php endif; ?>
                                <?php if (!empty($account['plan'])): ?>
                                <div class="bg-gray-50 rounded-lg p-4 border border-gray-100">
                                    <div class="flex justify-between items-center">
                                        <span class="text-gray-600 font-medium"><?php _e('Plan:', 'call-tracking-metrics'); ?></span>
                                        <span class="text-gray-800"><?= esc_html($account['plan']) ?></span>
                                    </div>
                                </div>
                                <?php endif; ?>
                                <?php if (!empty($account['usage'])): ?>
                                <div class="bg-gray-50 rounded-lg p-4 border border-gray-100">
                                    <div class="flex justify-between items-center">
                                        <span class="text-gray-600 font-medium"><?php _e('Usage:', 'call-tracking-metrics'); ?></span>
                                        <span class="text-gray-800"><?= esc_html($account['usage']) ?></span>
                                    </div>
                                </div>
                                <?php endif; ?>
                                <?php if (!empty($account['website'])): ?>
                                <div class="bg-gray-50 rounded-lg p-4 border border-gray-100">
                                    <div class="flex justify-between items-center">
                                        <span class="text-gray-600 font-medium"><?php _e('Website:', 'call-tracking-metrics'); ?></span>
                                        <a href="<?= esc_url($account['website']) ?>" target="_blank" rel="noopener noreferrer" class="text-blue-600 hover:text-blue-800 underline font-medium"><?= esc_html($account['website']) ?></a>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div class="bg-yellow-50 rounded-lg p-4 border border-yellow-100">
                                <p class="text-yellow-800 text-sm"><?php _e('Account information not available', 'call-tracking-metrics'); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- API Information -->
                    <div class="space-y-4">
                        <div class="flex items-center gap-2 mb-4">
                            <div class="w-2 h-8 bg-gradient-to-b from-purple-500 to-pink-500 rounded-full"></div>
                            <h3 class="text-lg font-semibold text-gray-800"><?php _e('API Information', 'call-tracking-metrics'); ?></h3>
                        </div>
                        <div class="space-y-3">
                            <div class="bg-gray-50 rounded-lg p-4 border border-gray-100">
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600 font-medium"><?php _e('API Key:', 'call-tracking-metrics'); ?></span>
                                    <span class="font-mono text-gray-800 bg-white px-2 py-1 rounded border">
                                        <?= esc_html(substr($apiKey ?? '', 0, 8)) ?>••••••••
                                    </span>
                                </div>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-4 border border-gray-100">
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600 font-medium"><?php _e('Base URL:', 'call-tracking-metrics'); ?></span>
                                    <span class="text-gray-800 break-all font-mono text-sm"><?= esc_html(ctm_get_api_url()) ?></span>
                                </div>
                            </div>
                            <div class="bg-blue-50 rounded-lg p-4 border border-blue-100">
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-600 font-medium"><?php _e('Last Connected:', 'call-tracking-metrics'); ?></span>
                                    <span class="text-blue-800 font-semibold"><?= date('M j, Y g:i A') ?></span>
                                </div>
                            </div>
                        </div>
                        <!-- Add Change API Keys and Disable API buttons -->
                        <!-- Change API Keys Modal -->
                        <div id="ctm-change-api-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm hidden">
                            <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-md w-full relative border border-gray-100">
                                <div class="flex items-center gap-3 mb-6">
                                    <div class="w-3 h-3 bg-blue-500 rounded-full"></div>
                                    <h3 class="text-xl font-bold text-gray-800"><?php _e('Change API Keys', 'call-tracking-metrics'); ?></h3>
                                </div>
                                <form id="ctm-change-api-form">
                                    <div class="mb-6">
                                        <label for="ctm_new_api_key" class="block text-gray-700 font-semibold mb-2"><?php _e('API Key', 'call-tracking-metrics'); ?></label>
                                        <input type="text" id="ctm_new_api_key" name="ctm_new_api_key" class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all" required autocomplete="off">
                                    </div>
                                    <div class="mb-6">
                                        <label for="ctm_new_api_secret" class="block text-gray-700 font-semibold mb-2"><?php _e('API Secret', 'call-tracking-metrics'); ?></label>
                                        <input type="text" id="ctm_new_api_secret" name="ctm_new_api_secret" class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all" required autocomplete="off">
                                    </div>
                                    <div class="flex justify-end gap-3 mt-8">
                                        <button type="button" id="ctm-cancel-change-api" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-6 py-3 rounded-lg font-medium transition-all duration-200 flex items-center gap-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                            <?php _e('Cancel', 'call-tracking-metrics'); ?>
                                        </button>
                                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition-all duration-200 flex items-center gap-2 shadow-lg">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            <?php _e('Save', 'call-tracking-metrics'); ?>
                                        </button>
                                    </div>
                                </form>
                                <button id="ctm-close-change-api" class="absolute top-4 right-4 text-gray-400 hover:text-gray-700 text-2xl leading-none transition-colors">&times;</button>
                            </div>
                        </div>
                        <!-- Disable API Modal -->
                        <div id="ctm-disable-api-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm hidden">
                            <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-md w-full relative border border-gray-100">
                                <div class="flex items-center gap-3 mb-6">
                                    <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                                    <h3 class="text-xl font-bold text-red-700"><?php _e('Disable API', 'call-tracking-metrics'); ?></h3>
                                </div>
                                <div class="bg-red-50 rounded-lg p-4 border border-red-200 mb-6">
                                    <p class="text-red-800 text-sm leading-relaxed"><?php _e('Are you sure you want to disable the API and clear all credentials? This action cannot be undone.', 'call-tracking-metrics'); ?></p>
                                </div>
                                <div class="flex justify-end gap-3">
                                    <button type="button" id="ctm-cancel-disable-api" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-6 py-3 rounded-lg font-medium transition-all duration-200 flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                        <?php _e('Cancel', 'call-tracking-metrics'); ?>
                                    </button>
                                    <button type="button" id="ctm-confirm-disable-api" class="bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-lg font-medium transition-all duration-200 flex items-center gap-2 shadow-lg">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                        <?php _e('Disable API', 'call-tracking-metrics'); ?>
                                    </button>
                                </div>
                                <button id="ctm-close-disable-api" class="absolute top-4 right-4 text-gray-400 hover:text-gray-700 text-2xl leading-none transition-colors">&times;</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Real-time API Connection Monitor -->
        <div class="bg-white border border-gray-200 rounded-xl shadow-lg overflow-hidden">
            <div class="bg-gradient-to-r from-green-500 to-emerald-500 px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div id="ctm-status-indicator" class="w-3 h-3 bg-white rounded-full mr-3 animate-pulse shadow-sm"></div>
                        <h2 class="text-lg font-bold text-white"><?php _e('Live API Connection Monitor', 'call-tracking-metrics'); ?></h2>
                        <span id="ctm-status-badge" class="ml-3 px-3 py-1 bg-white/20 backdrop-blur-sm text-white text-xs font-semibold rounded-full border border-white/30"><?php _e('Connected', 'call-tracking-metrics'); ?></span>
                    </div>
                    <div class="text-right text-white">
                        <div id="ctm-countdown" class="text-sm font-medium"><?php _e('Next test in 10m', 'call-tracking-metrics'); ?></div>
                        <div id="ctm-last-test" class="text-xs opacity-80"><?= date('g:i:s A') ?></div>
                    </div>
                </div>
            </div>
            
            <div class="p-6">
                <div class="flex items-center gap-4 mb-6">
                    <button id="ctm-test-api-btn" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition-all duration-200 flex items-center gap-2 shadow-lg">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <?php _e('Test API Connection', 'call-tracking-metrics'); ?>
                    </button>
                    <button id="ctm-toggle-auto-test" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-medium transition-all duration-200 flex items-center gap-2 shadow-lg">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <?php _e('Auto-Test: OFF', 'call-tracking-metrics'); ?>
                    </button>
                    <button id="ctm-clear-logs-btn" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 rounded-lg font-medium transition-all duration-200 flex items-center gap-2 shadow-lg">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        <?php _e('Clear Logs', 'call-tracking-metrics'); ?>
                    </button>
                </div>
                
                <!-- Progress Bar -->
                <div id="ctm-progress-container" class="hidden mb-6">
                    <div class="flex justify-between text-sm text-gray-600 mb-3">
                        <span class="font-medium"><?php _e('Connection Test Progress', 'call-tracking-metrics'); ?></span>
                        <span id="ctm-progress-percent" class="font-semibold">0%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
                        <div id="ctm-progress-bar" class="bg-gradient-to-r from-blue-500 to-cyan-500 h-3 rounded-full transition-all duration-300 shadow-sm" style="width: 0%"></div>
                    </div>
                </div>
                
                <!-- Test Logs -->
                <div id="ctm-test-logs" class="bg-gray-50 border border-gray-200 rounded-xl p-4 h-64 overflow-y-auto font-mono text-sm shadow-inner">
                    <div class="text-gray-500 italic"><?php _e('Click "Test Now" to see real-time logs...', 'call-tracking-metrics'); ?></div>
                </div>
                <!-- Account Summary (shown after successful connection) -->
                <div id="ctm-account-summary" class="hidden mt-6 p-4 bg-green-50 border border-green-200 rounded-xl">
                    <h5 class="font-semibold text-green-800 mb-3 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 15c2.5 0 4.847.655 6.879 1.804M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        <?php _e('Account Summary', 'call-tracking-metrics'); ?>
                    </h5>
                    <div id="ctm-account-details" class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm"></div>
                </div>
                <!-- Technical Details (collapsible) -->
                <div id="ctm-technical-details" class="hidden mt-6">
                    <button type="button" onclick="jQuery('#ctm-tech-details-content').toggleClass('hidden'); jQuery('#ctm-tech-details-icon').toggleClass('rotate-90');" class="flex items-center gap-2 text-sm text-gray-600 hover:text-gray-800 transition-colors font-medium">
                        <svg id="ctm-tech-details-icon" class="w-4 h-4 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                        <?php _e('View Technical Details', 'call-tracking-metrics'); ?>
                    </button>
                    <div id="ctm-tech-details-content" class="hidden mt-3 p-4 bg-gray-100 rounded-xl text-sm font-mono overflow-x-auto border"></div>
                </div>
            </div>
        </div>
        


        
    <?php else: ?>
        <!-- Not Connected State -->
        <div class="bg-white border border-red-200 rounded-xl shadow-lg overflow-hidden">
            <div class="bg-gradient-to-r from-red-500 to-pink-500 px-6 py-4">
                <div class="flex items-center">
                    <div class="w-3 h-3 bg-white rounded-full mr-3 shadow-sm"></div>
                    <h2 class="text-lg font-bold text-white"><?php _e('API Not Connected', 'call-tracking-metrics'); ?></h2>
                </div>
            </div>
            
            <div class="p-8 text-center">
                <div class="w-20 h-20 mx-auto mb-6 bg-red-100 rounded-full flex items-center justify-center">
                    <svg class="w-10 h-10 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.08 15.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-3"><?php _e('API Connection Required', 'call-tracking-metrics'); ?></h3>
                <p class="text-gray-600 mb-6 text-lg leading-relaxed max-w-md mx-auto">
                    <?php _e('You need to configure your API credentials to view account information and API activity.', 'call-tracking-metrics'); ?>
                </p>
                <a href="?page=call-tracking-metrics&tab=general" class="inline-flex items-center px-8 py-4 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold transition-all duration-200 shadow-lg hover:shadow-xl">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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