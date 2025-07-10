<?php
/**
 * Contact Form 7 Integration Service
 * 
 * This file contains the CF7Service class that handles integration between
 * Contact Form 7 plugin and CallTrackingMetrics, including form submission
 * processing, field mapping, and data formatting.
 * 
 * @package     CallTrackingMetrics
 * @subpackage  Service
 * @author      CallTrackingMetrics Team
 * @copyright   2024 CallTrackingMetrics
 * @license     GPL-2.0+
 * @version     2.0
 * @since       1.0.0
 */

namespace CTM\Service;

/**
 * Contact Form 7 Service Class
 * 
 * Handles all Contact Form 7 related functionality including:
 * - Form submission data processing
 * - Field mapping between CF7 fields and CTM fields
 * - Data validation and sanitization
 * - Form configuration retrieval
 * - Integration with CTM API data format
 * 
 * This service acts as a bridge between Contact Form 7's data structure
 * and the CallTrackingMetrics API requirements.
 * 
 * @since 1.0.0
 */
class CF7Service
{
    /**
     * Process Contact Form 7 submission for CTM API
     * 
     * Takes raw CF7 form submission data and converts it into the format
     * expected by the CallTrackingMetrics API. Handles field mapping,
     * data validation, and metadata extraction.
     * 
     * @since 1.0.0
     * @param object $form The CF7 form object containing form configuration
     * @param array  $data The submitted form data from CF7
     * @return array|null Formatted data for CTM API or null on failure
     */
    public function processSubmission($form, array $data): ?array
    {
        // Validate that CF7 is available and form is valid
        if (!class_exists('WPCF7_ContactForm') || !$form) {
            return null;
        }

        try {
            // Extract basic form information
            $formId = $form->id();
            $formTitle = $form->title();
            
            // Get field mapping configuration for this form
            $fieldMapping = get_option("ctm_mapping_cf7_{$formId}", []);
            
            // Build the payload for CTM API
            $payload = [
                'form_type' => 'contact_form_7',
                'form_id' => $formId,
                'form_title' => $formTitle,
                'form_url' => $this->getCurrentPageUrl(),
                'timestamp' => current_time('mysql'),
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
                'ip_address' => $this->getClientIpAddress(),
                'fields' => $this->mapFormFields($data, $fieldMapping),
                'raw_data' => $data, // Keep original data for debugging
            ];
            
            // Add referrer information if available
            if (!empty($_SERVER['HTTP_REFERER'])) {
                $payload['referrer'] = $_SERVER['HTTP_REFERER'];
            }
            
            // Add UTM parameters if present
            $utmParams = $this->extractUtmParameters();
            if (!empty($utmParams)) {
                $payload['utm_parameters'] = $utmParams;
            }
            
            return $payload;
            
        } catch (\Exception $e) {
            // Log error but don't break the form submission
            error_log('CTM CF7 Processing Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get all Contact Form 7 forms available on the site
     * 
     * Retrieves a list of all CF7 forms with their IDs and titles.
     * Used for form mapping configuration in the admin interface.
     * 
     * @since 1.0.0
     * @return array Array of form objects with id and title properties
     */
    public function getForms(): array
    {
        // Check if Contact Form 7 is available
        if (!class_exists('WPCF7_ContactForm')) {
            return [];
        }

        $forms = [];
        
        try {
            // Get all CF7 forms from the database
            $cf7_forms = \WPCF7_ContactForm::find([
                'posts_per_page' => -1, // Get all forms
                'orderby' => 'title',
                'order' => 'ASC'
            ]);
            
            // Format forms for our use
            foreach ($cf7_forms as $form) {
                $forms[] = [
                    'id' => $form->id(),
                    'title' => $form->title(),
                    'status' => $form->is_posted() ? 'active' : 'inactive',
                    'field_count' => count($this->getFormFields($form)),
                ];
            }
            
        } catch (\Exception $e) {
            error_log('CTM CF7 getForms Error: ' . $e->getMessage());
        }
        
        return $forms;
    }

    /**
     * Get form fields for a specific Contact Form 7 form
     * 
     * Extracts all available fields from a CF7 form including their
     * types, names, and labels for mapping configuration.
     * 
     * @since 1.0.0
     * @param int|object $formId The form ID or form object
     * @return array Array of field information
     */
    public function getFormFields($formId): array
    {
        // Handle both form ID and form object
        if (is_numeric($formId)) {
            $form = \WPCF7_ContactForm::get_instance($formId);
        } else {
            $form = $formId; // Already a form object
        }
        
        if (!$form) {
            return [];
        }

        $fields = [];
        
        try {
            // Get the form template (mail template)
            $form_template = $form->prop('form');
            
            // Parse CF7 shortcodes to extract field information
            $pattern = '/\[([a-zA-Z_-]+)(\*?)\s+([a-zA-Z0-9_-]+)([^\]]*)\]/';
            preg_match_all($pattern, $form_template, $matches, PREG_SET_ORDER);
            
            foreach ($matches as $match) {
                $field_type = $match[1];
                $required = !empty($match[2]); // * indicates required
                $field_name = $match[3];
                $field_options = isset($match[4]) ? trim($match[4]) : '';
                
                // Skip non-input fields
                if (in_array($field_type, ['submit', 'quiz', 'captchac', 'captchar'])) {
                    continue;
                }
                
                // Extract placeholder or label from options
                $label = $this->extractFieldLabel($field_options, $field_name);
                
                $fields[] = [
                    'name' => $field_name,
                    'type' => $this->normalizeFieldType($field_type),
                    'label' => $label,
                    'required' => $required,
                    'cf7_type' => $field_type, // Keep original CF7 type
                ];
            }
            
        } catch (\Exception $e) {
            error_log('CTM CF7 getFormFields Error: ' . $e->getMessage());
        }
        
        return $fields;
    }

    /**
     * Map form fields according to configured mapping
     * 
     * Transforms CF7 field data into CTM-compatible format using
     * the configured field mapping rules.
     * 
     * @since 1.0.0
     * @param array $formData     The raw form submission data
     * @param array $fieldMapping The configured field mapping
     * @return array Mapped field data for CTM API
     */
    private function mapFormFields(array $formData, array $fieldMapping): array
    {
        $mappedFields = [];
        
        foreach ($formData as $fieldName => $fieldValue) {
            // Skip internal CF7 fields
            if (strpos($fieldName, '_wpcf7') === 0) {
                continue;
            }
            
            // Get the mapped CTM field name
            $ctmFieldName = $fieldMapping[$fieldName] ?? $fieldName;
            
            // Clean and format the field value
            $cleanValue = $this->sanitizeFieldValue($fieldValue);
            
            if (!empty($cleanValue)) {
                $mappedFields[$ctmFieldName] = $cleanValue;
            }
        }
        
        return $mappedFields;
    }

    /**
     * Extract field label from CF7 field options
     * 
     * Parses CF7 field options to extract the display label or placeholder.
     * Falls back to the field name if no label is found.
     * 
     * @since 1.0.0
     * @param string $options   The field options string from CF7
     * @param string $fieldName The field name as fallback
     * @return string The extracted or generated label
     */
    private function extractFieldLabel(string $options, string $fieldName): string
    {
        // Look for placeholder attribute
        if (preg_match('/placeholder\s*["\']([^"\']+)["\']/', $options, $matches)) {
            return $matches[1];
        }
        
        // Look for watermark (older CF7 syntax)
        if (preg_match('/watermark\s*["\']([^"\']+)["\']/', $options, $matches)) {
            return $matches[1];
        }
        
        // Convert field name to readable label
        return ucwords(str_replace(['_', '-'], ' ', $fieldName));
    }

    /**
     * Normalize CF7 field types to standard types
     * 
     * Converts Contact Form 7 specific field types to more generic
     * field types that are easier to work with.
     * 
     * @since 1.0.0
     * @param string $cf7Type The original CF7 field type
     * @return string The normalized field type
     */
    private function normalizeFieldType(string $cf7Type): string
    {
        $typeMap = [
            'text' => 'text',
            'email' => 'email',
            'url' => 'url',
            'tel' => 'phone',
            'number' => 'number',
            'date' => 'date',
            'textarea' => 'textarea',
            'select' => 'select',
            'checkbox' => 'checkbox',
            'radio' => 'radio',
            'file' => 'file',
            'hidden' => 'hidden',
        ];
        
        return $typeMap[$cf7Type] ?? 'text';
    }

    /**
     * Sanitize and clean field values
     * 
     * Cleans form field values by removing unwanted characters,
     * handling arrays, and ensuring data safety.
     * 
     * @since 1.0.0
     * @param mixed $value The raw field value
     * @return string The sanitized field value
     */
    private function sanitizeFieldValue($value): string
    {
        // Handle array values (checkboxes, multi-select)
        if (is_array($value)) {
            $value = implode(', ', array_filter($value));
        }
        
        // Convert to string and sanitize
        $value = (string) $value;
        $value = sanitize_text_field($value);
        $value = trim($value);
        
        return $value;
    }

    /**
     * Get the current page URL where the form was submitted
     * 
     * @since 1.0.0
     * @return string The current page URL
     */
    private function getCurrentPageUrl(): string
    {
        if (isset($_SERVER['HTTP_HOST']) && isset($_SERVER['REQUEST_URI'])) {
            $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
            return $protocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        }
        
        return home_url();
    }

    /**
     * Get the client's IP address
     * 
     * Attempts to determine the real client IP address, accounting
     * for proxies and load balancers.
     * 
     * @since 1.0.0
     * @return string The client IP address
     */
    private function getClientIpAddress(): string
    {
        // Check for various IP headers in order of preference
        $ipHeaders = [
            'HTTP_CF_CONNECTING_IP',     // Cloudflare
            'HTTP_CLIENT_IP',            // Proxy
            'HTTP_X_FORWARDED_FOR',      // Load balancer/proxy
            'HTTP_X_FORWARDED',          // Proxy
            'HTTP_X_CLUSTER_CLIENT_IP',  // Cluster
            'HTTP_FORWARDED_FOR',        // Proxy
            'HTTP_FORWARDED',            // Proxy
            'REMOTE_ADDR'                // Standard
        ];
        
        foreach ($ipHeaders as $header) {
            if (!empty($_SERVER[$header])) {
                $ip = $_SERVER[$header];
                
                // Handle comma-separated IPs (X-Forwarded-For can contain multiple IPs)
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                
                // Validate IP address
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    /**
     * Extract UTM parameters from the current request
     * 
     * Captures marketing campaign parameters for tracking purposes.
     * 
     * @since 1.0.0
     * @return array Array of UTM parameters
     */
    private function extractUtmParameters(): array
    {
        $utmParams = [];
        $utmKeys = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content'];
        
        foreach ($utmKeys as $key) {
            if (!empty($_GET[$key])) {
                $utmParams[$key] = sanitize_text_field($_GET[$key]);
            }
        }
        
        return $utmParams;
    }
}