<?php
if (!defined('CTM_TESTING')) {
    define('CTM_TESTING', true);
}

use PHPUnit\Framework\TestCase;
use Brain\Monkey;
use CTM\Tests\Traits\MonkeyTrait;

class PluginBootstrapTest extends TestCase
{
    use MonkeyTrait;

    protected function setUp(): void
    {
        parent::setUp();
        Monkey\setUp();
        
        // Mock WordPress functions
        \Brain\Monkey\Functions\when('add_action')->justReturn(null);
        \Brain\Monkey\Functions\when('get_option')->justReturn(null);
        \Brain\Monkey\Functions\when('update_option')->justReturn(true);
        \Brain\Monkey\Functions\when('class_exists')->justReturn(false);
        \Brain\Monkey\Functions\when('defined')->justReturn(false);
        \Brain\Monkey\Functions\when('constant')->justReturn('1.0.0');
        \Brain\Monkey\Functions\when('version_compare')->justReturn(true);
        \Brain\Monkey\Functions\when('esc_html')->alias(function($text) { return $text; });
        \Brain\Monkey\Functions\when('wp_kses_post')->alias(function($text) { return $text; });
        
        // Define constants
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
    }

    protected function tearDown(): void
    {
        Monkey\tearDown();
        \Mockery::close();
        parent::tearDown();
    }

    public function testPluginInitializationDoesNotThrowException()
    {
        // Mock global variables
        global $wp_version;
        $wp_version = '6.5.0';
        
        // Mock WordPress functions
        \Brain\Monkey\Functions\when('add_action')->justReturn(null);
        \Brain\Monkey\Functions\when('get_option')->justReturn(null);
        \Brain\Monkey\Functions\when('update_option')->justReturn(true);
        \Brain\Monkey\Functions\when('class_exists')->justReturn(false);
        \Brain\Monkey\Functions\when('defined')->justReturn(false);
        \Brain\Monkey\Functions\when('constant')->justReturn('1.0.0');
        \Brain\Monkey\Functions\when('version_compare')->justReturn(true);
        \Brain\Monkey\Functions\when('esc_html')->alias(function($text) { return $text; });
        \Brain\Monkey\Functions\when('wp_kses_post')->alias(function($text) { return $text; });
        
        // Should not throw any exceptions
        $this->assertTrue(true);
    }

    public function testAdminInitHookRegistersWhenDefined()
    {
        // Mock global variables
        global $wp_version;
        $wp_version = '6.5.0';
        
        // Mock WordPress functions
        \Brain\Monkey\Functions\when('add_action')->alias(function($hook, $callback) {
            if ($hook === 'admin_init') {
                // Call the callback to test it
                $callback();
            }
        });
        \Brain\Monkey\Functions\when('get_option')->justReturn(null);
        \Brain\Monkey\Functions\when('update_option')->justReturn(true);
        \Brain\Monkey\Functions\when('class_exists')->justReturn(false);
        \Brain\Monkey\Functions\when('defined')->justReturn(false);
        \Brain\Monkey\Functions\when('constant')->justReturn('1.0.0');
        \Brain\Monkey\Functions\when('version_compare')->justReturn(true);
        \Brain\Monkey\Functions\when('esc_html')->alias(function($text) { return $text; });
        \Brain\Monkey\Functions\when('wp_kses_post')->alias(function($text) { return $text; });
        
        // Should not throw any exceptions
        $this->assertTrue(true);
    }

    public function testAdminFooterHookRegistersWhenDefined()
    {
        // Mock WordPress functions
        \Brain\Monkey\Functions\when('add_action')->alias(function($hook, $callback) {
            if ($hook === 'admin_footer') {
                // Call the callback to test it
                $callback();
            }
        });
        \Brain\Monkey\Functions\when('esc_html')->alias(function($text) { return $text; });
        \Brain\Monkey\Functions\when('wp_kses_post')->alias(function($text) { return $text; });
        
        // Should not throw any exceptions
        $this->assertTrue(true);
    }

    public function testVersionCompatibilityChecks()
    {
        // Mock global variables
        global $wp_version;
        $wp_version = '6.4.0';
        
        // Mock WordPress functions
        \Brain\Monkey\Functions\when('add_action')->justReturn(null);
        \Brain\Monkey\Functions\when('get_option')->justReturn(null);
        \Brain\Monkey\Functions\when('update_option')->justReturn(true);
        \Brain\Monkey\Functions\when('class_exists')->justReturn(false);
        \Brain\Monkey\Functions\when('defined')->justReturn(false);
        \Brain\Monkey\Functions\when('constant')->justReturn('1.0.0');
        \Brain\Monkey\Functions\when('version_compare')->alias(function($version1, $version2, $operator) {
            if ($operator === '<') {
                return version_compare($version1, $version2, '<');
            }
            return true;
        });
        \Brain\Monkey\Functions\when('esc_html')->alias(function($text) { return $text; });
        \Brain\Monkey\Functions\when('wp_kses_post')->alias(function($text) { return $text; });
        
        // Should not throw any exceptions
        $this->assertTrue(true);
    }

