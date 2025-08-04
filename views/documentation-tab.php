<?php
// Documentation tab view with main tabs and subtabs
?>
<div class="mb-12">
    <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-8 mb-8">
        <div class="flex items-center mb-6 border-b border-[#02bdf6] pb-4">
            <svg class="w-7 h-7 text-[#02bdf6] mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 1.343-3 3s1.343 3 3 3 3-1.343 3-3-1.343-3-3-3zm0 10c-4.418 0-8-3.582-8-8s3.582-8 8-8 8 3.582 8 8-3.582 8-8 8z" /></svg>
            <h2 class="text-2xl font-bold text-[#16294f] tracking-tight font-brand-heading"><?php _e('Documentation', 'call-tracking-metrics'); ?></h2>
        </div>
        <div id="main-doc-tabs" class="mb-8">
            <nav class="flex flex-wrap border-b border-gray-200">
                <button class="main-doc-tab px-4 py-2 -mb-px text-white border-b-2 border-blue-600 font-semibold focus:outline-none" data-tab="general"><?php _e('General', 'call-tracking-metrics'); ?></button>
                <button class="main-doc-tab px-4 py-2 -mb-px text-gray-700 border-b-2 border-transparent hover:text-blue-700 hover:border-blue-300" data-tab="debug"><?php _e('Debug', 'call-tracking-metrics'); ?></button>
                <button class="main-doc-tab px-4 py-2 -mb-px text-gray-700 border-b-2 border-transparent hover:text-blue-700 hover:border-blue-300" data-tab="api"><?php _e('API', 'call-tracking-metrics'); ?></button>
                <button class="main-doc-tab px-4 py-2 -mb-px text-gray-700 border-b-2 border-transparent hover:text-blue-700 hover:border-blue-300" data-tab="cti"><?php _e('CTI', 'call-tracking-metrics'); ?></button>
            </nav>
        </div>
        <div id="main-doc-tab-content">
            <div class="main-doc-tab-panel" data-tab="general">
                <!-- General documentation content -->
                <h3 class="text-xl font-semibold text-blue-700 mt-6 mb-2"><?php _e('Installation', 'call-tracking-metrics'); ?></h3>
                <ol class="list-decimal pl-6 mb-4">
                    <li><?php _e('Download and install the plugin from the WordPress plugin directory or upload it manually.', 'call-tracking-metrics'); ?></li>
                    <li><?php _e('Activate the plugin in your WordPress admin.', 'call-tracking-metrics'); ?></li>
                    <li><?php _e('Go to <b>Settings &gt; CallTrackingMetrics</b> to configure.', 'call-tracking-metrics'); ?></li>
                </ol>
                <h3 class="text-xl font-semibold text-blue-700 mt-6 mb-2"><?php _e('API Key Management', 'call-tracking-metrics'); ?></h3>
                <ul class="list-disc pl-6 mb-4">
                    <li><?php _e('Log in to your <a href="https://app.calltrackingmetrics.com/accounts/edit#account-api" class="text-blue-600 underline" target="_blank">CallTrackingMetrics account</a>.', 'call-tracking-metrics'); ?></li>
                    <li><?php _e('Navigate to <b>Account Settings &gt; API Keys</b>.', 'call-tracking-metrics'); ?></li>
                    <li><?php _e('Copy your <b>API Key</b> and <b>API Secret</b> and enter them in the plugin settings.', 'call-tracking-metrics'); ?></li>
                    <li><?php _e('Click <b>Save Settings</b> to connect.', 'call-tracking-metrics'); ?></li>
                </ul>
                <h3 class="text-xl font-semibold text-blue-700 mt-6 mb-2"><?php _e('Integration Details', 'call-tracking-metrics'); ?></h3>
                <ul class="list-disc pl-6 mb-4">
                    <li><b><?php _e('Contact Form 7:', 'call-tracking-metrics'); ?></b> <?php _e('Enable integration in the plugin settings. All CF7 submissions will be tracked and sent to CallTrackingMetrics.', 'call-tracking-metrics'); ?></li>
                    <li><b><?php _e('Gravity Forms:', 'call-tracking-metrics'); ?></b> <?php _e('Enable integration in the plugin settings. All GF submissions will be tracked and sent to CallTrackingMetrics.', 'call-tracking-metrics'); ?></li>
                </ul>
                <h3 class="text-xl font-semibold text-blue-700 mt-6 mb-2"><?php _e('Field Mapping', 'call-tracking-metrics'); ?></h3>
                <p><?php _e('Map your form fields to CallTrackingMetrics API fields for each form. Use the <b>Field Mapping</b> tab to:', 'call-tracking-metrics'); ?></p>
                <ul class="list-disc pl-6 mb-4">
                    <li><?php _e('Select a form and view its fields', 'call-tracking-metrics'); ?></li>
                    <li><?php _e('Map each field to an API field name', 'call-tracking-metrics'); ?></li>
                    <li><?php _e('Apply transformations (e.g., uppercase, join, date format)', 'call-tracking-metrics'); ?></li>
                    <li><?php _e('Set default values for missing fields', 'call-tracking-metrics'); ?></li>
                </ul>
                <h3 class="text-xl font-semibold text-blue-700 mt-6 mb-2"><?php _e('Troubleshooting', 'call-tracking-metrics'); ?></h3>
                <ul class="list-disc pl-6 mb-4">
                    <li><?php _e('Check the <b>Debug</b> tab for API logs and errors.', 'call-tracking-metrics'); ?></li>
                    <li><?php _e('Ensure your API credentials are correct and have the necessary permissions.', 'call-tracking-metrics'); ?></li>
                    <li><?php _e('Contact <a href="mailto:support@calltrackingmetrics.com" class="text-blue-600 underline">support@calltrackingmetrics.com</a> for help.', 'call-tracking-metrics'); ?></li>
                </ul>
                <h3 class="text-xl font-semibold text-blue-700 mt-6 mb-2"><?php _e('Security', 'call-tracking-metrics'); ?></h3>
                <ul class="list-disc pl-6 mb-4">
                    <li><?php _e('API credentials are stored securely in the WordPress options table.', 'call-tracking-metrics'); ?></li>
                    <li><?php _e('Only users with <b>manage_options</b> capability can view or change settings.', 'call-tracking-metrics'); ?></li>
                </ul>
                <h3 class="text-xl font-semibold text-blue-700 mt-6 mb-2"><?php _e('FAQ', 'call-tracking-metrics'); ?></h3>
                <ul class="list-disc pl-6 mb-4">
                    <li><b><?php _e('Q:', 'call-tracking-metrics'); ?></b> <?php _e('Why is my API connection failing?', 'call-tracking-metrics'); ?><br><b><?php _e('A:', 'call-tracking-metrics'); ?></b> <?php _e('Double-check your API Key and Secret, and ensure your account is active.', 'call-tracking-metrics'); ?></li>
                    <li><b><?php _e('Q:', 'call-tracking-metrics'); ?></b> <?php _e('Can I map custom fields?', 'call-tracking-metrics'); ?><br><b><?php _e('A:', 'call-tracking-metrics'); ?></b> <?php _e('Yes, use the Field Mapping tab to map any form field to an API field.', 'call-tracking-metrics'); ?></li>
                </ul>
                <h3 class="text-xl font-semibold text-blue-700 mt-6 mb-2"><?php _e('Support', 'call-tracking-metrics'); ?></h3>
                <ul class="list-disc pl-6 mb-4">
                    <li><?php _e('Email: <a href="mailto:support@calltrackingmetrics.com" class="text-blue-600 underline">support@calltrackingmetrics.com</a>', 'call-tracking-metrics'); ?></li>
                    <li><?php _e('Website: <a href="https://www.calltrackingmetrics.com/" class="text-blue-600 underline" target="_blank">calltrackingmetrics.com</a>', 'call-tracking-metrics'); ?></li>
                </ul>
                <h3 class="text-xl font-semibold text-blue-700 mt-6 mb-2"><?php _e('Plugin Overview', 'call-tracking-metrics'); ?></h3>
                <p class="mb-4"><?php _e('The CallTrackingMetrics WordPress plugin connects your site to the CallTrackingMetrics platform, enabling advanced call tracking, form tracking, and analytics. It supports seamless integration with Contact Form 7 and Gravity Forms, and provides robust debugging and logging tools for administrators.', 'call-tracking-metrics'); ?></p>
                <h3 class="text-xl font-semibold text-blue-700 mt-6 mb-2"><?php _e('Advanced Troubleshooting', 'call-tracking-metrics'); ?></h3>
                <ul class="list-disc pl-6 mb-4">
                    <li><?php _e('If form submissions are not appearing in CallTrackingMetrics, ensure the plugin is connected and the correct API credentials are used.', 'call-tracking-metrics'); ?></li>
                    <li><?php _e('Check the Debug tab for recent errors or failed API calls.', 'call-tracking-metrics'); ?></li>
                    <li><?php _e('Make sure your forms include required fields (such as phone number for FormReactor integration).', 'call-tracking-metrics'); ?></li>
                    <li><?php _e('Clear any site caching plugins and test again.', 'call-tracking-metrics'); ?></li>
                    <li><?php _e('Review your browser console for JavaScript errors that may block script execution.', 'call-tracking-metrics'); ?></li>
                </ul>
                <h3 class="text-xl font-semibold text-blue-700 mt-6 mb-2"><?php _e('Best Practices', 'call-tracking-metrics'); ?></h3>
                <ul class="list-disc pl-6 mb-4">
                    <li><?php _e('Keep your plugin and WordPress core up to date for security and compatibility.', 'call-tracking-metrics'); ?></li>
                    <li><?php _e('Use strong, unique API credentials and never share them publicly.', 'call-tracking-metrics'); ?></li>
                    <li><?php _e('Enable debugging only when needed to avoid excessive log growth.', 'call-tracking-metrics'); ?></li>
                    <li><?php _e('Regularly review logs and clear old entries to maintain performance.', 'call-tracking-metrics'); ?></li>
                </ul>
                <h3 class="text-xl font-semibold text-blue-700 mt-6 mb-2"><?php _e('Changelog', 'call-tracking-metrics'); ?></h3>
                <ul class="list-disc pl-6 mb-4">
                    <li><b><?php _e('2.0.0', 'call-tracking-metrics'); ?></b> – <?php _e('Major refactor, improved UI, added debug tools, and enhanced API integration.', 'call-tracking-metrics'); ?></li>
                    <li><b><?php _e('1.5.0', 'call-tracking-metrics'); ?></b> – <?php _e('Added Gravity Forms support and field mapping UI.', 'call-tracking-metrics'); ?></li>
                    <li><b><?php _e('1.0.0', 'call-tracking-metrics'); ?></b> – <?php _e('Initial release with Contact Form 7 integration.', 'call-tracking-metrics'); ?></li>
                </ul>
            </div>
            <div class="main-doc-tab-panel hidden" data-tab="debug">
                <!-- Debug subtabs -->
                <div id="debug-subtabs" class="mb-6">
                    <nav class="flex flex-wrap border-b border-gray-200">
                        <button class="debug-subtab px-4 py-2 -mb-px text-white border-b-2 border-blue-600 font-semibold focus:outline-none" data-subtab="system-info"><?php _e('System Information', 'call-tracking-metrics'); ?></button>
                        <button class="debug-subtab px-4 py-2 -mb-px text-gray-700 border-b-2 border-transparent hover:text-blue-700 hover:border-blue-300" data-subtab="health-check"><?php _e('Health Check', 'call-tracking-metrics'); ?></button>
                        <button class="debug-subtab px-4 py-2 -mb-px text-gray-700 border-b-2 border-transparent hover:text-blue-700 hover:border-blue-300" data-subtab="performance-monitor"><?php _e('Performance Monitor', 'call-tracking-metrics'); ?></button>
                        <button class="debug-subtab px-4 py-2 -mb-px text-gray-700 border-b-2 border-transparent hover:text-blue-700 hover:border-blue-300" data-subtab="log-settings"><?php _e('Log Settings', 'call-tracking-metrics'); ?></button>
                        <button class="debug-subtab px-4 py-2 -mb-px text-gray-700 border-b-2 border-transparent hover:text-blue-700 hover:border-blue-300" data-subtab="daily-logs"><?php _e('Daily Logs', 'call-tracking-metrics'); ?></button>
                    </nav>
                </div>
                <div id="debug-subtab-content">
                    <div class="debug-subtab-panel" data-subtab="system-info">
                        <h3 class="text-xl font-semibold text-blue-700 mt-6 mb-2"><?php _e('System Information', 'call-tracking-metrics'); ?></h3>
                        <p><?php _e('This section provides a comprehensive overview of your WordPress and server environment. It includes PHP version, WordPress version, memory usage, database details, and more. Use the <b>Export System Info</b> button to copy all details for support or troubleshooting.', 'call-tracking-metrics'); ?></p>
                        <ul class="list-disc pl-6 mb-4">
                            <li><?php _e('WordPress and server environment details', 'call-tracking-metrics'); ?></li>
                            <li><?php _e('PHP, MySQL, and server software versions', 'call-tracking-metrics'); ?></li>
                            <li><?php _e('Active plugins and theme information', 'call-tracking-metrics'); ?></li>
                            <li><?php _e('Memory usage and limits', 'call-tracking-metrics'); ?></li>
                            <li><?php _e('Quick export for support tickets', 'call-tracking-metrics'); ?></li>
                        </ul>
                    </div>
                    <div class="debug-subtab-panel hidden" data-subtab="health-check">
                        <h3 class="text-xl font-semibold text-blue-700 mt-6 mb-2"><?php _e('Health Check', 'call-tracking-metrics'); ?></h3>
                        <p><?php _e('The Health Check feature runs a series of automated tests to assess your plugin and server configuration. It checks API connectivity, form integrations, server requirements, and plugin status. Use the <b>Run Health Check</b> button to get instant feedback and recommendations.', 'call-tracking-metrics'); ?></p>
                        <ul class="list-disc pl-6 mb-4">
                            <li><?php _e('API key and connection validation', 'call-tracking-metrics'); ?></li>
                            <li><?php _e('Form integration checks (Contact Form 7, Gravity Forms)', 'call-tracking-metrics'); ?></li>
                            <li><?php _e('Server requirements (PHP version, cURL, SSL, memory)', 'call-tracking-metrics'); ?></li>
                            <li><?php _e('Plugin version, database tables, file permissions', 'call-tracking-metrics'); ?></li>
                            <li><?php _e('Actionable recommendations for any issues found', 'call-tracking-metrics'); ?></li>
                        </ul>
                    </div>
                    <div class="debug-subtab-panel hidden" data-subtab="performance-monitor">
                        <h3 class="text-xl font-semibold text-blue-700 mt-6 mb-2"><?php _e('Performance Monitor', 'call-tracking-metrics'); ?></h3>
                        <p><?php _e('The Performance Monitor provides real-time metrics on memory usage, page load time, database queries, and API calls. Use this tool to identify bottlenecks and optimize your site’s performance.', 'call-tracking-metrics'); ?></p>
                        <ul class="list-disc pl-6 mb-4">
                            <li><?php _e('Live memory, CPU, and execution time stats', 'call-tracking-metrics'); ?></li>
                            <li><?php _e('Database query count and timing', 'call-tracking-metrics'); ?></li>
                            <li><?php _e('Page load and resource loading times', 'call-tracking-metrics'); ?></li>
                            <li><?php _e('API call volume and response times', 'call-tracking-metrics'); ?></li>
                            <li><?php _e('Breakdown of plugin and theme performance', 'call-tracking-metrics'); ?></li>
                        </ul>
                    </div>
                    <div class="debug-subtab-panel hidden" data-subtab="log-settings">
                        <h3 class="text-xl font-semibold text-blue-700 mt-6 mb-2"><?php _e('Log Settings', 'call-tracking-metrics'); ?></h3>
                        <p><?php _e('Configure how debug logs are managed. Set retention periods, enable automatic cleanup, and receive email notifications for critical errors. Keeping logs well-managed helps maintain site performance and security.', 'call-tracking-metrics'); ?></p>
                        <ul class="list-disc pl-6 mb-4">
                            <li><?php _e('Set how many days logs are kept (1-365 days)', 'call-tracking-metrics'); ?></li>
                            <li><?php _e('Enable or disable automatic log cleanup', 'call-tracking-metrics'); ?></li>
                            <li><?php _e('Configure email notifications for errors', 'call-tracking-metrics'); ?></li>
                            <li><?php _e('Set the notification email address', 'call-tracking-metrics'); ?></li>
                            <li><?php _e('Update log settings instantly', 'call-tracking-metrics'); ?></li>
                        </ul>
                    </div>
                    <div class="debug-subtab-panel hidden" data-subtab="daily-logs">
                        <h3 class="text-xl font-semibold text-blue-700 mt-6 mb-2"><?php _e('Daily Logs', 'call-tracking-metrics'); ?></h3>
                        <p><?php _e('View, filter, and manage daily debug logs. Each day’s log shows all plugin activity, errors, warnings, and more. You can email logs, clear them, or view detailed context for each entry.', 'call-tracking-metrics'); ?></p>
                        <ul class="list-disc pl-6 mb-4">
                            <li><?php _e('Browse logs by date', 'call-tracking-metrics'); ?></li>
                            <li><?php _e('See error, warning, and info counts', 'call-tracking-metrics'); ?></li>
                            <li><?php _e('View detailed log entries and context', 'call-tracking-metrics'); ?></li>
                            <li><?php _e('Email logs for support or archiving', 'call-tracking-metrics'); ?></li>
                            <li><?php _e('Clear logs for specific days', 'call-tracking-metrics'); ?></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="main-doc-tab-panel hidden" data-tab="api">
                <!-- API subtabs -->
                <div id="api-subtabs" class="mb-6">
                    <nav class="flex flex-wrap border-b border-gray-200">
                        <button class="api-subtab px-4 py-2 -mb-px text-blue-700 border-b-2 border-blue-600 font-semibold focus:outline-none" data-subtab="overview"><?php _e('Overview', 'call-tracking-metrics'); ?></button>
                        <button class="api-subtab px-4 py-2 -mb-px text-gray-700 border-b-2 border-transparent hover:text-blue-700 hover:border-blue-300" data-subtab="authentication"><?php _e('Authentication', 'call-tracking-metrics'); ?></button>
                        <button class="api-subtab px-4 py-2 -mb-px text-gray-700 border-b-2 border-transparent hover:text-blue-700 hover:border-blue-300" data-subtab="endpoints"><?php _e('Endpoints', 'call-tracking-metrics'); ?></button>
                    </nav>
                </div>
                <div id="api-subtab-content">
                    <div class="api-subtab-panel" data-subtab="overview">
                        <h3 class="text-xl font-semibold text-blue-700 mt-6 mb-2"><?php _e('API Overview', 'call-tracking-metrics'); ?></h3>
                        <p><?php _e('The CallTrackingMetrics API allows you to programmatically submit form data, retrieve call logs, and manage account settings. This plugin uses the API to send form submissions and fetch account information.', 'call-tracking-metrics'); ?></p>
                        <ul class="list-disc pl-6 mb-4">
                            <li><?php _e('RESTful JSON API', 'call-tracking-metrics'); ?></li>
                            <li><?php _e('Requires API Key and Secret', 'call-tracking-metrics'); ?></li>
                            <li><?php _e('Supports form submission, call log retrieval, and more', 'call-tracking-metrics'); ?></li>
                        </ul>
                    </div>
                    <div class="api-subtab-panel hidden" data-subtab="authentication">
                        <h3 class="text-xl font-semibold text-blue-700 mt-6 mb-2"><?php _e('API Authentication', 'call-tracking-metrics'); ?></h3>
                        <p><?php _e('Authenticate using your API Key and Secret, which you can generate in your CallTrackingMetrics account settings. All requests must be made over HTTPS.', 'call-tracking-metrics'); ?></p>
                        <ul class="list-disc pl-6 mb-4">
                            <li><?php _e('Send credentials as HTTP headers: <code>X-Api-Key</code> and <code>X-Api-Secret</code>', 'call-tracking-metrics'); ?></li>
                            <li><?php _e('Never expose your credentials in client-side code', 'call-tracking-metrics'); ?></li>
                        </ul>
                    </div>
                    <div class="api-subtab-panel hidden" data-subtab="endpoints">
                        <h3 class="text-xl font-semibold text-blue-700 mt-6 mb-2"><?php _e('API Endpoints', 'call-tracking-metrics'); ?></h3>
                        <ul class="list-disc pl-6 mb-4">
                            <li><b><?php _e('POST /api/v1/formreactor/submit', 'call-tracking-metrics'); ?></b> – <?php _e('Submit form data to CallTrackingMetrics', 'call-tracking-metrics'); ?></li>
                            <li><b><?php _e('GET /api/v1/calls', 'call-tracking-metrics'); ?></b> – <?php _e('Retrieve recent call logs', 'call-tracking-metrics'); ?></li>
                            <li><b><?php _e('GET /api/v1/account', 'call-tracking-metrics'); ?></b> – <?php _e('Fetch account details', 'call-tracking-metrics'); ?></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="main-doc-tab-panel hidden" data-tab="cti">
                <!-- CTI subtabs -->
                <div id="cti-subtabs" class="mb-6">
                    <nav class="flex flex-wrap border-b border-gray-200">
                        <button class="cti-subtab px-4 py-2 -mb-px text-blue-700 border-b-2 border-blue-600 font-semibold focus:outline-none" data-subtab="overview"><?php _e('Overview', 'call-tracking-metrics'); ?></button>
                        <button class="cti-subtab px-4 py-2 -mb-px text-gray-700 border-b-2 border-transparent hover:text-blue-700 hover:border-blue-300" data-subtab="setup"><?php _e('Setup', 'call-tracking-metrics'); ?></button>
                        <button class="cti-subtab px-4 py-2 -mb-px text-gray-700 border-b-2 border-transparent hover:text-blue-700 hover:border-blue-300" data-subtab="features"><?php _e('Features', 'call-tracking-metrics'); ?></button>
                    </nav>
                </div>
                <div id="cti-subtab-content">
                    <div class="cti-subtab-panel" data-subtab="overview">
                        <h3 class="text-xl font-semibold text-blue-700 mt-6 mb-2"><?php _e('CTI Overview', 'call-tracking-metrics'); ?></h3>
                        <p><?php _e('Computer Telephony Integration (CTI) connects your website and phone system, allowing you to track calls, trigger workflows, and display caller information in real time. CTI is available for advanced users and enterprise accounts.', 'call-tracking-metrics'); ?></p>
                    </div>
                    <div class="cti-subtab-panel hidden" data-subtab="setup">
                        <h3 class="text-xl font-semibold text-blue-700 mt-6 mb-2"><?php _e('CTI Setup', 'call-tracking-metrics'); ?></h3>
                        <ol class="list-decimal pl-6 mb-4">
                            <li><?php _e('Contact CallTrackingMetrics support to enable CTI for your account.', 'call-tracking-metrics'); ?></li>
                            <li><?php _e('Configure your phone system to connect with the CTM API.', 'call-tracking-metrics'); ?></li>
                            <li><?php _e('Follow the integration guide provided by support for your specific PBX or VoIP system.', 'call-tracking-metrics'); ?></li>
                        </ol>
                    </div>
                    <div class="cti-subtab-panel hidden" data-subtab="features">
                        <h3 class="text-xl font-semibold text-blue-700 mt-6 mb-2"><?php _e('CTI Features', 'call-tracking-metrics'); ?></h3>
                        <ul class="list-disc pl-6 mb-4">
                            <li><?php _e('Real-time call tracking and analytics', 'call-tracking-metrics'); ?></li>
                            <li><?php _e('Screen pops with caller information', 'call-tracking-metrics'); ?></li>
                            <li><?php _e('Automated call logging and workflow triggers', 'call-tracking-metrics'); ?></li>
                            <li><?php _e('Integration with CRM and helpdesk platforms', 'call-tracking-metrics'); ?></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>