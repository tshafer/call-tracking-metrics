// JavaScript for the Field Mapping tab
// Expects window.ctmMappingTabData to be set in the view with needed values

document.addEventListener('DOMContentLoaded', function() {
    const data = window.ctmMappingTabData || {};
    const pluginCount = data.plugin_count || 0;
    const adminUrl = data.admin_url;
    const nonce = data.nonce;
    const translations = data.translations || {};

    // Auto-trigger form loading for single plugin
    if (pluginCount === 1) {
        setTimeout(function() {
            const formTypeSelect = document.getElementById('ctm_form_type');
            if (formTypeSelect && formTypeSelect.value) {
                const event = new Event('change', { bubbles: true });
                formTypeSelect.dispatchEvent(event);
                if (typeof jQuery !== 'undefined') {
                    jQuery(formTypeSelect).trigger('change');
                }
            }
        }, 100);
    }

    if (pluginCount > 0) {
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
                formIdSelect.innerHTML = `<option value="">${translations.select_form || 'Select a form...'}</option>`;
                return;
            }
            formIdSelect.innerHTML = `<option value="">${translations.loading_forms || 'Loading forms...'}</option>`;
            fetch(adminUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=ctm_get_forms&form_type=${encodeURIComponent(formType)}&nonce=${encodeURIComponent(nonce)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data) {
                    let options = `<option value="">${translations.select_form || 'Select a form...'}</option>`;
                    data.data.forEach(form => {
                        options += `<option value="${form.id}">${form.title}</option>`;
                    });
                    formIdSelect.innerHTML = options;
                    ctmShowToast(translations.forms_loaded || 'Forms loaded successfully', 'success');
                } else {
                    formIdSelect.innerHTML = `<option value="">${translations.error_loading_forms || 'Error loading forms'}</option>`;
                    ctmShowToast((data.data && data.data.message) || translations.failed_load_forms || 'Failed to load forms', 'error');
                }
            })
            .catch(error => {
                console.error('Error loading forms:', error);
                formIdSelect.innerHTML = `<option value="">${translations.error_loading_forms || 'Error loading forms'}</option>`;
                ctmShowToast(translations.network_error_forms || 'Network error while loading forms', 'error');
            });
        }
        function loadFields() {
            const formType = formTypeSelect.value;
            const formId = formIdSelect.value;
            if (!formType || !formId) {
                mappingContainer.innerHTML = '';
                return;
            }
            mappingContainer.innerHTML = `<div class="text-center py-4">${translations.loading_fields || 'Loading fields...'}</div>`;
            fetch(adminUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=ctm_get_fields&form_type=${encodeURIComponent(formType)}&form_id=${encodeURIComponent(formId)}&nonce=${encodeURIComponent(nonce)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data) {
                    displayFieldMapping(data.data);
                    ctmShowToast(translations.fields_loaded || 'Form fields loaded successfully', 'success');
                } else {
                    mappingContainer.innerHTML = `<div class="text-red-600 text-center py-4">${translations.error_loading_fields || 'Error loading fields'}</div>`;
                    ctmShowToast((data.data && data.data.message) || translations.failed_load_fields || 'Failed to load form fields', 'error');
                }
            })
            .catch(error => {
                console.error('Error loading fields:', error);
                mappingContainer.innerHTML = `<div class="text-red-600 text-center py-4">${translations.network_error_fields || 'Network error loading fields'}</div>`;
                ctmShowToast(translations.network_error_fields || 'Network error while loading fields', 'error');
            });
        }
        function displayFieldMapping(fields) {
            if (!fields || fields.length === 0) {
                mappingContainer.innerHTML = `<div class="text-gray-600 text-center py-4">${translations.no_fields_found || 'No fields found for this form'}</div>`;
                return;
            }
            let html = `
                <div class="bg-gray-50 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">${translations.map_fields_title || 'Map Form Fields to CTM'}</h3>
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
                            <option value="">${translations.dont_map || "Don't map"}</option>
                            <option value="phone">${translations.phone || 'Phone Number'}</option>
                            <option value="email">${translations.email || 'Email Address'}</option>
                            <option value="name">${translations.full_name || 'Full Name'}</option>
                            <option value="first_name">${translations.first_name || 'First Name'}</option>
                            <option value="last_name">${translations.last_name || 'Last Name'}</option>
                            <option value="company">${translations.company || 'Company'}</option>
                            <option value="message">${translations.message || 'Message'}</option>
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
                ctmShowToast(translations.select_form_type_first || 'Please select a form type and form first', 'error');
                return;
            }
            const originalText = saveBtn.textContent;
            saveBtn.disabled = true;
            saveBtn.textContent = translations.saving || 'Saving...';
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
            formData.append('nonce', nonce);
            Object.keys(mappingData).forEach(key => {
                formData.append(`mapping[${key}]`, mappingData[key]);
            });
            fetch(adminUrl, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    ctmShowToast((data.data && data.data.message) || translations.mapping_saved || 'Mapping saved successfully', 'success');
                    const mappedFields = Object.keys(mappingData).length;
                    if (mappedFields > 0) {
                        setTimeout(() => {
                            ctmShowToast(
                                (translations.mapped_fields || 'Successfully mapped {count} field(s) for {formType} form')
                                    .replace('{count}', mappedFields)
                                    .replace('{formType}', formType.toUpperCase()),
                                'info'
                            );
                        }, 1000);
                    }
                } else {
                    ctmShowToast((data.data && data.data.message) || translations.failed_save_mapping || 'Failed to save mapping', 'error');
                }
            })
            .catch(error => {
                console.error('Error saving mapping:', error);
                ctmShowToast(translations.network_error_save || 'Network error occurred while saving mapping', 'error');
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
                ctmShowToast(translations.select_form_type_first || 'Please select a form type and form first', 'error');
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
                ctmShowToast(translations.no_mappings_to_preview || 'No field mappings configured to preview', 'error');
                return;
            }
            let previewHtml = `
                <div class="bg-blue-50 rounded-lg p-6 border border-blue-200">
                    <h3 class="text-lg font-semibold text-blue-800 mb-4">${translations.mapping_preview_title || 'Mapping Preview'}</h3>
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
            ctmShowToast(translations.mapping_preview_generated || 'Mapping preview generated', 'success');
        }
        function ctmShowToast(message, type = 'info') {
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
            const container = document.querySelector('.mb-12');
            container.insertBefore(messageDiv, container.firstChild);
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
    }
}); 