<?php
// Общие блоки страниц: шаблон, шапка, подвал, карточки, формы.

function asset(string $path): string
{
    $file = SITE_ROOT . '/' . ltrim($path, '/');
    return '/' . ltrim($path, '/') . (is_file($file) ? '?v=' . filemtime($file) : '');
}

function layout(array $meta, string $content, array $opts = []): string
{
    $s = settings();
    $admin = !empty($opts['admin']);
    ob_start(); ?>
<!doctype html>
<html lang="ru">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($meta['title']) ?></title>
<?php if (!empty($meta['description'])): ?><meta name="description" content="<?= e($meta['description']) ?>">
<?php endif; ?>
<?php if (!empty($meta['canonical'])): ?><link rel="canonical" href="<?= e($meta['canonical']) ?>">
<meta property="og:title" content="<?= e($meta['title']) ?>">
<meta property="og:description" content="<?= e($meta['description'] ?? '') ?>">
<meta property="og:url" content="<?= e($meta['canonical']) ?>">
<meta property="og:type" content="<?= e($meta['type'] ?? 'website') ?>">
<meta property="og:locale" content="ru_RU">
<meta property="og:site_name" content="Загородный дом">
<?php endif; ?>
<meta name="robots" content="<?= empty($meta['index']) ? 'noindex, nofollow' : 'index, follow' ?>">
<?php if (!$admin && verification_code((string) ($s['yandexVerification'] ?? ''))): ?><meta name="yandex-verification" content="<?= e(verification_code($s['yandexVerification'])) ?>">
<?php endif; ?>
<?php if (!$admin && verification_code((string) ($s['googleVerification'] ?? ''))): ?><meta name="google-site-verification" content="<?= e(verification_code($s['googleVerification'])) ?>">
<?php endif; ?>
<link rel="icon" href="/favicon.ico" sizes="any">
<link rel="icon" href="/icon.svg" type="image/svg+xml">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Literata:opsz,wght@7..72,400..700&family=Manrope:wght@400..800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= asset('assets/site.css') ?>">
<?= $admin ? '' : metrika_head() ?>
</head>
<body>
<?= $admin ? '' : metrika_noscript() ?>
<?php if ($admin): ?>
<?= $content ?>
<script src="<?= asset('assets/admin.js') ?>" defer></script>
<?php else: ?>
<a class="skip btn btn-primary" href="#content">К содержанию</a>
<?= site_header() ?>
<main id="content"><?= $content ?></main>
<?= site_footer() ?>
<?= sticky_cta() ?>
<?= question_widget() ?>
<?= json_ld(organization_json()) ?>
<?php endif; ?>
<script src="<?= asset('assets/site.js') ?>" defer></script>
</body>
</html>
<?php
    return ob_get_clean();
}

function site_header(): string
{
    $s = settings();
    $phone = tel_href((string) ($s['phone'] ?? ''));
    ob_start(); ?>
<div class="topbar"><div class="wrap"><span>Изготавливаем в Муроме · доставка готовых построек по Владимирской области</span><span><?= e($s['hours'] ?? '') ?></span></div></div>
<header class="header">
  <div class="wrap">
    <div class="header-row">
      <a href="/" class="logo">
        <svg class="logo-mark" viewBox="0 0 64 64" aria-hidden="true"><rect width="64" height="64" rx="14" fill="#3E2718"/><path d="M10 30 L32 12 L54 30 L46 52 H18 Z" fill="#C9923A"/><path d="M27 52 V36 H37 V52" fill="#3E2718"/></svg>
        <span><span class="logo-name"><?= e($s['companyName'] ?? 'Загородный дом') ?></span><small>мастерская дачных построек</small></span>
      </a>
      <div class="header-actions">
        <?php if ($phone): ?><a class="btn btn-line" href="<?= e($phone) ?>"><?= e($s['phone']) ?></a>
        <?php else: ?><a class="btn btn-line" href="/kontakty">Контакты</a><?php endif; ?>
        <?= order_widget() ?>
      </div>
    </div>
    <div class="nav-wrap" data-nav>
      <button class="menu-btn btn btn-line" type="button" aria-expanded="false" data-nav-toggle>Меню</button>
      <nav class="primary-nav" aria-label="Разделы">
        <a href="/katalog">Каталог</a>
        <?php foreach (published_categories() as $cat): ?><a href="/<?= e($cat['slug']) ?>"><?= e($cat['menu']) ?></a><?php endforeach; ?>
        <a href="/goroda">Города</a>
        <a href="/proekty">Проекты</a>
        <a href="/blog">Блог</a>
        <a href="/kontakty">Контакты</a>
      </nav>
    </div>
  </div>
</header>
<?php
    return ob_get_clean();
}

