<div id="top-cf7-notice" class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-900 p-4 rounded flex items-center justify-between gap-4 mb-2">
    <div class="flex flex-col gap-2">
        <div class="flex items-center gap-4">
            <span class="font-semibold">Enable Contact Form 7 integration</span>
            <a href="<?= esc_url($cf7_url) ?>" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow transition">Install Contact Form 7</a>
        </div>
        <div class="text-sm mt-1">
            <strong>Note:</strong> It is required to use a form that captures a telephone number (<code>input type=&quot;tel&quot;</code>) in order for Contact Form 7 to integrate properly with our FormReactor. For more information, see <a href="https://www.calltrackingmetrics.com/support/integrations/wordpress" target="_blank" rel="noopener" class="underline text-blue-700">Using the CallTrackingMetrics WordPress Plugin</a>.
        </div>
        <div class="text-sm mt-1">
            <strong>Note:</strong> If you will request international (non-U.S.) phone numbers with your Contact Form 7 forms, we recommend using the plugin <a href="https://wordpress.org/plugins/cf7-international-telephone-input/" target="_blank" rel="noopener" class="underline text-blue-700">International Telephone Input for Contact Form 7</a> to avoid possible formatting issues with our FormReactor. Both <code>[tel]</code> and <code>[intl_tel]</code> are now supported as phone inputs.
        </div>
    </div>
    <button type="button" onclick="dismissTopNotice('cf7')" class="text-yellow-700 hover:text-yellow-900 p-1 rounded hover:bg-yellow-200 transition" title="Dismiss this notice.">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
    </button>
</div>

<script>
function dismissTopNotice(type) {
    // Hide the notice immediately
    const noticeId = 'top-' + type + '-notice';
    const notice = document.getElementById(noticeId);
    if (notice) {
        notice.style.transition = 'opacity 0.3s ease';
        notice.style.opacity = '0';
        setTimeout(() => {
            notice.style.display = 'none';
        }, 300);
    }
    // Send AJAX request to save dismiss preference
    const formData = new FormData();
    formData.append('action', 'ctm_dismiss_notice');
    formData.append('notice_type', type);
    formData.append('nonce', '<?= wp_create_nonce('ctm_dismiss_notice') ?>');
    fetch('<?= admin_url('admin-ajax.php') ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            ctmShowToast('Notice dismissed successfully', 'success');
        } else {
            ctmShowToast(data.data?.message || 'Failed to dismiss notice', 'error');
        }
    })
    .catch(error => {
        console.error('Error dismissing notice:', error);
        ctmShowToast('Network error occurred while dismissing notice', 'error');
    });
}
</script> 