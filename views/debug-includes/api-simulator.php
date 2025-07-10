<?php
/**
 * API Request Simulator Component
 * Tests API endpoints with real-time responses
 */
?>

<div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6">
    <h3 class="text-xl font-semibold text-gray-800 mb-4 flex items-center">
        <svg class="w-6 h-6 text-purple-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
        </svg>
        API Request Simulator
    </h3>
    
    <div class="space-y-4">
        <!-- Endpoint Selection -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="api-endpoint" class="block text-sm font-medium text-gray-700 mb-2">API Endpoint</label>
                <select id="api-endpoint" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500">
                    <option value="/api/v1/accounts/">Account Information</option>
                    <option value="/api/v1/forms">Forms List</option>
                    <option value="/api/v1/tracking_numbers">Tracking Numbers</option>
                    <option value="/api/v1/calls">Recent Calls</option>
                    <option value="/api/v1/sources">Traffic Sources</option>
                    <option value="/api/v1/webhooks">Webhook Configuration</option>
                </select>
            </div>
            
            <div>
                <label for="api-method" class="block text-sm font-medium text-gray-700 mb-2">HTTP Method</label>
                <select id="api-method" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500">
                    <option value="GET">GET</option>
                    <option value="POST">POST</option>
                    <option value="PUT">PUT</option>
                    <option value="DELETE">DELETE</option>
                </select>
            </div>
        </div>

        <!-- Custom Parameters -->
        <div>
            <label for="api-parameters" class="block text-sm font-medium text-gray-700 mb-2">
                Custom Parameters (JSON format)
                <span class="text-xs text-gray-500 ml-1">- Optional</span>
            </label>
            <textarea id="api-parameters" rows="3" placeholder='{"limit": 10, "offset": 0}' class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500 text-sm font-mono"></textarea>
        </div>

        <!-- Headers -->
        <div>
            <label for="api-headers" class="block text-sm font-medium text-gray-700 mb-2">
                Custom Headers (JSON format)
                <span class="text-xs text-gray-500 ml-1">- Optional</span>
            </label>
            <textarea id="api-headers" rows="2" placeholder='{"X-Custom-Header": "value"}' class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-purple-500 focus:border-purple-500 text-sm font-mono"></textarea>
        </div>

        <!-- Test Options -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="flex items-center">
                <input type="checkbox" id="include-auth" checked class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300 rounded">
                <label for="include-auth" class="ml-2 block text-sm text-gray-900">Include Authentication</label>
            </div>
            <div class="flex items-center">
                <input type="checkbox" id="verbose-output" class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300 rounded">
                <label for="verbose-output" class="ml-2 block text-sm text-gray-900">Verbose Output</label>
            </div>
            <div class="flex items-center">
                <input type="checkbox" id="log-request" checked class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300 rounded">
                <label for="log-request" class="ml-2 block text-sm text-gray-900">Log Request</label>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex gap-3">
            <button onclick="simulateApiRequest()" id="simulate-btn" class="bg-purple-600 hover:bg-purple-700 text-white font-medium px-6 py-2 rounded-lg transition duration-200 flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                </svg>
                Send Test Request
            </button>
            <button onclick="clearApiResponse()" class="bg-gray-600 hover:bg-gray-700 text-white font-medium px-4 py-2 rounded-lg transition duration-200">
                Clear Response
            </button>
            <button onclick="saveApiTest()" class="bg-green-600 hover:bg-green-700 text-white font-medium px-4 py-2 rounded-lg transition duration-200">
                Save Test
            </button>
        </div>

        <!-- Quick Test Templates -->
        <div class="border-t border-gray-200 pt-4">
            <h4 class="text-sm font-medium text-gray-700 mb-3">Quick Test Templates</h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
                <button onclick="loadTestTemplate('account_info')" class="text-left p-2 bg-blue-50 hover:bg-blue-100 rounded border text-sm transition duration-200">
                    <div class="font-medium text-blue-800">Account Info</div>
                    <div class="text-xs text-blue-600">Basic account details</div>
                </button>
                <button onclick="loadTestTemplate('forms_list')" class="text-left p-2 bg-green-50 hover:bg-green-100 rounded border text-sm transition duration-200">
                    <div class="font-medium text-green-800">Forms List</div>
                    <div class="text-xs text-green-600">All available forms</div>
                </button>
                <button onclick="loadTestTemplate('recent_calls')" class="text-left p-2 bg-orange-50 hover:bg-orange-100 rounded border text-sm transition duration-200">
                    <div class="font-medium text-orange-800">Recent Calls</div>
                    <div class="text-xs text-orange-600">Last 10 calls</div>
                </button>
            </div>
        </div>

        <!-- Response Display -->
        <div id="api-response" class="hidden">
            <div class="border-t border-gray-200 pt-4">
                <div class="flex items-center justify-between mb-3">
                    <h4 class="text-sm font-medium text-gray-700">API Response</h4>
                    <div class="flex gap-2">
                        <button onclick="copyApiResponse()" id="copy-response-btn" class="text-xs bg-blue-100 hover:bg-blue-200 text-blue-800 px-2 py-1 rounded">
                            Copy
                        </button>
                        <button onclick="formatApiResponse()" class="text-xs bg-green-100 hover:bg-green-200 text-green-800 px-2 py-1 rounded">
                            Format
                        </button>
                    </div>
                </div>
                
                <!-- Response Metadata -->
                <div id="response-metadata" class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4 text-xs">
                    <div class="bg-gray-50 p-2 rounded">
                        <div class="text-gray-600">Status Code</div>
                        <div id="response-status" class="font-medium">-</div>
                    </div>
                    <div class="bg-gray-50 p-2 rounded">
                        <div class="text-gray-600">Response Time</div>
                        <div id="response-time" class="font-medium">-</div>
                    </div>
                    <div class="bg-gray-50 p-2 rounded">
                        <div class="text-gray-600">Content Type</div>
                        <div id="response-content-type" class="font-medium">-</div>
                    </div>
                    <div class="bg-gray-50 p-2 rounded">
                        <div class="text-gray-600">Data Size</div>
                        <div id="response-size" class="font-medium">-</div>
                    </div>
                </div>

                <!-- Response Content -->
                <div class="bg-gray-900 rounded-lg p-4 overflow-x-auto">
                    <pre id="api-response-content" class="text-green-400 text-sm whitespace-pre-wrap font-mono"></pre>
                </div>
            </div>
        </div>
    </div>
</div>

 