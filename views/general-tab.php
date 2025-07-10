<?php
// General tab view
?>
<form method="post" action="options.php" class="space-y-6">
    <?php settings_fields('call-tracking-metrics'); ?>
    <?php do_settings_sections('call-tracking-metrics'); ?>
    
    <?php if ($apiStatus !== 'connected'): ?>
        <!-- API Connection Only View (Not Connected) -->
        <div class="max-w-4xl mx-auto">
            <div class="bg-white p-8 rounded-lg shadow-lg border border-gray-200">
                <div class="text-center mb-6">
                    <div class="flex items-center justify-center mb-4">
                        <div class="bg-blue-100 p-3 rounded-full">
                            <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"></path>
                            </svg>
                        </div>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-800 mb-2">Connect to Call Tracking Metrics</h2>
                    <p class="text-gray-600">Enter your API credentials to get started with CTM integration</p>
                </div>
                
                <div class="bg-blue-50 border-l-4 border-blue-400 p-4 rounded mb-6 flex items-center gap-4">
                    <span class="font-semibold">API Connection Status:</span>
                    <?php if ($apiStatus === 'not_connected'): ?>
                        <span class="bg-red-100 text-red-700 px-3 py-1 rounded">Not Connected</span>
                    <?php else: ?>
                        <span class="bg-gray-100 text-gray-700 px-3 py-1 rounded">Not Tested</span>
                    <?php endif; ?>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block mb-2 text-gray-700 font-medium">API Key</label>
                        <div class="relative">
                            <input type="password" id="ctm_api_key" name="ctm_api_key" value="<?= esc_attr($apiKey) ?>" class="block w-full rounded border-gray-300 focus:ring-blue-500 focus:border-blue-500 pr-10" placeholder="Enter your API key" />
                            <button type="button" tabindex="-1" onclick="let f=document.getElementById('ctm_api_key');f.type=f.type==='password'?'text':'password';this.innerHTML=f.type==='password'?'<svg class=\'w-5 h-5\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M15 12a3 3 0 11-6 0 3 3 0 016 0z\'/><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z\'/></svg>':'<svg class=\'w-5 h-5\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a9.956 9.956 0 012.223-3.592m3.31-2.687A9.956 9.956 0 0112 5c4.477 0 8.268 2.943 9.542 7a9.956 9.956 0 01-4.043 5.306M15 12a3 3 0 11-6 0 3 3 0 016 0z\'/><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M3 3l18 18\'/></svg>';" class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-blue-600 focus:outline-none">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </button>
                        </div>
                    </div>
                    <div>
                        <label class="block mb-2 text-gray-700 font-medium">API Secret</label>
                        <div class="relative">
                            <input type="password" id="ctm_api_secret" name="ctm_api_secret" value="<?= esc_attr($apiSecret) ?>" class="block w-full rounded border-gray-300 focus:ring-blue-500 focus:border-blue-500 pr-10" placeholder="Enter your API secret" />
                            <button type="button" tabindex="-1" onclick="let f=document.getElementById('ctm_api_secret');f.type=f.type==='password'?'text':'password';this.innerHTML=f.type==='password'?'<svg class=\'w-5 h-5\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M15 12a3 3 0 11-6 0 3 3 0 016 0z\'/><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z\'/></svg>':'<svg class=\'w-5 h-5\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a9.956 9.956 0 012.223-3.592m3.31-2.687A9.956 9.956 0 0112 5c4.477 0 8.268 2.943 9.542 7a9.956 9.956 0 01-4.043 5.306M15 12a3 3 0 11-6 0 3 3 0 016 0z\'/><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M3 3l18 18\'/></svg>';" class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-blue-600 focus:outline-none">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="text-center mb-6">
                    <!-- Test API Connection Button -->
                    <button type="button" id="test-api-btn" onclick="testApiConnection()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold px-8 py-3 rounded-lg shadow-lg transition disabled:opacity-50 disabled:cursor-not-allowed">
                        <span id="test-btn-text">Test API Connection</span>
                        <span id="test-btn-spinner" class="hidden ml-2">
                            <svg class="animate-spin h-4 w-4 inline" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </span>
                    </button>
                </div>
                
                <!-- Real-time API Test Logs -->
                <div id="api-test-logs" class="hidden mt-6 p-6 bg-gray-50 border border-gray-200 rounded-lg max-h-96 overflow-y-auto">
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="font-semibold text-gray-700 flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            API Test Results
                        </h4>
                        <div class="flex gap-2">
                            <span id="test-duration" class="text-xs text-gray-500 bg-gray-200 px-2 py-1 rounded hidden"></span>
                            <button type="button" onclick="clearTestLogs()" class="text-xs text-gray-500 hover:text-gray-700 bg-gray-200 hover:bg-gray-300 px-2 py-1 rounded transition">Clear</button>
                        </div>
                    </div>
                    
                    <!-- Progress Bar -->
                    <div id="test-progress" class="mb-4 hidden">
                        <div class="flex justify-between text-xs text-gray-600 mb-1">
                            <span>Testing Progress</span>
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
                            Account Summary
                        </h5>
                        <div id="account-details" class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs"></div>
                    </div>
                    
                    <!-- Technical Details (collapsible) -->
                    <div id="technical-details" class="hidden mt-4">
                        <button type="button" onclick="toggleTechnicalDetails()" class="flex items-center gap-2 text-xs text-gray-600 hover:text-gray-800 transition">
                            <svg id="tech-details-icon" class="w-3 h-3 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                            View Technical Details
                        </button>
                        <div id="tech-details-content" class="hidden mt-2 p-3 bg-gray-100 rounded text-xs font-mono overflow-x-auto"></div>
                    </div>
                </div>
                
                <div class="mt-6 text-center">
                    <button type="submit" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded shadow font-semibold transition">Save API Credentials</button>
                </div>
            </div>
        </div>
        
    <?php else: ?>
        <!-- Full Settings View (Connected) -->
        <div class="mb-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-2">
                <div class="bg-blue-50 border-l-4 border-blue-400 p-4 rounded flex items-center gap-4">
                    <span class="font-semibold">API Connection:</span>
                    <span class="bg-green-100 text-green-700 px-2 py-1 rounded">Connected</span>
                </div>
                <div class="bg-blue-50 border-l-4 border-blue-400 p-4 rounded flex items-center gap-4">
                    <span class="font-semibold">Integrations:</span>
                    <?php if ($cf7Enabled): ?><span class="bg-green-100 text-green-700 px-2 py-1 rounded">CF7</span><?php else: ?><span class="bg-gray-100 text-gray-700 px-2 py-1 rounded">CF7</span><?php endif; ?>
                    <?php if ($gfEnabled): ?><span class="bg-green-100 text-green-700 px-2 py-1 rounded ml-2">GF</span><?php else: ?><span class="bg-gray-100 text-gray-700 px-2 py-1 rounded ml-2">GF</span><?php endif; ?>
                </div>
                <div class="bg-blue-50 border-l-4 border-blue-400 p-4 rounded flex items-center gap-4">
                    <span class="font-semibold">Dashboard Widget:</span>
                    <?php if ($dashboardEnabled): ?><span class="bg-green-100 text-green-700 px-2 py-1 rounded">Enabled</span><?php else: ?><span class="bg-gray-100 text-gray-700 px-2 py-1 rounded">Disabled</span><?php endif; ?>
                </div>
                <div class="bg-blue-50 border-l-4 border-blue-400 p-4 rounded flex items-center gap-4">
                    <span class="font-semibold">Debug Mode:</span>
                    <?php if ($debugEnabled): ?><span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded">Enabled</span><?php else: ?><span class="bg-gray-100 text-gray-700 px-2 py-1 rounded">Disabled</span><?php endif; ?>
                </div>
            </div>

        </div>
        <!-- Settings Form Fields -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white p-6 rounded-lg shadow border border-gray-200">
                <h2 class="text-xl font-semibold mb-4 text-gray-700">Account</h2>
                <label class="block mb-2 text-gray-600 font-medium">API Key</label>
                <div class="relative mb-4">
                    <input type="password" id="ctm_api_key" name="ctm_api_key" value="<?= esc_attr($apiKey) ?>" class="block w-full rounded border-gray-300 focus:ring-blue-500 focus:border-blue-500 pr-10" />
                    <button type="button" tabindex="-1" onclick="let f=document.getElementById('ctm_api_key');f.type=f.type==='password'?'text':'password';this.innerHTML=f.type==='password'?'<svg class=\'w-5 h-5\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M15 12a3 3 0 11-6 0 3 3 0 016 0z\'/><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z\'/></svg>':'<svg class=\'w-5 h-5\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a9.956 9.956 0 012.223-3.592m3.31-2.687A9.956 9.956 0 0112 5c4.477 0 8.268 2.943 9.542 7a9.956 9.956 0 01-4.043 5.306M15 12a3 3 0 11-6 0 3 3 0 016 0z\'/><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M3 3l18 18\'/></svg>';" class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-blue-600 focus:outline-none">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </button>
                </div>
                <label class="block mb-2 text-gray-600 font-medium">API Secret</label>
                <div class="relative mb-4">
                    <input type="password" id="ctm_api_secret" name="ctm_api_secret" value="<?= esc_attr($apiSecret) ?>" class="block w-full rounded border-gray-300 focus:ring-blue-500 focus:border-blue-500 pr-10" />
                    <button type="button" tabindex="-1" onclick="let f=document.getElementById('ctm_api_secret');f.type=f.type==='password'?'text':'password';this.innerHTML=f.type==='password'?'<svg class=\'w-5 h-5\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M15 12a3 3 0 11-6 0 3 3 0 016 0z\'/><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z\'/></svg>':'<svg class=\'w-5 h-5\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a9.956 9.956 0 012.223-3.592m3.31-2.687A9.956 9.956 0 0112 5c4.477 0 8.268 2.943 9.542 7a9.956 9.956 0 01-4.043 5.306M15 12a3 3 0 11-6 0 3 3 0 016 0z\'/><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M3 3l18 18\'/></svg>';" class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-blue-600 focus:outline-none">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </button>
                </div>
                <!-- Test API Connection Button -->
                <button type="button" id="test-api-btn" onclick="testApiConnection()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold px-4 py-2 rounded shadow transition mb-4 disabled:opacity-50 disabled:cursor-not-allowed">
                    <span id="test-btn-text">Test API Connection</span>
                    <span id="test-btn-spinner" class="hidden ml-2">
                        <svg class="animate-spin h-4 w-4 inline" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </span>
                </button>
                
                <!-- Real-time API Test Logs -->
                <div id="api-test-logs" class="hidden mt-4 p-4 bg-gray-50 border border-gray-200 rounded max-h-96 overflow-y-auto">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="font-semibold text-gray-700 flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            API Test Results
                        </h4>
                        <div class="flex gap-2">
                            <span id="test-duration" class="text-xs text-gray-500 bg-gray-200 px-2 py-1 rounded hidden"></span>
                            <button type="button" onclick="clearTestLogs()" class="text-xs text-gray-500 hover:text-gray-700 bg-gray-200 hover:bg-gray-300 px-2 py-1 rounded transition">Clear</button>
                        </div>
                    </div>
                    
                    <!-- Progress Bar -->
                    <div id="test-progress" class="mb-4 hidden">
                        <div class="flex justify-between text-xs text-gray-600 mb-1">
                            <span>Testing Progress</span>
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
                            Account Summary
                        </h5>
                        <div id="account-details" class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs"></div>
                    </div>
                    
                    <!-- Technical Details (collapsible) -->
                    <div id="technical-details" class="hidden mt-4">
                        <button type="button" onclick="toggleTechnicalDetails()" class="flex items-center gap-2 text-xs text-gray-600 hover:text-gray-800 transition">
                            <svg id="tech-details-icon" class="w-3 h-3 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                            View Technical Details
                        </button>
                        <div id="tech-details-content" class="hidden mt-2 p-3 bg-gray-100 rounded text-xs font-mono overflow-x-auto"></div>
                    </div>
                </div>
            </div>
            <div class="bg-white p-6 rounded-lg shadow border border-gray-200">
                <h2 class="text-xl font-semibold mb-4 text-gray-700">Tracking</h2>
                <label class="flex items-center mb-4"><input type="checkbox" name="ctm_api_tracking_enabled" value="1"<?= checked($trackingEnabled, 1, false) ?> class="mr-2 rounded border-gray-300 focus:ring-blue-500" />Enable Tracking</label>
                <label class="block mb-2 text-gray-600 font-medium">Tracking Script</label>
                <textarea name="call_track_account_script" rows="3" class="block w-full rounded border-gray-300 focus:ring-blue-500 focus:border-blue-500 mb-4"><?= esc_textarea(get_option('call_track_account_script')) ?></textarea>
            </div>
            <div class="bg-white p-6 rounded-lg shadow border border-gray-200">
                <h2 class="text-xl font-semibold mb-4 text-gray-700">Integrations</h2>
                
                <!-- Contact Form 7 Integration -->
                <label class="flex items-center mb-2 <?= !class_exists('WPCF7_ContactForm') ? 'opacity-50 cursor-not-allowed' : '' ?>">
                    <input type="checkbox" 
                           name="ctm_api_cf7_enabled" 
                           value="1"
                           <?= checked($cf7Enabled, 1, false) ?> 
                           <?= !class_exists('WPCF7_ContactForm') ? 'disabled' : '' ?>
                           class="mr-2 rounded border-gray-300 focus:ring-blue-500 <?= !class_exists('WPCF7_ContactForm') ? 'opacity-50 cursor-not-allowed' : '' ?>" />
                    <span class="<?= !class_exists('WPCF7_ContactForm') ? 'text-gray-400' : '' ?>">Enable Contact Form 7 Integration</span>
                    <?php if (!class_exists('WPCF7_ContactForm')): ?>
                        <span class="ml-2 text-xs text-red-600 font-medium">(Plugin required)</span>
                    <?php endif; ?>
                </label>
                
                <!-- Always show install link -->
                <?php if (!class_exists('WPCF7_ContactForm')): ?>
                    <div class="mb-3">
                        <a href="<?= esc_url(admin_url('plugin-install.php?s=contact+form+7&tab=search&type=term')) ?>" class="text-blue-600 hover:text-blue-800 text-sm underline transition">Install Contact Form 7 →</a>
                    </div>
                <?php endif; ?>
                
                <!-- Dismissible warning banner -->
                <?php if (!class_exists('WPCF7_ContactForm') && !get_option('ctm_cf7_notice_dismissed', false)): ?>
                    <div id="cf7-notice" class="bg-yellow-50 border-l-4 border-yellow-400 text-yellow-800 p-2 rounded flex items-center justify-between gap-2 mb-4 mt-2 text-sm">
                        <div class="flex items-center gap-2">
                            <span class="font-semibold">Contact Form 7 is not installed or activated.</span>
                            <a href="<?= esc_url(admin_url('plugin-install.php?s=contact+form+7&tab=search&type=term')) ?>" class="ml-2 bg-blue-600 hover:bg-blue-700 hover:text-white! text-white px-4 py-2 rounded shadow transition text-sm font-medium">Install Contact Form 7</a>
                        </div>
                        <button type="button" onclick="dismissNotice('cf7')" class="text-yellow-600 hover:text-yellow-800 ml-2 p-1 rounded hover:bg-yellow-100 transition" title="I don't use this plugin">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                <?php endif; ?>
                
                <!-- Gravity Forms Integration -->
                <label class="flex items-center mb-2 <?= !class_exists('GFAPI') ? 'opacity-50 cursor-not-allowed' : '' ?>">
                    <input type="checkbox" 
                           name="ctm_api_gf_enabled" 
                           value="1"
                           <?= checked($gfEnabled, 1, false) ?> 
                           <?= !class_exists('GFAPI') ? 'disabled' : '' ?>
                           class="mr-2 rounded border-gray-300 focus:ring-blue-500 <?= !class_exists('GFAPI') ? 'opacity-50 cursor-not-allowed' : '' ?>" />
                    <span class="<?= !class_exists('GFAPI') ? 'text-gray-400' : '' ?>">Enable Gravity Forms Integration</span>
                    <?php if (!class_exists('GFAPI')): ?>
                        <span class="ml-2 text-xs text-red-600 font-medium">(Plugin required)</span>
                    <?php endif; ?>
                </label>
                
                <!-- Always show install link -->
                <?php if (!class_exists('GFAPI')): ?>
                    <div class="mb-3">
                        <a href="https://www.gravityforms.com/" target="_blank" rel="noopener" class="text-blue-600 hover:text-blue-800 text-sm underline transition">Get Gravity Forms →</a>
                    </div>
                <?php endif; ?>
                
                <!-- Dismissible warning banner -->
                <?php if (!class_exists('GFAPI') && !get_option('ctm_gf_notice_dismissed', false)): ?>
                    <div id="gf-notice" class="bg-yellow-50 border-l-4 border-yellow-400 text-yellow-800 p-2 rounded flex items-center justify-between gap-2 mb-2 mt-2 text-sm">
                        <div class="flex items-center gap-2">
                            <span class="font-semibold">Gravity Forms is not installed or activated.</span>
                            <a href="https://www.gravityforms.com/" target="_blank" rel="noopener" class="ml-2 bg-blue-600 hover:bg-blue-700 hover:text-white! text-white px-4 py-2 rounded shadow transition text-sm font-medium">Get Gravity Forms</a>
                        </div>
                        <button type="button" onclick="dismissNotice('gf')" class="text-yellow-600 hover:text-yellow-800 ml-2 p-1 rounded hover:bg-yellow-100 transition" title="I don't use this plugin">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                <?php endif; ?>
            </div>
            <div class="bg-white p-6 rounded-lg shadow border border-gray-200">
                <h2 class="text-xl font-semibold mb-4 text-gray-700">Dashboard</h2>
                <label class="flex items-center mb-2"><input type="checkbox" name="ctm_api_dashboard_enabled" value="1"<?= checked($dashboardEnabled, 1, false) ?> class="mr-2 rounded border-gray-300 focus:ring-blue-500" />Enable Dashboard Widget</label>
            </div>
        </div>
        <div class="mt-8">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded shadow font-semibold transition">Save Settings</button>
        </div>
    <?php endif; ?>