function site_footer(): string
{
    $s = settings();
    $phone = tel_href((string) ($s['phone'] ?? ''));
    ob_start(); ?>
<footer class="site-footer">
  <div class="wrap footer-grid">
    <div>
      <h2><?= e($s['companyName'] ?? '') ?></h2>
      <p class="footer-note"><?= e($s['address'] ?? '') ?></p>
      <p class="footer-note">
        <?= $phone ? '<a href="' . e($phone) . '">' . e($s['phone']) . '</a>' : e($s['phone'] ?? '') ?>
      </p>
      <p class="footer-note"><?= e($s['hours'] ?? '') ?></p>
    </div>
    <div>
      <h2>Каталог</h2>
      <ul>
        <?php foreach (published_categories() as $cat): ?><li><a href="/<?= e($cat['slug']) ?>"><?= e($cat['title']) ?></a></li><?php endforeach; ?>
        <li><a href="/individualnye-razmery">Свои размеры</a></li>
      </ul>
    </div>
    <div>
      <h2>Покупателю</h2>
      <ul>
        <li><a href="/dostavka">Доставка</a></li>
        <li><a href="/kak-zakazat">Как заказать</a></li>
        <li><a href="/garantiya">Гарантия и поддержка</a></li>
        <li><a href="/faq">Вопросы</a></li>
        <li><a href="/otzyvy">Отзывы</a></li>
        <li><a href="/o-nas">О мастерской</a></li>
        <li><a href="/blog">Статьи</a></li>
        <li><a href="/akcii">Новости</a></li>
      </ul>
    </div>
    <div>
      <h2>Города</h2>
      <ul>
        <?php foreach (array_slice(cities(), 0, 8) as $city): ?><li><a href="/goroda/<?= e($city['slug']) ?>"><?= e($city['name']) ?></a></li><?php endforeach; ?>
        <li><a href="/goroda">Все города области</a></li>
      </ul>
    </div>
  </div>
</footer>
<?php
    return ob_get_clean();
}

function sticky_cta(): string
{
    $phone = tel_href(setting('phone'));
    return '<div class="sticky-cta">' . ($phone ? '<a href="' . e($phone) . '">Позвонить</a>' : '<a href="/kontakty">Контакты</a>') . '<a class="alt" href="/obratnaya-svyaz">Заявка</a></div>';
}

function honeypot(): string
{
    return '<label class="hp">Сайт компании<input name="company_site" tabindex="-1" autocomplete="off"></label>';
}

function order_widget(): string
{
    $path = request_path();
    $open = query('error') === 'phone' && query('form') === 'widget';
    ob_start(); ?>
<div class="order-widget<?= $open ? ' is-open' : '' ?>" data-order-widget>
  <button class="btn btn-primary" type="button" aria-expanded="<?= $open ? 'true' : 'false' ?>" aria-controls="order-panel">Заявка</button>
  <form class="order-panel" action="/form/lead" method="post" id="order-panel" aria-label="Заявка">
    <p class="order-panel-title">Заявка</p>
    <?php if ($open): ?><p class="note">Укажите телефон: не меньше 10 цифр.</p><?php endif; ?>
    <input type="hidden" name="kind" value="order"><input type="hidden" name="page" value="<?= e($path) ?>"><input type="hidden" name="back" value="<?= e($path) ?>"><input type="hidden" name="form" value="widget">
    <?= honeypot() ?>
    <label>Имя<input name="name" autocomplete="name"></label>
    <label>Телефон<input name="phone" type="tel" autocomplete="tel" required inputmode="tel"></label>
    <label>Что нужно<input name="productType" placeholder="Туалет, хозблок, купель или душ"></label>
    <label>Комментарий<textarea name="message" rows="3" placeholder="Размер, подъезд, вопрос"></textarea></label>
    <button class="btn btn-primary" type="submit" data-submit>Отправить</button>
  </form>
</div>
<?php
    return ob_get_clean();
}

