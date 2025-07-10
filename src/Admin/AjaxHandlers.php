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

    public function __construct(
        $loggingSystem = null,
        $renderer = null,
        $formAjax = null,
        $logAjax = null,
        $apiAjax = null,
        $systemAjax = null
    ) {
        $this->loggingSystem = $loggingSystem ?: new LoggingSystem();
        $this->renderer = $renderer ?: new SettingsRenderer();
        $this->formAjax = $formAjax ?: new FormAjax();
        $this->logAjax = $logAjax ?: new LogAjax($this->loggingSystem, $this->renderer);
        $this->apiAjax = $apiAjax ?: new ApiAjax();
        $this->systemAjax = $systemAjax ?: new SystemAjax($this->loggingSystem, $this->renderer);
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