</form>

<script>
function dismissNotice(type) {
    // Hide the notice immediately
    const noticeId = type + '-notice';
    const notice = document.getElementById(noticeId);
    if (notice) {
        notice.style.transition = 'opacity 0.3s ease';
        notice.style.opacity = '0';
        setTimeout(() => {
            notice.style.display = 'none';
        }, 300);
    }
    
    // Send AJAX request to save dismiss preference
    const formData = new FormData();
    formData.append('action', 'ctm_dismiss_notice');
    formData.append('notice_type', type);
    formData.append('nonce', '<?= wp_create_nonce('ctm_dismiss_notice') ?>');
    
    fetch('<?= admin_url('admin-ajax.php') ?>', {
        method: 'POST',
        body: formData
    }).catch(error => {
        console.error('Error dismissing notice:', error);
    });
}

 function testApiConnection() {
     const apiKey = document.getElementById('ctm_api_key').value;
     const apiSecret = document.getElementById('ctm_api_secret').value;
     const testBtn = document.getElementById('test-api-btn');
     const testBtnText = document.getElementById('test-btn-text');
     const testBtnSpinner = document.getElementById('test-btn-spinner');
     const apiTestLogs = document.getElementById('api-test-logs');
     const apiLogContent = document.getElementById('api-log-content');
     const testDuration = document.getElementById('test-duration');
     const testProgress = document.getElementById('test-progress');
     const progressBar = document.getElementById('progress-bar');
     const progressText = document.getElementById('progress-text');
     const accountSummary = document.getElementById('account-summary');
     const accountDetails = document.getElementById('account-details');
     const technicalDetails = document.getElementById('technical-details');
     const techDetailsContent = document.getElementById('tech-details-content');
 
     if (!apiKey || !apiSecret) {
         alert('Please enter both API Key and API Secret.');
         return;
     }
 
     // Initialize test state
     const startTime = Date.now();
     let currentStep = 0;
     const totalSteps = 5;
     
     testBtn.disabled = true;
     testBtnText.classList.add('hidden');
     testBtnSpinner.classList.remove('hidden');
     apiTestLogs.classList.remove('hidden');
     apiLogContent.innerHTML = '';
     testDuration.classList.add('hidden');
     testProgress.classList.remove('hidden');
     accountSummary.classList.add('hidden');
     technicalDetails.classList.add('hidden');
     
     // Step 1: Validation
     updateProgress(++currentStep, totalSteps, 'Validating credentials...');
     appendLog('info', 'Starting API connection test...');
     appendLog('info', `API Key: ${apiKey.substring(0, 8)}...${apiKey.substring(apiKey.length - 4)}`);
     appendLog('info', `Target: https://api.calltrackingmetrics.com`);
     
     setTimeout(() => {
         // Step 2: Connection
         updateProgress(++currentStep, totalSteps, 'Establishing connection...');
         appendLog('info', 'Connecting to CTM API endpoint...');
         appendLog('info', 'Endpoint: /api/v1/accounts/current.json');
         
         const requestStart = Date.now();
         
         fetch('<?= admin_url('admin-ajax.php') ?>', {
             method: 'POST',
             headers: {
                 'Content-Type': 'application/x-www-form-urlencoded',
             },
             body: 'action=ctm_test_api_connection&api_key=' + encodeURIComponent(apiKey) + '&api_secret=' + encodeURIComponent(apiSecret) + '&nonce=<?= wp_create_nonce('ctm_test_api_connection') ?>'
         })
         .then(response => {
             const requestTime = Date.now() - requestStart;
             
             // Step 3: Response
             updateProgress(++currentStep, totalSteps, 'Processing response...');
             appendLog('info', `Response received in ${requestTime}ms`);
             appendLog('info', `HTTP Status: ${response.status} ${response.statusText}`);
             
             return response.json();
         })
         .then(data => {
             // Step 4: Analysis
             updateProgress(++currentStep, totalSteps, 'Analyzing account data...');
             
             const totalTime = Date.now() - startTime;
             testDuration.textContent = `${totalTime}ms`;
             testDuration.classList.remove('hidden');
             
             testBtn.disabled = false;
             testBtnText.classList.remove('hidden');
             testBtnSpinner.classList.add('hidden');
 
             if (data.success) {
                 // Step 5: Success
                 updateProgress(++currentStep, totalSteps, 'Connection successful!');
                 
                 appendLog('success', '✓ API Connection Successful!');
                 appendLog('success', `Total test duration: ${totalTime}ms`);
                 
                 // Show performance metrics
                 if (data.performance) {
                     displayPerformanceMetrics(data.performance, data.connection_quality);
                 }
                 
                 // Show detailed account information
                 if (data.account_info && data.account_info.account) {
                     displayAccountSummary(data.account_info.account, data.account_details?.account, data.capabilities);
                 }
                 
                 // Show technical details
                 displayTechnicalDetails(data);
                 
                 appendLog('success', 'All tests completed successfully!');
                 
                 // Auto-refresh countdown
                 let countdown = 3;
                 const countdownInterval = setInterval(() => {
                     appendLog('info', `Refreshing page in ${countdown} seconds...`);
                     countdown--;
                     if (countdown < 0) {
                         clearInterval(countdownInterval);
                         window.location.reload();
                     }
                 }, 1000);
             } else {
                 updateProgress(totalSteps, totalSteps, 'Connection failed');
                 progressBar.classList.remove('bg-blue-600');
                 progressBar.classList.add('bg-red-600');
                 
                 appendLog('error', '✗ API Connection Failed');
                 appendLog('error', `Total test duration: ${totalTime}ms`);
                 
                 if (data.message) appendLog('error', `Error: ${data.message}`);
                 if (data.details && Array.isArray(data.details)) {
                     data.details.forEach(detail => appendLog('warning', `• ${detail}`));
                 }
                 
                 // Show technical details for debugging
                 displayTechnicalDetails(data);
                 
                 appendLog('warning', 'Please check your API credentials and try again.');
             }
         })
         .catch(error => {
             const totalTime = Date.now() - startTime;
             testDuration.textContent = `${totalTime}ms`;
             testDuration.classList.remove('hidden');
             
             updateProgress(totalSteps, totalSteps, 'Network error');
             progressBar.classList.remove('bg-blue-600');
             progressBar.classList.add('bg-red-600');
             
             testBtn.disabled = false;
             testBtnText.classList.remove('hidden');
             testBtnSpinner.classList.add('hidden');
             
             appendLog('error', '✗ Network Error: ' + error.message);
             appendLog('error', `Total test duration: ${totalTime}ms`);
             appendLog('warning', 'Please check your internet connection and try again.');
         });
     }, 800); // Small delay to show initial progress
 }
 
  function updateProgress(step, total, message) {
     const progressBar = document.getElementById('progress-bar');
     const progressText = document.getElementById('progress-text');
     
     const percentage = Math.round((step / total) * 100);
     progressBar.style.width = percentage + '%';
     progressText.textContent = `${percentage}% - ${message}`;
 }

 function appendLog(type, message) {
     const apiLogContent = document.getElementById('api-log-content');
     const timestamp = new Date().toLocaleTimeString();
     let colorClass = 'text-gray-600';
     let bgClass = 'bg-gray-50';
     let icon = '•';
     
     switch(type) {
         case 'success':
             colorClass = 'text-green-700';
             bgClass = 'bg-green-50';
             icon = '✓';
             break;
         case 'error':
             colorClass = 'text-red-700';
             bgClass = 'bg-red-50';
             icon = '✗';
             break;
         case 'warning':
             colorClass = 'text-yellow-700';
             bgClass = 'bg-yellow-50';
             icon = '⚠';
             break;
         case 'info':
         default:
             colorClass = 'text-blue-700';
             bgClass = 'bg-blue-50';
             icon = 'ⓘ';
             break;
     }
     
     const logEntry = document.createElement('div');
     logEntry.className = `${colorClass} ${bgClass} p-2 rounded border-l-2 border-current flex gap-2 items-start`;
     logEntry.innerHTML = `
         <span class="text-gray-400 text-xs font-mono mt-0.5">[${timestamp}]</span>
         <span class="font-bold mt-0.5">${icon}</span>
         <span class="flex-1 font-medium">${message}</span>
     `;
     
     apiLogContent.appendChild(logEntry);
     apiLogContent.scrollTop = apiLogContent.scrollHeight;
 }

 function displayPerformanceMetrics(performance, quality) {
     if (quality) {
         const qualityColor = quality.color === 'green' ? 'text-green-600' : 
                             quality.color === 'blue' ? 'text-blue-600' :
                             quality.color === 'yellow' ? 'text-yellow-600' : 'text-red-600';
         
         appendLog('info', `${quality.description}`);
         appendLog('info', `API Response: ${performance.api_response_time}ms`);
         
         if (performance.details_response_time) {
             appendLog('info', `Details Response: ${performance.details_response_time}ms`);
         }
         
         if (performance.network_overhead > 0) {
             appendLog('info', `Network Overhead: ${performance.network_overhead.toFixed(1)}ms`);
         }
     }
 }

 function displayAccountSummary(account, details, capabilities) {
     const accountSummary = document.getElementById('account-summary');
     const accountDetails = document.getElementById('account-details');
     
     accountSummary.classList.remove('hidden');
     
     let summaryHTML = '';
     
     // Basic account info
     summaryHTML += `<div class="bg-white p-2 rounded border"><strong>Name:</strong> ${account.name || 'N/A'}</div>`;
     summaryHTML += `<div class="bg-white p-2 rounded border"><strong>Account ID:</strong> <code class="bg-gray-100 px-1 rounded">${account.id || 'N/A'}</code></div>`;
     
     if (account.email) {
         summaryHTML += `<div class="bg-white p-2 rounded border"><strong>Email:</strong> ${account.email}</div>`;
     }
     
     // API Capabilities
     if (capabilities) {
         const accessIcon = capabilities.account_access ? '✓' : '✗';
         const detailsIcon = capabilities.details_access ? '✓' : '✗';
         summaryHTML += `<div class="bg-white p-2 rounded border"><strong>API Access:</strong> <span class="text-green-600">${accessIcon}</span> Account, <span class="${capabilities.details_access ? 'text-green-600' : 'text-yellow-600'}">${detailsIcon}</span> Details</div>`;
         summaryHTML += `<div class="bg-white p-2 rounded border"><strong>API Version:</strong> ${capabilities.api_version}</div>`;
     }
     
     // Additional details if available
     if (details) {
         if (details.status) {
             const statusColor = details.status === 'active' ? 'text-green-600' : 'text-yellow-600';
             summaryHTML += `<div class="bg-white p-2 rounded border"><strong>Status:</strong> <span class="${statusColor} font-semibold">${details.status}</span></div>`;
         }
         if (details.timezone) {
             summaryHTML += `<div class="bg-white p-2 rounded border"><strong>Timezone:</strong> ${details.timezone}</div>`;
         }
         if (details.created_at) {
             const date = new Date(details.created_at).toLocaleDateString();
             summaryHTML += `<div class="bg-white p-2 rounded border"><strong>Created:</strong> ${date}</div>`;
         }
         if (details.phone) {
             summaryHTML += `<div class="bg-white p-2 rounded border"><strong>Phone:</strong> ${details.phone}</div>`;
         }
         if (details.website) {
             summaryHTML += `<div class="bg-white p-2 rounded border"><strong>Website:</strong> <a href="${details.website}" target="_blank" class="text-blue-600 underline">${details.website}</a></div>`;
         }
         if (details.plan || details.subscription) {
             summaryHTML += `<div class="bg-white p-2 rounded border"><strong>Plan:</strong> ${details.plan || details.subscription || 'Not specified'}</div>`;
         }
     }
     
     accountDetails.innerHTML = summaryHTML;
     
     appendLog('success', `Account verified: ${account.name} (${account.id})`);
 }

 function displayTechnicalDetails(data) {
     const technicalDetails = document.getElementById('technical-details');
     const techDetailsContent = document.getElementById('tech-details-content');
     
     technicalDetails.classList.remove('hidden');
     
     let detailsHTML = '';
     
     // Metadata Section
     if (data.metadata) {
         detailsHTML += `
             <div class="mb-4">
                 <h6 class="font-semibold text-gray-700 mb-2 flex items-center gap-1">
                     <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                     </svg>
                     Request Metadata
                 </h6>
                 <div class="grid grid-cols-2 gap-2 text-xs">
                     <div><strong>Request ID:</strong> <code class="bg-gray-200 px-1 rounded">${data.metadata.request_id?.substring(0, 8) || 'N/A'}</code></div>
                     <div><strong>Timestamp:</strong> ${data.metadata.timestamp || 'N/A'}</div>
                     <div><strong>WordPress:</strong> ${data.metadata.wordpress_version || 'N/A'}</div>
                     <div><strong>PHP:</strong> ${data.metadata.php_version || 'N/A'}</div>
                     <div><strong>Plugin:</strong> ${data.metadata.plugin_version || 'N/A'}</div>
                     <div><strong>Auth Method:</strong> ${data.metadata.auth_method || 'N/A'}</div>
                 </div>
             </div>
         `;
     }
     
     // Performance Section
     if (data.performance) {
         detailsHTML += `
             <div class="mb-4">
                 <h6 class="font-semibold text-gray-700 mb-2 flex items-center gap-1">
                     <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                     </svg>
                     Performance Metrics
                 </h6>
                 <div class="grid grid-cols-2 gap-2 text-xs">
                     <div><strong>Total Time:</strong> ${data.performance.total_execution_time}ms</div>
                     <div><strong>API Response:</strong> ${data.performance.api_response_time}ms</div>
                     <div><strong>Details Response:</strong> ${data.performance.details_response_time || 'N/A'}ms</div>
                     <div><strong>Network Overhead:</strong> ${data.performance.network_overhead?.toFixed(1) || 'N/A'}ms</div>
                 </div>
             </div>
         `;
     }
     
     // API Endpoints Section
     if (data.metadata) {
         detailsHTML += `
             <div class="mb-4">
                 <h6 class="font-semibold text-gray-700 mb-2 flex items-center gap-1">
                     <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                     </svg>
                     API Endpoints
                 </h6>
                 <div class="space-y-1 text-xs">
                     <div><strong>Base URL:</strong> <code class="bg-gray-200 px-1 rounded">${data.metadata.api_endpoint || 'N/A'}</code></div>
                     <div><strong>Account:</strong> <code class="bg-gray-200 px-1 rounded">${data.metadata.account_endpoint || 'N/A'}</code></div>
                     ${data.metadata.details_endpoint ? `<div><strong>Details:</strong> <code class="bg-gray-200 px-1 rounded">${data.metadata.details_endpoint}</code></div>` : ''}
                 </div>
             </div>
         `;
     }
     
     // Connection Quality
     if (data.connection_quality) {
         const quality = data.connection_quality;
         const qualityBadgeColor = quality.color === 'green' ? 'bg-green-100 text-green-800' :
                                   quality.color === 'blue' ? 'bg-blue-100 text-blue-800' :
                                   quality.color === 'yellow' ? 'bg-yellow-100 text-yellow-800' : 
                                   'bg-red-100 text-red-800';
         
         detailsHTML += `
             <div class="mb-4">
                 <h6 class="font-semibold text-gray-700 mb-2 flex items-center gap-1">
                     <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                     </svg>
                     Connection Quality
                 </h6>
                 <div class="flex items-center gap-2 text-xs">
                     <span class="px-2 py-1 rounded ${qualityBadgeColor} font-semibold">${quality.rating.toUpperCase()}</span>
                     <span>${quality.total_time}ms total response time</span>
                 </div>
             </div>
         `;
     }
     
     // Raw Response Data (collapsible)
     const sanitizedData = JSON.parse(JSON.stringify(data));
     
     // Remove sensitive information
     if (sanitizedData.account_info?.account) {
         delete sanitizedData.account_info.account.api_key;
         delete sanitizedData.account_info.account.api_secret;
     }
     
     const jsonString = JSON.stringify(sanitizedData, null, 2);
     
     detailsHTML += `
         <div class="mb-2">
             <button type="button" onclick="toggleRawData()" class="flex items-center gap-1 text-xs text-gray-600 hover:text-gray-800 transition">
                 <svg id="raw-data-icon" class="w-3 h-3 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                 </svg>
                 Raw API Response (JSON)
             </button>
             <div id="raw-data-content" class="hidden mt-2 p-2 bg-gray-50 rounded border">
                 <div class="mb-1 text-gray-600 text-xs"><strong>Complete API Response:</strong> (sensitive data removed)</div>
                 <pre class="whitespace-pre-wrap text-xs overflow-x-auto">${escapeHtml(jsonString)}</pre>
             </div>
         </div>
     `;
     
     techDetailsContent.innerHTML = detailsHTML;
 }

 function escapeHtml(text) {
     const div = document.createElement('div');
     div.textContent = text;
     return div.innerHTML;
 }
 
 function clearTestLogs() {
     document.getElementById('api-log-content').innerHTML = '';
     document.getElementById('test-progress').classList.add('hidden');
     document.getElementById('account-summary').classList.add('hidden');
     document.getElementById('technical-details').classList.add('hidden');
     document.getElementById('test-duration').classList.add('hidden');
     
     // Reset progress bar
     const progressBar = document.getElementById('progress-bar');
     progressBar.style.width = '0%';
     progressBar.classList.remove('bg-red-600');
     progressBar.classList.add('bg-blue-600');
 }
 
 function toggleTechnicalDetails() {
     const techDetailsContent = document.getElementById('tech-details-content');
     const techDetailsIcon = document.getElementById('tech-details-icon');
     
     techDetailsContent.classList.toggle('hidden');
     techDetailsIcon.classList.toggle('rotate-90');
 }
 
 function toggleRawData() {
     const rawDataContent = document.getElementById('raw-data-content');
     const rawDataIcon = document.getElementById('raw-data-icon');
     
     rawDataContent.classList.toggle('hidden');
     rawDataIcon.classList.toggle('rotate-90');
 }
</script> 