function question_widget(): string
{
    $path = request_path();
    if ($path === '/obratnaya-svyaz') return '';
    $open = query('error') === 'phone' && query('form') === 'question';
    ob_start(); ?>
<div class="ask-widget" data-ask-widget>
  <form class="ask-panel" action="/form/lead" method="post" aria-labelledby="ask-title"<?= $open ? '' : ' hidden' ?>>
    <div class="ask-head"><strong id="ask-title">Остались вопросы?</strong><button class="ask-close" type="button" aria-label="Закрыть" data-ask-close>×</button></div>
    <p class="muted">Напишите вопрос и телефон — ответим.</p>
    <?php if ($open): ?><p class="note">Укажите телефон: не меньше 10 цифр.</p><?php endif; ?>
    <input type="hidden" name="kind" value="question"><input type="hidden" name="page" value="<?= e($path) ?>"><input type="hidden" name="back" value="<?= e($path) ?>"><input type="hidden" name="form" value="question">
    <?= honeypot() ?>
    <label>Имя<input name="name" autocomplete="name"></label>
    <label>Телефон<input name="phone" type="tel" autocomplete="tel" required inputmode="tel"></label>
    <label>Вопрос<textarea name="message" required minlength="5" maxlength="2000" rows="3" placeholder="Например, какой размер встанет у бани"></textarea></label>
    <button class="btn btn-primary" type="submit" data-submit>Отправить</button>
  </form>
  <button class="ask-toggle" type="button" aria-expanded="<?= $open ? 'true' : 'false' ?>" data-ask-toggle><span class="ask-mark" aria-hidden="true">?</span>Остались вопросы?</button>
</div>
<?php
    return ob_get_clean();
}

function breadcrumbs(array $items): string
{
    $all = array_merge([['name' => 'Главная', 'href' => '/']], $items);
    $html = '<ol class="crumbs">';
    foreach ($all as $i => $item) {
        $html .= '<li>' . ($i > 0 ? '<span aria-hidden="true"> / </span>' : '')
            . ($i === count($all) - 1 ? '<span>' . e($item['name']) . '</span>' : '<a href="' . e($item['href']) . '">' . e($item['name']) . '</a>') . '</li>';
    }
    $html .= '</ol>';
    $site = site_url();
    $html .= json_ld([
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => array_map(function ($item, $i) use ($site) {
            return ['@type' => 'ListItem', 'position' => $i + 1, 'name' => $item['name'], 'item' => $site . $item['href']];
        }, $all, array_keys($all)),
    ]);
    return $html;
}

