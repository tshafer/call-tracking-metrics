<?php
// Settings page wrapper view
?>
<script src="https://cdn.tailwindcss.com/3.4.1"></script>
<div class="mb-6">
    <?php if (!empty($notices)) foreach ($notices as $notice) echo $notice; ?>
</div>
<div class="wrap max-w-full mx-auto p-6 bg-white rounded-lg shadow-md">
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
        <nav class="flex space-x-2">
            <a href="?page=call-tracking-metrics&tab=general" class="flex items-center px-4 py-2 rounded-t-lg font-medium transition-colors duration-200 <?= $active_tab === 'general' ? 'bg-blue-600 !text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?>">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                General
            </a>
            <a href="?page=call-tracking-metrics&tab=logs" class="flex items-center px-4 py-2 rounded-t-lg font-medium transition-colors duration-200 <?= $active_tab === 'logs' ? 'bg-blue-600 !text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?>">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Logs
            </a>
            <a href="?page=call-tracking-metrics&tab=mapping" class="flex items-center px-4 py-2 rounded-t-lg font-medium transition-colors duration-200 <?= $active_tab === 'mapping' ? 'bg-blue-600 !text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?>">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 3v6a1 1 0 001 1h6"></path>
                </svg>
                Field Mapping
            </a>
            <a href="?page=call-tracking-metrics&tab=api" class="flex items-center px-4 py-2 rounded-t-lg font-medium transition-colors duration-200 <?= $active_tab === 'api' ? 'bg-blue-600 !text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?>">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"></path>
                </svg>
                API Activity
            </a>
            <a href="?page=call-tracking-metrics&tab=documentation" class="flex items-center px-4 py-2 rounded-t-lg font-medium transition-colors duration-200 <?= $active_tab === 'documentation' ? 'bg-blue-600 !text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?>">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                </svg>
                Documentation
            </a>
            <?php if (get_option('ctm_debug_enabled')): ?>
            <a href="?page=call-tracking-metrics&tab=debug" class="flex items-center px-4 py-2 rounded-t-lg font-medium transition-colors duration-200 <?= $active_tab === 'debug' ? 'bg-blue-600 !text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?>">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4"></path>
                </svg>
                Debug
            </a>
            <?php endif; ?>
        </nav>
    <?php else: ?>
        <!-- Minimal Tab Navigation (Not Connected) -->
        <nav class="flex space-x-2">
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