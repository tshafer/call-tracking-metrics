jQuery(document).ready(function($) {
    // Only initialize if we're on the API tab
    if ($('#ctm-test-logs').length === 0) {
        return; // Not on API tab, exit early
    }
    
    let autoTestEnabled = true;
    let countdownTimer = null;
    let countdownValue = 10;
    let isTestInProgress = false;
    
    // Update status indicators
    function updateStatus(success, message = '') {
        const indicator = $('#ctm-status-indicator');
        const badge = $('#ctm-status-badge');
        const lastTest = $('#ctm-last-test');
        
        if (success) {
            indicator.removeClass('bg-red-500 bg-yellow-500').addClass('bg-green-500');
            badge.removeClass('bg-red-100 text-red-800 bg-yellow-100 text-yellow-800').addClass('bg-green-100 text-green-800').text('Connected');
        } else {
            indicator.removeClass('bg-green-500 bg-yellow-500').addClass('bg-red-500');
            badge.removeClass('bg-green-100 text-green-800 bg-yellow-100 text-yellow-800').addClass('bg-red-100 text-red-800').text('Failed');
        }
        
        lastTest.text('Last test: ' + new Date().toLocaleTimeString());
    }
    
    // Start countdown
    function startCountdown() {
        if (!autoTestEnabled || isTestInProgress) return;
        countdownValue = 600; // 10 minutes in seconds
        const countdown = $('#ctm-countdown');
        clearInterval(countdownTimer);
        countdownTimer = setInterval(() => {
            const mins = Math.floor(countdownValue / 60);
            const secs = countdownValue % 60;
            countdown.text(`Next test in ${mins}:${secs.toString().padStart(2, '0')}`);
            countdownValue--;
            if (countdownValue < 0) {
                clearInterval(countdownTimer);
                countdown.text('Testing...');
                performApiTest(true); // Auto test
            }
        }, 1000);
    }
    
    // Perform API test
    function performApiTest(isAutoTest = false) {
        if (isTestInProgress) return;
        
        isTestInProgress = true;
        const button = $('#ctm-test-api-btn');
        const logs = $('#ctm-test-logs');
        const progressContainer = $('#ctm-progress-container');
        const progressBar = $('#ctm-progress-bar');
        const progressPercent = $('#ctm-progress-percent');
        const countdown = $('#ctm-countdown');
        
        // Update button state (only if manual test)
        if (!isAutoTest) {
            button.prop('disabled', true).html('<svg class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Testing...');
        }
        
        // Show testing status
        countdown.text('Testing...');
        
        // Add test log if not auto test
        if (!isAutoTest) {
            appendLog('info', 'Starting API connection test...');
            progressContainer.removeClass('hidden');
        }
        
        // AJAX call to test API
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'ctm_test_api_connection',
                api_key: ctmApiData.api_key,
                api_secret: ctmApiData.api_secret,
                nonce: ctmApiData.nonce
            },
            success: function(response) {
                if (response.success) {
                    updateStatus(true);
                    
                    // Show detailed logs only for manual tests
                    if (!isAutoTest) {
                        progressBar.css('width', '100%');
                        progressPercent.text('100%');
                        
                        let logHtml = '';
                        if (response.data.logs) {
                            response.data.logs.forEach(log => {
                                let bgClass = 'bg-gray-50';
                                let textClass = 'text-gray-700';
                                let icon = '•';
                                
                                if (log.includes('✓') || log.includes('Success')) {
                                    bgClass = 'bg-green-50 border-l-4 border-green-500';
                                    textClass = 'text-green-700';
                                    icon = '✓';
                                } else if (log.includes('✗') || log.includes('Error')) {
                                    bgClass = 'bg-red-50 border-l-4 border-red-500';
                                    textClass = 'text-red-700';
                                    icon = '✗';
                                } else if (log.includes('→') || log.includes('Sending')) {
                                    bgClass = 'bg-blue-50 border-l-4 border-blue-500';
                                    textClass = 'text-blue-700';
                                    icon = '→';
                                }
                                
                                logHtml += `<div class="mb-2 p-2 rounded ${bgClass}"><span class="${textClass}">${icon} ${log}</span></div>`;
                            });
                        }
                        logs.html(logHtml);
                        
                        // Show duration if available
                        if (response.data.duration) {
                            logs.append(`<div class="mt-3 p-2 bg-gray-100 rounded text-center"><span class="text-gray-600">Total test duration: ${response.data.duration}ms</span></div>`);
                        }
                    } else {
                        // For auto tests, just add a simple success log
                        const timestamp = new Date().toLocaleTimeString();
                        appendLog('success', `✓ [${timestamp}] Auto-test successful`);
                    }
                    displayAccountSummary(response.data.account_info.account, response.data.account_details?.account, response.data.capabilities);
                    displayTechnicalDetails(response.data);
                    displayPerformanceMetrics(response.data.performance, response.data.connection_quality);
                } else {
                    updateStatus(false, response.data || 'Test failed');
                    
                    if (!isAutoTest) {
                        appendLog('error', `❌ ${response.data || 'Test failed'}`);
                    } else {
                        const timestamp = new Date().toLocaleTimeString();
                        appendLog('error', `✗ [${timestamp}] Auto-test failed`);
                    }
                }
            },
            error: function() {
                updateStatus(false, 'AJAX request failed');
                
                if (!isAutoTest) {
                    appendLog('error', '❌ AJAX request failed');
                } else {
                    const timestamp = new Date().toLocaleTimeString();
                    appendLog('error', `✗ [${timestamp}] Auto-test failed`);
                }
            },
            complete: function() {
                isTestInProgress = false;
                
                if (!isAutoTest) {
                    button.prop('disabled', false).html('<svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>Test Now');
                }
                
                // Restart countdown
                setTimeout(() => {
                    startCountdown();
                }, 1000);
            }
        });
    }
    
    // Event handlers
    $('#ctm-test-api-btn').on('click', function() {
        performApiTest(false);
    });
    
    $('#ctm-toggle-auto-test').on('click', function() {
        const button = $(this);
        autoTestEnabled = !autoTestEnabled;
        
        if (autoTestEnabled) {
            button.removeClass('bg-gray-600 hover:bg-gray-700').addClass('bg-green-600 hover:bg-green-700').html('<svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>Auto-Test: ON');
            startCountdown();
        } else {
            button.removeClass('bg-green-600 hover:bg-green-700').addClass('bg-gray-600 hover:bg-gray-700').html('<svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>Auto-Test: OFF');
            clearInterval(countdownTimer);
            $('#ctm-countdown').text('Auto-test disabled');
        }
    });
    
    // Clear Logs Button
    $('#ctm-clear-logs-btn').on('click', function() {
        clearTestLogs();
    });
    
    // Modal logic for Change API Keys
    $('#ctm-change-api-btn').on('click', function() {
        $('#ctm-change-api-modal').removeClass('hidden');
    });
    $('#ctm-close-change-api, #ctm-cancel-change-api').on('click', function() {
        $('#ctm-change-api-modal').addClass('hidden');
    });
    
    // AJAX submit for Change API Keys
    $('#ctm-change-api-form').on('submit', function(e) {
        e.preventDefault();
        var apiKey = $('#ctm_new_api_key').val();
        var apiSecret = $('#ctm_new_api_secret').val();
        var nonce = ctmApiData.change_api_nonce;
        var $modal = $('#ctm-change-api-modal');
        var $saveBtn = $(this).find('button[type=submit]');
        $saveBtn.prop('disabled', true).text('Saving...');
        $.post(ajaxurl, {
            action: 'ctm_change_api_keys',
            api_key: apiKey,
            api_secret: apiSecret,
            nonce: nonce
        }, function(resp) {
            $saveBtn.prop('disabled', false).text('Save');
            if (resp.success) {
                $modal.addClass('hidden');
                alert('API keys updated successfully.');
                location.reload();
            } else {
                alert(resp.data && resp.data.message ? resp.data.message : 'Failed to update API keys.');
            }
        });
    });
    
    // Modal logic for Disable API
    $('#ctm-disable-api-btn').on('click', function() {
        $('#ctm-disable-api-modal').removeClass('hidden');
    });
    $('#ctm-close-disable-api, #ctm-cancel-disable-api').on('click', function() {
        $('#ctm-disable-api-modal').addClass('hidden');
    });
    
    // AJAX for Disable API
    $('#ctm-confirm-disable-api').on('click', function() {
        var nonce = ctmApiData.disable_api_nonce;
        var $modal = $('#ctm-disable-api-modal');
        var $btn = $(this);
        $btn.prop('disabled', true).text('Disabling...');
        $.post(ajaxurl, {
            action: 'ctm_disable_api',
            nonce: nonce
        }, function(resp) {
            $btn.prop('disabled', false).text('Disable API');
            if (resp.success) {
                $modal.addClass('hidden');
                alert('API credentials cleared.');
                location.reload();
            } else {
                alert(resp.data && resp.data.message ? resp.data.message : 'Failed to disable API.');
            }
        });
    });
    
    // Initialize
    clearTestLogs();
    
    // Add initial connection status
    if (ctmApiData.initial_connected) {
        appendLog('success', `✓ [${new Date().toLocaleTimeString()}] API connection verified`);
    }
    
    // Start auto-testing
    startCountdown(); // Start countdown immediately on page load
    
    // Helper functions
    function clearTestLogs() {
        $('#ctm-test-logs').html('<div class="text-gray-500 italic">Auto-testing every 10 minutes. Click "Test Now" for manual test...</div>');
        $('#ctm-progress-container').addClass('hidden');
        $('#ctm-progress-bar').css('width', '0%');
        $('#ctm-progress-percent').text('0%');
    }
    
    function appendLog(type, message) {
        const logs = $('#ctm-test-logs');
        const timestamp = new Date().toLocaleTimeString();
        let colorClass = 'text-gray-600';
        let bgClass = 'bg-gray-50';
        let icon = '•';
        switch(type) {
            case 'success':
                colorClass = 'text-green-700';
                bgClass = 'bg-green-50';
                icon = '✓';
                break;
            case 'error':
                colorClass = 'text-red-700';
                bgClass = 'bg-red-50';
                icon = '✗';
                break;
            case 'warning':
                colorClass = 'text-yellow-700';
                bgClass = 'bg-yellow-50';
                icon = '⚠';
                break;
            case 'info':
            default:
                colorClass = 'text-blue-700';
                bgClass = 'bg-blue-50';
                icon = 'ⓘ';
                break;
        }
        const logEntry = $(`
            <div class="${colorClass} ${bgClass} p-2 rounded border-l-2 border-current flex gap-2 items-start">
                <span class="text-gray-400 text-xs font-mono mt-0.5">[${timestamp}]</span>
                <span class="font-bold mt-0.5">${icon}</span>
                <span class="flex-1 font-medium">${message}</span>
            </div>
        `);
        logs.append(logEntry);
        logs.scrollTop(logs[0].scrollHeight);
    }
    
    function displayAccountSummary(account, details, capabilities) {
        const accountSummary = $('#ctm-account-summary');
        const accountDetails = $('#ctm-account-details');
        accountSummary.removeClass('hidden');
        let summaryHTML = '';
        summaryHTML += `<div class="bg-white p-2 rounded border"><strong>Name:</strong> ${account.name || 'N/A'}</div>`;
        summaryHTML += `<div class="bg-white p-2 rounded border"><strong>Account ID:</strong> <code class="bg-gray-100 px-1 rounded">${account.id || 'N/A'}</code></div>`;
        if (account.email) {
            summaryHTML += `<div class="bg-white p-2 rounded border"><strong>Email:</strong> ${account.email}</div>`;
        }
        if (capabilities) {
            const accessIcon = capabilities.account_access ? '✓' : '✗';
            const detailsIcon = capabilities.details_access ? '✓' : '✗';
            summaryHTML += `<div class="bg-white p-2 rounded border"><strong>API Access:</strong> <span class="text-green-600">${accessIcon}</span> Account, <span class="${capabilities.details_access ? 'text-green-600' : 'text-yellow-600'}">${detailsIcon}</span> Details</div>`;
            summaryHTML += `<div class="bg-white p-2 rounded border"><strong>API Version:</strong> ${capabilities.api_version}</div>`;
        }
        if (details) {
            if (details.status) {
                const statusColor = details.status === 'active' ? 'text-green-600' : 'text-yellow-600';
                summaryHTML += `<div class="bg-white p-2 rounded border"><strong>Status:</strong> <span class="${statusColor} font-semibold">${details.status}</span></div>`;
            }
            if (details.timezone) {
                summaryHTML += `<div class="bg-white p-2 rounded border"><strong>Timezone:</strong> ${details.timezone}</div>`;
            }
            if (details.created_at) {
                const date = new Date(details.created_at).toLocaleDateString();
                summaryHTML += `<div class="bg-white p-2 rounded border"><strong>Created:</strong> ${date}</div>`;
            }
            if (details.phone) {
                summaryHTML += `<div class="bg-white p-2 rounded border"><strong>Phone:</strong> ${details.phone}</div>`;
            }
            if (details.website) {
                summaryHTML += `<div class="bg-white p-2 rounded border"><strong>Website:</strong> <a href="${details.website}" target="_blank" class="text-blue-600 underline">${details.website}</a></div>`;
            }
            if (details.plan || details.subscription) {
                summaryHTML += `<div class="bg-white p-2 rounded border"><strong>Plan:</strong> ${details.plan || details.subscription || 'Not specified'}</div>`;
            }
        }
        accountDetails.html(summaryHTML);
        appendLog('success', `Account verified: ${account.name} (${account.id})`);
    }
    
    function displayTechnicalDetails(data) {
        const technicalDetails = $('#ctm-technical-details');
        const techDetailsContent = $('#ctm-tech-details-content');
        technicalDetails.removeClass('hidden');
        let detailsHTML = '';
        if (data.metadata) {
            detailsHTML += `
                <div class="mb-4">
                    <h6 class="font-semibold text-gray-700 mb-2 flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Request Metadata
                    </h6>
                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <div><strong>Request ID:</strong> <code class="bg-gray-200 px-1 rounded">${data.metadata.request_id?.substring(0, 8) || 'N/A'}</code></div>
                        <div><strong>Timestamp:</strong> ${data.metadata.timestamp || 'N/A'}</div>
                        <div><strong>WordPress:</strong> ${data.metadata.wordpress_version || 'N/A'}</div>
                        <div><strong>PHP:</strong> ${data.metadata.php_version || 'N/A'}</div>
                        <div><strong>Plugin:</strong> ${data.metadata.plugin_version || 'N/A'}</div>
                        <div><strong>Auth Method:</strong> ${data.metadata.auth_method || 'N/A'}</div>
                    </div>
                </div>
            `;
        }
        if (data.performance) {
            detailsHTML += `
                <div class="mb-4">
                    <h6 class="font-semibold text-gray-700 mb-2 flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                        Performance Metrics
                    </h6>
                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <div><strong>Total Time:</strong> ${data.performance.total_execution_time}ms</div>
                        <div><strong>API Response:</strong> ${data.performance.api_response_time}ms</div>
                        <div><strong>Details Response:</strong> ${data.performance.details_response_time || 'N/A'}ms</div>
                        <div><strong>Network Overhead:</strong> ${data.performance.network_overhead?.toFixed(1) || 'N/A'}ms</div>
                    </div>
                </div>
            `;
        }
        if (data.metadata) {
            detailsHTML += `
                <div class="mb-4">
                    <h6 class="font-semibold text-gray-700 mb-2 flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                        </svg>
                        API Endpoints
                    </h6>
                    <div class="space-y-1 text-xs">
                        <div><strong>Base URL:</strong> <code class="bg-gray-200 px-1 rounded">${data.metadata.api_endpoint || 'N/A'}</code></div>
                        <div><strong>Account:</strong> <code class="bg-gray-200 px-1 rounded">${data.metadata.account_endpoint || 'N/A'}</code></div>
                        ${data.metadata.details_endpoint ? `<div><strong>Details:</strong> <code class="bg-gray-200 px-1 rounded">${data.metadata.details_endpoint}</code></div>` : ''}
                    </div>
                </div>
            `;
        }
        if (data.connection_quality) {
            const quality = data.connection_quality;
            const qualityBadgeColor = quality.color === 'green' ? 'bg-green-100 text-green-800' :
                                      quality.color === 'blue' ? 'bg-blue-100 text-blue-800' :
                                      quality.color === 'yellow' ? 'bg-yellow-100 text-yellow-800' : 
                                      'bg-red-100 text-red-800';
            detailsHTML += `
                <div class="mb-4">
                    <h6 class="font-semibold text-gray-700 mb-2 flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        Connection Quality
                    </h6>
                    <div class="flex items-center gap-2 text-xs">
                        <span class="px-2 py-1 rounded ${qualityBadgeColor} font-semibold">${quality.rating.toUpperCase()}</span>
                        <span>${quality.total_time}ms total response time</span>
                    </div>
                </div>
            `;
        }
        // Raw Response Data (collapsible)
        const sanitizedData = JSON.parse(JSON.stringify(data));
        if (sanitizedData.account_info?.account) {
            delete sanitizedData.account_info.account.api_key;
            delete sanitizedData.account_info.account.api_secret;
        }
        const jsonString = JSON.stringify(sanitizedData, null, 2);
        detailsHTML += `
            <div class="mb-2">
                <button type="button" onclick="jQuery('#ctm-raw-data-content').toggleClass('hidden'); jQuery('#ctm-raw-data-icon').toggleClass('rotate-90');" class="flex items-center gap-1 text-xs text-gray-600 hover:text-gray-800 transition">
                    <svg id="ctm-raw-data-icon" class="w-3 h-3 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                    Raw API Response (JSON)
                </button>
                <div id="ctm-raw-data-content" class="hidden mt-2 p-2 bg-gray-50 rounded border">
                    <div class="mb-1 text-gray-600 text-xs"><strong>Complete API Response:</strong> (sensitive data removed)</div>
                    <pre class="whitespace-pre-wrap text-xs overflow-x-auto">${escapeHtml(jsonString)}</pre>
                </div>
            </div>
        `;
        techDetailsContent.html(detailsHTML);
    }
    
    function escapeHtml(text) {
        return $('<div/>').text(text).html();
    }
    
    function displayPerformanceMetrics(performance, quality) {
        if (quality) {
            appendLog('info', `${quality.description}`);
            appendLog('info', `API Response: ${performance.api_response_time}ms`);
            if (performance.details_response_time) {
                appendLog('info', `Details Response: ${performance.details_response_time}ms`);
            }
            if (performance.network_overhead > 0) {
                appendLog('info', `Network Overhead: ${performance.network_overhead.toFixed(1)}ms`);
            }
        }
    }
}); 