<?php
// Documentation tab view with main tabs and subtabs
?>
<div class="mb-12">
    <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-8 mb-8">
        <div class="flex items-center mb-6 border-b border-[#02bdf6] pb-4">
            <svg class="w-7 h-7 text-[#02bdf6] mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 1.343-3 3s1.343 3 3 3 3-1.343 3-3-1.343-3-3-3zm0 10c-4.418 0-8-3.582-8-8s3.582-8 8-8 8 3.582 8 8-3.582 8-8 8z" /></svg>
            <h2 class="text-2xl font-bold text-[#16294f] tracking-tight font-brand-heading"><?php _e('Documentation', 'call-tracking-metrics'); ?></h2>
        </div>
        <div id="main-doc-tabs" class="mb-8">
            <nav class="flex flex-wrap border-b border-gray-200">
                <button class="main-doc-tab px-4 py-2 -mb-px text-gray-700 border-b-2 border-transparent hover:text-blue-700 hover:border-blue-300 flex items-center gap-2" data-tab="general">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    <?php _e('General', 'call-tracking-metrics'); ?>
                </button>
                <button class="main-doc-tab px-4 py-2 -mb-px text-gray-700 border-b-2 border-transparent hover:text-blue-700 hover:border-blue-300 flex items-center gap-2" data-tab="debug">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4"></path>
                    </svg>
                    <?php _e('Debug', 'call-tracking-metrics'); ?>
                </button>
                <button class="main-doc-tab px-4 py-2 -mb-px text-gray-700 border-b-2 border-transparent hover:text-blue-700 hover:border-blue-300 flex items-center gap-2" data-tab="api">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"></path>
                    </svg>
                    <?php _e('API', 'call-tracking-metrics'); ?>
                </button>
                <button class="main-doc-tab px-4 py-2 -mb-px text-gray-700 border-b-2 border-transparent hover:text-blue-700 hover:border-blue-300 flex items-center gap-2" data-tab="cti">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                    </svg>
                    <?php _e('CTI', 'call-tracking-metrics'); ?>
                </button>
            </nav>
        </div>
        <div id="main-doc-tab-content">
            <div class="main-doc-tab-panel" data-tab="general">
                <!-- General documentation content -->
                <div class="space-y-8">
                    <!-- Installation Section -->
                    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 p-6 rounded-lg border border-blue-200">
                        <div class="flex items-center mb-4">
                            <div class="bg-blue-100 p-2 rounded-lg mr-3">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <h3 class="text-xl font-semibold text-blue-700"><?php _e('Quick Installation', 'call-tracking-metrics'); ?></h3>
                        </div>
                        <ol class="list-decimal pl-6 mb-4 space-y-2">
                            <li class="flex items-start">
                                <span class="mr-2">📦</span>
                                <?php _e('Download and install the plugin from the WordPress plugin directory or upload it manually.', 'call-tracking-metrics'); ?>
                            </li>
                            <li class="flex items-start">
                                <span class="mr-2">⚡</span>
                                <?php _e('Activate the plugin in your WordPress admin.', 'call-tracking-metrics'); ?>
                            </li>
                            <li class="flex items-start">
                                <span class="mr-2">⚙️</span>
                                <?php _e('Go to <b>Settings &gt; CallTrackingMetrics</b> to configure.', 'call-tracking-metrics'); ?>
                            </li>
                        </ol>
                    </div>

                    <!-- API Key Management Section -->
                    <div class="bg-gradient-to-r from-green-50 to-emerald-50 p-6 rounded-lg border border-green-200">
                        <div class="flex items-center mb-4">
                            <div class="bg-green-100 p-2 rounded-lg mr-3">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                                </svg>
                            </div>
                            <h3 class="text-xl font-semibold text-green-700"><?php _e('🔑 API Key Management', 'call-tracking-metrics'); ?></h3>
                        </div>
                        <ul class="list-disc pl-6 mb-4 space-y-2">
                            <li class="flex items-start">
                                <span class="mr-2">🌐</span>
                                <?php _e('Log in to your <a href="https://app.calltrackingmetrics.com/accounts/edit#account-api" class="text-green-600 underline font-medium" target="_blank">CallTrackingMetrics account</a>.', 'call-tracking-metrics'); ?>
                            </li>
                            <li class="flex items-start">
                                <span class="mr-2">🔧</span>
                                <?php _e('Navigate to <b>Account Settings &gt; API Keys</b>.', 'call-tracking-metrics'); ?>
                            </li>
                            <li class="flex items-start">
                                <span class="mr-2">📋</span>
                                <?php _e('Copy your <b>API Key</b> and <b>API Secret</b> and enter them in the plugin settings.', 'call-tracking-metrics'); ?>
                            </li>
                            <li class="flex items-start">
                                <span class="mr-2">💾</span>
                                <?php _e('Click <b>Save Settings</b> to connect.', 'call-tracking-metrics'); ?>
                            </li>
                        </ul>
                    </div>

                    <!-- Integration Details Section -->
                    <div class="bg-gradient-to-r from-purple-50 to-pink-50 p-6 rounded-lg border border-purple-200">
                        <div class="flex items-center mb-4">
                            <div class="bg-purple-100 p-2 rounded-lg mr-3">
                                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                </svg>
                            </div>
                            <h3 class="text-xl font-semibold text-purple-700"><?php _e('🔗 Integration Details', 'call-tracking-metrics'); ?></h3>
                        </div>
                        <div class="grid md:grid-cols-2 gap-4">
                            <div class="bg-white p-4 rounded-lg border border-purple-100">
                                <div class="flex items-center mb-2">
                                    <span class="text-2xl mr-2">📝</span>
                                    <h4 class="font-semibold text-purple-700"><?php _e('Contact Form 7', 'call-tracking-metrics'); ?></h4>
                                </div>
                                <p class="text-sm text-gray-600"><?php _e('Enable integration in the plugin settings. All CF7 submissions will be tracked and sent to CallTrackingMetrics.', 'call-tracking-metrics'); ?></p>
                            </div>
                            <div class="bg-white p-4 rounded-lg border border-purple-100">
                                <div class="flex items-center mb-2">
                                    <span class="text-2xl mr-2">⚡</span>
                                    <h4 class="font-semibold text-purple-700"><?php _e('Gravity Forms', 'call-tracking-metrics'); ?></h4>
                                </div>
                                <p class="text-sm text-gray-600"><?php _e('Enable integration in the plugin settings. All GF submissions will be tracked and sent to CallTrackingMetrics.', 'call-tracking-metrics'); ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Troubleshooting Section -->
                    <div class="bg-gradient-to-r from-red-50 to-pink-50 p-6 rounded-lg border border-red-200">
                        <div class="flex items-center mb-4">
                            <div class="bg-red-100 p-2 rounded-lg mr-3">
                                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.08 15.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                </svg>
                            </div>
                            <h3 class="text-xl font-semibold text-red-700"><?php _e('🔧 Troubleshooting', 'call-tracking-metrics'); ?></h3>
                        </div>
                        <ul class="list-disc pl-6 mb-4 space-y-2">
                            <li class="flex items-start">
                                <span class="mr-2">🐛</span>
                                <?php _e('Check the <b>Debug</b> tab for API logs and errors.', 'call-tracking-metrics'); ?>
                            </li>
                            <li class="flex items-start">
                                <span class="mr-2">🔐</span>
                                <?php _e('Ensure your API credentials are correct and have the necessary permissions.', 'call-tracking-metrics'); ?>
                            </li>
                            <li class="flex items-start">
                                <span class="mr-2">📧</span>
                                <?php _e('Contact <a href="mailto:support@calltrackingmetrics.com" class="text-red-600 underline font-medium">support@calltrackingmetrics.com</a> for help.', 'call-tracking-metrics'); ?>
                            </li>
                        </ul>
                    </div>

                    <!-- Security Section -->
                    <div class="bg-gradient-to-r from-indigo-50 to-blue-50 p-6 rounded-lg border border-indigo-200">
                        <div class="flex items-center mb-4">
                            <div class="bg-indigo-100 p-2 rounded-lg mr-3">
                                <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                </svg>
                            </div>
                            <h3 class="text-xl font-semibold text-indigo-700"><?php _e('🔒 Security', 'call-tracking-metrics'); ?></h3>
                        </div>
                        <ul class="list-disc pl-6 mb-4 space-y-2">
                            <li class="flex items-start">
                                <span class="mr-2">💾</span>
                                <?php _e('API credentials are stored securely in the WordPress options table.', 'call-tracking-metrics'); ?>
                            </li>
                            <li class="flex items-start">
                                <span class="mr-2">👤</span>
                                <?php _e('Only users with <b>manage_options</b> capability can view or change settings.', 'call-tracking-metrics'); ?>
                            </li>
                        </ul>
                    </div>

                    <!-- FAQ Section -->
                    <div class="bg-gradient-to-r from-teal-50 to-cyan-50 p-6 rounded-lg border border-teal-200">
                        <div class="flex items-center mb-4">
                            <div class="bg-teal-100 p-2 rounded-lg mr-3">
                                <svg class="w-6 h-6 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <h3 class="text-xl font-semibold text-teal-700"><?php _e('❓ FAQ', 'call-tracking-metrics'); ?></h3>
                        </div>
                        <div class="space-y-4">
                            <div class="bg-white p-4 rounded-lg border border-teal-100">
                                <div class="flex items-start">
                                    <span class="text-2xl mr-3">❓</span>
                                    <div>
                                        <p class="font-semibold text-teal-700 mb-1"><?php _e('Why is my API connection failing?', 'call-tracking-metrics'); ?></p>
                                        <p class="text-sm text-gray-600"><?php _e('Double-check your API Key and Secret, and ensure your account is active.', 'call-tracking-metrics'); ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-white p-4 rounded-lg border border-teal-100">
                                <div class="flex items-start">
                                    <span class="text-2xl mr-3">❓</span>
                                    <div>
                                        <p class="font-semibold text-teal-700 mb-1"><?php _e('Can I map custom fields?', 'call-tracking-metrics'); ?></p>
                                        <p class="text-sm text-gray-600"><?php _e('No, the plugin does not support custom field mapping. All form data is sent as is to CallTrackingMetrics.', 'call-tracking-metrics'); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Support Section -->
                    <div class="bg-gradient-to-r from-emerald-50 to-green-50 p-6 rounded-lg border border-emerald-200">
                        <div class="flex items-center mb-4">
                            <div class="bg-emerald-100 p-2 rounded-lg mr-3">
                                <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                </svg>
                            </div>
                            <h3 class="text-xl font-semibold text-emerald-700"><?php _e('💬 Support', 'call-tracking-metrics'); ?></h3>
                        </div>
                        <div class="grid md:grid-cols-2 gap-4">
                            <div class="bg-white p-4 rounded-lg border border-emerald-100">
                                <div class="flex items-center mb-2">
                                    <span class="text-2xl mr-2">📧</span>
                                    <h4 class="font-semibold text-emerald-700"><?php _e('Email Support', 'call-tracking-metrics'); ?></h4>
                                </div>
                                <a href="mailto:support@calltrackingmetrics.com" class="text-emerald-600 underline font-medium">support@calltrackingmetrics.com</a>
                            </div>
                            <div class="bg-white p-4 rounded-lg border border-emerald-100">
                                <div class="flex items-center mb-2">
                                    <span class="text-2xl mr-2">🌐</span>
                                    <h4 class="font-semibold text-emerald-700"><?php _e('Website', 'call-tracking-metrics'); ?></h4>
                                </div>
                                <a href="https://www.calltrackingmetrics.com/" class="text-emerald-600 underline font-medium" target="_blank">calltrackingmetrics.com</a>
                            </div>
                        </div>
                    </div>

                    <!-- Plugin Overview Section -->
                    <div class="bg-gradient-to-r from-gray-50 to-slate-50 p-6 rounded-lg border border-gray-200">
                        <div class="flex items-center mb-4">
                            <div class="bg-gray-100 p-2 rounded-lg mr-3">
                                <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <h3 class="text-xl font-semibold text-gray-700"><?php _e('📖 Plugin Overview', 'call-tracking-metrics'); ?></h3>
                        </div>
                        <p class="mb-4"><?php _e('The CallTrackingMetrics WordPress plugin connects your site to the CallTrackingMetrics platform, enabling advanced call tracking, form tracking, and analytics. It supports seamless integration with Contact Form 7 and Gravity Forms, and provides robust debugging and logging tools for administrators.', 'call-tracking-metrics'); ?></p>
                    </div>

                    <!-- Advanced Troubleshooting Section -->
                    <div class="bg-gradient-to-r from-amber-50 to-orange-50 p-6 rounded-lg border border-amber-200">
                        <div class="flex items-center mb-4">
                            <div class="bg-amber-100 p-2 rounded-lg mr-3">
                                <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                            </div>
                            <h3 class="text-xl font-semibold text-amber-700"><?php _e('🔍 Advanced Troubleshooting', 'call-tracking-metrics'); ?></h3>
                        </div>
                        <ul class="list-disc pl-6 mb-4 space-y-2">
                            <li class="flex items-start">
                                <span class="mr-2">📊</span>
                                <?php _e('If form submissions are not appearing in CallTrackingMetrics, ensure the plugin is connected and the correct API credentials are used.', 'call-tracking-metrics'); ?>
                            </li>
                            <li class="flex items-start">
                                <span class="mr-2">🐛</span>
                                <?php _e('Check the Debug tab for recent errors or failed API calls.', 'call-tracking-metrics'); ?>
                            </li>
                            <li class="flex items-start">
                                <span class="mr-2">📞</span>
                                <?php _e('Make sure your forms include required fields (such as phone number for FormReactor integration).', 'call-tracking-metrics'); ?>
                            </li>
                            <li class="flex items-start">
                                <span class="mr-2">🧹</span>
                                <?php _e('Clear any site caching plugins and test again.', 'call-tracking-metrics'); ?>
                            </li>
                            <li class="flex items-start">
                                <span class="mr-2">🔍</span>
                                <?php _e('Review your browser console for JavaScript errors that may block script execution.', 'call-tracking-metrics'); ?>
                            </li>
                        </ul>
                    </div>

                    <!-- Best Practices Section -->
                    <div class="bg-gradient-to-r from-lime-50 to-green-50 p-6 rounded-lg border border-lime-200">
                        <div class="flex items-center mb-4">
                            <div class="bg-lime-100 p-2 rounded-lg mr-3">
                                <svg class="w-6 h-6 text-lime-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <h3 class="text-xl font-semibold text-lime-700"><?php _e('✅ Best Practices', 'call-tracking-metrics'); ?></h3>
                        </div>
                        <ul class="list-disc pl-6 mb-4 space-y-2">
                            <li class="flex items-start">
                                <span class="mr-2">🔄</span>
                                <?php _e('Keep your plugin and WordPress core up to date for security and compatibility.', 'call-tracking-metrics'); ?>
                            </li>
                            <li class="flex items-start">
                                <span class="mr-2">🔐</span>
                                <?php _e('Use strong, unique API credentials and never share them publicly.', 'call-tracking-metrics'); ?>
                            </li>
                            <li class="flex items-start">
                                <span class="mr-2">🐛</span>
                                <?php _e('Enable debugging only when needed to avoid excessive log growth.', 'call-tracking-metrics'); ?>
                            </li>
                            <li class="flex items-start">
                                <span class="mr-2">🧹</span>
                                <?php _e('Regularly review logs and clear old entries to maintain performance.', 'call-tracking-metrics'); ?>
                            </li>
                        </ul>
                    </div>

                    <!-- Changelog Section -->
                    <div class="bg-gradient-to-r from-violet-50 to-purple-50 p-6 rounded-lg border border-violet-200">
                        <div class="flex items-center mb-4">
                            <div class="bg-violet-100 p-2 rounded-lg mr-3">
                                <svg class="w-6 h-6 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <h3 class="text-xl font-semibold text-violet-700"><?php _e('📝 Changelog', 'call-tracking-metrics'); ?></h3>
                        </div>
                        <div class="space-y-3">
                            <div class="bg-white p-4 rounded-lg border border-violet-100">
                                <div class="flex items-center mb-2">
                                    <span class="bg-violet-100 text-violet-700 px-2 py-1 rounded text-sm font-semibold mr-3">2.0.0</span>
                                    <span class="text-sm text-gray-500"><?php _e('Latest Release', 'call-tracking-metrics'); ?></span>
                                </div>
                                <p class="text-sm text-gray-600"><?php _e('Major refactor, improved UI, added debug tools, and enhanced API integration.', 'call-tracking-metrics'); ?></p>
                            </div>
                            <div class="bg-white p-4 rounded-lg border border-violet-100">
                                <div class="flex items-center mb-2">
                                    <span class="bg-gray-100 text-gray-700 px-2 py-1 rounded text-sm font-semibold mr-3">1.5.0</span>
                                </div>
                                <p class="text-sm text-gray-600"><?php _e('Added Gravity Forms support and enhanced API integration.', 'call-tracking-metrics'); ?></p>
                            </div>
                            <div class="bg-white p-4 rounded-lg border border-violet-100">
                                <div class="flex items-center mb-2">
                                    <span class="bg-gray-100 text-gray-700 px-2 py-1 rounded text-sm font-semibold mr-3">1.0.0</span>
                                </div>
                                <p class="text-sm text-gray-600"><?php _e('Initial release with Contact Form 7 integration.', 'call-tracking-metrics'); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="main-doc-tab-panel hidden" data-tab="debug">
                <!-- Debug subtabs -->
                <div id="debug-subtabs" class="mb-6">
                    <nav class="flex flex-wrap border-b border-gray-200">
                        <button class="debug-subtab px-4 py-2 -mb-px text-gray-700 border-b-2 border-transparent hover:text-blue-700 hover:border-blue-300" data-subtab="system-info"><?php _e('System Information', 'call-tracking-metrics'); ?></button>
                        <button class="debug-subtab px-4 py-2 -mb-px text-gray-700 border-b-2 border-transparent hover:text-blue-700 hover:border-blue-300" data-subtab="health-check"><?php _e('Health Check', 'call-tracking-metrics'); ?></button>
                        <button class="debug-subtab px-4 py-2 -mb-px text-gray-700 border-b-2 border-transparent hover:text-blue-700 hover:border-blue-300" data-subtab="performance-monitor"><?php _e('Performance Monitor', 'call-tracking-metrics'); ?></button>
                        <button class="debug-subtab px-4 py-2 -mb-px text-gray-700 border-b-2 border-transparent hover:text-blue-700 hover:border-blue-300" data-subtab="log-settings"><?php _e('Log Settings', 'call-tracking-metrics'); ?></button>
                        <button class="debug-subtab px-4 py-2 -mb-px text-gray-700 border-b-2 border-transparent hover:text-blue-700 hover:border-blue-300" data-subtab="daily-logs"><?php _e('Daily Logs', 'call-tracking-metrics'); ?></button>
                    </nav>
                </div>
                <div id="debug-subtab-content">
                    <div class="debug-subtab-panel" data-subtab="system-info">
                        <div class="space-y-8">
                            <!-- System Information Section -->
                            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 p-6 rounded-lg border border-blue-200">
                                <div class="flex items-center mb-4">
                                    <div class="bg-blue-100 p-2 rounded-lg mr-3">
                                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2h-5.586a1 1 0 01-.707-.293l-5.414-5.414a1 1 0 00-.293-.707V19a2 2 0 002 2zM9 9h6v6H9V9z"></path>
                                        </svg>
                                    </div>
                                    <h3 class="text-xl font-semibold text-blue-700"><?php _e('💻 System Information', 'call-tracking-metrics'); ?></h3>
                                </div>
                                <p class="mb-4"><?php _e('This section provides a comprehensive overview of your WordPress and server environment. It includes PHP version, WordPress version, memory usage, database details, and more. Use the <b>Export System Info</b> button to copy all details for support or troubleshooting.', 'call-tracking-metrics'); ?></p>
                                <ul class="list-disc pl-6 mb-4 space-y-2">
                                    <li class="flex items-start">
                                        <span class="mr-2">🌐</span>
                                        <?php _e('WordPress and server environment details', 'call-tracking-metrics'); ?>
                                    </li>
                                    <li class="flex items-start">
                                        <span class="mr-2">⚙️</span>
                                        <?php _e('PHP, MySQL, and server software versions', 'call-tracking-metrics'); ?>
                                    </li>
                                    <li class="flex items-start">
                                        <span class="mr-2">🔌</span>
                                        <?php _e('Active plugins and theme information', 'call-tracking-metrics'); ?>
                                    </li>
                                    <li class="flex items-start">
                                        <span class="mr-2">💾</span>
                                        <?php _e('Memory usage and limits', 'call-tracking-metrics'); ?>
                                    </li>
                                    <li class="flex items-start">
                                        <span class="mr-2">📤</span>
                                        <?php _e('Quick export for support tickets', 'call-tracking-metrics'); ?>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="debug-subtab-panel hidden" data-subtab="health-check">
                        <div class="space-y-8">
                            <!-- Health Check Section -->
                            <div class="bg-gradient-to-r from-green-50 to-emerald-50 p-6 rounded-lg border border-green-200">
                                <div class="flex items-center mb-4">
                                    <div class="bg-green-100 p-2 rounded-lg mr-3">
                                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                    <h3 class="text-xl font-semibold text-green-700"><?php _e('🏥 Health Check', 'call-tracking-metrics'); ?></h3>
                                </div>
                                <p class="mb-4"><?php _e('The Health Check feature runs a series of automated tests to assess your plugin and server configuration. It checks API connectivity, form integrations, server requirements, and plugin status. Use the <b>Run Health Check</b> button to get instant feedback and recommendations.', 'call-tracking-metrics'); ?></p>
                                <ul class="list-disc pl-6 mb-4 space-y-2">
                                    <li class="flex items-start">
                                        <span class="mr-2">🔑</span>
                                        <?php _e('API key and connection validation', 'call-tracking-metrics'); ?>
                                    </li>
                                    <li class="flex items-start">
                                        <span class="mr-2">📝</span>
                                        <?php _e('Form integration checks (Contact Form 7, Gravity Forms)', 'call-tracking-metrics'); ?>
                                    </li>
                                    <li class="flex items-start">
                                        <span class="mr-2">⚙️</span>
                                        <?php _e('Server requirements (PHP version, cURL, SSL, memory)', 'call-tracking-metrics'); ?>
                                    </li>
                                    <li class="flex items-start">
                                        <span class="mr-2">🔧</span>
                                        <?php _e('Plugin version, database tables, file permissions', 'call-tracking-metrics'); ?>
                                    </li>
                                    <li class="flex items-start">
                                        <span class="mr-2">💡</span>
                                        <?php _e('Actionable recommendations for any issues found', 'call-tracking-metrics'); ?>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="debug-subtab-panel hidden" data-subtab="performance-monitor">
                        <div class="space-y-8">
                            <!-- Performance Monitor Section -->
                            <div class="bg-gradient-to-r from-purple-50 to-pink-50 p-6 rounded-lg border border-purple-200">
                                <div class="flex items-center mb-4">
                                    <div class="bg-purple-100 p-2 rounded-lg mr-3">
                                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                        </svg>
                                    </div>
                                    <h3 class="text-xl font-semibold text-purple-700"><?php _e('⚡ Performance Monitor', 'call-tracking-metrics'); ?></h3>
                                </div>
                                <p class="mb-4"><?php _e('The Performance Monitor provides real-time metrics on memory usage, page load time, database queries, and API calls. Use this tool to identify bottlenecks and optimize your site\'s performance.', 'call-tracking-metrics'); ?></p>
                                <ul class="list-disc pl-6 mb-4 space-y-2">
                                    <li class="flex items-start">
                                        <span class="mr-2">📊</span>
                                        <?php _e('Live memory, CPU, and execution time stats', 'call-tracking-metrics'); ?>
                                    </li>
                                    <li class="flex items-start">
                                        <span class="mr-2">🗄️</span>
                                        <?php _e('Database query count and timing', 'call-tracking-metrics'); ?>
                                    </li>
                                    <li class="flex items-start">
                                        <span class="mr-2">⏱️</span>
                                        <?php _e('Page load and resource loading times', 'call-tracking-metrics'); ?>
                                    </li>
                                    <li class="flex items-start">
                                        <span class="mr-2">🌐</span>
                                        <?php _e('API call volume and response times', 'call-tracking-metrics'); ?>
                                    </li>
                                    <li class="flex items-start">
                                        <span class="mr-2">🔍</span>
                                        <?php _e('Breakdown of plugin and theme performance', 'call-tracking-metrics'); ?>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="debug-subtab-panel hidden" data-subtab="log-settings">
                        <div class="space-y-8">
                            <!-- Log Settings Section -->
                            <div class="bg-gradient-to-r from-orange-50 to-yellow-50 p-6 rounded-lg border border-orange-200">
                                <div class="flex items-center mb-4">
                                    <div class="bg-orange-100 p-2 rounded-lg mr-3">
                                        <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4"></path>
                                        </svg>
                                    </div>
                                    <h3 class="text-xl font-semibold text-orange-700"><?php _e('⚙️ Log Settings', 'call-tracking-metrics'); ?></h3>
                                </div>
                                <p class="mb-4"><?php _e('Configure how debug logs are managed. Set retention periods, enable automatic cleanup, and receive email notifications for critical errors. Keeping logs well-managed helps maintain site performance and security.', 'call-tracking-metrics'); ?></p>
                                <ul class="list-disc pl-6 mb-4 space-y-2">
                                    <li class="flex items-start">
                                        <span class="mr-2">📅</span>
                                        <?php _e('Set how many days logs are kept (1-365 days)', 'call-tracking-metrics'); ?>
                                    </li>
                                    <li class="flex items-start">
                                        <span class="mr-2">🧹</span>
                                        <?php _e('Enable or disable automatic log cleanup', 'call-tracking-metrics'); ?>
                                    </li>
                                    <li class="flex items-start">
                                        <span class="mr-2">📧</span>
                                        <?php _e('Configure email notifications for errors', 'call-tracking-metrics'); ?>
                                    </li>
                                    <li class="flex items-start">
                                        <span class="mr-2">📮</span>
                                        <?php _e('Set the notification email address', 'call-tracking-metrics'); ?>
                                    </li>
                                    <li class="flex items-start">
                                        <span class="mr-2">💾</span>
                                        <?php _e('Update log settings instantly', 'call-tracking-metrics'); ?>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="debug-subtab-panel hidden" data-subtab="daily-logs">
                        <div class="space-y-8">
                            <!-- Daily Logs Section -->
                            <div class="bg-gradient-to-r from-teal-50 to-cyan-50 p-6 rounded-lg border border-teal-200">
                                <div class="flex items-center mb-4">
                                    <div class="bg-teal-100 p-2 rounded-lg mr-3">
                                        <svg class="w-6 h-6 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                    </div>
                                    <h3 class="text-xl font-semibold text-teal-700"><?php _e('📋 Daily Logs', 'call-tracking-metrics'); ?></h3>
                                </div>
                                <p class="mb-4"><?php _e('View, filter, and manage daily debug logs. Each day\'s log shows all plugin activity, errors, warnings, and more. You can email logs, clear them, or view detailed context for each entry.', 'call-tracking-metrics'); ?></p>
                                <ul class="list-disc pl-6 mb-4 space-y-2">
                                    <li class="flex items-start">
                                        <span class="mr-2">📅</span>
                                        <?php _e('Browse logs by date', 'call-tracking-metrics'); ?>
                                    </li>
                                    <li class="flex items-start">
                                        <span class="mr-2">📊</span>
                                        <?php _e('See error, warning, and info counts', 'call-tracking-metrics'); ?>
                                    </li>
                                    <li class="flex items-start">
                                        <span class="mr-2">🔍</span>
                                        <?php _e('View detailed log entries and context', 'call-tracking-metrics'); ?>
                                    </li>
                                    <li class="flex items-start">
                                        <span class="mr-2">📧</span>
                                        <?php _e('Email logs for support or archiving', 'call-tracking-metrics'); ?>
                                    </li>
                                    <li class="flex items-start">
                                        <span class="mr-2">🗑️</span>
                                        <?php _e('Clear logs for specific days', 'call-tracking-metrics'); ?>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="main-doc-tab-panel hidden" data-tab="api">
                <!-- API subtabs -->
                <div id="api-subtabs" class="mb-6">
                    <nav class="flex flex-wrap border-b border-gray-200">
                        <button class="api-subtab px-4 py-2 -mb-px text-blue-700 border-b-2 border-transparent hover:text-blue-700 hover:border-blue-300" data-subtab="overview"><?php _e('Overview', 'call-tracking-metrics'); ?></button>
                        <button class="api-subtab px-4 py-2 -mb-px text-gray-700 border-b-2 border-transparent hover:text-blue-700 hover:border-blue-300" data-subtab="authentication"><?php _e('Authentication', 'call-tracking-metrics'); ?></button>
                        <button class="api-subtab px-4 py-2 -mb-px text-gray-700 border-b-2 border-transparent hover:text-blue-700 hover:border-blue-300" data-subtab="endpoints"><?php _e('Endpoints', 'call-tracking-metrics'); ?></button>
                    </nav>
                </div>
                <div id="api-subtab-content">
                    <div class="api-subtab-panel" data-subtab="overview">
                        <div class="space-y-8">
                            <!-- API Overview Section -->
                            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 p-6 rounded-lg border border-blue-200">
                                <div class="flex items-center mb-4">
                                    <div class="bg-blue-100 p-2 rounded-lg mr-3">
                                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"></path>
                                        </svg>
                                    </div>
                                    <h3 class="text-xl font-semibold text-blue-700"><?php _e('🌐 API Overview', 'call-tracking-metrics'); ?></h3>
                                </div>
                                <p class="mb-4"><?php _e('The CallTrackingMetrics API allows you to programmatically submit form data, retrieve call logs, and manage account settings. This plugin uses the API to send form submissions and fetch account information.', 'call-tracking-metrics'); ?></p>
                                <div class="grid md:grid-cols-3 gap-4">
                                    <div class="bg-white p-4 rounded-lg border border-blue-100">
                                        <div class="flex items-center mb-2">
                                            <span class="text-2xl mr-2">🔗</span>
                                            <h4 class="font-semibold text-blue-700"><?php _e('RESTful JSON API', 'call-tracking-metrics'); ?></h4>
                                        </div>
                                        <p class="text-sm text-gray-600"><?php _e('Modern REST API with JSON responses', 'call-tracking-metrics'); ?></p>
                                    </div>
                                    <div class="bg-white p-4 rounded-lg border border-blue-100">
                                        <div class="flex items-center mb-2">
                                            <span class="text-2xl mr-2">🔐</span>
                                            <h4 class="font-semibold text-blue-700"><?php _e('Secure Authentication', 'call-tracking-metrics'); ?></h4>
                                        </div>
                                        <p class="text-sm text-gray-600"><?php _e('API Key and Secret required', 'call-tracking-metrics'); ?></p>
                                    </div>
                                    <div class="bg-white p-4 rounded-lg border border-blue-100">
                                        <div class="flex items-center mb-2">
                                            <span class="text-2xl mr-2">📊</span>
                                            <h4 class="font-semibold text-blue-700"><?php _e('Comprehensive Features', 'call-tracking-metrics'); ?></h4>
                                        </div>
                                        <p class="text-sm text-gray-600"><?php _e('Form submission, call logs, and more', 'call-tracking-metrics'); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="api-subtab-panel hidden" data-subtab="authentication">
                        <div class="space-y-8">
                            <!-- Authentication Section -->
                            <div class="bg-gradient-to-r from-green-50 to-emerald-50 p-6 rounded-lg border border-green-200">
                                <div class="flex items-center mb-4">
                                    <div class="bg-green-100 p-2 rounded-lg mr-3">
                                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                                        </svg>
                                    </div>
                                    <h3 class="text-xl font-semibold text-green-700"><?php _e('🔐 API Authentication', 'call-tracking-metrics'); ?></h3>
                                </div>
                                <p class="mb-4"><?php _e('Authenticate using your API Key and Secret, which you can generate in your CallTrackingMetrics account settings. All requests must be made over HTTPS.', 'call-tracking-metrics'); ?></p>
                                <div class="bg-white p-4 rounded-lg border border-green-100">
                                    <h4 class="font-semibold text-green-700 mb-2"><?php _e('🔑 Authentication Headers', 'call-tracking-metrics'); ?></h4>
                                    <ul class="list-disc pl-6 mb-4 space-y-2">
                                        <li class="flex items-start">
                                            <span class="mr-2">📋</span>
                                            <?php _e('Send credentials as HTTP headers: <code class="bg-gray-100 px-1 rounded">X-Api-Key</code> and <code class="bg-gray-100 px-1 rounded">X-Api-Secret</code>', 'call-tracking-metrics'); ?>
                                        </li>
                                        <li class="flex items-start">
                                            <span class="mr-2">🔒</span>
                                            <?php _e('Never expose your credentials in client-side code', 'call-tracking-metrics'); ?>
                                        </li>
                                        <li class="flex items-start">
                                            <span class="mr-2">🌐</span>
                                            <?php _e('All requests must use HTTPS for security', 'call-tracking-metrics'); ?>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="api-subtab-panel hidden" data-subtab="endpoints">
                        <div class="space-y-8">
                            <!-- Endpoints Section -->
                            <div class="bg-gradient-to-r from-purple-50 to-pink-50 p-6 rounded-lg border border-purple-200">
                                <div class="flex items-center mb-4">
                                    <div class="bg-purple-100 p-2 rounded-lg mr-3">
                                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                        </svg>
                                    </div>
                                    <h3 class="text-xl font-semibold text-purple-700"><?php _e('⚡ API Endpoints', 'call-tracking-metrics'); ?></h3>
                                </div>
                                <div class="space-y-4">
                                    <div class="bg-white p-4 rounded-lg border border-purple-100">
                                        <div class="flex items-center mb-2">
                                            <span class="bg-green-100 text-green-700 px-2 py-1 rounded text-sm font-semibold mr-3">GET</span>
                                            <code class="bg-gray-100 px-2 py-1 rounded text-sm font-mono">/api/v1/accounts/</code>
                                        </div>
                                        <p class="text-sm text-gray-600"><?php _e('Get account information and validate credentials', 'call-tracking-metrics'); ?></p>
                                    </div>
                                    <div class="bg-white p-4 rounded-lg border border-purple-100">
                                        <div class="flex items-center mb-2">
                                            <span class="bg-green-100 text-green-700 px-2 py-1 rounded text-sm font-semibold mr-3">GET</span>
                                            <code class="bg-gray-100 px-2 py-1 rounded text-sm font-mono">/api/v1/accounts/{accountId}</code>
                                        </div>
                                        <p class="text-sm text-gray-600"><?php _e('Get detailed account information by ID', 'call-tracking-metrics'); ?></p>
                                    </div>
                                    <div class="bg-white p-4 rounded-lg border border-purple-100">
                                        <div class="flex items-center mb-2">
                                            <span class="bg-blue-100 text-blue-700 px-2 py-1 rounded text-sm font-semibold mr-3">POST</span>
                                            <code class="bg-gray-100 px-2 py-1 rounded text-sm font-mono">/api/v1/formreactor/{formId}</code>
                                        </div>
                                        <p class="text-sm text-gray-600"><?php _e('Submit form data to CallTrackingMetrics', 'call-tracking-metrics'); ?></p>
                                    </div>
                                    <div class="bg-white p-4 rounded-lg border border-purple-100">
                                        <div class="flex items-center mb-2">
                                            <span class="bg-green-100 text-green-700 px-2 py-1 rounded text-sm font-semibold mr-3">GET</span>
                                            <code class="bg-gray-100 px-2 py-1 rounded text-sm font-mono">/api/v1/calls</code>
                                        </div>
                                        <p class="text-sm text-gray-600"><?php _e('Retrieve call logs and analytics', 'call-tracking-metrics'); ?></p>
                                    </div>
                                    <div class="bg-white p-4 rounded-lg border border-purple-100">
                                        <div class="flex items-center mb-2">
                                            <span class="bg-green-100 text-green-700 px-2 py-1 rounded text-sm font-semibold mr-3">GET</span>
                                            <code class="bg-gray-100 px-2 py-1 rounded text-sm font-mono">/api/v1/tracking_numbers</code>
                                        </div>
                                        <p class="text-sm text-gray-600"><?php _e('Get available tracking numbers', 'call-tracking-metrics'); ?></p>
                                    </div>
                                    <div class="bg-white p-4 rounded-lg border border-purple-100">
                                        <div class="flex items-center mb-2">
                                            <span class="bg-green-100 text-green-700 px-2 py-1 rounded text-sm font-semibold mr-3">GET</span>
                                            <code class="bg-gray-100 px-2 py-1 rounded text-sm font-mono">/api/v1/accounts/{accountId}/scripts</code>
                                        </div>
                                        <p class="text-sm text-gray-600"><?php _e('Get tracking script for account', 'call-tracking-metrics'); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="main-doc-tab-panel hidden" data-tab="cti">
                <!-- CTI subtabs -->
                <div id="cti-subtabs" class="mb-6">
                    <nav class="flex flex-wrap border-b border-gray-200">
                        <button class="cti-subtab px-4 py-2 -mb-px text-blue-700 border-b-2 border-transparent hover:text-blue-700 hover:border-blue-300" data-subtab="overview"><?php _e('Overview', 'call-tracking-metrics'); ?></button>
                        <button class="cti-subtab px-4 py-2 -mb-px text-gray-700 border-b-2 border-transparent hover:text-blue-700 hover:border-blue-300" data-subtab="setup"><?php _e('Setup', 'call-tracking-metrics'); ?></button>
                        <button class="cti-subtab px-4 py-2 -mb-px text-gray-700 border-b-2 border-transparent hover:text-blue-700 hover:border-blue-300" data-subtab="features"><?php _e('Features', 'call-tracking-metrics'); ?></button>
                    </nav>
                </div>
                <div id="cti-subtab-content">
                    <div class="cti-subtab-panel" data-subtab="overview">
                        <div class="space-y-8">
                            <!-- CTI Overview Section -->
                            <div class="bg-gradient-to-r from-indigo-50 to-purple-50 p-6 rounded-lg border border-indigo-200">
                                <div class="flex items-center mb-4">
                                    <div class="bg-indigo-100 p-2 rounded-lg mr-3">
                                        <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                        </svg>
                                    </div>
                                    <h3 class="text-xl font-semibold text-indigo-700"><?php _e('📞 CTI Overview', 'call-tracking-metrics'); ?></h3>
                                </div>
                                <p class="mb-4"><?php _e('Computer Telephony Integration (CTI) connects your website and phone system, allowing you to track calls, trigger workflows, and display caller information in real time. CTI is available for advanced users and enterprise accounts.', 'call-tracking-metrics'); ?></p>
                            </div>

                            <!-- What is CTI Section -->
                            <div class="bg-gradient-to-r from-blue-50 to-cyan-50 p-6 rounded-lg border border-blue-200">
                                <div class="flex items-center mb-4">
                                    <div class="bg-blue-100 p-2 rounded-lg mr-3">
                                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                    <h4 class="text-lg font-semibold text-blue-700"><?php _e('❓ What is CTI?', 'call-tracking-metrics'); ?></h4>
                                </div>
                                <p class="mb-4"><?php _e('Computer Telephony Integration (CTI) is a technology that enables computers to interact with telephone systems. In the context of CallTrackingMetrics, CTI creates a seamless connection between your website visitors and your phone system, providing real-time data exchange and enhanced call management capabilities.', 'call-tracking-metrics'); ?></p>
                            </div>

                            <!-- Key Benefits Section -->
                            <div class="bg-gradient-to-r from-green-50 to-emerald-50 p-6 rounded-lg border border-green-200">
                                <div class="flex items-center mb-4">
                                    <div class="bg-green-100 p-2 rounded-lg mr-3">
                                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                    <h4 class="text-lg font-semibold text-green-700"><?php _e('✨ Key Benefits', 'call-tracking-metrics'); ?></h4>
                                </div>
                                <ul class="list-disc pl-6 mb-4 space-y-2">
                                    <li class="flex items-start">
                                        <span class="mr-2">📊</span>
                                        <strong><?php _e('Real-time Call Tracking:', 'call-tracking-metrics'); ?></strong> <?php _e('Monitor incoming calls as they happen with detailed analytics and reporting.', 'call-tracking-metrics'); ?>
                                    </li>
                                    <li class="flex items-start">
                                        <span class="mr-2">👥</span>
                                        <strong><?php _e('Enhanced Customer Experience:', 'call-tracking-metrics'); ?></strong> <?php _e('Provide personalized service by having caller information available before answering.', 'call-tracking-metrics'); ?>
                                    </li>
                                    <li class="flex items-start">
                                        <span class="mr-2">🤖</span>
                                        <strong><?php _e('Workflow Automation:', 'call-tracking-metrics'); ?></strong> <?php _e('Automatically trigger actions based on call events, such as logging calls to CRM systems.', 'call-tracking-metrics'); ?>
                                    </li>
                                    <li class="flex items-start">
                                        <span class="mr-2">⚡</span>
                                        <strong><?php _e('Improved Efficiency:', 'call-tracking-metrics'); ?></strong> <?php _e('Reduce call handling time and increase agent productivity with pre-populated caller data.', 'call-tracking-metrics'); ?>
                                    </li>
                                    <li class="flex items-start">
                                        <span class="mr-2">📈</span>
                                        <strong><?php _e('Advanced Analytics:', 'call-tracking-metrics'); ?></strong> <?php _e('Gain insights into call patterns, conversion rates, and customer journey tracking.', 'call-tracking-metrics'); ?>
                                    </li>
                                </ul>
                            </div>

                            <!-- How CTI Works Section -->
                            <div class="bg-gradient-to-r from-purple-50 to-pink-50 p-6 rounded-lg border border-purple-200">
                                <div class="flex items-center mb-4">
                                    <div class="bg-purple-100 p-2 rounded-lg mr-3">
                                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                        </svg>
                                    </div>
                                    <h4 class="text-lg font-semibold text-purple-700"><?php _e('🔄 How CTI Works', 'call-tracking-metrics'); ?></h4>
                                </div>
                                <p class="mb-4"><?php _e('When a visitor interacts with your website, CTI technology captures their behavior and creates a unique identifier. When that same visitor calls your business, the CTI system matches the call to the website session, providing your team with valuable context about the caller\'s journey and interests.', 'call-tracking-metrics'); ?></p>
                                
                                <div class="bg-white p-4 rounded-lg border border-purple-100">
                                    <h5 class="font-semibold text-purple-800 mb-3"><?php _e('📋 Typical CTI Workflow:', 'call-tracking-metrics'); ?></h5>
                                    <ol class="list-decimal pl-6 text-sm space-y-2">
                                        <li class="flex items-start">
                                            <span class="mr-2">🌐</span>
                                            <?php _e('Visitor browses your website and shows interest in specific products/services', 'call-tracking-metrics'); ?>
                                        </li>
                                        <li class="flex items-start">
                                            <span class="mr-2">🔢</span>
                                            <?php _e('CTI system assigns a unique tracking number to the visitor', 'call-tracking-metrics'); ?>
                                        </li>
                                        <li class="flex items-start">
                                            <span class="mr-2">📞</span>
                                            <?php _e('Visitor calls the tracking number from your website', 'call-tracking-metrics'); ?>
                                        </li>
                                        <li class="flex items-start">
                                            <span class="mr-2">⚡</span>
                                            <?php _e('CTI system instantly provides caller information to your phone system', 'call-tracking-metrics'); ?>
                                        </li>
                                        <li class="flex items-start">
                                            <span class="mr-2">👨‍💼</span>
                                            <?php _e('Agent receives call with full context about the visitor\'s website activity', 'call-tracking-metrics'); ?>
                                        </li>
                                    </ol>
                                </div>
                            </div>

                            <!-- Integration Capabilities Section -->
                            <div class="bg-gradient-to-r from-orange-50 to-yellow-50 p-6 rounded-lg border border-orange-200">
                                <div class="flex items-center mb-4">
                                    <div class="bg-orange-100 p-2 rounded-lg mr-3">
                                        <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                        </svg>
                                    </div>
                                    <h4 class="text-lg font-semibold text-orange-700"><?php _e('🔗 Integration Capabilities', 'call-tracking-metrics'); ?></h4>
                                </div>
                                <ul class="list-disc pl-6 mb-4 space-y-2">
                                    <li class="flex items-start">
                                        <span class="mr-2">☎️</span>
                                        <strong><?php _e('PBX Systems:', 'call-tracking-metrics'); ?></strong> <?php _e('Compatible with most modern PBX and VoIP systems including Avaya, Cisco, and Asterisk.', 'call-tracking-metrics'); ?>
                                    </li>
                                    <li class="flex items-start">
                                        <span class="mr-2">📊</span>
                                        <strong><?php _e('CRM Integration:', 'call-tracking-metrics'); ?></strong> <?php _e('Seamless integration with popular CRM platforms like Salesforce, HubSpot, and Zoho.', 'call-tracking-metrics'); ?>
                                    </li>
                                    <li class="flex items-start">
                                        <span class="mr-2">🎧</span>
                                        <strong><?php _e('Help Desk Systems:', 'call-tracking-metrics'); ?></strong> <?php _e('Connect with Zendesk, Freshdesk, and other customer support platforms.', 'call-tracking-metrics'); ?>
                                    </li>
                                    <li class="flex items-start">
                                        <span class="mr-2">🔧</span>
                                        <strong><?php _e('Custom APIs:', 'call-tracking-metrics'); ?></strong> <?php _e('Flexible API integration for custom business systems and workflows.', 'call-tracking-metrics'); ?>
                                    </li>
                                </ul>
                            </div>

                            <!-- Enterprise Features Section -->
                            <div class="bg-gradient-to-r from-red-50 to-pink-50 p-6 rounded-lg border border-red-200">
                                <div class="flex items-center mb-4">
                                    <div class="bg-red-100 p-2 rounded-lg mr-3">
                                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                        </svg>
                                    </div>
                                    <h4 class="text-lg font-semibold text-red-700"><?php _e('🏢 Enterprise Features', 'call-tracking-metrics'); ?></h4>
                                </div>
                                <p class="mb-4"><?php _e('CTI is designed for enterprise-level businesses that require advanced call management capabilities. Features include multi-location support, advanced reporting, custom integrations, and dedicated support for complex deployment scenarios.', 'call-tracking-metrics'); ?></p>
                            </div>

                            <!-- Important Note Section -->
                            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 p-6 rounded-lg border border-blue-200">
                                <div class="flex items-center mb-4">
                                    <div class="bg-blue-100 p-2 rounded-lg mr-3">
                                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.08 15.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                        </svg>
                                    </div>
                                    <h4 class="text-lg font-semibold text-blue-700"><?php _e('💡 Important Note', 'call-tracking-metrics'); ?></h4>
                                </div>
                                <p class="text-blue-800"><strong><?php _e('Note:', 'call-tracking-metrics'); ?></strong> <?php _e('CTI functionality requires enterprise-level CallTrackingMetrics accounts and may need additional setup with your phone system provider. Contact our support team to discuss your specific requirements and implementation options.', 'call-tracking-metrics'); ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="cti-subtab-panel hidden" data-subtab="setup">
                        <h3 class="text-xl font-semibold text-blue-700 mt-6 mb-2"><?php _e('CTI Setup', 'call-tracking-metrics'); ?></h3>
                        <ol class="list-decimal pl-6 mb-4">
                            <li><?php _e('Contact CallTrackingMetrics support to enable CTI for your account.', 'call-tracking-metrics'); ?></li>
                            <li><?php _e('Configure your phone system to connect with the CTM API.', 'call-tracking-metrics'); ?></li>
                            <li><?php _e('Follow the integration guide provided by support for your specific PBX or VoIP system.', 'call-tracking-metrics'); ?></li>
                        </ol>
                    </div>
                    <div class="cti-subtab-panel hidden" data-subtab="features">
                        <h3 class="text-xl font-semibold text-blue-700 mt-6 mb-2"><?php _e('CTI Features', 'call-tracking-metrics'); ?></h3>
                        <ul class="list-disc pl-6 mb-4">
                            <li><?php _e('Real-time call tracking and analytics', 'call-tracking-metrics'); ?></li>
                            <li><?php _e('Screen pops with caller information', 'call-tracking-metrics'); ?></li>
                            <li><?php _e('Automated call logging and workflow triggers', 'call-tracking-metrics'); ?></li>
                            <li><?php _e('Integration with CRM and helpdesk platforms', 'call-tracking-metrics'); ?></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>