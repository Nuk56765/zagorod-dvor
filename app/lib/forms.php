<?php
// Формы сайта: заявки и отзывы. Плюс robots.txt и карта сайта.

function submit_lead(): void
{
    $back = safe_back(post('back') ?: '/obratnaya-svyaz');
    if (post('company_site') !== '') redirect('/spasibo');
    $phone = mb_substr(post('phone'), 0, 40);
    if (strlen(preg_replace('/\D/', '', $phone)) < 10) {
        redirect($back . '?error=phone&form=' . rawurlencode(preg_replace('/[^a-z-]/', '', post('form'))));
    }
    $clip = function ($key, $len = 200) {
        return mb_substr(post($key), 0, $len);
    };
    $lead = [
        'id' => 'lead-' . round(microtime(true) * 1000),
        'createdAt' => gmdate('Y-m-d\TH:i:s.v\Z'),
        'kind' => $clip('kind', 30) ?: 'feedback',
        'name' => $clip('name', 80),
        'phone' => $phone,
        'city' => $clip('city', 80),
        'productType' => $clip('productType', 120),
        'size' => $clip('size', 80),
        'message' => implode("\n", array_filter([option_summary(), $clip('message', 2000)], 'strlen')),
        'page' => $clip('page', 200),
        'status' => 'new',
    ];
    db_update(function ($db) use ($lead) {
        array_unshift($db['leads'], $lead);
        return $db;
    });
    notify_lead($lead);
    redirect('/spasibo');
}

function notify_lead(array $lead): void
{
    $to = (string) config('notify_email', '');
    if (!looks_like_email($to)) return;
    $lines = [
        'Новая заявка с сайта ' . site_url(),
        '',
        'Имя: ' . ($lead['name'] ?: '—'),
        'Телефон: ' . $lead['phone'],
        'Город: ' . ($lead['city'] ?: '—'),
        'Что нужно: ' . ($lead['productType'] ?: '—'),
        'Размер: ' . ($lead['size'] ?: '—'),
        'Страница: ' . $lead['page'],
        '',
        $lead['message'],
        '',
        'Все заявки: ' . site_url() . '/admin/leads',
    ];
    $host = parse_url(site_url(), PHP_URL_HOST) ?: 'localhost';
    $headers = "From: =?UTF-8?B?" . base64_encode('Сайт ' . $host) . "?= <no-reply@$host>\r\nContent-Type: text/plain; charset=UTF-8";
    @mail($to, '=?UTF-8?B?' . base64_encode('Заявка: ' . $lead['phone']) . '?=', implode("\n", $lines), $headers);
}

function submit_review(): void
{
    $back = safe_back(post('back') ?: '/otzyvy', '/otzyvy');
    if (post('company_site') !== '') redirect($back . '#otzyv');
    $author = mb_substr(post('author'), 0, 80);
    $text = mb_substr(post('text'), 0, 2000);
    $rating = (int) post('rating');
    $slug = post('productSlug');
    $known = $slug === '' || find_by(db()['products'], 'slug', $slug);
    if (mb_strlen($author) < 2 || mb_strlen($text) < 10 || $rating < 1 || $rating > 5 || !$known) redirect($back . '?review=error#otzyv');
    db_update(function ($db) use ($author, $text, $rating, $slug) {
        array_unshift($db['reviews'], [
            'id' => 'rev-' . round(microtime(true) * 1000),
            'author' => $author,
            'city' => mb_substr(post('city'), 0, 80),
            'text' => $text,
            'productSlug' => $slug,
            'rating' => $rating,
            'demo' => false,
            'published' => false,
        ]);
        return $db;
    });
    redirect($back . '?review=sent#otzyv');
}

function robots_txt(): void
{
    header('Content-Type: text/plain; charset=utf-8');
    $site = site_url();
    echo "User-Agent: *\nAllow: /\nDisallow: /admin\nDisallow: /form\nDisallow: /spasibo\n\nHost: $site\nSitemap: $site/sitemap.xml\n";
}

function sitemap_xml(): void
{
    $site = site_url();
    $urls = [];
    $add = function ($path, $freq, $priority) use (&$urls, $site) {
        $urls[] = ['loc' => $site . ($path ?: '/'), 'freq' => $freq, 'priority' => $priority];
    };
    foreach (['', '/katalog', '/goroda', '/proekty', '/o-nas', '/individualnye-razmery', '/otzyvy', '/faq', '/dostavka', '/kontakty', '/blog', '/akcii', '/garantiya', '/kak-zakazat', '/obratnaya-svyaz'] as $path) {
        $add($path, 'weekly', $path === '' ? '1.0' : '0.6');
    }
    foreach (published_categories() as $cat) $add('/' . $cat['slug'], 'weekly', '0.9');
    foreach (published_products() as $p) $add(product_url($p), 'weekly', '0.8');
    foreach (cities() as $city) {
        $add('/goroda/' . $city['slug'], 'monthly', '0.7');
        foreach (category_paths() as $slug) $add('/goroda/' . $city['slug'] . '/' . $slug, 'monthly', '0.6');
    }
    foreach (db()['posts'] as $post) if (!empty($post['published'])) $add('/blog/' . $post['slug'], 'monthly', '0.5');
    foreach (db()['news'] as $item) if (!empty($item['published'])) $add('/akcii/' . $item['slug'], 'monthly', '0.4');
    foreach (db()['projects'] as $project) if (!empty($project['published'])) $add('/proekty/' . $project['slug'], 'monthly', '0.4');
    header('Content-Type: application/xml; charset=utf-8');
    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
    foreach ($urls as $u) {
        echo '<url><loc>' . e($u['loc']) . '</loc><changefreq>' . $u['freq'] . '</changefreq><priority>' . $u['priority'] . "</priority></url>\n";
    }
    echo '</urlset>';
}
