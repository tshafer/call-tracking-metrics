<?php
// Field Mapping tab view
$cf7_installed = class_exists('WPCF7_ContactForm');
$gf_installed = class_exists('GFAPI');
$available_plugins = [];

if ($cf7_installed) $available_plugins['cf7'] = 'Contact Form 7';
if ($gf_installed) $available_plugins['gf'] = 'Gravity Forms';

$plugin_count = count($available_plugins);
?>
<div class="mb-12">
    <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-8 mb-8">
        <div class="flex items-center mb-6 border-b border-blue-100 pb-4">
            <svg class="w-7 h-7 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2a4 4 0 014-4h3m4 4v1a3 3 0 01-3 3H7a3 3 0 01-3-3v-1a9 9 0 0118 0z" /></svg>
            <h2 class="text-2xl font-bold text-blue-800 tracking-tight">Field Mapping</h2>
        </div>
        
        <?php if ($plugin_count === 0): ?>
            <!-- No plugins installed -->
            <div class="text-center py-12">
                <div class="bg-gray-50 rounded-lg p-8 max-w-md mx-auto">
                    <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 15.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">No Form Plugins Installed</h3>
                    <p class="text-gray-600 mb-6">To use field mapping, you need to install at least one supported form plugin.</p>
                    <div class="space-y-3">
                        <a href="<?= esc_url(admin_url('plugin-install.php?s=contact+form+7&tab=search&type=term')) ?>" 
                           class="block bg-blue-600 hover:bg-blue-700 text-white font-bold px-6 py-3 rounded-lg shadow transition">
                            Install Contact Form 7
                        </a>
                        <a href="https://www.gravityforms.com/" target="_blank" rel="noopener"
                           class="block bg-green-600 hover:bg-green-700 text-white font-bold px-6 py-3 rounded-lg shadow transition">
                            Get Gravity Forms
                        </a>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- At least one plugin is installed -->
            <form id="ctm-field-mapping-form" class="space-y-8">
                <div class="grid grid-cols-1 <?= $plugin_count > 1 ? 'md:grid-cols-2' : '' ?> gap-8 mb-6">
                    
                    <?php if ($plugin_count > 1): ?>
                        <!-- Multiple plugins - show selector -->
                        <div>
                            <label class="block mb-2 text-gray-600 font-medium">Form Type</label>
                            <select id="ctm_form_type" class="block w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500 text-base">
                                <option value="">Select form type...</option>
                                <?php foreach ($available_plugins as $key => $name): ?>
                                    <option value="<?= esc_attr($key) ?>"><?= esc_html($name) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php else: ?>
                        <!-- Single plugin - auto-select but keep as select for JavaScript compatibility -->
                        <?php $single_key = array_key_first($available_plugins); ?>
                        <?php $single_name = $available_plugins[$single_key]; ?>
                        <div>
                            <label class="block mb-2 text-gray-600 font-medium">Form Type</label>
                            <select id="ctm_form_type" class="block w-full rounded-lg border-gray-300 bg-gray-50 text-base cursor-not-allowed" style="pointer-events: none;">
                                <option value="<?= esc_attr($single_key) ?>" selected><?= esc_html($single_name) ?> (Auto-selected)</option>
                            </select>
                        </div>
                    <?php endif; ?>
                    
                    <div>
                        <label class="block mb-2 text-gray-600 font-medium">Form</label>
                        <select id="ctm_form_id" class="block w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500 text-base">
                            <option value="">Select a form...</option>
                        </select>
                    </div>
                </div>
                
                <div id="ctm-mapping-table-container"></div>
                
                <div class="flex gap-4 mt-6">
                    <button type="button" id="ctm-save-mapping" class="bg-blue-600 hover:bg-blue-700 text-white font-bold px-6 py-2 rounded-lg shadow transition">Save Mapping</button>
                    <button type="button" id="ctm-preview-mapping" class="bg-gray-500 hover:bg-gray-600 text-white font-bold px-6 py-2 rounded-lg shadow transition">Preview</button>
                </div>
            </form>
            
            <div id="ctm-mapping-preview" class="mt-8 hidden"></div>
            
            <!-- Show install links for missing plugins -->
            <?php if (!$cf7_installed || !$gf_installed): ?>
                <div class="mt-8 pt-6 border-t border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">Add More Form Plugins</h3>
                    <div class="flex gap-4">
                        <?php if (!$cf7_installed): ?>
                            <a href="<?= esc_url(admin_url('plugin-install.php?s=contact+form+7&tab=search&type=term')) ?>" 
                               class="text-blue-600 hover:text-blue-800 text-sm underline transition">
                                Install Contact Form 7 →
                            </a>
                        <?php endif; ?>
                        <?php if (!$gf_installed): ?>
                            <a href="https://www.gravityforms.com/" target="_blank" rel="noopener"
                               class="text-green-600 hover:text-green-800 text-sm underline transition">
                                Get Gravity Forms →
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
            
        <?php endif; ?>
    </div>
</div>

