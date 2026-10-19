<?php
// Опции товара (материал, окна) и их доплаты.

function sort_options(array $options): array
{
    usort($options, function ($a, $b) {
        return (($a['sort'] ?? 0) <=> ($b['sort'] ?? 0)) ?: strcmp($a['title'], $b['title']);
    });
    return $options;
}

function visible_options(array $product): array
{
    $excluded = $product['excludedOptionIds'] ?? [];
    return sort_options(array_values(array_filter(db()['options'], function ($option) use ($excluded) {
        return !empty($option['published']) && !in_array($option['id'], $excluded, true) && !empty($option['choices']);
    })));
}

function starting_extra(array $options): int
{
    $sum = 0;
    foreach ($options as $option) {
        if ($option['kind'] === 'choice') $sum += (int) ($option['choices'][0]['price'] ?? 0);
    }
    return $sum;
}

function price_delta(int $price): string
{
    if ($price === 0) return 'в цене';
    return $price > 0 ? '+' . format_price($price) : format_price($price);
}

function choices_to_text(array $choices): string
{
    return implode("\n", array_map(function ($choice) {
        return $choice['label'] . ' | ' . $choice['price'];
    }, $choices));
}

function parse_choices(string $raw): array
{
    $taken = [];
    $choices = [];
    foreach (preg_split('/\r?\n/', $raw) as $line) {
        $line = trim($line);
        if ($line === '') continue;
        $parts = explode('|', $line);
        $label = mb_substr(trim(array_shift($parts)), 0, 80, 'UTF-8');
        if ($label === '') continue;
        $id = unique_slug($label, $taken);
        $taken[] = $id;
        $value = str_replace([' ', ',', '−'], ['', '.', '-'], implode('|', $parts));
        $price = is_numeric($value) ? max(-1000000, min(1000000, (int) round((float) $value))) : 0;
        $choices[] = ['id' => $id, 'label' => $label, 'price' => $price];
        if (count($choices) >= 30) break;
    }
    return $choices;
}

// Какие опции выбрал покупатель — текстом в заявку.
function option_summary(): string
{
    $lines = [];
    foreach (db()['options'] as $option) {
        $ids = post_list('opt_' . $option['id']);
        if (!$ids) continue;
        $picked = array_values(array_filter($option['choices'], function ($choice) use ($ids, $option) {
            return $option['kind'] === 'choice' ? $choice['id'] === $ids[0] : in_array($choice['id'], $ids, true);
        }));
        if ($option['kind'] === 'choice') $picked = array_slice($picked, 0, 1);
        if (!$picked) continue;
        $labels = array_map(function ($choice) {
            return $choice['price'] ? $choice['label'] . ' (' . price_delta((int) $choice['price']) . ')' : $choice['label'];
        }, $picked);
        $lines[] = $option['title'] . ': ' . implode(', ', $labels);
    }
    return implode("\n", $lines);
}
