<?php

namespace App\Controllers;

/**
 * Base Controller
 * All controllers extend this base class
 */
abstract class Controller
{
    protected $view;

    public function __construct()
    {
        $this->view = new \App\Core\View();
    }

    /**
     * Render a view
     */
    protected function render($view, $data = [])
    {
        return $this->view->render($view, $data);
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
     * Get POST data
     */
    protected function post($key = null, $default = null)
    {
        if ($key === null) {
            return $_POST;
        }
        return $_POST[$key] ?? $default;
    }

    /**
     * Get GET data
     */
    protected function get($key = null, $default = null)
    {
        if ($key === null) {
            return $_GET;
        }
        return $_GET[$key] ?? $default;
    }

    /**
     * Validate required fields
     */
    protected function validateRequired($data, $fields)
    {
        $missing = [];
        foreach ($fields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                $missing[] = $field;
            }
        }
        return $missing;
    }
}
