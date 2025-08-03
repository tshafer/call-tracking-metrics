<?php
// Form Import tab view
$has_api_credentials = !empty($apiKey) && !empty($apiSecret);
$has_form_plugins = $cf7_available || $gf_available;
?>
<div class="mb-12">
    <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-8 mb-8">
        <div class="flex items-center mb-6 border-b border-blue-100 pb-4">
            <svg class="w-7 h-7 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
            </svg>
            <h2 class="text-2xl font-bold text-blue-800 tracking-tight"><?php _e('Import Forms from CallTrackingMetrics', 'call-tracking-metrics'); ?></h2>
        </div>
        
        <?php if (!$has_api_credentials): ?>
            <!-- No API credentials -->
            <div class="text-center py-12">
                <div class="bg-yellow-50 rounded-lg p-8 max-w-md mx-auto">
                    <svg class="w-12 h-12 text-yellow-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 15.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                    <h3 class="text-lg font-semibold text-yellow-800 mb-2"><?php _e('API Credentials Required', 'call-tracking-metrics'); ?></h3>
                    <p class="text-yellow-700 mb-6"><?php _e('You need to configure your CallTrackingMetrics API credentials to import forms.', 'call-tracking-metrics'); ?></p>
                    <a href="<?= esc_url(admin_url('admin.php?page=call-tracking-metrics&tab=api')) ?>" 
                       class="bg-yellow-600 hover:bg-yellow-700 text-white font-bold px-6 py-3 rounded-lg shadow transition"><?php _e('Configure API Settings', 'call-tracking-metrics'); ?></a>
                </div>
            </div>
        <?php elseif (!$has_form_plugins): ?>
            <!-- No form plugins installed -->
            <div class="text-center py-12">
                <div class="bg-gray-50 rounded-lg p-8 max-w-md mx-auto">
                    <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <h3 class="text-lg font-semibold text-gray-800 mb-2"><?php _e('No Form Plugins Installed', 'call-tracking-metrics'); ?></h3>
                    <p class="text-gray-600 mb-6"><?php _e('To import forms, you need to install at least one supported form plugin.', 'call-tracking-metrics'); ?></p>
                    <div class="space-y-3">
                        <a href="<?= esc_url(admin_url('plugin-install.php?s=contact+form+7&tab=search&type=term')) ?>" 
                           class="block bg-blue-600 hover:bg-blue-700 text-white font-bold px-6 py-3 rounded-lg shadow transition"><?php _e('Install Contact Form 7', 'call-tracking-metrics'); ?></a>
                        <a href="https://www.gravityforms.com/" target="_blank" rel="noopener"
                           class="block bg-green-600 hover:bg-green-700 text-white font-bold px-6 py-3 rounded-lg shadow transition"><?php _e('Get Gravity Forms', 'call-tracking-metrics'); ?></a>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- Import interface -->
            <div class="space-y-8">
                <!-- Step 1: Load Available Forms -->
                <div class="bg-blue-50 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-blue-800 mb-4"><?php _e('Step 1: Load Available Forms', 'call-tracking-metrics'); ?></h3>
                    <p class="text-blue-700 mb-4"><?php _e('Click the button below to fetch available forms from your CallTrackingMetrics account.', 'call-tracking-metrics'); ?></p>
                    <button type="button" id="ctm-load-forms" class="bg-blue-600 hover:bg-blue-700 text-white font-bold px-6 py-3 rounded-lg shadow transition">
                        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                        </svg>
                        <?php _e('Load Available Forms', 'call-tracking-metrics'); ?>
                    </button>
                    <div id="ctm-loading-forms" class="mt-4 hidden">
                        <div class="flex items-center text-blue-600">
                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <?php _e('Loading forms from CallTrackingMetrics...', 'call-tracking-metrics'); ?>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Select Form and Target -->
                <div id="ctm-form-selection" class="hidden">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4"><?php _e('Step 2: Select Form and Import Target', 'call-tracking-metrics'); ?></h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Form Selection -->
                        <div>
                            <label class="block mb-2 text-gray-600 font-medium"><?php _e('Select Form to Import', 'call-tracking-metrics'); ?></label>
                            <select id="ctm-form-select" class="block w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500 text-base">
                                <option value=""><?php _e('Choose a form...', 'call-tracking-metrics'); ?></option>
                            </select>
                        </div>

                        <!-- Target Type Selection -->
                        <div>
                            <label class="block mb-2 text-gray-600 font-medium"><?php _e('Import to', 'call-tracking-metrics'); ?></label>
                            <select id="ctm-target-type" class="block w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500 text-base">
                                <option value=""><?php _e('Select target...', 'call-tracking-metrics'); ?></option>
                                <?php if ($cf7_available): ?>
                                    <option value="cf7"><?php _e('Contact Form 7', 'call-tracking-metrics'); ?></option>
                                <?php endif; ?>
                                <?php if ($gf_available): ?>
                                    <option value="gf"><?php _e('Gravity Forms', 'call-tracking-metrics'); ?></option>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Form Title -->
                    <div class="mt-6">
                        <label class="block mb-2 text-gray-600 font-medium"><?php _e('Form Title', 'call-tracking-metrics'); ?></label>
                        <input type="text" id="ctm-form-title" class="block w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500 text-base" 
                               placeholder="<?php _e('Enter a title for the imported form...', 'call-tracking-metrics'); ?>">
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex gap-4 mt-6">
                        <button type="button" id="ctm-preview-form" class="bg-gray-500 hover:bg-gray-600 text-white font-bold px-6 py-3 rounded-lg shadow transition">
                            <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                            <?php _e('Preview Form', 'call-tracking-metrics'); ?>
                        </button>
                        <button type="button" id="ctm-import-form" class="bg-green-600 hover:bg-green-700 text-white font-bold px-6 py-3 rounded-lg shadow transition">
                            <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path>
                            </svg>
                            <?php _e('Import Form', 'call-tracking-metrics'); ?>
                        </button>
                    </div>
                </div>

                <!-- Preview Area -->
                <div id="ctm-preview-area" class="hidden">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4"><?php _e('Form Preview', 'call-tracking-metrics'); ?></h3>
                    <div id="ctm-preview-content" class="bg-gray-50 rounded-lg p-6 border border-gray-200"></div>
                </div>

                <!-- Import Status -->
                <div id="ctm-import-status" class="hidden">
                    <div id="ctm-import-loading" class="bg-blue-50 rounded-lg p-4 hidden">
                        <div class="flex items-center text-blue-600">
                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <?php _e('Importing form...', 'call-tracking-metrics'); ?>
                        </div>
                    </div>
                    <div id="ctm-import-success" class="bg-green-50 rounded-lg p-4 hidden">
                        <div class="flex items-center text-green-600">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span id="ctm-success-message"></span>
                        </div>
                    </div>
                    <div id="ctm-import-error" class="bg-red-50 rounded-lg p-4 hidden">
                        <div class="flex items-center text-red-600">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            <span id="ctm-error-message"></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Help Section -->
            <div class="mt-8 pt-6 border-t border-gray-200">
                <h3 class="text-lg font-semibold text-gray-700 mb-4"><?php _e('How Form Import Works', 'call-tracking-metrics'); ?></h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h4 class="font-semibold text-gray-800 mb-2"><?php _e('Contact Form 7', 'call-tracking-metrics'); ?></h4>
                        <ul class="text-sm text-gray-600 space-y-1">
                            <li>• <?php _e('Creates a new CF7 form with all fields', 'call-tracking-metrics'); ?></li>
                            <li>• <?php _e('Generates email templates automatically', 'call-tracking-metrics'); ?></li>
                            <li>• <?php _e('Maps field types appropriately', 'call-tracking-metrics'); ?></li>
                            <li>• <?php _e('Includes validation messages', 'call-tracking-metrics'); ?></li>
                        </ul>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h4 class="font-semibold text-gray-800 mb-2"><?php _e('Gravity Forms', 'call-tracking-metrics'); ?></h4>
                        <ul class="text-sm text-gray-600 space-y-1">
                            <li>• <?php _e('Creates a new GF form with all fields', 'call-tracking-metrics'); ?></li>
                            <li>• <?php _e('Sets up notifications automatically', 'call-tracking-metrics'); ?></li>
                            <li>• <?php _e('Configures confirmations', 'call-tracking-metrics'); ?></li>
                            <li>• <?php _e('Maps field types and options', 'call-tracking-metrics'); ?></li>
                        </ul>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    let availableForms = [];
    const nonce = '<?php echo wp_create_nonce('ctm_form_import_nonce'); ?>';

    // Load available forms
    $('#ctm-load-forms').on('click', function() {
        const button = $(this);
        const loading = $('#ctm-loading-forms');
        
        button.prop('disabled', true);
        loading.removeClass('hidden');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'ctm_get_available_forms',
                nonce: nonce
            },
            success: function(response) {
                if (response.success) {
                    availableForms = response.data.forms;
                    populateFormSelect();
                    $('#ctm-form-selection').removeClass('hidden');
                    showMessage('success', response.data.message);
                } else {
                    showMessage('error', response.data.message);
                }
            },
            error: function() {
                showMessage('error', 'Failed to load forms. Please try again.');
            },
            complete: function() {
                button.prop('disabled', false);
                loading.addClass('hidden');
            }
        });
    });

    // Populate form select
    function populateFormSelect() {
        const select = $('#ctm-form-select');
        select.empty();
        select.append('<option value=""><?php _e('Choose a form...', 'call-tracking-metrics'); ?></option>');
        
        availableForms.forEach(function(form) {
            select.append(`<option value="${form.id}" data-form='${JSON.stringify(form)}'>${form.name}</option>`);
        });
    }

    // Preview form
    $('#ctm-preview-form').on('click', function() {
        const formId = $('#ctm-form-select').val();
        const targetType = $('#ctm-target-type').val();
        
        if (!formId || !targetType) {
            showMessage('error', 'Please select both a form and target type.');
            return;
        }
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'ctm_preview_form',
                nonce: nonce,
                ctm_form_id: formId,
                target_type: targetType
            },
            success: function(response) {
                if (response.success) {
                    $('#ctm-preview-content').html(response.data.preview);
                    $('#ctm-preview-area').removeClass('hidden');
                } else {
                    showMessage('error', response.data.message);
                }
            },
            error: function() {
                showMessage('error', 'Failed to generate preview. Please try again.');
            }
        });
    });

    // Import form
    $('#ctm-import-form').on('click', function() {
        const formId = $('#ctm-form-select').val();
        const targetType = $('#ctm-target-type').val();
        const formTitle = $('#ctm-form-title').val();
        
        if (!formId || !targetType || !formTitle) {
            showMessage('error', 'Please fill in all required fields.');
            return;
        }
        
        const button = $(this);
        const loading = $('#ctm-import-loading');
        const success = $('#ctm-import-success');
        const error = $('#ctm-import-error');
        
        button.prop('disabled', true);
        loading.removeClass('hidden');
        success.addClass('hidden');
        error.addClass('hidden');
        $('#ctm-import-status').removeClass('hidden');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'ctm_import_form',
                nonce: nonce,
                ctm_form_id: formId,
                target_type: targetType,
                form_title: formTitle
            },
            success: function(response) {
                if (response.success) {
                    $('#ctm-success-message').text(response.data.message);
                    success.removeClass('hidden');
                    // Reset form
                    $('#ctm-form-select').val('');
                    $('#ctm-target-type').val('');
                    $('#ctm-form-title').val('');
                    $('#ctm-preview-area').addClass('hidden');
                } else {
                    $('#ctm-error-message').text(response.data.message);
                    error.removeClass('hidden');
                }
            },
            error: function() {
                $('#ctm-error-message').text('Import failed. Please try again.');
                error.removeClass('hidden');
            },
            complete: function() {
                button.prop('disabled', false);
                loading.addClass('hidden');
            }
        });
    });

    // Auto-populate form title when form is selected
    $('#ctm-form-select').on('change', function() {
        const selectedOption = $(this).find('option:selected');
        if (selectedOption.val()) {
            const formData = JSON.parse(selectedOption.attr('data-form'));
            $('#ctm-form-title').val(formData.name);
        }
    });

    // Show message helper
    function showMessage(type, message) {
        const alertClass = type === 'success' ? 'bg-green-50 text-green-800' : 'bg-red-50 text-red-800';
        const alert = $(`<div class="rounded-lg p-4 mb-4 ${alertClass}">${message}</div>`);
        
        $('.bg-white').first().prepend(alert);
        
        setTimeout(function() {
            alert.fadeOut(function() {
                $(this).remove();
            });
        }, 5000);
    }
});
</script>

<style>
.ctm-preview-cf7, .ctm-preview-gf {
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
}

.form-field {
    margin-bottom: 1rem;
}

.form-field label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: #374151;
}

.form-field input,
.form-field textarea,
.form-field select {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 0.5rem;
    background-color: #f9fafb;
    color: #6b7280;
}

.form-field button {
    background-color: #3b82f6;
    color: white;
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 0.5rem;
    font-weight: 500;
    cursor: not-allowed;
    opacity: 0.6;
}
</style> 