<?php

namespace App\Models;

use App\Models\Database;

/**
 * Subscription Model
 * Handles subscription management
 */
class Subscription
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get all available subscription plans
     */
    public function getAllPlans()
    {
        $sql = "SELECT * FROM subscriptions ORDER BY price ASC";
        return $this->db->fetchAll($sql);
    }

    /**
     * Get subscription plan by slug
     */
    public function getPlanBySlug($slug)
    {
        $sql = "SELECT * FROM subscriptions WHERE slug = ?";
        return $this->db->fetchOne($sql, [$slug]);
    }

    /**
     * Get subscription plan by ID
     */
    public function getPlanById($id)
    {
        $sql = "SELECT * FROM subscriptions WHERE id = ?";
        return $this->db->fetchOne($sql, [$id]);
    }

    /**
     * Get user subscription
     */
    public function getUserSubscription($userId)
    {
        $sql = "SELECT s.*, us.status, us.current_period_start, us.current_period_end, 
                        us.stripe_subscription_id, us.stripe_customer_id, us.cancel_at_period_end
                FROM user_subscriptions us
                JOIN subscriptions s ON us.subscription_id = s.id
                WHERE us.user_id = ? AND us.status = 'active'
                ORDER BY us.created_at DESC
                LIMIT 1";
        
        $subscription = $this->db->fetchOne($sql, [$userId]);
        
        // Check if subscription has expired
        if ($subscription && $subscription['current_period_end']) {
            $endDate = strtotime($subscription['current_period_end']);
            $today = strtotime(date('Y-m-d'));
            
            if ($endDate < $today) {
                // Subscription has expired, update status
                $this->updateSubscriptionStatusByUser($userId, 'canceled');
                // Reset user quota to free tier
                $this->resetUserToFreeQuota($userId);
                return false;
            }
        }
        
        return $subscription;
    }

    /**
     * Create user subscription
     */
    public function createUserSubscription($userId, $subscriptionId, $stripeSubscriptionId = null, $stripeCustomerId = null)
    {
        $plan = $this->getPlanById($subscriptionId);
        
        if (!$plan) {
            return ['success' => false, 'message' => 'Plan introuvable'];
        }

        // Calculate period dates
        $startDate = date('Y-m-d');
        $endDate = date('Y-m-d', strtotime('+1 month'));
        
        if ($plan['interval'] === 'yearly') {
            $endDate = date('Y-m-d', strtotime('+1 year'));
        }

        $sql = "INSERT INTO user_subscriptions (user_id, subscription_id, stripe_subscription_id, stripe_customer_id, 
                current_period_start, current_period_end, status) 
                VALUES (?, ?, ?, ?, ?, ?, 'active')";
        
        $result = $this->db->query($sql, [$userId, $subscriptionId, $stripeSubscriptionId, $stripeCustomerId, $startDate, $endDate]);

        if ($result) {
            // Update user quota limit
            $this->updateUserQuotaLimit($userId, $plan['api_quota']);
            return ['success' => true, 'subscription_id' => $this->db->lastInsertId()];
        }

        return ['success' => false, 'message' => 'Erreur lors de la création de l\'abonnement'];
    }

    /**
     * Cancel user subscription
     */
    public function cancelUserSubscription($userId, $cancelAtPeriodEnd = true)
    {
        $subscription = $this->getUserSubscription($userId);

        if (!$subscription) {
            return ['success' => false, 'message' => 'Aucun abonnement actif'];
        }

        $sql = "UPDATE user_subscriptions SET cancel_at_period_end = ? WHERE user_id = ? AND status = 'active'";
        $this->db->query($sql, [$cancelAtPeriodEnd ? 1 : 0, $userId]);

        return ['success' => true];
    }

    /**
     * Update user quota limit
     */
    private function updateUserQuotaLimit($userId, $quotaLimit)
    {
        $sql = "UPDATE users SET api_quota_limit = ? WHERE id = ?";
        $this->db->query($sql, [$quotaLimit, $userId]);
    }

    /**
     * Check if user has active subscription
     */
    public function hasActiveSubscription($userId)
    {
        return $this->getUserSubscription($userId) !== false;
    }

    /**
     * Update subscription status
     */
    public function updateSubscriptionStatus($subscriptionId, $status)
    {
        $sql = "UPDATE user_subscriptions SET status = ? WHERE id = ?";
        $this->db->query($sql, [$status, $subscriptionId]);
        return ['success' => true];
    }

    /**
     * Update subscription status by user ID
     */
    private function updateSubscriptionStatusByUser($userId, $status)
    {
        $sql = "UPDATE user_subscriptions SET status = ? WHERE user_id = ? AND status = 'active'";
        $this->db->query($sql, [$status, $userId]);
    }

    /**
     * Reset user to free quota
     */
    private function resetUserToFreeQuota($userId)
    {
        // Set quota to 0 to force user to take a subscription
        $sql = "UPDATE users SET api_quota_limit = 0, api_quota_used = 0 WHERE id = ?";
        $this->db->query($sql, [$userId]);
    }

    /**
     * Get subscription statistics
     */
    public function getStatistics()
    {
        $sql = "SELECT 
                    COUNT(DISTINCT user_id) as total_subscribers,
                    COUNT(CASE WHEN status = 'active' THEN 1 END) as active_subscriptions,
                    COUNT(CASE WHEN status = 'canceled' THEN 1 END) as canceled_subscriptions,
                    SUM(s.price) as total_revenue
                FROM user_subscriptions us
                JOIN subscriptions s ON us.subscription_id = s.id";
        return $this->db->fetchOne($sql);
    }

    /**
     * Get subscriptions by plan
     */
    public function getSubscriptionsByPlan()
    {
        $sql = "SELECT s.name, s.slug, COUNT(us.user_id) as subscriber_count
                FROM subscriptions s
                LEFT JOIN user_subscriptions us ON s.id = us.subscription_id AND us.status = 'active'
                GROUP BY s.id, s.name, s.slug
                ORDER BY subscriber_count DESC";
        return $this->db->fetchAll($sql);
    }
}
