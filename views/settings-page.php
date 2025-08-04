<?php
// Add error handling to prevent blank pages
try {
    // Ensure required variables are defined with defaults
    $apiStatus = $apiStatus ?? 'not_connected';
    $active_tab = $active_tab ?? 'general';
    $tab_content = $tab_content ?? '';
    $notices = $notices ?? [];
    
    // Validate that required WordPress functions are available
    if (!function_exists('_e')) {
        throw new Exception('WordPress translation functions not available');
    }
    
    if (!function_exists('get_option')) {
        throw new Exception('WordPress options functions not available');
    }
    
} catch (Exception $e) {
    // Log the error and show a user-friendly message
    error_log('CTM Plugin Error: ' . $e->getMessage());
    echo '<div class="wrap"><div class="notice notice-error"><p>Plugin Error: Unable to load Call Tracking Metrics settings. Please contact support.</p></div></div>';
    return;
}
?>
<div class="mb-6">
    <?php if (!empty($notices)) foreach ($notices as $notice) echo $notice; ?>
</div>
<div class="wrap max-w-full mx-auto p-6 bg-white rounded-lg shadow-md">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-3xl font-bold text-gray-800"><?php _e('CallTrackingMetrics Settings', 'call-tracking-metrics'); ?></h1>
        <!-- API Status Indicator -->
        <div class="flex items-center gap-3">
            <?php if ($apiStatus === 'connected'): ?>
                <div class="flex items-center gap-2 px-3 py-2 bg-green-50 border border-green-200 rounded-lg">
                    <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                    <span class="text-sm font-medium text-green-700"><?php _e('API Connected', 'call-tracking-metrics'); ?></span>
                    <a href="?page=call-tracking-metrics&tab=api" class="text-green-600 hover:text-green-800 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                        </svg>
                    </a>
                </div>
            <?php else: ?>
                <div class="flex items-center gap-2 px-3 py-2 bg-red-50 border border-red-200 rounded-lg">
                    <div class="w-2 h-2 bg-red-500 rounded-full"></div>
                    <span class="text-sm font-medium text-red-700"><?php _e('API Not Connected', 'call-tracking-metrics'); ?></span>
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
                <span><?php _e('General', 'call-tracking-metrics'); ?></span>
            </a>
            <a href="?page=call-tracking-metrics&tab=logs" class="flex items-center px-4 py-2 rounded-t-lg font-medium transition-colors duration-200 <?= $active_tab === 'logs' ? 'bg-blue-600 !text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?>">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <span><?php _e('Logs', 'call-tracking-metrics'); ?></span>
            </a>
            <!-- <a href="?page=call-tracking-metrics&tab=mapping" class="flex items-center px-4 py-2 rounded-t-lg font-medium transition-colors duration-200 <?= $active_tab === 'mapping' ? 'bg-blue-600 !text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?>">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 3v6a1 1 0 001 1h6"></path>
                </svg>
                <span><?php _e('Field Mapping', 'call-tracking-metrics'); ?></span>
            </a> -->
            <a href="?page=call-tracking-metrics&tab=api" class="flex items-center px-4 py-2 rounded-t-lg font-medium transition-colors duration-200 <?= $active_tab === 'api' ? 'bg-blue-600 !text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?>">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"></path>
                </svg>
                <span><?php _e('API Activity', 'call-tracking-metrics'); ?></span>
            </a>
            <a href="?page=call-tracking-metrics&tab=import" class="flex items-center px-4 py-2 rounded-t-lg font-medium transition-colors duration-200 <?= $active_tab === 'import' ? 'bg-blue-600 !text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?>">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path>
                </svg>
                <span><?php _e('Import Forms', 'call-tracking-metrics'); ?></span>
            </a>
            <a href="?page=call-tracking-metrics&tab=documentation" class="flex items-center px-4 py-2 rounded-t-lg font-medium transition-colors duration-200 <?= $active_tab === 'documentation' ? 'bg-blue-600 !text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?>">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                </svg>
                <span><?php _e('Documentation', 'call-tracking-metrics'); ?></span>
            </a>
            <?php if (get_option('ctm_debug_enabled')): ?>
            <a href="?page=call-tracking-metrics&tab=debug" class="flex items-center px-4 py-2 rounded-t-lg font-medium transition-colors duration-200 <?= $active_tab === 'debug' ? 'bg-blue-600 !text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?>">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4"></path>
                </svg>
                <span><?php _e('Debug', 'call-tracking-metrics'); ?></span>
            </a>
            <?php endif; ?>
        </nav>
    <?php else: ?>
        <!-- Minimal Tab Navigation (Not Connected) -->
        <nav class="flex space-x-2">
            <span class="px-4 py-2 rounded-t-lg font-medium bg-blue-600 text-white"><?php _e('API Connection', 'call-tracking-metrics'); ?></span>
            <span class="px-3 py-2 text-sm text-gray-500 italic flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m0 0l-2-2m2 2l2-2m-2-7a3 3 0 100 6 3 3 0 000-6z"></path>
                </svg>
                <span><?php _e('More tabs available after connection', 'call-tracking-metrics'); ?></span>
            </span>
        </nav>
    <?php endif; ?>
    
    <div class="bg-gray-50 p-6 rounded-b-lg">
        <?= $tab_content ?>
    </div>
