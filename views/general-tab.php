<?php
// General tab view

// Ensure required variables are defined with defaults
$apiStatus = $apiStatus ?? 'not_connected';
$apiKey = $apiKey ?? '';
$apiSecret = $apiSecret ?? '';

$apiKey = 'a558177d58fba59529de6c72c3c749defd033718';
$apiSecret = '4272ece96eb1d0b7066efc3ed90e436001db';
?>
<form method="post" action="" class="space-y-6">
    <?php wp_nonce_field('ctm_save_settings', 'ctm_settings_nonce'); ?>
    
    <?php if ($apiStatus !== 'connected'): ?>
        <!-- API Connection Only View (Not Connected) -->
        <div class="max-w-4xl mx-auto">
            <div class="bg-white p-8 rounded-lg shadow-lg border border-gray-200">
                <div class="text-center mb-6">
                    <div class="flex items-center justify-center mb-4">
                        <div class="bg-[#e6f7ff] p-4 rounded-full">
                           <img src="<?= plugin_dir_url(__DIR__) . '/assets/images/ctm_logo-mark_cyan_400x400.png' ?>" alt="CallTrackingMetrics Logo" class="h-10">
                        </div>
                    </div>
                    <h2 class="text-2xl font-bold text-[#16294f] mb-2 font-brand-heading"><?php _e('Connect to CallTrackingMetrics', 'call-tracking-metrics'); ?></h2>
                    <p class="text-gray-600 font-brand-body"><?php _e('Enter your API credentials to get started with CTM integration', 'call-tracking-metrics'); ?></p>
                </div>
                

                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block mb-2 text-gray-700 font-medium"><?php _e('API Key', 'call-tracking-metrics'); ?></label>
                        <div class="relative">
                            <input type="password" id="ctm_api_key" name="ctm_api_key" value="<?= esc_attr($apiKey) ?>" class="block w-full rounded border-gray-300 focus:ring-[#02bdf6] focus:border-[#02bdf6] pr-10" placeholder="<?php esc_attr_e('Enter your API key', 'call-tracking-metrics'); ?>" />
                            <button type="button" tabindex="-1" onclick="let f=document.getElementById('ctm_api_key');f.type=f.type==='password'?'text':'password';this.innerHTML=f.type==='password'?'<svg class=\'w-5 h-5\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M15 12a3 3 0 11-6 0 3 3 0 016 0z\'/><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z\'/></svg>':'<svg class=\'w-5 h-5\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a9.956 9.956 0 012.223-3.592m3.31-2.687A9.956 9.956 0 0112 5c4.477 0 8.268 2.943 9.542 7a9.956 9.956 0 01-4.043 5.306M15 12a3 3 0 11-6 0 3 3 0 016 0z\'/><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M3 3l18 18\'/></svg>';" class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-[#02bdf6] focus:outline-none">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </button>
                        </div>
                    </div>
                    <div>
                        <label class="block mb-2 text-gray-700 font-medium"><?php _e('API Secret', 'call-tracking-metrics'); ?></label>
                        <div class="relative">
                            <input type="password" id="ctm_api_secret" name="ctm_api_secret" value="<?= esc_attr($apiSecret) ?>" class="block w-full rounded border-gray-300 focus:ring-[#02bdf6] focus:border-[#02bdf6] pr-10" placeholder="<?php esc_attr_e('Enter your API secret', 'call-tracking-metrics'); ?>" />
                            <button type="button" tabindex="-1" onclick="let f=document.getElementById('ctm_api_secret');f.type=f.type==='password'?'text':'password';this.innerHTML=f.type==='password'?'<svg class=\'w-5 h-5\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M15 12a3 3 0 11-6 0 3 3 0 016 0z\'/><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z\'/></svg>':'<svg class=\'w-5 h-5\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a9.956 9.956 0 012.223-3.592m3.31-2.687A9.956 9.956 0 0112 5c4.477 0 8.268 2.943 9.542 7a9.956 9.956 0 01-4.043 5.306M15 12a3 3 0 11-6 0 3 3 0 016 0z\'/><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M3 3l18 18\'/></svg>';" class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-[#02bdf6] focus:outline-none">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </button>
                        </div>
                    </div>
                </div>
                

                

                

                
                <!-- Real-time API Test Logs -->
                <div id="api-test-logs" class="hidden mt-6 p-6 bg-gray-50 border border-gray-200 rounded-lg max-h-96 overflow-y-auto">
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="font-semibold text-[#16294f] flex items-center gap-2 font-brand-heading">
                            <svg class="w-5 h-5 text-[#02bdf6] inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                            <?php _e('Connection Test', 'call-tracking-metrics'); ?>
                        </h4>
                        <div class="flex gap-2">
                            <span id="test-duration" class="text-xs text-gray-500 bg-gray-200 px-2 py-1 rounded hidden"></span>
                            <button type="button" onclick="clearTestLogs()" class="text-xs text-gray-500 hover:text-gray-700 bg-gray-200 hover:bg-gray-300 px-2 py-1 rounded transition flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                <?php _e('Clear', 'call-tracking-metrics'); ?>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Progress Bar -->
                    <div id="test-progress" class="mb-4 hidden">
                        <div class="flex justify-between text-xs text-gray-600 mb-1">
                            <span><?php _e('Testing Progress', 'call-tracking-metrics'); ?></span>
                            <span id="progress-text">0%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div id="progress-bar" class="bg-blue-600 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                        </div>
                    </div>
                    
                    <div id="api-log-content" class="text-sm space-y-2"></div>
                    
                    <!-- Account Summary (shown after successful connection) -->
                    <div id="account-summary" class="hidden mt-4 p-3 bg-green-50 border border-green-200 rounded">
                        <h5 class="font-semibold text-green-800 mb-2 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 15c2.5 0 4.847.655 6.879 1.804M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            <?php _e('Account Summary', 'call-tracking-metrics'); ?>
                        </h5>
                        <div id="account-details" class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs"></div>
                    </div>
                    
                    <!-- Technical Details (collapsible) -->
                    <div id="technical-details" class="hidden mt-4">
                        <button type="button" onclick="toggleTechnicalDetails()" class="flex items-center gap-2 text-xs text-gray-600 hover:text-gray-800 transition">
                            <svg id="tech-details-icon" class="w-3 h-3 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                            <?php _e('View Technical Details', 'call-tracking-metrics'); ?>
                        </button>
                        <div id="tech-details-content" class="hidden mt-2 p-3 bg-gray-100 rounded text-xs font-mono overflow-x-auto"></div>
                    </div>
                </div>
                
                <div class="mt-8 flex justify-center">
                    <button type="submit" id="save-api-btn" class="bg-blue-600 hover:bg-blue-700 !text-white font-bold px-8 py-4 rounded-lg shadow-lg transition text-lg flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <?php _e('Save API Credentials', 'call-tracking-metrics'); ?>
                    </button>
                </div>
            </div>
        </div>
        
    <?php else: ?>
        <!-- Full Settings View (Connected) -->
        <input type="hidden" name="ctm_api_key" value="<?= esc_attr($apiKey) ?>">
        <input type="hidden" name="ctm_api_secret" value="<?= esc_attr($apiSecret) ?>">
        <div class="mb-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 mb-2">
                <div class="bg-blue-50 border-l-2 border-blue-400 p-2 rounded flex items-center gap-2 text-sm">
                    <span class="font-semibold"><?php _e('API Connection:', 'call-tracking-metrics'); ?></span>
                    <span class="bg-green-100 text-green-700 px-2 py-0.5 rounded text-xs font-medium"><?php _e('Connected', 'call-tracking-metrics'); ?></span>
                </div>
                <div class="bg-blue-50 border-l-2 border-blue-400 p-2 rounded flex items-center gap-2 text-sm">
                    <span class="font-semibold"><?php _e('Integrations:', 'call-tracking-metrics'); ?></span>
                    <?php if ($cf7Enabled): ?><span class="bg-green-100 text-green-700 px-2 py-0.5 rounded text-xs font-medium">CF7</span><?php else: ?><span class="bg-gray-100 text-gray-700 px-2 py-0.5 rounded text-xs font-medium">CF7</span><?php endif; ?>
                    <?php if ($gfEnabled): ?><span class="bg-green-100 text-green-700 px-2 py-0.5 rounded text-xs font-medium ml-1">GF</span><?php else: ?><span class="bg-gray-100 text-gray-700 px-2 py-0.5 rounded text-xs font-medium ml-1">GF</span><?php endif; ?>
                </div>
                <div class="bg-blue-50 border-l-2 border-blue-400 p-2 rounded flex items-center gap-2 text-sm">
                    <span class="font-semibold"><?php _e('Dashboard Widget:', 'call-tracking-metrics'); ?></span>
                    <?php if ($dashboardEnabled): ?><span class="bg-green-100 text-green-700 px-2 py-0.5 rounded text-xs font-medium">Enabled</span><?php else: ?><span class="bg-gray-100 text-gray-700 px-2 py-0.5 rounded text-xs font-medium">Disabled</span><?php endif; ?>
                </div>
                <div class="bg-blue-50 border-l-2 border-blue-400 p-2 rounded flex items-center gap-2 text-sm">
                    <span class="font-semibold"><?php _e('Debug Mode:', 'call-tracking-metrics'); ?></span>
                    <?php if ($debugEnabled): ?><span class="bg-green-100 text-green-700 px-2 py-0.5 rounded text-xs font-medium">Enabled</span><?php else: ?><span class="bg-gray-100 text-gray-700 px-2 py-0.5 rounded text-xs font-medium">Disabled</span><?php endif; ?>
                </div>
            </div>

        </div>
        <!-- Settings Form Fields -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            
            <!-- Tracking Script Section -->
            <div class="bg-white border border-gray-200 rounded-lg p-6 mb-6 shadow-sm max-w-2xl">
                <label for="ctm_tracking_script" class="text-xl font-semibold mb-4 text-gray-700"><?php _e('Tracking Script', 'call-tracking-metrics'); ?></label>
                <p class="text-gray-500 text-sm mb-4"><?php _e('This script is automatically fetched from CallTrackingMetrics. You can override it if needed, but we recommend using the auto-fetched version for accuracy.', 'call-tracking-metrics'); ?></p>
                <div class="flex flex-col md:flex-row md:items-center gap-4 mb-4">
                    <textarea id="ctm_tracking_script" name="call_track_account_script" rows="3"
                        class="p-2 block w-full rounded border border-gray-300 focus:ring-blue-500 focus:border-blue-500 transition bg-gray-100 text-gray-700 disabled:bg-gray-100 disabled:text-gray-400 disabled:cursor-not-allowed read-only:bg-gray-100 read-only:text-gray-400 read-only:cursor-not-allowed shadow-sm"
                        style="min-height: 60px; font-family: monospace; font-size: 0.97em; resize: vertical;"
                        readonly><?= esc_textarea(get_option('call_track_account_script')) ?></textarea>
                </div>
                <div class="flex items-center gap-2 mt-2">
                    <input type="checkbox" id="ctm_tracking_override_checkbox" class="mr-2">
                    <label for="ctm_tracking_override_checkbox" class="text-gray-700 select-none cursor-pointer"><?php _e('Allow manual override of tracking code', 'call-tracking-metrics'); ?></label>
                </div>
                <div class="flex items-center gap-2 mt-4">
                    <input type="checkbox" id="ctm_auto_inject_tracking_script" name="ctm_auto_inject_tracking_script" value="1" class="mr-2" <?= checked(get_option('ctm_auto_inject_tracking_script'), 1, false) ?>>
                    <label for="ctm_auto_inject_tracking_script" class="text-gray-700 select-none cursor-pointer font-medium"><?php _e('Auto-inject tracking script into site <head>', 'call-tracking-metrics'); ?></label>
                </div>
                <p class="text-gray-500 text-xs mt-1 ml-6"><?php _e('If enabled, the tracking script above will be automatically inserted into your site\'s <head> on every page.', 'call-tracking-metrics'); ?></p>
            </div>
            <!-- Debug Mode Section -->
            <div class="bg-white p-6 rounded-lg shadow border border-gray-200">
                <h2 class="text-xl font-semibold mb-4 text-gray-700"><?php _e('Options', 'call-tracking-metrics'); ?></h2>
                <div class="flex items-center gap-2 mb-2">
                    <input type="checkbox" id="ctm_debug_enabled" name="ctm_debug_enabled" value="1" class="mr-2" <?= checked(get_option('ctm_debug_enabled'), 1, false) ?>>
                    <label for="ctm_debug_enabled" class="text-gray-700 select-none cursor-pointer font-medium"><?php _e('Enable Debugging (show Debug tab)', 'call-tracking-metrics'); ?></label>
                </div>
                <div class="flex items-center gap-2 mb-2">
                    <input type="checkbox" id="ctm_dashboard_enabled" name="ctm_dashboard_enabled" value="1" class="mr-2" <?= checked(get_option('ctm_dashboard_enabled'), 1, false) ?>>
                    <label for="ctm_dashboard_enabled" class="text-gray-700 select-none cursor-pointer font-medium"><?php _e('Enable Dashboard Widget', 'call-tracking-metrics'); ?></label>
                </div>
            </div>
            <div class="bg-white p-6 rounded-lg shadow border border-gray-200">
                <h2 class="text-xl font-semibold mb-4 text-gray-700"><?php _e('Integrations', 'call-tracking-metrics'); ?></h2>
                
                <!-- Contact Form 7 Integration -->
                <label class="flex items-center mb-2 <?= (!is_plugin_active('contact-form-7/wp-contact-form-7.php') && !class_exists('WPCF7_ContactForm') && !function_exists('wpcf7_contact_form')) ? 'opacity-50 cursor-not-allowed' : '' ?>">
                    <input type="checkbox" 
                           name="ctm_api_cf7_enabled" 
                           value="1"
                           <?= checked($cf7Enabled, 1, false) ?> 
                           <?= (!is_plugin_active('contact-form-7/wp-contact-form-7.php') && !class_exists('WPCF7_ContactForm') && !function_exists('wpcf7_contact_form')) ? 'disabled' : '' ?>
                           class="mr-2 rounded border-gray-300 focus:ring-blue-500 <?= (!is_plugin_active('contact-form-7/wp-contact-form-7.php') && !class_exists('WPCF7_ContactForm') && !function_exists('wpcf7_contact_form')) ? 'opacity-50 cursor-not-allowed' : '' ?>" />
                    <span class="<?= (!is_plugin_active('contact-form-7/wp-contact-form-7.php') && !class_exists('WPCF7_ContactForm') && !function_exists('wpcf7_contact_form')) ? 'text-gray-400' : '' ?>"><?php _e('Enable Contact Form 7 Integration', 'call-tracking-metrics'); ?></span>
                    <?php if (!is_plugin_active('contact-form-7/wp-contact-form-7.php') && !class_exists('WPCF7_ContactForm') && !function_exists('wpcf7_contact_form')): ?>
                        <span class="ml-2 text-xs text-red-600 font-medium"><?php _e('(Plugin required)', 'call-tracking-metrics'); ?></span>
                    <?php endif; ?>
                </label>
                <!-- Integration notes for Contact Form 7 -->
                <div class="ml-6 text-xs text-gray-700 space-y-1 mb-3">
                    <div><strong><?php _e('Note:', 'call-tracking-metrics'); ?></strong> <?php _e('It is required to use a form that captures a telephone number (<code>input type="tel"</code>) in order for Contact Form 7 to integrate properly with our FormReactor. For more information, see', 'call-tracking-metrics'); ?> <a href="https://www.calltrackingmetrics.com/support/integrations/wordpress" target="_blank" rel="noopener" class="underline text-blue-700"><?php _e('Using the CallTrackingMetrics WordPress Plugin', 'call-tracking-metrics'); ?></a>.</div>
                    <div><strong><?php _e('Note:', 'call-tracking-metrics'); ?></strong> <?php _e('If you will request international (non-U.S.) phone numbers with your Contact Form 7 forms, we recommend using the plugin', 'call-tracking-metrics'); ?> <a href="https://wordpress.org/plugins/cf7-international-telephone-input/" target="_blank" rel="noopener" class="underline text-blue-700"><?php _e('International Telephone Input for Contact Form 7', 'call-tracking-metrics'); ?></a> <?php _e('to avoid possible formatting issues with our FormReactor. Both', 'call-tracking-metrics'); ?> <code>[tel]</code> <?php _e('and', 'call-tracking-metrics'); ?> <code>[intl_tel]</code> <?php _e('are now supported as phone inputs.', 'call-tracking-metrics'); ?></div>
                </div>
                
                <!-- Dismissible warning banner -->
                <?php if (!is_plugin_active('contact-form-7/wp-contact-form-7.php') && !class_exists('WPCF7_ContactForm') && !function_exists('wpcf7_contact_form')): ?>
                    <div id="cf7-notice" class="bg-yellow-50 border-l-2 border-yellow-400 text-yellow-800 p-1.5 rounded flex items-center justify-between gap-1 mb-3 mt-2 text-xs ml-6">
                        <div class="flex items-center justify-between w-full gap-1">
                            <span class="font-semibold"><?php _e('Contact Form 7 is not installed or activated.', 'call-tracking-metrics'); ?></span>
                            <a href="<?= esc_url(admin_url('plugin-install.php?s=contact+form+7&tab=search&type=term')) ?>" target="_blank" rel="noopener" class="ml-1 bg-blue-600 hover:bg-blue-700 text-white px-2 py-1 rounded shadow transition text-xs font-medium hover:text-white! text-white!"><?php _e('Install Contact Form 7', 'call-tracking-metrics'); ?></a>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Gravity Forms Integration -->
                <?php 
                $gf_plugin_active = function_exists('is_plugin_active') ? is_plugin_active('gravityforms/gravityforms.php') : false;
                $gf_class_exists = class_exists('GFAPI');
                $gf_available = $gf_plugin_active || $gf_class_exists;
                ?>
                <label class="flex items-center mb-2 <?= (!$gf_available) ? 'opacity-50 cursor-not-allowed' : '' ?>">
                    <input type="checkbox" 
                           name="ctm_api_gf_enabled" 
                           value="1"
                           <?= checked($gfEnabled, 1, false) ?> 
                           <?= (!$gf_available) ? 'disabled' : '' ?>
                           class="mr-2 rounded border-gray-300 focus:ring-blue-500 <?= (!$gf_available) ? 'opacity-50 cursor-not-allowed' : '' ?>" />
                    <span class="<?= (!$gf_available) ? 'text-gray-400' : '' ?>"><?php _e('Enable Gravity Forms Integration', 'call-tracking-metrics'); ?></span>
                    <?php if (!$gf_available): ?>
                        <span class="ml-2 text-xs text-red-600 font-medium"><?php _e('(Plugin required)', 'call-tracking-metrics'); ?></span>
                    <?php endif; ?>
                </label>
                <!-- Integration notes for Gravity Forms -->
                <div class="ml-6 text-xs text-gray-700 space-y-1 mb-3">
                    <div><strong><?php _e('Note:', 'call-tracking-metrics'); ?></strong> <?php _e('It is required to use a form that captures a telephone number (<code>input type="tel"</code>) in order for Gravity Forms to integrate properly with our FormReactor. For more information, see', 'call-tracking-metrics'); ?> <a href="https://www.calltrackingmetrics.com/support/integrations/wordpress" target="_blank" rel="noopener" class="underline text-blue-700"><?php _e('Using the CallTrackingMetrics WordPress Plugin', 'call-tracking-metrics'); ?></a>.</div>
                </div>
                
                <!-- Dismissible warning banner -->
                <?php if (!$gf_available): ?>
                    <div id="gf-notice" class="bg-yellow-50 border-l-2 border-yellow-400 text-yellow-800 p-1.5 rounded flex items-center justify-between gap-1 mb-3 mt-2 text-xs ml-6">
                        <div class="flex items-center justify-between w-full gap-1">
                            <span class="font-semibold"><?php _e('Gravity Forms is not installed or activated.', 'call-tracking-metrics'); ?></span>
                            <a href="<?= esc_url(admin_url('plugin-install.php?s=gravity+forms&tab=search&type=term')) ?>" target="_blank" rel="noopener" class="ml-1 bg-blue-600 hover:bg-blue-700 text-white px-2 py-1 rounded shadow transition text-xs font-medium hover:text-white! text-white!"><?php _e('Install Gravity Forms', 'call-tracking-metrics'); ?></a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="mt-8">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 !text-white px-6 py-2 rounded shadow font-semibold transition flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <?php _e('Save Settings', 'call-tracking-metrics'); ?>
            </button>
        </div>
    <?php endif; ?>
</form>