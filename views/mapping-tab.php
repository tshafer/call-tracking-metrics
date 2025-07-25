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
            <h2 class="text-2xl font-bold text-blue-800 tracking-tight"><?php _e('Field Mapping', 'call-tracking-metrics'); ?></h2>
        </div>
        
        <?php if ($plugin_count === 0): ?>
            <!-- No plugins installed -->
            <div class="text-center py-12">
                <div class="bg-gray-50 rounded-lg p-8 max-w-md mx-auto">
                    <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 15.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                    <h3 class="text-lg font-semibold text-gray-800 mb-2"><?php _e('No Form Plugins Installed', 'call-tracking-metrics'); ?></h3>
                    <p class="text-gray-600 mb-6"><?php _e('To use field mapping, you need to install at least one supported form plugin.', 'call-tracking-metrics'); ?></p>
                    <div class="space-y-3">
                        <a href="<?= esc_url(admin_url('plugin-install.php?s=contact+form+7&tab=search&type=term')) ?>" 
                           class="block bg-blue-600 hover:bg-blue-700 text-white font-bold px-6 py-3 rounded-lg shadow transition text-white!"><?php _e('Install Contact Form 7', 'call-tracking-metrics'); ?></a>
                        <a href="<?= esc_url(admin_url('plugin-install.php?s=gravity+forms&tab=search&type=term')) ?>" target="_blank" rel="noopener"
                           class="block bg-green-600 hover:bg-green-700 text-white font-bold px-6 py-3 rounded-lg shadow transition text-white!"><?php _e('Get Gravity Forms', 'call-tracking-metrics'); ?></a>
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
                            <label class="block mb-2 text-gray-600 font-medium"><?php _e('Form Type', 'call-tracking-metrics'); ?></label>
                            <select id="ctm_form_type" class="block w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500 text-base">
                                <option value=""><?php _e('Select form type...', 'call-tracking-metrics'); ?></option>
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
                            <label class="block mb-2 text-gray-600 font-medium"><?php _e('Form Type', 'call-tracking-metrics'); ?></label>
                            <select id="ctm_form_type" class="block w-full rounded-lg border-gray-300 bg-gray-50 text-base cursor-not-allowed" style="pointer-events: none;">
                                <option value="<?= esc_attr($single_key) ?>" selected><?php echo sprintf(__('Auto-selected: %s', 'call-tracking-metrics'), esc_html($single_name)); ?></option>
                            </select>
                        </div>
                    <?php endif; ?>
                    
                    <div>
                        <label class="block mb-2 text-gray-600 font-medium"><?php _e('Form', 'call-tracking-metrics'); ?></label>
                        <select id="ctm_form_id" class="block w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500 text-base">
                            <option value=""><?php _e('Select a form...', 'call-tracking-metrics'); ?></option>
                        </select>
                    </div>
                </div>
                
                <div id="ctm-mapping-table-container"></div>
                
                <div class="flex gap-4 mt-6">
                    <button type="button" id="ctm-save-mapping" class="bg-blue-600 hover:bg-blue-700 text-white font-bold px-6 py-2 rounded-lg shadow transition"><?php _e('Save Mapping', 'call-tracking-metrics'); ?></button>
                    <button type="button" id="ctm-preview-mapping" class="bg-gray-500 hover:bg-gray-600 text-white font-bold px-6 py-2 rounded-lg shadow transition"><?php _e('Preview', 'call-tracking-metrics'); ?></button>
                </div>
            </form>
            
            <div id="ctm-mapping-preview" class="mt-8 hidden"></div>
            
            <!-- Show install links for missing plugins -->
            <?php if (!$cf7_installed || !$gf_installed): ?>
                <div class="mt-8 pt-6 border-t border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4"><?php _e('Add More Form Plugins', 'call-tracking-metrics'); ?></h3>
                    <div class="flex gap-4">
                        <?php if (!$cf7_installed): ?>
                            <a href="<?= esc_url(admin_url('plugin-install.php?s=contact+form+7&tab=search&type=term')) ?>" 
                               class="text-blue-600 hover:text-blue-800 text-sm underline transition"><?php _e('Install Contact Form 7', 'call-tracking-metrics'); ?> &rarr;</a>
                        <?php endif; ?>
                        <?php if (!$gf_installed): ?>
                            <a href="https://www.gravityforms.com/" target="_blank" rel="noopener"
                               class="text-green-600 hover:text-green-800 text-sm underline transition"><?php _e('Get Gravity Forms', 'call-tracking-metrics'); ?> &rarr;</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
            
        <?php endif; ?>
    </div>
