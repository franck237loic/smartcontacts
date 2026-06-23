<?php

namespace App\Models;

use App\Models\Database;

/**
 * API Key Model
 * Handles API key generation and management
 */
class ApiKey
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Generate a new API key
     */
    public function generateKey()
    {
        return 'gpa_' . bin2hex(random_bytes(32));
    }

    /**
     * Create API key for user
     */
    public function create($userId, $name, $expiresAt = null)
    {
        $key = $this->generateKey();
        $keyHash = hash('sha256', $key);

        $sql = "INSERT INTO api_keys (user_id, name, key_hash, expires_at) VALUES (?, ?, ?, ?)";
        $result = $this->db->query($sql, [$userId, $name, $keyHash, $expiresAt]);

        if ($result) {
            return ['success' => true, 'key' => $key, 'key_id' => $this->db->lastInsertId()];
        }

        return ['success' => false, 'message' => 'Erreur lors de la création de la clé API'];
    }

    /**
     * Get user API keys
     */
    public function getUserKeys($userId)
    {
        $sql = "SELECT id, name, last_used_at, expires_at, is_active, created_at 
                FROM api_keys 
                WHERE user_id = ? 
                ORDER BY created_at DESC";
        return $this->db->fetchAll($sql, [$userId]);
    }

    /**
     * Validate API key
     */
    public function validate($key)
    {
        $keyHash = hash('sha256', $key);

        $sql = "SELECT ak.*, u.id as user_id, u.email, u.api_quota_limit, u.api_quota_used
                FROM api_keys ak
                JOIN users u ON ak.user_id = u.id
                WHERE ak.key_hash = ? AND ak.is_active = 1
                AND (ak.expires_at IS NULL OR ak.expires_at > NOW())";
        
        $apiKey = $this->db->fetchOne($sql, [$keyHash]);

        if (!$apiKey) {
            return ['success' => false, 'message' => 'Clé API invalide'];
        }

        // Update last used
        $this->updateLastUsed($apiKey['id']);

        return ['success' => true, 'api_key' => $apiKey];
    }

    /**
     * Update last used timestamp
     */
    private function updateLastUsed($keyId)
    {
        $sql = "UPDATE api_keys SET last_used_at = NOW() WHERE id = ?";
        $this->db->query($sql, [$keyId]);
    }

    /**
     * Delete API key
     */
    public function delete($keyId, $userId)
    {
        $sql = "DELETE FROM api_keys WHERE id = ? AND user_id = ?";
        $this->db->query($sql, [$keyId, $userId]);
        return ['success' => true];
    }

    /**
     * Deactivate API key
     */
    public function deactivate($keyId, $userId)
    {
        $sql = "UPDATE api_keys SET is_active = 0 WHERE id = ? AND user_id = ?";
        $this->db->query($sql, [$keyId, $userId]);
        return ['success' => true];
    }

    /**
     * Reactivate API key
     */
    public function reactivate($keyId, $userId)
    {
        $sql = "UPDATE api_keys SET is_active = 1 WHERE id = ? AND user_id = ?";
        $this->db->query($sql, [$keyId, $userId]);
        return ['success' => true];
    }
}
