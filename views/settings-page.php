<?php
// Settings page wrapper view
?>
<script src="https://cdn.tailwindcss.com/3.4.1"></script>
<div class="mb-6">
    <?php if (!empty($notices)) foreach ($notices as $notice) echo $notice; ?>
</div>
<div class="wrap max-w-4xl mx-auto p-6 bg-white rounded-lg shadow-md">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-3xl font-bold text-gray-800">CallTrackingMetrics Settings</h1>
        <!-- API Status Indicator -->
        <div class="flex items-center gap-3">
            <?php if ($apiStatus === 'connected'): ?>
                <div class="flex items-center gap-2 px-3 py-2 bg-green-50 border border-green-200 rounded-lg">
                    <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                    <span class="text-sm font-medium text-green-700">API Connected</span>
                    <a href="?page=call-tracking-metrics&tab=api" class="text-green-600 hover:text-green-800 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                        </svg>
                    </a>
                </div>
            <?php else: ?>
                <div class="flex items-center gap-2 px-3 py-2 bg-red-50 border border-red-200 rounded-lg">
                    <div class="w-2 h-2 bg-red-500 rounded-full"></div>
                    <span class="text-sm font-medium text-red-700">API Not Connected</span>
                    <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.08 15.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
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