</div>
<script>
window.ctmMappingTabData = {
    plugin_count: <?= json_encode($plugin_count) ?>,
    admin_url: <?= json_encode(admin_url('admin-ajax.php')) ?>,
    nonce: <?= json_encode(wp_create_nonce('ctm_mapping_nonce')) ?>,
    translations: {
        select_form: <?php echo json_encode(__('Select a form...', 'call-tracking-metrics')); ?>,
        loading_forms: <?php echo json_encode(__('Loading forms...', 'call-tracking-metrics')); ?>,
        error_loading_forms: <?php echo json_encode(__('Error loading forms', 'call-tracking-metrics')); ?>,
        failed_load_forms: <?php echo json_encode(__('Failed to load forms', 'call-tracking-metrics')); ?>,
        network_error_forms: <?php echo json_encode(__('Network error while loading forms', 'call-tracking-metrics')); ?>,
        forms_loaded: <?php echo json_encode(__('Forms loaded successfully', 'call-tracking-metrics')); ?>,
        loading_fields: <?php echo json_encode(__('Loading fields...', 'call-tracking-metrics')); ?>,
        error_loading_fields: <?php echo json_encode(__('Error loading fields', 'call-tracking-metrics')); ?>,
        failed_load_fields: <?php echo json_encode(__('Failed to load form fields', 'call-tracking-metrics')); ?>,
        network_error_fields: <?php echo json_encode(__('Network error while loading fields', 'call-tracking-metrics')); ?>,
        fields_loaded: <?php echo json_encode(__('Form fields loaded successfully', 'call-tracking-metrics')); ?>,
        no_fields_found: <?php echo json_encode(__('No fields found for this form', 'call-tracking-metrics')); ?>,
        map_fields_title: <?php echo json_encode(__('Map Form Fields to CTM', 'call-tracking-metrics')); ?>,
        dont_map: <?php echo json_encode(__("Don't map", 'call-tracking-metrics')); ?>,
        phone: <?php echo json_encode(__('Phone Number', 'call-tracking-metrics')); ?>,
        email: <?php echo json_encode(__('Email Address', 'call-tracking-metrics')); ?>,
        full_name: <?php echo json_encode(__('Full Name', 'call-tracking-metrics')); ?>,
        first_name: <?php echo json_encode(__('First Name', 'call-tracking-metrics')); ?>,
        last_name: <?php echo json_encode(__('Last Name', 'call-tracking-metrics')); ?>,
        company: <?php echo json_encode(__('Company', 'call-tracking-metrics')); ?>,
        message: <?php echo json_encode(__('Message', 'call-tracking-metrics')); ?>,
        select_form_type_first: <?php echo json_encode(__('Please select a form type and form first', 'call-tracking-metrics')); ?>,
        saving: <?php echo json_encode(__('Saving...', 'call-tracking-metrics')); ?>,
        mapping_saved: <?php echo json_encode(__('Mapping saved successfully', 'call-tracking-metrics')); ?>,
        mapped_fields: <?php echo json_encode(__('Successfully mapped {count} field(s) for {formType} form', 'call-tracking-metrics')); ?>,
        failed_save_mapping: <?php echo json_encode(__('Failed to save mapping', 'call-tracking-metrics')); ?>,
        network_error_save: <?php echo json_encode(__('Network error occurred while saving mapping', 'call-tracking-metrics')); ?>,
        no_mappings_to_preview: <?php echo json_encode(__('No field mappings configured to preview', 'call-tracking-metrics')); ?>,
        mapping_preview_title: <?php echo json_encode(__('Mapping Preview', 'call-tracking-metrics')); ?>,
        mapping_preview_generated: <?php echo json_encode(__('Mapping preview generated', 'call-tracking-metrics')); ?>
    }
};
</script>
