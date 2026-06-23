<?php

namespace App\Controllers;

use App\Models\Subscription;
use App\Models\User;
use App\Core\Controller;
use App\Services\StripeService;

/**
 * Subscription Controller
 * Handles subscription management and payment
 */
class SubscriptionController extends Controller
{
    private $subscriptionModel;
    private $userModel;
    private $stripeService;

    public function __construct()
    {
        parent::__construct();
        $this->subscriptionModel = new Subscription();
        $this->userModel = new User();
        $this->stripeService = new StripeService();
    }

    /**
     * Show subscription plans
     */
    public function plans()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ?auth/login');
            exit;
        }

        $plans = $this->subscriptionModel->getAllPlans();
        $currentSubscription = $this->subscriptionModel->getUserSubscription($_SESSION['user_id']);
        $stripePublishableKey = $this->stripeService->getPublishableKey();
        
        return $this->render('subscription/plans', [
            'title' => 'Plans d\'abonnement - GlobalPhone Analytics',
            'plans' => $plans,
            'currentSubscription' => $currentSubscription,
            'stripePublishableKey' => $stripePublishableKey
        ]);
    }

    /**
     * Show checkout page
     */
    public function checkout()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ?auth/login');
            exit;
        }

        $planSlug = $this->get('plan');
        $plan = $this->subscriptionModel->getPlanBySlug($planSlug);

        if (!$plan) {
            header('Location: ?subscription/plans');
            exit;
        }

        return $this->render('subscription/checkout', [
            'title' => 'Paiement - GlobalPhone Analytics',
            'plan' => $plan
        ]);
    }

    /**
     * Process payment with Stripe
     */
    public function processPayment()
    {
        if (!isset($_SESSION['user_id'])) {
            return $this->json(['success' => false, 'message' => 'Non authentifié'], 401);
        }

        $planSlug = $this->post('plan');
        $interval = $this->post('interval', 'monthly');

        $plan = $this->subscriptionModel->getPlanBySlug($planSlug);

        if (!$plan) {
            return $this->json(['success' => false, 'message' => 'Plan invalide'], 400);
        }

        // Handle free plan directly without Stripe
        if ($plan['price'] == 0) {
            $result = $this->subscriptionModel->createUserSubscription(
                $_SESSION['user_id'],
                $plan['id']
            );

            if ($result['success']) {
                return $this->json([
                    'success' => true,
                    'redirect' => '?subscription/success'
                ]);
            }

            return $this->json(['success' => false, 'message' => 'Erreur lors de l\'activation du plan'], 500);
        }

        // Create Stripe checkout session for paid plans
        $result = $this->stripeService->createCheckoutSession(
            $planSlug,
            $interval,
            $_SESSION['user_id']
        );

        if ($result['success']) {
            return $this->json([
                'success' => true,
                'checkout_url' => $result['url']
            ]);
        }

        return $this->json(['success' => false, 'message' => $result['message']], 500);
    }

    /**
     * Show success page
     */
    public function success()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ?auth/login');
            exit;
        }

        return $this->render('subscription/success', [
            'title' => 'Abonnement réussi - GlobalPhone Analytics'
        ]);
    }

    /**
     * Cancel subscription
     */
    public function cancel()
    {
        if (!isset($_SESSION['user_id'])) {
            return $this->json(['success' => false, 'message' => 'Non authentifié'], 401);
        }

        // Get user's Stripe subscription ID
        $subscription = $this->subscriptionModel->getUserSubscription($_SESSION['user_id']);
        
        if ($subscription && $subscription['stripe_subscription_id']) {
            // Cancel in Stripe
            $result = $this->stripeService->cancelSubscription($subscription['stripe_subscription_id']);
            
            if (!$result['success']) {
                return $this->json(['success' => false, 'message' => $result['message']], 400);
            }
        }

        // Cancel in database
        $result = $this->subscriptionModel->cancelUserSubscription($_SESSION['user_id']);

        if ($result['success']) {
            return $this->json(['success' => true, 'message' => 'Abonnement annulé']);
        }

        return $this->json(['success' => false, 'message' => $result['message']], 400);
    }

    /**
     * Show user subscription management
     */
    public function manage()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ?auth/login');
            exit;
        }

        $subscription = $this->subscriptionModel->getUserSubscription($_SESSION['user_id']);
        $user = $this->userModel->findById($_SESSION['user_id']);

        return $this->render('subscription/manage', [
            'title' => 'Gérer mon abonnement - GlobalPhone Analytics',
            'subscription' => $subscription,
            'user' => $user
        ]);
    }

    /**
     * Handle Stripe webhook
     */
    public function webhook()
    {
        $payload = file_get_contents('php://input');
        $sigHeader = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

        try {
            $event = $this->stripeService->verifyWebhookSignature($payload, $sigHeader);

            switch ($event->type) {
                case 'checkout.session.completed':
                    $session = $event->data->object;
                    $this->stripeService->handleCheckoutSessionCompleted($session);
                    break;

                case 'customer.subscription.deleted':
                    $subscription = $event->data->object;
                    $this->stripeService->handleSubscriptionCancelled($subscription);
                    break;

                case 'invoice.payment_succeeded':
                    // Handle successful payment for recurring subscription
                    $invoice = $event->data->object;
                    // You can add logic here to update subscription end date
                    break;

                case 'invoice.payment_failed':
                    // Handle failed payment
                    $invoice = $event->data->object;
                    // You can add logic here to notify user of failed payment
                    break;
            }

            http_response_code(200);
            echo json_encode(['success' => true]);
            exit;

        } catch (\Exception $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            exit;
        }
    }
}
