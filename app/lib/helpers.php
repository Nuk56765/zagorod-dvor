<?php
// Общие функции: экранирование, форматирование, адреса, ответы.

function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function starts_with(string $haystack, string $needle): bool
{
    return $needle === '' || strncmp($haystack, $needle, strlen($needle)) === 0;
}

function contains(string $haystack, string $needle): bool
{
    return $needle === '' || strpos($haystack, $needle) !== false;
}

function config(string $key, $default = null)
{
    static $config = null;
    if ($config === null) {
        $config = require APP_DIR . '/config.php';
        if (is_file(APP_DIR . '/config.local.php')) $config = array_merge($config, require APP_DIR . '/config.local.php');
    }
    return array_key_exists($key, $config) ? $config[$key] : $default;
}

function format_price($value): string
{
    return number_format((int) $value, 0, '', "\u{00A0}") . "\u{00A0}₽";
}

function format_date(string $iso): string
{
    $time = strtotime($iso);
    if ($time === false) return $iso;
    $months = ['января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря'];
    return date('j', $time) . ' ' . $months[(int) date('n', $time) - 1] . ' ' . date('Y', $time) . ' г.';
}

function phone_digits(string $phone): ?string
{
    $digits = preg_replace('/\D/', '', $phone);
    return strlen($digits) < 10 ? null : $digits;
}

function tel_href(string $phone): ?string
{
    $digits = phone_digits($phone);
    if (!$digits) return null;
    if (strlen($digits) === 10) return 'tel:+7' . $digits;
    if (strlen($digits) === 11 && $digits[0] === '8') return 'tel:+7' . substr($digits, 1);
    return 'tel:+' . $digits;
}

function looks_like_email(string $value): bool
{
    return (bool) preg_match('/^[^\s@]+@[^\s@]+\.[^\s@]+$/', $value);
}

function slugify(string $input): string
{
    $map = ['а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'e', 'ж' => 'zh', 'з' => 'z', 'и' => 'i',
        'й' => 'j', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't',
        'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c', 'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sch', 'ъ' => '', 'ы' => 'y', 'ь' => '',
        'э' => 'e', 'ю' => 'yu', 'я' => 'ya'];
    $out = strtr(mb_strtolower(trim($input), 'UTF-8'), $map);
    $out = trim(preg_replace('/[^a-z0-9]+/', '-', $out), '-');
    return substr($out, 0, 80);
}

function unique_slug(string $base, array $taken): string
{
    $root = slugify($base) ?: 'material';
    if (!in_array($root, $taken, true)) return $root;
    $n = 2;
    while (in_array("$root-$n", $taken, true)) $n++;
    return "$root-$n";
}

// Пояса доставки считаются от Мурома, где изготавливаем постройки.
function delivery_zones(): array
{
    return [
        ['max' => 60, 'name' => 'ближний пояс', 'fee' => 'ориентир от 2 500 ₽', 'label' => 'до 60 км'],
        ['max' => 150, 'name' => 'средний пояс', 'fee' => 'ориентир от 4 500 ₽', 'label' => 'до 150 км'],
        ['max' => 215, 'name' => 'дальний пояс', 'fee' => 'ориентир от 7 000 ₽', 'label' => 'до 215 км'],
        ['max' => PHP_INT_MAX, 'name' => 'самый дальний пояс', 'fee' => 'ориентир от 9 500 ₽', 'label' => 'дальше 215 км'],
    ];
}

function delivery_zone(int $km): array
{
    foreach (delivery_zones() as $i => $zone) {
        if ($km <= $zone['max']) return $zone + ['index' => $i];
    }
    return [];
}

function distance_text(array $city): string
{
    return (int) $city['km'] === 0 ? 'мы в этом городе' : 'около ' . (int) $city['km'] . ' км от Мурома';
}

function ucfirst_utf8(string $value): string
{
    return mb_strtoupper(mb_substr($value, 0, 1, 'UTF-8'), 'UTF-8') . mb_substr($value, 1, null, 'UTF-8');
}

function lower(string $value): string
{
    return mb_strtolower($value, 'UTF-8');
}

function request_path(): string
{
    $path = rawurldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
    return $path === '' ? '/' : $path;
}

function query(string $key, string $default = ''): string
{
    return isset($_GET[$key]) && is_string($_GET[$key]) ? trim($_GET[$key]) : $default;
}

function post(string $key, string $default = ''): string
{
    return isset($_POST[$key]) && is_string($_POST[$key]) ? trim($_POST[$key]) : $default;
}

function post_list(string $key): array
{
    $value = $_POST[$key] ?? [];
    return array_values(array_filter(array_map('strval', is_array($value) ? $value : [$value]), 'strlen'));
}

function post_flag(string $key): bool
{
    return post($key) === '1';
}

function post_money(string $key): int
{
    $value = str_replace([' ', ','], ['', '.'], post($key));
    return is_numeric($value) ? (int) round((float) $value) : 0;
}

function post_lines(string $key): array
{
    return array_values(array_filter(array_map('trim', preg_split('/\r?\n/', post($key))), 'strlen'));
}

function redirect(string $url, int $code = 303): void
{
    header('Location: ' . $url, true, $code);
    exit;
}

function safe_back(string $value, string $fallback = '/obratnaya-svyaz'): string
{
    if (!starts_with($value, '/') || starts_with($value, '//')) return $fallback;
    return explode('?', $value)[0] ?: $fallback;
}

class NotFound extends Exception
{
}

function not_found(): void
{
    throw new NotFound();
}

// 1 отзыв, 2 отзыва, 5 отзывов
function plural(int $n, string $one, string $few, string $many): string
{
    $n = abs($n) % 100;
    $last = $n % 10;
    if ($n > 10 && $n < 20) return $many;
    if ($last === 1) return $one;
    if ($last >= 2 && $last <= 4) return $few;
    return $many;
}

// Код подтверждения Вебмастера: если в настройки вставили тег целиком, берём только content="…".
function verification_code(string $value): string
{
    $value = trim($value);
    if (preg_match('/content\s*=\s*["\']([^"\']+)["\']/i', $value, $m)) $value = $m[1];
    return preg_replace('/[^A-Za-z0-9_\-]/', '', $value);
}