function illustration(string $kind, string $title): string
{
    $cabin = function ($x) {
        return '<g><path d="M' . $x . ' 250 L' . ($x + 70) . ' 190 L' . ($x + 150) . ' 250 Z" fill="#8d4b27"/><rect x="' . ($x + 12) . '" y="250" width="126" height="90" fill="#c98455"/><rect x="' . ($x + 48) . '" y="275" width="42" height="65" fill="#3e2718"/><circle cx="' . ($x + 78) . '" cy="230" r="8" fill="#f6efe4"/></g>';
    };
    $barn = '<g><path d="M120 250 L210 180 L430 180 L500 250 Z" fill="#6b3e24"/><rect x="140" y="250" width="330" height="100" fill="#c98455"/><rect x="170" y="280" width="50" height="70" fill="#3e2718"/><rect x="280" y="280" width="50" height="70" fill="#3e2718"/><rect x="390" y="275" width="70" height="75" fill="#e7d3b0"/></g>';
    $tub = '<g><ellipse cx="320" cy="300" rx="120" ry="36" fill="#8d4b27"/><path d="M200 250 h240 v40 a120 36 0 0 1 -240 0 Z" fill="#a8683c"/><ellipse cx="320" cy="250" rx="120" ry="36" fill="#7eb0c9"/><rect x="430" y="210" width="28" height="70" fill="#5c534c"/><path d="M250 250 v-28 h20 v28 M390 250 v-40 h16 v40" fill="#f6efe4"/></g>';
    $shower = function ($shift) {
        return '<g' . ($shift ? ' transform="translate(' . $shift . ' 0)"' : '') . '><rect x="230" y="170" width="160" height="28" fill="#6b3e24"/><rect x="250" y="145" width="90" height="28" rx="8" fill="#d7dde2"/><rect x="250" y="198" width="140" height="140" fill="#c98455"/><rect x="292" y="230" width="46" height="108" fill="#3e2718"/><path d="M300 175 v18 M320 175 v24 M340 175 v16" stroke="#7eb0c9" stroke-width="3"/></g>';
    };
    $body = '';
    if ($kind === 'tualety' || $kind === 'hero') $body .= $cabin(150);
    if ($kind === 'hozbloki') $body .= $barn;
    if ($kind === 'kupeli') $body .= $tub;
    if ($kind === 'dushi' || $kind === 'hero') $body .= $shower($kind === 'hero' ? 170 : 0);
    return '<svg class="illus" viewBox="0 0 640 420" role="img" aria-label="' . e($title) . '"><title>' . e($title) . '</title>'
        . '<rect width="640" height="420" fill="#d7ebe0"/><circle cx="520" cy="78" r="36" fill="#f0c56e"/>'
        . '<path d="M0 300 C120 270 180 320 320 300 C460 280 520 330 640 300 V420 H0 Z" fill="#b7d0a4"/>'
        . '<path d="M70 250 l26-70 18 70 Z" fill="#2f6b45"/><rect x="88" y="250" width="8" height="50" fill="#6b3e24"/>'
        . '<path d="M540 230 l30-80 22 80 Z" fill="#24563a"/><rect x="562" y="230" width="8" height="70" fill="#6b3e24"/>'
        . $body . '</svg>';
}

function product_url(array $product): string
{
    return '/' . $product['categorySlug'] . '/' . $product['slug'];
}

function product_card(array $product, array $attrs = []): string
{
    $url = product_url($product);
    $extra = '';
    foreach ($attrs as $name => $value) if ($value !== null) $extra .= ' ' . $name . '="' . e($value) . '"';
    $media = !empty($product['photos'][0])
        ? '<img src="' . e($product['photos'][0]) . '" alt="' . e($product['title']) . '" loading="lazy">'
        : illustration($product['categorySlug'], $product['title']);
    return '<article class="card"' . $extra . '>' . $media . '<div class="body"><div class="badges">'
        . (!empty($product['hit']) ? '<span class="badge">Хит</span>' : '')
        . (!empty($product['popular']) ? '<span class="badge">Популярное</span>' : '')
        . '<span class="badge badge-demo">Под заказ</span></div>'
        . '<h3><a class="title" href="' . e($url) . '">' . e($product['title']) . '</a></h3>'
        . '<p class="muted">' . e($product['size']) . '</p><p>' . e($product['excerpt']) . '</p>'
        . '<p class="price">' . format_price($product['price']) . '</p>'
        . '<a class="btn btn-moss" href="' . e($url) . '">Подробнее</a></div></article>';
}

function product_grid(array $products, string $class = ''): string
{
    if (!$class) $class = count($products) % 3 === 1 ? 'grid-4' : 'grid-3';
    return '<div class="' . e($class) . '">' . implode('', array_map('product_card', $products)) . '</div>';
}

function price_note(): string
{
    return '<p class="note">' . e(setting('priceNote')) . '</p>';
}

function faq_list(array $items): string
{
    $html = '<div class="faq">';
    foreach ($items as $item) $html .= '<details><summary>' . e($item['q']) . '</summary><p>' . e($item['a']) . '</p></details>';
    return $html . '</div>';
}

