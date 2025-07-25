<?php
/**
 * Field Mapping Management
 * 
 * This file contains the FieldMapping class that handles field mapping
 * configuration between WordPress form fields and CallTrackingMetrics
 * fields, including mapping storage, retrieval, and asset management.
 * 
 * @package     CallTrackingMetrics
 * @subpackage  Admin
 * @author      CallTrackingMetrics Team
 * @copyright   2024 CallTrackingMetrics
 * @license     GPL-2.0+
 * @version     2.0
 * @since       2.0.0
 */

namespace CTM\Admin;

/**
 * Field Mapping Management Class
 * 
 * Handles field mapping functionality between form plugins and CTM:
 * - Field mapping storage and retrieval
 * - Mapping configuration persistence
 * - JavaScript and CSS asset management
 * - Form field discovery and mapping interface
 * 
 * This class provides the infrastructure for users to map form fields
 * from Contact Form 7 and Gravity Forms to CallTrackingMetrics fields,
 * enabling proper data transmission to the CTM API.
 * 
 * @since 2.0.0
 */
class FieldMapping
{
    public $options = [];

    /**
     * Save field mapping for a given form
     * 
     * Stores the field mapping configuration in WordPress options.
     * The mapping defines how form fields should be sent to CTM API.
     * 
     * @since 2.0.0
     * @param string     $form_type The form type ('gf' or 'cf7')
     * @param string|int $form_id   The form ID
     * @param array      $mapping   The field mapping array (form_field => ctm_field)
     * @return void
     */
    public function saveFieldMapping(string $form_type, $form_id, array $mapping): void
    {
        $option_name = "ctm_mapping_{$form_type}_{$form_id}";
        update_option($option_name, $mapping);
    }

    /**
     * Get field mapping for a given form
     * 
     * Retrieves the stored field mapping configuration for a specific form.
     * Returns null if no mapping has been configured.
     * 
     * @since 2.0.0
     * @param string     $form_type The form type ('gf' or 'cf7')
     * @param string|int $form_id   The form ID
     * @return array|null The field mapping array or null if not found
     */
    public function getFieldMapping(string $form_type, $form_id): ?array
    {
        $option_name = "ctm_mapping_{$form_type}_{$form_id}";
        $mapping = get_option($option_name, null);

        if (!is_array($mapping)) {
            return null;
        }
        return $mapping;
    }

    /**
     * Enqueue JavaScript and CSS assets for field mapping interface
     * 
     * Loads the necessary scripts and styles for the field mapping
     * functionality in the admin interface. Only loads on the plugin's
     * settings page to avoid unnecessary asset loading.
     * 
     * @since 2.0.0
     * @return void
     */
    public function enqueueMappingAssets(): void
    {
        // Only enqueue on the plugin settings page
        add_action('admin_enqueue_scripts', function($hook) {
            // Check if we're on the CallTrackingMetrics settings page
            if ($hook !== 'settings_page_call-tracking-metrics') {
                return;
            }
            
            // Enqueue JavaScript for field mapping functionality
            \wp_enqueue_script(
                'ctm-field-mapping',
                \plugin_dir_url(__FILE__) . '../../js/field-mapping.js',
                ['jquery'],
                '2.0.0',
                true
            );

            // Enqueue the new mapping-tab JS (after field-mapping)
            \wp_enqueue_script(
                'ctm-mapping-tab',
                \plugin_dir_url(__FILE__) . '../../assets/js/mapping-tab.js',
                ['ctm-field-mapping'],
                '2.0.0',
                true
            );

            // Enqueue CSS for field mapping interface styling
            \wp_enqueue_style(
                'ctm-field-mapping',
                \plugin_dir_url(__FILE__) . '../../css/field-mapping.css',
                [],
                '2.0.0'
            );

            // Localize script with AJAX data and nonces (for legacy field-mapping.js)
            \wp_localize_script('ctm-field-mapping', 'ctm_mapping', [
                'ajax_url' => \admin_url('admin-ajax.php'),
                'nonce' => \wp_create_nonce('ctm_mapping_nonce'),
                'strings' => [
                    'loading' => \__('Loading...', 'call-tracking-metrics'),
                    'error' => \__('An error occurred. Please try again.', 'call-tracking-metrics'),
                    'success' => \__('Mapping saved successfully!', 'call-tracking-metrics'),
                    'no_fields' => \__('No fields found for this form.', 'call-tracking-metrics'),
                    'select_form' => \__('Please select a form first.', 'call-tracking-metrics'),
                    'confirm_reset' => \__('Are you sure you want to reset this mapping?', 'call-tracking-metrics'),
                ]
            ]);
        });
    }

