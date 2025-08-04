function dismissNotice(type) {
    // Hide the notice immediately
    const noticeId = type + '-notice';
    const notice = document.getElementById(noticeId);
    if (notice) {
        notice.style.transition = 'opacity 0.3s ease';
        notice.style.opacity = '0';
        setTimeout(() => {
            notice.style.display = 'none';
        }, 300);
    }
    
    // Send AJAX request to save dismiss preference
    const formData = new FormData();
    formData.append('action', 'ctm_dismiss_notice');
    formData.append('notice_type', type);
    formData.append('nonce', ctmGeneralData.nonce);
    
    fetch(ctmGeneralData.ajaxurl, {
        method: 'POST',
        body: formData
    }).catch(error => {
        console.error('Error dismissing notice:', error);
    });
}


function testApiConnection() {
    const apiKey = document.getElementById('ctm_api_key').value;
    const apiSecret = document.getElementById('ctm_api_secret').value;
    const saveBtn = document.getElementById('save-api-btn');
    const apiTestLogs = document.getElementById('api-test-logs');
    const apiLogContent = document.getElementById('api-log-content');
    const testDuration = document.getElementById('test-duration');
    const testProgress = document.getElementById('test-progress');
    const progressBar = document.getElementById('progress-bar');
    const progressText = document.getElementById('progress-text');
    const accountSummary = document.getElementById('account-summary');
    const accountDetails = document.getElementById('account-details');
    const technicalDetails = document.getElementById('technical-details');
    const techDetailsContent = document.getElementById('tech-details-content');

    if (!apiKey || !apiSecret) {
        alert('Please enter both API Key and API Secret.');
        return false; // Prevent form submission
    }

    // Validate minimum length
    if (apiKey.length < 20 || apiSecret.length < 20) {
        alert('API Key and Secret must be at least 20 characters long.');
        return false; // Prevent form submission
    }

    // Initialize test state
    const startTime = Date.now();
    let currentStep = 0;
    const totalSteps = 5;
    
    saveBtn.disabled = true;
    saveBtn.textContent = 'Testing Connection...';
    apiTestLogs.classList.remove('hidden');
    apiLogContent.innerHTML = '';
    testDuration.classList.add('hidden');
    testProgress.classList.remove('hidden');
    accountSummary.classList.add('hidden');
    technicalDetails.classList.add('hidden');
    
    // Step 1: Validation
    updateProgress(++currentStep, totalSteps, 'Validating credentials...');
    appendLog('info', 'Starting API connection test...');
    appendLog('info', `API Key: ${apiKey.substring(0, 8)}...${apiKey.substring(apiKey.length - 4)}`);
    appendLog('info', `Target: https://api.calltrackingmetrics.com`);
    
    setTimeout(() => {
        // Step 2: Connection
        updateProgress(++currentStep, totalSteps, 'Establishing connection...');
        appendLog('info', 'Connecting to CTM API endpoint...');
        appendLog('info', 'Endpoint: /api/v1/accounts/current.json');
        
        const requestStart = Date.now();
        
        fetch(ctmGeneralData.ajaxurl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=ctm_test_api_connection&api_key=' + encodeURIComponent(apiKey) + '&api_secret=' + encodeURIComponent(apiSecret) + '&nonce=' + ctmGeneralData.testNonce
        })
        .then(response => {
            const requestTime = Date.now() - requestStart;
            
            // Step 3: Response
            updateProgress(++currentStep, totalSteps, 'Processing response...');
            appendLog('info', `Response received in ${requestTime}ms`);
            appendLog('info', `HTTP Status: ${response.status} ${response.statusText}`);
            
            return response.json();
        })
        .then(data => {
            // Step 4: Analysis
            updateProgress(++currentStep, totalSteps, 'Analyzing account data...');
            
            const totalTime = Date.now() - startTime;
            testDuration.textContent = `${totalTime}ms`;
            testDuration.classList.remove('hidden');
            
            if (data.success) {
                // Step 5: Success
                updateProgress(++currentStep, totalSteps, 'Connection successful!');
                
                appendLog('success', '✓ API Connection Successful!');
                appendLog('success', `Total test duration: ${totalTime}ms`);
                
                // Show performance metrics
                if (data.performance) {
                    displayPerformanceMetrics(data.performance, data.connection_quality);
                }
                
                // Show detailed account information
                if (data.account_info && data.account_info.account) {
                    displayAccountSummary(data.account_info.account, data.account_details?.account, data.capabilities);
                }
                
                // Show technical details
                displayTechnicalDetails(data);
                
                appendLog('success', 'All tests completed successfully!');
                
                // Update button and allow form submission
                saveBtn.textContent = 'API Credentials Saved Successfully!';
                saveBtn.classList.remove('bg-blue-600', 'hover:bg-blue-700');
                saveBtn.classList.add('bg-green-600', 'hover:bg-green-700');
                
                // Submit the form after successful test
                setTimeout(() => {
                    const form = document.querySelector('form');
                    if (form) {
                        form.submit();
                    }
                }, 2000);
                
            } else {
                // Step 5: Error
                updateProgress(++currentStep, totalSteps, 'Connection failed!');
                
                appendLog('error', '✗ Failed to connect to CTM API: ' + data.message);
                appendLog('error', `Total test duration: ${totalTime}ms`);
                
                if (data.message) appendLog('error', `Error: ${data.message}`);
                if (data.details && Array.isArray(data.details)) {
                    data.details.forEach(detail => appendLog('warning', `• ${detail}`));
                }
                
                // Show technical details for debugging
                displayTechnicalDetails(data);
                
                appendLog('warning', 'Please check your API credentials and try again.');
                
                // Reset button
                saveBtn.disabled = false;
                saveBtn.textContent = 'Save API Credentials';
                
                return false; // Prevent form submission
            }
        })
        .catch(error => {
            const totalTime = Date.now() - startTime;
            testDuration.textContent = `${totalTime}ms`;
            testDuration.classList.remove('hidden');
            
            updateProgress(totalSteps, totalSteps, 'Network error');
            progressBar.classList.remove('bg-blue-600');
            progressBar.classList.add('bg-red-600');
            
            appendLog('error', '✗ Network Error: ' + error.message);
            appendLog('error', `Total test duration: ${totalTime}ms`);
            appendLog('warning', 'Please check your internet connection and try again.');
            
            // Reset button
            saveBtn.disabled = false;
            saveBtn.textContent = 'Save API Credentials';
            
            return false; // Prevent form submission
        });
    }, 800); // Small delay to show initial progress
}

