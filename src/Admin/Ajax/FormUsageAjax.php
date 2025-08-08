<?php
/**
 * Form Usage AJAX Handler
 * 
 * Handles AJAX requests for finding where forms are being used on the website.
 * 
 * @since 2.0.0
 * @package CTM\Admin\Ajax
 */

namespace CTM\Admin\Ajax;

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Form Usage AJAX Handler Class
 * 
 * @since 2.0.0
 */
class FormUsageAjax
{
    /**
     * Logging system instance
     * 
     * @var \CTM\Admin\LoggingSystem
     */
    private $loggingSystem;

    /**
     * Constructor
     * 
     * @since 2.0.0
     */
    public function __construct()
    {
        $this->loggingSystem = new \CTM\Admin\LoggingSystem();
        
        // Register AJAX handlers
        add_action('wp_ajax_ctm_get_form_usage', [$this, 'ajaxGetFormUsage']);
        add_action('wp_ajax_ctm_clear_form_usage_cache', [$this, 'ajaxClearFormUsageCache']);
    }

    /**
     * AJAX handler for getting form usage
     * 
     * @since 2.0.0
     * @return void
     */
    public function ajaxGetFormUsage(): void
    {
        check_ajax_referer('ctm_form_usage_nonce', 'nonce');
        
        $form_id = (int) ($_POST['form_id'] ?? 0);
        $form_type = sanitize_text_field($_POST['form_type'] ?? '');
        $force_refresh = (bool) ($_POST['force_refresh'] ?? false);
        
        if (empty($form_id) || empty($form_type)) {
            wp_send_json_error(['message' => 'Form ID and form type are required']);
        }
        
        try {
            $usage_data = $this->getFormUsage($form_id, $form_type, $force_refresh);
            wp_send_json_success($usage_data);
        } catch (\Exception $e) {
            $this->logInternal('Form Usage Error: ' . $e->getMessage(), 'error');
            wp_send_json_error(['message' => 'Failed to get form usage: ' . $e->getMessage()]);
        }
    }

    /**
     * AJAX handler for clearing form usage cache
     * 
     * @since 2.0.0
     * @return void
     */
    public function ajaxClearFormUsageCache(): void
    {
        check_ajax_referer('ctm_form_usage_nonce', 'nonce');

        $form_id = (int) ($_POST['form_id'] ?? 0);
        $form_type = sanitize_text_field($_POST['form_type'] ?? '');

        if (empty($form_id) || empty($form_type)) {
            wp_send_json_error(['message' => 'Form ID and form type are required for cache clearing']);
        }

        $cache_key = 'ctm_form_usage_' . $form_type . '_' . $form_id;
        $deleted = delete_transient($cache_key);

        if ($deleted) {
            wp_send_json_success(['message' => 'Form usage cache cleared for form ' . $form_id . ' (Type: ' . $form_type . ')']);
        } else {
            wp_send_json_error(['message' => 'Form usage cache not found for form ' . $form_id . ' (Type: ' . $form_type . ')']);
        }
    }

