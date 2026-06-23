<?php

namespace App\Controllers;

use App\Models\User;
use App\Models\SearchHistory;
use App\Models\ApiKey;
use App\Models\Subscription;
use App\Core\Controller;
use App\Core\Middleware\AuthMiddleware;

/**
 * User Controller
 * Handles user dashboard and account management
 */
class UserController extends Controller
{
    private $userModel;
    private $searchHistoryModel;
    private $apiKeyModel;
    private $subscriptionModel;

    public function __construct()
    {
        parent::__construct();
        $this->userModel = new User();
        $this->searchHistoryModel = new SearchHistory();
        $this->apiKeyModel = new ApiKey();
        $this->subscriptionModel = new Subscription();
    }

    /**
     * User dashboard
     */
    public function dashboard()
    {
        AuthMiddleware::requireAuth();
        
        $userId = AuthMiddleware::userId();
        $user = $this->userModel->findById($userId);
        $subscription = $this->subscriptionModel->getUserSubscription($userId);
        $recentSearches = $this->searchHistoryModel->getRecentSearches($userId, 10);
        $apiKeys = $this->apiKeyModel->getUserKeys($userId);
        
        return $this->render('user/dashboard', [
            'title' => 'Mon Dashboard - GlobalPhone Analytics',
            'user' => $user,
            'subscription' => $subscription,
            'recentSearches' => $recentSearches,
            'apiKeys' => $apiKeys
        ]);
    }

    /**
     * User search history
     */
    public function history()
    {
        AuthMiddleware::requireAuth();
        
        $userId = AuthMiddleware::userId();
        $history = $this->searchHistoryModel->getUserHistory($userId, 100);
        
        return $this->render('user/history', [
            'title' => 'Historique des recherches - GlobalPhone Analytics',
            'history' => $history
        ]);
    }

    /**
     * API Keys management
     */
    public function apiKeys()
    {
        AuthMiddleware::requireAuth();
        
        $userId = AuthMiddleware::userId();
        $apiKeys = $this->apiKeyModel->getUserKeys($userId);
        
        return $this->render('user/api-keys', [
            'title' => 'Clés API - GlobalPhone Analytics',
            'apiKeys' => $apiKeys
        ]);
    }

    /**
     * Create API key
     */
    public function createApiKey()
    {
        AuthMiddleware::requireAuth();
        
        $name = $this->post('name');
        $expiresAt = $this->post('expires_at');
        
        if (empty($name)) {
            return $this->json(['success' => false, 'message' => 'Nom requis'], 400);
        }
        
        $result = $this->apiKeyModel->create(AuthMiddleware::userId(), $name, $expiresAt ?: null);
        
        if ($result['success']) {
            return $this->json(['success' => true, 'key' => $result['key']]);
        }
        
        return $this->json(['success' => false, 'message' => 'Erreur lors de la création'], 500);
    }

    /**
     * Delete API key
     */
    public function deleteApiKey()
    {
        AuthMiddleware::requireAuth();
        
        $keyId = $this->post('key_id');
        
        $result = $this->apiKeyModel->delete($keyId, AuthMiddleware::userId());
        
        return $this->json($result);
    }

    /**
     * Clear search history
     */
    public function clearHistory()
    {
        AuthMiddleware::requireAuth();
        
        $result = $this->searchHistoryModel->clearUserHistory(AuthMiddleware::userId());
        
        return $this->json($result);
    }
}
