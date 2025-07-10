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
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNoticeMessage('Notice dismissed successfully', 'success');
        } else {
            showNoticeMessage(data.data?.message || 'Failed to dismiss notice', 'error');
        }
    })
    .catch(error => {
        console.error('Error dismissing notice:', error);
        showNoticeMessage('Network error occurred while dismissing notice', 'error');
    });
}

function showNoticeMessage(message, type = 'info') {
    // Create message element
    const messageDiv = document.createElement('div');
    messageDiv.className = `fixed top-4 right-4 z-50 p-4 rounded-lg border-l-4 shadow-lg max-w-md ${
        type === 'success' ? 'bg-green-50 border-green-400 text-green-700' :
        type === 'error' ? 'bg-red-50 border-red-400 text-red-700' :
        'bg-blue-50 border-blue-400 text-blue-700'
    }`;
    
    messageDiv.innerHTML = `
        <div class="flex items-center">
            <svg class="w-5 h-5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                ${type === 'success' ? 
                    '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>' :
                    type === 'error' ?
                    '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>' :
                    '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>'
                }
            </svg>
            <span class="font-medium">${message}</span>
            <button onclick="this.parentElement.parentElement.remove()" class="ml-auto pl-2 text-gray-400 hover:text-gray-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
    `;
    
    // Add to page
    document.body.appendChild(messageDiv);
    
    // Animate in
    messageDiv.style.transform = 'translateX(100%)';
    messageDiv.style.transition = 'transform 0.3s ease';
    setTimeout(() => {
        messageDiv.style.transform = 'translateX(0)';
    }, 10);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        messageDiv.style.transform = 'translateX(100%)';
        setTimeout(() => {
            if (messageDiv.parentNode) {
                messageDiv.parentNode.removeChild(messageDiv);
            }
        }, 300);
    }, 5000);
}
</script> 