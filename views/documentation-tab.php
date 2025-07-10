<?php
// Documentation tab view with main tabs and subtabs
?>
<div class="mb-12">
    <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-8 mb-8">
        <div class="flex items-center mb-6 border-b border-blue-100 pb-4">
            <svg class="w-7 h-7 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 1.343-3 3s1.343 3 3 3 3-1.343 3-3-1.343-3-3-3zm0 10c-4.418 0-8-3.582-8-8s3.582-8 8-8 8 3.582 8 8-3.582 8-8 8z" /></svg>
            <h2 class="text-2xl font-bold text-blue-800 tracking-tight">Documentation</h2>
        </div>
        <div id="main-doc-tabs" class="mb-8">
            <nav class="flex flex-wrap border-b border-gray-200">
                <button class="main-doc-tab px-4 py-2 -mb-px text-blue-700 border-b-2 border-blue-600 font-semibold focus:outline-none" data-tab="general">General</button>
                <button class="main-doc-tab px-4 py-2 -mb-px text-gray-700 border-b-2 border-transparent hover:text-blue-700 hover:border-blue-300" data-tab="debug">Debug</button>
                <button class="main-doc-tab px-4 py-2 -mb-px text-gray-700 border-b-2 border-transparent hover:text-blue-700 hover:border-blue-300" data-tab="api">API</button>
                <button class="main-doc-tab px-4 py-2 -mb-px text-gray-700 border-b-2 border-transparent hover:text-blue-700 hover:border-blue-300" data-tab="cti">CTI</button>
            </nav>
        </div>
        <div id="main-doc-tab-content">
            <div class="main-doc-tab-panel" data-tab="general">
                <!-- General documentation content -->
                <h3 class="text-xl font-semibold text-blue-700 mt-6 mb-2">Installation</h3>
                <ol class="list-decimal pl-6 mb-4">
                    <li>Download and install the plugin from the WordPress plugin directory or upload it manually.</li>
                    <li>Activate the plugin in your WordPress admin.</li>
                    <li>Go to <b>Settings &gt; CallTrackingMetrics</b> to configure.</li>
                </ol>
                <h3 class="text-xl font-semibold text-blue-700 mt-6 mb-2">API Key Management</h3>
                <ul class="list-disc pl-6 mb-4">
                    <li>Log in to your <a href="https://app.calltrackingmetrics.com/accounts/edit#account-api" class="text-blue-600 underline" target="_blank">CallTrackingMetrics account</a>.</li>
                    <li>Navigate to <b>Account Settings &gt; API Keys</b>.</li>
                    <li>Copy your <b>API Key</b> and <b>API Secret</b> and enter them in the plugin settings.</li>
                    <li>Click <b>Save Settings</b> to connect.</li>
                </ul>
                <h3 class="text-xl font-semibold text-blue-700 mt-6 mb-2">Integration Details</h3>
                <ul class="list-disc pl-6 mb-4">
                    <li><b>Contact Form 7:</b> Enable integration in the plugin settings. All CF7 submissions will be tracked and sent to CallTrackingMetrics.</li>
                    <li><b>Gravity Forms:</b> Enable integration in the plugin settings. All GF submissions will be tracked and sent to CallTrackingMetrics.</li>
                </ul>
                <h3 class="text-xl font-semibold text-blue-700 mt-6 mb-2">Field Mapping</h3>
                <p>Map your form fields to CallTrackingMetrics API fields for each form. Use the <b>Field Mapping</b> tab to:</p>
                <ul class="list-disc pl-6 mb-4">
                    <li>Select a form and view its fields</li>
                    <li>Map each field to an API field name</li>
                    <li>Apply transformations (e.g., uppercase, join, date format)</li>
                    <li>Set default values for missing fields</li>
                </ul>
                <h3 class="text-xl font-semibold text-blue-700 mt-6 mb-2">Troubleshooting</h3>
                <ul class="list-disc pl-6 mb-4">
                    <li>Check the <b>Debug</b> tab for API logs and errors.</li>
                    <li>Ensure your API credentials are correct and have the necessary permissions.</li>
                    <li>Contact <a href="mailto:support@calltrackingmetrics.com" class="text-blue-600 underline">support@calltrackingmetrics.com</a> for help.</li>
                </ul>
                <h3 class="text-xl font-semibold text-blue-700 mt-6 mb-2">Security</h3>
                <ul class="list-disc pl-6 mb-4">
                    <li>API credentials are stored securely in the WordPress options table.</li>
                    <li>Only users with <b>manage_options</b> capability can view or change settings.</li>
                </ul>
                <h3 class="text-xl font-semibold text-blue-700 mt-6 mb-2">FAQ</h3>
                <ul class="list-disc pl-6 mb-4">
                    <li><b>Q:</b> Why is my API connection failing?<br><b>A:</b> Double-check your API Key and Secret, and ensure your account is active.</li>
                    <li><b>Q:</b> Can I map custom fields?<br><b>A:</b> Yes, use the Field Mapping tab to map any form field to an API field.</li>
                </ul>
                <h3 class="text-xl font-semibold text-blue-700 mt-6 mb-2">Support</h3>
                <ul class="list-disc pl-6 mb-4">
                    <li>Email: <a href="mailto:support@calltrackingmetrics.com" class="text-blue-600 underline">support@calltrackingmetrics.com</a></li>
                    <li>Website: <a href="https://www.calltrackingmetrics.com/" class="text-blue-600 underline" target="_blank">calltrackingmetrics.com</a></li>
                </ul>
            </div>
            <div class="main-doc-tab-panel hidden" data-tab="debug">
                <!-- Debug subtabs -->
                <div id="debug-subtabs" class="mb-6">
                    <nav class="flex flex-wrap border-b border-gray-200">
                        <button class="debug-subtab px-4 py-2 -mb-px text-blue-700 border-b-2 border-blue-600 font-semibold focus:outline-none" data-subtab="system-info">System Information</button>
                        <button class="debug-subtab px-4 py-2 -mb-px text-gray-700 border-b-2 border-transparent hover:text-blue-700 hover:border-blue-300" data-subtab="health-check">Health Check</button>
                        <button class="debug-subtab px-4 py-2 -mb-px text-gray-700 border-b-2 border-transparent hover:text-blue-700 hover:border-blue-300" data-subtab="performance-monitor">Performance Monitor</button>
                        <button class="debug-subtab px-4 py-2 -mb-px text-gray-700 border-b-2 border-transparent hover:text-blue-700 hover:border-blue-300" data-subtab="log-settings">Log Settings</button>
                        <button class="debug-subtab px-4 py-2 -mb-px text-gray-700 border-b-2 border-transparent hover:text-blue-700 hover:border-blue-300" data-subtab="daily-logs">Daily Logs</button>
                    </nav>
                </div>
                <div id="debug-subtab-content">
                    <div class="debug-subtab-panel" data-subtab="system-info">
                        <h3 class="text-xl font-semibold text-blue-700 mt-6 mb-2">System Information</h3>
                        <p>This section provides a comprehensive overview of your WordPress and server environment. It includes PHP version, WordPress version, memory usage, database details, and more. Use the <b>Export System Info</b> button to copy all details for support or troubleshooting.</p>
                        <ul class="list-disc pl-6 mb-4">
                            <li>WordPress and server environment details</li>
                            <li>PHP, MySQL, and server software versions</li>
                            <li>Active plugins and theme information</li>
                            <li>Memory usage and limits</li>
                            <li>Quick export for support tickets</li>
                        </ul>
                    </div>
                    <div class="debug-subtab-panel hidden" data-subtab="health-check">
                        <h3 class="text-xl font-semibold text-blue-700 mt-6 mb-2">Health Check</h3>
                        <p>The Health Check feature runs a series of automated tests to assess your plugin and server configuration. It checks API connectivity, form integrations, server requirements, and plugin status. Use the <b>Run Health Check</b> button to get instant feedback and recommendations.</p>
                        <ul class="list-disc pl-6 mb-4">
                            <li>API key and connection validation</li>
                            <li>Form integration checks (Contact Form 7, Gravity Forms)</li>
                            <li>Server requirements (PHP version, cURL, SSL, memory)</li>
                            <li>Plugin version, database tables, file permissions</li>
                            <li>Actionable recommendations for any issues found</li>
                        </ul>
                    </div>
                    <div class="debug-subtab-panel hidden" data-subtab="performance-monitor">
                        <h3 class="text-xl font-semibold text-blue-700 mt-6 mb-2">Performance Monitor</h3>
                        <p>The Performance Monitor provides real-time metrics on memory usage, page load time, database queries, and API calls. Use this tool to identify bottlenecks and optimize your site’s performance.</p>
                        <ul class="list-disc pl-6 mb-4">
                            <li>Live memory, CPU, and execution time stats</li>
                            <li>Database query count and timing</li>
                            <li>Page load and resource loading times</li>
                            <li>API call volume and response times</li>
                            <li>Breakdown of plugin and theme performance</li>
                        </ul>
                    </div>
                    <div class="debug-subtab-panel hidden" data-subtab="log-settings">
                        <h3 class="text-xl font-semibold text-blue-700 mt-6 mb-2">Log Settings</h3>
                        <p>Configure how debug logs are managed. Set retention periods, enable automatic cleanup, and receive email notifications for critical errors. Keeping logs well-managed helps maintain site performance and security.</p>
                        <ul class="list-disc pl-6 mb-4">
                            <li>Set how many days logs are kept (1-365 days)</li>
                            <li>Enable or disable automatic log cleanup</li>
                            <li>Configure email notifications for errors</li>
                            <li>Set the notification email address</li>
                            <li>Update log settings instantly</li>
                        </ul>
                    </div>
                    <div class="debug-subtab-panel hidden" data-subtab="daily-logs">
                        <h3 class="text-xl font-semibold text-blue-700 mt-6 mb-2">Daily Logs</h3>
                        <p>View, filter, and manage daily debug logs. Each day’s log shows all plugin activity, errors, warnings, and more. You can email logs, clear them, or view detailed context for each entry.</p>
                        <ul class="list-disc pl-6 mb-4">
                            <li>Browse logs by date</li>
                            <li>See error, warning, and info counts</li>
                            <li>View detailed log entries and context</li>
                            <li>Email logs for support or archiving</li>
                            <li>Clear logs for specific days</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="main-doc-tab-panel hidden" data-tab="api">
                <!-- API subtabs -->
                <div id="api-subtabs" class="mb-6">
                    <nav class="flex flex-wrap border-b border-gray-200">
                        <button class="api-subtab px-4 py-2 -mb-px text-blue-700 border-b-2 border-blue-600 font-semibold focus:outline-none" data-subtab="overview">Overview</button>
                        <button class="api-subtab px-4 py-2 -mb-px text-gray-700 border-b-2 border-transparent hover:text-blue-700 hover:border-blue-300" data-subtab="authentication">Authentication</button>
                        <button class="api-subtab px-4 py-2 -mb-px text-gray-700 border-b-2 border-transparent hover:text-blue-700 hover:border-blue-300" data-subtab="endpoints">Endpoints</button>
                    </nav>
                </div>
                <div id="api-subtab-content">
                    <div class="api-subtab-panel" data-subtab="overview">
                        <h3 class="text-xl font-semibold text-blue-700 mt-6 mb-2">API Overview</h3>
                        <p>This section will describe the API integration, usage, and best practices. (Placeholder)</p>
                    </div>
                    <div class="api-subtab-panel hidden" data-subtab="authentication">
                        <h3 class="text-xl font-semibold text-blue-700 mt-6 mb-2">API Authentication</h3>
                        <p>How to authenticate with the API. (Placeholder)</p>
                    </div>
                    <div class="api-subtab-panel hidden" data-subtab="endpoints">
                        <h3 class="text-xl font-semibold text-blue-700 mt-6 mb-2">API Endpoints</h3>
                        <p>List of available API endpoints and their usage. (Placeholder)</p>
                    </div>
                </div>
            </div>
            <div class="main-doc-tab-panel hidden" data-tab="cti">
                <!-- CTI subtabs -->
                <div id="cti-subtabs" class="mb-6">
                    <nav class="flex flex-wrap border-b border-gray-200">
                        <button class="cti-subtab px-4 py-2 -mb-px text-blue-700 border-b-2 border-blue-600 font-semibold focus:outline-none" data-subtab="overview">Overview</button>
                        <button class="cti-subtab px-4 py-2 -mb-px text-gray-700 border-b-2 border-transparent hover:text-blue-700 hover:border-blue-300" data-subtab="setup">Setup</button>
                        <button class="cti-subtab px-4 py-2 -mb-px text-gray-700 border-b-2 border-transparent hover:text-blue-700 hover:border-blue-300" data-subtab="features">Features</button>
                    </nav>
                </div>
                <div id="cti-subtab-content">
                    <div class="cti-subtab-panel" data-subtab="overview">
                        <h3 class="text-xl font-semibold text-blue-700 mt-6 mb-2">CTI Overview</h3>
                        <p>This section will describe the CTI (Computer Telephony Integration) features and setup. (Placeholder)</p>
                    </div>
                    <div class="cti-subtab-panel hidden" data-subtab="setup">
                        <h3 class="text-xl font-semibold text-blue-700 mt-6 mb-2">CTI Setup</h3>
                        <p>How to set up CTI integration. (Placeholder)</p>
                    </div>
                    <div class="cti-subtab-panel hidden" data-subtab="features">
                        <h3 class="text-xl font-semibold text-blue-700 mt-6 mb-2">CTI Features</h3>
                        <p>List of CTI features and their usage. (Placeholder)</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
