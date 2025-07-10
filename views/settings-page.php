<?php
// Settings page wrapper view
?>
<script src="https://cdn.tailwindcss.com/3.4.1"></script>
<div class="mb-6">
    <?php if (!empty($notices)) foreach ($notices as $notice) echo $notice; ?>
</div>
<div class="wrap max-w-4xl mx-auto p-6 bg-white rounded-lg shadow-md">
    <h1 class="text-3xl font-bold mb-6 text-gray-800">CallTrackingMetrics Settings</h1>
    
    <?php if ($apiStatus === 'connected'): ?>
        <!-- Full Tab Navigation (Connected) -->
        <nav class="flex space-x-2 mb-8">
            <a href="?page=call-tracking-metrics&tab=general" class="px-4 py-2 rounded-t-lg font-medium <?= $active_tab === 'general' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?>">General</a>
            <a href="?page=call-tracking-metrics&tab=logs" class="px-4 py-2 rounded-t-lg font-medium <?= $active_tab === 'logs' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?>">Logs</a>
            <a href="?page=call-tracking-metrics&tab=mapping" class="px-4 py-2 rounded-t-lg font-medium <?= $active_tab === 'mapping' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?>">Field Mapping</a>
            <a href="?page=call-tracking-metrics&tab=api" class="px-4 py-2 rounded-t-lg font-medium <?= $active_tab === 'api' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?>">API Activity</a>
            <a href="?page=call-tracking-metrics&tab=documentation" class="px-4 py-2 rounded-t-lg font-medium <?= $active_tab === 'documentation' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?>">Documentation</a>
            <a href="?page=call-tracking-metrics&tab=debug" class="px-4 py-2 rounded-t-lg font-medium <?= $active_tab === 'debug' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?>">Debug</a>
        </nav>
    <?php else: ?>
        <!-- Minimal Tab Navigation (Not Connected) -->
        <nav class="flex space-x-2 mb-8">
            <span class="px-4 py-2 rounded-t-lg font-medium bg-blue-600 text-white">API Connection</span>
            <span class="px-3 py-2 text-sm text-gray-500 italic flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m0 0l-2-2m2 2l2-2m-2-7a3 3 0 100 6 3 3 0 000-6z"></path>
                </svg>
                More tabs available after connection
            </span>
        </nav>
    <?php endif; ?>
    
    <div class="bg-gray-50 p-6 rounded-b-lg">
        <?= $tab_content ?>
    </div>
</div> 