<?php if ($plugin_count === 1): ?>
<script>
// Auto-trigger form loading for single plugin
document.addEventListener('DOMContentLoaded', function() {
    // Small delay to ensure all JavaScript is loaded
    setTimeout(function() {
        const formTypeSelect = document.getElementById('ctm_form_type');
        if (formTypeSelect && formTypeSelect.value) {
            // Trigger the change event to load forms
            const event = new Event('change', { bubbles: true });
            formTypeSelect.dispatchEvent(event);
            
            // Also trigger using jQuery if available (for compatibility)
            if (typeof jQuery !== 'undefined') {
                jQuery(formTypeSelect).trigger('change');
            }
        }
    }, 100);
});
</script>
<?php endif; ?>

<?php if ($plugin_count > 0): ?>
<script>
// Mapping functionality
document.addEventListener('DOMContentLoaded', function() {
    const formTypeSelect = document.getElementById('ctm_form_type');
    const formIdSelect = document.getElementById('ctm_form_id');
    const saveBtn = document.getElementById('ctm-save-mapping');
    const previewBtn = document.getElementById('ctm-preview-mapping');
    const mappingContainer = document.getElementById('ctm-mapping-table-container');
    const previewContainer = document.getElementById('ctm-mapping-preview');
    
    if (formTypeSelect) {
        formTypeSelect.addEventListener('change', loadForms);
    }
    
    if (formIdSelect) {
        formIdSelect.addEventListener('change', loadFields);
    }
    
    if (saveBtn) {
        saveBtn.addEventListener('click', saveMapping);
    }
    
    if (previewBtn) {
        previewBtn.addEventListener('click', previewMapping);
    }
    
    function loadForms() {
        const formType = formTypeSelect.value;
        if (!formType) {
            formIdSelect.innerHTML = '<option value="">Select a form...</option>';
            return;
        }
        
        formIdSelect.innerHTML = '<option value="">Loading forms...</option>';
        
        fetch('<?= admin_url('admin-ajax.php') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=ctm_get_forms&form_type=${encodeURIComponent(formType)}&nonce=<?= wp_create_nonce('ctm_mapping_nonce') ?>`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data) {
                let options = '<option value="">Select a form...</option>';
                data.data.forEach(form => {
                    options += `<option value="${form.id}">${form.title}</option>`;
                });
                formIdSelect.innerHTML = options;
                showMappingMessage('Forms loaded successfully', 'success');
            } else {
                formIdSelect.innerHTML = '<option value="">Error loading forms</option>';
                showMappingMessage(data.data?.message || 'Failed to load forms', 'error');
            }
        })
        .catch(error => {
            console.error('Error loading forms:', error);
            formIdSelect.innerHTML = '<option value="">Error loading forms</option>';
            showMappingMessage('Network error while loading forms', 'error');
        });
    }
    
    function loadFields() {
        const formType = formTypeSelect.value;
        const formId = formIdSelect.value;
        
        if (!formType || !formId) {
            mappingContainer.innerHTML = '';
            return;
        }
        
        mappingContainer.innerHTML = '<div class="text-center py-4">Loading fields...</div>';
        
        fetch('<?= admin_url('admin-ajax.php') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=ctm_get_fields&form_type=${encodeURIComponent(formType)}&form_id=${encodeURIComponent(formId)}&nonce=<?= wp_create_nonce('ctm_mapping_nonce') ?>`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data) {
                displayFieldMapping(data.data);
                showMappingMessage('Form fields loaded successfully', 'success');
            } else {
                mappingContainer.innerHTML = '<div class="text-red-600 text-center py-4">Error loading fields</div>';
                showMappingMessage(data.data?.message || 'Failed to load form fields', 'error');
            }
        })
        .catch(error => {
            console.error('Error loading fields:', error);
            mappingContainer.innerHTML = '<div class="text-red-600 text-center py-4">Network error loading fields</div>';
            showMappingMessage('Network error while loading fields', 'error');
        });
    }
    
    function displayFieldMapping(fields) {
        if (!fields || fields.length === 0) {
            mappingContainer.innerHTML = '<div class="text-gray-600 text-center py-4">No fields found for this form</div>';
            return;
        }
        
        let html = `
            <div class="bg-gray-50 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Map Form Fields to CTM</h3>
                <div class="space-y-4">
        `;
        
        fields.forEach(field => {
            html += `
                <div class="flex items-center justify-between bg-white p-4 rounded border">
                    <div>
                        <label class="font-medium text-gray-700">${field.label}</label>
                        <div class="text-sm text-gray-500">${field.type} field</div>
                    </div>
                    <select name="mapping[${field.name}]" class="ml-4 rounded border-gray-300 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Don't map</option>
                        <option value="phone">Phone Number</option>
                        <option value="email">Email Address</option>
                        <option value="name">Full Name</option>
                        <option value="first_name">First Name</option>
                        <option value="last_name">Last Name</option>
                        <option value="company">Company</option>
                        <option value="message">Message</option>
                    </select>
                </div>
            `;
        });
        
        html += `
                </div>
            </div>
        `;
        
        mappingContainer.innerHTML = html;
    }
    
    function saveMapping() {
        const formType = formTypeSelect.value;
        const formId = formIdSelect.value;
        
        if (!formType || !formId) {
            showMappingMessage('Please select a form type and form first', 'error');
            return;
        }
        
        const originalText = saveBtn.textContent;
        saveBtn.disabled = true;
        saveBtn.textContent = 'Saving...';
        
        // Collect mapping data
        const mappingData = {};
        const mappingSelects = mappingContainer.querySelectorAll('select[name^="mapping["]');
        mappingSelects.forEach(select => {
            const fieldName = select.name.match(/mapping\[(.*?)\]/)[1];
            if (select.value) {
                mappingData[fieldName] = select.value;
            }
        });
        
        const formData = new FormData();
        formData.append('action', 'ctm_save_mapping');
        formData.append('form_type', formType);
        formData.append('form_id', formId);
        formData.append('nonce', '<?= wp_create_nonce('ctm_mapping_nonce') ?>');
        
        // Add mapping data
        Object.keys(mappingData).forEach(key => {
            formData.append(`mapping[${key}]`, mappingData[key]);
        });
        
        fetch('<?= admin_url('admin-ajax.php') ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showMappingMessage(data.data.message || 'Mapping saved successfully', 'success');
                
                // Show what was mapped
                const mappedFields = Object.keys(mappingData).length;
                if (mappedFields > 0) {
                    setTimeout(() => {
                        showMappingMessage(`Successfully mapped ${mappedFields} field${mappedFields !== 1 ? 's' : ''} for ${formType.toUpperCase()} form`, 'info');
                    }, 1000);
                }
            } else {
                showMappingMessage(data.data?.message || 'Failed to save mapping', 'error');
            }
        })
        .catch(error => {
            console.error('Error saving mapping:', error);
            showMappingMessage('Network error occurred while saving mapping', 'error');
        })
        .finally(() => {
            saveBtn.disabled = false;
            saveBtn.textContent = originalText;
        });
    }
    
    function previewMapping() {
        const formType = formTypeSelect.value;
        const formId = formIdSelect.value;
        
        if (!formType || !formId) {
            showMappingMessage('Please select a form type and form first', 'error');
            return;
        }
        
        const mappingData = {};
        const mappingSelects = mappingContainer.querySelectorAll('select[name^="mapping["]');
        mappingSelects.forEach(select => {
            const fieldName = select.name.match(/mapping\[(.*?)\]/)[1];
            if (select.value) {
                mappingData[fieldName] = select.value;
            }
        });
        
        if (Object.keys(mappingData).length === 0) {
            showMappingMessage('No field mappings configured to preview', 'error');
            return;
        }
        
        let previewHtml = `
            <div class="bg-blue-50 rounded-lg p-6 border border-blue-200">
                <h3 class="text-lg font-semibold text-blue-800 mb-4">Mapping Preview</h3>
                <div class="space-y-2">
        `;
        
        Object.keys(mappingData).forEach(fieldName => {
            previewHtml += `
                <div class="flex justify-between items-center bg-white p-3 rounded border">
                    <span class="font-medium text-gray-700">${fieldName}</span>
                    <span class="text-blue-600 font-medium">→ ${mappingData[fieldName]}</span>
                </div>
            `;
        });
        
        previewHtml += `
                </div>
                <div class="mt-4 text-sm text-blue-700">
                    ${Object.keys(mappingData).length} field${Object.keys(mappingData).length !== 1 ? 's' : ''} will be sent to CTM
                </div>
            </div>
        `;
        
        previewContainer.innerHTML = previewHtml;
        previewContainer.classList.remove('hidden');
        
        showMappingMessage('Mapping preview generated', 'success');
    }
    
    function showMappingMessage(message, type = 'info') {
        // Create message element
        const messageDiv = document.createElement('div');
        messageDiv.className = `p-4 mb-4 rounded-lg border-l-4 ${
            type === 'success' ? 'bg-green-50 border-green-400 text-green-700' :
            type === 'error' ? 'bg-red-50 border-red-400 text-red-700' :
            'bg-blue-50 border-blue-400 text-blue-700'
        }`;
        
        messageDiv.innerHTML = `
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    ${type === 'success' ? 
                        '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>' :
                        type === 'error' ?
                        '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>' :
                        '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>'
                    }
                </svg>
                <span class="font-medium">${message}</span>
            </div>
        `;
        
        // Insert at top of mapping container's parent
        const container = document.querySelector('.mb-12');
        container.insertBefore(messageDiv, container.firstChild);
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            messageDiv.style.transition = 'opacity 0.5s ease';
            messageDiv.style.opacity = '0';
            setTimeout(() => {
                if (messageDiv.parentNode) {
                    messageDiv.parentNode.removeChild(messageDiv);
                }
            }, 500);
        }, 5000);
    }
});
</script>
<?php endif; ?> 