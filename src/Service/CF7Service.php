<?php
namespace CTM\Service;

/**
 * Handles Contact Form 7 integration for CallTrackingMetrics.
 */
class CF7Service
{
    /**
     * Process a Contact Form 7 submission and prepare data for API.
     *
     * @param \WPCF7_ContactForm $form
     * @param array $data
     * @return array
     */
    public function processSubmission($form, array $data): array
    {
        $fields = [];
        $labels = [];
        $sublabels = [];
        $files = [];
        $entry = $form->form_scan_shortcode();
        $name = $email = $phone = '';
        
        // Common field types to process
        $commonFieldTypes = [
            "text", "textarea", "select", "url", "number", "date", 
            "range", "color", "time", "datetime-local", "month", "week"
        ];

        foreach ($entry as $field) {
            $fieldName = $field["name"] ?? '';
            $fieldValue = $data[$fieldName] ?? '';
            $fieldType = $field["basetype"] ?? '';

            // Handle phone fields
            if (in_array($fieldType, ["tel", "intl_tel"])) {
                if ($fieldType === "intl_tel") {
                    $intl_regex = "/^\+(?:[0-9]?){6,14}[0-9]$/";
                    if (preg_match($intl_regex, $fieldValue)) {
                        $phone = $fieldValue;
                    }
                } else {
                    $phone = $fieldValue;
                }
            }
            // Handle email fields
            elseif ($fieldType === "email") {
                $email = $fieldValue;
            }
            // Handle name fields with fallback
            elseif ($fieldName === "your-name" || stripos($fieldName, 'name') !== false) {
                $name = $fieldValue ?: $name;
            }
            // Handle file uploads
            elseif ($fieldType === "file") {
                if (isset($_FILES[$fieldName]) && !empty($_FILES[$fieldName]['name'])) {
                    $files[$fieldName] = [
                        'name' => $_FILES[$fieldName]['name'],
                        'type' => $_FILES[$fieldName]['type'],
                        'tmp_name' => $_FILES[$fieldName]['tmp_name'],
                        'error' => $_FILES[$fieldName]['error'],
                        'size' => $_FILES[$fieldName]['size']
                    ];
                }
            }
            // Handle checkboxes and radios
            elseif (in_array($fieldType, ["checkbox", "radio"])) {
                $fields[$fieldName] = is_array($fieldValue) ? implode(', ', $fieldValue) : $fieldValue;
            }
            // Handle quizzes
            elseif ($fieldType === "quiz") {
                $hash = $data["_wpcf7_quiz_answer_" . $fieldName] ?? '';
                foreach ($field["raw_values"] as $answer) {
                    $answer_pos = strpos($answer, "|");
                    if ($answer_pos !== false) {
                        if ($hash === wp_hash(wpcf7_canonicalize(substr($answer, $answer_pos + 1)), 'wpcf7_quiz')) {
                            $fields[$fieldName] = $fieldValue;
                            $labels[$fieldName] = substr($answer, 0, $answer_pos);
                            break;
                        }
                    }
                }
            }
            // Handle common field types
            elseif (!empty($fieldName) && in_array($fieldType, $commonFieldTypes)) {
                $fields[$fieldName] = $fieldValue;
            }
            // Fallback for unknown field types
            elseif (!empty($fieldName)) {
                $fields[$fieldName] = $fieldValue;
            }
        }

        return [
            "name" => $name,
            "email" => $email,
            "phone" => $phone,
            "fields" => $fields,
            "labels" => $labels,
            "sublabels" => $sublabels,
            "files" => $files
        ];
    }
}