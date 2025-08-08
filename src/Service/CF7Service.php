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
class CF7Service extends BaseFormService
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

            // Build a field type map from getFormFields
            $fieldTypeMap = [];
            foreach ($this->getFormFields($form) as $fieldDef) {
                $fieldTypeMap[$fieldDef['name']] = $fieldDef['type'];
            }
            
            // Map form fields and extract required CTM fields
            $mappedFields = $this->mapFormFieldsDirect($data, $fieldTypeMap);
            
            // Extract required CTM fields
            $phoneNumber = '';
            $callerName = '';
            $email = '';
            $countryCode = '';
            
            // Find phone number field
            foreach ($data as $fieldName => $fieldValue) {
                $fieldType = $fieldTypeMap[$fieldName] ?? $this->normalizeFieldType($fieldName);
                $fieldNameLower = strtolower($fieldName);
                
                // Map phone number to required field
                if ($fieldType === 'phone' || $fieldType === 'tel' || 
                    strpos($fieldNameLower, 'phone') !== false || 
                    strpos($fieldNameLower, 'tel') !== false) {
                    $phoneNumber = $this->sanitizeFieldValue($fieldValue);
                }
                
                // Map name field
                if (strpos($fieldNameLower, 'name') !== false && 
                    (strpos($fieldNameLower, 'first') !== false || 
                     strpos($fieldNameLower, 'last') !== false || 
                     strpos($fieldNameLower, 'full') !== false)) {
                    $callerName = $this->sanitizeFieldValue($fieldValue);
                }
                
                // Map email field
                if ($fieldType === 'email' || strpos($fieldNameLower, 'email') !== false) {
                    $email = $this->sanitizeFieldValue($fieldValue);
                }
                
                // Map country code field
                if (strpos($fieldNameLower, 'country') !== false) {
                    $countryCode = $this->sanitizeFieldValue($fieldValue);
                }
            }
            
            // Build the payload for CTM API with required fields
            $payload = [
                'form_type'         => 'contact_form_7',
                'form_id'           => $formId,
                'phone_number'      => $phoneNumber,
                'country_code'      => $countryCode,
                'type'              => 'API',
                'caller_name'       => $callerName,
                'email'             => $email,
                'callback_number'   => '',
                'delay_calling_by'  => '',
                'form_reactor'      => [
                    'form_id' => $formId
                ],
                'id'                => $formId,
                'name'              => $formTitle,
                '__ctm_api_authorized__' => '1',
                'visitor_sid'       => $_COOKIE['__ctmid'] ?? '',
                'domain'            => $_SERVER['HTTP_HOST'] ?? '',
                'raw_data'          => $data,
                'fields'            => $mappedFields,
            ];
            
            // Add callback_number and delay_calling_by if present
            foreach ($data as $key => $value) {
                if (strtolower($key) === 'callback_number' || strtolower($key) === 'callback number') {
                    $payload['callback_number'] = $value;
                }
                if (strtolower($key) === 'delay_calling_by' || strtolower($key) === 'delay calling by') {
                    $payload['delay_calling_by'] = $value;
                }
            }
            
            // Add custom fields (all other fields, excluding required CTM fields)
            foreach ($mappedFields as $fieldName => $fieldValue) {
                $fieldNameLower = strtolower($fieldName);
                // Skip fields that are already mapped to top-level CTM fields
                if (!in_array($fieldName, ['phone', 'name', 'email', 'country']) && 
                    strpos($fieldNameLower, 'phone') === false && 
                    strpos($fieldNameLower, 'tel') === false &&
                    strpos($fieldNameLower, 'name') === false &&
                    strpos($fieldNameLower, 'email') === false &&
                    strpos($fieldNameLower, 'country') === false) {
                    $payload['custom_' . $fieldName] = $fieldValue;
                }
            }
            
            // Add referrer information if available
            $payload = $this->addReferrerToPayload($payload);
            
            // Add UTM parameters if present
            $utmParams = $this->extractUtmFromUrl();
            $payload = $this->addUtmToPayload($payload, $utmParams);
            
            return $payload;
            
        } catch (\Exception $e) {
            throw $e;
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
            $this->logInternal('CF7 getForms Error: ' . $e->getMessage(), 'error');
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
            
            // Check if form template is null or empty
            if (empty($form_template)) {
                return $fields;
            }
            
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
            $this->logInternal('CF7 getFormFields Error: ' . $e->getMessage(), 'error');
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
    private function mapFormFieldsDirect(array $formData, array $fieldTypeMap = []): array
    {
        $mappedFields = [];
        $addressFields = ['address_street', 'address_city', 'address_state', 'address_zip', 'address_country'];
        $address = [];
        foreach ($formData as $fieldName => $fieldValue) {
            if (strpos($fieldName, '_wpcf7') === 0) {
                continue;
            }
            $type = $fieldTypeMap[$fieldName] ?? $this->normalizeFieldType($fieldName);
            // Group address fields
            if (in_array($fieldName, $addressFields)) {
                $address[str_replace('address_', '', $fieldName)] = $this->sanitizeFieldValue($fieldValue);
                continue;
            }
            // File fields: pass as URL if present
            if ($type === 'file' && !empty($fieldValue) && filter_var($fieldValue, FILTER_VALIDATE_URL)) {
                $mappedFields[$fieldName] = $fieldValue;
                continue;
            }
            // Checkboxes: send as arrays if possible
            if ($type === 'checkbox') {
                $arrayValue = is_array($fieldValue) ? $fieldValue : explode(',', $fieldValue);
                $arrayValue = array_filter((array)$arrayValue);
                $mappedFields[$fieldName] = $arrayValue;
                continue;
            }
            // Unsupported field types: skip and log
            $supportedTypes = ['text','textarea','select','multiselect','number','phone','email','url','date','time','file','radio','checkbox','hidden','list'];
            if (!in_array($type, $supportedTypes)) {
                $this->logDebug("Field {$fieldName} skipped (unsupported type)");
                continue;
            }
            // Clean and format the field value
            $cleanValue = $this->sanitizeFieldValue($fieldValue);
            if (!empty($cleanValue)) {
                $mappedFields[$fieldName] = $cleanValue;
            }
        }
        if (!empty($address)) {
            $mappedFields['address'] = $address;
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
    protected function normalizeFieldType(string $cf7Type): string
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
     * @param string $fieldType The type of field (email, phone, url, etc.)
     * @return string The sanitized field value
     */
    protected function sanitizeFieldValue($value, string $fieldType = 'text'): string
    {
        // Handle array values (checkboxes, multi-select)
        if (is_array($value)) {
            $value = implode(', ', array_filter($value));
        }
        
        // Convert to string
        $value = (string) $value;
        
        // Field-specific sanitization
        switch ($fieldType) {
            case 'email':
                $value = sanitize_email($value);
                break;
            case 'website':
            case 'url':
                $value = esc_url_raw($value);
                break;
            case 'phone':
                // Remove non-numeric characters except + and common phone formatting
                $value = preg_replace('/[^0-9+\s\-\(\)\.]/', '', $value);
                break;
            case 'number':
                $value = is_numeric($value) ? $value : '';
                break;
            default:
                $value = sanitize_text_field($value);
        }
        
        return trim($value);
    }




    /**
     * Check if a form has a phone number field
     * 
     * @since 2.0.0
     * @param object $form The CF7 form object
     * @return bool True if form has a phone field
     */
    public function hasPhoneField($form): bool
    {
        if (!$form) {
            return false;
        }

        $fields = $this->getFormFields($form);
        
        foreach ($fields as $field) {
            $fieldName = strtolower($field['name']);
            $fieldType = strtolower($field['type']);
            
            // Check for phone field type
            if ($fieldType === 'phone' || $fieldType === 'tel') {
                return true;
            }
            
            // Check for phone-related field names
            $phoneKeywords = ['phone', 'telephone', 'tel', 'mobile', 'cell', 'number'];
            foreach ($phoneKeywords as $keyword) {
                if (strpos($fieldName, $keyword) !== false) {
                    return true;
                }
            }
        }
        
        return false;
    }

    /**
     * Get phone field information for a form
     * 
     * @since 2.0.0
     * @param object $form The CF7 form object
     * @return array|null Phone field info or null if not found
     */
    public function getPhoneField($form): ?array
    {
        if (!$form) {
            return null;
        }

        $fields = $this->getFormFields($form);
        
        foreach ($fields as $field) {
            $fieldName = strtolower($field['name']);
            $fieldType = strtolower($field['type']);
            
            // Check for phone field type
            if ($fieldType === 'phone' || $fieldType === 'tel') {
                return $field;
            }
            
            // Check for phone-related field names
            $phoneKeywords = ['phone', 'telephone', 'tel', 'mobile', 'cell', 'number'];
            foreach ($phoneKeywords as $keyword) {
                if (strpos($fieldName, $keyword) !== false) {
                    return $field;
                }
            }
        }
        
        return null;
    }

    // Add debug logging helper
    private function logDebug($msg) {
        $this->logInternal($msg, 'debug');
    }
}