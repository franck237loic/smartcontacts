<?php

namespace App\Models;

use App\Models\Database;

/**
 * Search History Model
 * Handles user search history
 */
class SearchHistory
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Add search to history
     */
    public function add($userId, $phone, $result, $ipAddress = null)
    {
        $sql = "INSERT INTO search_history (user_id, phone, result, ip_address) VALUES (?, ?, ?, ?)";
        $this->db->query($sql, [$userId, $phone, json_encode($result), $ipAddress]);
        return ['success' => true];
    }

    /**
     * Get user search history
     */
    public function getUserHistory($userId, $limit = 50)
    {
        $sql = "SELECT * FROM search_history 
                WHERE user_id = ? 
                ORDER BY created_at DESC 
                LIMIT ?";
        return $this->db->fetchAll($sql, [$userId, $limit]);
    }

    /**
     * Get search statistics for user
     */
    public function getUserStatistics($userId)
    {
        $sql = "SELECT 
                    COUNT(*) as total_searches,
                    COUNT(DISTINCT phone) as unique_phones,
                    COUNT(CASE WHEN JSON_EXTRACT(resultado, '$.success') = 1 THEN 1 END) as successful_searches,
                    DATE(created_at) as search_date
                FROM search_history 
                WHERE user_id = ?
                GROUP BY DATE(created_at)
                ORDER BY search_date DESC
                LIMIT 30";
        return $this->db->fetchAll($sql, [$userId]);
    }

    /**
     * Get recent searches
     */
    public function getRecentSearches($userId, $limit = 10)
    {
        $sql = "SELECT phone, result, created_at 
                FROM search_history 
                WHERE user_id = ? 
                ORDER BY created_at DESC 
                LIMIT ?";
        return $this->db->fetchAll($sql, [$userId, $limit]);
    }

    /**
     * Delete search history
     */
    public function delete($historyId, $userId)
    {
        $sql = "DELETE FROM search_history WHERE id = ? AND user_id = ?";
        $this->db->query($sql, [$historyId, $userId]);
        return ['success' => true];
    }

    /**
     * Clear all user history
     */
    public function clearUserHistory($userId)
    {
        $sql = "DELETE FROM search_history WHERE user_id = ?";
        $this->db->query($sql, [$userId]);
        return ['success' => true];
    }
}