    /**
     * Get form usage data
     * 
     * @since 2.0.0
     * @param int $form_id The form ID
     * @param string $form_type The form type (cf7 or gf)
     * @param bool $force_refresh Force a fresh search if true
     * @return array Usage data
     */
    private function getFormUsage(int $form_id, string $form_type, bool $force_refresh = false): array
    {
        // Check cache first (unless force refresh is requested)
        if (!$force_refresh) {
            $cache_key = 'ctm_form_usage_' . $form_type . '_' . $form_id;
            $cached_data = get_transient($cache_key);
            if ($cached_data !== false) {
                return $cached_data;
            }
        }
        
        $usage_data = [
            'pages' => [],
            'widgets' => [],
            'shortcodes' => [],
            'theme_files' => []
        ];

        // Get shortcode for the form
        $shortcode = $this->getFormShortcode($form_id, $form_type);
        
        // Temporary testing for form 64
        if ($form_id == 64 && $form_type == 'gf') {
            $this->logInternal('Testing form 64 - Shortcode: ' . ($shortcode ?: 'null'), 'debug');
            
            // Check if GF form exists
            if (class_exists('GFAPI')) {
                try {
                    $form = \GFAPI::get_form(64);
                    if ($form) {
                        $this->logInternal('Form 64 exists in GF system - Title: ' . $form['title'], 'debug');
                    } else {
                        $this->logInternal('Form 64 does not exist in GF system', 'debug');
                    }
                } catch (\Exception $e) {
                    $this->logInternal('Error checking form 64: ' . $e->getMessage(), 'debug');
                }
            }
            
            // Direct test of post 105 content
            $post_105 = get_post(105);
            if ($post_105) {
                $content = $post_105->post_content;
                $this->logInternal('Post 105 content length: ' . strlen($content), 'debug');
                
                // Test specific patterns
                $patterns_to_test = [
                    'gform_64',
                    'gform_wrapper_64',
                    'data-formid="64"',
                    'data-formid=\'64\'',
                    'gformInitSpinner(64)'
                ];
                
                foreach ($patterns_to_test as $pattern) {
                    $found = strpos($content, $pattern) !== false;
                    $this->logInternal('Pattern "' . $pattern . '" found: ' . ($found ? 'yes' : 'no'), 'debug');
                }
            } else {
                $this->logInternal('Post 105 not found', 'debug');
            }
        }
        
        if ($shortcode) {
            // Search for shortcode usage in posts and pages
            $usage_data['pages'] = $this->searchShortcodeInPosts($shortcode);
            
            // Search for shortcode usage in widgets
            $usage_data['widgets'] = $this->searchShortcodeInWidgets($shortcode);
            
            // Search for shortcode usage in theme files
            $usage_data['theme_files'] = $this->searchShortcodeInThemeFiles($shortcode);
            
            // Get shortcode usage statistics
            $usage_data['shortcodes'] = $this->getShortcodeUsageStats($shortcode);
            
            // Enhanced search: Look for form ID in various contexts
            $usage_data['enhanced_pages'] = $this->searchEnhancedFormUsage($form_id, $form_type);
            
            // Search in custom post types
            $usage_data['custom_post_types'] = $this->searchCustomPostTypes($form_id, $form_type);
            
            // Search in theme files
            $usage_data['theme_files_forms'] = $this->searchThemeFilesForForms($form_id, $form_type);
        }
        
        // Cache the results for 1 hour (3600 seconds)
        $cache_key = 'ctm_form_usage_' . $form_type . '_' . $form_id;
        set_transient($cache_key, $usage_data, 3600);
        
        return $usage_data;
    }

    /**
     * Get the shortcode for a form
     * 
     * @since 2.0.0
     * @param int $form_id The form ID
     * @param string $form_type The form type (cf7 or gf)
     * @return string|null The shortcode or null if not found
     */
    private function getFormShortcode(int $form_id, string $form_type): ?string
    {
        if ($form_type === 'cf7') {
            // For CF7, check if the form exists
            if (class_exists('WPCF7_ContactForm')) {
                $form = \WPCF7_ContactForm::get_instance($form_id);
                if ($form) {
                    return '[contact-form-7 id="' . $form_id . '"]';
                }
            }
        } elseif ($form_type === 'gf') {
            // For GF, check if the form exists
            if (class_exists('GFAPI')) {
                try {
                    $form = \GFAPI::get_form($form_id);
                    if ($form) {
                        return '[gravityform id="' . $form_id . '"]';
                    }
                } catch (\Exception $e) {
                    $this->logInternal('Error getting GF form ' . $form_id . ': ' . $e->getMessage(), 'error');
                }
            }
        }
        
        return null;
    }

    /**
     * Search for shortcode usage in posts and pages
     * 
     * @since 2.0.0
     * @param string $shortcode The shortcode to search for
     * @return array Array of pages/posts using the shortcode
     */
    private function searchShortcodeInPosts(string $shortcode): array
    {
        $posts = get_posts([
            'post_type' => ['post', 'page'],
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'meta_query' => [
                [
                    'key' => '_wp_page_template',
                    'compare' => 'NOT EXISTS'
                ]
            ]
        ]);

        $usage = [];
        
        foreach ($posts as $post) {
            if (strpos($post->post_content, $shortcode) !== false) {
                $usage[] = [
                    'id' => $post->ID,
                    'title' => $post->post_title,
                    'type' => $post->post_type === 'page' ? 'Page' : 'Post',
                    'edit_url' => get_edit_post_link($post->ID),
                    'view_url' => get_permalink($post->ID)
                ];
            }
        }

        return $usage;
    }

