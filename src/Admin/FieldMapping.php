<?php
namespace CTM\Admin;

/**
 * Handles field mapping between forms and CTM fields
 */
class FieldMapping
{
    /**
     * Save field mapping for a given form.
     *
     * @param string $form_type 'gf' or 'cf7'
     * @param string|int $form_id
     * @param array $mapping
     */
    public function saveFieldMapping(string $form_type, $form_id, array $mapping): void
    {
        $option_name = "ctm_mapping_{$form_type}_{$form_id}";
        update_option($option_name, $mapping);
    }

    /**
     * Get field mapping for a given form.
     *
     * @param string $form_type 'gf' or 'cf7'
     * @param string|int $form_id
     * @return array|null
     */
    public function getFieldMapping(string $form_type, $form_id): ?array
    {
        $option_name = "ctm_mapping_{$form_type}_{$form_id}";
        return get_option($option_name, null);
    }

    /**
     * Register AJAX handlers and enqueue admin JS for field mapping UI.
     */
    public function enqueueMappingAssets(): void
    {
        add_action('admin_enqueue_scripts', function($hook) {
            if ($hook !== 'settings_page_call-tracking-metrics') return;
            
            // Enqueue mapping JS
            wp_enqueue_script('ctm-mapping-js', plugins_url('js/ctm-mapping.js', dirname(__FILE__, 2)), ['jquery'], null, true);
            wp_localize_script('ctm-mapping-js', 'ctmMappingAjax', [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('ctm_mapping_nonce'),
            ]);
        });
    }
} 