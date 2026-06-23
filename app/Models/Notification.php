<?php

namespace App\Models;

use App\Models\Database;

/**
 * Notification Model
 * Handles user notifications
 */
class Notification
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Create a new notification
     */
    public function create($userId, $type, $title, $message, $link = null)
    {
        $sql = "INSERT INTO notifications (user_id, type, title, message, link) 
                VALUES (?, ?, ?, ?, ?)";
        return $this->db->query($sql, [$userId, $type, $title, $message, $link]);
    }

    /**
     * Get unread notifications for a user
     */
    public function getUnread($userId, $limit = 10)
    {
        $sql = "SELECT * FROM notifications 
                WHERE user_id = ? AND is_read = 0 
                ORDER BY created_at DESC 
                LIMIT ?";
        return $this->db->fetchAll($sql, [$userId, $limit]);
    }

    /**
     * Get all notifications for a user
     */
    public function getAll($userId, $limit = 20)
    {
        $sql = "SELECT * FROM notifications 
                WHERE user_id = ? 
                ORDER BY created_at DESC 
                LIMIT ?";
        return $this->db->fetchAll($sql, [$userId, $limit]);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($notificationId)
    {
        $sql = "UPDATE notifications SET is_read = 1 WHERE id = ?";
        return $this->db->query($sql, [$notificationId]);
    }

    /**
     * Mark all notifications as read for a user
     */
    public function markAllAsRead($userId)
    {
        $sql = "UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0";
        return $this->db->query($sql, [$userId]);
    }

    /**
     * Get unread count for a user
     */
    public function getUnreadCount($userId)
    {
        $sql = "SELECT COUNT(*) as count FROM notifications 
                WHERE user_id = ? AND is_read = 0";
        $result = $this->db->fetchOne($sql, [$userId]);
        return $result ? $result['count'] : 0;
    }

    /**
     * Delete notification
     */
    public function delete($notificationId)
    {
        $sql = "DELETE FROM notifications WHERE id = ?";
        return $this->db->query($sql, [$notificationId]);
    }

    /**
     * Delete old notifications (older than 30 days)
     */
    public function deleteOld($days = 30)
    {
        $sql = "DELETE FROM notifications 
                WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)";
        return $this->db->query($sql, [$days]);
    }
}