    /**
     * Search for shortcode usage in widgets
     * 
     * @since 2.0.0
     * @param string $shortcode The shortcode to search for
     * @return array Array of widgets using the shortcode
     */
    private function searchShortcodeInWidgets(string $shortcode): array
    {
        $usage = [];
        
        // Get all widget areas
        global $wp_registered_sidebars;
        
        if (!$wp_registered_sidebars) {
            return $usage;
        }

        foreach ($wp_registered_sidebars as $sidebar_id => $sidebar) {
            $widgets = get_option('sidebars_widgets');
            
            if (!isset($widgets[$sidebar_id])) {
                continue;
            }

            foreach ($widgets[$sidebar_id] as $widget_id) {
                $widget_data = get_option('widget_' . str_replace('-', '_', $widget_id));
                
                if (!$widget_data) {
                    continue;
                }

                // Check text widgets for shortcode usage
                if (strpos($widget_id, 'text') !== false) {
                    foreach ($widget_data as $instance) {
                        if (isset($instance['text']) && strpos($instance['text'], $shortcode) !== false) {
                            $usage[] = [
                                'title' => $instance['title'] ?: 'Text Widget',
                                'area' => $sidebar['name'],
                                'edit_url' => admin_url('widgets.php')
                            ];
                        }
                    }
                }
            }
        }

        return $usage;
    }

    /**
     * Search for shortcode usage in theme files
     * 
     * @since 2.0.0
     * @param string $shortcode The shortcode to search for
     * @return array Array of theme files using the shortcode
     */
    private function searchShortcodeInThemeFiles(string $shortcode): array
    {
        $usage = [];
        $theme_dir = get_template_directory();
        
        // Common theme files to check
        $theme_files = [
            'header.php',
            'footer.php',
            'sidebar.php',
            'index.php',
            'single.php',
            'page.php',
            'functions.php'
        ];

        foreach ($theme_files as $file) {
            $file_path = $theme_dir . '/' . $file;
            
            if (file_exists($file_path)) {
                $content = file_get_contents($file_path);
                
                if (strpos($content, $shortcode) !== false) {
                    $count = substr_count($content, $shortcode);
                    $usage[] = [
                        'name' => $file,
                        'path' => str_replace(ABSPATH, '', $file_path),
                        'count' => $count
                    ];
                }
            }
        }

        return $usage;
    }

    /**
     * Get shortcode usage statistics
     * 
     * @since 2.0.0
     * @param string $shortcode The shortcode to analyze
     * @return array Shortcode usage statistics
     */
    private function getShortcodeUsageStats(string $shortcode): array
    {
        $stats = [];
        
        // Count usage in posts/pages
        $posts_count = $this->countShortcodeInPosts($shortcode);
        if ($posts_count > 0) {
            $stats[] = [
                'location' => 'Posts & Pages',
                'count' => $posts_count,
                'code' => $shortcode
            ];
        }

        // Count usage in widgets
        $widgets_count = $this->countShortcodeInWidgets($shortcode);
        if ($widgets_count > 0) {
            $stats[] = [
                'location' => 'Widgets',
                'count' => $widgets_count,
                'code' => $shortcode
            ];
        }

        // Count usage in theme files
        $theme_count = $this->countShortcodeInThemeFiles($shortcode);
        if ($theme_count > 0) {
            $stats[] = [
                'location' => 'Theme Files',
                'count' => $theme_count,
                'code' => $shortcode
            ];
        }

        return $stats;
    }

    /**
     * Count shortcode usage in posts
     * 
     * @since 2.0.0
     * @param string $shortcode The shortcode to count
     * @return int Count of usage
     */
    private function countShortcodeInPosts(string $shortcode): int
    {
        $posts = get_posts([
            'post_type' => ['post', 'page'],
            'post_status' => 'publish',
            'posts_per_page' => -1
        ]);

        $count = 0;
        
        foreach ($posts as $post) {
            $count += substr_count($post->post_content, $shortcode);
        }

        return $count;
    }

