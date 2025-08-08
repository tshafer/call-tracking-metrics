<?php
/**
 * Form AJAX Handler
 * 
 * This file contains the FormAjax class which handles AJAX requests related to
 * form management including retrieving forms, form fields, and managing notices.
 * 
 * @package     CallTrackingMetrics
 * @subpackage  Admin\Ajax
 * @author      CallTrackingMetrics Team
 * @copyright   2024 CallTrackingMetrics
 * @license     GPL-2.0+
 * @version     2.0.0
 * @link        https://calltrackingmetrics.com
 * @since       1.0.0
 */

namespace CTM\Admin\Ajax;

/**
 * Form AJAX Request Handler
 * 
 * Handles AJAX requests related to form management including:
 * - Retrieving available forms (Contact Form 7 and Gravity Forms)
 * - Getting form fields for mapping
 * - Managing plugin notices and dismissals
 * 
 * This class provides a bridge between the frontend JavaScript and the backend
 * form plugins, allowing dynamic form and field selection in the admin interface.
 * 
 * @package     CallTrackingMetrics
 * @subpackage  Admin\Ajax
 * @author      CallTrackingMetrics Team
 * @since       1.0.0
 * @version     2.0.0
 */
class FormAjax {
    /**
     * Gravity Forms API class name
     * 
     * @since 1.0.0
     * @var string|null
     */
    private $gfapi;

    /**
     * Contact Form 7 class name
     * 
     * @since 1.0.0
     * @var string|null
     */
    private $wpcf7_contact_form;
    
    /**
     * Initialize Form AJAX handler
     * 
     * Sets up the form plugin class references for dependency injection.
     * If not provided, will attempt to detect available form plugins.
     * 
     * @since 1.0.0
     * @param string|null $gfapi               Optional Gravity Forms API class name
     * @param string|null $wpcf7_contact_form  Optional Contact Form 7 class name
     */
    public function __construct($gfapi = null, $wpcf7_contact_form = null) {
        $this->gfapi = $gfapi ?: (class_exists('GFAPI') ? 'GFAPI' : null);
        $this->wpcf7_contact_form = $wpcf7_contact_form ?: (class_exists('WPCF7_ContactForm') ? 'WPCF7_ContactForm' : null);
    }
    
    public function registerHandlers() {
        \add_action('wp_ajax_ctm_get_forms', [$this, 'ajaxGetForms']);
        \add_action('wp_ajax_ctm_get_fields', [$this, 'ajaxGetFields']);
        \add_action('wp_ajax_ctm_dismiss_notice', [$this, 'ajaxDismissNotice']);
    }
    
    public function ajaxGetForms(): void
    {
        check_ajax_referer('ctm_mapping_nonce', 'nonce');
        $type = sanitize_text_field($_POST['form_type'] ?? '');
        $forms = [];
        if ($type === 'gf' && $this->gfapi && class_exists($this->gfapi)) {
            $gf_forms = call_user_func([$this->gfapi, 'get_forms']);
            foreach ($gf_forms as $form) {
                $forms[] = ['id' => $form['id'], 'title' => $form['title'] ?? ''];
            }
        } elseif ($type === 'cf7' && $this->wpcf7_contact_form && class_exists($this->wpcf7_contact_form)) {
            $cf7_forms = call_user_func([$this->wpcf7_contact_form, 'find']);
            foreach ($cf7_forms as $form) {
                $forms[] = ['id' => $form->id(), 'title' => $form->title()];
            }
        }
        wp_send_json_success($forms);
    }
    
    public function ajaxGetFields(): void
    {
        check_ajax_referer('ctm_mapping_nonce', 'nonce');
        $type = sanitize_text_field($_POST['form_type'] ?? '');
        $form_id = sanitize_text_field($_POST['form_id'] ?? '');
        $fields = [];
        if (empty($form_id)) {
            wp_send_json_success([]);
            return;
        }
        if ($type === 'gf' && $this->gfapi && class_exists($this->gfapi)) {
            $form = call_user_func([$this->gfapi, 'get_form'], $form_id);
            if ($form && isset($form['fields'])) {
                foreach ($form['fields'] as $field) {
                    $fields[] = ['id' => $field['id'], 'label' => $field['label'] ?? $field['type']];
                }
            }
        } elseif ($type === 'cf7' && $this->wpcf7_contact_form && class_exists($this->wpcf7_contact_form)) {
            $form = call_user_func([$this->wpcf7_contact_form, 'get_instance'], $form_id);
            if ($form && method_exists($form, 'scan_form_tags')) {
                foreach ($form->scan_form_tags() as $tag) {
                    $fields[] = ['id' => $tag->name, 'label' => $tag->name];
                }
            }
        }
        wp_send_json_success($fields);
    }
    
    public function ajaxDismissNotice(): void
    {
        check_ajax_referer('ctm_dismiss_notice', 'nonce');
        $type = sanitize_text_field($_POST['notice_type'] ?? '');
        if ($type === 'cf7') {
            update_option('ctm_cf7_notice_dismissed', true);
            wp_send_json_success(['message' => 'CF7 notice dismissed.']);
        } elseif ($type === 'gf') {
            update_option('ctm_gf_notice_dismissed', true);
            wp_send_json_success(['message' => 'GF notice dismissed.']);
        }
        wp_send_json_error(['message' => 'Invalid notice type.']);
    }
} 