<?php
// Тексты страниц городов под запросы вида «купить дачный туалет в Муроме».

function category_keys(): array
{
    return [
        'tualety' => ['buy' => 'Купить дачный туалет', 'plural' => 'Дачные туалеты', 'thing' => 'деревянный туалет для дачи', 'accusative' => 'дачный туалет'],
        'hozbloki' => ['buy' => 'Купить хозблок для дачи', 'plural' => 'Хозблоки для дачи', 'thing' => 'деревянный хозблок', 'accusative' => 'хозблок'],
        'kupeli' => ['buy' => 'Купить купель для дачи', 'plural' => 'Купели для бани и дачи', 'thing' => 'деревянную купель', 'accusative' => 'купель'],
        'dushi' => ['buy' => 'Купить летний душ для дачи', 'plural' => 'Летние души для дачи', 'thing' => 'летний душ из дерева', 'accusative' => 'летний душ'],
    ];
}

function from_price(?int $price): string
{
    return $price ? ' от ' . format_price($price) : '';
}

function city_category_copy(array $city, string $slug, ?int $price, int $models): array
{
    $zone = delivery_zone((int) $city['km']);
    $key = category_keys()[$slug];
    $labels = category_labels();
    $label = lower($labels[$slug]);
    $in = $city['inCity'];
    $from = from_price($price);
    $lead = "{$key['buy']} {$in}{$from}: изготавливаем {$key['thing']} под заказ и привозим готовым — собирать на участке ничего не нужно. "
        . ($models ? "Моделей в каталоге: {$models}, а" : 'А') . ' любой другой размер сделаем по заявке.';
    $paragraphs = [
        "Ищете, где купить {$key['accusative']} {$in}? Мы делаем каждую постройку после заявки: под ваш размер, сторону двери и подъезд к участку, — и доставляем готовой {$in} и по всему району. Цена" . ($from ?: ' — по заявке') . ', точную сумму с доставкой называем до начала изготовления.',
        $city['note'],
        "Доставка {$in}: {$city['road']} По сроку закладываем так: {$city['days']}. Расстояние: " . distance_text($city) . " — {$zone['name']}, {$zone['fee']}.",
        "Где чаще ставим: {$city['districts']}",
        "Грунт и основание: {$city['ground']} {$city['hints'][$slug]}",
        "Подъезд: {$city['access']} Постройку привозим целиком, поэтому приложите к заявке фото ворот — без него не обещаем манипулятор. Если проезд узкий, сделаем габарит под ваши ворота.",
    ];
    $faqs = [
        ['q' => "Сколько стоит {$key['accusative']} {$in}?", 'a' => "{$labels[$slug]} {$in} —" . ($from ?: ' цена по заявке') . " за типовую модель. Доставка: {$zone['fee']}. Итог с опциями и доставкой называем до изготовления."],
        ['q' => "За сколько привезёте {$label} {$in}?", 'a' => "{$city['days']}. Дорога: {$city['road']}"],
        ['q' => "Проедет ли машина с готовой постройкой {$in}?", 'a' => $city['access']],
        ['q' => "На какое основание ставить {$label} {$in}?", 'a' => "{$city['ground']} {$city['hints'][$slug]}"],
    ];
    return [
        'h1' => "{$key['plural']} {$in} — под заказ с доставкой",
        'title' => "{$key['buy']} {$in}{$from}",
        'description' => "{$key['buy']} {$in}{$from}. Изготовление под заказ, доставка готовой постройки: {$zone['fee']}. Модели в каталоге и свой размер по заявке.",
        'lead' => $lead,
        'paragraphs' => $paragraphs,
        'faqs' => $faqs,
    ];
}

function city_hub_copy(array $city, ?int $price): array
{
    $zone = delivery_zone((int) $city['km']);
    $in = $city['inCity'];
    $from = from_price($price);
    return [
        'title' => "Дачные постройки {$in} под заказ{$from}",
        'h1' => "Дачные постройки под заказ {$in}",
        'description' => "Дачные туалеты, хозблоки, души и купели {$in}{$from}. Изготавливаем под заказ, привозим готовыми: {$zone['fee']}. Подъезд, грунт и сроки — на странице.",
        'paragraphs' => [
            "Купить дачный туалет, хозблок, летний душ или купель {$in} можно с доставкой готовой постройки прямо на участок. Мы изготавливаем всё под заказ — под ваш размер и подъезд, — поэтому на даче ничего не собирают.",
            $city['note'],
            "Как едем: {$city['road']} Срок: {$city['days']}. Расстояние: " . distance_text($city) . ", {$zone['name']}, {$zone['fee']}.",
            "Районы и типичные адреса: {$city['districts']}",
            "Грунт: {$city['ground']}",
            "Разгрузка: {$city['access']}",
        ],
    ];
}
