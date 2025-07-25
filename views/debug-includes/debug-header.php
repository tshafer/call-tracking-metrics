<?php
/**
 * Debug Header Component
 * Displays debug mode status, statistics, and toggle controls
 */

// Ensure variables are available from parent context
$debugEnabled = $debugEnabled ?? false;
$log_stats = $log_stats ?? ['total_days' => 0, 'total_entries' => 0, 'total_size' => 0, 'type_counts' => []];
?>

<div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-6 sm:p-6 p-4 mb-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-6">
        <div class="flex items-center gap-3">
            <svg class="w-7 h-7 text-blue-600 -mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <h2 class="text-2xl font-bold tracking-tight text-gray-900">Debug Center</h2>
        </div>

        <div class="flex items-center gap-1">
            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <span class="font-semibold text-gray-900"><?= $log_stats['total_entries'] ?></span>
            <span class="text-gray-500 ml-1">Log Entries</span>
        </div>
        <div class="flex items-center gap-1">
            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <span class="font-semibold text-gray-900"><?= $log_stats['total_days'] ?></span>
            <span class="text-gray-500 ml-1">Days Logged</span>
        </div>
        <div class="flex items-center gap-1">
            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/>
            </svg>
            <span class="font-semibold text-gray-900"><?= function_exists('size_format') ? size_format($log_stats['total_size']) : $log_stats['total_size'] . ' bytes' ?></span>
            <span class="text-gray-500 ml-1">Data</span>
        </div>
        <div class="flex items-center gap-1">
            <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10" stroke-width="2" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4"/>
            </svg>
            <span class="font-semibold text-gray-900"><?= $log_stats['type_counts']['error'] ?? 0 ?></span>
            <span class="text-gray-500 ml-1">Errors</span>
        </div>
        <div class="flex items-center gap-1">
            <svg class="w-5 h-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10" stroke-width="2" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01"/>
            </svg>
            <span class="font-semibold text-gray-900"><?= $log_stats['type_counts']['warning'] ?? 0 ?></span>
            <span class="text-gray-500 ml-1">Warnings</span>
        </div>
        <div class="flex items-center gap-1">
            <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10" stroke-width="2" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 16h.01M12 8v4"/>
            </svg>
            <span class="font-semibold text-gray-900"><?= $log_stats['type_counts']['info'] ?? 0 ?></span>
            <span class="text-gray-500 ml-1">Info</span>
        </div>
        <div class="flex items-center gap-1">
            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <rect x="4" y="4" width="16" height="16" rx="2" stroke-width="2"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h8"/>
            </svg>
            <span class="font-semibold text-gray-900"><?= $log_stats['type_counts']['debug'] ?? 0 ?></span>
            <span class="text-gray-500 ml-1">Debug</span>
        </div>
    </div>
    
    <?php if ($debugEnabled && !empty($log_stats['type_counts'])): ?>
        <div class="mt-6 border-t border-white/20">
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
                        <div class="text-xs text-blue-600 capitalize"><?= $type ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div> 