<?php

/**
 * Application Entry Point
 * GlobalPhone Analytics - MVC Architecture
 */

// Define base path
define('BASE_PATH', dirname(__DIR__));

// Load Composer autoloader
if (file_exists(BASE_PATH . '/vendor/autoload.php')) {
    require_once BASE_PATH . '/vendor/autoload.php';
}

// Load autoloader
require_once BASE_PATH . '/app/Core/Autoloader.php';
App\Core\Autoloader::register();

// Load configuration
$config = require BASE_PATH . '/app/Config/config.php';

// Set timezone
date_default_timezone_set($config['app']['timezone']);

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load routes
$router = require BASE_PATH . '/app/Routes/web.php';

// Get the URL
$url = $_SERVER['QUERY_STRING'] ?? '';
$url = rtrim($url, '/');

// Dispatch the route
try {
    $router->dispatch($url);
} catch (\Exception $e) {
    // Handle errors
    if ($config['app']['debug']) {
        echo '<h1>Error</h1>';
        echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
        if ($config['app']['env'] === 'development') {
            echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
        }
    } else {
        http_response_code(500);
        echo 'Internal Server Error';
    }
}
