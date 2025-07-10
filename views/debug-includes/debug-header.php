<?php
/**
 * Debug Header Component
 * Displays debug mode status, statistics, and toggle controls
 */

// Ensure variables are available from parent context
$debugEnabled = $debugEnabled ?? false;
$log_stats = $log_stats ?? ['total_days' => 0, 'total_entries' => 0, 'total_size' => 0, 'type_counts' => []];
?>

<div class="bg-gradient-to-r from-blue-600 to-purple-600 rounded-xl shadow-lg p-8 mb-8 text-white">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-3xl font-bold mb-2">Debug Center</h2>
            <p class="text-blue-100 mb-4">Advanced debugging tools and system diagnostics</p>
            
            <?php if ($debugEnabled): ?>
                <div class="flex items-center space-x-6 text-sm">
                    <div class="flex items-center">
                        <div class="w-3 h-3 bg-green-400 rounded-full mr-2 animate-pulse"></div>
                        <span>Debug Mode: Active</span>
                    </div>
                    <div class="flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <span><?= $log_stats['total_entries'] ?> Log Entries</span>
                    </div>
                    <div class="flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span><?= $log_stats['total_days'] ?> Days Logged</span>
                    </div>
                    <div class="flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/>
                        </svg>
                        <span><?= size_format($log_stats['total_size']) ?> Data</span>
                    </div>
                </div>
            <?php else: ?>
                <div class="flex items-center">
                    <div class="w-3 h-3 bg-red-400 rounded-full mr-2"></div>
                    <span>Debug Mode: Disabled</span>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="text-right">
            <?php if ($debugEnabled): ?>
                <button type="button" onclick="toggleDebugMode()" id="toggle-debug-btn" class="bg-white/20 hover:bg-white/30 text-white font-medium px-6 py-3 rounded-lg shadow transition duration-200 disabled:opacity-50 disabled:cursor-not-allowed backdrop-blur-sm border border-white/20">
                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 10a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z"/>
                    </svg>
                    Disable Debug Mode
                </button>
                
                <div class="mt-3 text-xs text-blue-100">
                    Last activity: <?= current_time('M j, Y \a\t g:i A') ?>
                </div>
            <?php else: ?>
                <button type="button" onclick="toggleDebugMode()" id="toggle-debug-btn" class="bg-green-500 hover:bg-green-600 text-white font-medium px-6 py-3 rounded-lg shadow transition duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h1m4 0h1m-6 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Enable Debug Mode
                </button>
                
                <div class="mt-3 text-xs text-blue-100">
                    Enable to start logging activity
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <?php if ($debugEnabled && !empty($log_stats['type_counts'])): ?>
        <div class="mt-6 pt-6 border-t border-white/20">
            <h4 class="text-lg font-semibold mb-3">Log Type Distribution</h4>
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-4">
                <?php 
                $type_colors = [
                    'error' => 'bg-red-500',
                    'warning' => 'bg-yellow-500',
                    'info' => 'bg-blue-500',
                    'debug' => 'bg-gray-500',
                    'api' => 'bg-purple-500',
                    'config' => 'bg-indigo-500',
                    'system' => 'bg-green-500'
                ];
                
                foreach ($log_stats['type_counts'] as $type => $count): 
                    $color = $type_colors[$type] ?? 'bg-gray-500';
                ?>
                    <div class="text-center">
                        <div class="<?= $color ?> w-full h-2 rounded-full mb-2"></div>
                        <div class="text-sm font-medium"><?= $count ?></div>
                        <div class="text-xs text-blue-100 capitalize"><?= $type ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div> 