function updateProgress(step, total, message) {
    const progressBar = document.getElementById('progress-bar');
    const progressText = document.getElementById('progress-text');
    
    const percentage = Math.round((step / total) * 100);
    progressBar.style.width = percentage + '%';
    progressText.textContent = `${percentage}% - ${message}`;
}

function appendLog(type, message) {
    const apiLogContent = document.getElementById('api-log-content');
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
    
    const logEntry = document.createElement('div');
    logEntry.className = `${colorClass} ${bgClass} p-2 rounded border-l-2 border-current flex gap-2 items-start`;
    logEntry.innerHTML = `
        <span class="text-gray-400 text-xs font-mono mt-0.5">[${timestamp}]</span>
        <span class="font-bold mt-0.5">${icon}</span>
        <span class="flex-1 font-medium">${message}</span>
    `;
    
    apiLogContent.appendChild(logEntry);
    apiLogContent.scrollTop = apiLogContent.scrollHeight;
}

function displayPerformanceMetrics(performance, quality) {
    if (quality) {
        const qualityColor = quality.color === 'green' ? 'text-green-600' : 
                            quality.color === 'blue' ? 'text-blue-600' :
                            quality.color === 'yellow' ? 'text-yellow-600' : 'text-red-600';
        
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

function displayAccountSummary(account, details, capabilities) {
    const accountSummary = document.getElementById('account-summary');
    const accountDetails = document.getElementById('account-details');
    
    accountSummary.classList.remove('hidden');
    
    let summaryHTML = '';
    
    // Basic account info
    summaryHTML += `<div class="bg-white p-2 rounded border"><strong>Name:</strong> ${account.name || 'N/A'}</div>`;
    summaryHTML += `<div class="bg-white p-2 rounded border"><strong>Account ID:</strong> <code class="bg-gray-100 px-1 rounded">${account.id || 'N/A'}</code></div>`;
    
    if (account.email) {
        summaryHTML += `<div class="bg-white p-2 rounded border"><strong>Email:</strong> ${account.email}</div>`;
    }
    
    // API Capabilities
    if (capabilities) {
        const accessIcon = capabilities.account_access ? '✓' : '✗';
        const detailsIcon = capabilities.details_access ? '✓' : '✗';
        summaryHTML += `<div class="bg-white p-2 rounded border"><strong>API Access:</strong> <span class="text-green-600">${accessIcon}</span> Account, <span class="${capabilities.details_access ? 'text-green-600' : 'text-yellow-600'}">${detailsIcon}</span> Details</div>`;
        summaryHTML += `<div class="bg-white p-2 rounded border"><strong>API Version:</strong> ${capabilities.api_version}</div>`;
    }
    
    // Additional details if available
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
    
    accountDetails.innerHTML = summaryHTML;
    
    appendLog('success', `Account verified: ${account.name} (${account.id})`);
}

function displayTechnicalDetails(data) {
    const technicalDetails = document.getElementById('technical-details');
    const techDetailsContent = document.getElementById('tech-details-content');
    
    technicalDetails.classList.remove('hidden');
    
    let detailsHTML = '';
    
    // Metadata Section
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
    
    // Performance Section
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
    
    // API Endpoints Section
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
    
    // Connection Quality
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
                    <span class="text-gray-600">${Number(quality.total_time).toFixed(2)}ms total response time</span>
                </div>
            </div>
        `;
    }
    
    // Raw Response Data (collapsible)
    const sanitizedData = JSON.parse(JSON.stringify(data));
    
    // Remove sensitive information
    if (sanitizedData.account_info?.account) {
        delete sanitizedData.account_info.account.api_key;
        delete sanitizedData.account_info.account.api_secret;
    }
    
    const jsonString = JSON.stringify(sanitizedData, null, 2);
    
    detailsHTML += `
        <div class="mb-2">
            <button type="button" onclick="toggleRawData()" class="flex items-center gap-1 text-xs text-gray-600 hover:text-gray-800 transition">
                <svg id="raw-data-icon" class="w-3 h-3 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
                Raw API Response (JSON)
            </button>
            <div id="raw-data-content" class="hidden mt-2 p-2 bg-gray-50 rounded border">
                <div class="mb-1 text-gray-600 text-xs"><strong>Complete API Response:</strong> (sensitive data removed)</div>
                <pre class="whitespace-pre-wrap text-xs overflow-x-auto">${escapeHtml(jsonString)}</pre>
            </div>
        </div>
    `;
    
    techDetailsContent.innerHTML = detailsHTML;
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function clearTestLogs() {
    document.getElementById('api-log-content').innerHTML = '';
    document.getElementById('test-progress').classList.add('hidden');
    document.getElementById('account-summary').classList.add('hidden');
    document.getElementById('technical-details').classList.add('hidden');
    document.getElementById('test-duration').classList.add('hidden');
    
    // Reset progress bar
    const progressBar = document.getElementById('progress-bar');
    progressBar.style.width = '0%';
    progressBar.classList.remove('bg-red-600');
    progressBar.classList.add('bg-blue-600');
}

function toggleTechnicalDetails() {
    const techDetailsContent = document.getElementById('tech-details-content');
    const techDetailsIcon = document.getElementById('tech-details-icon');
    
    techDetailsContent.classList.toggle('hidden');
    techDetailsIcon.classList.toggle('rotate-90');
}

function toggleRawData() {
    const rawDataContent = document.getElementById('raw-data-content');
    const rawDataIcon = document.getElementById('raw-data-icon');
    
    rawDataContent.classList.toggle('hidden');
    rawDataIcon.classList.toggle('rotate-90');
}

// Ensure ctmShowToast is available
if (typeof window.ctmShowToast !== 'function') {
    window.ctmShowToast = function(message, type = 'info') {
        let container = document.getElementById('ctm-toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'ctm-toast-container';
            container.style.position = 'fixed';
            container.style.top = '1.5rem';
            container.style.right = '1.5rem';
            container.style.zIndex = '9999';
            document.body.appendChild(container);
        }
        // Remove any existing toasts after a short delay
        Array.from(container.children).forEach(child => {
            child.style.opacity = 0;
            setTimeout(() => child.remove(), 500);
        });
        // Toast color based on type
        let bg = 'bg-blue-600';
        if (type === 'success') bg = 'bg-green-600';
        if (type === 'error') bg = 'bg-red-600';
        if (type === 'warning') bg = 'bg-yellow-600';
        // Create toast element
        const toast = document.createElement('div');
        toast.className = `${bg} text-white px-4 py-2 rounded shadow mb-2 transition-opacity duration-500`;
        toast.style.opacity = 1;
        toast.textContent = message;
        container.appendChild(toast);
        // Fade out and remove after 3 seconds
        setTimeout(() => {
            toast.style.opacity = 0;
            setTimeout(() => toast.remove(), 500);
        }, 3000);
    };
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Override checkbox logic for readOnly
    const overrideCheckbox = document.getElementById('ctm_tracking_override_checkbox');
    const trackingTextarea = document.getElementById('ctm_tracking_script');
    if (overrideCheckbox && trackingTextarea) {
        overrideCheckbox.addEventListener('change', function() {
            trackingTextarea.readOnly = !this.checked;
        });
    }
    
    // Form submission handler for API credentials
    const apiForm = document.querySelector('form');
    const saveBtn = document.getElementById('save-api-btn');
    
    if (apiForm && saveBtn) {
        apiForm.addEventListener('submit', function(e) {
            e.preventDefault(); // Prevent default form submission
            
            // Run API test instead
            testApiConnection();
        });
    }
    
    // Countdown timer for API test
    let countdownSeconds = 600; // 10 minutes
    const countdownDisplay = document.getElementById('api-test-countdown');
    function updateCountdownDisplay() {
        if (countdownDisplay) {
            const mins = Math.floor(countdownSeconds / 60);
            const secs = countdownSeconds % 60;
            countdownDisplay.textContent = `${mins}:${secs.toString().padStart(2, '0')}`;
        }
    }
    updateCountdownDisplay();
}); 