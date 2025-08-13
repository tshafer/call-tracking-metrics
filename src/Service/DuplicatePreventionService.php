<?php

declare(strict_types=1);

namespace CTM\Service;

/**
 * Duplicate Form Submission Prevention Service
 * 
 * Prevents duplicate form submissions by using WordPress transients
 * with keys based on CTM session ID and form ID.
 * 
 * @since 2.0.0
 */
class DuplicatePreventionService
{
    /**
     * Default expiration time for duplicate prevention (in seconds)
     * 
     * @since 2.0.0
     * @var int
     */
    private const DEFAULT_EXPIRATION = 60; // 1 minute

    /**
     * Check if a form submission is a duplicate
     * 
     * @since 2.0.0
     * @param string $formId The form ID (CF7 or GF)
     * @param string $formType The form type ('cf7' or 'gf')
     * @param int $expirationSeconds How long to prevent duplicates (default: 60 seconds)
     * @return bool True if this is a duplicate submission
     */
    public function isDuplicateSubmission(string $formId, string $formType, int $expirationSeconds = self::DEFAULT_EXPIRATION): bool
    {
        $ctmSessionId = $this->getCTMSessionId();
        
        if (empty($ctmSessionId)) {
            // If we can't get a CTM session ID, fall back to IP-based prevention
            return $this->isDuplicateByIP($formId, $formType, $expirationSeconds);
        }
        
        $transientKey = $this->generateTransientKey($ctmSessionId, $formId, $formType);
        $existingSubmission = get_transient($transientKey);
        
        if ($existingSubmission !== false) {
            return true; // This is a duplicate
        }
        
        // Set the transient to prevent future duplicates
        set_transient($transientKey, time(), $expirationSeconds);
        
        return false; // Not a duplicate
    }
    
    /**
     * Get the CTM session ID from the current page
     * 
     * @since 2.0.0
     * @return string|null The CTM session ID or null if not available
     */
    private function getCTMSessionId(): ?string
    {
        // Try to get from cookies first (most reliable)
        if (isset($_COOKIE['ctm_session_id'])) {
            return sanitize_text_field($_COOKIE['ctm_session_id']);
        }
        
        // Try to get from JavaScript variable if available
        if (isset($_POST['ctm_session_id'])) {
            return sanitize_text_field($_POST['ctm_session_id']);
        }
        
        // Try to get from request headers
        if (isset($_SERVER['HTTP_X_CTM_SESSION_ID'])) {
            return sanitize_text_field($_SERVER['HTTP_X_CTM_SESSION_ID']);
        }
        
        return null;
    }
    
    /**
     * Fallback duplicate prevention using IP address
     * 
     * @since 2.0.0
     * @param string $formId The form ID
     * @param string $formType The form type
     * @param int $expirationSeconds Expiration time
     * @return bool True if duplicate by IP
     */
    private function isDuplicateByIP(string $formId, string $formType, int $expirationSeconds): bool
    {
        $ipAddress = $this->getClientIP();
        $transientKey = $this->generateIPTransientKey($ipAddress, $formId, $formType);
        
        $existingSubmission = get_transient($transientKey);
        
        if ($existingSubmission !== false) {
            return true; // This is a duplicate
        }
        
        // Set the transient to prevent future duplicates
        set_transient($transientKey, time(), $expirationSeconds);
        
        return false; // Not a duplicate
    }
    
    /**
     * Generate a transient key for CTM session-based duplicate prevention
     * 
     * @since 2.0.0
     * @param string $ctmSessionId The CTM session ID
     * @param string $formId The form ID
     * @param string $formType The form type
     * @return string The transient key
     */
    private function generateTransientKey(string $ctmSessionId, string $formId, string $formType): string
    {
        return 'ctm_duplicate_' . md5($ctmSessionId . '_' . $formId . '_' . $formType);
    }
    
    /**
     * Generate a transient key for IP-based duplicate prevention
     * 
     * @since 2.0.0
     * @param string $ipAddress The client IP address
     * @param string $formId The form ID
     * @param string $formType The form type
     * @return string The transient key
     */
    private function generateIPTransientKey(string $ipAddress, string $formId, string $formType): string
    {
        return 'ctm_duplicate_ip_' . md5($ipAddress . '_' . $formId . '_' . $formType);
    }
    
    /**
     * Get the client IP address
     * 
     * @since 2.0.0
     * @return string The client IP address
     */
    private function getClientIP(): string
    {
        $ipKeys = [
            'HTTP_CF_CONNECTING_IP', // Cloudflare
            'HTTP_CLIENT_IP',        // Client IP
            'HTTP_X_FORWARDED_FOR',  // X-Forwarded-For
            'HTTP_X_FORWARDED',      // X-Forwarded
            'HTTP_FORWARDED_FOR',    // Forwarded-For
            'HTTP_FORWARDED',        // Forwarded
            'REMOTE_ADDR'            // Remote address
        ];
        
        foreach ($ipKeys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = $_SERVER[$key];
                
                // Handle X-Forwarded-For which can contain multiple IPs
                if ($key === 'HTTP_X_FORWARDED_FOR') {
                    $ips = explode(',', $ip);
                    $ip = trim($ips[0]);
                }
                
                // Validate IP address
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }
    
    /**
     * Clear duplicate prevention for a specific form submission
     * 
     * @since 2.0.0
     * @param string $formId The form ID
     * @param string $formType The form type
     * @return void
     */
    public function clearDuplicatePrevention(string $formId, string $formType): void
    {
        $ctmSessionId = $this->getCTMSessionId();
        
        if (!empty($ctmSessionId)) {
            $transientKey = $this->generateTransientKey($ctmSessionId, $formId, $formType);
            delete_transient($transientKey);
        }
        
        // Also clear IP-based prevention
        $ipAddress = $this->getClientIP();
        $ipTransientKey = $this->generateIPTransientKey($ipAddress, $formId, $formType);
        delete_transient($ipTransientKey);
    }
    
    /**
     * Get the current duplicate prevention settings
     * 
     * @since 2.0.0
     * @return array The current settings
     */
    public function getSettings(): array
    {
        return [
            'enabled' => (bool) get_option('ctm_duplicate_prevention_enabled', true),
            'expiration_seconds' => (int) get_option('ctm_duplicate_prevention_expiration', self::DEFAULT_EXPIRATION),
            'use_ctm_session' => (bool) get_option('ctm_duplicate_prevention_use_session', true),
            'fallback_to_ip' => (bool) get_option('ctm_duplicate_prevention_fallback_ip', true)
        ];
    }
    
    /**
     * Check if duplicate prevention is enabled
     * 
     * @since 2.0.0
     * @return bool True if enabled
     */
    public function isEnabled(): bool
    {
        $settings = $this->getSettings();
        return $settings['enabled'];
    }
}