    /**
     * Count shortcode usage in widgets
     * 
     * @since 2.0.0
     * @param string $shortcode The shortcode to count
     * @return int Count of usage
     */
    private function countShortcodeInWidgets(string $shortcode): int
    {
        $count = 0;
        
        global $wp_registered_sidebars;
        
        if (!$wp_registered_sidebars) {
            return $count;
        }

        foreach ($wp_registered_sidebars as $sidebar_id => $sidebar) {
            $widgets = get_option('sidebars_widgets');
            
            if (!isset($widgets[$sidebar_id])) {
                continue;
            }

            foreach ($widgets[$sidebar_id] as $widget_id) {
                $widget_data = get_option('widget_' . str_replace('-', '_', $widget_id));
                
                if (!$widget_data) {
                    continue;
                }

                if (strpos($widget_id, 'text') !== false) {
                    foreach ($widget_data as $instance) {
                        if (isset($instance['text'])) {
                            $count += substr_count($instance['text'], $shortcode);
                        }
                    }
                }
            }
        }

        return $count;
    }

    /**
     * Count shortcode usage in theme files
     * 
     * @since 2.0.0
     * @param string $shortcode The shortcode to count
     * @return int Count of usage
     */
    private function countShortcodeInThemeFiles(string $shortcode): int
    {
        $count = 0;
        $theme_dir = get_template_directory();
        
        $theme_files = [
            'header.php',
            'footer.php',
            'sidebar.php',
            'index.php',
            'single.php',
            'page.php',
            'functions.php'
        ];

        foreach ($theme_files as $file) {
            $file_path = $theme_dir . '/' . $file;
            
            if (file_exists($file_path)) {
                $content = file_get_contents($file_path);
                $count += substr_count($content, $shortcode);
            }
        }

        return $count;
    }

