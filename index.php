<?php
/**
 * easyPublisher - Ein minimalistischer Markdown-Publisher für iA Writer
 */

// Fehleranzeige für die Entwicklung (in Produktion auskommentieren oder entfernen)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Composer Autoloader
require 'vendor/autoload.php';

// Konfiguration
define('BASE_PATH', __DIR__);
define('CONTENT_PATH', BASE_PATH . '/content');
define('CORE_PATH', BASE_PATH . '/core');
define('THEME_PATH', BASE_PATH . '/theme');

// Hilfsfunktionen laden
require CORE_PATH . '/helpers/functions.php';

// Router initialisieren und ausführen
require CORE_PATH . '/classes/Router.php';
$router = new Router();
$router->route();
