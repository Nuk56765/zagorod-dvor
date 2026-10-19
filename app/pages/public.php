<?php
// Главная, каталог, разделы и карточки товаров.

function page_home(): void
{
    $db = db();
    $published = published_products();
    $popular = array_values(array_filter($published, function ($p) { return !empty($p['popular']); }));
    $reviews = array_slice(array_values(array_filter($db['reviews'], function ($r) { return !empty($r['published']); })), 0, 3);
    $faqs = array_values(array_filter($db['faqs'], function ($f) { return !empty($f['published']); }));
    usort($faqs, function ($a, $b) { return ($a['sort'] ?? 0) <=> ($b['sort'] ?? 0); });
    $faqs = array_slice($faqs, 0, 4);
    $projects = array_slice(array_values(array_filter($db['projects'], function ($p) { return !empty($p['published']); })), 0, 3);
    $from = min_price($published);
    $search = search_index();
    ob_start(); ?>
<section class="wrap hero">
  <div class="hero-copy">
    <p class="kicker">Муром · Владимирская область</p>
    <h1>Дачные постройки под заказ — привезём готовыми</h1>
    <p class="lead">Деревянные туалеты, хозблоки, летние души и купели для дачи. Изготавливаем под ваш размер и доставляем готовыми по всей Владимирской области — собирать на участке ничего не нужно.</p>
    <form class="hero-search" action="/katalog" method="get" role="search" data-site-search>
      <label class="visually-hidden" for="home-q">Поиск по товарам и разделам</label>
      <div class="search-box">
        <span class="hero-search-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/></svg></span>
        <input id="home-q" name="q" type="search" autocomplete="off" role="combobox" aria-expanded="false" aria-controls="home-suggest" aria-autocomplete="list" placeholder="Найти: туалет 1×1, хозблок с душем, купель…">
        <button class="btn btn-primary" type="submit">Найти</button>
        <ul class="search-suggest" id="home-suggest" role="listbox" hidden></ul>
      </div>
      <script type="application/json" data-search-index><?= str_replace('<', '<', json_encode($search, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) ?></script>
    </form>
    <div class="hero-actions">
      <a class="btn btn-primary btn-lg" href="/katalog">Смотреть каталог</a>
      <a class="btn btn-line btn-lg" href="/individualnye-razmery">Нужен свой размер</a>
    </div>
    <ul class="trust">
      <li>Только под заказ<span>размер, дверь и окно — под ваш участок</span></li>
      <li>Привозим готовыми<span>без сборки и мусора на даче</span></li>
      <li>Цену фиксируем заранее<span>до изготовления, не после привоза</span></li>
      <li>Доставка по всей области<span>21 город и другие населённые пункты</span></li>
    </ul>
  </div>
  <div class="hero-panel">
    <img class="hero-photo hero-photo-wide" src="/assets/hero-hozblok.jpg" alt="Деревянный хозблок с открытой дверью на дачном участке среди цветов" width="1080" height="598" fetchpriority="high">
    <?php if ($from): ?><p class="hero-tag"><small>Постройки для дачи</small><strong>от <?= format_price($from) ?></strong></p><?php endif; ?>
  </div>
</section>

<section class="section wrap">
  <?= section_head('Что делаем', 'Каталог', ['/katalog', 'Весь каталог']) ?>
  <div class="grid-4 cat-strip">
    <?php foreach (published_categories() as $cat): $price = min_price(products_by_category($cat['slug'])); ?>
    <a href="/<?= e($cat['slug']) ?>" class="card cat-tile">
      <?= !empty($cat['photos'][0]) || $cat['slug'] === 'hozbloki' ? category_media($cat, $cat['title'] . ' под заказ', 'cat-photo') : illustration($cat['slug'], $cat['title']) ?>
      <div class="body">
        <h3><?= e($cat['title']) ?></h3>
        <p class="desc"><?= e($cat['lead']) ?></p>
        <?php if ($price): ?><p class="price">от <?= format_price($price) ?></p><?php endif; ?>
      </div>
    </a>
    <?php endforeach; ?>
  </div>
</section>

<section class="section wrap">
  <?= section_head('Как мы работаем с дачниками', 'Спокойно и понятно') ?>
  <div class="why">
    <article class="paper"><?= icon('shield') ?><h3>Гарантия качества</h3><p>На каркас и узлы постройки закладываем понятный срок, но формулировку для договора вы правите сами: сейчас на странице гарантии лежит черновик, не готовый юридический текст.</p><a class="more" href="/garantiya">Условия гарантии</a></article>
    <article class="paper"><?= icon('phone') ?><h3>Постоянная поддержка</h3><p><?= e(setting('supportText')) ?></p><a class="more" href="/kontakty">Контакты</a></article>
    <article class="paper"><?= icon('truck') ?><h3>Доставка готовых построек</h3><p>Изготавливаем в Муроме и привозим постройку целиком по всей области — от Меленок и Вязников до Владимира, Суздаля и Александрова. На странице города — дорога, срок, подъезд и грунт.</p><p><a class="more" href="/dostavka">Сроки и тарифы</a></p><a class="more" href="/goroda">21 город области</a></article>
  </div>
</section>

<?php if ($popular): ?>
<section class="section section-leaf">
  <div class="wrap">
    <?= section_head('Популярные товары', 'Выбор покупателей', ['/katalog', 'Весь каталог']) ?>
    <div class="rail"><?= implode('', array_map('product_card', $popular)) ?></div>
  </div>
</section>
<?php endif; ?>

<section class="section section-wood">
  <div class="wrap">
    <?= section_head('Как сделать заказ', 'Четыре шага', ['/kak-zakazat', 'Подробный порядок']) ?>
    <div class="steps">
      <div class="step"><b>1</b> Выберите модель или напишите свой размер.</div>
      <div class="step"><b>2</b> Оставьте телефон и город.</div>
      <div class="step"><b>3</b> Уточняем подъезд, грунт и фиксируем цену.</div>
      <div class="step"><b>4</b> Изготавливаем и привозим постройку готовой.</div>
    </div>
  </div>
</section>

<?php if ($projects): ?>
<section class="section wrap">
  <?= section_head('Примеры проектов', 'На участках', ['/proekty', 'Все проекты']) ?>
  <p class="note">Пока в папке мастерской нет фото реальных объектов, карточки подписаны как примеры. Их можно заменить в админке.</p>
  <div class="grid-3" style="margin-top:1.2rem">
    <?php foreach ($projects as $project): ?>
    <article class="paper">
      <?= !empty($project['demo']) ? '<span class="badge badge-demo">Пример проекта</span>' : '' ?>
      <h3><a href="/proekty/<?= e($project['slug']) ?>"><?= e($project['title']) ?></a></h3>
      <p class="muted"><?= e($project['city']) ?> · <?= e($project['year']) ?></p>
      <p><?= e($project['excerpt']) ?></p>
    </article>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<?php if ($reviews): ?>
<section class="section section-alt">
  <div class="wrap">
    <?= section_head('Отзывы', 'Что говорят', ['/otzyvy', 'Все отзывы']) ?>
    <div class="grid-3">
      <?php foreach ($reviews as $review): ?>
      <blockquote class="paper quote">
        <?= !empty($review['demo']) ? '<span class="badge badge-demo" style="align-self:start">Пример отзыва</span>' : '' ?>
        <p><?= e($review['text']) ?></p>
        <p class="muted"><?= e($review['author']) ?>, <?= e($review['city']) ?></p>
      </blockquote>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<section class="section wrap">
  <?= section_head('Привозим в ваш город', 'Доставка', ['/goroda', 'Все города']) ?>
  <p class="chips"><?php foreach (cities() as $city): ?><a href="/goroda/<?= e($city['slug']) ?>"><?= e($city['name']) ?></a><?php endforeach; ?></p>
  <div class="text-col" style="margin-top:2rem">
    <h2>Дачные постройки под заказ во Владимирской области</h2>
    <p>«Загородный дом» изготавливает деревянные постройки для дачи под заказ: <a href="/tualety">дачные туалеты</a>, <a href="/hozbloki">хозблоки</a>, <a href="/dushi">летние души</a> и <a href="/kupeli">купели</a>. Мы не держим склад одинаковых коробок — каждую постройку делаем после заявки под ваш размер, сторону двери, окно и подъезд к участку.</p>
    <p>Готовую постройку привозим целиком, поэтому на даче ничего не собирают: её снимают с машины и ставят на подготовленное основание. Купить дачный туалет можно от <?= format_price(min_price(products_by_category('tualety')) ?: 16990) ?>, летний душ — от <?= format_price(min_price(products_by_category('dushi')) ?: 29900) ?>, хозблок — от <?= format_price(min_price(products_by_category('hozbloki')) ?: 89000) ?>, купель — от <?= format_price(min_price(products_by_category('kupeli')) ?: 96000) ?>. Цену с доставкой называем до начала изготовления.</p>
    <p>Мастерская находится в Муроме, отсюда и считаем доставку. Привозим по Мурому и всей Владимирской области: в Меленки, Вязники, Ковров, Судогду, Гусь-Хрустальный, Владимир, Суздаль, Собинку, Петушки, Александров и другие города. На странице каждого города — срок доставки, подъезд и подсказки по грунту.</p>
  </div>
</section>

<section class="section section-alt">
  <div class="wrap">
    <?= section_head('Частые вопросы', '', ['/faq', 'Все вопросы']) ?>
    <?= faq_list($faqs) ?>
  </div>
</section>

<section class="section section-leaf">
  <div class="wrap custom-cta">
    <div>
      <p class="kicker">Под ваш участок</p>
      <h2>Сделаем проект по индивидуальным размерам</h2>
      <p class="lead">Мы и так делаем всё под заказ. Если между баней и забором не встаёт каталожный размер или ворота узкие — напишите габарит, город и телефон.</p>
      <?= price_note() ?>
    </div>
    <?= lead_form(['kind' => 'custom', 'page' => '/', 'title' => 'Заявка на свой размер']) ?>
  </div>
</section>
<?php
    $html = ob_get_clean();
    render(page_meta('Дачные туалеты, хозблоки, души и купели в Муроме и области', 'Деревянные дачные туалеты от ' . format_price($from ?: 16990) . ', хозблоки, летние души и купели под заказ. Изготавливаем в Муроме и привозим готовыми по Владимирской области: Ковров, Владимир и ещё 18 городов.', '/'), $html);
}

function page_catalog(): void
{
    $q = query('q');
    $titles = array_column(db()['categories'], 'title', 'slug');
    $items = published_products();
    $fold = function ($value) {
        $value = mb_strtolower($value, 'UTF-8');
        $value = str_replace(['ё', '×', 'х'], ['е', 'x', 'x'], $value);
        return trim(preg_replace('/\s+/u', ' ', $value));
    };
    $folded = $fold($q);
    $cards = '';
    $shown = 0;
    $data = [];
    foreach ($items as $p) {
        $haystack = $fold($p['title'] . ' ' . $p['excerpt'] . ' ' . $p['size'] . ' ' . ($titles[$p['categorySlug']] ?? ''));
        $tight = str_replace(' ', '', $haystack);
        $match = true;
        if ($folded !== '') {
            foreach (explode(' ', $folded) as $word) {
                if (!contains($haystack, $word) && !contains($tight, $word)) { $match = false; break; }
            }
        }
        $shown += $match ? 1 : 0;
        $cards .= product_card($p, ['data-search' => $haystack, 'hidden' => $match ? null : 'hidden']);
        $data[] = ['t' => $p['title'], 'u' => product_url($p), 'p' => format_price($p['price']), 'c' => ($titles[$p['categorySlug']] ?? '') . ' · ' . $p['size'], 's' => $haystack];
    }
    ob_start(); ?>
<div class="wrap page-hero">
  <?= breadcrumbs([['name' => 'Каталог', 'href' => '/katalog']]) ?>
  <h1>Каталог дачных построек</h1>
  <p class="lead">Четыре направления, всё под заказ и с доставкой готовых построек. В карточке — размер, материал и что не входит в цену, чтобы смета не всплывала потом.</p>
  <p class="chips"><?php foreach (published_categories() as $cat): ?><a href="/<?= e($cat['slug']) ?>"><?= e($cat['title']) ?></a><?php endforeach; ?></p>
  <form class="form" action="/katalog" method="get" role="search" data-catalog-search>
    <div class="search-box">
      <label for="catalog-q">Найти модель
        <input id="catalog-q" name="q" value="<?= e($q) ?>" autocomplete="off" role="combobox" aria-expanded="false" aria-controls="catalog-suggest" aria-autocomplete="list" placeholder="<?= e($items[0]['title'] ?? '') ?>">
      </label>
      <ul class="search-suggest" id="catalog-suggest" role="listbox" hidden></ul>
    </div>
  </form>
  <?= price_note() ?>
  <div class="grid-3 section" data-catalog-grid><?= $cards ?></div>
  <p data-catalog-empty<?= $shown ? ' hidden' : '' ?>>Ничего не нашли. Посмотрите раздел целиком или оставьте <a href="/individualnye-razmery">свой размер</a>.</p>
  <script type="application/json" id="catalog-data"><?= str_replace('<', '<', json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) ?></script>
</div>
<?php
    render(page_meta('Каталог дачных построек под заказ — цены и размеры', 'Каталог деревянных дачных туалетов, хозблоков, летних душей и купелей под заказ: цены от ' . format_price(min_price($items) ?: 16990) . ', размеры и комплектация. Доставка готовых построек по Владимирской области.', '/katalog'), ob_get_clean());
}

function page_category(string $slug): void
{
    $cat = category($slug);
    if (!$cat || empty($cat['published'])) not_found();
    $products = products_by_category($slug);
    $from = min_price($products);
    $posts = array_slice(array_values(array_filter(db()['posts'], function ($p) use ($slug) {
        return !empty($p['published']) && in_array($slug, $p['tags'] ?? [], true);
    })), 0, 3);
    ob_start(); ?>
<article class="page-hero">
  <div class="wrap">
    <?= breadcrumbs([['name' => 'Каталог', 'href' => '/katalog'], ['name' => $cat['title'], 'href' => "/$slug"]]) ?>
    <div class="cat-hero">
      <div>
        <h1><?= e($cat['h1']) ?></h1>
        <p class="lead"><?= e($cat['lead']) ?></p>
        <div class="facts">
          <p class="fact"><small>Моделей в каталоге</small><strong><?= count($products) ?></strong></p>
          <?php if ($from): ?><p class="fact"><small>Цена</small><strong>от <?= format_price($from) ?></strong></p><?php endif; ?>
          <p class="fact"><small>Доставка</small><strong>по всей области</strong></p>
        </div>
        <div class="hero-actions">
          <a class="btn btn-primary btn-lg" href="#modeli">Смотреть модели</a>
          <a class="btn btn-line btn-lg" href="#zakaz">Оставить заявку</a>
        </div>
      </div>
      <div class="media-frame"><?= category_media($cat) ?></div>
    </div>
  </div>

  <section id="modeli" class="section section-alt">
    <div class="wrap">
      <?= section_head('Модели', $cat['title'], ['/individualnye-razmery', 'Нужен свой размер']) ?>
      <?= product_grid($products) ?>
    </div>
  </section>

  <section class="section wrap">
    <div class="text-col"><?= paragraphs($cat['intro']) ?></div>
    <?= price_note() ?>
    <h2>Цены и размеры</h2>
    <?= price_table($products) ?>
  </section>

  <section class="section section-leaf">
    <div class="wrap">
      <p class="kicker">Подсказки</p>
      <h2>Как выбрать</h2>
      <div class="grid-2">
        <?php foreach ($cat['choose'] as $item): ?><section class="paper"><h3><?= e($item['title']) ?></h3><p><?= e($item['text']) ?></p></section><?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="section wrap">
    <h2><?= e($cat['compareTitle']) ?></h2>
    <div class="table-wrap"><table>
      <thead><tr><th>Критерий</th><?php foreach ($cat['compareHeads'] as $head): ?><th><?= e($head) ?></th><?php endforeach; ?></tr></thead>
      <tbody><?php foreach ($cat['compareRows'] as $row): ?><tr><th><?= e($row['label']) ?></th><?php foreach ($row['cells'] as $cell): ?><td><?= e($cell) ?></td><?php endforeach; ?></tr><?php endforeach; ?></tbody>
    </table></div>
    <div class="grid-2" style="margin-top:1.6rem">
      <section class="paper"><h3>Материалы</h3><p><?= e($cat['materials']) ?></p></section>
      <section class="paper"><h3>Как ставим</h3><p><?= e($cat['install']) ?></p></section>
    </div>
  </section>

  <section class="section section-alt">
    <div class="wrap">
      <h2>Вопросы по категории</h2>
      <?= faq_list($cat['faq']) ?>
      <?= faq_json_ld($cat['faq']) ?>
    </div>
  </section>

  <section class="section wrap">
    <h2>Города, куда везём</h2>
    <p class="chips"><?php foreach (cities() as $city): ?><a href="/goroda/<?= e($city['slug']) ?>/<?= e($slug) ?>"><?= e($city['name']) ?></a><?php endforeach; ?></p>
    <?php if ($posts): ?>
    <h2>Почитать перед заказом</h2>
    <ul class="read-list"><?php foreach ($posts as $post): ?><li><a href="/blog/<?= e($post['slug']) ?>"><?= e($post['title']) ?></a></li><?php endforeach; ?></ul>
    <?php endif; ?>
  </section>

  <section class="section section-leaf">
    <div class="wrap custom-cta">
      <div>
        <p class="kicker">Заявка</p>
        <h2>Подберём <?= e(lower($cat['menu'])) ?> под ваш участок</h2>
        <p class="lead">Оставьте телефон и город. Уточним подъезд, грунт и состав работ, затем зафиксируем цену.</p>
      </div>
      <?= lead_form(['id' => 'zakaz', 'kind' => 'order', 'page' => "/$slug", 'productType' => $cat['title'], 'title' => 'Заказать: ' . $cat['title']]) ?>
    </div>
  </section>
</article>
<?php
    render(page_meta(fill_price($cat['seoTitle'], $from), fill_price($cat['seoDescription'], $from), "/$slug"), ob_get_clean());
}

// Что предложить к товару: модели из админки, иначе по одной популярной из соседних разделов.
function cross_sell(array $product): array
{
    $published = array_values(array_filter(published_products(), function ($p) use ($product) { return $p['id'] !== $product['id']; }));
    $picked = [];
    foreach ($product['crossSell'] ?? [] as $slug) {
        $item = find_by($published, 'slug', $slug);
        if ($item) $picked[] = $item;
    }
    if ($picked) return array_slice($picked, 0, 4);
    $pairs = [
        'tualety' => ['dushi', 'hozbloki', 'kupeli'],
        'dushi' => ['tualety', 'hozbloki', 'kupeli'],
        'hozbloki' => ['tualety', 'dushi', 'kupeli'],
        'kupeli' => ['dushi', 'hozbloki', 'tualety'],
    ];
    $out = [];
    foreach ($pairs[$product['categorySlug']] ?? [] as $slug) {
        $items = array_values(array_filter($published, function ($p) use ($slug) { return $p['categorySlug'] === $slug; }));
        if (!$items) continue;
        $popular = array_values(array_filter($items, function ($p) { return !empty($p['popular']); }));
        $out[] = $popular[0] ?? $items[0];
    }
    return $out;
}

function page_product(string $category, string $slug): void
{
    $product = null;
    foreach (db()['products'] as $p) {
        if ($p['categorySlug'] === $category && $p['slug'] === $slug && !empty($p['published'])) { $product = $p; break; }
    }
    $cat = category($category);
    if (!$product || !$cat) not_found();
    $related = array_slice(array_values(array_filter(products_by_category($category), function ($p) use ($product) { return $p['id'] !== $product['id']; })), 0, 3);
    $together = cross_sell($product);
    $options = visible_options($product);
    $extra = starting_extra($options);
    $url = site_url() . product_url($product);
    $formId = 'order-' . $product['id'];
    ob_start(); ?>
<article class="page-hero">
  <div class="wrap">
    <?= breadcrumbs([['name' => 'Каталог', 'href' => '/katalog'], ['name' => $cat['title'], 'href' => '/' . $cat['slug']], ['name' => $product['title'], 'href' => product_url($product)]]) ?>
    <div class="product-top">
      <div class="product-media">
        <?php if (!empty($product['photos'])): foreach ($product['photos'] as $i => $photo): ?>
        <div class="media-frame"><img src="<?= e($photo) ?>" alt="<?= e($product['title'] . ($i ? ' — фото ' . ($i + 1) : '')) ?>"<?= $i ? ' loading="lazy"' : '' ?>></div>
        <?php endforeach; else: ?>
        <div class="media-frame"><?= illustration($product['categorySlug'], $product['title']) ?></div>
        <?php endif; ?>
      </div>
      <div class="buy-box paper" data-configurator data-base="<?= (int) $product['price'] ?>">
        <div class="badges">
          <?= !empty($product['hit']) ? '<span class="badge">Хит продаж</span>' : '' ?>
          <?= !empty($product['popular']) ? '<span class="badge">Популярное</span>' : '' ?>
          <span class="badge badge-demo">Под заказ</span>
        </div>
        <h1><?= e($product['title']) ?></h1>
        <p class="muted subtitle"><?= e($product['size']) ?></p>
        <p class="price" aria-live="polite" data-price><?= format_price($product['price'] + $extra) ?></p>
        <p class="muted" data-extra<?= $extra ? '' : ' hidden' ?>>Доплата за опции: <span data-extra-value><?= format_price($extra) ?></span></p>
        <a class="btn btn-primary btn-lg btn-block" href="#<?= e($formId) ?>">Заказать эту модель</a>
        <p><?= e($product['excerpt']) ?></p>
        <dl class="specs">
          <dt>Размер</dt><dd><?= e($product['size']) ?></dd>
          <dt>Площадь</dt><dd><?= e($product['area']) ?></dd>
          <dt>Материал</dt><dd><?= e($product['material']) ?></dd>
          <dt>Крыша</dt><dd><?= e($product['roof']) ?></dd>
        </dl>
        <div class="kit">
          <p class="kit-box kit-in"><b>В комплекте</b><?= e($product['kit']) ?></p>
          <p class="kit-box kit-out"><b>Не входит</b><?= e($product['notIncluded']) ?></p>
        </div>
        <?php if ($options): ?>
        <div class="option-block">
          <h2>Опции</h2>
          <?php foreach ($options as $option): ?>
          <fieldset class="option-group">
            <legend><?= e($option['title']) ?></legend>
            <?php if (!empty($option['hint'])): ?><p class="muted hint"><?= e($option['hint']) ?></p><?php endif; ?>
            <?php foreach ($option['choices'] as $i => $choice): $radio = $option['kind'] === 'choice'; ?>
            <label class="check">
              <input form="<?= e($formId) ?>" type="<?= $radio ? 'radio' : 'checkbox' ?>" name="opt_<?= e($option['id']) ?><?= $radio ? '' : '[]' ?>" value="<?= e($choice['id']) ?>" data-price="<?= (int) $choice['price'] ?>"<?= $radio && $i === 0 ? ' checked required' : '' ?>>
              <span><?= e($choice['label']) ?></span>
              <span class="option-delta"><?= e(price_delta((int) $choice['price'])) ?></span>
            </label>
            <?php endforeach; ?>
          </fieldset>
          <?php endforeach; ?>
          <p class="muted">Доплата прибавляется к цене ориентира и попадает в заявку.</p>
        </div>
        <?php endif; ?>
        <?= price_note() ?>
      </div>
    </div>
  </div>

  <section class="section wrap">
    <div class="text-col">
      <h2>Описание</h2>
      <?= rich_text($product['description']) ?>
      <p class="chips">
        <a href="/<?= e($cat['slug']) ?>">Все <?= e(lower($cat['title'])) ?></a>
        <a href="/dostavka">Доставка</a>
        <a href="/goroda">Города области</a>
        <a href="/individualnye-razmery">Свой размер</a>
      </p>
    </div>
  </section>

  <?php if ($together): ?>
  <section class="section section-alt">
    <div class="wrap">
      <?= section_head('С этим покупают', 'Один рейс — меньше расходов на доставку') ?>
      <?= product_grid($together, count($together) === 4 ? 'grid-4' : 'grid-3') ?>
    </div>
  </section>
  <?php endif; ?>

  <section class="section section-leaf">
    <div class="wrap custom-cta">
      <div>
        <p class="kicker">Заявка</p>
        <h2>Как пройдёт заказ</h2>
        <p class="lead">Выбранные опции попадут в заявку. Постройку изготовим под заказ и привезём готовой.</p>
        <ol class="mini-steps">
          <li>Оставьте телефон и город.</li>
          <li>Уточняем подъезд, грунт и фиксируем цену.</li>
          <li>Изготавливаем и привозим постройку готовой.</li>
        </ol>
      </div>
      <?= lead_form(['id' => $formId, 'kind' => 'order', 'page' => product_url($product), 'productType' => $product['title'], 'title' => 'Заказать эту модель']) ?>
    </div>
  </section>

  <?php if ($related): ?>
  <section class="section wrap">
    <?= section_head('Похожие модели', '', ['/' . $cat['slug'], 'Все ' . lower($cat['title'])]) ?>
    <?= product_grid($related, 'grid-3') ?>
  </section>
  <?php endif; ?>

  <?php
    $reviews = array_values(array_filter(db()['reviews'], function ($r) use ($product) {
        return !empty($r['published']) && ($r['productSlug'] ?? '') === $product['slug'];
    }));
    // В разметку для поисковиков идут только настоящие отзывы, примеры — нет.
    $realReviews = array_values(array_filter($reviews, function ($r) { return empty($r['demo']); }));
  ?>
  <section class="section section-alt" id="otzyvy">
    <div class="wrap">
      <?= section_head('Отзывы о модели', $product['title'], $reviews ? [] : ['/otzyvy', 'Все отзывы'], '') ?>
      <div class="reviews-layout">
        <div class="reviews-list">
          <?php if ($realReviews): $avg = array_sum(array_column($realReviews, 'rating')) / count($realReviews); ?>
          <p class="rating-summary"><span class="stars" aria-hidden="true"><?= str_repeat('★', (int) round($avg)) . str_repeat('☆', 5 - (int) round($avg)) ?></span> <b><?= number_format($avg, 1, ',', '') ?></b> из 5 · <?= count($realReviews) ?> <?= plural(count($realReviews), 'отзыв', 'отзыва', 'отзывов') ?></p>
          <?php endif; ?>
          <?php foreach ($reviews as $review): ?>
          <blockquote class="paper quote review-card">
            <?= !empty($review['demo']) ? '<span class="badge badge-demo" style="align-self:start">Пример отзыва</span>' : '' ?>
            <p class="stars" aria-label="Оценка <?= (int) $review['rating'] ?> из 5"><?= str_repeat('★', (int) $review['rating']) . str_repeat('☆', 5 - (int) $review['rating']) ?></p>
            <p><?= e($review['text']) ?></p>
            <p class="muted"><?= e($review['author']) ?><?= $review['city'] ? ', ' . e($review['city']) : '' ?></p>
          </blockquote>
          <?php endforeach; ?>
          <?php if (!$reviews): ?>
          <div class="paper reviews-empty">
            <p class="stars" aria-hidden="true">☆☆☆☆☆</p>
            <p><b>Об этой модели пока нет отзывов.</b> Если вы уже поставили «<?= e(trim($product['title'], '«»')) ?>» у себя на участке, расскажите, как она служит, — отзыв появится здесь после проверки.</p>
          </div>
          <?php endif; ?>
        </div>
        <?= review_form(product_url($product), $product) ?>
      </div>
    </div>
  </section>
  <?php
    $schema = [
      '@context' => 'https://schema.org',
      '@type' => 'Product',
      'name' => $product['title'],
      'description' => $product['excerpt'],
      'category' => $cat['title'],
      'url' => $url,
      'material' => $product['material'],
      'image' => array_map(function ($photo) { return starts_with($photo, 'http') ? $photo : site_url() . $photo; }, $product['photos'] ?? []),
      'offers' => ['@type' => 'Offer', 'priceCurrency' => 'RUB', 'price' => $product['price'], 'availability' => 'https://schema.org/MadeToOrder', 'url' => $url],
    ];
    if ($realReviews) {
        $schema['aggregateRating'] = ['@type' => 'AggregateRating', 'ratingValue' => round($avg, 1), 'reviewCount' => count($realReviews), 'bestRating' => 5, 'worstRating' => 1];
        $schema['review'] = array_map(function ($r) {
            return ['@type' => 'Review', 'author' => ['@type' => 'Person', 'name' => $r['author']], 'reviewBody' => $r['text'],
                'reviewRating' => ['@type' => 'Rating', 'ratingValue' => (int) $r['rating'], 'bestRating' => 5]];
        }, array_slice($realReviews, 0, 10));
    }
  ?>
  <?= json_ld($schema) ?>
</article>
<?php
    render(page_meta(fill_price($product['seoTitle'], (int) $product['price']), 'Цена ' . format_price($product['price']) . '. ' . fill_price($product['seoDescription'], (int) $product['price']), product_url($product)), ob_get_clean());
}

// Подсказки поиска: разделы и опубликованные товары.
function search_fold(string $value): string
{
    $value = str_replace(['ё', '×', 'х'], ['е', 'x', 'x'], mb_strtolower($value, 'UTF-8'));
    return trim(preg_replace('/\s+/u', ' ', $value));
}

function search_index(): array
{
    $titles = array_column(db()['categories'], 'title', 'slug');
    $items = [];
    foreach (published_categories() as $cat) {
        $products = products_by_category($cat['slug']);
        $price = min_price($products);
        $items[] = [
            'k' => 'cat', 't' => $cat['title'], 'u' => '/' . $cat['slug'],
            'p' => $price ? 'от ' . format_price($price) : '',
            'c' => 'Раздел · ' . count($products) . ' ' . plural(count($products), 'модель', 'модели', 'моделей'),
            's' => search_fold($cat['title'] . ' ' . $cat['menu'] . ' ' . $cat['h1'] . ' ' . $cat['lead']),
        ];
    }
    foreach (published_products() as $p) {
        $items[] = [
            'k' => 'product', 't' => $p['title'], 'u' => product_url($p), 'p' => format_price($p['price']),
            'c' => ($titles[$p['categorySlug']] ?? '') . ' · ' . $p['size'],
            's' => search_fold($p['title'] . ' ' . $p['excerpt'] . ' ' . $p['size'] . ' ' . ($titles[$p['categorySlug']] ?? '')),
        ];
    }
    return $items;
}