    /**
     * Enhanced search for form usage in various contexts
     * 
     * @since 2.0.0
     * @param int $form_id The form ID
     * @param string $form_type The form type
     * @return array Array of pages/posts using the form
     */
    private function searchEnhancedFormUsage(int $form_id, string $form_type): array
    {
        $usage = [];
        
        // Get all posts and pages
        $posts = get_posts([
            'post_type' => ['post', 'page'],
            'post_status' => 'publish',
            'posts_per_page' => -1
        ]);
        
        foreach ($posts as $post) {
            $content = $post->post_content;
            $found = false;
            $match_type = '';
            $match_details = '';
            
            // Also check post meta and other content areas
            $meta_content = '';
            $meta_fields = get_post_meta($post->ID);
            foreach ($meta_fields as $key => $values) {
                if (is_array($values)) {
                    foreach ($values as $value) {
                        $meta_content .= ' ' . $value;
                    }
                }
            }
            
            // Combine content for searching
            $search_content = $content . ' ' . $meta_content;
            
            // Search for various patterns
            $patterns = [
                // Exact shortcode patterns
                'cf7' => [
                    '[contact-form-7 id="' . $form_id . '"]',
                    '[contact-form-7 id=\'' . $form_id . '\']',
                    '[contact-form-7 id=' . $form_id . ']',
                    'contact-form-7 id="' . $form_id . '"',
                    'contact-form-7 id=\'' . $form_id . '\'',
                    'contact-form-7 id=' . $form_id,
                    'wpcf7-form',
                    'wpcf7-form-' . $form_id,
                    'wpcf7-response-output',
                    'wpcf7-mail-sent-ok',
                    'wpcf7-validation-errors',
                    'wpcf7-spinner',
                    'wpcf7-submit'
                ],
                'gf' => [
                    '[gravityform id="' . $form_id . '"]',
                    '[gravityform id=\'' . $form_id . '\']',
                    '[gravityform id=' . $form_id . ']',
                    'gravityform id="' . $form_id . '"',
                    'gravityform id=\'' . $form_id . '\'',
                    'gravityform id=' . $form_id,
                    'gform_' . $form_id,
                    'gform_wrapper_' . $form_id,
                    'gform_submit_button_' . $form_id,
                    'gform_ajax_frame_' . $form_id,
                    'gform_confirmation_wrapper_' . $form_id,
                    'gform_confirmation_message_' . $form_id,
                    'gformInitSpinner(' . $form_id,
                    'gformInitDatepicker',
                    'gformInitPriceFields',
                    'gform_page_loaded',
                    'gform_confirmation_loaded',
                    'gform_pre_post_render',
                    'data-formid=\'' . $form_id . '\' novalidate',
                    'data-formid="' . $form_id . '" novalidate'
                ]
            ];
            
            // Check for patterns
            if (isset($patterns[$form_type])) {
                foreach ($patterns[$form_type] as $pattern) {
                    if (strpos($search_content, $pattern) !== false) {
                        $found = true;
                        $match_type = 'shortcode_pattern';
                        $match_details = $pattern;
                        break;
                    }
                }
            }
            
            // Check for form ID in various contexts
            $form_id_patterns = [
                'id="' . $form_id . '"',
                'id=\'' . $form_id . '\'',
                'id=' . $form_id,
                'form_id="' . $form_id . '"',
                'form_id=\'' . $form_id . '\'',
                'form_id=' . $form_id,
                'form=' . $form_id,
                'form="' . $form_id . '"',
                'form=\'' . $form_id . '\'',
                'data-formid="' . $form_id . '"',
                'data-formid=\'' . $form_id . '\'',
                'data-formid=' . $form_id,
                'data-formid=\'' . $form_id . '\' novalidate',
                'data-formid="' . $form_id . '" novalidate'
            ];
            
            // Add Gravity Forms specific patterns
            if ($form_type === 'gf') {
                $form_id_patterns = array_merge($form_id_patterns, [
                    'gform_' . $form_id,
                    'gform_wrapper_' . $form_id,
                    'gform_submit_button_' . $form_id,
                    'gform_ajax_frame_' . $form_id,
                    'gform_confirmation_wrapper_' . $form_id,
                    'gform_confirmation_message_' . $form_id,
                    'gform_validation_error',
                    'gform_gravityforms',
                    'gformInitSpinner(' . $form_id,
                    'gformInitDatepicker',
                    'gformInitPriceFields',
                    'gform_page_loaded',
                    'gform_confirmation_loaded',
                    'gform_pre_post_render'
                ]);
            }
            
            // Add Contact Form 7 specific patterns
            if ($form_type === 'cf7') {
                $form_id_patterns = array_merge($form_id_patterns, [
                    'wpcf7-form',
                    'wpcf7-form-' . $form_id,
                    'wpcf7-response-output',
                    'wpcf7-mail-sent-ok',
                    'wpcf7-validation-errors',
                    'wpcf7-spinner',
                    'wpcf7-submit',
                    'contact-form-7'
                ]);
            }
            
            foreach ($form_id_patterns as $pattern) {
                if (strpos($search_content, $pattern) !== false) {
                    $found = true;
                    $match_type = 'form_id_pattern';
                    $match_details = $pattern;
                    break;
                }
            }
            
            // Check for form title or name references
            if ($form_type === 'cf7') {
                $cf7_form = \WPCF7_ContactForm::get_instance($form_id);
                if ($cf7_form) {
                    $form_title = $cf7_form->title();
                    if (strpos($search_content, $form_title) !== false) {
                        $found = true;
                        $match_type = 'form_title';
                        $match_details = $form_title;
                    }
                }
            } elseif ($form_type === 'gf') {
                $gf_form = \GFAPI::get_form($form_id);
                if ($gf_form) {
                    $form_title = $gf_form['title'];
                    if (strpos($search_content, $form_title) !== false) {
                        $found = true;
                        $match_type = 'form_title';
                        $match_details = $form_title;
                    }
                }
            }
            
            // Temporary debugging for post 105
            if ($post->ID == 105) {
                $this->logInternal('Post 105 search - Content length: ' . strlen($content) . ', Meta length: ' . strlen($meta_content) . ', Found: ' . ($found ? 'yes' : 'no') . ', Match: ' . $match_type, 'debug');
                if (!$found) {
                    $this->logInternal('Post 105 - Checking for gform_64: ' . (strpos($search_content, 'gform_64') !== false ? 'found' : 'not found'), 'debug');
                    $this->logInternal('Post 105 - Checking for data-formid: ' . (strpos($search_content, 'data-formid') !== false ? 'found' : 'not found'), 'debug');
                }
            }
            
            if ($found) {
                $usage[] = [
                    'id' => $post->ID,
                    'title' => $post->post_title,
                    'type' => $post->post_type === 'page' ? 'Page' : 'Post',
                    'edit_url' => get_edit_post_link($post->ID),
                    'view_url' => get_permalink($post->ID),
                    'match_type' => $match_type,
                    'match_details' => $match_details,
                    'url' => get_permalink($post->ID)
                ];
            }
        }
        
        return $usage;
    }

