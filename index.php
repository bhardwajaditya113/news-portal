<?php
/**
 * Laravel Bootstrap for Azure App Service
 * This file redirects all requests to Laravel's public/index.php
 */

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/'
);

// If request is for a file that exists in public, serve it
$publicPath = __DIR__.'/public'.$uri;
if ($uri !== '/' && file_exists($publicPath) && !is_dir($publicPath)) {
    return false; // Let web server handle it
}

// Bootstrap Laravel
require_once __DIR__.'/public/index.php';
