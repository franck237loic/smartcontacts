<?php

namespace App\Controllers;

use App\Models\User;
use App\Core\Controller;

/**
 * Auth Controller
 * Handles user authentication (login, register, logout)
 */
class AuthController extends Controller
{
    private $userModel;

    public function __construct()
    {
        parent::__construct();
        $this->userModel = new User();
    }

    /**
     * Show login form
     */
    public function loginForm()
    {
        return $this->render('auth/login', ['title' => 'Connexion - GlobalPhone Analytics']);
    }

    /**
     * Handle login
     */
    public function login()
    {
        $email = $this->post('email');
        $password = $this->post('password');

        if (empty($email) || empty($password)) {
            return $this->json(['success' => false, 'message' => 'Email et mot de passe requis'], 400);
        }

        $result = $this->userModel->login($email, $password);

        if ($result['success']) {
            // Set session
            $_SESSION['user_id'] = $result['user']['id'];
            $_SESSION['user_email'] = $result['user']['email'];
            $_SESSION['user_name'] = $result['user']['first_name'] . ' ' . $result['user']['last_name'];
            $_SESSION['user_role'] = $result['user']['role'];

            return $this->json(['success' => true, 'redirect' => '?dashboard']);
        }

        return $this->json(['success' => false, 'message' => $result['message']], 401);
    }

    /**
     * Show register form
     */
    public function registerForm()
    {
        return $this->render('auth/register', ['title' => 'Inscription - GlobalPhone Analytics']);
    }

    /**
     * Handle registration
     */
    public function register()
    {
        $email = $this->post('email');
        $password = $this->post('password');
        $confirmPassword = $this->post('confirm_password');
        $firstName = $this->post('first_name');
        $lastName = $this->post('last_name');
        $company = $this->post('company');

        // Validation
        if (empty($email) || empty($password)) {
            return $this->json(['success' => false, 'message' => 'Email et mot de passe requis'], 400);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->json(['success' => false, 'message' => 'Email invalide'], 400);
        }

        if (strlen($password) < 8) {
            return $this->json(['success' => false, 'message' => 'Le mot de passe doit contenir au moins 8 caractères'], 400);
        }

        if ($password !== $confirmPassword) {
            return $this->json(['success' => false, 'message' => 'Les mots de passe ne correspondent pas'], 400);
        }

        $result = $this->userModel->register($email, $password, $firstName, $lastName, $company);

        if ($result['success']) {
            // Auto-login after registration
            $_SESSION['user_id'] = $result['user_id'];
            $_SESSION['user_email'] = $email;
            $_SESSION['user_name'] = $firstName . ' ' . $lastName;
            $_SESSION['user_role'] = 'user';

            return $this->json(['success' => true, 'redirect' => '?dashboard']);
        }

        return $this->json(['success' => false, 'message' => $result['message']], 400);
    }

    /**
     * Handle logout
     */
    public function logout()
    {
        session_destroy();
        header('Location: ?auth/login');
        exit;
    }

    /**
     * Show forgot password form
     */
    public function forgotPasswordForm()
    {
        return $this->render('auth/forgot-password', ['title' => 'Mot de passe oublié - GlobalPhone Analytics']);
    }

    /**
     * Handle forgot password
     */
    public function forgotPassword()
    {
        $email = $this->post('email');

        if (empty($email)) {
            return $this->json(['success' => false, 'message' => 'Email requis'], 400);
        }

        $result = $this->userModel->requestPasswordReset($email);

        // For testing: return the reset token in the response
        if ($result['success'] && isset($result['reset_token'])) {
            return $this->json([
                'success' => true,
                'message' => 'Token de réinitialisation (pour tests): ' . $result['reset_token'],
                'reset_token' => $result['reset_token']
            ]);
        }

        // Always return success to prevent email enumeration
        return $this->json(['success' => true, 'message' => 'Si cet email existe, vous recevrez un lien de réinitialisation']);
    }

    /**
     * Show reset password form
     */
    public function resetPasswordForm()
    {
        $token = $this->get('token');
        return $this->render('auth/reset-password', ['title' => 'Réinitialiser le mot de passe - GlobalPhone Analytics', 'token' => $token]);
    }

    /**
     * Handle reset password
     */
    public function resetPassword()
    {
        $token = $this->post('token');
        $password = $this->post('password');
        $confirmPassword = $this->post('confirm_password');

        if (empty($token) || empty($password)) {
            return $this->json(['success' => false, 'message' => 'Token et mot de passe requis'], 400);
        }

        if (strlen($password) < 8) {
            return $this->json(['success' => false, 'message' => 'Le mot de passe doit contenir au moins 8 caractères'], 400);
        }

        if ($password !== $confirmPassword) {
            return $this->json(['success' => false, 'message' => 'Les mots de passe ne correspondent pas'], 400);
        }

        $result = $this->userModel->resetPassword($token, $password);

        if ($result['success']) {
            return $this->json(['success' => true, 'redirect' => '?auth/login']);
        }

        return $this->json(['success' => false, 'message' => $result['message']], 400);
    }

    /**
     * Show profile form
     */
    public function profileForm()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ?auth/login');
            exit;
        }

        $user = $this->userModel->findById($_SESSION['user_id']);
        return $this->render('auth/profile', ['title' => 'Mon Profil - GlobalPhone Analytics', 'user' => $user]);
    }

    /**
     * Handle profile update
     */
    public function updateProfile()
    {
        if (!isset($_SESSION['user_id'])) {
            return $this->json(['success' => false, 'message' => 'Non authentifié'], 401);
        }

        $firstName = $this->post('first_name');
        $lastName = $this->post('last_name');
        $company = $this->post('company');

        $result = $this->userModel->updateProfile($_SESSION['user_id'], $firstName, $lastName, $company);

        if ($result['success']) {
            $_SESSION['user_name'] = $firstName . ' ' . $lastName;
            return $this->json(['success' => true, 'message' => 'Profil mis à jour']);
        }

        return $this->json(['success' => false, 'message' => 'Erreur lors de la mise à jour'], 500);
    }

    /**
     * Handle password change
     */
    public function changePassword()
    {
        if (!isset($_SESSION['user_id'])) {
            return $this->json(['success' => false, 'message' => 'Non authentifié'], 401);
        }

        $currentPassword = $this->post('current_password');
        $newPassword = $this->post('new_password');
        $confirmPassword = $this->post('confirm_password');

        if (strlen($newPassword) < 8) {
            return $this->json(['success' => false, 'message' => 'Le mot de passe doit contenir au moins 8 caractères'], 400);
        }

        if ($newPassword !== $confirmPassword) {
            return $this->json(['success' => false, 'message' => 'Les mots de passe ne correspondent pas'], 400);
        }

        $result = $this->userModel->updatePassword($_SESSION['user_id'], $currentPassword, $newPassword);

        if ($result['success']) {
            return $this->json(['success' => true, 'message' => 'Mot de passe changé']);
        }

        return $this->json(['success' => false, 'message' => $result['message']], 400);
    }
}
