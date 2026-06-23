<?php

namespace App\Controllers;

use App\Models\Notification;
use App\Core\Controller;

/**
 * Notification Controller
 * Handles user notifications
 */
class NotificationController extends Controller
{
    private $notificationModel;

    public function __construct()
    {
        parent::__construct();
        $this->notificationModel = new Notification();
    }

    /**
     * Get unread notifications
     */
    public function getUnread()
    {
        if (!isset($_SESSION['user_id'])) {
            return $this->json(['success' => false, 'message' => 'Non authentifié'], 401);
        }

        $notifications = $this->notificationModel->getUnread($_SESSION['user_id']);
        $unreadCount = $this->notificationModel->getUnreadCount($_SESSION['user_id']);

        return $this->json([
            'success' => true,
            'notifications' => $notifications,
            'unread_count' => $unreadCount
        ]);
    }

    /**
     * Get all notifications
     */
    public function getAll()
    {
        if (!isset($_SESSION['user_id'])) {
            return $this->json(['success' => false, 'message' => 'Non authentifié'], 401);
        }

        $notifications = $this->notificationModel->getAll($_SESSION['user_id']);
        $unreadCount = $this->notificationModel->getUnreadCount($_SESSION['user_id']);

        return $this->json([
            'success' => true,
            'notifications' => $notifications,
            'unread_count' => $unreadCount
        ]);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead()
    {
        if (!isset($_SESSION['user_id'])) {
            return $this->json(['success' => false, 'message' => 'Non authentifié'], 401);
        }

        $notificationId = $this->post('id');

        if (!$notificationId) {
            return $this->json(['success' => false, 'message' => 'ID de notification requis'], 400);
        }

        $this->notificationModel->markAsRead($notificationId);

        return $this->json(['success' => true]);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        if (!isset($_SESSION['user_id'])) {
            return $this->json(['success' => false, 'message' => 'Non authentifié'], 401);
        }

        $this->notificationModel->markAllAsRead($_SESSION['user_id']);

        return $this->json(['success' => true]);
    }

    /**
     * Delete notification
     */
    public function delete()
    {
        if (!isset($_SESSION['user_id'])) {
            return $this->json(['success' => false, 'message' => 'Non authentifié'], 401);
        }

        $notificationId = $this->post('id');

        if (!$notificationId) {
            return $this->json(['success' => false, 'message' => 'ID de notification requis'], 400);
        }

        $this->notificationModel->delete($notificationId);

        return $this->json(['success' => true]);
    }
}
