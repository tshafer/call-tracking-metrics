<?php
namespace CTM\Service;

/**
 * Handles Gravity Forms integration for CallTrackingMetrics.
 */
class GFService
{
    /**
     * Process a Gravity Forms submission and prepare data for API.
     * Handles checkboxes, lists, file uploads, address fields, name, product, consent, post fields, and more, with robust fallbacks.
     *
     * @param array $entry
     * @param array $form
     * @return array|null
     */
    public function processSubmission(array $entry, array $form): ?array
    {
        // Graceful fallback: check if form structure is valid
        if (!$form || !isset($form['fields']) || !is_array($form['fields'])) {
            if (function_exists('error_log')) error_log('CTM: GF form structure missing or invalid.');
            return null;
        }
        $fields = [];
        $post_data = [];
        foreach ($form['fields'] as $field) {
            $id = $field['id'] ?? null;
            $type = $field['type'] ?? '';
            if (!$id || (!isset($entry[$id]) && !in_array($type, ['address', 'name']))) continue;
            // Skip empty values for most types
            if (isset($entry[$id]) && ($entry[$id] === '' || $entry[$id] === null) && !in_array($type, ['checkbox', 'list', 'product', 'price', 'quantity', 'calculation', 'consent', 'post_title', 'post_content', 'post_excerpt', 'post_tags', 'post_category', 'post_custom_field', 'signature'])) continue;
            // Handle checkboxes (multiple values)
            if ($type === 'checkbox') {
                $fields[$id] = is_array($entry[$id]) ? implode(', ', $entry[$id]) : $entry[$id];
            // Handle lists (multi-row/column)
            } elseif ($type === 'list') {
                $fields[$id] = is_array($entry[$id]) ? json_encode($entry[$id]) : $entry[$id];
            // Handle file uploads
            } elseif ($type === 'fileupload') {
                $urls = is_array($entry[$id]) ? $entry[$id] : [$entry[$id]];
                $fields[$id] = implode(', ', array_filter($urls));
            // Handle address fields (compound)
            } elseif ($type === 'address') {
                $address = [
                    'street'  => $entry[$id . '.1'] ?? '',
                    'street2' => $entry[$id . '.2'] ?? '',
                    'city'    => $entry[$id . '.3'] ?? '',
                    'state'   => $entry[$id . '.4'] ?? '',
                    'zip'     => $entry[$id . '.5'] ?? '',
                    'country' => $entry[$id . '.6'] ?? '',
                ];
                if (implode('', $address) !== '') {
                    $fields[$id] = $address;
                }
            // Handle name fields (compound)
            } elseif ($type === 'name') {
                $name = [
                    'prefix'    => $entry[$id . '.2'] ?? '',
                    'first'     => $entry[$id . '.3'] ?? '',
                    'middle'    => $entry[$id . '.4'] ?? '',
                    'last'      => $entry[$id . '.6'] ?? '',
                    'suffix'    => $entry[$id . '.8'] ?? '',
                ];
                $name['full'] = trim(($name['prefix'] ? $name['prefix'] . ' ' : '') . $name['first'] . ' ' . ($name['middle'] ? $name['middle'] . ' ' : '') . $name['last'] . ($name['suffix'] ? ' ' . $name['suffix'] : ''));
                if (implode('', $name) !== '') {
                    $fields[$id] = $name;
                }
            // Handle consent fields
            } elseif ($type === 'consent') {
                $fields[$id] = !empty($entry[$id]) ? true : false;
            // Handle product/price/quantity fields
            } elseif (in_array($type, ['product', 'price', 'quantity'])) {
                $fields[$id] = [
                    'label' => $field['label'] ?? '',
                    'value' => $entry[$id],
                ];
            // Handle calculation fields
            } elseif ($type === 'calculation') {
                $fields[$id] = [
                    'value' => $entry[$id],
                    'formula' => $field['calculationFormula'] ?? '',
                ];
            // Handle post fields
            } elseif (in_array($type, ['post_title', 'post_content', 'post_excerpt', 'post_tags', 'post_category', 'post_custom_field'])) {
                $post_data[$type] = $entry[$id];
            // Handle signature fields
            } elseif ($type === 'signature') {
                $fields[$id] = $entry[$id]; // usually a base64 image or URL
            // Handle conditional logic: skip if field is hidden (if 'isHidden' is set)
            } elseif (isset($field['isHidden']) && $field['isHidden']) {
                continue;
            // Handle other supported types
            } elseif (in_array($type, ['text', 'textarea', 'email', 'phone', 'number', 'select', 'radio', 'date', 'time', 'website', 'hidden', 'password'])) {
                $fields[$id] = $entry[$id];
            // Edge case: unsupported field type
            } else {
                if (function_exists('error_log')) error_log('CTM: GF unsupported field type: ' . $type . ' (ID: ' . $id . ')');
            }
        }
        if (!empty($post_data)) {
            $fields['post_data'] = $post_data;
        }
        return $fields;
    }
} 