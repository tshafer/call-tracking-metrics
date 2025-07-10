<?php
// Documentation tab view
?>
<div class="mb-12">
    <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-8 mb-8">
        <div class="flex items-center mb-6 border-b border-blue-100 pb-4">
            <svg class="w-7 h-7 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 1.343-3 3s1.343 3 3 3 3-1.343 3-3-1.343-3-3-3zm0 10c-4.418 0-8-3.582-8-8s3.582-8 8-8 8 3.582 8 8-3.582 8-8 8z" /></svg>
            <h2 class="text-2xl font-bold text-blue-800 tracking-tight">Documentation</h2>
        </div>
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
</div> 