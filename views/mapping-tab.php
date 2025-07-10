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