<?php
/**
 * Form Import AJAX Handler
 * 
 * This file contains the FormImportAjax class that handles AJAX requests
 * for importing forms from CallTrackingMetrics FormReactor.
 * 
 * @package     CallTrackingMetrics
 * @subpackage  Admin
 * @author      CallTrackingMetrics Team
 * @copyright   2024 CallTrackingMetrics
 * @license     GPL-2.0+
 * @version     2.0
 * @since       2.0.0
 */

namespace CTM\Admin\Ajax;

use CTM\Service\FormImportService;
use CTM\Service\ApiService;
use CTM\Service\CF7Service;
use CTM\Service\GFService;

/**
 * Form Import AJAX Handler Class
 * 
 * Handles AJAX requests for form import functionality including:
 * - Fetching available forms from CTM
 * - Importing forms to CF7/GF
 * - Form validation and error handling
 * 
 * @since 2.0.0
 */
class FormImportAjax
{
    /**
     * Form Import Service instance
     * 
     * @since 2.0.0
     * @var FormImportService
     */
    private FormImportService $formImportService;

    /**
     * Initialize the form import AJAX handler
     * 
     * @since 2.0.0
     * @param FormImportService $formImportService The form import service
     */
    public function __construct(FormImportService $formImportService)
    {
        $this->formImportService = $formImportService;
    }

    /**
     * Register AJAX handlers
     * 
     * @since 2.0.0
     */
    public function registerHandlers(): void
    {
        add_action('wp_ajax_ctm_get_available_forms', [$this, 'getAvailableForms']);
        add_action('wp_ajax_ctm_import_form', [$this, 'importForm']);
        add_action('wp_ajax_ctm_preview_form', [$this, 'previewForm']);
    }

