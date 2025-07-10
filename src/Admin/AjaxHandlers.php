<?php
namespace CTM\Admin;

use CTM\Admin\Ajax\FormAjax;
use CTM\Admin\Ajax\LogAjax;
use CTM\Admin\Ajax\ApiAjax;
use CTM\Admin\Ajax\SystemAjax;

/**
 * Handles all AJAX requests for the CTM plugin
 */
class AjaxHandlers
{
    private LoggingSystem $loggingSystem;
    private SettingsRenderer $renderer;
    private FormAjax $formAjax;
    private LogAjax $logAjax;
    private ApiAjax $apiAjax;
    private SystemAjax $systemAjax;

    public function __construct()
    {
        $this->loggingSystem = new LoggingSystem();
        $this->renderer = new SettingsRenderer();
        $this->formAjax = new FormAjax();
        $this->logAjax = new LogAjax($this->loggingSystem, $this->renderer);
        $this->apiAjax = new ApiAjax();
        $this->systemAjax = new SystemAjax($this->loggingSystem, $this->renderer);
    }

    /**
     * Register all AJAX handlers
     */
    public function registerHandlers(): void
    {
        $this->formAjax->registerHandlers();
        $this->logAjax->registerHandlers();
        $this->apiAjax->registerHandlers();
        $this->systemAjax->registerHandlers();
    }
} 