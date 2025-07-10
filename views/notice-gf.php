<div id="top-gf-notice" class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-900 p-4 rounded flex items-center justify-between gap-4 mb-2">
    <div class="flex items-center gap-4">
        <span class="font-semibold">Gravity Forms is not installed or activated.</span>
        <a href="<?= esc_url($gf_url) ?>" target="_blank" rel="noopener" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow transition">Get Gravity Forms</a>
    </div>
    <button type="button" onclick="dismissTopNotice('gf')" class="text-yellow-700 hover:text-yellow-900 p-1 rounded hover:bg-yellow-200 transition" title="I don't use this plugin">
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
    }).catch(error => {
        console.error('Error dismissing notice:', error);
    });
}
</script> 