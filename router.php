<?php

/**
 * Router for PHP built-in server
 * This file tells the built-in server to route all requests through Laravel
 * except for actual static files (CSS, JS, images in public/)
 */

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// If the request is for an actual file in public/, serve it
if ($uri !== '/' && file_exists(__DIR__ . '/public' . $uri)) {
    return false;
}

// Otherwise, route through Laravel
require_once __DIR__ . '/public/index.php';