    /**
     * Get all field mappings for a specific form type
     * 
     * Retrieves all stored field mappings for either Contact Form 7
     * or Gravity Forms. Useful for bulk operations or overview displays.
     * 
     * @since 2.0.0
     * @param string $form_type The form type ('gf' or 'cf7')
     * @return array Array of mappings keyed by form ID
     */
    public function getAllMappingsForType(string $form_type): array
    {
        global $wpdb;
        
        $mappings = [];
        $option_pattern = "ctm_mapping_{$form_type}_%";
        
        // Query all options that match our mapping pattern
        if ($this instanceof self && property_exists($this, 'options') && is_array($this->options) && array_key_exists('db', $this->options)) {
            $results = $this->options['db'];
        } else {
            if(!property_exists($wpdb, 'options')) {
                return $mappings;
            }
            $results = $wpdb->get_results($wpdb->prepare(
                "SELECT option_name, option_value FROM {$wpdb->options} WHERE option_name LIKE %s",
                $option_pattern
            ));
        }
        
        foreach ($results as $result) {
            // Extract form ID from option name
            $form_id = str_replace("ctm_mapping_{$form_type}_", '', $result->option_name);
            
            // Unserialize the mapping data
            $mapping = maybe_unserialize($result->option_value);
            
            if (is_array($mapping)) {
                $mappings[$form_id] = $mapping;
            }
        }
        
        return $mappings;
    }

    /**
     * Delete field mapping for a specific form
     * 
     * Removes the stored field mapping configuration for a form.
     * This is useful when forms are deleted or mappings need to be reset.
     * 
     * @since 2.0.0
     * @param string     $form_type The form type ('gf' or 'cf7')
     * @param string|int $form_id   The form ID
     * @return bool True if mapping was deleted, false otherwise
     */
    public function deleteFieldMapping(string $form_type, $form_id): bool
    {
        $option_name = "ctm_mapping_{$form_type}_{$form_id}";
        return delete_option($option_name);
    }

    /**
     * Check if a form has any field mapping configured
     * 
     * Quick check to determine if a form has been mapped without
     * retrieving the full mapping configuration.
     * 
     * @since 2.0.0
     * @param string     $form_type The form type ('gf' or 'cf7')
     * @param string|int $form_id   The form ID
     * @return bool True if mapping exists, false otherwise
     */
    public function hasMappingConfigured(string $form_type, $form_id): bool
    {
        $mapping = $this->getFieldMapping($form_type, $form_id);
        return $mapping !== null && !empty($mapping);
    }

    /**
     * Get mapping statistics for admin overview
     * 
     * Returns statistics about field mappings including total
     * mapped forms, unmapped forms, and mapping completeness.
     * 
     * @since 2.0.0
     * @return array Statistics about field mappings
     */
    public function getMappingStatistics(): array
    {
        $stats = [
            'cf7' => [
                'total_forms' => 0,
                'mapped_forms' => 0,
                'total_mappings' => 0,
            ],
            'gf' => [
                'total_forms' => 0,
                'mapped_forms' => 0,
                'total_mappings' => 0,
            ],
        ];
        
        // Get CF7 statistics
        if (class_exists('WPCF7_ContactForm')) {
            $cf7_forms = \WPCF7_ContactForm::find(['posts_per_page' => -1]);
            $stats['cf7']['total_forms'] = count($cf7_forms);
            
            $cf7_mappings = $this->getAllMappingsForType('cf7');
            $stats['cf7']['mapped_forms'] = count($cf7_mappings);
            $stats['cf7']['total_mappings'] = array_sum(array_map('count', $cf7_mappings));
        }
        
        // Get GF statistics
        if (class_exists('GFAPI')) {
            try {
                if (method_exists('\GFAPI', 'get_forms')) {
                    $gf_forms = \GFAPI::get_forms();
                    $stats['gf']['total_forms'] = count($gf_forms);
                    
                    $gf_mappings = $this->getAllMappingsForType('gf');
                    $stats['gf']['mapped_forms'] = count($gf_mappings);
                    $stats['gf']['total_mappings'] = array_sum(array_map('count', $gf_mappings));
                }
            } catch (\Exception $e) {
                error_log('CTM: Error getting GF mapping statistics: ' . $e->getMessage());
            }
        }
        
        return $stats;
    }

    /**
     * Validate field mapping configuration
     * 
     * Checks if a field mapping configuration is valid by ensuring
     * all mapped fields exist and are properly formatted.
     * 
     * @since 2.0.0
     * @param array  $mapping   The field mapping to validate
     * @param string $form_type The form type ('gf' or 'cf7')
     * @param mixed  $form_id   The form ID
     * @return array Validation result with 'valid' boolean and 'errors' array
     */
    public function validateMapping(array $mapping, string $form_type, $form_id): array
    {
        $errors = [];
        $valid = true;
        
        // Check if mapping is empty
        if (empty($mapping)) {
            return ['valid' => true, 'errors' => []]; // Empty mapping is valid
        }
        
        // Validate form type
        if (!in_array($form_type, ['cf7', 'gf'])) {
            $errors[] = 'Invalid form type specified';
            $valid = false;
        }
        
        // Validate mapping structure
        foreach ($mapping as $form_field => $ctm_field) {
            if (empty($form_field) || empty($ctm_field)) {
                $errors[] = "Invalid mapping: '{$form_field}' => '{$ctm_field}'";
                $valid = false;
            }
            
            if (!is_string($form_field) || !is_string($ctm_field)) {
                $errors[] = "Mapping values must be strings: '{$form_field}' => '{$ctm_field}'";
                $valid = false;
            }
        }
        
        return ['valid' => $valid, 'errors' => $errors];
    }

    public function __construct() {
        // The $this->options initialization is now at the top of the class
    }
} 