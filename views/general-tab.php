<?php
// General tab view
?>
<form method="post" action="options.php" class="space-y-6">
    <?php settings_fields('call-tracking-metrics'); ?>
    <?php do_settings_sections('call-tracking-metrics'); ?>
    <div class="mb-8">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-2">
            <div class="bg-blue-50 border-l-4 border-blue-400 p-4 rounded flex items-center gap-4">
                <span class="font-semibold">API Connection:</span>
                <?php if ($apiStatus === 'connected'): ?>
                    <span class="bg-green-100 text-green-700 px-2 py-1 rounded">Connected</span>
                <?php elseif ($apiStatus === 'not_connected'): ?>
                    <span class="bg-red-100 text-red-700 px-2 py-1 rounded">Not Connected</span>
                <?php else: ?>
                    <span class="bg-gray-100 text-gray-700 px-2 py-1 rounded">Not Tested</span>
                <?php endif; ?>
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
        <?php if ($apiStatus === 'connected' && $accountInfo && isset($accountInfo['account'])): ?>
            <?php $acct = $accountInfo['account']; ?>
            <div class="bg-white border border-blue-200 rounded p-4 mt-2">
                <div class="flex items-center gap-4 mb-2">
                    <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 15c2.5 0 4.847.655 6.879 1.804M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                    <span class="font-semibold text-blue-800">CTM Account:</span>
                    <span class="ml-2"><?= esc_html($acct['name'] ?? 'N/A') ?></span>
                    <span class="ml-4 text-gray-500">ID: <span class="font-mono"><?= esc_html($acct['id'] ?? 'N/A') ?></span></span>
                    <?php if (!empty($acct['email'])): ?><span class="ml-4 text-gray-500">Email: <span class="font-mono"><?= esc_html($acct['email']) ?></span></span><?php endif; ?>
                </div>
                <?php if (isset($acctDetails['account'])): ?>
                    <?php $details = $acctDetails['account']; ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-2 text-sm">
                        <?php if (!empty($details['timezone'])): ?><div><span class="font-semibold">Timezone:</span> <?= esc_html($details['timezone']) ?></div><?php endif; ?>
                        <?php if (!empty($details['created_at'])): ?><div><span class="font-semibold">Created:</span> <?= esc_html($details['created_at']) ?></div><?php endif; ?>
                        <?php if (!empty($details['status'])): ?><div><span class="font-semibold">Status:</span> <?= esc_html($details['status']) ?></div><?php endif; ?>
                        <?php if (!empty($details['phone'])): ?><div><span class="font-semibold">Phone:</span> <?= esc_html($details['phone']) ?></div><?php endif; ?>
                        <?php if (!empty($details['website'])): ?><div><span class="font-semibold">Website:</span> <a href="<?= esc_url($details['website']) ?>" class="text-blue-600 underline" target="_blank"><?= esc_html($details['website']) ?></a></div><?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php elseif ($apiStatus === 'not_connected' && $apiKey && $apiSecret): ?>
            <div class="bg-yellow-50 text-yellow-800 p-2 rounded mt-2">Unable to load account info. Please check your API keys.</div>
        <?php endif; ?>
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
            <button type="submit" name="ctm_test_api" class="bg-blue-600 hover:bg-blue-700 text-white font-bold px-4 py-2 rounded shadow transition mb-4">Test API Connection</button>
            <div class="text-sm text-gray-500">Account ID: <span class="font-mono"><?= esc_html($accountId) ?></span></div>
        </div>
        <div class="bg-white p-6 rounded-lg shadow border border-gray-200">
            <h2 class="text-xl font-semibold mb-4 text-gray-700">Tracking</h2>
            <label class="flex items-center mb-4"><input type="checkbox" name="ctm_api_tracking_enabled" value="1"<?= checked($trackingEnabled, 1, false) ?> class="mr-2 rounded border-gray-300 focus:ring-blue-500" />Enable Tracking</label>
            <label class="block mb-2 text-gray-600 font-medium">Tracking Script</label>
            <textarea name="call_track_account_script" rows="3" class="block w-full rounded border-gray-300 focus:ring-blue-500 focus:border-blue-500 mb-4"><?= esc_textarea(get_option('call_track_account_script')) ?></textarea>
        </div>
        <div class="bg-white p-6 rounded-lg shadow border border-gray-200">
            <h2 class="text-xl font-semibold mb-4 text-gray-700">Integrations</h2>
            <label class="flex items-center mb-2"><input type="checkbox" name="ctm_api_cf7_enabled" value="1"<?= checked($cf7Enabled, 1, false) ?> class="mr-2 rounded border-gray-300 focus:ring-blue-500" />Enable Contact Form 7 Integration</label>
            <?php if (!class_exists('WPCF7_ContactForm')): ?>
                <div class="bg-yellow-50 border-l-4 border-yellow-400 text-yellow-800 p-2 rounded flex items-center gap-2 mb-2 mt-2 text-sm">
                    <span class="font-semibold">Contact Form 7 is not installed or activated.</span>
                    <a href="<?= esc_url(admin_url('plugin-install.php?s=contact+form+7&tab=search&type=term')) ?>" class="ml-2 bg-blue-600 hover:bg-blue-700 hover:text-white! text-white px-4 py-2 rounded shadow transition text-sm font-medium">Install Contact Form 7</a>
                </div>
            <?php endif; ?>
            <label class="flex items-center mb-2"><input type="checkbox" name="ctm_api_gf_enabled" value="1"<?= checked($gfEnabled, 1, false) ?> class="mr-2 rounded border-gray-300 focus:ring-blue-500" />Enable Gravity Forms Integration</label>
            <?php if (!class_exists('GFAPI')): ?>
                <div class="bg-yellow-50 border-l-4 border-yellow-400 text-yellow-800 p-2 rounded flex items-center gap-2 mb-2 mt-2 text-sm">
                    <span class="font-semibold">Gravity Forms is not installed or activated.</span>
                    <a href="https://www.gravityforms.com/" target="_blank" rel="noopener" class="ml-2 bg-blue-600 hover:bg-blue-700 hover:text-white! text-white px-4 py-2 rounded shadow transition text-sm font-medium">Get Gravity Forms</a>
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
</form> 