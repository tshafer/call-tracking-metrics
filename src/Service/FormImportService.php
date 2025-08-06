<?php
/**
 * Form Import Service
 * 
 * This file contains the FormImportService class that handles importing
 * forms from CallTrackingMetrics FormReactor into Contact Form 7 and Gravity Forms.
 * 
 * @package     CallTrackingMetrics
 * @subpackage  Service
 * @author      CallTrackingMetrics Team
 * @copyright   2024 CallTrackingMetrics
 * @license     GPL-2.0+
 * @version     2.0
 * @since       2.0.0
 */

namespace CTM\Service;

/**
 * Form Import Service Class
 * 
 * Handles importing forms from CallTrackingMetrics FormReactor into
 * Contact Form 7 and Gravity Forms. Provides functionality to:
 * - Fetch available forms from CTM API
 * - Convert CTM form structure to CF7/GF format
 * - Create forms in WordPress
 * - Map fields between systems
 * 
 * @since 2.0.0
 */
class FormImportService
{
    /**
     * API Service instance
     * 
     * @since 2.0.0
     * @var ApiService
     */
    private ApiService $apiService;

    /**
     * CF7 Service instance
     * 
     * @since 2.0.0
     * @var CF7Service
     */
    private CF7Service $cf7Service;

    /**
     * GF Service instance
     * 
     * @since 2.0.0
     * @var GFService
     */
    private GFService $gfService;

    /**
     * Initialize the form import service
     * 
     * @since 2.0.0
     * @param ApiService $apiService The API service for CTM communication
     * @param CF7Service $cf7Service The CF7 service for form creation
     * @param GFService $gfService The GF service for form creation
     */
    public function __construct(ApiService $apiService, CF7Service $cf7Service, GFService $gfService)
    {
        $this->apiService = $apiService;
        $this->cf7Service = $cf7Service;
        $this->gfService = $gfService;
    }

    /**
     * Get available forms from CallTrackingMetrics
     * 
     * @since 2.0.0
     * @param string $apiKey The API key
     * @param string $apiSecret The API secret
     * @return array|null Array of forms or null on failure
     */
    public function getAvailableForms(string $apiKey, string $apiSecret): ?array
    {
        try {
            error_log('CTM Debug: FormImportService::getAvailableForms - Starting');
            
            // Get forms from the form_reactors endpoint
            $formsResponse = $this->apiService->getFormsDirect($apiKey, $apiSecret);
            
            error_log('CTM Debug: getFormsDirect response: ' . ($formsResponse ? 'success' : 'null'));
            
            if (!$formsResponse) {
                error_log('CTM Form Import Error: No response from forms API');
                return null;
            }

            error_log('CTM Debug: Forms response keys: ' . implode(', ', array_keys($formsResponse)));

            // Handle the response format from /api/v1/accounts/{account_id}/form_reactors
            $forms = [];
            if (isset($formsResponse['forms'])) {
                // New format - forms array
                $forms = $formsResponse['forms'];
                error_log('CTM Debug: Using new format (forms array) - count: ' . count($forms));
            } elseif (isset($formsResponse['form_reactors'])) {
                // Old format - form_reactors array
                $forms = $formsResponse['form_reactors'];
                error_log('CTM Debug: Using old format (form_reactors array) - count: ' . count($forms));
            } else {
                // Direct array of forms
                $forms = $formsResponse;
                error_log('CTM Debug: Using direct array format - count: ' . count($forms));
            }

            if (empty($forms)) {
                error_log('CTM Form Import Error: No forms found in API response');
                return null;
            }

            // Filter and format forms for import
            $availableForms = [];
            foreach ($forms as $form) {
                if (isset($form['id']) && isset($form['name'])) {
                    error_log('CTM Debug: getAvailableForms - Processing form: ' . $form['name'] . ' (ID: ' . $form['id'] . ')');
                    error_log('CTM Debug: getAvailableForms - Form keys: ' . implode(', ', array_keys($form)));
                    
                    // Handle both old and new field structures
                    $fields = [];
                    if (isset($form['fields']) && is_array($form['fields'])) {
                        // Old format - fields
                        $fields = $form['fields'];
                        error_log('CTM Debug: getAvailableForms - Using old format (fields) - count: ' . count($fields));
                    } elseif (isset($form['custom_fields']) && is_array($form['custom_fields'])) {
                        // New format - custom_fields
                        $fields = $form['custom_fields'];
                        error_log('CTM Debug: getAvailableForms - Using new format (custom_fields) - count: ' . count($fields));
                    } else {
                        error_log('CTM Debug: getAvailableForms - No fields or custom_fields found in form');
                    }

                    $availableForms[] = [
                        'id' => $form['id'],
                        'name' => $form['name'],
                        'description' => $form['description'] ?? '',
                        'fields' => $fields,
                        'created_at' => $form['created_at'] ?? '',
                        'updated_at' => $form['updated_at'] ?? '',
                        'account_id' => $form['account_id'] ?? '',
                        // Include additional form properties for better mapping
                        'custom_fields' => $form['custom_fields'] ?? [],
                        'tracking_number' => $form['tracking_number'] ?? null,
                        'managed_mode' => $form['managed_mode'] ?? '',
                        'managed_id' => $form['managed_id'] ?? '',
                        'style' => $form['style'] ?? '',
                        'theme' => $form['theme'] ?? '',
                        'completion_text' => $form['completion_text'] ?? '',
                        'error_text' => $form['error_text'] ?? ''
                    ];
                    
                    error_log('CTM Debug: getAvailableForms - Added form with ' . count($fields) . ' fields');
                }
            }

            if (empty($availableForms)) {
                error_log('CTM Form Import Error: No valid forms found after processing');
                return null;
            }

            error_log('CTM Debug: Successfully processed ' . count($availableForms) . ' forms');
            return $availableForms;
        } catch (\Exception $e) {
            error_log('CTM Form Import Error (getAvailableForms): ' . $e->getMessage());
            error_log('CTM Debug: Exception stack trace: ' . $e->getTraceAsString());
            return null;
        }
    }

