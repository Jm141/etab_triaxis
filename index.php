<?php
/**
 * Application Entry Point
 */

// Start session
require_once __DIR__ . '/core/Session.php';
Session::start();

// Autoload core classes
spl_autoload_register(function ($class) {
    $file = __DIR__ . '/core/' . $class . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// Load configuration
$config = require __DIR__ . '/config/app.php';
date_default_timezone_set($config['timezone']);

// Initialize router
require_once __DIR__ . '/core/Router.php';
$router = new Router($config['base_url']);

// Load routes
require_once __DIR__ . '/routes/web.php';

// Dispatch
$router->dispatch();



