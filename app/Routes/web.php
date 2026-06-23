<?php

namespace App\Routes;

use App\Core\Router;

/**
 * Web Routes
 * Define all application routes here
 */

$router = new Router();

// Dashboard routes
$router->add('', ['controller' => 'DashboardController', 'action' => 'index']);
$router->add('dashboard', ['controller' => 'DashboardController', 'action' => 'index']);
$router->add('dashboard/analytics', ['controller' => 'DashboardController', 'action' => 'analytics']);
$router->add('dashboard/analyze', ['controller' => 'DashboardController', 'action' => 'analyze']);
$router->add('api', ['controller' => 'DashboardController', 'action' => 'index']);

// Auth routes
$router->add('auth/login', ['controller' => 'AuthController', 'action' => 'loginForm']);
$router->add('auth/register', ['controller' => 'AuthController', 'action' => 'registerForm']);
$router->add('auth/logout', ['controller' => 'AuthController', 'action' => 'logout']);
$router->add('auth/forgot-password', ['controller' => 'AuthController', 'action' => 'forgotPasswordForm']);
$router->add('auth/reset-password', ['controller' => 'AuthController', 'action' => 'resetPasswordForm']);
$router->add('auth/profile', ['controller' => 'AuthController', 'action' => 'profileForm']);

// Subscription routes
$router->add('subscription/plans', ['controller' => 'SubscriptionController', 'action' => 'plans']);
$router->add('subscription/checkout', ['controller' => 'SubscriptionController', 'action' => 'checkout']);
$router->add('subscription/success', ['controller' => 'SubscriptionController', 'action' => 'success']);
$router->add('subscription/manage', ['controller' => 'SubscriptionController', 'action' => 'manage']);

// User routes
$router->add('user/dashboard', ['controller' => 'UserController', 'action' => 'dashboard']);
$router->add('user/history', ['controller' => 'UserController', 'action' => 'history']);
$router->add('user/api-keys', ['controller' => 'UserController', 'action' => 'apiKeys']);

// API routes
$router->add('api/phone/analyze', ['controller' => 'PhoneController', 'action' => 'analyze']);
$router->add('api/phone/batch', ['controller' => 'PhoneController', 'action' => 'batchAnalyze']);
$router->add('api/phone/countries', ['controller' => 'PhoneController', 'action' => 'countries']);
$router->add('api/phone/operators', ['controller' => 'PhoneController', 'action' => 'operators']);
$router->add('api/phone/prefixes', ['controller' => 'PhoneController', 'action' => 'prefixes']);
$router->add('api/phone/search', ['controller' => 'PhoneController', 'action' => 'search']);
$router->add('api/phone/statistics', ['controller' => 'PhoneController', 'action' => 'statistics']);

// Dashboard API routes
$router->add('api/dashboard/stats', ['controller' => 'DashboardController', 'action' => 'apiStats']);
$router->add('api/dashboard/countries', ['controller' => 'DashboardController', 'action' => 'apiCountryDistribution']);
$router->add('api/dashboard/operators', ['controller' => 'DashboardController', 'action' => 'apiOperatorDistribution']);

// Export route
$router->add('api/export/csv', ['controller' => 'PhoneController', 'action' => 'exportCsv']);

return $router;
