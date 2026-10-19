<?php
// Маршрутизация: какой адрес какую страницу открывает.

define('APP_DIR', __DIR__);
define('DATA_DIR', dirname(__DIR__) . '/data');

mb_internal_encoding('UTF-8');
date_default_timezone_set('Europe/Moscow');

foreach (['helpers', 'db', 'migrations', 'seo', 'geo', 'options', 'richtext', 'components', 'forms'] as $lib) require APP_DIR . "/lib/$lib.php";
foreach (['public', 'geo', 'content'] as $pages) require APP_DIR . "/pages/$pages.php";

function render(array $meta, string $content, int $status = 200, array $opts = []): void
{
    http_response_code($status);
    header('Content-Type: text/html; charset=utf-8');
    echo layout($meta, $content, $opts);
}

function page_not_found(): void
{
    $html = '<div class="wrap page-hero"><h1>Страница не найдена</h1><p>Такого адреса нет. Откройте каталог или список городов области.</p><p><a href="/">На главную</a> · <a href="/katalog">Каталог</a></p></div>';
    render(['title' => 'Страница не найдена — Загородный дом', 'description' => '', 'index' => false], $html, 404);
}

function dispatch(): void
{
    $path = request_path();
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

    if (config('force_https') && PHP_SAPI !== 'cli-server') {
        $host = $_SERVER['HTTP_HOST'] ?? '';
        if (!is_https() || starts_with($host, 'www.')) {
            redirect('https://' . preg_replace('/^www\./', '', $host) . ($_SERVER['REQUEST_URI'] ?? '/'), 301);
        }
    }

    // Адреса без слеша в конце, как в Next.js-версии
    if ($path !== '/' && substr($path, -1) === '/') {
        $qs = $_SERVER['QUERY_STRING'] ?? '';
        redirect(rtrim($path, '/') . ($qs ? "?$qs" : ''), 301);
    }

    if ($path === '/robots.txt') { robots_txt(); return; }
    if ($path === '/sitemap.xml') { sitemap_xml(); return; }

    if ($method === 'POST') {
        if ($path === '/form/lead') { submit_lead(); return; }
        if ($path === '/form/review') { submit_review(); return; }
    }

    // Админка в публичную версию не входит.

    $static = [
        '/' => 'page_home',
        '/katalog' => 'page_catalog',
        '/goroda' => 'page_cities',
        '/dostavka' => 'page_delivery',
        '/kak-zakazat' => 'page_how_to_order',
        '/o-nas' => 'page_about',
        '/individualnye-razmery' => 'page_custom',
        '/kontakty' => 'page_contacts',
        '/obratnaya-svyaz' => 'page_feedback',
        '/spasibo' => 'page_thanks',
        '/garantiya' => 'page_warranty',
        '/faq' => 'page_faq',
        '/otzyvy' => 'page_reviews',
        '/blog' => 'page_blog',
        '/akcii' => 'page_news',
        '/proekty' => 'page_projects',
    ];
    if (isset($static[$path])) { $static[$path](); return; }

    $parts = explode('/', trim($path, '/'));
    if ($parts[0] === 'blog' && count($parts) === 2) { page_post($parts[1]); return; }
    if ($parts[0] === 'akcii' && count($parts) === 2) { page_news_item($parts[1]); return; }
    if ($parts[0] === 'proekty' && count($parts) === 2) { page_project($parts[1]); return; }
    if ($parts[0] === 'goroda' && count($parts) === 2) { page_city($parts[1]); return; }
    if ($parts[0] === 'goroda' && count($parts) === 3) { page_city_category($parts[1], $parts[2]); return; }
    if (count($parts) === 1 && is_category_slug($parts[0])) { page_category($parts[0]); return; }
    if (count($parts) === 2 && is_category_slug($parts[0])) { page_product($parts[0], $parts[1]); return; }

    not_found();
}

try {
    dispatch();
} catch (NotFound $e) {
    page_not_found();
}
