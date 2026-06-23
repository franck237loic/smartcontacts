<?php

namespace App\Core;

/**
 * Base Controller
 * Provides common functionality for all controllers
 */
class Controller
{
    /**
     * Constructor
     */
    public function __construct()
    {
        // Constructor vide
    }

    /**
     * Render a view
     */
    protected function render($view, $data = [])
    {
        extract($data);
        require BASE_PATH . '/app/Views/' . $view . '.php';
    }

    /**
     * Return JSON response
     */
    protected function json($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * Get GET parameter
     */
    protected function get($key, $default = null)
    {
        return $_GET[$key] ?? $default;
    }

    /**
     * Get POST parameter
     */
    protected function post($key, $default = null)
    {
        return $_POST[$key] ?? $default;
    }
}