function price_table(array $products): string
{
    $html = '<div class="table-wrap"><table><thead><tr><th>Модель</th><th>Размер</th><th>Материал</th><th>Цена ориентира</th></tr></thead><tbody>';
    foreach ($products as $p) {
        $html .= '<tr><td><a href="' . e(product_url($p)) . '">' . e($p['title']) . '</a></td><td>' . e($p['size']) . '</td><td>' . e($p['material']) . '</td><td>' . format_price($p['price']) . '</td></tr>';
    }
    return $html . '</tbody></table></div>';
}

function section_head(string $title, string $kicker = '', array $more = [], string $lead = ''): string
{
    return '<div class="section-head"><div>' . ($kicker ? '<p class="kicker">' . e($kicker) . '</p>' : '') . '<h2>' . e($title) . '</h2>'
        . ($lead ? '<p class="lead">' . e($lead) . '</p>' : '') . '</div>'
        . ($more ? '<a class="more" href="' . e($more[0]) . '">' . e($more[1]) . '</a>' : '') . '</div>';
}

function lead_form(array $o): string
{
    $kind = $o['kind'];
    $page = $o['page'];
    $error = query('error') === 'phone' && query('form') === $kind;
    ob_start(); ?>
<form<?= !empty($o['id']) ? ' id="' . e($o['id']) . '"' : '' ?> class="paper form" action="/form/lead" method="post">
  <h2><?= e($o['title']) ?></h2>
  <?php if ($error): ?><p class="note">Укажите телефон: не меньше 10 цифр.</p><?php endif; ?>
  <input type="hidden" name="kind" value="<?= e($kind) ?>"><input type="hidden" name="page" value="<?= e($page) ?>"><input type="hidden" name="back" value="<?= e($page) ?>"><input type="hidden" name="form" value="<?= e($kind) ?>">
  <?= honeypot() ?>
  <label>Имя<input name="name" autocomplete="name"></label>
  <label>Телефон<input name="phone" type="tel" autocomplete="tel" required inputmode="tel"></label>
  <label>Город
    <select name="city">
      <option value="">Выберите город</option>
      <?php $byKm = cities(); usort($byKm, function ($a, $b) { return $a['km'] <=> $b['km']; }); foreach ($byKm as $city): ?><option value="<?= e($city['name']) ?>"><?= e($city['name']) ?></option><?php endforeach; ?>
      <option value="Другой населённый пункт области">Другой населённый пункт области</option>
    </select>
  </label>
  <label>Что нужно<input name="productType" value="<?= e($o['productType'] ?? '') ?>" placeholder="Туалет, хозблок, купель или душ"></label>
  <label>Размер, если уже знаете<input name="size" placeholder="Например, 1,2 × 1,2 м"></label>
  <label>Комментарий<textarea name="message" placeholder="Подъезд, грунт, нужен ли душ внутри"></textarea></label>
  <button class="btn btn-primary" type="submit" data-submit>Отправить заявку</button>
  <p class="muted"><?= e(setting('leadTime')) ?></p>
</form>
<?php
    return ob_get_clean();
}

function review_form(string $page, ?array $product = null): string
{
    $status = query('review');
    ob_start(); ?>
<form id="otzyv" class="paper form review-form" action="/form/review" method="post">
  <h2>Написать отзыв</h2>
  <?php if ($product): ?><p class="muted">Отзыв о модели <?= e($product['title']) ?></p><?php endif; ?>
  <?php if ($status === 'sent'): ?><p class="note">Спасибо. Отзыв сохранён и появится на сайте после проверки.</p><?php endif; ?>
  <?php if ($status === 'error'): ?><p class="note">Напишите имя, поставьте оценку и текст хотя бы в пару предложений.</p><?php endif; ?>
  <input type="hidden" name="back" value="<?= e($page) ?>">
  <?php if ($product): ?><input type="hidden" name="productSlug" value="<?= e($product['slug']) ?>"><?php endif; ?>
  <?= honeypot() ?>
  <fieldset class="star-field"><legend>Оценка</legend><div class="star-rate">
    <?php foreach ([5, 4, 3, 2, 1] as $value): ?><label><input type="radio" name="rating" value="<?= $value ?>"<?= $value === 5 ? ' required' : '' ?> aria-label="<?= $value ?> из 5"><span aria-hidden="true">★</span></label><?php endforeach; ?>
  </div></fieldset>
  <label>Имя<input name="author" autocomplete="name" required maxlength="80"></label>
  <label>Город<input name="city" placeholder="Например, Ковров" maxlength="80"></label>
  <?php if (!$product): ?>
  <label>О чём отзыв
    <select name="productSlug"><option value="">О мастерской в целом</option>
      <?php foreach (published_products() as $item): ?><option value="<?= e($item['slug']) ?>"><?= e($item['title']) ?></option><?php endforeach; ?>
    </select>
  </label>
  <?php endif; ?>
  <label>Текст отзыва<textarea name="text" required minlength="10" maxlength="2000" placeholder="Что заказали и как прошло"></textarea></label>
  <button class="btn btn-primary" type="submit" data-submit>Отправить отзыв</button>
</form>
<?php
    return ob_get_clean();
}

