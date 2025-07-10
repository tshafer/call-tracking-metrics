<?php
/**
 * Debug Disabled State Component
 * Displays the interface when debug mode is disabled
 */
?>

<div class="bg-white rounded-xl shadow-lg border border-gray-200 p-8">
    <div class="text-center py-12">
        <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
        </svg>
        <h3 class="text-xl font-semibold text-gray-800 mb-3">Debug Mode is Disabled</h3>
        <p class="text-gray-600 mb-6 max-w-md mx-auto">
            Debug mode is currently disabled. Enable it to start logging plugin activity, API requests, and troubleshooting information.
        </p>
        <div class="bg-gray-50 rounded-lg p-4 mb-6 max-w-lg mx-auto">
            <h4 class="font-medium text-gray-800 mb-2">What debug mode provides:</h4>
            <ul class="text-sm text-gray-600 space-y-1 text-left">
                <li>• Detailed API request and response logging</li>
                <li>• Error tracking and troubleshooting information</li>
                <li>• Plugin activity monitoring</li>
                <li>• Performance metrics and timing data</li>
                <li>• Integration debugging for forms and webhooks</li>
            </ul>
        </div>
        <button type="button" onclick="toggleDebugMode()" id="toggle-debug-btn" class="bg-green-600 hover:bg-green-700 text-white font-medium px-8 py-3 rounded-lg shadow transition duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
            Enable Debug Mode
        </button>
    </div>
</div> 