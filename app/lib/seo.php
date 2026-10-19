<?php
// Мета-теги, адрес сайта и микроразметка.

function is_local_url(string $url): bool
{
    return (bool) preg_match('#//(localhost|127\.0\.0\.1)(:|/|$)#', $url);
}

function site_url(): string
{
    $fromConfig = rtrim((string) config('site_url', ''), '/');
    $fromDb = rtrim(setting('siteUrl'), '/');
    // Адрес из админки главнее, но localhost из черновой базы не должен попасть на боевой домен.
    if ($fromDb && preg_match('#^https?://#', $fromDb) && !(is_local_url($fromDb) && $fromConfig && !is_local_url($fromConfig))) return $fromDb;
    if ($fromConfig) return $fromConfig;
    $scheme = is_https() ? 'https' : 'http';
    return $scheme . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
}

function is_https(): bool
{
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') return true;
    if (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https') return true;
    if (($_SERVER['HTTP_X_FORWARDED_SSL'] ?? '') === 'on') return true;
    return ($_SERVER['SERVER_PORT'] ?? '') === '443';
}

// В SEO-полях из админки можно писать {цена} — подставится актуальная цена.
function fill_price(string $text, ?int $price): string
{
    $value = $price ? format_price($price) : '';
    $out = preg_replace_callback('/\s*(от\s+)?\{цена\}/u', function ($m) use ($value) {
        return $value ? ' ' . ($m[1] ?? '') . $value : '';
    }, $text);
    return trim($out);
}

function fit_title(string $title): string
{
    $len = function ($s) {
        return mb_strlen($s, 'UTF-8');
    };
    $branded = contains($title, 'Загородный дом') ? $title : $title . ' — Загородный дом';
    if ($len($branded) >= 50 && $len($branded) <= 65) return $branded;
    if ($len($title) >= 50 && $len($title) <= 65) return $title;
    if ($len($branded) <= 68) return $branded;
    if ($len($title) <= 68) return $title;
    return preg_replace('/\s+\S*$/u', '', mb_substr($branded, 0, 64, 'UTF-8'));
}

function fit_description(string $text): string
{
    $value = trim(preg_replace('/\s+/u', ' ', $text));
    $extra = ' Доставка по Владимирской области.';
    $len = mb_strlen($value, 'UTF-8');
    if ($len < 140 && $len + mb_strlen($extra, 'UTF-8') <= 165) $value .= $extra;
    if (mb_strlen($value, 'UTF-8') > 165) {
        $value = trim(preg_replace('/\s+\S*$/u', '', mb_substr($value, 0, 157, 'UTF-8'))) . '…';
    }
    return $value;
}

function page_meta(string $title, string $description, string $path, array $opts = []): array
{
    return [
        'title' => fit_title($title),
        'description' => fit_description($description),
        'canonical' => site_url() . $path,
        'type' => $opts['type'] ?? 'website',
        'index' => $opts['index'] ?? true,
    ];
}

function json_ld(array $data): string
{
    $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    return '<script type="application/ld+json">' . str_replace('<', '<', $json) . '</script>';
}

function faq_json_ld(array $items): string
{
    return json_ld([
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => array_map(function ($item) {
            return ['@type' => 'Question', 'name' => $item['q'], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $item['a']]];
        }, array_values($items)),
    ]);
}

function organization_json(): array
{
    $s = settings();
    $address = (string) ($s['address'] ?? '');
    $real = $address && !contains(lower($address), 'укажите');
    $data = [
        '@context' => 'https://schema.org',
        '@type' => 'LocalBusiness',
        'name' => $s['companyName'] ?? 'Загородный дом',
        'url' => site_url(),
        'description' => 'Деревянные дачные туалеты, хозблоки, купели и души под заказ с доставкой готовых построек по Владимирской области.',
        'areaServed' => ['@type' => 'AdministrativeArea', 'name' => 'Владимирская область'],
        'address' => array_merge(['@type' => 'PostalAddress', 'addressLocality' => 'Муром', 'addressRegion' => 'Владимирская область', 'addressCountry' => 'RU'], $real ? ['streetAddress' => $address] : []),
        'openingHours' => 'Mo-Su 09:00-21:00',
        'priceRange' => 'от ' . format_price(min_price(published_products()) ?: 16990),
    ];
    if (tel_href((string) ($s['phone'] ?? ''))) $data['telephone'] = $s['phone'];
    return $data;
}
