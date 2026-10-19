<?php
// Данные сайта лежат в data/db.json — тот же формат, что и в Next.js-версии.

function db_path(): string
{
    return DATA_DIR . '/db.json';
}

function db(bool $fresh = false): array
{
    static $cache = null;
    if ($cache === null || $fresh) {
        $raw = is_file(db_path()) ? file_get_contents(db_path()) : '';
        $cache = $raw ? json_decode($raw, true) : null;
        if (!is_array($cache)) throw new RuntimeException('Не найден файл данных data/db.json');
        foreach (['categories', 'options', 'products', 'projects', 'reviews', 'faqs', 'posts', 'news', 'leads'] as $key) {
            if (!isset($cache[$key]) || !is_array($cache[$key])) $cache[$key] = [];
        }
        // Разовые обновления данных (app/lib/migrations.php) — под той же блокировкой, что и сохранение.
        static $migrating = false;
        if (!$migrating && migrations_pending($cache)) {
            $migrating = true;
            db_update(function ($data) {
                return migrations_pending($data) ? run_migrations($data) : $data;
            });
            $migrating = false;
        }
    }
    return $cache;
}

// Изменение данных под блокировкой: два одновременных сохранения не затрут друг друга.
function db_update(callable $change): array
{
    $lock = fopen(DATA_DIR . '/db.lock', 'c');
    flock($lock, LOCK_EX);
    try {
        $data = db(true);
        $result = $change($data);
        if (is_array($result)) $data = $result;
        $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        if ($json === false) throw new RuntimeException('Не удалось сохранить данные');
        $tmp = db_path() . '.tmp';
        file_put_contents($tmp, $json);
        rename($tmp, db_path());
        db(true);
        return $data;
    } finally {
        flock($lock, LOCK_UN);
        fclose($lock);
    }
}

function settings(): array
{
    return db()['settings'] ?? [];
}

function setting(string $key): string
{
    return (string) (settings()[$key] ?? '');
}

function published_products(): array
{
    return array_values(array_filter(db()['products'], function ($item) {
        return !empty($item['published']);
    }));
}

function products_by_category(string $slug): array
{
    return array_values(array_filter(published_products(), function ($item) use ($slug) {
        return $item['categorySlug'] === $slug;
    }));
}

function min_price(array $items): ?int
{
    if (!$items) return null;
    return (int) min(array_column($items, 'price'));
}

function published_categories(): array
{
    $items = array_values(array_filter(db()['categories'], function ($item) {
        return !empty($item['published']);
    }));
    usort($items, function ($a, $b) {
        return ($a['sort'] ?? 0) <=> ($b['sort'] ?? 0);
    });
    return $items;
}

function find_by(array $items, string $key, string $value): ?array
{
    foreach ($items as $item) {
        if (($item[$key] ?? null) === $value) return $item;
    }
    return null;
}

function category(string $slug): ?array
{
    return find_by(db()['categories'], 'slug', $slug);
}

function is_category_slug(string $value): bool
{
    return in_array($value, category_paths(), true);
}

function category_paths(): array
{
    return ['tualety', 'hozbloki', 'kupeli', 'dushi'];
}

function category_labels(): array
{
    return cities_data()['categoryLabels'];
}

function cities_data(): array
{
    static $data = null;
    if ($data === null) $data = json_decode(file_get_contents(APP_DIR . '/cities.json'), true);
    return $data;
}

function cities(): array
{
    return cities_data()['cities'];
}

function get_city(string $slug): ?array
{
    return find_by(cities(), 'slug', $slug);
}
