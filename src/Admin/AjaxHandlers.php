<?php
namespace CTM\Admin;

use CTM\Admin\Ajax\FormAjax;
use CTM\Admin\Ajax\LogAjax;
use CTM\Admin\Ajax\ApiAjax;
use CTM\Admin\Ajax\SystemAjax;
use CTM\Admin\Ajax\SystemSecurityAjax;
use CTM\Admin\Ajax\SystemPerformanceAjax;
use CTM\Admin\Ajax\FormImportAjax;
use CTM\Service\FormImportService;
use CTM\Service\ApiService;
use CTM\Service\CF7Service;
use CTM\Service\GFService;

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
    private SystemSecurityAjax $systemSecurityAjax;
    private SystemPerformanceAjax $systemPerformanceAjax;
    private FormImportAjax $formImportAjax;

    public function __construct(
        $loggingSystem = null,
        $renderer = null,
        $formAjax = null,
        $logAjax = null,
        $apiAjax = null,
        $systemAjax = null,
        $systemSecurityAjax = null,
        $systemPerformanceAjax = null,
        $formImportAjax = null
    ) {
        $this->loggingSystem = $loggingSystem ?: new LoggingSystem();
        $this->renderer = $renderer ?: new SettingsRenderer();
        $this->formAjax = $formAjax ?: new FormAjax();
        $this->logAjax = $logAjax ?: new LogAjax($this->loggingSystem, $this->renderer);
        $this->apiAjax = $apiAjax ?: new ApiAjax();
        $this->systemAjax = $systemAjax ?: new SystemAjax($this->loggingSystem, $this->renderer);
        $this->systemSecurityAjax = $systemSecurityAjax ?: new SystemSecurityAjax($this->loggingSystem, $this->renderer);
        $this->systemPerformanceAjax = $systemPerformanceAjax ?: new SystemPerformanceAjax($this->loggingSystem, $this->renderer);
        $this->formImportAjax = $formImportAjax ?: new FormImportAjax(
            new FormImportService(
                new ApiService(ctm_get_api_url()),
                new CF7Service(),
                new GFService()
            )
        );
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
        $this->systemSecurityAjax->registerHandlers();
        $this->systemPerformanceAjax->registerHandlers();
        $this->formImportAjax->registerHandlers();
    }
} 