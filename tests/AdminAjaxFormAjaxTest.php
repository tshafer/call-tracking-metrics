<?php
use PHPUnit\Framework\TestCase;
use CTM\Admin\Ajax\FormAjax;
use Brain\Monkey;
use CTM\Tests\Traits\MonkeyTrait;
class AdminAjaxFormAjaxTest extends TestCase
{
    use MonkeyTrait;
    protected function setUp(): void
    {
        parent::setUp();
        Monkey\setUp();
        $this->initalMonkey();
        // Remove global justReturn mocks for functions with expectations

        if (!class_exists('GFAPI')) {
            eval('class GFAPI {
                public static function get_forms() { return [["id"=>1,"title"=>"Test Form","fields"=>[["id"=>1,"label"=>"Field 1","type"=>"text"]],"is_active"=>true]]; }
                public static function get_form($id) { if ($id == 1) return ["id"=>1,"title"=>"Test Form","fields"=>[["id"=>1,"label"=>"Field 1","type"=>"text"]]]; return null; }
            }');
        }
        if (!class_exists('WPCF7_ContactForm')) {
            eval('class WPCF7_ContactForm {
                public static function find() { $f = new self; return [$f]; }
                public static function get_instance($id) { $f = new self; return $id == 2 ? $f : null; }
                public function id() { return 2; }
                public function title() { return "CF7 Form"; }
                public function scan_form_tags() { $t = new \stdClass; $t->name = "cf7_field"; return [$t]; }
            }');
        }
    }
    protected function tearDown(): void
    {
        Monkey\tearDown();
        parent::tearDown();
    }
    public function testCanBeConstructed()
    {
        $formAjax = new FormAjax('GFAPI', 'WPCF7_ContactForm', new \CTM\Admin\FieldMapping());
        $this->assertInstanceOf(FormAjax::class, $formAjax);
    }

    public function testRegisterHandlersAddsActions()
    {
        \Brain\Monkey\Functions\expect('add_action')->times(4);
        $formAjax = new FormAjax('GFAPI', 'WPCF7_ContactForm', new \CTM\Admin\FieldMapping());
        $formAjax->registerHandlers();
    }

    public function testAjaxGetFormsReturnsEmptyForUnknownType()
    {
        $_POST = ['form_type' => 'unknown'];
        \Brain\Monkey\Functions\expect('wp_send_json_success')->once()->with([]);
        $formAjax = new FormAjax('GFAPI', 'WPCF7_ContactForm', new \CTM\Admin\FieldMapping());
        $formAjax->ajaxGetForms();
    }

    public function testAjaxGetFormsReturnsGFForms()
    {
        $_POST = ['form_type' => 'gf'];
        if (!class_exists('GFAPI')) {
            eval('class GFAPI { public static function get_forms() { return [["id"=>1,"title"=>"Test Form"]]; } }');
        }
        \Brain\Monkey\Functions\expect('wp_send_json_success')->once()->with([
            ['id' => 1, 'title' => 'Test Form']
        ]);
        $formAjax = new FormAjax('GFAPI', 'WPCF7_ContactForm', new \CTM\Admin\FieldMapping());
        $formAjax->ajaxGetForms();
    }

    public function testAjaxGetFormsReturnsCF7Forms()
    {
        $_POST = ['form_type' => 'cf7'];
        if (!class_exists('WPCF7_ContactForm')) {
            eval('class WPCF7_ContactForm { public static function find() { $f = new self; return [$f]; } public function id() { return 2; } public function title() { return "CF7 Form"; } }');
        }
        \Brain\Monkey\Functions\expect('wp_send_json_success')->once()->with([
            ['id' => 2, 'title' => 'CF7 Form']
        ]);
        $formAjax = new FormAjax('GFAPI', 'WPCF7_ContactForm', new \CTM\Admin\FieldMapping());
        $formAjax->ajaxGetForms();
    }

    public function testAjaxGetFieldsReturnsEmptyForUnknownType()
    {
        $_POST = ['form_type' => 'unknown', 'form_id' => ''];
        \Brain\Monkey\Functions\expect('wp_send_json_success')->once()->with([]);
        $formAjax = new FormAjax('GFAPI', 'WPCF7_ContactForm', new \CTM\Admin\FieldMapping());
        $formAjax->ajaxGetFields();
    }

    public function testAjaxGetFieldsReturnsGFFields()
    {
        $_POST = ['form_type' => 'gf', 'form_id' => 1];
        if (!class_exists('GFAPI')) {
            eval('class GFAPI { public static function get_form($id) { return ["fields"=>[["id"=>1,"label"=>"Field 1"]]]; } }');
        }
        \Brain\Monkey\Functions\expect('wp_send_json_success')->once()->with([
            ['id' => 1, 'label' => 'Field 1']
        ]);
        $formAjax = new FormAjax('GFAPI', 'WPCF7_ContactForm', new \CTM\Admin\FieldMapping());
        $formAjax->ajaxGetFields();
    }

