<?php

namespace CTM\Tests\Traits;

use Brain\Monkey;

trait MonkeyTrait
{
    public function initalMonkey(): void
    {
        // Helper to avoid duplicate mocks
        $set = [];
        $mock = function($fn, $cb) use (&$set) {
            if (!isset($set[$fn])) {
                $set[$fn] = true;
                $cb();
            }
        };
        // WordPress core and plugin functions
        $mock('active_plugins', fn() => \Brain\Monkey\Functions\when('active_plugins')->justReturn(['call-tracking-metrics/call-tracking-metrics.php']));
        $mock('plugin_dir_path', fn() => \Brain\Monkey\Functions\when('plugin_dir_path')->alias(function($file) { return __DIR__ . '/../../'; }));
        $mock('plugin_dir_url', fn() => \Brain\Monkey\Functions\when('plugin_dir_url')->alias(function($file) { return '/'; }));
        $mock('home_url', fn() => \Brain\Monkey\Functions\when('home_url')->justReturn('http://example.com'));
        $mock('admin_url', fn() => \Brain\Monkey\Functions\when('admin_url')->justReturn('http://example.com/wp-admin/'));
        $mock('get_option', fn() => \Brain\Monkey\Functions\when('get_option')->alias(function($key, $default = '') {
            if ($key === 'active_plugins') {
                return [];
            }
            if ($key === 'ctm_api_cf7_logs' || $key === 'ctm_api_gf_logs') {
                return [];
            }
            
            return $default;
        }));
        $mock('checked', fn() => \Brain\Monkey\Functions\when('checked')->alias(function() { return ''; }));
        $mock('wp_kses_post', fn() => \Brain\Monkey\Functions\when('wp_kses_post')->alias(function($v) { return $v; }));
        $mock('wp_redirect', fn() => \Brain\Monkey\Functions\when('wp_redirect')->justReturn(null));
        $mock('wp_get_referer', fn() => \Brain\Monkey\Functions\when('wp_get_referer')->justReturn('http://example.com'));
        $mock('esc_attr', fn() => \Brain\Monkey\Functions\when('esc_attr')->alias(function($v) { return $v; }));
        $mock('esc_textarea', fn() => \Brain\Monkey\Functions\when('esc_textarea')->alias(function($v) { return $v; }));
        $mock('sanitize_text_field', fn() => \Brain\Monkey\Functions\when('sanitize_text_field')->alias(function($v) { return $v; }));
        $mock('sanitize_email', fn() => \Brain\Monkey\Functions\when('sanitize_email')->alias(function($v) { return $v; }));
        $mock('add_options_page', fn() => \Brain\Monkey\Functions\when('add_options_page')->justReturn(null));
        $mock('register_setting', fn() => \Brain\Monkey\Functions\when('register_setting')->justReturn(null));
        $mock('do_settings_sections', fn() => \Brain\Monkey\Functions\when('do_settings_sections')->alias(function() { echo '<!--do_settings_sections-->'; }));
        $mock('settings_fields', fn() => \Brain\Monkey\Functions\when('settings_fields')->alias(function() { echo '<!--settings_fields-->'; }));
        $mock('is_multisite', fn() => \Brain\Monkey\Functions\when('is_multisite')->justReturn(false));
        // Plugin class stubs
        if (!class_exists('WPCF7_ContactForm')) {
            eval('class WPCF7_ContactForm {
                public static function find() { return []; }
                public static function get_instance($id = null) { return new self(); }
                public function id() { return 2; }
                public function title() { return "Test Form"; }
            }');
        }
        if (!class_exists('GFAPI')) {
            eval('class GFAPI {
                public static function get_forms() { return []; }
                public static function get_form($id = null) { return []; }
            }');
        }
        \Brain\Monkey\Functions\when('get_locale')->justReturn('en_US');

        $mock('register_setting', fn() => \Brain\Monkey\Functions\when('register_setting')->justReturn(null));
        $mock('plugin_dir_path', fn() => \Brain\Monkey\Functions\when('plugin_dir_path')->justReturn('/tmp/'));
        $mock('count_users', fn() => \Brain\Monkey\Functions\when('count_users')->justReturn(['total_users' => 1]));
        // $mock('add_action', fn() => \Brain\Monkey\Functions\when('add_action')->justReturn(null));
        $mock('home_url', fn() => \Brain\Monkey\Functions\when('home_url')->justReturn('https://example.com'));
        $mock('check_ajax_referer', fn() => \Brain\Monkey\Functions\when('check_ajax_referer')->justReturn(true));
        $mock('sanitize_text_field', fn() => \Brain\Monkey\Functions\when('sanitize_text_field')->alias(fn($v) => $v));
        // $mock('wp_send_json_success', fn() => \Brain\Monkey\Functions\when('wp_send_json_success')->justReturn(null));
        // $mock('wp_send_json_error', fn() => \Brain\Monkey\Functions\when('wp_send_json_error')->justReturn(null));
        $mock('update_option', fn() => \Brain\Monkey\Functions\when('update_option')->justReturn(true));
        $mock('sanitize_email', fn() => \Brain\Monkey\Functions\when('sanitize_email')->alias(fn($v) => $v));
        $mock('is_email', fn() => \Brain\Monkey\Functions\when('is_email')->justReturn(true));
        $mock('esc_html', fn() => \Brain\Monkey\Functions\when('esc_html')->alias(fn($v) => $v));
        $mock('get_bloginfo', fn() => \Brain\Monkey\Functions\when('get_bloginfo')->justReturn('5.8'));
        $mock('wp_mail', fn() => \Brain\Monkey\Functions\when('wp_mail')->justReturn(true));
        $mock('tempnam', fn() => \Brain\Monkey\Functions\when('tempnam')->justReturn('/tmp/ctm_log.csv'));
        $mock('sys_get_temp_dir', fn() => \Brain\Monkey\Functions\when('sys_get_temp_dir')->justReturn('/tmp'));
        $mock('fopen', fn() => \Brain\Monkey\Functions\when('fopen')->justReturn(true));
        $mock('fputcsv', fn() => \Brain\Monkey\Functions\when('fputcsv')->justReturn(true));
        $mock('fclose', fn() => \Brain\Monkey\Functions\when('fclose')->justReturn(true));
        $mock('unlink', fn() => \Brain\Monkey\Functions\when('unlink')->justReturn(true));
        $mock('current_time', fn() => \Brain\Monkey\Functions\when('current_time')->justReturn('2024-01-01 00:00:00'));
        $mock('get_current_user_id', fn() => \Brain\Monkey\Functions\when('get_current_user_id')->justReturn(1));
        $mock('wp_generate_uuid4', fn() => \Brain\Monkey\Functions\when('wp_generate_uuid4')->justReturn('uuid-1234'));
        $mock('wp_cache_get_stats', fn() => \Brain\Monkey\Functions\when('wp_cache_get_stats')->justReturn(['hits' => 100, 'misses' => 10]));
        $mock('size_format', fn() => \Brain\Monkey\Functions\when('size_format')->alias(fn($size) => $size));
        $mock('get_num_queries', fn() => \Brain\Monkey\Functions\when('get_num_queries')->justReturn(100));
        $mock('wp_convert_hr_to_bytes', fn() => \Brain\Monkey\Functions\when('wp_convert_hr_to_bytes')->alias(fn($size) => $size));
        $mock('wp_get_theme', fn() => \Brain\Monkey\Functions\when('wp_get_theme')->justReturn(new class {
            public function get($key) {
                if ($key === 'Name') return 'Test Theme';
                if ($key === 'Version') return '1.0.0';
                return null;
            }
            public function parent() { return null; }
            public function get_stylesheet() { return 'test-theme'; }
        }));
        $mock('wp_upload_dir', fn() => \Brain\Monkey\Functions\when('wp_upload_dir')->justReturn([
            'baseurl' => 'https://example.com/wp-content/uploads',
            'basedir' => '/tmp'
        ]));
        $mock('wp_remote_retrieve_response_code', fn() => \Brain\Monkey\Functions\when('wp_remote_retrieve_response_code')->justReturn(200));
        $mock('wp_remote_request', fn() => \Brain\Monkey\Functions\when('wp_remote_request')->alias(function() {
            return [
                'response' => ['code' => 200],
                'body' => json_encode([
                    'account' => ['id' => 1],
                    'forms' => [],
                    'numbers' => [],
                    'calls' => []
                ])
            ];
        }));
        $mock('wp_remote_retrieve_body', fn() => \Brain\Monkey\Functions\when('wp_remote_retrieve_body')->justReturn('{}'));
        $mock('wp_remote_head', fn() => \Brain\Monkey\Functions\when('wp_remote_head')->justReturn(true));
        $mock('_get_cron_array', fn() => \Brain\Monkey\Functions\when('_get_cron_array')->justReturn([]));
        $mock('is_admin', fn() => \Brain\Monkey\Functions\when('is_admin')->justReturn(true));
        $mock('is_user_logged_in', fn() => \Brain\Monkey\Functions\when('is_user_logged_in')->justReturn(true));
        $mock('current_user_can', fn() => \Brain\Monkey\Functions\when('current_user_can')->justReturn(true));
        $mock('get_template_directory', fn() => \Brain\Monkey\Functions\when('get_template_directory')->justReturn('/tmp'));
        $mock('get_stylesheet_directory', fn() => \Brain\Monkey\Functions\when('get_stylesheet_directory')->justReturn('/tmp'));
        $mock('get_stylesheet_directory_uri', fn() => \Brain\Monkey\Functions\when('get_stylesheet_directory_uri')->justReturn('https://example.com/wp-content/themes/test-theme'));
        $mock('get_template_directory_uri', fn() => \Brain\Monkey\Functions\when('get_template_directory_uri')->justReturn('https://example.com/wp-content/themes/test-theme'));
        $mock('is_wp_error', fn() => \Brain\Monkey\Functions\when('is_wp_error')->justReturn(false));
        $mock('wp_remote_get', fn() => \Brain\Monkey\Functions\when('wp_remote_get')->justReturn(true));
        $mock('wp_script_is', fn() => \Brain\Monkey\Functions\when('wp_script_is')->justReturn(false));
        $mock('wp_enqueue_script', fn() => \Brain\Monkey\Functions\when('wp_enqueue_script')->justReturn(null));
        $mock('wp_enqueue_style', fn() => \Brain\Monkey\Functions\when('wp_enqueue_style')->justReturn(null));
        $mock('wp_enqueue_media', fn() => \Brain\Monkey\Functions\when('wp_enqueue_media')->justReturn(null));
        $mock('is_ssl', fn() => \Brain\Monkey\Functions\when('is_ssl')->justReturn(false));
        $mock('wp_nonce_field', fn() => \Brain\Monkey\Functions\when('wp_nonce_field')->justReturn(null));
        $mock('wp_nonce_url', fn() => \Brain\Monkey\Functions\when('wp_nonce_url')->justReturn('https://example.com'));
        $mock('wp_nonce_tick', fn() => \Brain\Monkey\Functions\when('wp_nonce_tick')->justReturn(1));
        $mock('wp_nonce_tick', fn() => \Brain\Monkey\Functions\when('wp_nonce_tick')->justReturn(1));
        $mock('wp_get_upload_dir', fn() => \Brain\Monkey\Functions\when('wp_get_upload_dir')->justReturn([
            'baseurl' => 'https://example.com/wp-content/uploads',
            'basedir' => '/tmp'
        ]));
        $mock('wp_get_current_user', fn() => \Brain\Monkey\Functions\when('wp_get_current_user')->justReturn(new class {
            public function ID() { return 1; }
        }));

        // Define constants only if not already defined
        if (!defined('ABSPATH')) define('ABSPATH', '/tmp');
        if (!defined('WP_CONTENT_DIR')) define('WP_CONTENT_DIR', '/tmp');
        if (!defined('WP_DEBUG')) define('WP_DEBUG', true);
        if (!defined('WP_DEBUG_LOG')) define('WP_DEBUG_LOG', true);
        if (!defined('WP_DEBUG_DISPLAY')) define('WP_DEBUG_DISPLAY', true);
        if (!defined('WP_MEMORY_LIMIT')) define('WP_MEMORY_LIMIT', '128M');
        if (!defined('WP_MAX_MEMORY_LIMIT')) define('WP_MAX_MEMORY_LIMIT', '128M');
        if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
        if (!defined('DB_NAME')) define('DB_NAME', 'wordpress');
        if (!defined('DB_CHARSET')) define('DB_CHARSET', 'utf8');
        if (!defined('DB_COLLATE')) define('DB_COLLATE', 'utf8_general_ci');
        if (!defined('DB_PREFIX')) define('DB_PREFIX', 'wp_');
        if (!defined('DB_VERSION')) define('DB_VERSION', '5.8');

        $GLOBALS['wpdb'] = new class {
            public function get_var($query) {
                if (strpos($query, 'SHOW TABLES LIKE') !== false) {
                    return 'wp_options';
                }
                return null;
            }
            public function prepare($query, $args) {
                return $query;
            }
            public function db_version() { return '5.8'; }
            public $prefix = 'wp_';
            public $db_charset = 'utf8';
            public $db_collate = 'utf8_general_ci';
            public $db_host = 'localhost';
            public $db_name = 'wordpress';
        };
    }
}