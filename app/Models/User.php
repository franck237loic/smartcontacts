<?php

namespace App\Models;

use App\Models\Database;

/**
 * User Model
 * Handles user authentication and management
 */
class User
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Register a new user
     */
    public function register($email, $password, $firstName = null, $lastName = null, $company = null)
    {
        // Check if email already exists
        if ($this->findByEmail($email)) {
            return ['success' => false, 'message' => 'Email déjà utilisé'];
        }

        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        // Generate verification token
        $verificationToken = bin2hex(random_bytes(32));

        $sql = "INSERT INTO users (email, password, first_name, last_name, company, verification_token) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $result = $this->db->query($sql, [$email, $hashedPassword, $firstName, $lastName, $company, $verificationToken]);

        if ($result) {
            return ['success' => true, 'user_id' => $this->db->lastInsertId(), 'verification_token' => $verificationToken];
        }

        return ['success' => false, 'message' => 'Erreur lors de l\'inscription'];
    }

    /**
     * Login user
     */
    public function login($email, $password)
    {
        $user = $this->findByEmail($email);

        if (!$user) {
            return ['success' => false, 'message' => 'Email ou mot de passe incorrect'];
        }

        if (!password_verify($password, $user['password'])) {
            return ['success' => false, 'message' => 'Email ou mot de passe incorrect'];
        }

        // Update last login
        $this->updateLastLogin($user['id']);

        return ['success' => true, 'user' => $user];
    }

    /**
     * Find user by email
     */
    public function findByEmail($email)
    {
        $sql = "SELECT * FROM users WHERE email = ?";
        return $this->db->fetchOne($sql, [$email]);
    }

    /**
     * Find user by ID
     */
    public function findById($id)
    {
        $sql = "SELECT * FROM users WHERE id = ?";
        return $this->db->fetchOne($sql, [$id]);
    }

    /**
     * Find user by verification token
     */
    public function findByVerificationToken($token)
    {
        $sql = "SELECT * FROM users WHERE verification_token = ?";
        return $this->db->fetchOne($sql, [$token]);
    }

    /**
     * Verify email
     */
    public function verifyEmail($token)
    {
        $user = $this->findByVerificationToken($token);

        if (!$user) {
            return ['success' => false, 'message' => 'Token de validation invalide'];
        }

        $sql = "UPDATE users SET email_verified = TRUE, verification_token = NULL WHERE id = ?";
        $this->db->query($sql, [$user['id']]);

        return ['success' => true];
    }

    /**
     * Request password reset
     */
    public function requestPasswordReset($email)
    {
        $user = $this->findByEmail($email);

        if (!$user) {
            return ['success' => false, 'message' => 'Email non trouvé'];
        }

        // Generate reset token
        $resetToken = bin2hex(random_bytes(32));
        $resetTokenExpires = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $sql = "UPDATE users SET reset_token = ?, reset_token_expires = ? WHERE id = ?";
        $this->db->query($sql, [$resetToken, $resetTokenExpires, $user['id']]);

        return ['success' => true, 'reset_token' => $resetToken];
    }

    /**
     * Reset password
     */
    public function resetPassword($token, $newPassword)
    {
        $sql = "SELECT * FROM users WHERE reset_token = ? AND reset_token_expires > NOW()";
        $user = $this->db->fetchOne($sql, [$token]);

        if (!$user) {
            return ['success' => false, 'message' => 'Token de réinitialisation invalide ou expiré'];
        }

        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

        $sql = "UPDATE users SET password = ?, reset_token = NULL, reset_token_expires = NULL WHERE id = ?";
        $this->db->query($sql, [$hashedPassword, $user['id']]);

        return ['success' => true];
    }

    /**
     * Update last login
     */
    private function updateLastLogin($userId)
    {
        $sql = "UPDATE users SET last_login_at = NOW() WHERE id = ?";
        $this->db->query($sql, [$userId]);
    }

    /**
     * Update user profile
     */
    public function updateProfile($userId, $firstName = null, $lastName = null, $company = null)
    {
        $sql = "UPDATE users SET first_name = ?, last_name = ?, company = ? WHERE id = ?";
        $this->db->query($sql, [$firstName, $lastName, $company, $userId]);
        return ['success' => true];
    }

    /**
     * Update password
     */
    public function updatePassword($userId, $currentPassword, $newPassword)
    {
        $user = $this->findById($userId);

        if (!$user || !password_verify($currentPassword, $user['password'])) {
            return ['success' => false, 'message' => 'Mot de passe actuel incorrect'];
        }

        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

        $sql = "UPDATE users SET password = ? WHERE id = ?";
        $this->db->query($sql, [$hashedPassword, $userId]);

        return ['success' => true];
    }

    /**
     * Get user subscription
     */
    public function getSubscription($userId)
    {
        $sql = "SELECT s.*, us.status, us.current_period_start, us.current_period_end 
                FROM user_subscriptions us
                JOIN subscriptions s ON us.subscription_id = s.id
                WHERE us.user_id = ? AND us.status = 'active'
                ORDER BY us.created_at DESC
                LIMIT 1";
        return $this->db->fetchOne($sql, [$userId]);
    }

    /**
     * Update API quota
     */
    public function incrementApiQuota($userId)
    {
        $sql = "UPDATE users SET api_quota_used = api_quota_used + 1 WHERE id = ?";
        $this->db->query($sql, [$userId]);
    }

    /**
     * Check API quota
     */
    public function checkApiQuota($userId)
    {
        $user = $this->findById($userId);
        return $user['api_quota_used'] < $user['api_quota_limit'];
    }

    /**
     * Reset monthly API quota
     */
    public function resetMonthlyQuota($userId)
    {
        $sql = "UPDATE users SET api_quota_used = 0 WHERE id = ?";
        $this->db->query($sql, [$userId]);
    }
}
