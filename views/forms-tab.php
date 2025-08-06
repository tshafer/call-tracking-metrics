<?php
/**
 * Forms Management Tab Template
 * 
 * Displays existing Contact Form 7 and Gravity Forms with management options.
 * 
 * @since 2.0.0
 * @var bool $cf7_available Whether Contact Form 7 is available
 * @var bool $gf_available Whether Gravity Forms is available
 * @var array $cf7_forms Array of Contact Form 7 forms
 * @var array $gf_forms Array of Gravity Forms
 * @var string $apiKey The CTM API key
 * @var string $apiSecret The CTM API secret
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Helper function to truncate long CTM form IDs
if (!function_exists('truncate_ctm_id')) {
function truncate_ctm_id($id, $max_length = 20) {
    if (empty($id) || $id === 'N/A') {
        return $id;
    }
    
    if (strlen($id) <= $max_length) {
        return $id;
    }
    
    $start_length = 8;
    $end_length = 8;
    
    return substr($id, 0, $start_length) . '...' . substr($id, -$end_length);
}
}
?>

<div class="forms-management-container">
    <!-- Header Section -->
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-gray-800 mb-2"><?php _e('Manage Forms', 'call-tracking-metrics'); ?></h2>
        <p class="text-gray-600"><?php _e('View and manage your existing Contact Form 7 and Gravity Forms. Edit them in WordPress or link them to CallTrackingMetrics.', 'call-tracking-metrics'); ?></p>
    </div>

    <!-- Forms Status Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <!-- CF7 Status Card -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center mb-4">
                <div class="bg-blue-100 rounded-full p-3 mr-4">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-800"><?php _e('Contact Form 7', 'call-tracking-metrics'); ?></h3>
                    <p class="text-gray-500 text-sm">
                        <?php if ($cf7_available): ?>
                            <?php printf(_n('%d form found', '%d forms found', count($cf7_forms), 'call-tracking-metrics'), count($cf7_forms)); ?>
                        <?php else: ?>
                            <?php _e('Not installed', 'call-tracking-metrics'); ?>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
            <?php if (!$cf7_available): ?>
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <p class="text-yellow-800 text-sm mb-3"><?php _e('Contact Form 7 is not installed or activated.', 'call-tracking-metrics'); ?></p>
                    <a href="<?php echo admin_url('plugin-install.php?s=contact+form+7&tab=search&type=term'); ?>" class="inline-flex items-center px-3 py-1 text-sm font-medium text-yellow-800 bg-yellow-100 rounded-md hover:bg-yellow-200 transition-colors">
                        <?php _e('Install Contact Form 7', 'call-tracking-metrics'); ?>
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <!-- GF Status Card -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center mb-4">
                <div class="bg-green-100 rounded-full p-3 mr-4">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V9a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-800"><?php _e('Gravity Forms', 'call-tracking-metrics'); ?></h3>
                    <p class="text-gray-500 text-sm">
                        <?php if ($gf_available): ?>
                            <?php printf(_n('%d form found', '%d forms found', count($gf_forms), 'call-tracking-metrics'), count($gf_forms)); ?>
                        <?php else: ?>
                            <?php _e('Not installed', 'call-tracking-metrics'); ?>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
            <?php if (!$gf_available): ?>
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <p class="text-yellow-800 text-sm mb-3"><?php _e('Gravity Forms is not installed or activated.', 'call-tracking-metrics'); ?></p>
                    <a href="https://www.gravityforms.com/" target="_blank" class="inline-flex items-center px-3 py-1 text-sm font-medium text-yellow-800 bg-yellow-100 rounded-md hover:bg-yellow-200 transition-colors">
                        <?php _e('Get Gravity Forms', 'call-tracking-metrics'); ?>
                        <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                        </svg>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Contact Form 7 Forms List -->
    <?php if ($cf7_available && !empty($cf7_forms)): ?>
    <div class="mb-8">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                    <svg class="w-5 h-5 text-blue-600 mr-2 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <?php _e('Contact Form 7 Forms', 'call-tracking-metrics'); ?>
                </h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <?php foreach ($cf7_forms as $form): ?>
                    <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                        <div class="flex items-start justify-between mb-3">
                            <h4 class="font-medium text-gray-900 truncate mr-2"><?php echo esc_html($form['title']); ?></h4>
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 whitespace-nowrap">
                                CF7
                            </span>
                        </div>
                        
                        <div class="space-y-1 mb-4 text-sm text-gray-500">
                            <div class="flex justify-between">
                                <span><?php _e('WP ID:', 'call-tracking-metrics'); ?></span>
                                <span>#<?php echo esc_html($form['id']); ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span><?php _e('CTM ID:', 'call-tracking-metrics'); ?></span>
                                <span title="<?php echo esc_attr($form['ctm_form_id'] ?: 'N/A'); ?>"><?php echo esc_html(truncate_ctm_id($form['ctm_form_id'] ?: 'N/A')); ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span><?php _e('Imported:', 'call-tracking-metrics'); ?></span>
                                <span><?php echo esc_html(date('M j, Y', strtotime($form['ctm_import_date'] ?: $form['date_created']))); ?></span>
                            </div>
                        </div>
                        
                        <div class="space-y-2">
                            <!-- Preview Form -->
                            <button type="button" 
                                    class="w-full inline-flex items-center justify-center px-3 py-2 text-sm font-medium text-gray-600 bg-gray-50 rounded-md hover:bg-gray-100 hover:text-gray-700 transition-colors ctm-preview-wp-form"
                                    data-form-id="<?php echo esc_attr($form['id']); ?>" 
                                    data-form-type="cf7"
                                    data-form-title="<?php echo esc_attr($form['title']); ?>">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                <?php _e('Preview Form', 'call-tracking-metrics'); ?>
                            </button>
                            
                            <!-- Edit in WordPress -->
                            <a href="<?php echo admin_url('admin.php?page=wpcf7&post=' . $form['id'] . '&action=edit'); ?>" 
                               class="w-full inline-flex items-center justify-center px-3 py-2 text-sm font-medium text-blue-600 bg-blue-50 rounded-md hover:bg-blue-100 hover:text-blue-700 transition-colors">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                                <?php _e('Edit in WP', 'call-tracking-metrics'); ?>
                            </a>
                            
                            <!-- CTM Actions -->
                            <?php if ($apiKey && $apiSecret): ?>
                                <?php if ($form['ctm_form_id']): ?>
                                    <div class="flex space-x-2">
                                        <!-- Edit in CTM -->
                                        <button type="button" 
                                                class="flex-1 inline-flex items-center justify-center px-3 py-2 text-sm font-medium text-green-600 bg-green-50 rounded-md hover:bg-green-100 hover:text-green-700 transition-colors ctm-link-form"
                                                data-form-id="<?php echo esc_attr($form['id']); ?>" 
                                                data-form-type="cf7"
                                                data-form-title="<?php echo esc_attr($form['title']); ?>"
                                                data-ctm-form-id="<?php echo esc_attr($form['ctm_form_id']); ?>">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                                            </svg>
                                            <?php _e('Edit in CTM', 'call-tracking-metrics'); ?>
                                        </button>
                                        
                                        <!-- Update from CTM -->
                                        <button type="button" 
                                                class="flex-1 inline-flex items-center justify-center px-3 py-2 text-sm font-medium text-orange-600 bg-orange-50 rounded-md hover:bg-orange-100 hover:text-orange-700 transition-colors ctm-update-form"
                                                data-form-id="<?php echo esc_attr($form['id']); ?>" 
                                                data-form-type="cf7"
                                                data-form-title="<?php echo esc_attr($form['title']); ?>"
                                                data-ctm-form-id="<?php echo esc_attr($form['ctm_form_id']); ?>">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                            </svg>
                                            <?php _e('Update from CTM', 'call-tracking-metrics'); ?>
                                        </button>
                                    </div>
                                <?php else: ?>
                                    <!-- Sync with CTM (not yet synced) -->
                                    <button type="button" 
                                            class="w-full inline-flex items-center justify-center px-3 py-2 text-sm font-medium text-purple-600 bg-purple-50 rounded-md hover:bg-purple-100 hover:text-purple-700 transition-colors ctm-sync-form"
                                            data-form-id="<?php echo esc_attr($form['id']); ?>" 
                                            data-form-type="cf7"
                                            data-form-title="<?php echo esc_attr($form['title']); ?>">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                        </svg>
                                        <?php _e('Sync with CTM', 'call-tracking-metrics'); ?>
                                    </button>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Gravity Forms List -->
    <?php if ($gf_available && !empty($gf_forms)): ?>
    <div class="mb-8">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                    <svg class="w-5 h-5 text-green-600 mr-2 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V9a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    <?php _e('Gravity Forms', 'call-tracking-metrics'); ?>
                </h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <?php foreach ($gf_forms as $form): ?>
                    <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                        <div class="flex items-start justify-between mb-3">
                            <h4 class="font-medium text-gray-900 truncate mr-2"><?php echo esc_html($form['title']); ?></h4>
                            <div class="flex items-center space-x-2">
                                <?php if ($form['is_active']): ?>
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 whitespace-nowrap">
                                        <?php _e('Active', 'call-tracking-metrics'); ?>
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 whitespace-nowrap">
                                        <?php _e('Inactive', 'call-tracking-metrics'); ?>
                                    </span>
                                <?php endif; ?>
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 whitespace-nowrap">
                                    GF
                                </span>
                            </div>
                        </div>
                        
                        <div class="space-y-1 mb-4 text-sm text-gray-500">
                            <div class="flex justify-between">
                                <span><?php _e('WP ID:', 'call-tracking-metrics'); ?></span>
                                <span>#<?php echo esc_html($form['id']); ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span><?php _e('CTM ID:', 'call-tracking-metrics'); ?></span>
                                <span title="<?php echo esc_attr($form['ctm_form_id'] ?: 'N/A'); ?>"><?php echo esc_html(truncate_ctm_id($form['ctm_form_id'] ?: 'N/A')); ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span><?php _e('Entries:', 'call-tracking-metrics'); ?></span>
                                <span><?php echo number_format($form['entries']); ?></span>
                            </div>
                            <div class="flex justify-between">
                                <span><?php _e('Imported:', 'call-tracking-metrics'); ?></span>
                                <span><?php echo esc_html(date('M j, Y', strtotime($form['ctm_import_date'] ?: $form['date_created']))); ?></span>
                            </div>
                        </div>
                        
                        <div class="space-y-2">
                            <!-- Preview Form -->
                            <button type="button" 
                                    class="w-full inline-flex items-center justify-center px-3 py-2 text-sm font-medium text-gray-600 bg-gray-50 rounded-md hover:bg-gray-100 hover:text-gray-700 transition-colors ctm-preview-wp-form"
                                    data-form-id="<?php echo esc_attr($form['id']); ?>" 
                                    data-form-type="gf"
                                    data-form-title="<?php echo esc_attr($form['title']); ?>">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                <?php _e('Preview Form', 'call-tracking-metrics'); ?>
                            </button>
                            
                            <!-- Edit in WordPress -->
                            <a href="<?php echo admin_url('admin.php?page=gf_edit_forms&id=' . $form['id']); ?>" 
                               class="w-full inline-flex items-center justify-center px-3 py-2 text-sm font-medium text-blue-600 bg-blue-50 rounded-md hover:bg-blue-100 hover:text-blue-700 transition-colors">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                                <?php _e('Edit in WP', 'call-tracking-metrics'); ?>
                            </a>
                            
                            <!-- CTM Actions -->
                            <?php if ($apiKey && $apiSecret): ?>
                                <?php if ($form['ctm_form_id']): ?>
                                    <div class="flex space-x-2">
                                        <!-- Edit in CTM -->
                                        <button type="button" 
                                                class="flex-1 inline-flex items-center justify-center px-3 py-2 text-sm font-medium text-green-600 bg-green-50 rounded-md hover:bg-green-100 hover:text-green-700 transition-colors ctm-link-form"
                                                data-form-id="<?php echo esc_attr($form['id']); ?>" 
                                                data-form-type="gf"
                                                data-form-title="<?php echo esc_attr($form['title']); ?>"
                                                data-ctm-form-id="<?php echo esc_attr($form['ctm_form_id']); ?>">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                                            </svg>
                                            <?php _e('Edit in CTM', 'call-tracking-metrics'); ?>
                                        </button>
                                        
                                        <!-- Update from CTM -->
                                        <button type="button" 
                                                class="flex-1 inline-flex items-center justify-center px-3 py-2 text-sm font-medium text-orange-600 bg-orange-50 rounded-md hover:bg-orange-100 hover:text-orange-700 transition-colors ctm-update-form"
                                                data-form-id="<?php echo esc_attr($form['id']); ?>" 
                                                data-form-type="gf"
                                                data-form-title="<?php echo esc_attr($form['title']); ?>"
                                                data-ctm-form-id="<?php echo esc_attr($form['ctm_form_id']); ?>">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                            </svg>
                                            <?php _e('Update from CTM', 'call-tracking-metrics'); ?>
                                        </button>
                                    </div>
                                <?php else: ?>
                                    <!-- Sync with CTM (not yet synced) -->
                                    <button type="button" 
                                            class="w-full inline-flex items-center justify-center px-3 py-2 text-sm font-medium text-purple-600 bg-purple-50 rounded-md hover:bg-purple-100 hover:text-purple-700 transition-colors ctm-sync-form"
                                            data-form-id="<?php echo esc_attr($form['id']); ?>" 
                                            data-form-type="gf"
                                            data-form-title="<?php echo esc_attr($form['title']); ?>">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                        </svg>
                                        <?php _e('Sync with CTM', 'call-tracking-metrics'); ?>
                                    </button>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- No Forms Message -->
    <?php if (($cf7_available && empty($cf7_forms)) && ($gf_available && empty($gf_forms))): ?>
    <div class="text-center py-12">
        <div class="bg-gray-50 rounded-lg p-8">
            <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <h3 class="text-lg font-medium text-gray-900 mb-2"><?php _e('No Forms Found', 'call-tracking-metrics'); ?></h3>
            <p class="text-gray-500 mb-4"><?php _e('You haven\'t imported any forms from CallTrackingMetrics yet. Get started by importing your first form.', 'call-tracking-metrics'); ?></p>
            <div class="flex justify-center">
                <a href="<?php echo admin_url('admin.php?page=call-tracking-metrics&tab=import'); ?>" class="inline-flex items-center px-6 py-3 text-sm font-medium !text-white bg-blue-600 rounded-md hover:bg-blue-700 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    <?php _e('Import Forms from CTM', 'call-tracking-metrics'); ?>
                </a>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- CTM Link Modal -->
<div id="ctm-link-modal" class="fixed inset-0 bg-black bg-opacity-60 overflow-y-auto h-full w-full hidden" style="z-index: 999999;">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100">
                <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                </svg>
            </div>
            <h3 class="text-lg leading-6 font-medium text-gray-900 mt-2"><?php _e('Edit Form in CallTrackingMetrics', 'call-tracking-metrics'); ?></h3>
            <div class="mt-4 px-6 py-3">
                <div class="bg-gray-50 rounded-lg p-4 mb-4">
                    <div class="text-sm font-medium text-gray-900 mb-1" id="ctm-modal-form-title">Form Name</div>
                    <div class="text-xs text-gray-500">
                        <span class="font-medium">CTM ID:</span> 
                        <span id="ctm-modal-form-id" class="font-mono">Loading...</span>
                    </div>
                </div>
                <p class="text-sm text-gray-600 text-center">
                    <?php _e('This will open the form editor in CallTrackingMetrics in a new tab.', 'call-tracking-metrics'); ?>
                </p>
            </div>
            <div class="items-center px-4 py-3">
                <button id="ctm-link-confirm" class="px-4 py-2 bg-green-500 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-300">
                    <?php _e('Open Form Editor', 'call-tracking-metrics'); ?>
                </button>
                <button id="ctm-link-cancel" class="mt-3 px-4 py-2 bg-gray-300 text-gray-800 text-base font-medium rounded-md w-full shadow-sm hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-300">
                    <?php _e('Cancel', 'call-tracking-metrics'); ?>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- CTM Sync Modal -->
<div id="ctm-sync-modal" class="fixed inset-0 bg-black bg-opacity-60 overflow-y-auto h-full w-full hidden" style="z-index: 999999;">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-purple-100">
                <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
            </div>
            <h3 class="text-lg leading-6 font-medium text-gray-900 mt-2"><?php _e('Sync Form with CallTrackingMetrics', 'call-tracking-metrics'); ?></h3>
            <div class="mt-4 px-6 py-3">
                <div class="bg-gray-50 rounded-lg p-4 mb-4">
                    <div class="text-sm font-medium text-gray-900 mb-1" id="ctm-sync-form-title">Form Name</div>
                    <div class="text-xs text-gray-500 mb-3">
                        <span class="font-medium">WordPress Form Type:</span> 
                        <span id="ctm-sync-form-type" class="font-mono">Type</span>
                    </div>
                    <div class="text-xs text-gray-600">
                        <?php _e('Select a CallTrackingMetrics form to sync with this WordPress form:', 'call-tracking-metrics'); ?>
                    </div>
                </div>
                
                <div class="mb-4">
                    <select id="ctm-sync-form-select" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                        <option value=""><?php _e('Loading CTM forms...', 'call-tracking-metrics'); ?></option>
                    </select>
                </div>
                
                <p class="text-sm text-gray-600 text-center">
                    <?php _e('This will link your WordPress form to the selected CallTrackingMetrics form for tracking.', 'call-tracking-metrics'); ?>
                </p>
            </div>
            <div class="items-center px-4 py-3">
                <button id="ctm-sync-confirm" class="px-4 py-2 bg-purple-500 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-purple-600 focus:outline-none focus:ring-2 focus:ring-purple-300 disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                    <?php _e('Sync Forms', 'call-tracking-metrics'); ?>
                </button>
                <button id="ctm-sync-cancel" class="mt-3 px-4 py-2 bg-gray-300 text-gray-800 text-base font-medium rounded-md w-full shadow-sm hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-300">
                    <?php _e('Cancel', 'call-tracking-metrics'); ?>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Form Preview Modal -->
<div id="ctm-preview-modal" class="fixed inset-0 bg-black bg-opacity-60 hidden items-center justify-center" style="z-index: 999999;">
    <div class="bg-white rounded-lg max-w-4xl w-full mx-4 max-h-[90vh] flex flex-col">
        <!-- Modal Header -->
        <div class="flex items-center justify-between p-6 border-b border-gray-200">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-gray-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                </svg>
                <h3 class="text-lg font-semibold text-gray-900"><?php _e('Form Preview', 'call-tracking-metrics'); ?></h3>
                <span class="ml-2 text-sm text-gray-500">-</span>
                <span id="ctm-preview-form-title" class="ml-1 text-sm font-medium text-gray-700"></span>
            </div>
            <button type="button" id="ctm-preview-close" class="text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        
        <!-- Modal Body -->
        <div class="flex-1 overflow-y-auto p-6">
            <!-- Loading State -->
            <div id="ctm-preview-loading" class="text-center py-12">
                <svg class="animate-spin h-8 w-8 text-gray-400 mx-auto mb-4" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <p class="text-gray-500"><?php _e('Loading form preview...', 'call-tracking-metrics'); ?></p>
            </div>
            
            <!-- Preview Content -->
            <div id="ctm-preview-content" class="hidden">
                <!-- Form preview will be loaded here -->
            </div>
            
            <!-- Error State -->
            <div id="ctm-preview-error" class="hidden text-center py-12">
                <svg class="w-12 h-12 text-red-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.464 0L4.35 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
                <p class="text-red-600 font-medium mb-2"><?php _e('Failed to load preview', 'call-tracking-metrics'); ?></p>
                <p id="ctm-preview-error-message" class="text-gray-500 text-sm"></p>
            </div>
        </div>
        
        <!-- Modal Footer -->
        <div class="border-t border-gray-200 px-6 py-4">
            <div class="flex justify-between items-center">
                <div class="text-xs text-gray-500">
                    <?php _e('This is a preview only. Changes made here will not be saved.', 'call-tracking-metrics'); ?>
                </div>
                <button type="button" id="ctm-preview-close-btn" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200 transition-colors">
                    <?php _e('Close', 'call-tracking-metrics'); ?>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Handle CTM Link buttons
    $('.ctm-link-form').on('click', function() {
        const formId = $(this).data('form-id');
        const formType = $(this).data('form-type');
        const formTitle = $(this).data('form-title');
        const ctmFormId = $(this).data('ctm-form-id');
        
        // Use truncated CTM ID for better display
        const truncatedCtmId = ctmFormId.length > 20 ? 
            ctmFormId.substring(0, 8) + '...' + ctmFormId.substring(ctmFormId.length - 8) : 
            ctmFormId;
        
        // Update modal content
        console.log('Updating modal with:', { formTitle, ctmFormId, truncatedCtmId });
        $('#ctm-modal-form-title').text(formTitle);
        $('#ctm-modal-form-id').text(truncatedCtmId).attr('title', ctmFormId);
        
        // Debug: Verify the update worked
        console.log('Modal form ID element content:', $('#ctm-modal-form-id').text());
        
        // Show modal and prevent body scrolling
        $('#ctm-link-modal').removeClass('hidden');
        $('body').addClass('overflow-hidden');
        
        // Store form data for the confirm action
        $('#ctm-link-confirm').data('form-id', formId).data('form-type', formType).data('form-title', formTitle).data('ctm-form-id', ctmFormId);
    });
    
    // Handle confirm link
    $('#ctm-link-confirm').on('click', function() {
        const formId = $(this).data('form-id');
        const formType = $(this).data('form-type');
        const formTitle = $(this).data('form-title');
        const ctmFormId = $(this).data('ctm-form-id');
        
        // Open CTM form editor in a new tab with specific form ID
        const ctmUrl = ctmFormId ? 
            'https://app.calltrackingmetrics.com/form_reactors/' + ctmFormId + '/edit' : 
            'https://app.calltrackingmetrics.com/form_reactors';
        window.open(ctmUrl, '_blank');
        
        $('#ctm-link-modal').addClass('hidden');
        $('body').removeClass('overflow-hidden');
        
        // Optional: Show success message
        $('body').append('<div class="fixed top-4 right-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded" style="z-index: 999999;" id="ctm-success-toast">' +
            '<span class="block sm:inline"><?php _e('CallTrackingMetrics opened in new tab. You can now link your form.', 'call-tracking-metrics'); ?></span>' +
            '</div>');
        
        setTimeout(function() {
            $('#ctm-success-toast').fadeOut(function() {
                $(this).remove();
            });
        }, 5000);
    });
    
    // Handle cancel
    $('#ctm-link-cancel').on('click', function() {
        $('#ctm-link-modal').addClass('hidden');
        $('body').removeClass('overflow-hidden');
    });
    
    // Close modal when clicking outside
    $('#ctm-link-modal').on('click', function(e) {
        if (e.target === this) {
            $(this).addClass('hidden');
            $('body').removeClass('overflow-hidden');
        }
    });
    
    // Handle CTM Sync buttons
    $('.ctm-sync-form').on('click', function() {
        const formId = $(this).data('form-id');
        const formType = $(this).data('form-type');
        const formTitle = $(this).data('form-title');
        
        // Update sync modal content
        $('#ctm-sync-form-title').text(formTitle);
        $('#ctm-sync-form-type').text(formType.toUpperCase());
        
        // Show sync modal and prevent body scrolling
        $('#ctm-sync-modal').removeClass('hidden');
        $('body').addClass('overflow-hidden');
        
        // Load CTM forms for selection
        loadCTMFormsForSync();
        
        // Store form data for the sync action
        $('#ctm-sync-confirm').data('form-id', formId).data('form-type', formType).data('form-title', formTitle);
    });
    
    // Load CTM forms for sync selection
    function loadCTMFormsForSync() {
        const select = $('#ctm-sync-form-select');
        select.html('<option value=""><?php _e('Loading CTM forms...', 'call-tracking-metrics'); ?></option>');
        $('#ctm-sync-confirm').prop('disabled', true);
        
        // AJAX call to get available CTM forms
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'ctm_get_available_forms',
                nonce: '<?php echo wp_create_nonce('ctm_form_import_nonce'); ?>'
            },
            success: function(response) {
                if (response.success && response.data.forms) {
                    select.html('<option value=""><?php _e('Select a CTM form...', 'call-tracking-metrics'); ?></option>');
                    response.data.forms.forEach(function(form) {
                        const truncatedId = form.id.length > 20 ? 
                            form.id.substring(0, 8) + '...' + form.id.substring(form.id.length - 8) : 
                            form.id;
                        select.append(`<option value="${form.id}" title="${form.id}">${form.name} (${truncatedId})</option>`);
                    });
                } else {
                    select.html('<option value=""><?php _e('No CTM forms available', 'call-tracking-metrics'); ?></option>');
                }
            },
            error: function() {
                select.html('<option value=""><?php _e('Error loading CTM forms', 'call-tracking-metrics'); ?></option>');
            }
        });
    }
    
    // Enable/disable sync button based on form selection
    $('#ctm-sync-form-select').on('change', function() {
        $('#ctm-sync-confirm').prop('disabled', !$(this).val());
    });
    
    // Handle sync confirm
    $('#ctm-sync-confirm').on('click', function() {
        const formId = $(this).data('form-id');
        const formType = $(this).data('form-type');
        const formTitle = $(this).data('form-title');
        const ctmFormId = $('#ctm-sync-form-select').val();
        
        if (!ctmFormId) return;
        
        // Show loading state
        $(this).prop('disabled', true).text('<?php _e('Syncing...', 'call-tracking-metrics'); ?>');
        
        // AJAX call to sync the forms
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'ctm_sync_form',
                nonce: '<?php echo wp_create_nonce('ctm_form_import_nonce'); ?>',
                wp_form_id: formId,
                wp_form_type: formType,
                ctm_form_id: ctmFormId
            },
            success: function(response) {
                if (response.success) {
                    $('#ctm-sync-modal').addClass('hidden');
                    $('body').removeClass('overflow-hidden');
                    
                    // Show success message and reload page
                    $('body').append('<div class="fixed top-4 right-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded" style="z-index: 999999;" id="ctm-sync-success-toast">' +
                        '<span class="block sm:inline"><?php _e('Form synced successfully! Reloading page...', 'call-tracking-metrics'); ?></span>' +
                        '</div>');
                    
                    setTimeout(function() {
                        location.reload();
                    }, 2000);
                } else {
                    alert('<?php _e('Sync failed: ', 'call-tracking-metrics'); ?>' + (response.data ? response.data.message : '<?php _e('Unknown error', 'call-tracking-metrics'); ?>'));
                    $(this).prop('disabled', false).text('<?php _e('Sync Forms', 'call-tracking-metrics'); ?>');
                }
            },
            error: function() {
                alert('<?php _e('Sync failed. Please try again.', 'call-tracking-metrics'); ?>');
                $(this).prop('disabled', false).text('<?php _e('Sync Forms', 'call-tracking-metrics'); ?>');
            }
        });
    });
    
    // Handle sync cancel
    $('#ctm-sync-cancel').on('click', function() {
        $('#ctm-sync-modal').addClass('hidden');
        $('body').removeClass('overflow-hidden');
    });
    
    // Close sync modal when clicking outside
    $('#ctm-sync-modal').on('click', function(e) {
        if (e.target === this) {
            $(this).addClass('hidden');
            $('body').removeClass('overflow-hidden');
        }
    });
    
    // Handle CTM Update buttons
    $('.ctm-update-form').on('click', function() {
        const formId = $(this).data('form-id');
        const formType = $(this).data('form-type');
        const formTitle = $(this).data('form-title');
        const ctmFormId = $(this).data('ctm-form-id');
        
        if (confirm('<?php _e('This will update your WordPress form with the latest content from CallTrackingMetrics. Any local changes may be overwritten. Continue?', 'call-tracking-metrics'); ?>')) {
            // Show loading state
            $(this).prop('disabled', true).html('<svg class="w-4 h-4 mr-1 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg><?php _e('Updating...', 'call-tracking-metrics'); ?>');
            
            // AJAX call to update the form
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'ctm_update_form',
                    nonce: '<?php echo wp_create_nonce('ctm_form_import_nonce'); ?>',
                    wp_form_id: formId,
                    wp_form_type: formType,
                    ctm_form_id: ctmFormId
                },
                success: function(response) {
                    if (response.success) {
                        // Show success message and reload page
                        $('body').append('<div class="fixed top-4 right-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded" style="z-index: 999999;" id="ctm-update-success-toast">' +
                            '<span class="block sm:inline"><?php _e('Form updated successfully! Reloading page...', 'call-tracking-metrics'); ?></span>' +
                            '</div>');
                        
                        setTimeout(function() {
                            location.reload();
                        }, 2000);
                    } else {
                        alert('<?php _e('Update failed: ', 'call-tracking-metrics'); ?>' + (response.data ? response.data.message : '<?php _e('Unknown error', 'call-tracking-metrics'); ?>'));
                        $(this).prop('disabled', false).html('<svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg><?php _e('Update from CTM', 'call-tracking-metrics'); ?>');
                    }
                }.bind(this),
                error: function() {
                    alert('<?php _e('Update failed. Please try again.', 'call-tracking-metrics'); ?>');
                    $(this).prop('disabled', false).html('<svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg><?php _e('Update from CTM', 'call-tracking-metrics'); ?>');
                }.bind(this)
            });
        }
    });
    
    // Handle Form Preview buttons
    $('.ctm-preview-wp-form').on('click', function() {
        const formId = $(this).data('form-id');
        const formType = $(this).data('form-type');
        const formTitle = $(this).data('form-title');
        
        // Show modal and set form title
        $('#ctm-preview-form-title').text(formTitle);
        $('#ctm-preview-modal').removeClass('hidden').addClass('flex');
        $('body').addClass('overflow-hidden');
        
        // Reset modal states
        $('#ctm-preview-loading').show();
        $('#ctm-preview-content').hide().html('');
        $('#ctm-preview-error').hide();
        
        // AJAX call to get form preview
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'ctm_preview_wp_form',
                nonce: '<?php echo wp_create_nonce('ctm_form_import_nonce'); ?>',
                form_id: formId,
                form_type: formType
            },
            success: function(response) {
                $('#ctm-preview-loading').hide();
                
                if (response.success && response.data && response.data.preview) {
                    $('#ctm-preview-content').html(response.data.preview).show();
                } else {
                    $('#ctm-preview-error').show();
                    $('#ctm-preview-error-message').text(response.data ? response.data.message : '<?php _e('Unknown error occurred', 'call-tracking-metrics'); ?>');
                }
            },
            error: function() {
                $('#ctm-preview-loading').hide();
                $('#ctm-preview-error').show();
                $('#ctm-preview-error-message').text('<?php _e('Network error occurred', 'call-tracking-metrics'); ?>');
            }
        });
    });
    
    // Handle preview modal close
    $('#ctm-preview-close, #ctm-preview-close-btn').on('click', function() {
        $('#ctm-preview-modal').addClass('hidden').removeClass('flex');
        $('body').removeClass('overflow-hidden');
    });
    
    // Close preview modal when clicking outside
    $('#ctm-preview-modal').on('click', function(e) {
        if (e.target === this) {
            $(this).addClass('hidden').removeClass('flex');
            $('body').removeClass('overflow-hidden');
        }
    });
});
</script>