</div>

<!-- Support Footer -->
<div class="mt-8 bg-white border border-gray-200 rounded-lg shadow-sm">
    <div class="bg-blue-50 px-6 py-4 border-b border-blue-200 rounded-t-lg">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-blue-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192L5.636 18.364M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"></path>
                </svg>
                <h3 class="text-lg font-semibold text-blue-800"><?php _e('Need Help?', 'call-tracking-metrics'); ?></h3>
            </div>
            <div class="text-sm text-blue-600">
                <span class="font-medium"><?php _e('Plugin Version:', 'call-tracking-metrics'); ?></span> 2.0.0
            </div>
        </div>
    </div>
    
    <div class="p-6">
        <div class="flex flex-wrap justify-center gap-8">
            <!-- Documentation -->
            <div class="flex items-center space-x-2">
                <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                </svg>
                <span class="font-semibold text-gray-800"><?php _e('Documentation', 'call-tracking-metrics'); ?></span>
            </div>
            
            <!-- Support -->
            <div class="flex items-center space-x-2">
                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192L5.636 18.364M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"></path>
                </svg>
                <span class="font-semibold text-gray-800"><?php _e('Support', 'call-tracking-metrics'); ?></span>
            </div>
            
            <!-- Resources -->
            <div class="flex items-center space-x-2">
                <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                </svg>
                <span class="font-semibold text-gray-800"><?php _e('Resources', 'call-tracking-metrics'); ?></span>
            </div>
        </div>
        
        <!-- Quick Help Section -->
        <div class="mt-6 pt-6 border-t border-gray-200">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <span class="text-sm text-gray-600"><?php _e('Quick Help:', 'call-tracking-metrics'); ?></span>
                    <a href="?page=call-tracking-metrics&tab=debug" class="text-sm text-blue-600 hover:text-blue-800 underline"><?php _e('Debug Tools', 'call-tracking-metrics'); ?></a>
                    <span class="text-gray-400">|</span>
                    <a href="?page=call-tracking-metrics&tab=api" class="text-sm text-blue-600 hover:text-blue-800 underline"><?php _e('API Activity', 'call-tracking-metrics'); ?></a>
                    <span class="text-gray-400">|</span>
                    <a href="?page=call-tracking-metrics&tab=logs" class="text-sm text-blue-600 hover:text-blue-800 underline"><?php _e('View Logs', 'call-tracking-metrics'); ?></a>
                </div>
                <div class="text-xs text-gray-500">
                    <?php _e('WordPress', 'call-tracking-metrics'); ?> <?= get_bloginfo('version') ?> | 
                    <?php _e('PHP', 'call-tracking-metrics'); ?> <?= PHP_VERSION ?>
                </div>
            </div>
        </div>
    </div>
</div> 