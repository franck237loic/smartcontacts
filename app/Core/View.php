<?php

namespace App\Core;

/**
 * View Class
 * Handles view rendering and template management
 */
class View
{
    private $viewPath;
    private $data = [];

    public function __construct()
    {
        $config = require __DIR__ . '/../Config/config.php';
        $this->viewPath = $config['view']['path'];
    }

    /**
     * Render a view with data
     */
    public function render($view, $data = [])
    {
        $this->data = array_merge($this->data, $data);
        
        $viewFile = $this->viewPath . '/' . $view . '.php';
        
        if (!file_exists($viewFile)) {
            throw new \Exception("View file not found: $viewFile");
        }

        // Extract data to variables
        extract($this->data);

        // Start output buffering
        ob_start();

        // Include the view file
        require $viewFile;

        // Get the content and clean the buffer
        $content = ob_get_clean();

        return $content;
    }

    /**
     * Set data for the view
     */
    public function set($key, $value)
    {
        $this->data[$key] = $value;
        return $this;
    }

    /**
     * Get data from the view
     */
    public function get($key, $default = null)
    {
        return $this->data[$key] ?? $default;
    }

    /**
     * Render a partial view
     */
    public function partial($partial, $data = [])
    {
        return $this->render('partials/' . $partial, $data);
    }
}
