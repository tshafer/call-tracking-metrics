<?php
/**
 * Gravity Forms Integration Service
 * 
 * This file contains the GFService class that handles integration between
 * Gravity Forms plugin and CallTrackingMetrics, including form submission
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

// Only import GFAPI if Gravity Forms is available
if (class_exists('GFAPI')) {
    // GFAPI is available - no need to import as it's in global namespace
}

/**
 * Gravity Forms Service Class
 * 
 * Handles all Gravity Forms related functionality including:
 * - Form submission data processing and validation
 * - Field mapping between GF fields and CTM fields
 * - Complex field type handling (file uploads, multi-part fields)
 * - Form configuration and metadata retrieval
 * - Integration with CTM API data format
 * 
 * This service acts as a bridge between Gravity Forms' complex data structure
 * and the CallTrackingMetrics API requirements, handling GF's advanced
 * field types and validation rules.
 * 
 * @since 1.0.0
 */
class GFService
{
    /**
     * Process Gravity Forms submission for CTM API
     * 
     * Takes raw GF form submission data and converts it into the format
     * expected by the CallTrackingMetrics API. Handles complex field types,
     * field mapping, data validation, and metadata extraction.
     * 
     * @since 1.0.0
     * @param array $entry The GF entry data containing submitted values
     * @param array $form  The GF form configuration array
     * @return array|null Formatted data for CTM API or null on failure
     */
    public function processSubmission(array $entry, array $form): ?array
    {
        // Optimized Gravity Forms submission processing for CTM API

        // Validate that Gravity Forms is available and data is valid
        if (!class_exists('GFAPI') || empty($entry) || empty($form)) {
            return null;
        }

 
        try {
            // File-based debug logging for troubleshooting
            $logFile = WP_CONTENT_DIR . '/ctm-gf-debug.log';

            // Build a mapping of entry keys to field labels (single pass)
            $fieldLabels = [];
            if (!empty($form['fields']) && is_array($form['fields'])) {
                foreach ($form['fields'] as $field) {
                    if (!is_object($field) || !isset($field->id)) {
                        continue;
                    }
                    $fid = (string)$field->id;
                    $flabel = $field->label ?? ('Field ' . $fid);
                    $fieldLabels[$fid] = $flabel;
                    if (!empty($field->inputs) && is_array($field->inputs)) {
                        foreach ($field->inputs as $input) {
                            if (!is_array($input) || !isset($input['id'])) {
                                continue;
                            }
                            $iid = (string)$input['id'];
                            $ilabel = $input['label'] ?? ($flabel . ' ' . $iid);
                            $fieldLabels[$iid] = $ilabel;
                        }
                    }
                }
            }

            // Helper function to slugify keys
            $slugify = function($str) {
                $str = strtolower($str);
                $str = preg_replace('/[^a-z0-9]+/', '_', $str);
                $str = trim($str, '_');
                return $str;
            };

            // Map form fields using labels as keys, and build pretty entry
            $fieldsWithLabels = [];
            $prettyEntry = [];
            foreach ($entry as $key => $value) {
                $label = $fieldLabels[(string)$key] ?? $key;
                $slug = $slugify($label);
                $fieldsWithLabels[$slug] = $value;
                $prettyEntry[$slug] = $value;
            }


            // Log entry for debugging
            $logEntry = [
                'timestamp' => date('Y-m-d H:i:s'),
                'entry' => $entry,
                'pretty_entry' => $prettyEntry,
                'form' => $form,
                'backtrace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10)
            ];
            file_put_contents($logFile, json_encode($logEntry, JSON_PRETTY_PRINT) . "\n", FILE_APPEND);

            // Extract basic form and entry information
            $formId = $form['id'];
            $formTitle = $form['title'] ?? '';

            // Merge address fields into a single string
            $addressParts = [
                'Street Address', 'Address Line 2', 'City', 'State / Province', 'ZIP / Postal Code', 'Country'
            ];
            $addressValues = [];
            foreach ($addressParts as $part) {
                if (!empty($fieldsWithLabels[$slugify($part)])) {
                    $addressValues[] = $fieldsWithLabels[$slugify($part)];
                }
            }
            if ($addressValues) {
                $fieldsWithLabels['address'] = implode(', ', $addressValues);
            }

            // Merge name fields into a single string
            $nameParts = ['Prefix', 'First', 'Middle', 'Last', 'Suffix'];
            $nameValues = [];
            foreach ($nameParts as $part) {
                if (!empty($fieldsWithLabels[$slugify($part)])) {
                    $nameValues[] = $fieldsWithLabels[$slugify($part)];
                }
            }
            if ($nameValues) {
                $fieldsWithLabels['name'] = implode(' ', $nameValues);
            }

            // Extract form_reactor fields (top 5)
            $formReactor = [
                'form_id' => $formId
            ];
            if (!empty($fieldsWithLabels['phone'])) {
                $formReactor['phone_number'] = $fieldsWithLabels['phone'];
            }
            if (!empty($fieldsWithLabels['name'])) {
                $formReactor['caller_name'] = $fieldsWithLabels['name'];
            }
            // Map any field whose label contains 'email' (case-insensitive) to email
            $emailField = null;
            foreach ($fieldsWithLabels as $label => $value) {
                if (stripos($label, 'email') !== false && !empty($value)) {
                    $emailField = $value;
                    break;
                }
            }
            if ($emailField !== null) {
                $formReactor['email'] = $emailField;
            }
            if (!empty($fieldsWithLabels['country'])) {
                $formReactor['country_code'] = $fieldsWithLabels['country'];
            }

            // Add callback_number and delay_calling_by if present (case-insensitive search)
            $callbackNumber = '';
            $delayCallingBy = '';
            foreach ($fieldsWithLabels as $label => $value) {
                $l = strtolower($label);
                if ($l === 'callback number' || $l === 'callback_number') {
                    $callbackNumber = $value;
                } elseif ($l === 'delay calling by' || $l === 'delay_calling_by') {
                    $delayCallingBy = $value;
                }
            }
            if ($callbackNumber !== '') {
                $formReactor['callback_number'] = $callbackNumber;
            }
            if ($delayCallingBy !== '') {
                $formReactor['delay_calling_by'] = $delayCallingBy;
            }

            // Remove these from the field array to avoid duplication
            $customFields = $fieldsWithLabels;
            unset(
                $customFields['phone'],
                $customFields['name'],
                $customFields['email'],
                $customFields['country']
            );
            if ($callbackNumber !== '') {
                unset($customFields['callback number'], $customFields['callback_number']);
            }
            if ($delayCallingBy !== '') {
                unset($customFields['delay calling by'], $customFields['delay_calling_by']);
            }

            // When building mappedCustomFields, ensure keys are slugified
            $mappedCustomFields = $this->mapFormFields($entry, $form, []);
            $normalizedCustomFields = [];
            foreach ($mappedCustomFields as $k => $v) {
                $normalizedCustomFields[$slugify($k)] = $v;
            }
            // Remove any top-level fields from normalizedCustomFields
            foreach ([
                'phone', 'name', 'email', 'country',
                'callback_number', 'delay_calling_by'
            ] as $removeKey) {
                unset($normalizedCustomFields[$removeKey]);
            }

            // Build the payload with top-level fields and mapped custom fields
            $payload = [
                'phone_number'      => $formReactor['phone_number'] ?? '',
                'country_code'      => $formReactor['country_code'] ?? '',
                'type'              => 'API',
                'caller_name'       => $formReactor['caller_name'] ?? '',
                'email'             => $formReactor['email'] ?? '',
                'callback_number'   => $formReactor['callback_number'] ?? '',
                'delay_calling_by'  => $formReactor['delay_calling_by'] ?? '',
            ];
            foreach ($normalizedCustomFields as $k => $v) {
                $payload['custom_' . $k] = $v;
            }
            $payload['form_reactor'] = $formReactor;
            $payload['id'] = $formId;
            $payload['name'] = $formTitle;
            $payload['__ctm_api_authorized__'] = '1';
            $payload['visitor_sid'] = $_COOKIE['__ctmid'] ?? '';
            $payload['domain'] = $_SERVER['HTTP_HOST'] ?? '';
            $payload['raw_data'] = $entry;

            error_log(print_r($payload, true));
            return $payload;
        } catch (\Exception $e) {
            // Log error but don't break the form submission
            error_log('CTM GF Processing Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get all Gravity Forms available on the site
     * 
     * Retrieves a list of all GF forms with their IDs, titles, and status.
     * Used for form mapping configuration in the admin interface.
     * 
     * @since 1.0.0
     * @return array Array of form objects with id, title, and status properties
     */
    public function getForms(): array
    {
        // Check if Gravity Forms is available
        if (!class_exists('GFAPI')) {
            return [];
        }

        $forms = [];
        
        try {
            // Get all GF forms using the API
            if (method_exists('\GFAPI', 'get_forms')) {
                $gf_forms = \GFAPI::get_forms();
                
                // Format forms for our use
                foreach ($gf_forms as $form) {
                    $forms[] = [
                        'id' => $form['id'],
                        'title' => $form['title'] ?? '',
                        'status' => isset($form['is_active']) ? ($form['is_active'] ? 'active' : 'inactive') : 'inactive',
                        // 'field_count' => count($form['fields']),
                        'entries_count' => method_exists('\GFAPI', 'count_entries') ? \GFAPI::count_entries($form['id']) : 0,
                    ];
                }
            }
            
        } catch (\Exception $e) {
            error_log('CTM GF getForms Error: ' . $e->getMessage());
        }
        
        return $forms;
    }

    /**
     * Get form fields for a specific Gravity Forms form
     * 
     * Extracts all available fields from a GF form including their
     * types, IDs, labels, and properties for mapping configuration.
     * 
     * @since 1.0.0
     * @param int $formId The form ID to get fields for
     * @return array Array of field information
     */
    public function getFormFields(int $formId): array
    {
        // Check if Gravity Forms is available
        if (!class_exists('GFAPI')) {
            return [];
        }

        $fields = [];
        
        try {
            // Get form configuration from GF API
            if (method_exists('\GFAPI', 'get_form')) {
                $form = \GFAPI::get_form($formId);
            } else {
                return [];
            }
            
            if (!$form || !isset($form['fields'])) {
                return [];
            }
            
            // Process each field in the form
            foreach ($form['fields'] as $field) {
                // Skip page breaks and section breaks
                if (in_array($field->type, ['page', 'section', 'html'])) {
                    continue;
                }
                
                // Handle multi-part fields (name, address, etc.)
                if ($this->isMultiPartField($field)) {
                    $subFields = $this->getSubFields($field);
                    $fields = array_merge($fields, $subFields);
                } else {
                    // Single field
                    $fields[] = [
                        'id' => $field->id,
                        'name' => $this->getFieldName($field),
                        'type' => $this->normalizeFieldType($field->type),
                        'label' => $field->label,
                        'required' => $field->isRequired ?? false,
                        'gf_type' => $field->type, // Keep original GF type
                        'choices' => $this->getFieldChoices($field),
                    ];
                }
            }
            
        } catch (\Exception $e) {
            error_log('CTM GF getFormFields Error: ' . $e->getMessage());
        }
        
        return $fields;
    }

    /**
     * Map form fields according to configured mapping
     * 
     * Transforms GF field data into CTM-compatible format using
     * the configured field mapping rules. Handles complex GF field types.
     * 
     * @since 1.0.0
     * @param array $entry        The GF entry data
     * @param array $form         The GF form configuration
     * @param array $fieldMapping The configured field mapping
     * @return array Mapped field data for CTM API
     */
    private function mapFormFields(array $entry, array $form, array $fieldMapping): array
    {
        $mappedFields = [];
        
        // Check if form has fields and they are properly structured
        if (empty($form['fields']) || !is_array($form['fields'])) {
            $this->logDebug("Form has no fields or fields are not properly structured");
            return $mappedFields;
        }
        
        foreach ($form['fields'] as $field) {
            // Skip if field is not properly structured
            if (!is_object($field) || !isset($field->id)) {
                continue;
            }
            
            $fieldId = $field->id;
            $fieldValue = $entry[$fieldId] ?? null;
            if ($this->isAdminField($field)) {
                continue;
            }
            // Skip conditional fields not present in entry
            if (!array_key_exists($fieldId, $entry)) {
                $this->logDebug("Field {$fieldId} skipped (conditional or not present in entry)");
                continue;
            }
            // File Uploads (already handled)
            if ($this->normalizeFieldType($field->type) === 'file') {
                if (!empty($fieldValue)) {
                    // Gravity Forms stores file uploads as a string (single) or serialized array (multiple)
                    $urls = is_array($fieldValue) ? $fieldValue : (is_serialized($fieldValue) ? unserialize($fieldValue) : [$fieldValue]);
                    $urls = array_filter((array)$urls, function($url) { return filter_var($url, FILTER_VALIDATE_URL); });
                    if (!empty($urls)) {
                        $ctmFieldName = $fieldMapping[$fieldId] ?? $this->getFieldName($field);
                        $mappedFields[$ctmFieldName] = count($urls) === 1 ? reset($urls) : array_values($urls);
                    }
                }
                continue;
            }
            // Address Fields (already handled)
            if ($this->normalizeFieldType($field->type) === 'address' && isset($field->inputs)) {
                $address = [];
                foreach ($field->inputs as $input) {
                    $inputId = $input['id'];
                    $inputValue = $entry[$inputId] ?? '';
                    if (!empty($inputValue)) {
                        $part = $input['label'] ?? $inputId;
                        $address[$part] = $this->sanitizeFieldValue($inputValue, $field);
                    }
                }
                if (!empty($address)) {
                    $ctmFieldName = $fieldMapping[$fieldId] ?? $this->getFieldName($field);
                    $mappedFields[$ctmFieldName] = $address;
                }
                continue;
            }
            // Checkboxes and Lists: send as arrays if possible
            if (in_array($this->normalizeFieldType($field->type), ['checkbox', 'list'])) {
                if (!empty($fieldValue)) {
                    $ctmFieldName = $fieldMapping[$fieldId] ?? $this->getFieldName($field);
                    $arrayValue = is_array($fieldValue) ? $fieldValue : (is_serialized($fieldValue) ? unserialize($fieldValue) : explode(',', $fieldValue));
                    $arrayValue = array_filter((array)$arrayValue);
                    $mappedFields[$ctmFieldName] = $arrayValue;
                }
                continue;
            }
            // Unsupported field types: skip and log
            $supportedTypes = ['text','textarea','select','multiselect','number','phone','email','url','date','time','file','radio','checkbox','name','address','hidden','list','post_title','post_content','post_excerpt'];
            if (!in_array($this->normalizeFieldType($field->type), $supportedTypes)) {
                $this->logDebug("Field {$fieldId} ({$field->type}) skipped (unsupported type)");
                continue;
            }
            // Single field processing
            $fieldName = $this->getFieldName($field);
            $ctmFieldName = $fieldMapping[$fieldId] ?? $fieldName;
            $cleanValue = $this->sanitizeFieldValue($fieldValue, $field);
            if (!empty($cleanValue)) {
                $mappedFields[$ctmFieldName] = $cleanValue;
            }
        }
        return $mappedFields;
    }

    /**
     * Check if a field is a multi-part field (name, address, etc.)
     * 
     * @since 1.0.0
     * @param object $field The GF field object
     * @return bool True if field has multiple parts
     */
    private function isMultiPartField($field): bool
    {
        return in_array($field->type, ['name', 'address', 'time', 'date']);
    }

    /**
     * Check if a field is an administrative field that shouldn't be sent to CTM
     * 
     * @since 1.0.0
     * @param object $field The GF field object
     * @return bool True if field is administrative
     */
    private function isAdminField($field): bool
    {
        return in_array($field->type, ['page', 'section', 'html', 'captcha', 'password']);
    }

    /**
     * Get sub-fields for multi-part fields
     * 
     * @since 1.0.0
     * @param object $field The GF field object
     * @return array Array of sub-field information
     */
    private function getSubFields($field): array
    {
        $subFields = [];
        
        // Handle different multi-part field types
        switch ($field->type) {
            case 'name':
                $nameParts = ['prefix', 'first', 'middle', 'last', 'suffix'];
                foreach ($nameParts as $part) {
                    if (isset($field->inputs)) {
                        foreach ($field->inputs as $input) {
                            if (strpos($input['id'], ".{$part}") !== false) {
                                $subFields[] = [
                                    'id' => $input['id'],
                                    'name' => $field->label . ' - ' . ucfirst($part),
                                    'type' => 'text',
                                    'label' => $input['label'] ?? ucfirst($part),
                                    'required' => $field->isRequired ?? false,
                                    'gf_type' => 'name_part',
                                ];
                            }
                        }
                    }
                }
                break;
                
            case 'address':
                $addressParts = ['street', 'street2', 'city', 'state', 'zip', 'country'];
                foreach ($addressParts as $part) {
                    if (isset($field->inputs)) {
                        foreach ($field->inputs as $input) {
                            if (strpos($input['id'], ".{$part}") !== false) {
                                $subFields[] = [
                                    'id' => $input['id'],
                                    'name' => $field->label . ' - ' . ucfirst($part),
                                    'type' => 'text',
                                    'label' => $input['label'] ?? ucfirst($part),
                                    'required' => $field->isRequired ?? false,
                                    'gf_type' => 'address_part',
                                ];
                            }
                        }
                    }
                }
                break;
        }
        
        return $subFields;
    }

    /**
     * Process multi-part field data
     * 
     * @since 1.0.0
     * @param object $field        The GF field object
     * @param array  $entry        The entry data
     * @param array  $fieldMapping The field mapping configuration
     * @return array Processed sub-field data
     */
    private function processMultiPartField($field, array $entry, array $fieldMapping): array
    {
        $subFieldData = [];
        
        if (isset($field->inputs)) {
            foreach ($field->inputs as $input) {
                $inputId = $input['id'];
                $inputValue = $entry[$inputId] ?? '';
                
                if (!empty($inputValue)) {
                    $inputName = $input['label'] ?? "Field {$inputId}";
                    $ctmFieldName = $fieldMapping[$inputId] ?? $inputName;
                    $cleanValue = $this->sanitizeFieldValue($inputValue, $field);
                    
                    $subFieldData[$ctmFieldName] = $cleanValue;
                }
            }
        }
        
        return $subFieldData;
    }

    /**
     * Get a readable field name for a GF field
     * 
     * @since 1.0.0
     * @param object $field The GF field object
     * @return string The field name
     */
    private function getFieldName($field): string
    {
        // Use admin label if available, otherwise use label
        $adminLabel = isset($field->adminLabel) ? $field->adminLabel : null;
        $label = isset($field->label) ? $field->label : null;
        return $adminLabel ?: $label ?: "Field {$field->id}";
    }

    /**
     * Get field choices for select/radio/checkbox fields
     * 
     * @since 1.0.0
     * @param object $field The GF field object
     * @return array Array of field choices
     */
    private function getFieldChoices($field): array
    {
        if (!isset($field->choices) || !is_array($field->choices)) {
            return [];
        }
        
        $choices = [];
        foreach ($field->choices as $choice) {
            $choices[] = [
                'text' => $choice['text'] ?? '',
                'value' => $choice['value'] ?? '',
            ];
        }
        
        return $choices;
    }

    /**
     * Normalize GF field types to standard types
     * 
     * Converts Gravity Forms specific field types to more generic
     * field types that are easier to work with.
     * 
     * @since 1.0.0
     * @param string $gfType The original GF field type
     * @return string The normalized field type
     */
    private function normalizeFieldType(string $gfType): string
    {
        $typeMap = [
            'text' => 'text',
            'textarea' => 'textarea',
            'select' => 'select',
            'multiselect' => 'multiselect',
            'number' => 'number',
            'phone' => 'phone',
            'email' => 'email',
            'website' => 'url',
            'date' => 'date',
            'time' => 'time',
            'fileupload' => 'file',
            'radio' => 'radio',
            'checkbox' => 'checkbox',
            'name' => 'name',
            'address' => 'address',
            'hidden' => 'hidden',
            'list' => 'list',
            'post_title' => 'text',
            'post_content' => 'textarea',
            'post_excerpt' => 'textarea',
        ];
        
        return $typeMap[$gfType] ?? 'text';
    }

    /**
     * Sanitize and clean field values based on field type
     * 
     * Cleans form field values by removing unwanted characters,
     * handling arrays, and ensuring data safety based on field type.
     * 
     * @since 1.0.0
     * @param mixed  $value The raw field value
     * @param object $field The GF field object for context
     * @return string The sanitized field value
     */
    private function sanitizeFieldValue($value, $field): string
    {
        // Handle array values (checkboxes, multi-select, list fields)
        if (is_array($value)) {
            // Filter out empty values and join with commas
            $value = implode(', ', array_filter($value));
        }
        
        // Convert to string
        $value = (string) $value;
        
        // Field-specific sanitization
        $fieldType = isset($field->type) ? $field->type : 'text';
        switch ($fieldType) {
            case 'email':
                $value = sanitize_email($value);
                break;
            case 'website':
                $value = esc_url_raw($value);
                break;
            case 'phone':
                // Remove non-numeric characters except + and spaces
                $value = preg_replace('/[^0-9+\s\-\(\)]/', '', $value);
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
     * Extract UTM parameters from GF entry
     * 
     * @since 1.0.0
     * @param array $entry The GF entry data
     * @return array UTM parameters
     */
    private function extractUtmFromEntry(array $entry): array
    {
        $utmParams = [];
        $utmKeys = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content'];
        
        foreach ($utmKeys as $key) {
            if (!empty($entry[$key])) {
                $utmParams[$key] = sanitize_text_field($entry[$key]);
            }
        }
        
        return $utmParams;
    }

    /**
     * Get the form URL from entry data
     * 
     * @since 1.0.0
     * @param array $entry The GF entry data
     * @return string The form URL
     */
    private function getFormUrl(array $entry): string
    {
        return $entry['source_url'] ?? home_url();
    }

    /**
     * Get the client's IP address (fallback method)
     * 
     * @since 1.0.0
     * @return string The client IP address
     */
    private function getClientIpAddress(): string
    {
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    // Add debug logging helper
    private function logDebug($msg) {
        if (function_exists('error_log')) {
            error_log('[CTM GFService] ' . $msg);
        }
    }
} 