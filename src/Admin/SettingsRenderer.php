<?php
namespace CTM\Admin;

/**
 * Handles rendering of settings pages and tab content
 */
class SettingsRenderer
{
    /**
     * Render a view file with variables
     */
    public function renderView(string $view, array $vars = []): void
    {
        $viewPath = plugin_dir_path(__FILE__) . '../../views/' . $view . '.php';
        
        if (!file_exists($viewPath)) {
            echo "<div style='color:red'>View not found: $viewPath</div>";
            return;
        }
        
        extract($vars);
        include $viewPath;
    }

    /**
     * Get general tab content
     */
    public function getGeneralTabContent(): string
    {
        $apiKey = get_option('ctm_api_key');
        $apiSecret = get_option('ctm_api_secret');
        $accountId = get_option('ctm_api_auth_account');
        $dashboardEnabled = get_option('ctm_api_dashboard_enabled');
        $trackingEnabled = get_option('ctm_api_tracking_enabled');
        $cf7Enabled = get_option('ctm_api_cf7_enabled');
        $gfEnabled = get_option('ctm_api_gf_enabled');
        $debugEnabled = LoggingSystem::isDebugEnabled();
        
        $apiStatus = 'not_tested';
        $accountInfo = null;
        $acctDetails = null;
        
        if ($apiKey && $apiSecret) {
            $apiService = new \CTM\Service\ApiService('https://api.calltrackingmetrics.com');
            $accountInfo = $apiService->getAccountInfo($apiKey, $apiSecret);
            $apiStatus = ($accountInfo && isset($accountInfo['account'])) ? 'connected' : 'not_connected';
            
            if ($apiStatus === 'connected' && $accountInfo && isset($accountInfo['account']['id'])) {
                $acctDetails = $apiService->getAccountById($accountInfo['account']['id'], $apiKey, $apiSecret);
            }
        }
        
        ob_start();
        $this->renderView('general-tab', [
            'apiKey' => $apiKey,
            'apiSecret' => $apiSecret,
            'accountId' => $accountId,
            'dashboardEnabled' => $dashboardEnabled,
            'trackingEnabled' => $trackingEnabled,
            'cf7Enabled' => $cf7Enabled,
            'gfEnabled' => $gfEnabled,
            'debugEnabled' => $debugEnabled,
            'apiStatus' => $apiStatus,
            'accountInfo' => $accountInfo,
            'acctDetails' => $acctDetails,
        ]);
        return ob_get_clean();
    }

    /**
     * Get logs tab content
     */
    public function getLogsTabContent(): string
    {
        $cf7Logs = json_decode(get_option('ctm_api_cf7_logs', '[]'), true) ?: [];
        $gfLogs = json_decode(get_option('ctm_api_gf_logs', '[]'), true) ?: [];
        
        ob_start();
        $this->renderView('logs-tab', [
            'cf7Logs' => $cf7Logs,
            'gfLogs' => $gfLogs,
        ]);
        return ob_get_clean();
    }

    /**
     * Get mapping tab content
     */
    public function getMappingTabContent(): string
    {
        ob_start();
        $this->renderView('mapping-tab');
        return ob_get_clean();
    }

    /**
     * Get API tab content
     */
    public function getApiTabContent(): string
    {
        $apiKey = get_option('ctm_api_key');
        $apiSecret = get_option('ctm_api_secret');
        $apiStatus = 'not_tested';
        $accountInfo = null;
        
        if ($apiKey && $apiSecret) {
            $apiService = new \CTM\Service\ApiService('https://api.calltrackingmetrics.com');
            $accountInfo = $apiService->getAccountInfo($apiKey, $apiSecret);
            $apiStatus = ($accountInfo && isset($accountInfo['account'])) ? 'connected' : 'not_connected';
        }
        
        ob_start();
        $this->renderView('api-tab', [
            'apiKey' => $apiKey,
            'apiSecret' => $apiSecret,
            'apiStatus' => $apiStatus,
            'accountInfo' => $accountInfo,
        ]);
        return ob_get_clean();
    }

    /**
     * Get documentation tab content
     */
    public function getDocumentationTabContent(): string
    {
        ob_start();
        $this->renderView('documentation-tab');
        return ob_get_clean();
    }

    /**
     * Get debug tab content
     */
    public function getDebugTabContent(): string
    {
        $loggingSystem = new LoggingSystem();
        $debugEnabled = LoggingSystem::isDebugEnabled();
        
        ob_start();
        $this->renderView('debug-tab', [
            'retention_days' => (int) get_option('ctm_log_retention_days', 7),
            'auto_cleanup' => get_option('ctm_log_auto_cleanup', true),
            'email_notifications' => get_option('ctm_log_email_notifications', false),
            'notification_email' => get_option('ctm_log_notification_email', get_option('admin_email')),
            'debugEnabled' => $debugEnabled,
            'available_dates' => $loggingSystem->getAvailableLogDates(),
            'log_statistics' => $loggingSystem->getLogStatistics()
        ]);
        return ob_get_clean();
    }
} 