<?php

namespace App\Models;

use App\Models\Database;

/**
 * Audit Log Model
 * Handles audit logging for tracking user actions
 */
class AuditLog
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Log an action
     */
    public function log($userId, $action, $entityType = null, $entityId = null, $oldValues = null, $newValues = null)
    {
        $sql = "INSERT INTO audit_logs (user_id, action, entity_type, entity_id, old_values, new_values, ip_address, user_agent) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
        
        return $this->db->query($sql, [
            $userId,
            $action,
            $entityType,
            $entityId,
            $oldValues ? json_encode($oldValues) : null,
            $newValues ? json_encode($newValues) : null,
            $ipAddress,
            $userAgent
        ]);
    }

    /**
     * Get logs for a user
     */
    public function getUserLogs($userId, $limit = 50)
    {
        $sql = "SELECT * FROM audit_logs 
                WHERE user_id = ? 
                ORDER BY created_at DESC 
                LIMIT ?";
        return $this->db->fetchAll($sql, [$userId, $limit]);
    }

    /**
     * Get logs by action
     */
    public function getLogsByAction($action, $limit = 50)
    {
        $sql = "SELECT al.*, u.email, u.first_name, u.last_name 
                FROM audit_logs al
                LEFT JOIN users u ON al.user_id = u.id
                WHERE al.action = ? 
                ORDER BY al.created_at DESC 
                LIMIT ?";
        return $this->db->fetchAll($sql, [$action, $limit]);
    }

    /**
     * Get logs by entity
     */
    public function getLogsByEntity($entityType, $entityId, $limit = 50)
    {
        $sql = "SELECT al.*, u.email, u.first_name, u.last_name 
                FROM audit_logs al
                LEFT JOIN users u ON al.user_id = u.id
                WHERE al.entity_type = ? AND al.entity_id = ? 
                ORDER BY al.created_at DESC 
                LIMIT ?";
        return $this->db->fetchAll($sql, [$entityType, $entityId, $limit]);
    }

    /**
     * Get recent logs
     */
    public function getRecentLogs($limit = 50)
    {
        $sql = "SELECT al.*, u.email, u.first_name, u.last_name 
                FROM audit_logs al
                LEFT JOIN users u ON al.user_id = u.id
                ORDER BY al.created_at DESC 
                LIMIT ?";
        return $this->db->fetchAll($sql, [$limit]);
    }

    /**
     * Delete old logs (older than 90 days)
     */
    public function deleteOld($days = 90)
    {
        $sql = "DELETE FROM audit_logs 
                WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)";
        return $this->db->query($sql, [$days]);
    }
}