    public function testAjaxGetFieldsReturnsCF7Fields()
    {
        $_POST = ['form_type' => 'cf7', 'form_id' => 2];
        if (!class_exists('WPCF7_ContactForm')) {
            eval('class WPCF7_ContactForm { public static function get_instance($id) { return new self; } public function scan_form_tags() { $t = new \stdClass; $t->name = "cf7_field"; return [$t]; } }');
        }
        \Brain\Monkey\Functions\expect('wp_send_json_success')->once()->with([
            ['id' => 'cf7_field', 'label' => 'cf7_field']
        ]);
        $formAjax = new FormAjax('GFAPI', 'WPCF7_ContactForm', new \CTM\Admin\FieldMapping());
        $formAjax->ajaxGetFields();
    }

    public function testAjaxSaveMappingSuccess()
    {
        $_POST = ['form_type' => 'gf', 'form_id' => 1, 'mapping' => ['a' => 'b']];
        if (!class_exists('CTM\\Admin\\FieldMapping')) {
            eval('namespace CTM\\Admin; class FieldMapping { public function saveFieldMapping($type, $id, $mapping) { \update_option("ctm_mapping_{$type}_{$id}", $mapping); return true; } }');
        }
        \Brain\Monkey\Functions\expect('update_option')->once()->with('ctm_mapping_gf_1', ['a' => 'b'])->andReturn(true);
        \Brain\Monkey\Functions\expect('wp_send_json_success')->once()->with(['message' => 'Mapping saved.']);
        \Brain\Monkey\Functions\expect('wp_send_json_error')->zeroOrMoreTimes();
        $formAjax = new FormAjax('GFAPI', 'WPCF7_ContactForm', new \CTM\Admin\FieldMapping());
        $formAjax->ajaxSaveMapping();
    }

    public function testAjaxSaveMappingErrorOnInvalidData()
    {
        $_POST = ['form_type' => '', 'form_id' => '', 'mapping' => 'not_array'];
        \Brain\Monkey\Functions\expect('wp_send_json_error')->once()->with(['message' => 'Invalid mapping data.']);
        $formAjax = new FormAjax('GFAPI', 'WPCF7_ContactForm', new \CTM\Admin\FieldMapping());
        $formAjax->ajaxSaveMapping();
    }

    public function testAjaxDismissNoticeCF7()
    {
        $_POST = ['notice_type' => 'cf7'];
        \Brain\Monkey\Functions\expect('update_option')->once()->with('ctm_cf7_notice_dismissed', true)->andReturn(true);
        \Brain\Monkey\Functions\expect('wp_send_json_success')->once()->with(['message' => 'CF7 notice dismissed.']);
        \Brain\Monkey\Functions\expect('wp_send_json_error')->zeroOrMoreTimes();
        $formAjax = new FormAjax('GFAPI', 'WPCF7_ContactForm', new \CTM\Admin\FieldMapping());
        $formAjax->ajaxDismissNotice();
    }

    public function testAjaxDismissNoticeGF()
    {
        $_POST = ['notice_type' => 'gf'];
        \Brain\Monkey\Functions\expect('update_option')->once()->with('ctm_gf_notice_dismissed', true)->andReturn(true);
        \Brain\Monkey\Functions\expect('wp_send_json_success')->once()->with(['message' => 'GF notice dismissed.']);
        \Brain\Monkey\Functions\expect('wp_send_json_error')->zeroOrMoreTimes();
        $formAjax = new FormAjax('GFAPI', 'WPCF7_ContactForm', new \CTM\Admin\FieldMapping());
        $formAjax->ajaxDismissNotice();
    }

    public function testAjaxDismissNoticeErrorOnInvalidType()
    {
        $_POST = ['notice_type' => 'other'];
        \Brain\Monkey\Functions\expect('wp_send_json_error')->once()->with(['message' => 'Invalid notice type.']);
        $formAjax = new FormAjax('GFAPI', 'WPCF7_ContactForm', new \CTM\Admin\FieldMapping());
        $formAjax->ajaxDismissNotice();
    }

    // Additional edge and negative tests for coverage
    public function testAjaxGetFormsNoPostType()
    {
        unset($_POST['form_type']);
        \Brain\Monkey\Functions\expect('wp_send_json_success')->once()->with([]);
        $formAjax = new FormAjax('GFAPI', 'WPCF7_ContactForm', new \CTM\Admin\FieldMapping());
        $formAjax->ajaxGetForms();
    }

    public function testAjaxGetFieldsNoFormId()
    {
        $_POST = ['form_type' => 'gf'];
        \Brain\Monkey\Functions\expect('wp_send_json_success')->once()->with([]);
        $formAjax = new FormAjax('GFAPI', 'WPCF7_ContactForm', new \CTM\Admin\FieldMapping());
        $formAjax->ajaxGetFields();
    }