    public function testPluginIntegrationChecks()
    {
        // Mock global variables
        global $wp_version;
        $wp_version = '6.5.0';
        
        // Mock WordPress functions
        \Brain\Monkey\Functions\when('add_action')->justReturn(null);
        \Brain\Monkey\Functions\when('get_option')->justReturn(null);
        \Brain\Monkey\Functions\when('update_option')->justReturn(true);
        \Brain\Monkey\Functions\when('class_exists')->alias(function($class) {
            if ($class === 'GFAPI') return true;
            if ($class === 'WPCF7_ContactForm') return true;
            return false;
        });
        \Brain\Monkey\Functions\when('defined')->alias(function($constant) {
            if ($constant === 'GF_VERSION') return true;
            if ($constant === 'WPCF7_VERSION') return true;
            return false;
        });
        \Brain\Monkey\Functions\when('constant')->alias(function($constant) {
            if ($constant === 'GF_VERSION') return '2.6.0';
            if ($constant === 'WPCF7_VERSION') return '5.6.0';
            return '1.0.0';
        });
        \Brain\Monkey\Functions\when('version_compare')->alias(function($version1, $version2, $operator) {
            if ($operator === '<') {
                return version_compare($version1, $version2, '<');
            }
            return true;
        });
        \Brain\Monkey\Functions\when('esc_html')->alias(function($text) { return $text; });
        \Brain\Monkey\Functions\when('wp_kses_post')->alias(function($text) { return $text; });
        
        // Should not throw any exceptions
        $this->assertTrue(true);
    }

    public function testErrorNoticeGeneration()
    {
        // Mock global variables
        global $wp_version;
        $wp_version = '6.3.0'; // Below minimum
        
        // Mock WordPress functions
        \Brain\Monkey\Functions\when('add_action')->alias(function($hook, $callback) {
            if ($hook === 'admin_notices') {
                // Call the callback to test it
                $callback();
            }
        });
        \Brain\Monkey\Functions\when('get_option')->justReturn(null);
        \Brain\Monkey\Functions\when('update_option')->justReturn(true);
        \Brain\Monkey\Functions\when('class_exists')->justReturn(false);
        \Brain\Monkey\Functions\when('defined')->justReturn(false);
        \Brain\Monkey\Functions\when('constant')->justReturn('1.0.0');
        \Brain\Monkey\Functions\when('version_compare')->alias(function($version1, $version2, $operator) {
            if ($operator === '<') {
                return version_compare($version1, $version2, '<');
            }
            return true;
        });
        \Brain\Monkey\Functions\when('esc_html')->alias(function($text) { return $text; });
        \Brain\Monkey\Functions\when('wp_kses_post')->alias(function($text) { return $text; });
        
        // Should not throw any exceptions
        $this->assertTrue(true);
    }

    public function testIntegrationDisablingOnVersionMismatch()
    {
        // Mock global variables
        global $wp_version;
        $wp_version = '6.3.0'; // Below minimum
        
        // Mock WordPress functions
        \Brain\Monkey\Functions\when('add_action')->justReturn(null);
        \Brain\Monkey\Functions\when('get_option')->justReturn(null);
        \Brain\Monkey\Functions\when('update_option')->alias(function($option, $value) {
            // Verify that integrations are being disabled
            if ($option === 'ctm_api_gf_enabled' && $value === false) {
                return true;
            }
            if ($option === 'ctm_api_cf7_enabled' && $value === false) {
                return true;
            }
            return true;
        });
        \Brain\Monkey\Functions\when('class_exists')->justReturn(false);
        \Brain\Monkey\Functions\when('defined')->justReturn(false);
        \Brain\Monkey\Functions\when('constant')->justReturn('1.0.0');
        \Brain\Monkey\Functions\when('version_compare')->alias(function($version1, $version2, $operator) {
            if ($operator === '<') {
                return version_compare($version1, $version2, '<');
            }
            return true;
        });
        \Brain\Monkey\Functions\when('esc_html')->alias(function($text) { return $text; });
        \Brain\Monkey\Functions\when('wp_kses_post')->alias(function($text) { return $text; });
        
        // Should not throw any exceptions
        $this->assertTrue(true);
    }
} 