    /**
     * Search for form usage in custom post types and other contexts
     * 
     * @since 2.0.0
     * @param int $form_id The form ID
     * @param string $form_type The form type
     * @return array Array of content using the form
     */
    private function searchCustomPostTypes(int $form_id, string $form_type): array
    {
        $usage = [];
        
        // Get all registered post types
        $post_types = get_post_types(['public' => true], 'names');
        
        foreach ($post_types as $post_type) {
            $posts = get_posts([
                'post_type' => $post_type,
                'post_status' => 'publish',
                'posts_per_page' => -1
            ]);
            
            foreach ($posts as $post) {
                $content = $post->post_content;
                $found = false;
                
                // Check for form ID patterns
                $patterns = [
                    'id="' . $form_id . '"',
                    'id=\'' . $form_id . '\'',
                    'id=' . $form_id,
                    'form_id="' . $form_id . '"',
                    'form_id=\'' . $form_id . '\'',
                    'form_id=' . $form_id
                ];
                
                foreach ($patterns as $pattern) {
                    if (strpos($content, $pattern) !== false) {
                        $found = true;
                        break;
                    }
                }
                
                if ($found) {
                    $usage[] = [
                        'id' => $post->ID,
                        'title' => $post->post_title,
                        'type' => ucfirst($post_type),
                        'edit_url' => get_edit_post_link($post->ID),
                        'view_url' => get_permalink($post->ID),
                        'url' => get_permalink($post->ID)
                    ];
                }
            }
        }
        
        return $usage;
    }

    /**
     * Search for forms in theme files and other areas
     * 
     * @since 2.0.0
     * @param int $form_id The form ID
     * @param string $form_type The form type
     * @return array Array of theme files using the form
     */
    private function searchThemeFilesForForms(int $form_id, string $form_type): array
    {
        $usage = [];
        $theme_dir = get_template_directory();
        $files_to_check = [
            'header.php',
            'footer.php',
            'index.php',
            'single.php',
            'page.php',
            'functions.php',
            'sidebar.php'
        ];
        
        foreach ($files_to_check as $file) {
            $file_path = $theme_dir . '/' . $file;
            if (file_exists($file_path)) {
                $content = file_get_contents($file_path);
                if ($content !== false) {
                    $found = false;
                    $match_type = '';
                    
                    // Check for Gravity Forms patterns
                    if ($form_type === 'gf') {
                        $gf_patterns = [
                            'gform_' . $form_id,
                            'gform_wrapper_' . $form_id,
                            'data-formid="' . $form_id . '"',
                            'data-formid=\'' . $form_id . '\'',
                            'data-formid=\'' . $form_id . '\' novalidate',
                            'data-formid="' . $form_id . '" novalidate',
                            'gformInitSpinner(' . $form_id,
                            'gravityform id="' . $form_id . '"',
                            'gravityform id=\'' . $form_id . '\''
                        ];
                        
                        foreach ($gf_patterns as $pattern) {
                            if (strpos($content, $pattern) !== false) {
                                $found = true;
                                $match_type = 'theme_file';
                                break;
                            }
                        }
                    }
                    
                    // Check for Contact Form 7 patterns
                    if ($form_type === 'cf7') {
                        $cf7_patterns = [
                            'contact-form-7 id="' . $form_id . '"',
                            'contact-form-7 id=\'' . $form_id . '\'',
                            'wpcf7-form-' . $form_id,
                            'contact-form-7'
                        ];
                        
                        foreach ($cf7_patterns as $pattern) {
                            if (strpos($content, $pattern) !== false) {
                                $found = true;
                                $match_type = 'theme_file';
                                break;
                            }
                        }
                    }
                    
                    if ($found) {
                        $usage[] = [
                            'file' => $file,
                            'path' => $file_path,
                            'match_type' => $match_type,
                            'type' => 'Theme File'
                        ];
                    }
                }
            }
        }
        
        return $usage;
    }

    /**
     * Log internal message
     * 
     * @since 2.0.0
     * @param string $message The message to log
     * @param string $type The log type
     * @return void
     */
    private function logInternal(string $message, string $type = 'debug'): void
    {
        if ($this->loggingSystem->isDebugEnabled()) {
            $this->loggingSystem->logActivity($message, $type);
        }
    }
} 