    /**
     * Get available forms with pagination support
     * 
     * @since 2.0.0
     * @param string $apiKey The API key
     * @param string $apiSecret The API secret
     * @param int $page Page number (default: 1)
     * @param int $perPage Items per page (default: 50, max: 100)
     * @return array|null Paginated form reactors or null on failure
     */
    public function getAvailableFormsPaginated(string $apiKey, string $apiSecret, int $page = 1, int $perPage = 50): ?array
    {
        try {
            // Get paginated forms from the form_reactors endpoint
            $formsResponse = $this->apiService->getFormsDirect($apiKey, $apiSecret, $page, $perPage);
            
            if (!$formsResponse) {
                error_log('CTM Form Import Error: No response from paginated forms API');
                return null;
            }

            // Handle the response format from /api/v1/accounts/{account_id}/form_reactors
            $forms = [];
            $pagination = [];
            
            if (isset($formsResponse['forms'])) {
                // New format - forms array
                $forms = $formsResponse['forms'];
                $pagination = [
                    'page' => $formsResponse['page'] ?? $page,
                    'per_page' => $formsResponse['per_page'] ?? $perPage,
                    'total_entries' => $formsResponse['total_entries'] ?? 0,
                    'total_pages' => $formsResponse['total_pages'] ?? 1,
                    'next_page' => $formsResponse['next_page'] ?? '',
                    'previous_page' => $formsResponse['previous_page'] ?? ''
                ];
            } elseif (isset($formsResponse['form_reactors'])) {
                // Old format - form_reactors array
                $forms = $formsResponse['form_reactors'];
                $pagination = $formsResponse['pagination'] ?? [];
            } else {
                // Direct array of forms
                $forms = $formsResponse;
            }

            if (empty($forms)) {
                error_log('CTM Form Import Error: No forms found in paginated API response');
                return null;
            }

            // Filter and format forms for import
            $availableForms = [];
            foreach ($forms as $form) {
                if (isset($form['id']) && isset($form['name'])) {
                    // Handle both old and new field structures
                    $fields = [];
                    if (isset($form['fields']) && is_array($form['fields'])) {
                        // Old format - fields
                        $fields = $form['fields'];
                    } elseif (isset($form['custom_fields']) && is_array($form['custom_fields'])) {
                        // New format - custom_fields
                        $fields = $form['custom_fields'];
                    }

                    $availableForms[] = [
                        'id' => $form['id'],
                        'name' => $form['name'],
                        'description' => $form['description'] ?? '',
                        'fields' => $fields,
                        'created_at' => $form['created_at'] ?? '',
                        'updated_at' => $form['updated_at'] ?? '',
                        'account_id' => $form['account_id'] ?? '',
                        // Include additional form properties for better mapping
                        'custom_fields' => $form['custom_fields'] ?? [],
                        'tracking_number' => $form['tracking_number'] ?? null,
                        'managed_mode' => $form['managed_mode'] ?? '',
                        'managed_id' => $form['managed_id'] ?? '',
                        'style' => $form['style'] ?? '',
                        'theme' => $form['theme'] ?? '',
                        'completion_text' => $form['completion_text'] ?? '',
                        'error_text' => $form['error_text'] ?? ''
                    ];
                }
            }

            if (empty($availableForms)) {
                error_log('CTM Form Import Error: No valid forms found after processing paginated response');
                return null;
            }

            return [
                'forms' => $availableForms,
                'pagination' => $pagination
            ];
        } catch (\Exception $e) {
            error_log('CTM Form Import Error (getAvailableFormsPaginated): ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get a specific form reactor by ID
     * 
     * @since 2.0.0
     * @param string $formReactorId The form reactor ID
     * @param string $apiKey The API key
     * @param string $apiSecret The API secret
     * @return array|null Form reactor data or null on failure
     */
    public function getFormReactorById(string $formReactorId, string $apiKey, string $apiSecret): ?array
    {
        try {
            // First get account information to get the account ID
            $accountInfo = $this->apiService->getAccountInfo($apiKey, $apiSecret);
            if (!$accountInfo || !isset($accountInfo['account']['id'])) {
                error_log('CTM Form Import Error: Could not retrieve account information');
                return null;
            }
            
            $accountId = $accountInfo['account']['id'];
            
            // Get specific form reactor
            $formReactor = $this->apiService->getFormReactorById($accountId, $formReactorId, $apiKey, $apiSecret);
            
            if (!$formReactor) {
                return null;
            }

            // Handle both old and new field structures
            $fields = [];
            if (isset($formReactor['fields']) && is_array($formReactor['fields'])) {
                $fields = $formReactor['fields'];
            } elseif (isset($formReactor['custom_fields']) && is_array($formReactor['custom_fields'])) {
                $fields = $formReactor['custom_fields'];
            }

            // Format the form reactor data
            return [
                'id' => $formReactor['id'],
                'name' => $formReactor['name'],
                'description' => $formReactor['description'] ?? '',
                'fields' => $fields,
                'created_at' => $formReactor['created_at'] ?? '',
                'updated_at' => $formReactor['updated_at'] ?? '',
                'account_id' => $accountId,
                // Include additional form properties for better mapping
                'custom_fields' => $formReactor['custom_fields'] ?? [],
                'tracking_number' => $formReactor['tracking_number'] ?? null,
                'managed_mode' => $formReactor['managed_mode'] ?? '',
                'managed_id' => $formReactor['managed_id'] ?? '',
                'style' => $formReactor['style'] ?? '',
                'theme' => $formReactor['theme'] ?? '',
                'completion_text' => $formReactor['completion_text'] ?? '',
                'error_text' => $formReactor['error_text'] ?? ''
            ];
        } catch (\Exception $e) {
            error_log('CTM Form Import Error (getFormReactorById): ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Check for duplicate forms in Contact Form 7
     * 
     * @since 2.0.0
     * @param array $ctmForm The CTM form data
     * @param string $formTitle The title for the new form
     * @return array|null Duplicate form info or null if no duplicate
     */
    public function checkForCF7Duplicate(array $ctmForm, string $formTitle): ?array
    {
        if (!class_exists('WPCF7_ContactForm')) {
            return null;
        }

        // Get all CF7 forms
        $cf7Forms = \WPCF7_ContactForm::find();
        
        if (empty($cf7Forms)) {
            return null;
        }

        // Generate the CF7 content for comparison
        $newFormContent = $this->convertToCF7Format($ctmForm);
        
        foreach ($cf7Forms as $existingForm) {
            // Check if the form content is identical
            $existingContent = $existingForm->prop('form');
            
            // Normalize whitespace for comparison
            $normalizedNew = preg_replace('/\s+/', ' ', trim($newFormContent));
            $normalizedExisting = preg_replace('/\s+/', ' ', trim($existingContent));
            
            if ($normalizedNew === $normalizedExisting) {
                return [
                    'form_id' => $existingForm->id(),
                    'form_title' => $existingForm->title(),
                    'form_content' => $existingContent,
                    'edit_url' => admin_url('admin.php?page=wpcf7&post=' . $existingForm->id() . '&action=edit')
                ];
            }
        }

        return null;
    }

    /**
     * Check for duplicate forms in Gravity Forms
     * 
     * @since 2.0.0
     * @param array $ctmForm The CTM form data
     * @param string $formTitle The title for the new form
     * @return array|null Duplicate form info or null if no duplicate
     */
    public function checkForGFDuplicate(array $ctmForm, string $formTitle): ?array
    {
        if (!class_exists('GFAPI')) {
            return null;
        }

        // Get all GF forms
        $gfForms = \GFAPI::get_forms();
        
        if (empty($gfForms)) {
            return null;
        }

        // Generate the GF structure for comparison
        $newFormData = $this->convertToGFFormat($ctmForm, $formTitle);
        
        foreach ($gfForms as $existingForm) {
            // Get full form details
            $fullForm = \GFAPI::get_form($existingForm['id']);
            
            if (!$fullForm || empty($fullForm['fields'])) {
                continue;
            }

            // Compare field structures (excluding IDs and form-specific data)
            if ($this->compareGFFormStructures($newFormData, $fullForm)) {
                return [
                    'form_id' => $existingForm['id'],
                    'form_title' => $existingForm['title'],
                    'form_data' => $fullForm,
                    'edit_url' => admin_url('admin.php?page=gf_edit_forms&id=' . $existingForm['id'])
                ];
            }
        }

        return null;
    }

    /**
     * Compare two GF form structures for similarity
     * 
     * @since 2.0.0
     * @param array $form1 First form data
     * @param array $form2 Second form data
     * @return bool True if forms are structurally identical
     */
    private function compareGFFormStructures(array $form1, array $form2): bool
    {
        $fields1 = $form1['fields'] ?? [];
        $fields2 = $form2['fields'] ?? [];

        if (count($fields1) !== count($fields2)) {
            return false;
        }

        // Sort fields by label for comparison
        usort($fields1, function($a, $b) {
            return strcmp($a['label'] ?? '', $b['label'] ?? '');
        });
        
        usort($fields2, function($a, $b) {
            $labelA = is_object($b) ? $b->label : ($b['label'] ?? '');
            $labelB = is_object($a) ? $a->label : ($a['label'] ?? '');
            return strcmp($labelA, $labelB);
        });

        // Compare each field
        for ($i = 0; $i < count($fields1); $i++) {
            $field1 = $fields1[$i];
            $field2 = $fields2[$i];
            
            // Convert field2 object to array if needed
            if (is_object($field2)) {
                $field2 = [
                    'label' => $field2->label ?? '',
                    'type' => $field2->type ?? '',
                    'isRequired' => $field2->isRequired ?? false
                ];
            }

            // Compare key properties
            if (($field1['label'] ?? '') !== ($field2['label'] ?? '') ||
                ($field1['type'] ?? '') !== ($field2['type'] ?? '') ||
                ($field1['isRequired'] ?? false) !== ($field2['isRequired'] ?? false)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Import a form to Contact Form 7
     * 
     * @since 2.0.0
     * @param array $ctmForm The CTM form data
     * @param string $formTitle The title for the new form
     * @return array|null Import result or null on failure
     */
    public function importToCF7(array $ctmForm, string $formTitle): ?array
    {
        error_log('CTM Debug: importToCF7 - Starting import for form: ' . $formTitle);
        
        if (!class_exists('WPCF7_ContactForm')) {
            error_log('CTM Debug: importToCF7 - Contact Form 7 is not installed');
            return ['success' => false, 'error' => 'Contact Form 7 is not installed'];
        }

        try {
            // Convert CTM form to CF7 format
            $cf7FormContent = $this->convertToCF7Format($ctmForm);
            
            error_log('CTM Debug: importToCF7 - Converted form content length: ' . strlen($cf7FormContent));
            
            // Create the form using CF7 API properly
            $formData = [
                'post_title' => $formTitle,
                'post_status' => 'publish',
                'post_type' => 'wpcf7_contact_form'
            ];

            error_log('CTM Debug: importToCF7 - Creating post with data: ' . json_encode($formData));
            
            $postId = wp_insert_post($formData);
            
            if (is_wp_error($postId)) {
                error_log('CTM Debug: importToCF7 - Failed to create post: ' . $postId->get_error_message());
                return ['success' => false, 'error' => 'Failed to create form: ' . $postId->get_error_message()];
            }

            error_log('CTM Debug: importToCF7 - Successfully created post with ID: ' . $postId);

            // Set form properties using CF7 API
            if (method_exists('\WPCF7_ContactForm', 'get_instance')) {
                $form = \WPCF7_ContactForm::get_instance($postId);
                if ($form && method_exists($form, 'set_properties')) {
                    $mailTemplate = $this->generateCF7MailTemplate($ctmForm);
                    $mail2Template = $this->generateCF7Mail2Template($ctmForm);
                    $messages = $this->generateCF7Messages($ctmForm);
                    
                    error_log('CTM Debug: importToCF7 - Setting form properties');
                    error_log('CTM Debug: importToCF7 - Mail template: ' . json_encode($mailTemplate));
                    
                    $form->set_properties([
                        'form' => $cf7FormContent,
                        'mail' => $mailTemplate,
                        'mail_2' => $mail2Template,
                        'messages' => $messages
                    ]);
                    
                    // Save the form to ensure the shortcode gets the correct ID
                    $form->save();
                    
                    error_log('CTM Debug: importToCF7 - Form properties set successfully');
                    error_log('CTM Debug: importToCF7 - Form saved with ID: ' . $postId);
                } else {
                    error_log('CTM Debug: importToCF7 - Could not set form properties - form or method not available');
                }
            } else {
                error_log('CTM Debug: importToCF7 - WPCF7_ContactForm::get_instance method not available');
            }

            // Store CTM metadata for tracking imported forms
            update_post_meta($postId, '_ctm_imported', true);
            update_post_meta($postId, '_ctm_form_id', $ctmForm['id'] ?? '');
            update_post_meta($postId, '_ctm_import_date', current_time('mysql'));
            update_post_meta($postId, '_ctm_form_data', json_encode($ctmForm));
            
            error_log('CTM Debug: importToCF7 - Import completed successfully with CTM metadata');
            
            return [
                'success' => true,
                'form_id' => $postId,
                'form_title' => $formTitle,
                'message' => 'Form imported successfully to Contact Form 7',
                'ctm_form_id' => $ctmForm['id'] ?? ''
            ];

        } catch (\Exception $e) {
            error_log('CTM Form Import Error (importToCF7): ' . $e->getMessage());
            error_log('CTM Debug: importToCF7 - Exception stack trace: ' . $e->getTraceAsString());
            return ['success' => false, 'error' => 'Import failed: ' . $e->getMessage()];
        }
    }

    /**
     * Import a form to Gravity Forms
     * 
     * @since 2.0.0
     * @param array $ctmForm The CTM form data
     * @param string $formTitle The title for the new form
     * @return array|null Import result or null on failure
     */
    public function importToGF(array $ctmForm, string $formTitle): ?array
    {
        if (!class_exists('GFAPI')) {
            return ['success' => false, 'error' => 'Gravity Forms is not installed'];
        }

        try {
            // Convert CTM form to GF format
            $gfFormData = $this->convertToGFFormat($ctmForm, $formTitle);
            
            // Create the form using GF API
            if (method_exists('\GFAPI', 'add_form')) {
                $formId = \GFAPI::add_form($gfFormData);
            } else {
                return ['success' => false, 'error' => 'Gravity Forms API is not available'];
            }
            
            if (is_wp_error($formId)) {
                return ['success' => false, 'error' => 'Failed to create form: ' . $formId->get_error_message()];
            }

            // Store CTM metadata for tracking imported forms
            gform_update_meta($formId, '_ctm_imported', true);
            gform_update_meta($formId, '_ctm_form_id', $ctmForm['id'] ?? '');
            gform_update_meta($formId, '_ctm_import_date', current_time('mysql'));
            gform_update_meta($formId, '_ctm_form_data', json_encode($ctmForm));

            return [
                'success' => true,
                'form_id' => $formId,
                'form_title' => $formTitle,
                'message' => 'Form imported successfully to Gravity Forms',
                'ctm_form_id' => $ctmForm['id'] ?? ''
            ];

        } catch (\Exception $e) {
            error_log('CTM Form Import Error (importToGF): ' . $e->getMessage());
            return ['success' => false, 'error' => 'Import failed: ' . $e->getMessage()];
        }
    }

    /**
     * Convert CTM form to CF7 format
     * 
     * @since 2.0.0
     * @param array $ctmForm The CTM form data
     * @return string The CF7 form content
     */
    public function convertToCF7Format(array $ctmForm): string
    {
        $formContent = '';
        
        error_log('CTM Debug: convertToCF7Format - Starting conversion');
        error_log('CTM Debug: convertToCF7Format - Form data keys: ' . implode(', ', array_keys($ctmForm)));
        error_log('CTM Debug: convertToCF7Format - Full form data: ' . json_encode($ctmForm, JSON_PRETTY_PRINT));
        
        // Handle both old and new field structures
        $fields = [];
        if (isset($ctmForm['fields']) && is_array($ctmForm['fields'])) {
            $fields = $ctmForm['fields'];
            error_log('CTM Debug: convertToCF7Format - Using old format (fields) - count: ' . count($fields));
        } elseif (isset($ctmForm['custom_fields']) && is_array($ctmForm['custom_fields'])) {
            $fields = $ctmForm['custom_fields'];
            error_log('CTM Debug: convertToCF7Format - Using new format (custom_fields) - count: ' . count($fields));
        } else {
            error_log('CTM Debug: convertToCF7Format - No fields or custom_fields found in form data');
            error_log('CTM Debug: convertToCF7Format - Available keys: ' . implode(', ', array_keys($ctmForm)));
        }
        
        if (!empty($fields)) {
            error_log('CTM Debug: convertToCF7Format - Processing ' . count($fields) . ' fields');
            foreach ($fields as $index => $field) {
                error_log('CTM Debug: convertToCF7Format - Field ' . $index . ' data: ' . json_encode($field));
                
                $fieldType = $field['type'] ?? 'text';
                $fieldName = $field['name'] ?? 'field_' . uniqid();
                $fieldLabel = $field['label'] ?? $fieldName;
                $required = isset($field['required']) && $field['required'] ? '*' : '';
                $halfWidth = isset($field['half_width']) && $field['half_width'] ? ' class="half-width"' : '';
                
                // Handle document fields by name if type doesn't match
                if (stripos($fieldLabel, 'document') !== false && !in_array($fieldType, ['file_upload', 'file', 'document', 'upload'])) {
                    $fieldType = 'document';
                }
                
                error_log("CTM Debug: convertToCF7Format - Processing field {$index}: {$fieldName} (type: {$fieldType}, required: " . ($required ? 'yes' : 'no') . ", label: {$fieldLabel})");
                
                // Start label tag
                $formContent .= "<label> {$fieldLabel}\n    ";
                
                switch ($fieldType) {
                    case 'email':
                        $formContent .= "[email{$required} {$fieldName} autocomplete:email]";
                        error_log("CTM Debug: convertToCF7Format - Added email field: [email{$required} {$fieldName} autocomplete:email]");
                        break;
                    case 'textarea':
                    case 'text_area':
                        $formContent .= "[textarea{$required} {$fieldName}]";
                        error_log("CTM Debug: convertToCF7Format - Added textarea field: [textarea{$required} {$fieldName}]");
                        break;
                    case 'number':
                    case 'decimal':
                        $formContent .= "[number{$required} {$fieldName}]";
                        error_log("CTM Debug: convertToCF7Format - Added number field: [number{$required} {$fieldName}]");
                        break;
                    case 'phone':
                        $formContent .= "[tel{$required} {$fieldName} autocomplete:tel]";
                        error_log("CTM Debug: convertToCF7Format - Added phone field: [tel{$required} {$fieldName} autocomplete:tel]");
                        break;
                       case 'website':
                       case 'url':
                           $formContent .= "[text{$required} {$fieldName}]";
                           error_log("CTM Debug: convertToCF7Format - Added website field as text: [text{$required} {$fieldName}]");
                           break;
                    case 'picker':
                    case 'select':
                    case 'choice_list':
                        $options = $field['options'] ?? [];
                        // Ensure options is an array
                        if (!is_array($options)) {
                            $options = [];
                        }
                        $optionsStr = implode('|', $options);
                        $formContent .= "[select{$required} {$fieldName} \"{$optionsStr}\"]";
                        error_log("CTM Debug: convertToCF7Format - Added select field: [select{$required} {$fieldName} \"{$optionsStr}\"]");
                        break;
                    case 'checkbox':
                        $formContent .= "[checkbox{$required} {$fieldName}]";
                        error_log("CTM Debug: convertToCF7Format - Added checkbox field: [checkbox{$required} {$fieldName}]");
                        break;
                    case 'radio':
                        $options = $field['options'] ?? [];
                        // Ensure options is an array
                        if (!is_array($options)) {
                            $options = [];
                        }
                        $optionsStr = implode('|', $options);
                        $formContent .= "[radio{$required} {$fieldName} \"{$optionsStr}\"]";
                        error_log("CTM Debug: convertToCF7Format - Added radio field: [radio{$required} {$fieldName} \"{$optionsStr}\"]");
                        break;
                    case 'information':
                        // Information fields are typically display-only, so we'll use a hidden field or text
                        $formContent .= "[text {$fieldName}]";
                        error_log("CTM Debug: convertToCF7Format - Added information field: [text {$fieldName}]");
                        break;
                    case 'captcha':
                        $formContent .= "[captchar]";
                        error_log("CTM Debug: convertToCF7Format - Added captcha field: [captchar]");
                        break;
                    case 'date':
                        $formContent .= "[date{$required} {$fieldName}]";
                        error_log("CTM Debug: convertToCF7Format - Added date field: [date{$required} {$fieldName}]");
                        break;
                    case 'file_upload':
                    case 'file':
                    case 'upload':
                    case 'document':
                        $fileType = $field['file_type'] ?? '';
                        $formContent .= "[file{$required} {$fieldName}]";
                        error_log("CTM Debug: convertToCF7Format - Added file field: [file{$required} {$fieldName}]");
                        break;
                    default:
                        // For text fields, add autocomplete if it's a name field
                        $autocomplete = '';
                        if (stripos($fieldName, 'name') !== false || stripos($fieldLabel, 'name') !== false) {
                            $autocomplete = ' autocomplete:name';
                        }
                        $formContent .= "[text{$required} {$fieldName}{$autocomplete}]";
                        error_log("CTM Debug: convertToCF7Format - Added default text field: [text{$required} {$fieldName}{$autocomplete}]");
                        break;
                }
                
                // Close label tag
                $formContent .= " </label>\n\n";
            }
        } else {
            error_log('CTM Debug: convertToCF7Format - No fields found to convert');
            // Add a default form if no fields are found
            $formContent = "<label> Your name
    [text* your-name autocomplete:name] </label>

<label> Your email
    [email* your-email autocomplete:email] </label>

<label> Subject
    [text* your-subject] </label>

<label> Your message (optional)
    [textarea your-message] </label>

[submit \"Submit\"]";
            error_log('CTM Debug: convertToCF7Format - Added default form content');
        }
        
        // Add submit button if not already present
        if (strpos($formContent, '[submit') === false) {
            $formContent .= "[submit \"Submit\"]\n";
            error_log("CTM Debug: convertToCF7Format - Added submit button");
        }
        
        error_log("CTM Debug: convertToCF7Format - Final form content:\n" . $formContent);
        
        return $formContent;
    }

    /**
     * Generate CF7 mail template
     * 
     * @since 2.0.0
     * @param array $ctmForm The CTM form data
     * @return array The mail template
     */
    private function generateCF7MailTemplate(array $ctmForm): array
    {
        $subject = 'New form submission from ' . ($ctmForm['name'] ?? 'Contact Form');
        $body = "You have received a new form submission.\n\n";
        
        // Handle both old and new field structures
        $fields = [];
        if (isset($ctmForm['fields']) && is_array($ctmForm['fields'])) {
            $fields = $ctmForm['fields'];
        } elseif (isset($ctmForm['custom_fields']) && is_array($ctmForm['custom_fields'])) {
            $fields = $ctmForm['custom_fields'];
        }
        
        if (!empty($fields)) {
            foreach ($fields as $field) {
                $fieldLabel = $field['label'] ?? $field['name'] ?? '';
                $fieldName = $field['name'] ?? '';
                if ($fieldName) {
                    $body .= "{$fieldLabel}: [{$fieldName}]\n";
                }
            }
        }
        
        return [
            'subject' => $subject,
            'sender' => '[your-name] <[your-email]>',
            'recipient' => get_option('admin_email'),
            'body' => $body,
            'additional_headers' => '',
            'attachments' => '',
            'use_html' => false,
            'exclude_blank' => false
        ];
    }

    /**
     * Generate CF7 mail2 template
     * 
     * @since 2.0.0
     * @param array $ctmForm The CTM form data
     * @return array The mail2 template
     */
    private function generateCF7Mail2Template(array $ctmForm): array
    {
        return [
            'subject' => 'Thank you for your submission',
            'sender' => get_option('blogname') . ' <' . get_option('admin_email') . '>',
            'recipient' => '[your-email]',
            'body' => "Thank you for submitting the form. We'll get back to you soon.\n\nBest regards,\n" . get_option('blogname'),
            'additional_headers' => '',
            'attachments' => '',
            'use_html' => false,
            'exclude_blank' => false
        ];
    }

    /**
     * Generate CF7 messages
     * 
     * @since 2.0.0
     * @param array $ctmForm The CTM form data
     * @return array The messages
     */
    private function generateCF7Messages(array $ctmForm): array
    {
        return [
            'mail_sent_ok' => 'Thank you for your message. It has been sent.',
            'mail_sent_ng' => 'There was an error trying to send your message. Please try again later.',
            'validation_error' => 'One or more fields have an error. Please check and try again.',
            'spam' => 'There was an error trying to send your message. Please try again later.',
            'accept_terms' => 'You must accept the terms and conditions before sending your message.',
            'invalid_required' => 'Please fill the required field.',
            'invalid_too_long' => 'This field has a too long response.',
            'invalid_too_short' => 'This field has a too short response.'
        ];
    }

    /**
     * Convert CTM form to GF format
     * 
     * @since 2.0.0
     * @param array $ctmForm The CTM form data
     * @param string $formTitle The form title
     * @return array The GF form data
     */
    public function convertToGFFormat(array $ctmForm, string $formTitle): array
    {
        $formData = [
            'title' => $formTitle,
            'description' => $ctmForm['description'] ?? '',
            'fields' => [],
            'button' => [
                'type' => 'text',
                'text' => 'Submit',
                'imageUrl' => ''
            ],
            'confirmations' => [
                'default' => [
                    'id' => uniqid(),
                    'name' => 'Default Confirmation',
                    'event' => 'form_saved',
                    'message' => $ctmForm['completion_text'] ?? 'Thank you for your submission.',
                    'isDefault' => true,
                    'conditionalLogic' => null
                ]
            ],
            'notifications' => [
                'default' => [
                    'id' => uniqid(),
                    'name' => 'Admin Notification',
                    'event' => 'form_submission',
                    'to' => get_option('admin_email'),
                    'subject' => 'New form submission from ' . $formTitle,
                    'message' => "You have received a new form submission.\n\n{all_fields}",
                    'from' => get_option('blogname') . ' <' . get_option('admin_email') . '>',
                    'replyTo' => '',
                    'routing' => null,
                    'conditionalLogic' => null,
                    'isActive' => true
                ]
            ]
        ];

        $fieldId = 1;
        
        // Handle both old and new field structures
        $fields = [];
        if (isset($ctmForm['fields']) && is_array($ctmForm['fields'])) {
            $fields = $ctmForm['fields'];
        } elseif (isset($ctmForm['custom_fields']) && is_array($ctmForm['custom_fields'])) {
            $fields = $ctmForm['custom_fields'];
        }
        
        if (!empty($fields)) {
            foreach ($fields as $field) {
                $fieldType = $field['type'] ?? 'text';
                $fieldLabel = $field['label'] ?? $field['name'] ?? 'Field ' . $fieldId;
                $required = isset($field['required']) && $field['required'];
                $halfWidth = isset($field['half_width']) && $field['half_width'];
                
                // Handle document fields by name if type doesn't match
                if (stripos($fieldLabel, 'document') !== false && !in_array($fieldType, ['file_upload', 'file', 'document', 'upload'])) {
                    $fieldType = 'document';
                }
                
                $gfField = [
                    'id' => $fieldId,
                    'label' => $fieldLabel,
                    'type' => $this->mapFieldTypeToGF($fieldType),
                    'isRequired' => $required,
                    'size' => $halfWidth ? 'small' : 'medium',
                    'defaultValue' => '',
                    'choices' => null,
                    'inputs' => null,
                    'conditionalLogic' => null
                ];

                // Handle specific field types
                if (in_array($fieldType, ['select', 'radio', 'picker', 'choice_list'])) {
                    $options = $field['options'] ?? [];
                    $choices = [];
                    foreach ($options as $option) {
                        $choices[] = [
                            'text' => $option,
                            'value' => $option,
                            'isSelected' => false
                        ];
                    }
                    $gfField['choices'] = $choices;
                }

                // Handle information fields (display-only)
                if ($fieldType === 'information') {
                    $gfField['content'] = $field['content'] ?? $fieldLabel;
                }

                // Handle file upload fields
                if (in_array($fieldType, ['file_upload', 'file', 'upload', 'document'])) {
                    $gfField['multipleFiles'] = false;
                    $gfField['allowedExtensions'] = $field['file_type'] ?? '';
                }

                // Handle CAPTCHA fields
                if ($fieldType === 'captcha') {
                    $gfField['captchaType'] = 'recaptcha';
                    $gfField['captchaTheme'] = 'light';
                    $gfField['captchaSize'] = 'normal';
                }

                // Handle date fields with restrictions
                if ($fieldType === 'date') {
                    if (isset($field['disable_before']) && $field['disable_before']) {
                        $gfField['dateFormat'] = 'mdy';
                        $gfField['calendarIconType'] = 'calendar';
                        $gfField['dateType'] = 'datepicker';
                    }
                }

                $formData['fields'][] = $gfField;
                $fieldId++;
            }
        }

        return $formData;
    }

    /**
     * Map CTM field type to GF field type
     * 
     * @since 2.0.0
     * @param string $ctmType The CTM field type
     * @return string The GF field type
     */
    private function mapFieldTypeToGF(string $ctmType): string
    {
        $typeMap = [
            'text' => 'text',
            'email' => 'email',
            'textarea' => 'textarea',
            'text_area' => 'textarea',
            'select' => 'select',
            'picker' => 'select',
            'choice_list' => 'select',
            'checkbox' => 'checkbox',
            'radio' => 'radio',
            'number' => 'number',
            'decimal' => 'number',
            'phone' => 'phone',
            'url' => 'url',
            'website' => 'url',
            'date' => 'date',
            'time' => 'time',
            'file_upload' => 'fileupload',
            'file' => 'fileupload',
            'upload' => 'fileupload',
            'document' => 'fileupload',
            'information' => 'html',
            'captcha' => 'captcha'
        ];

        return $typeMap[$ctmType] ?? 'text';
    }

    /**
     * Validate import parameters
     * 
     * @since 2.0.0
     * @param array $params The import parameters
     * @return array Validation result
     */
    public function validateImportParams(array $params): array
    {
        $errors = [];

        if (empty($params['ctm_form_id'])) {
            $errors[] = 'CTM form ID is required';
        }

        if (empty($params['form_title'])) {
            $errors[] = 'Form title is required';
        }

        if (empty($params['target_type'])) {
            $errors[] = 'Target form type is required';
        }

        if (!in_array($params['target_type'], ['cf7', 'gf'])) {
            $errors[] = 'Invalid target form type';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Check if a CTM form is already imported
     * 
     * @since 2.0.0
     * @param string $ctmFormId The CTM form ID to check
     * @return array|null Import info if found, null otherwise
     */
    public function getImportedFormInfo(string $ctmFormId): ?array
    {
        // Check Contact Form 7
        if (class_exists('WPCF7_ContactForm')) {
            $cf7Forms = \WPCF7_ContactForm::find(['posts_per_page' => -1]);
            foreach ($cf7Forms as $form) {
                $importedCtmId = get_post_meta($form->id(), '_ctm_form_id', true);
                if ($importedCtmId === $ctmFormId) {
                    return [
                        'type' => 'cf7',
                        'form_id' => $form->id(),
                        'form_title' => $form->title(),
                        'import_date' => get_post_meta($form->id(), '_ctm_import_date', true),
                        'edit_url' => admin_url('admin.php?page=wpcf7&post=' . $form->id() . '&action=edit')
                    ];
                }
            }
        }

        // Check Gravity Forms
        if (class_exists('GFAPI')) {
            $gfForms = \GFAPI::get_forms();
            foreach ($gfForms as $form) {
                $importedCtmId = gform_get_meta($form['id'], '_ctm_form_id');
                if ($importedCtmId === $ctmFormId) {
                    return [
                        'type' => 'gf',
                        'form_id' => $form['id'],
                        'form_title' => $form['title'],
                        'import_date' => gform_get_meta($form['id'], '_ctm_import_date'),
                        'edit_url' => admin_url('admin.php?page=gf_edit_forms&id=' . $form['id'])
                    ];
                }
            }
        }

        return null;
    }
} 