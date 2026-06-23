<?php

namespace App\Core\Middleware;

/**
 * Authentication Middleware
 * Protects routes that require authentication
 */
class AuthMiddleware
{
    /**
     * Check if user is authenticated
     */
    public static function check()
    {
        return isset($_SESSION['user_id']);
    }

    /**
     * Require authentication
     * Redirects to login if not authenticated
     */
    public static function requireAuth()
    {
        if (!self::check()) {
            header('Location: ?auth/login');
            exit;
        }
    }

    /**
     * Require guest (not authenticated)
     * Redirects to dashboard if already authenticated
     */
    public static function requireGuest()
    {
        if (self::check()) {
            header('Location: ?dashboard');
            exit;
        }
    }

    /**
     * Check if user has specific role
     */
    public static function hasRole($role)
    {
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === $role;
    }

    /**
     * Require specific role
     */
    public static function requireRole($role)
    {
        if (!self::hasRole($role)) {
            header('Location: ?dashboard');
            exit;
        }
    }

    /**
     * Get current user ID
     */
    public static function userId()
    {
        return $_SESSION['user_id'] ?? null;
    }

    /**
     * Get current user data
     */
    public static function user()
    {
        return [
            'id' => $_SESSION['user_id'] ?? null,
            'email' => $_SESSION['user_email'] ?? null,
            'name' => $_SESSION['user_name'] ?? null,
            'role' => $_SESSION['user_role'] ?? null
        ];
    }
}