    /**
     * Get available forms from CTM
     * 
     * @since 2.0.0
     */
    public function getAvailableForms(): void
    {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'ctm_form_import_nonce')) {
            wp_send_json_error(['message' => 'Security check failed']);
        }

        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Insufficient permissions']);
        }

        $apiKey = get_option('ctm_api_key');
        $apiSecret = get_option('ctm_api_secret');

        if (!$apiKey || !$apiSecret) {
            wp_send_json_error(['message' => 'API credentials not configured']);
        }

        try {
            // Add debug logging
            error_log('CTM Debug: Starting getAvailableForms request');
            error_log('CTM Debug: API Key: ' . substr($apiKey, 0, 8) . '...');
            error_log('CTM Debug: API Secret: ' . substr($apiSecret, 0, 8) . '...');
            
            $forms = $this->formImportService->getAvailableForms($apiKey, $apiSecret);
            
            if ($forms === null) {
                error_log('CTM Debug: getAvailableForms returned null');
                wp_send_json_error(['message' => 'Failed to fetch forms from CallTrackingMetrics']);
            }

            error_log('CTM Debug: Successfully retrieved ' . count($forms) . ' forms');
            
            wp_send_json_success([
                'forms' => $forms,
                'message' => sprintf('Found %d forms available for import', count($forms))
            ]);

        } catch (\Exception $e) {
            error_log('CTM Debug: Exception in getAvailableForms: ' . $e->getMessage());
            error_log('CTM Debug: Stack trace: ' . $e->getTraceAsString());
            wp_send_json_error(['message' => 'Error fetching forms: ' . $e->getMessage()]);
        }
    }

    /**
     * Import a form to CF7 or GF
     * 
     * @since 2.0.0
     */
    public function importForm(): void
    {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'ctm_form_import_nonce')) {
            wp_send_json_error(['message' => 'Security check failed']);
        }

        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Insufficient permissions']);
        }

        $ctmFormId = sanitize_text_field($_POST['ctm_form_id'] ?? '');
        $formTitle = sanitize_text_field($_POST['form_title'] ?? '');
        $targetType = sanitize_text_field($_POST['target_type'] ?? '');

        // Validate parameters
        $validation = $this->formImportService->validateImportParams([
            'ctm_form_id' => $ctmFormId,
            'form_title' => $formTitle,
            'target_type' => $targetType
        ]);

        if (!$validation['valid']) {
            wp_send_json_error(['message' => 'Validation failed: ' . implode(', ', $validation['errors'])]);
        }

        // Get API credentials
        $apiKey = get_option('ctm_api_key');
        $apiSecret = get_option('ctm_api_secret');

        if (!$apiKey || !$apiSecret) {
            wp_send_json_error(['message' => 'API credentials not configured']);
        }

        try {
            // Get the specific form data
            $forms = $this->formImportService->getAvailableForms($apiKey, $apiSecret);
            $ctmForm = null;

            if ($forms) {
                foreach ($forms as $form) {
                    if ($form['id'] == $ctmFormId) {
                        $ctmForm = $form;
                        break;
                    }
                }
            }

            if (!$ctmForm) {
                wp_send_json_error(['message' => 'Form not found']);
            }

            // Import based on target type
            $result = null;
            if ($targetType === 'cf7') {
                $result = $this->formImportService->importToCF7($ctmForm, $formTitle);
            } elseif ($targetType === 'gf') {
                $result = $this->formImportService->importToGF($ctmForm, $formTitle);
            }

            if (!$result) {
                wp_send_json_error(['message' => 'Import failed']);
            }

            if ($result['success']) {
                wp_send_json_success([
                    'message' => $result['message'],
                    'form_id' => $result['form_id'],
                    'form_title' => $result['form_title']
                ]);
            } else {
                wp_send_json_error(['message' => $result['error']]);
            }

        } catch (\Exception $e) {
            wp_send_json_error(['message' => 'Import failed: ' . $e->getMessage()]);
        }
    }

    /**
     * Preview a form before import
     * 
     * @since 2.0.0
     */
    public function previewForm(): void
    {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'ctm_form_import_nonce')) {
            wp_send_json_error(['message' => 'Security check failed']);
        }

        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Insufficient permissions']);
        }

        $ctmFormId = sanitize_text_field($_POST['ctm_form_id'] ?? '');
        $targetType = sanitize_text_field($_POST['target_type'] ?? '');

        if (!$ctmFormId || !$targetType) {
            wp_send_json_error(['message' => 'Missing required parameters']);
        }

        // Get API credentials
        $apiKey = get_option('ctm_api_key');
        $apiSecret = get_option('ctm_api_secret');

        if (!$apiKey || !$apiSecret) {
            wp_send_json_error(['message' => 'API credentials not configured']);
        }

        try {
            // Get the specific form data
            $forms = $this->formImportService->getAvailableForms($apiKey, $apiSecret);
            $ctmForm = null;

            if ($forms) {
                foreach ($forms as $form) {
                    if ($form['id'] == $ctmFormId) {
                        $ctmForm = $form;
                        break;
                    }
                }
            }

            if (!$ctmForm) {
                wp_send_json_error(['message' => 'Form not found']);
            }

            // Generate preview based on target type
            $preview = '';
            if ($targetType === 'cf7') {
                $preview = $this->generateCF7Preview($ctmForm);
            } elseif ($targetType === 'gf') {
                $preview = $this->generateGFPreview($ctmForm);
            }

            wp_send_json_success([
                'preview' => $preview,
                'form_data' => $ctmForm
            ]);

        } catch (\Exception $e) {
            wp_send_json_error(['message' => 'Preview failed: ' . $e->getMessage()]);
        }
    }

    /**
     * Generate CF7 preview
     * 
     * @since 2.0.0
     * @param array $ctmForm The CTM form data
     * @return string The preview HTML
     */
    private function generateCF7Preview(array $ctmForm): string
    {
        $preview = '<div class="ctm-preview-cf7">';
        $preview .= '<h3>Contact Form 7 Preview</h3>';
        $preview .= '<div class="cf7-form-preview">';
        
        if (isset($ctmForm['fields']) && is_array($ctmForm['fields'])) {
            foreach ($ctmForm['fields'] as $field) {
                $fieldType = $field['type'] ?? 'text';
                $fieldLabel = $field['label'] ?? $field['name'] ?? '';
                $required = isset($field['required']) && $field['required'] ? ' *' : '';
                
                $preview .= '<div class="form-field">';
                $preview .= '<label>' . esc_html($fieldLabel . $required) . '</label>';
                
                switch ($fieldType) {
                    case 'email':
                        $preview .= '<input type="email" placeholder="' . esc_attr($fieldLabel) . '" disabled>';
                        break;
                    case 'textarea':
                    case 'text_area':
                        $preview .= '<textarea placeholder="' . esc_attr($fieldLabel) . '" disabled></textarea>';
                        break;
                    case 'number':
                    case 'decimal':
                        $preview .= '<input type="number" placeholder="' . esc_attr($fieldLabel) . '" disabled>';
                        break;
                    case 'phone':
                        $preview .= '<input type="tel" placeholder="' . esc_attr($fieldLabel) . '" disabled>';
                        break;
                    case 'website':
                    case 'url':
                        $preview .= '<input type="url" placeholder="' . esc_attr($fieldLabel) . '" disabled>';
                        break;
                    case 'select':
                    case 'picker':
                    case 'choice_list':
                        $preview .= '<select disabled>';
                        $preview .= '<option>' . esc_html($fieldLabel) . '</option>';
                        if (isset($field['options']) && is_array($field['options'])) {
                            foreach ($field['options'] as $option) {
                                $preview .= '<option>' . esc_html($option) . '</option>';
                            }
                        }
                        $preview .= '</select>';
                        break;
                    case 'checkbox':
                        $preview .= '<input type="checkbox" disabled> <span>' . esc_html($fieldLabel) . '</span>';
                        break;
                    case 'radio':
                        if (isset($field['options']) && is_array($field['options'])) {
                            foreach ($field['options'] as $option) {
                                $preview .= '<input type="radio" name="' . esc_attr($field['name']) . '" disabled> <span>' . esc_html($option) . '</span><br>';
                            }
                        }
                        break;
                    case 'information':
                        $content = $field['content'] ?? $fieldLabel;
                        $preview .= '<div class="information-field" style="background: #f8f9fa; padding: 10px; border-radius: 4px; border: 1px solid #dee2e6;">' . esc_html($content) . '</div>';
                        break;
                    case 'captcha':
                        $preview .= '<div class="captcha-field" style="background: #f8f9fa; padding: 10px; border-radius: 4px; border: 1px solid #dee2e6; text-align: center;">CAPTCHA Field</div>';
                        break;
                    case 'date':
                        $preview .= '<input type="date" disabled>';
                        break;
                    case 'file_upload':
                    case 'file':
                        $preview .= '<input type="file" disabled>';
                        break;
                    default:
                        $preview .= '<input type="text" placeholder="' . esc_attr($fieldLabel) . '" disabled>';
                        break;
                }
                
                $preview .= '</div>';
            }
        }
        
        $preview .= '<div class="form-field">';
        $preview .= '<button type="submit" disabled>Submit</button>';
        $preview .= '</div>';
        $preview .= '</div>';
        $preview .= '</div>';
        
        return $preview;
    }

    /**
     * Generate GF preview
     * 
     * @since 2.0.0
     * @param array $ctmForm The CTM form data
     * @return string The preview HTML
     */
    private function generateGFPreview(array $ctmForm): string
    {
        $preview = '<div class="ctm-preview-gf">';
        $preview .= '<h3>Gravity Forms Preview</h3>';
        $preview .= '<div class="gf-form-preview">';
        
        if (isset($ctmForm['fields']) && is_array($ctmForm['fields'])) {
            foreach ($ctmForm['fields'] as $field) {
                $fieldType = $field['type'] ?? 'text';
                $fieldLabel = $field['label'] ?? $field['name'] ?? '';
                $required = isset($field['required']) && $field['required'] ? ' *' : '';
                
                $preview .= '<div class="form-field">';
                $preview .= '<label>' . esc_html($fieldLabel . $required) . '</label>';
                
                switch ($fieldType) {
                    case 'email':
                        $preview .= '<input type="email" placeholder="' . esc_attr($fieldLabel) . '" disabled>';
                        break;
                    case 'textarea':
                    case 'text_area':
                        $preview .= '<textarea placeholder="' . esc_attr($fieldLabel) . '" disabled></textarea>';
                        break;
                    case 'number':
                    case 'decimal':
                        $preview .= '<input type="number" placeholder="' . esc_attr($fieldLabel) . '" disabled>';
                        break;
                    case 'phone':
                        $preview .= '<input type="tel" placeholder="' . esc_attr($fieldLabel) . '" disabled>';
                        break;
                    case 'website':
                    case 'url':
                        $preview .= '<input type="url" placeholder="' . esc_attr($fieldLabel) . '" disabled>';
                        break;
                    case 'select':
                    case 'picker':
                    case 'choice_list':
                        $preview .= '<select disabled>';
                        $preview .= '<option>' . esc_html($fieldLabel) . '</option>';
                        if (isset($field['options']) && is_array($field['options'])) {
                            foreach ($field['options'] as $option) {
                                $preview .= '<option>' . esc_html($option) . '</option>';
                            }
                        }
                        $preview .= '</select>';
                        break;
                    case 'checkbox':
                        $preview .= '<input type="checkbox" disabled> <span>' . esc_html($fieldLabel) . '</span>';
                        break;
                    case 'radio':
                        if (isset($field['options']) && is_array($field['options'])) {
                            foreach ($field['options'] as $option) {
                                $preview .= '<input type="radio" name="' . esc_attr($field['name']) . '" disabled> <span>' . esc_html($option) . '</span><br>';
                            }
                        }
                        break;
                    case 'information':
                        $content = $field['content'] ?? $fieldLabel;
                        $preview .= '<div class="information-field" style="background: #f8f9fa; padding: 10px; border-radius: 4px; border: 1px solid #dee2e6;">' . esc_html($content) . '</div>';
                        break;
                    case 'captcha':
                        $preview .= '<div class="captcha-field" style="background: #f8f9fa; padding: 10px; border-radius: 4px; border: 1px solid #dee2e6; text-align: center;">CAPTCHA Field</div>';
                        break;
                    case 'date':
                        $preview .= '<input type="date" disabled>';
                        break;
                    case 'file_upload':
                    case 'file':
                        $preview .= '<input type="file" disabled>';
                        break;
                    default:
                        $preview .= '<input type="text" placeholder="' . esc_attr($fieldLabel) . '" disabled>';
                        break;
                }
                
                $preview .= '</div>';
            }
        }
        
        $preview .= '<div class="form-field">';
        $preview .= '<button type="submit" disabled>Submit</button>';
        $preview .= '</div>';
        $preview .= '</div>';
        $preview .= '</div>';
        
        return $preview;
    }
} 