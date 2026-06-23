<?php

namespace App\Core\Middleware;

/**
 * Rate Limit Middleware
 * Handles API rate limiting based on user quota and IP-based limits
 */
class RateLimitMiddleware
{
    private $db;
    private $maxRequestsPerMinute = 60;
    private $maxRequestsPerHour = 1000;

    public function __construct()
    {
        $this->db = \App\Models\Database::getInstance();
    }

    /**
     * Check rate limit for API requests
     */
    public function check($apiKey = null)
    {
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        
        // If API key is provided, check user quota
        if ($apiKey) {
            $apiKeyModel = new \App\Models\ApiKey();
            $validation = $apiKeyModel->validate($apiKey);
            
            if (!$validation['success']) {
                return ['allowed' => false, 'message' => 'Clé API invalide', 'retry_after' => 60];
            }
            
            $userData = $validation['api_key'];
            
            // Check user quota
            if ($userData['api_quota_used'] >= $userData['api_quota_limit']) {
                return ['allowed' => false, 'message' => 'Quota API dépassé', 'retry_after' => 86400];
            }
        }
        
        // Check IP-based rate limiting
        $ipLimit = $this->checkIpRateLimit($ipAddress);
        
        if (!$ipLimit['allowed']) {
            return $ipLimit;
        }
        
        return ['allowed' => true];
    }

    /**
     * Check IP-based rate limit
     */
    private function checkIpRateLimit($ipAddress)
    {
        $currentTime = time();
        $minuteAgo = $currentTime - 60;
        $hourAgo = $currentTime - 3600;
        
        // Create rate limit table if not exists
        $this->createRateLimitTable();
        
        // Clean old entries
        $this->cleanOldRateLimitEntries($hourAgo);
        
        // Check requests in last minute
        $sql = "SELECT COUNT(*) as count FROM rate_limits 
                WHERE ip_address = ? AND created_at > ?";
        $result = $this->db->fetchOne($sql, [$ipAddress, $minuteAgo]);
        
        if ($result['count'] >= $this->maxRequestsPerMinute) {
            return ['allowed' => false, 'message' => 'Trop de requêtes par minute', 'retry_after' => 60];
        }
        
        // Check requests in last hour
        $sql = "SELECT COUNT(*) as count FROM rate_limits 
                WHERE ip_address = ? AND created_at > ?";
        $result = $this->db->fetchOne($sql, [$ipAddress, $hourAgo]);
        
        if ($result['count'] >= $this->maxRequestsPerHour) {
            return ['allowed' => false, 'message' => 'Trop de requêtes par heure', 'retry_after' => 3600];
        }
        
        // Log this request
        $this->logRequest($ipAddress);
        
        return ['allowed' => true];
    }

    /**
     * Log a request for rate limiting
     */
    private function logRequest($ipAddress)
    {
        $sql = "INSERT INTO rate_limits (ip_address, created_at) VALUES (?, ?)";
        $this->db->query($sql, [$ipAddress, time()]);
    }

    /**
     * Clean old rate limit entries
     */
    private function cleanOldRateLimitEntries($timestamp)
    {
        $sql = "DELETE FROM rate_limits WHERE created_at < ?";
        $this->db->query($sql, [$timestamp]);
    }

    /**
     * Create rate limit table if not exists
     */
    private function createRateLimitTable()
    {
        $sql = "CREATE TABLE IF NOT EXISTS rate_limits (
            id INT AUTO_INCREMENT PRIMARY KEY,
            ip_address VARCHAR(45) NOT NULL,
            created_at INT NOT NULL,
            INDEX idx_ip_created (ip_address, created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        
        $this->db->query($sql);
    }

    /**
     * Get rate limit headers
     */
    public function getHeaders()
    {
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $currentTime = time();
        $minuteAgo = $currentTime - 60;
        
        $sql = "SELECT COUNT(*) as count FROM rate_limits 
                WHERE ip_address = ? AND created_at > ?";
        $result = $this->db->fetchOne($sql, [$ipAddress, $minuteAgo]);
        
        $remaining = max(0, $this->maxRequestsPerMinute - $result['count']);
        
        return [
            'X-RateLimit-Limit' => $this->maxRequestsPerMinute,
            'X-RateLimit-Remaining' => $remaining,
            'X-RateLimit-Reset' => $currentTime % 60
        ];
    }
}
