<?php
// Точка входа сайта. Код и данные лежат рядом с public_html, в папках app/ и data/, и из интернета недоступны.
define('SITE_ROOT', __DIR__);

// Встроенный сервер PHP (только для проверки на компьютере): отдаём файлы как есть.
if (PHP_SAPI === 'cli-server') {
    $path = rawurldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/');
    if (preg_match('#^/api/media/([\w.-]+)$#', $path, $m)) $path = '/uploads/' . $m[1];
    $file = __DIR__ . $path;
    if ($path !== '/' && is_file($file) && strpos(realpath($file), realpath(__DIR__)) === 0) {
        if ($path !== '/index.php') {
            if (strpos($_SERVER['REQUEST_URI'], '/api/media/') === 0) {
                header('Content-Type: ' . (mime_content_type($file) ?: 'application/octet-stream'));
                readfile($file);
                return true;
            }
            return false;
        }
    }
}

require dirname(__DIR__) . '/app/bootstrap.php';