function icon(string $name): string
{
    $paths = [
        'shield' => '<path d="M12 3l7 3v6c0 4.5-3 7.6-7 9-4-1.4-7-4.5-7-9V6z M8.5 12l2.5 2.5 4.5-5"/>',
        'phone' => '<path d="M5 4h4l2 5-2.5 1.5a11 11 0 005 5L15 13l5 2v4a2 2 0 01-2 2A16 16 0 013 6a2 2 0 012-2z"/>',
        'truck' => '<path d="M3 6h11v10H3z M14 10h4l3 3v3h-7 M7.5 19a1.8 1.8 0 100-.1 M17.5 19a1.8 1.8 0 100-.1"/>',
    ];
    return '<span class="why-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">' . $paths[$name] . '</svg></span>';
}

// Фото раздела из админки; без фото — прежняя картинка или рисунок.
function category_media(array $cat, string $alt = '', string $class = ''): string
{
    if (!empty($cat['photos'][0])) {
        return '<img' . ($class ? ' class="' . e($class) . '"' : '') . ' src="' . e($cat['photos'][0]) . '" alt="' . e($alt ?: $cat['title']) . '" loading="lazy">';
    }
    return $cat['slug'] === 'hozbloki'
        ? '<img' . ($class ? ' class="' . e($class) . '"' : '') . ' src="/assets/hozbloki.jpg" alt="' . e($alt ?: 'Деревянный хозблок на бетонных блоках: окно, открытая дверь и односкатная крыша') . '">'
        : illustration($cat['slug'], $alt ?: $cat['title']);
}

