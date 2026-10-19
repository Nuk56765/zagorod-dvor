<?php
// Города: список, страница города и «раздел в городе».

function page_cities(): void
{
    $directions = [
        ['title' => 'Муром и рядом', 'text' => 'Здесь мы изготавливаем постройки, поэтому по Мурому и Меленкам возим быстрее и дешевле всего.', 'slugs' => ['murom', 'melenki']],
        ['title' => 'Север и северо-восток', 'text' => 'Вязники и Гороховец часто везём одной машиной, Ковров — вместе с Камешково.', 'slugs' => ['vyazniki', 'gorokhovets', 'kovrov', 'kameshkovo']],
        ['title' => 'Трасса на Владимир', 'text' => 'Судогда стоит на нашей дороге к Владимиру. Гусь-Хрустальный берём через Меленки, Радужный и Суздаль — вместе с владимирским рейсом.', 'slugs' => ['sudogda', 'gus-hrustalny', 'vladimir', 'raduzhny', 'suzdal']],
        ['title' => 'Запад по М-7', 'text' => 'Собинка и Лакинск, Петушки и Покров — пары, которые удобно везти одним рейсом. В пятницу вечером трасса стоит, окно лучше на утро.', 'slugs' => ['sobinka', 'lakinsk', 'petushki', 'pokrov', 'kirzhach']],
        ['title' => 'Северо-запад и Ополье', 'text' => 'Самое длинное плечо от Мурома. Александров, Карабаново и Струнино — один рейс, не три отдельные поездки.', 'slugs' => ['yurev-polsky', 'kolchugino', 'aleksandrov', 'karabanovo', 'strunino']],
    ];
    ob_start(); ?>
<div class="wrap page-hero">
  <?= breadcrumbs([['name' => 'Города', 'href' => '/goroda']]) ?>
  <h1>Доставка дачных построек по Владимирской области</h1>
  <p class="lead">Изготавливаем деревянные туалеты, хозблоки, души и купели под заказ в Муроме и привозим готовыми в любой город области. Цвет точки на карте — пояс доставки. Нажмите на город, чтобы открыть его страницу с ценами, сроком доставки, подъездом и грунтом.</p>
  <?= region_map() ?>
  <?php foreach ($directions as $direction): ?>
  <section>
    <h2><?= e($direction['title']) ?></h2>
    <p><?= e($direction['text']) ?></p>
    <ul>
      <?php foreach ($direction['slugs'] as $slug): $city = get_city($slug); if (!$city) continue; $zone = delivery_zone((int) $city['km']); ?>
      <li><a href="/goroda/<?= e($slug) ?>"><?= e($city['name']) ?></a> — <?= e(distance_text($city)) ?>, <?= e($zone['name']) ?>, <?= e($zone['fee']) ?>.
        <a href="/goroda/<?= e($slug) ?>/tualety">Туалеты</a>, <a href="/goroda/<?= e($slug) ?>/hozbloki">хозблоки</a>, <a href="/goroda/<?= e($slug) ?>/dushi">души</a>, <a href="/goroda/<?= e($slug) ?>/kupeli">купели</a></li>
      <?php endforeach; ?>
    </ul>
  </section>
  <?php endforeach; ?>
  <p><a href="/dostavka">Как читается сетка доставки</a></p>
</div>
<?php
    render(page_meta('Доставка дачных построек из Мурома по Владимирской области', 'Дачные туалеты, хозблоки, души и купели под заказ: изготавливаем в Муроме и привозим готовыми в 21 город Владимирской области. Карта доставки, сроки и стоимость.', '/goroda'), ob_get_clean());
}

