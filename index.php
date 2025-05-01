<?php
/**
 * easyPublisher - A minimalist Markdown publisher for Markdown files
 */

// Error display for development (comment out or remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Composer Autoloader
require 'vendor/autoload.php';

// Configuration
define('BASE_PATH', __DIR__);
define('CONTENT_PATH', BASE_PATH . '/content');
define('CORE_PATH', BASE_PATH . '/core');
define('THEME_PATH', BASE_PATH . '/theme');

// Load helper functions
require CORE_PATH . '/helpers/functions.php';

// Initialize and run the router
require CORE_PATH . '/classes/Router.php';
$router = new Router();
$router->route();
