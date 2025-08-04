<?php
/**
 * Debug Disabled State Component
 * Displays the interface when debug mode is disabled
 */
?>

<div class="bg-white rounded-xl shadow-lg border border-gray-200 p-8">
    <div class="text-center py-12">
        <div class="flex flex-col items-center justify-center gap-2 mb-6">
            <svg class="h-12 w-12 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
            </svg>
            <h3 class="text-2xl font-extrabold text-gray-900"><?php _e('Debug Mode is Disabled', 'call-tracking-metrics'); ?></h3>
        </div>
        <p class="text-gray-600 mb-6 max-w-md mx-auto"><?php _e('Debug mode is currently disabled. Enable it to start logging plugin activity, API requests, and troubleshooting information.', 'call-tracking-metrics'); ?></p>
        <div class="bg-gray-50 rounded-lg p-4 mb-6 max-w-lg mx-auto">
            <h4 class="font-medium text-gray-800 mb-2"><?php _e('What debug mode provides:', 'call-tracking-metrics'); ?></h4>
            <ul class="text-sm text-gray-600 space-y-1 text-left">
                <li>• <?php _e('Detailed API request and response logging', 'call-tracking-metrics'); ?></li>
                <li>• <?php _e('Error tracking and troubleshooting information', 'call-tracking-metrics'); ?></li>
                <li>• <?php _e('Plugin activity monitoring', 'call-tracking-metrics'); ?></li>
                <li>• <?php _e('Performance metrics and timing data', 'call-tracking-metrics'); ?></li>
                <li>• <?php _e('Integration debugging for forms and webhooks', 'call-tracking-metrics'); ?></li>
            </ul>
        </div>
        <button type="button" onclick="toggleDebugMode()" id="toggle-debug-btn" class="bg-green-600 hover:bg-green-700 text-white font-medium px-8 py-3 rounded-lg shadow transition duration-200 disabled:opacity-50 disabled:cursor-not-allowed"><?php _e('Enable Debug Mode', 'call-tracking-metrics'); ?></button>
    </div>
</div> 

<script>    

function toggleDebugMode() {
    const button = document.getElementById('toggle-debug-btn');
    const originalText = button.textContent;
    
    // Disable button and show loading state
    button.disabled = true;
    button.textContent = <?php _e('Processing...', 'call-tracking-metrics'); ?>;
    
    // Prepare form data
    const formData = new FormData();
    formData.append('action', 'ctm_toggle_debug_mode');
    formData.append('nonce', '<?= wp_create_nonce('ctm_toggle_debug_mode') ?>');
    
    // Send AJAX request
    fetch('<?= admin_url('admin-ajax.php') ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            ctmShowToast(data.data.message, 'success');
            
            // Update button text and state
            const button = document.getElementById('toggle-debug-btn');
            if (data.data.debug_enabled) {
                button.textContent = '<?php _e('Disable Debug Mode', 'call-tracking-metrics'); ?>';
                button.className = button.className.replace('bg-green-600 hover:bg-green-700', 'bg-red-600 hover:bg-red-700');
                
                // Show success message
                ctmShowToast('<?php _e('Debug logging is now active. All plugin activity will be recorded.', 'call-tracking-metrics'); ?>', 'info');
            } else {
                button.textContent = '<?php _e('Enable Debug Mode', 'call-tracking-metrics'); ?>';
                button.className = button.className.replace('bg-red-600 hover:bg-red-700', 'bg-green-600 hover:bg-green-700');
                
                // Show success message
                ctmShowToast('<?php _e('Debug logging has been stopped. Existing logs are preserved.', 'call-tracking-metrics'); ?>', 'info');
            }
            
        } else {
            ctmShowToast(data.data.message || '<?php _e('Failed to toggle debug mode', 'call-tracking-metrics'); ?>', 'error');
            // Re-enable button on error
            button.disabled = false;
            button.textContent = originalText;
        }
    })
    .catch(error => {
        console.error('Error toggling debug mode:', error);
            ctmShowToast('<?php _e('Network error occurred while toggling debug mode', 'call-tracking-metrics'); ?>', 'error');
        // Re-enable button on error
        button.disabled = false;
        button.textContent = originalText;
    });
}

</script>