function page_city(string $slug): void
{
    $city = get_city($slug);
    if (!$city) not_found();
    $copy = city_hub_copy($city, min_price(published_products()));
    $zone = delivery_zone((int) $city['km']);
    $in = $city['inCity'];
    $faqs = [
        ['q' => "За сколько дней привезёте {$in}?", 'a' => "{$city['days']}. {$city['road']}"],
        ['q' => "Сколько стоит доставка {$in}?", 'a' => ucfirst_utf8($zone['name']) . ": {$zone['fee']}. Точную сумму называем в ответе на заявку."],
        ['q' => "Какой грунт учитывать {$in}?", 'a' => $city['ground']],
        ['q' => "Нужен ли манипулятор {$in}?", 'a' => $city['access']],
    ];
    $labels = category_labels();
    $keys = category_keys();
    ob_start(); ?>
<article class="page-hero">
  <div class="wrap">
    <?= breadcrumbs([['name' => 'Города', 'href' => '/goroda'], ['name' => $city['name'], 'href' => "/goroda/$slug"]]) ?>
    <h1><?= e($copy['h1']) ?></h1>
    <p class="lead"><?= e($copy['paragraphs'][0]) ?></p>
    <div class="grid-4 cat-strip" style="margin-top:1.4rem">
      <?php foreach (category_paths() as $key): $price = min_price(products_by_category($key)); ?>
      <a href="/goroda/<?= e($slug) ?>/<?= e($key) ?>" class="card cat-tile">
        <?php $keyCat = category($key); ?><?= $keyCat && !empty($keyCat['photos'][0]) ? category_media($keyCat, $labels[$key] . ' ' . $in, 'cat-photo') : illustration($key, $labels[$key] . ' ' . $in) ?>
        <div class="body">
          <h3><?= e($labels[$key] . ' ' . $in) ?></h3>
          <p class="desc"><?= e($city['hints'][$key]) ?></p>
          <?php if ($price): ?><p class="price">от <?= format_price($price) ?></p><?php endif; ?>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>

  <section class="section section-alt">
    <div class="wrap text-col">
      <h2>Доставка <?= e($in) ?></h2>
      <?php foreach (array_slice($copy['paragraphs'], 1) as $paragraph): ?><p><?= e($paragraph) ?></p><?php endforeach; ?>
      <p>Сетка тарифа черновая, точную цифру подтверждаем в заявке. <a href="/dostavka">Общая страница доставки</a>.</p>
      <?= region_map($slug) ?>
      <?= price_note() ?>
    </div>
  </section>

  <section class="section wrap">
    <h2>Вопросы <?= e($in) ?></h2>
    <?= faq_list($faqs) ?>
    <?= faq_json_ld($faqs) ?>
    <h2>Что ищут <?= e($in) ?></h2>
    <p class="chips"><?php foreach (category_paths() as $key): ?><a href="/goroda/<?= e($slug) ?>/<?= e($key) ?>"><?= e(lower($keys[$key]['buy']) . ' ' . $in) ?></a><?php endforeach; ?></p>
    <h2>Соседние города</h2>
    <p class="chips"><?php foreach ($city['neighbors'] as $n): $neighbor = get_city($n); if ($neighbor): ?><a href="/goroda/<?= e($n) ?>"><?= e($neighbor['name']) ?></a><?php endif; endforeach; ?></p>
  </section>

  <section class="section section-leaf">
    <div class="wrap custom-cta">
      <div>
        <p class="kicker">Заявка</p>
        <h2>Заказать постройку с доставкой <?= e($in) ?></h2>
        <p class="lead">Напишите, что нужно, и оставьте телефон. Назовём цену с доставкой до начала изготовления.</p>
      </div>
      <?= lead_form(['id' => 'zakaz', 'kind' => 'order', 'page' => "/goroda/$slug", 'title' => "Заявка $in"]) ?>
    </div>
  </section>
</article>
<?php
    render(page_meta($copy['title'], $copy['description'], "/goroda/$slug"), ob_get_clean());
}

