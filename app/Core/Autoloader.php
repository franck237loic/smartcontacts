<?php

namespace App\Core;

/**
 * Autoloader Class
 * Handles automatic class loading
 */
class Autoloader
{
    /**
     * Register the autoloader
     */
    public static function register()
    {
        spl_autoload_register([__CLASS__, 'autoload']);
    }

    /**
     * Autoload classes
     */
    public static function autoload($class)
    {
        // Remove the namespace prefix
        $prefix = 'App\\';
        $baseDir = __DIR__ . '/../';

        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) {
            return;
        }

        $relativeClass = substr($class, $len);
        $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

        if (file_exists($file)) {
            require $file;
        }
    }
}
