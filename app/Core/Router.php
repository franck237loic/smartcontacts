<?php

namespace App\Core;

/**
 * Router Class
 * Handles URL routing and controller dispatching
 */
class Router
{
    private $routes = [];
    private $params = [];

    /**
     * Add a route
     */
    public function add($route, $params = [])
    {
        // Convert route to regex
        $route = str_replace('/', '\/', $route);
        $route = preg_replace('/\{([a-z]+)\}/', '(?P<\1>[a-z0-9-]+)', $route);
        $route = preg_replace('/\{([a-z]+):([^\}]+)\}/', '(?P<\1>\2)', $route);
        $route = '/^' . $route . '$/i';

        $this->routes[$route] = $params;
    }

    /**
     * Get routes
     */
    public function getRoutes()
    {
        return $this->routes;
    }

    /**
     * Match the URL to a route
     */
    public function match($url)
    {
        foreach ($this->routes as $route => $params) {
            if (preg_match($route, $url, $matches)) {
                // Get named capture group values
                foreach ($matches as $key => $value) {
                    if (is_string($key)) {
                        $params[$key] = $value;
                    }
                }
                $this->params = $params;
                return true;
            }
        }
        return false;
    }

    /**
     * Get the matched parameters
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Dispatch the route to the controller
     */
    public function dispatch($url)
    {
        $url = $this->removeQueryStringVariables($url);

        // Handle POST requests for auth routes
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handlePostRequest($url);
            return;
        }

        // Handle GET requests for API endpoints
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $this->handleGetRequest($url);
        }

        if ($this->match($url)) {
            $controller = $this->params['controller'] ?? null;
            $action = $this->params['action'] ?? 'index';

            if ($controller) {
                $controller = $this->convertToStudlyCaps($controller);
                $controller = $this->getNamespace() . $controller;

                if (class_exists($controller)) {
                    $controllerObject = new $controller();
                    $action = $this->convertToCamelCase($action);

                    if (method_exists($controllerObject, $action)) {
                        $result = $controllerObject->$action();
                        // If JSON response was sent, exit to prevent layout rendering
                        if (headers_sent() && strpos(implode(' ', headers_list()), 'application/json') !== false) {
                            exit;
                        }
                        echo $result;
                    } else {
                        throw new \Exception("Method $action in controller $controller not found");
                    }
                } else {
                    throw new \Exception("Controller $controller not found");
                }
            } else {
                throw new \Exception("No controller specified");
            }
        } else {
            throw new \Exception("No route matched", 404);
        }
    }

    /**
     * Handle POST requests
     */
    private function handlePostRequest($url)
    {
        // Handle JSON request body
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (strpos($contentType, 'application/json') !== false) {
            $jsonInput = file_get_contents('php://input');
            $jsonData = json_decode($jsonInput, true);
            if ($jsonData) {
                $_POST = array_merge($_POST, $jsonData);
            }
        }

        // Map POST routes to controller actions
        $postRoutes = [
            'auth/login' => ['controller' => 'AuthController', 'action' => 'login'],
            'auth/register' => ['controller' => 'AuthController', 'action' => 'register'],
            'auth/logout' => ['controller' => 'AuthController', 'action' => 'logout'],
            'auth/forgot-password' => ['controller' => 'AuthController', 'action' => 'forgotPassword'],
            'auth/reset-password' => ['controller' => 'AuthController', 'action' => 'resetPassword'],
            'auth/profile' => ['controller' => 'AuthController', 'action' => 'updateProfile'],
            'auth/change-password' => ['controller' => 'AuthController', 'action' => 'changePassword'],
            'subscription/process-payment' => ['controller' => 'SubscriptionController', 'action' => 'processPayment'],
            'stripe/webhook' => ['controller' => 'SubscriptionController', 'action' => 'webhook'],
            'subscription/cancel' => ['controller' => 'SubscriptionController', 'action' => 'cancel'],
            'user/create-api-key' => ['controller' => 'UserController', 'action' => 'createApiKey'],
            'user/delete-api-key' => ['controller' => 'UserController', 'action' => 'deleteApiKey'],
            'user/clear-history' => ['controller' => 'UserController', 'action' => 'clearHistory'],
            'notification/mark-read' => ['controller' => 'NotificationController', 'action' => 'markAsRead'],
            'notification/mark-all-read' => ['controller' => 'NotificationController', 'action' => 'markAllAsRead'],
            'notification/delete' => ['controller' => 'NotificationController', 'action' => 'delete'],
        ];

        if (isset($postRoutes[$url])) {
            $route = $postRoutes[$url];
            $controller = $this->convertToStudlyCaps($route['controller']);
            $controller = $this->getNamespace() . $controller;

            if (class_exists($controller)) {
                $controllerObject = new $controller();
                $action = $route['action'];

                if (method_exists($controllerObject, $action)) {
                    echo $controllerObject->$action();
                    return;
                }
            }
        } else {
            throw new \Exception("No route matched", 404);
        }
    }

    /**
     * Handle GET requests for API endpoints
     */
    private function handleGetRequest($url)
    {
        // Map GET API routes to controller actions
        $getApiRoutes = [
            'notification/unread' => ['controller' => 'NotificationController', 'action' => 'getUnread'],
            'notification/all' => ['controller' => 'NotificationController', 'action' => 'getAll'],
        ];

        if (isset($getApiRoutes[$url])) {
            $route = $getApiRoutes[$url];
            $controller = $this->convertToStudlyCaps($route['controller']);
            $controller = $this->getNamespace() . $controller;

            if (class_exists($controller)) {
                $controllerObject = new $controller();
                $action = $route['action'];

                if (method_exists($controllerObject, $action)) {
                    echo $controllerObject->$action();
                    return;
                }
            }
        }
    }

    /**
     * Convert the string with hyphens to StudlyCaps
     */
    private function convertToStudlyCaps($string)
    {
        return str_replace(' ', '', ucwords(str_replace('-', ' ', $string)));
    }

    /**
     * Convert the string with hyphens to camelCase
     */
    private function convertToCamelCase($string)
    {
        return lcfirst($this->convertToStudlyCaps($string));
    }

    /**
     * Remove the query string variables from the URL
     */
    private function removeQueryStringVariables($url)
    {
        // Handle URLs like ?auth/register&param=value
        if (strpos($url, '&') !== false) {
            $parts = explode('&', $url, 2);
            $url = $parts[0];
        }
        // Handle URLs like ?param=value (not a route)
        if (strpos($url, '=') !== false) {
            $url = '';
        }
        return $url;
    }

    /**
     * Get the namespace
     */
    private function getNamespace()
    {
        $namespace = 'App\Controllers\\';
        if (array_key_exists('namespace', $this->params)) {
            $namespace .= $this->params['namespace'] . '\\';
        }
        return $namespace;
    }
}