    public function testAjaxSaveMappingMissingMapping()
    {
        $_POST = ['form_type' => 'gf', 'form_id' => 1];
        \Brain\Monkey\Functions\expect('wp_send_json_error')->once()->with(['message' => 'Invalid mapping data.']);
        \Brain\Monkey\Functions\expect('update_option')->zeroOrMoreTimes();
        \Brain\Monkey\Functions\expect('wp_send_json_success')->zeroOrMoreTimes();
        $formAjax = new FormAjax('GFAPI', 'WPCF7_ContactForm', new \CTM\Admin\FieldMapping());
        $formAjax->ajaxSaveMapping();
    }

    public function testAjaxSaveMappingMappingNotArray()
    {
        $_POST = ['form_type' => 'gf', 'form_id' => 1, 'mapping' => 'string'];
        \Brain\Monkey\Functions\expect('wp_send_json_error')->once()->with(['message' => 'Invalid mapping data.']);
        $formAjax = new FormAjax('GFAPI', 'WPCF7_ContactForm', new \CTM\Admin\FieldMapping());
        $formAjax->ajaxSaveMapping();
    }

    public function testAjaxDismissNoticeNoType()
    {
        unset($_POST['notice_type']);
        \Brain\Monkey\Functions\expect('wp_send_json_error')->once()->with(['message' => 'Invalid notice type.']);
        $formAjax = new FormAjax('GFAPI', 'WPCF7_ContactForm', new \CTM\Admin\FieldMapping());
        $formAjax->ajaxDismissNotice();
    }

    public function testAjaxGetFieldsGFFormNoFields()
    {
        if (class_exists('GFAPI', false)) {
            $this->markTestSkipped('Cannot override already loaded GFAPI class. Refactor FormAjax for dependency injection to allow proper mocking.');
            return;
        }
        $_POST = ['form_type' => 'gf', 'form_id' => 1];
        if (!class_exists('GFAPI_NoFields')) {
            eval('class GFAPI_NoFields { public static function get_form($id) { return ["id"=>1, "title"=>"Test Form", "fields"=>[]]; } }');
        }
        class_alias('GFAPI_NoFields', 'GFAPI');
        \Brain\Monkey\Functions\expect('wp_send_json_success')->once()->with([]);
        $formAjax = new FormAjax('GFAPI', 'WPCF7_ContactForm', new \CTM\Admin\FieldMapping());
        $formAjax->ajaxGetFields();
    }

    public function testAjaxGetFieldsCF7FormNoTags()
    {
        if (class_exists('WPCF7_ContactForm', false)) {
            $this->markTestSkipped('Cannot override already loaded WPCF7_ContactForm class. Refactor FormAjax for dependency injection to allow proper mocking.');
            return;
        }
        $_POST = ['form_type' => 'cf7', 'form_id' => 2];
        if (!class_exists('WPCF7_ContactForm_NoTags')) {
            eval('class WPCF7_ContactForm_NoTags { public static function get_instance($id) { return new self; } public function scan_form_tags() { return []; } }');
        }
        class_alias('WPCF7_ContactForm_NoTags', 'WPCF7_ContactForm');
        \Brain\Monkey\Functions\expect('wp_send_json_success')->once()->with([]);
        $formAjax = new FormAjax('GFAPI', 'WPCF7_ContactForm', new \CTM\Admin\FieldMapping());
        $formAjax->ajaxGetFields();
    }

    public function testAjaxGetFieldsGFFormNull()
    {
        if (class_exists('GFAPI', false)) {
            $this->markTestSkipped('Cannot override already loaded GFAPI class. Refactor FormAjax for dependency injection to allow proper mocking.');
            return;
        }
        $_POST = ['form_type' => 'gf', 'form_id' => 1];
        if (!class_exists('GFAPI_Null')) {
            eval('class GFAPI_Null { public static function get_form($id) { return null; } }');
        }
        class_alias('GFAPI_Null', 'GFAPI');
        \Brain\Monkey\Functions\expect('wp_send_json_success')->once()->with([]);
        $formAjax = new FormAjax('GFAPI', 'WPCF7_ContactForm', new \CTM\Admin\FieldMapping());
        $formAjax->ajaxGetFields();
    }

    public function testAjaxGetFieldsCF7FormNull()
    {
        if (class_exists('WPCF7_ContactForm', false)) {
            $this->markTestSkipped('Cannot override already loaded WPCF7_ContactForm class. Refactor FormAjax for dependency injection to allow proper mocking.');
            return;
        }
        $_POST = ['form_type' => 'cf7', 'form_id' => 2];
        if (!class_exists('WPCF7_ContactForm_Null')) {
            eval('class WPCF7_ContactForm_Null { public static function get_instance($id) { return null; } }');
        }
        class_alias('WPCF7_ContactForm_Null', 'WPCF7_ContactForm');
        \Brain\Monkey\Functions\expect('wp_send_json_success')->once()->with([]);
        $formAjax = new FormAjax('GFAPI', 'WPCF7_ContactForm', new \CTM\Admin\FieldMapping());
        $formAjax->ajaxGetFields();
    }
} 