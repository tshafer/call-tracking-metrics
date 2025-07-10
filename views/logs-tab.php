<?php
// Logs tab view
?>
<div class="mb-12">
    <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-8 mb-8">
        <div class="flex items-center mb-6 border-b border-blue-100 pb-4">
            <svg class="w-7 h-7 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2a4 4 0 014-4h3m4 4v1a3 3 0 01-3 3H7a3 3 0 01-3-3v-1a9 9 0 0118 0z" /></svg>
            <h2 class="text-2xl font-bold text-blue-800 tracking-tight">CF7 Logs</h2>
        </div>
        <div class="mb-6">
            <button type="button" onclick="clearLogs('cf7')" class="bg-gradient-to-r from-blue-600 to-blue-400 hover:from-blue-700 hover:to-blue-500 text-white font-bold px-6 py-2 rounded-lg shadow transition-transform transform hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed" id="clear-cf7-btn">Clear CF7 Logs</button>
        </div>
        <?php if (empty($cf7Logs)): ?>
            <div class="text-gray-500">No CF7 logs found.</div>
        <?php else: ?>
            <div class="overflow-x-auto"><table class="min-w-full bg-white border border-gray-200 rounded-xl shadow-sm text-sm">
                <thead class="sticky top-0 z-10 bg-blue-50 text-blue-800 font-semibold">
                    <tr><th class="px-4 py-2 border-b text-left">Date</th><th class="px-4 py-2 border-b text-left">Status</th><th class="px-4 py-2 border-b text-left">Message</th></tr>
                </thead>
                <tbody>
                <?php $rowAlt = false; foreach ($cf7Logs as $log):
                    $status = strtolower($log['status'] ?? '');
                    $badge = $status === 'success' ? 'bg-green-100 text-green-700 border-green-300' : 'bg-red-100 text-red-700 border-red-300';
                    $icon = $status === 'success'
                        ? '<svg class="inline w-4 h-4 mr-1 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>'
                        : '<svg class="inline w-4 h-4 mr-1 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>';
                    $rowBg = $rowAlt ? 'bg-blue-50' : 'bg-white';
                ?>
                    <tr class="<?= $rowBg ?> hover:bg-blue-100 transition">
                        <td class="px-4 py-2 border-b font-mono"><?= esc_html($log['date'] ?? '') ?></td>
                        <td class="px-4 py-2 border-b"><span class="inline-flex items-center px-2 py-1 rounded border text-xs font-semibold <?= $badge ?>" title="<?= ucfirst($status) ?>"><?= $icon . esc_html($log['status'] ?? '') ?></span></td>
                        <td class="px-4 py-2 border-b" title="<?= esc_attr($log['message'] ?? '') ?>"><?= esc_html($log['message'] ?? '') ?></td>
                    </tr>
                <?php $rowAlt = !$rowAlt; endforeach; ?>
                </tbody>
            </table></div>
        <?php endif; ?>
    </div>
    <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-8 mb-8">
        <div class="flex items-center mb-6 border-b border-blue-100 pb-4">
            <svg class="w-7 h-7 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v4a1 1 0 001 1h3m10-5v4a1 1 0 001 1h3m-7 4v4m0 0H7m5 0h5" /></svg>
            <h2 class="text-2xl font-bold text-blue-800 tracking-tight">Gravity Forms Logs</h2>
        </div>
        <div class="mb-6">
            <button type="button" onclick="clearLogs('gf')" class="bg-gradient-to-r from-blue-600 to-blue-400 hover:from-blue-700 hover:to-blue-500 text-white font-bold px-6 py-2 rounded-lg shadow transition-transform transform hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed" id="clear-gf-btn">Clear GF Logs</button>
        </div>
        <?php if (empty($gfLogs)): ?>
            <div class="text-gray-500">No Gravity Forms logs found.</div>
        <?php else: ?>
            <div class="overflow-x-auto"><table class="min-w-full bg-white border border-gray-200 rounded-xl shadow-sm text-sm">
                <thead class="sticky top-0 z-10 bg-blue-50 text-blue-800 font-semibold">
                    <tr><th class="px-4 py-2 border-b text-left">Date</th><th class="px-4 py-2 border-b text-left">Status</th><th class="px-4 py-2 border-b text-left">Message</th></tr>
                </thead>
                <tbody>
                <?php $rowAlt = false; foreach ($gfLogs as $log):
                    $status = strtolower($log['status'] ?? '');
                    $badge = $status === 'success' ? 'bg-green-100 text-green-700 border-green-300' : 'bg-red-100 text-red-700 border-red-300';
                    $icon = $status === 'success'
                        ? '<svg class="inline w-4 h-4 mr-1 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>'
                        : '<svg class="inline w-4 h-4 mr-1 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>';
                    $rowBg = $rowAlt ? 'bg-blue-50' : 'bg-white';
                ?>
                    <tr class="<?= $rowBg ?> hover:bg-blue-100 transition">
                        <td class="px-4 py-2 border-b font-mono"><?= esc_html($log['date'] ?? '') ?></td>
                        <td class="px-4 py-2 border-b"><span class="inline-flex items-center px-2 py-1 rounded border text-xs font-semibold <?= $badge ?>" title="<?= ucfirst($status) ?>"><?= $icon . esc_html($log['status'] ?? '') ?></span></td>
                        <td class="px-4 py-2 border-b" title="<?= esc_attr($log['message'] ?? '') ?>"><?= esc_html($log['message'] ?? '') ?></td>
                    </tr>
                <?php $rowAlt = !$rowAlt; endforeach; ?>
                </tbody>
            </table></div>
        <?php endif; ?>
    </div>
</div>

<script>
function clearLogs(logType) {
    const button = document.getElementById(`clear-${logType}-btn`);
    const originalText = button.textContent;
    
    // Confirm action
    if (!confirm(`Are you sure you want to clear all ${logType.toUpperCase()} logs? This action cannot be undone.`)) {
        return;
    }
    
    // Disable button and show loading state
    button.disabled = true;
    button.textContent = 'Clearing...';
    
    // Prepare form data
    const formData = new FormData();
    formData.append('action', 'ctm_clear_logs');
    formData.append('log_type', logType);
    formData.append('nonce', '<?= wp_create_nonce('ctm_clear_logs') ?>');
    
    // Send AJAX request
    fetch('<?= admin_url('admin-ajax.php') ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            ctmShowToast(data.data.message, 'success');
            
            // Clear the log table
            const logTable = button.closest('.bg-white').querySelector('table');
            if (logTable) {
                const tbody = logTable.querySelector('tbody');
                if (tbody) {
                    tbody.innerHTML = '';
                }
                // Hide table and show "no logs" message
                logTable.parentElement.style.display = 'none';
                const noLogsDiv = document.createElement('div');
                noLogsDiv.className = 'text-gray-500';
                noLogsDiv.textContent = `No ${logType.toUpperCase()} logs found.`;
                logTable.parentElement.parentElement.appendChild(noLogsDiv);
            }
        } else {
            ctmShowToast(data.data.message || 'Failed to clear logs', 'error');
        }
    })
    .catch(error => {
        console.error('Error clearing logs:', error);
        ctmShowToast('Network error occurred while clearing logs', 'error');
    })
    .finally(() => {
        // Re-enable button
        button.disabled = false;
        button.textContent = originalText;
    });
}
</script>