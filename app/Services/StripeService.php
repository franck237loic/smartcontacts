<?php

namespace App\Services;

use Stripe\Stripe;
use Stripe\Checkout\Session;
use Stripe\Customer;
use Stripe\Subscription;

/**
 * Stripe Payment Service
 * Handles Stripe checkout sessions and subscription management
 */
class StripeService
{
    private $config;
    private $secretKey;

    public function __construct()
    {
        $config = require __DIR__ . '/../Config/config.php';
        $this->config = $config;
        $this->secretKey = $config['stripe']['secret_key'];
        
        // Only initialize Stripe if a valid API key is configured
        if ($this->secretKey && $this->secretKey !== 'sk_test_your_secret_key_here') {
            Stripe::setApiKey($this->secretKey);
        }
    }

    /**
     * Create a checkout session for a subscription
     */
    public function createCheckoutSession($subscriptionSlug, $interval = 'monthly', $userId = null)
    {
        // Check if Stripe is configured
        if (!$this->secretKey || $this->secretKey === 'sk_test_your_secret_key_here') {
            return [
                'success' => false,
                'message' => 'Stripe n\'est pas configuré. Veuillez configurer vos clés API Stripe.',
            ];
        }

        try {
            // Get subscription details from database
            $subscription = $this->getSubscriptionBySlug($subscriptionSlug);
            
            if (!$subscription) {
                throw new \Exception('Subscription plan not found');
            }

            // Get or create Stripe customer
            $customerId = $this->getOrCreateCustomer($userId);

            // Create checkout session
            $session = Session::create([
                'customer' => $customerId,
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => $this->config['stripe']['currency'],
                        'product_data' => [
                            'name' => $subscription['name'],
                            'description' => 'Abonnement ' . $subscription['name'] . ' - ' . $interval,
                        ],
                        'unit_amount' => $subscription['price'] * 100, // Convert to cents
                        'recurring' => [
                            'interval' => $interval,
                        ],
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'subscription',
                'success_url' => $this->config['stripe']['success_url'],
                'cancel_url' => $this->config['stripe']['cancel_url'],
                'metadata' => [
                    'user_id' => $userId,
                    'subscription_slug' => $subscriptionSlug,
                    'interval' => $interval,
                ],
            ]);

            return [
                'success' => true,
                'session_id' => $session->id,
                'url' => $session->url,
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get or create Stripe customer
     */
    private function getOrCreateCustomer($userId)
    {
        if (!$userId) {
            // Create anonymous customer
            $customer = Customer::create([
                'description' => 'Guest customer',
            ]);
            return $customer->id;
        }

        // Check if user already has a Stripe customer ID
        $db = $this->getDatabase();
        $stmt = $db->prepare("SELECT stripe_customer_id FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();

        if ($user && $user['stripe_customer_id']) {
            return $user['stripe_customer_id'];
        }

        // Create new customer
        $customer = Customer::create([
            'email' => $user['email'] ?? null,
            'name' => $user['first_name'] . ' ' . $user['last_name'] ?? null,
            'metadata' => [
                'user_id' => $userId,
            ],
        ]);

        // Save customer ID to database
        $stmt = $db->prepare("UPDATE users SET stripe_customer_id = ? WHERE id = ?");
        $stmt->execute([$customer->id, $userId]);

        return $customer->id;
    }

    /**
     * Get subscription by slug from database
     */
    private function getSubscriptionBySlug($slug)
    {
        $db = $this->getDatabase();
        $stmt = $db->prepare("SELECT * FROM subscriptions WHERE slug = ?");
        $stmt->execute([$slug]);
        return $stmt->fetch();
    }

    /**
     * Get database connection
     */
    private function getDatabase()
    {
        $config = $this->config['database'];
        return new \PDO(
            "mysql:host=" . $config['host'] . ";dbname=" . $config['dbname'] . ";charset=utf8mb4",
            $config['username'],
            $config['password'],
            $config['options']
        );
    }

    /**
     * Verify webhook signature
     */
    public function verifyWebhookSignature($payload, $sigHeader)
    {
        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload,
                $sigHeader,
                $this->config['stripe']['webhook_secret']
            );
            return $event;
        } catch (\UnexpectedValueException $e) {
            // Invalid payload
            throw new \Exception('Invalid payload');
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            // Invalid signature
            throw new \Exception('Invalid signature');
        }
    }

    /**
     * Handle checkout session completed event
     */
    public function handleCheckoutSessionCompleted($session)
    {
        $userId = $session->metadata->user_id;
        $subscriptionSlug = $session->metadata->subscription_slug;
        $interval = $session->metadata->interval;
        $stripeSubscriptionId = $session->subscription;

        // Get subscription details
        $subscription = $this->getSubscriptionBySlug($subscriptionSlug);
        
        // Update user subscription in database
        $db = $this->getDatabase();
        
        // Update user quota
        $stmt = $db->prepare("UPDATE users SET api_quota_limit = ?, api_quota_used = 0 WHERE id = ?");
        $stmt->execute([$subscription['api_quota'], $userId]);

        // Create or update user subscription record
        $stmt = $db->prepare("
            INSERT INTO user_subscriptions (user_id, subscription_id, stripe_subscription_id, status, interval, start_date, end_date)
            VALUES (?, ?, ?, 'active', ?, NOW(), DATE_ADD(NOW(), INTERVAL 1 MONTH))
            ON DUPLICATE KEY UPDATE 
                stripe_subscription_id = ?,
                status = 'active',
                interval = ?,
                start_date = NOW(),
                end_date = DATE_ADD(NOW(), INTERVAL 1 MONTH)
        ");
        $stmt->execute([
            $userId,
            $subscription['id'],
            $stripeSubscriptionId,
            $interval,
            $stripeSubscriptionId,
            $interval
        ]);

        return true;
    }

    /**
     * Handle subscription cancelled event
     */
    public function handleSubscriptionCancelled($subscription)
    {
        $stripeSubscriptionId = $subscription->id;

        // Update user subscription in database
        $db = $this->getDatabase();
        $stmt = $db->prepare("
            UPDATE user_subscriptions 
            SET status = 'cancelled', end_date = NOW()
            WHERE stripe_subscription_id = ?
        ");
        $stmt->execute([$stripeSubscriptionId]);

        // Reset user quota to free tier
        $freeQuota = $this->getFreeQuota();
        $stmt = $db->prepare("UPDATE users SET api_quota_limit = ? WHERE id IN (SELECT user_id FROM user_subscriptions WHERE stripe_subscription_id = ?)");
        $stmt->execute([$freeQuota, $stripeSubscriptionId]);

        return true;
    }

    /**
     * Get free tier quota
     */
    private function getFreeQuota()
    {
        $db = $this->getDatabase();
        $stmt = $db->prepare("SELECT api_quota FROM subscriptions WHERE slug = 'free'");
        $stmt->execute();
        $result = $stmt->fetch();
        return $result ? $result['api_quota'] : 100;
    }

    /**
     * Cancel subscription
     */
    public function cancelSubscription($stripeSubscriptionId)
    {
        try {
            $subscription = Subscription::retrieve($stripeSubscriptionId);
            $subscription->cancel();

            // Update database
            $db = $this->getDatabase();
            $stmt = $db->prepare("
                UPDATE user_subscriptions 
                SET status = 'cancelled', end_date = NOW()
                WHERE stripe_subscription_id = ?
            ");
            $stmt->execute([$stripeSubscriptionId]);

            return ['success' => true];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get publishable key for frontend
     */
    public function getPublishableKey()
    {
        return $this->config['stripe']['publishable_key'];
    }
}