function page_city_category(string $citySlug, string $slug): void
{
    $city = get_city($citySlug);
    $cat = is_category_slug($slug) ? category($slug) : null;
    if (!$city || !$cat || empty($cat['published'])) not_found();
    $products = products_by_category($slug);
    $price = min_price($products);
    $copy = city_category_copy($city, $slug, $price, count($products));
    $in = $city['inCity'];
    $key = category_keys()[$slug];
    $labels = category_labels();
    $path = "/goroda/$citySlug/$slug";
    ob_start(); ?>
<article class="page-hero">
  <div class="wrap">
    <?= breadcrumbs([['name' => 'Города', 'href' => '/goroda'], ['name' => $city['name'], 'href' => "/goroda/$citySlug"], ['name' => $cat['title'], 'href' => $path]]) ?>
    <div class="cat-hero">
      <div>
        <h1><?= e($copy['h1']) ?></h1>
        <p class="lead"><?= e($copy['lead']) ?></p>
        <div class="facts">
          <?php if ($price): ?><p class="fact"><small>Цена</small><strong>от <?= format_price($price) ?></strong></p><?php endif; ?>
          <p class="fact"><small>Изготовление</small><strong>под заказ</strong></p>
          <p class="fact"><small>Доставка <?= e($in) ?></small><strong>готовой постройки</strong></p>
        </div>
        <div class="hero-actions">
          <a class="btn btn-primary btn-lg" href="#modeli">Смотреть модели</a>
          <a class="btn btn-line btn-lg" href="#zakaz">Заявка <?= e($in) ?></a>
        </div>
      </div>
      <div class="media-frame"><?= category_media($cat, $slug === 'hozbloki' ? "Деревянный хозблок для дачи — доставка $in" : $cat['title'] . ' ' . $in) ?></div>
    </div>
  </div>

  <section id="modeli" class="section section-alt">
    <div class="wrap">
      <?= section_head('Модели и цены', $cat['title'] . ' ' . $in, ['/' . $slug, 'Подробно о разделе']) ?>
      <?= product_grid($products) ?>
    </div>
  </section>

  <section class="section wrap">
    <div class="text-col">
      <h2><?= e($key['buy'] . ' ' . $in) ?></h2>
      <?php foreach ($copy['paragraphs'] as $paragraph): ?><p><?= e($paragraph) ?></p><?php endforeach; ?>
    </div>
    <?= price_note() ?>
    <h2>Цены <?= e($in) ?></h2>
    <?= price_table($products) ?>
  </section>

  <section class="section section-alt">
    <div class="wrap">
      <h2>Вопросы <?= e($in) ?></h2>
      <?= faq_list($copy['faqs']) ?>
      <?= faq_json_ld($copy['faqs']) ?>
      <?php if ($price): ?>
      <?= json_ld([
          '@context' => 'https://schema.org',
          '@type' => 'Product',
          'name' => $cat['title'] . ' ' . $in,
          'description' => $copy['description'],
          'url' => site_url() . $path,
          'offers' => [
              '@type' => 'AggregateOffer',
              'priceCurrency' => 'RUB',
              'lowPrice' => $price,
              'highPrice' => max(array_column($products, 'price')),
              'offerCount' => count($products),
              'availability' => 'https://schema.org/MadeToOrder',
              'areaServed' => $city['name'],
          ],
      ]) ?>
      <?php endif; ?>
    </div>
  </section>

  <section class="section wrap">
    <h2>Ещё <?= e($in) ?></h2>
    <p class="chips">
      <?php foreach (category_paths() as $other): if ($other === $slug) continue; ?><a href="/goroda/<?= e($citySlug) ?>/<?= e($other) ?>"><?= e($labels[$other] . ' ' . $in) ?></a><?php endforeach; ?>
      <a href="/goroda/<?= e($citySlug) ?>">Все постройки <?= e($in) ?></a>
    </p>
    <h2><?= e($cat['title']) ?> в соседних городах</h2>
    <p class="chips"><?php foreach ($city['neighbors'] as $n): $neighbor = get_city($n); if ($neighbor): ?><a href="/goroda/<?= e($n) ?>/<?= e($slug) ?>"><?= e($cat['title'] . ' ' . $neighbor['inCity']) ?></a><?php endif; endforeach; ?></p>
  </section>

  <section class="section section-leaf">
    <div class="wrap custom-cta">
      <div>
        <p class="kicker">Заявка</p>
        <h2><?= e($key['buy'] . ' ' . $in) ?></h2>
        <p class="lead">Оставьте телефон — перезвоним, уточним подъезд и грунт, назовём цену с доставкой <?= e($in) ?> до начала изготовления.</p>
      </div>
      <?= lead_form(['id' => 'zakaz', 'kind' => 'order', 'page' => $path, 'productType' => $cat['title'] . ', ' . $city['name'], 'title' => 'Заявка: ' . lower($cat['menu']) . ' ' . $in]) ?>
    </div>
  </section>
</article>
<?php
    render(page_meta($copy['title'], $copy['description'], $path), ob_get_clean());
}