// Карта Владимирской области: контур из OpenStreetMap, города доставки, цвет — пояс доставки от Мурома.
function region_map(string $current = ''): string
{
    $map = json_decode(file_get_contents(APP_DIR . '/region-map.json'), true);
    // Подписи там, где города стоят тесно: смещение подписи относительно точки и выравнивание.
    $labels = [
        'aleksandrov' => [0, -10, 'middle'], 'strunino' => [-8, 4, 'end'], 'karabanovo' => [6, 14, 'start'],
        'kirzhach' => [-8, 4, 'end'], 'kolchugino' => [8, 4, 'start'], 'yurev-polsky' => [0, -10, 'middle'],
        'lakinsk' => [-6, -8, 'end'], 'sobinka' => [-6, 14, 'end'], 'raduzhny' => [6, 14, 'start'],
        'vladimir' => [-9, -6, 'end'], 'suzdal' => [0, -10, 'middle'], 'kameshkovo' => [0, -10, 'middle'],
        'kovrov' => [8, -6, 'start'], 'petushki' => [0, 16, 'middle'], 'pokrov' => [-8, 4, 'end'],
        'murom' => [0, 26, 'middle'], 'gorokhovets' => [-4, -11, 'middle'], 'vyazniki' => [-9, 4, 'end'],
    ];
    $colors = ['#2f6b45', '#5f8f3e', '#c9923a', '#c45c32'];
    $dots = '';
    $murom = null;
    foreach (cities() as $city) {
        if (!isset($city['x'])) continue;
        $zone = delivery_zone((int) $city['km']);
        $l = $labels[$city['slug']] ?? [8, 4, 'start'];
        $isBase = (int) $city['km'] === 0;
        $active = $current === $city['slug'];
        $label = e($city['name']);
        $title = e($city['name'] . ($isBase ? ' — здесь изготавливаем постройки' : ': ' . distance_text($city) . ', ' . $zone['name']));
        if ($isBase) { $murom = $city; }
        $dots .= '<a href="/goroda/' . e($city['slug']) . '" class="map-city' . ($active ? ' is-current' : '') . ($isBase ? ' is-base' : '') . '"><title>' . $title . '</title>'
            . ($isBase
                ? '<circle cx="' . $city['x'] . '" cy="' . $city['y'] . '" r="13" fill="#3e2718"/><path d="M' . ($city['x'] - 7) . ' ' . ($city['y'] + 1) . ' l7 -7 l7 7 v6 h-14 z" fill="#f0c56e"/>'
                : '<circle cx="' . $city['x'] . '" cy="' . $city['y'] . '" r="' . ($active ? 8 : 6) . '" fill="' . $colors[$zone['index']] . '" stroke="#fff" stroke-width="2"/>')
            . '<text x="' . ($city['x'] + $l[0]) . '" y="' . ($city['y'] + $l[1]) . '" text-anchor="' . $l[2] . '">' . $label . '</text></a>';
    }
    // Лучи от Мурома к городам — направление рейсов.
    $rays = '';
    if ($murom) {
        foreach (cities() as $city) {
            if (!isset($city['x']) || (int) $city['km'] === 0) continue;
            $rays .= '<line x1="' . $murom['x'] . '" y1="' . $murom['y'] . '" x2="' . $city['x'] . '" y2="' . $city['y'] . '"/>';
        }
    }
    $legend = '';
    foreach (delivery_zones() as $i => $zone) {
        $legend .= '<li><span class="map-dot" style="background:' . $colors[$i] . '"></span>' . e(ucfirst_utf8($zone['name'])) . ' — ' . e($zone['label']) . ', ' . e($zone['fee']) . '</li>';
    }
    return '<figure class="region-map">'
        . '<div class="map-scroll"><svg viewBox="0 0 ' . $map['width'] . ' ' . $map['height'] . '" role="img" aria-labelledby="region-map-title">'
        . '<title id="region-map-title">Карта Владимирской области: города, куда доставляем дачные постройки из Мурома</title>'
        . '<path class="map-region" d="' . $map['path'] . '"/>'
        . '<g class="map-rays">' . $rays . '</g>'
        . '<g class="map-cities">' . $dots . '</g>'
        . '</svg></div>'
        . '<figcaption><ul class="map-legend"><li><span class="map-dot map-dot-base"></span>Муром — здесь изготавливаем постройки</li>' . $legend . '</ul>'
        . '<small class="muted">Граница области: © участники OpenStreetMap. Расстояния по дорогам — ориентир.</small></figcaption>'
        . '</figure>';
}

// Яндекс Метрика. Номер счётчика можно сменить в app/config.php (metrika_id), 0 — выключить.
function metrika_id(): int
{
    return (int) config('metrika_id', 113656532);
}

function metrika_head(): string
{
    $id = metrika_id();
    if (!$id || PHP_SAPI === 'cli-server') return '';
    return <<<HTML
<!-- Yandex.Metrika counter -->
<script type="text/javascript">
    (function(m,e,t,r,i,k,a){
        m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)};
        m[i].l=1*new Date();
        for (var j = 0; j < document.scripts.length; j++) {if (document.scripts[j].src === r) { return; }}
        k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)
    })(window, document,'script','https://mc.yandex.ru/metrika/tag.js?id={$id}', 'ym');

    ym({$id}, 'init', {ssr:true, webvisor:true, clickmap:true, ecommerce:"dataLayer", referrer: document.referrer, url: location.href, accurateTrackBounce:true, trackLinks:true});
    window.zdMetrikaId = {$id};
</script>
<!-- /Yandex.Metrika counter -->
HTML;
}

function metrika_noscript(): string
{
    $id = metrika_id();
    if (!$id || PHP_SAPI === 'cli-server') return '';
    return '<noscript><div><img src="https://mc.yandex.ru/watch/' . $id . '" style="position:absolute; left:-9999px;" alt="" /></div></noscript>';
}
