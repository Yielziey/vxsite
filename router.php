<?php
// filename: router.php

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if ($path !== '/' && file_exists(__DIR__ . $path)) {
    return false;
}
$php_file = __DIR__ . $path;
if (is_dir($php_file)) {
    if (file_exists($php_file . '/index.php')) {
        $php_file .= '/index.php';
    } elseif (file_exists($php_file . '/main.php')) {
        $php_file .= '/main.php';
    }
}
if (strpos(basename($php_file), '.') === false) {
     $php_file .= '.php';
}

if (file_exists($php_file) && is_file($php_file)) {
    require_once $php_file;
} else {
    http_response_code(404);
    require_once __DIR__ . '/404.php';
}
?>