// Main tab switcher
const mainTabButtons = document.querySelectorAll('.main-doc-tab');
const mainTabPanels = document.querySelectorAll('.main-doc-tab-panel');
mainTabButtons.forEach(btn => {
    btn.addEventListener('click', function() {
        mainTabButtons.forEach(b => b.classList.remove('text-blue-700', 'border-blue-600', 'font-semibold'));
        mainTabButtons.forEach(b => b.classList.add('text-gray-700', 'border-transparent'));
        this.classList.add('text-blue-700', 'border-blue-600', 'font-semibold');
        this.classList.remove('text-gray-700', 'border-transparent');
        const tab = this.getAttribute('data-tab');
        mainTabPanels.forEach(panel => {
            if (panel.getAttribute('data-tab') === tab) {
                panel.classList.remove('hidden');
            } else {
                panel.classList.add('hidden');
            }
        });
        // Reset subtabs to first for each main tab
        if(tab === 'debug') {
            showSubtab('debug', 'system-info');
        } else if(tab === 'api') {
            showSubtab('api', 'overview');
        } else if(tab === 'cti') {
            showSubtab('cti', 'overview');
        }
    });
});
// Debug subtab switcher
function showSubtab(group, subtab) {
    document.querySelectorAll('.' + group + '-subtab').forEach(btn => {
        if(btn.getAttribute('data-subtab') === subtab) {
            btn.classList.add('text-blue-700', 'border-blue-600', 'font-semibold');
            btn.classList.remove('text-gray-700', 'border-transparent');
        } else {
            btn.classList.remove('text-blue-700', 'border-blue-600', 'font-semibold');
            btn.classList.add('text-gray-700', 'border-transparent');
        }
    });
    document.querySelectorAll('.' + group + '-subtab-panel').forEach(panel => {
        if(panel.getAttribute('data-subtab') === subtab) {
            panel.classList.remove('hidden');
        } else {
            panel.classList.add('hidden');
        }
    });
}
document.querySelectorAll('.debug-subtab').forEach(btn => {
    btn.addEventListener('click', function() {
        showSubtab('debug', this.getAttribute('data-subtab'));
    });
});
document.querySelectorAll('.api-subtab').forEach(btn => {
    btn.addEventListener('click', function() {
        showSubtab('api', this.getAttribute('data-subtab'));
    });
});
document.querySelectorAll('.cti-subtab').forEach(btn => {
    btn.addEventListener('click', function() {
        showSubtab('cti', this.getAttribute('data-subtab'));
    });
});
// Default to General tab and first subtab for each group
if(mainTabPanels.length) mainTabPanels[0].classList.remove('hidden');
showSubtab('debug', 'system-info');
showSubtab('api', 'overview');
showSubtab('cti', 'overview');
</script> 