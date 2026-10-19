<?php
// Информационные страницы, блог, новости, проекты, отзывы.

function published(array $items): array
{
    return array_values(array_filter($items, function ($item) { return !empty($item['published']); }));
}

function page_delivery(): void
{
    $faqs = [
        ['q' => 'Постройку привозят собранной?', 'a' => 'Да. Мы изготавливаем постройку под заказ и привозим её целиком, готовой. На участке её только выгружают и ставят на подготовленное основание.'],
        ['q' => 'Откуда считается доставка?', 'a' => 'От Мурома: здесь мы изготавливаем постройки. Поэтому Муром и Меленки — ближний пояс, а запад области — Александров, Киржач, Покров — самый дальний.'],
        ['q' => 'Сколько стоит доставка?', 'a' => 'Ближний пояс до 60 км от Мурома — ориентир от 2 500 ₽, средний до 150 км — от 4 500 ₽, дальний до 215 км — от 7 000 ₽, дальше — от 9 500 ₽. Точную сумму называем в ответе на заявку.'],
        ['q' => 'Что делать, если ворота узкие?', 'a' => 'Пришлите фото проезда. Варианты: манипулятор через забор или постройка под ширину ваших ворот — мы всё равно делаем под заказ.'],
    ];
    $cities = cities();
    usort($cities, function ($a, $b) { return $a['km'] <=> $b['km']; });
    ob_start(); ?>
<article class="wrap page-hero">
  <?= breadcrumbs([['name' => 'Доставка', 'href' => '/dostavka']]) ?>
  <div class="text-col">
    <h1>Доставка готовых построек из Мурома по Владимирской области</h1>
    <?= paragraphs(setting('deliveryText')) ?>
    <h2>Как читается срок</h2>
    <p>Считаем от Мурома, где изготавливаем постройки. Муром и Меленки часто закрываем в день готовности или на следующий день. Средний пояс — Вязники, Ковров, Судогда, Владимир — рейсом по направлению. Дальний запад — Петушки, Покров, Александров — день называем после подтверждения заказа, а не «на завтра» с порога.</p>
    <h2>Что подготовить ко дню доставки</h2>
    <p>Основание: блоки, столбики или сваи, выставленные по уровню. Открытые ворота и свободный разворот. Фото поворота, если сомневаетесь, что пройдёт машина с готовой постройкой. Зимой — расчищенный подъезд. Подробности по грунту — на странице вашего <a href="/goroda">города</a>.</p>
  </div>
  <h2>Карта доставки</h2>
  <?= region_map() ?>
  <h2>Пояса доставки по городам</h2>
  <div class="table-wrap"><table>
    <thead><tr><th>Город</th><th>Расстояние</th><th>Пояс</th><th>Доставка</th></tr></thead>
    <tbody><?php foreach ($cities as $city): $zone = delivery_zone((int) $city['km']); ?>
      <tr><td><a href="/goroda/<?= e($city['slug']) ?>"><?= e($city['name']) ?></a></td><td><?= e(distance_text($city)) ?></td><td><?= e($zone['name']) ?></td><td><?= e($zone['fee']) ?></td></tr>
    <?php endforeach; ?></tbody>
  </table></div>
  <h2>Вопросы о доставке</h2>
  <?= faq_list($faqs) ?>
  <?= faq_json_ld($faqs) ?>
</article>
<?php
    render(page_meta('Доставка готовых дачных построек из Мурома по области', 'Доставка готовых дачных туалетов, хозблоков, душей и купелей из Мурома по Владимирской области: пояса и цены, карта, сроки, подъезд. Собирать на участке не нужно.', '/dostavka'), ob_get_clean());
}

function page_how_to_order(): void
{
    $steps = [
        ['Выбор', 'Модель из каталога или свой размер. Сразу смотрите блок «не входит»: основание, доставка, яма или ёмкость.'],
        ['Заявка', 'Телефон, город и, если есть, фото ворот. Форма сохраняется в заявках, даже пока номер мастерской не заполнен.'],
        ['Уточнение', 'Подъезд, грунт, нужен ли душ внутри, сторона двери. Мы работаем только под заказ, поэтому всё это можно поменять.'],
        ['Цена', 'Называем состав и сумму с доставкой до начала изготовления.'],
        ['Изготовление', setting('leadTime')],
        ['Доставка', 'Привозим постройку готовой, целиком — собирать на участке ничего не нужно. День зависит от пояса, подробности на странице доставки.'],
    ];
    ob_start(); ?>
<article class="wrap page-hero">
  <?= breadcrumbs([['name' => 'Как заказать', 'href' => '/kak-zakazat']]) ?>
  <h1>Как заказать дачную постройку</h1>
  <ol><?php foreach ($steps as $step): ?><li><b><?= e($step[0]) ?>.</b> <?= e($step[1]) ?></li><?php endforeach; ?></ol>
  <p><a href="/katalog">Каталог</a> · <a href="/individualnye-razmery">Свой размер</a> · <a href="/obratnaya-svyaz">Обратная связь</a></p>
</article>
<?php
    render(page_meta('Как заказать дачную постройку под заказ', 'Как заказать дачный туалет, хозблок, душ или купель: выбор модели, заявка, уточнение подъезда, фиксация цены, изготовление под заказ и доставка готовой постройки.', '/kak-zakazat'), ob_get_clean());
}

function page_about(): void
{
    ob_start(); ?>
<article class="wrap page-hero">
  <?= breadcrumbs([['name' => 'О мастерской', 'href' => '/o-nas']]) ?>
  <h1>О мастерской</h1>
  <?= paragraphs(setting('aboutText')) ?>
  <h2>Как работаем</h2>
  <p>Работаем только под заказ: сначала размер, город и подъезд, потом дерево. Постройку привозим готовой, поэтому на даче нет сборки, обрезков и бригады на несколько дней. Не обещаем «под ключ», пока в смете не названы основание и доставка. Земля, электрика и чужие ёмкости остаются отдельными строками.</p>
  <p>Регион один: Владимирская область. Федеральную витрину с одинаковым текстом на сто городов не делаем — у Коврова, Мещёры и Ополья разные грунт и подъезд, это написано на страницах городов.</p>
</article>
<?php
    render(page_meta('О мастерской «Загородный дом» — дачные постройки под заказ', 'Кто делает дачные туалеты, хозблоки, души и купели под заказ во Владимирской области и привозит их готовыми. Без выдуманных лет на рынке и чужих реквизитов.', '/o-nas'), ob_get_clean());
}

function page_custom(): void
{
    ob_start(); ?>
<div class="wrap page-hero">
  <?= breadcrumbs([['name' => 'Свои размеры', 'href' => '/individualnye-razmery']]) ?>
  <div class="custom-cta">
    <article>
      <h1>Дачные постройки по индивидуальным размерам</h1>
      <p class="lead">Мы и так делаем всё под заказ. Свой размер нужен, когда каталог не ложится на забор, планировку или узкие ворота.</p>
      <p>Напишите тип постройки, длину и ширину, город и телефон. В комментарии полезны ширина ворот, сторона входа и есть ли уже столбики. Если сомневаетесь на десять сантиметров, так и напишите: вилка честнее одной неверной цифры.</p>
      <p>Постройку по вашим размерам привезём готовой, поэтому габарит сразу сверяем с проездом к участку. <?= e(setting('leadTime')) ?> Цену своего размера не копируем с похожей карточки — её называют после размеров.</p>
    </article>
    <?= lead_form(['kind' => 'custom', 'page' => '/individualnye-razmery', 'title' => 'Заявка на свой размер']) ?>
  </div>
</div>
<?php
    render(page_meta('Дачные постройки по индивидуальным размерам', 'Закажите дачный туалет, хозблок, душ или купель по своим размерам. Изготовим под заказ и привезём готовыми по Владимирской области. Цена — после размеров.', '/individualnye-razmery'), ob_get_clean());
}

function page_contacts(): void
{
    $s = settings();
    $phone = tel_href((string) ($s['phone'] ?? ''));
    ob_start(); ?>
<div class="wrap page-hero">
  <?= breadcrumbs([['name' => 'Контакты', 'href' => '/kontakty']]) ?>
  <div class="grid-2">
    <article>
      <h1>Контакты</h1>
      <p><b><?= e($s['companyName'] ?? '') ?></b></p>
      <p>Телефон: <?= $phone ? '<a href="' . e($phone) . '">' . e($s['phone']) . '</a>' : e($s['phone'] ?? '') ?></p>
      <p>Адрес: <?= e($s['address'] ?? '') ?> · <a href="https://yandex.ru/maps/?text=<?= rawurlencode((string) ($s['address'] ?? '')) ?>" target="_blank" rel="noopener">на карте</a></p>
      <p>Часы: <?= e($s['hours'] ?? '') ?></p>
      <p>Изготавливаем постройки в Муроме и привозим готовыми по всей Владимирской области. <a href="/goroda">Карта доставки</a>.</p>
    </article>
    <?= lead_form(['kind' => 'feedback', 'page' => '/kontakty', 'title' => 'Написать']) ?>
  </div>
</div>
<?php
    render(page_meta('Контакты мастерской «Загородный дом» в Муроме', 'Телефон и адрес мастерской дачных построек в Муроме: ' . setting('address') . ', ' . setting('phone') . '. Работаем ' . setting('hours') . ', доставляем по Владимирской области.', '/kontakty'), ob_get_clean());
}

function page_feedback(): void
{
    ob_start(); ?>
<div class="wrap page-hero">
  <?= breadcrumbs([['name' => 'Обратная связь', 'href' => '/obratnaya-svyaz']]) ?>
  <div class="grid-2">
    <article>
      <h1>Обратная связь</h1>
      <p>Напишите, что нужно поставить, в каком городе и как к вам подъехать. Заявка попадает в раздел «Заявки» админки и не теряется.</p>
    </article>
    <?= lead_form(['kind' => 'feedback', 'page' => '/obratnaya-svyaz', 'title' => 'Заявка']) ?>
  </div>
</div>
<?php
    render(page_meta('Обратная связь с мастерской', 'Заявка на дачный туалет, хозблок, душ или купель под заказ с доставкой по Владимирской области. Перезвоним и назовём цену до изготовления.', '/obratnaya-svyaz'), ob_get_clean());
}

function page_thanks(): void
{
    $html = '<div class="wrap page-hero"><h1>Заявка отправлена</h1><p>Мы записали телефон, город и комментарий и перезвоним в рабочее время: ' . e(setting('hours')) . '.</p><p><a href="/katalog">Вернуться в каталог</a></p></div>';
    render(page_meta('Заявка отправлена', 'Заявка сохранена.', '/spasibo', ['index' => false]), $html);
}

function page_warranty(): void
{
    ob_start(); ?>
<article class="wrap page-hero">
  <?= breadcrumbs([['name' => 'Гарантия', 'href' => '/garantiya']]) ?>
  <h1>Гарантия и поддержка</h1>
  <p class="note">Это черновик. Не копируйте его в договор, пока не поправите формулировку в админке под свою мастерскую.</p>
  <?= paragraphs(setting('warrantyText')) ?>
  <h2>Поддержка</h2>
  <p><?= e(setting('supportText')) ?></p>
  <p>Срок изготовления: <?= e(setting('leadTime')) ?></p>
</article>
<?php
    render(page_meta('Гарантия и поддержка построек', 'Гарантия на дачные постройки под заказ и как устроена поддержка после доставки. Черновик условий, текст для договора нужно заменить.', '/garantiya'), ob_get_clean());
}

function page_faq(): void
{
    $faqs = published(db()['faqs']);
    usort($faqs, function ($a, $b) { return ($a['sort'] ?? 0) <=> ($b['sort'] ?? 0); });
    $html = '<div class="wrap page-hero">' . breadcrumbs([['name' => 'Вопросы', 'href' => '/faq']]) . '<h1>Вопросы и ответы</h1>' . faq_list($faqs) . faq_json_ld($faqs) . '</div>';
    render(page_meta('Вопросы о заказе дачных построек', 'Ответы про цены, изготовление под заказ, свои размеры, доставку готовых построек, зиму и гарантию на дачные постройки во Владимирской области.', '/faq'), $html);
}

function page_reviews(): void
{
    $reviews = published(db()['reviews']);
    ob_start(); ?>
<div class="wrap page-hero">
  <?= breadcrumbs([['name' => 'Отзывы', 'href' => '/otzyvy']]) ?>
  <h1>Отзывы</h1>
  <p class="note">Карточки с пометкой «пример» написаны как образец тона. Это не проверенные отзывы клиентов. Замените их в админке, когда появятся свои.</p>
  <?= review_form('/otzyvy') ?>
  <div class="grid-2 section">
    <?php foreach ($reviews as $review): $product = $review['productSlug'] ? find_by(published_products(), 'slug', $review['productSlug']) : null; ?>
    <blockquote class="paper">
      <?= !empty($review['demo']) ? '<span class="badge badge-demo">Пример отзыва, не клиент</span>' : '' ?>
      <p><?= e($review['text']) ?></p>
      <footer class="muted"><?= e($review['author']) ?>, <?= e($review['city']) ?><?= $product ? ' · <a href="' . e(product_url($product)) . '">' . e($product['title']) . '</a>' : '' ?></footer>
    </blockquote>
    <?php endforeach; ?>
  </div>
</div>
<?php
    render(page_meta('Отзывы о дачных постройках', 'Отзывы о дачных туалетах, хозблоках, душах и купелях. Демонстрационные тексты помечены и не выдаются за слова реальных клиентов.', '/otzyvy'), ob_get_clean());
}

function page_blog(): void
{
    $posts = published(db()['posts']);
    ob_start(); ?>
<div class="wrap page-hero">
  <?= breadcrumbs([['name' => 'Блог', 'href' => '/blog']]) ?>
  <h1>Блог</h1>
  <p class="lead">Короткие практические разборы перед заказом: размеры, грунт, зима и свои габариты. Без воды про «уют и гармонию».</p>
  <div class="grid-2">
    <?php foreach ($posts as $post): ?>
    <article class="paper"><p class="muted"><?= e(format_date($post['date'])) ?></p><h2><a href="/blog/<?= e($post['slug']) ?>"><?= e($post['title']) ?></a></h2><p><?= e($post['excerpt']) ?></p></article>
    <?php endforeach; ?>
  </div>
</div>
<?php
    render(page_meta('Блог о дачных постройках', 'Как выбрать дачный туалет, хозблок, летний душ и купель, как подготовить основание и что делать зимой. Статьи мастерской для Владимирской области.', '/blog'), ob_get_clean());
}

function page_post(string $slug): void
{
    $posts = published(db()['posts']);
    $post = find_by($posts, 'slug', $slug);
    if (!$post) not_found();
    $others = array_slice(array_values(array_filter($posts, function ($p) use ($slug) { return $p['slug'] !== $slug; })), 0, 3);
    $labels = category_labels();
    ob_start(); ?>
<article class="wrap page-hero">
  <?= breadcrumbs([['name' => 'Блог', 'href' => '/blog'], ['name' => $post['title'], 'href' => "/blog/$slug"]]) ?>
  <div class="text-col">
    <p class="muted"><?= e(format_date($post['date'])) ?></p>
    <h1><?= e($post['title']) ?></h1>
    <p class="lead"><?= e($post['excerpt']) ?></p>
    <?= rich_text($post['body']) ?>
    <h2>Каталог по теме</h2>
    <p class="chips"><?php foreach ($post['tags'] ?? [] as $tag): if (isset($labels[$tag])): ?><a href="/<?= e($tag) ?>"><?= e($labels[$tag]) ?></a><?php endif; endforeach; ?><a href="/goroda">Города области</a></p>
    <?php if ($others): ?>
    <h2>Ещё заметки</h2>
    <ul class="read-list"><?php foreach ($others as $item): ?><li><a href="/blog/<?= e($item['slug']) ?>"><?= e($item['title']) ?></a></li><?php endforeach; ?></ul>
    <?php endif; ?>
  </div>
  <?= json_ld([
      '@context' => 'https://schema.org', '@type' => 'Article', 'headline' => $post['title'],
      'datePublished' => $post['date'], 'dateModified' => $post['date'], 'description' => $post['excerpt'],
      'mainEntityOfPage' => site_url() . "/blog/$slug",
      'author' => ['@type' => 'Organization', 'name' => setting('companyName')],
      'publisher' => ['@type' => 'Organization', 'name' => setting('companyName')],
  ]) ?>
</article>
<?php
    render(page_meta($post['seoTitle'] ?: $post['title'], $post['seoDescription'] ?: $post['excerpt'], "/blog/$slug", ['type' => 'article']), ob_get_clean());
}

function page_news(): void
{
    $items = published(db()['news']);
    ob_start(); ?>
<div class="wrap page-hero">
  <?= breadcrumbs([['name' => 'Новости', 'href' => '/akcii']]) ?>
  <h1>Новости</h1>
  <p class="lead">Что нового в мастерской: модели, размеры и доставка.</p>
  <div class="grid-2">
    <?php foreach ($items as $item): ?>
    <article class="paper"><p class="kicker">Новость · <?= e(format_date($item['date'])) ?></p><h2><a href="/akcii/<?= e($item['slug']) ?>"><?= e($item['title']) ?></a></h2><p><?= e($item['excerpt']) ?></p></article>
    <?php endforeach; ?>
  </div>
</div>
<?php
    render(page_meta('Новости мастерской «Загородный дом»', 'Новости мастерской: дачные постройки под заказ, доставка по Владимирской области, новые модели и размеры.', '/akcii'), ob_get_clean());
}

function page_news_item(string $slug): void
{
    $item = find_by(published(db()['news']), 'slug', $slug);
    if (!$item) not_found();
    ob_start(); ?>
<article class="wrap page-hero">
  <?= breadcrumbs([['name' => 'Новости', 'href' => '/akcii'], ['name' => $item['title'], 'href' => "/akcii/$slug"]]) ?>
  <div class="text-col">
    <p class="muted"><?= e(format_date($item['date'])) ?></p>
    <h1><?= e($item['title']) ?></h1>
    <?= rich_text($item['body']) ?>
  </div>
  <?= json_ld([
      '@context' => 'https://schema.org', '@type' => 'NewsArticle', 'headline' => $item['title'], 'datePublished' => $item['date'],
      'description' => $item['excerpt'], 'mainEntityOfPage' => site_url() . "/akcii/$slug",
      'author' => ['@type' => 'Organization', 'name' => setting('companyName')],
  ]) ?>
</article>
<?php
    render(page_meta($item['seoTitle'] ?: $item['title'], $item['seoDescription'] ?: $item['excerpt'], "/akcii/$slug", ['type' => 'article']), ob_get_clean());
}

function page_projects(): void
{
    $projects = published(db()['projects']);
    ob_start(); ?>
<div class="wrap page-hero">
  <?= breadcrumbs([['name' => 'Проекты', 'href' => '/proekty']]) ?>
  <h1>Проекты</h1>
  <p class="note">Если на карточке написано «пример проекта», это учебная схема для витрины, не сдача объекта и не отзыв клиента.</p>
  <div class="grid-2 section">
    <?php foreach ($projects as $project): ?>
    <article class="card">
      <?= !empty($project['photos'][0]) ? '<img src="' . e($project['photos'][0]) . '" alt="' . e($project['title']) . '" loading="lazy">' : illustration($project['categorySlug'], $project['title']) ?>
      <div class="body">
        <?= !empty($project['demo']) ? '<span class="badge badge-demo">Пример проекта</span>' : '' ?>
        <h2><a href="/proekty/<?= e($project['slug']) ?>"><?= e($project['title']) ?></a></h2>
        <p class="muted"><?= e($project['city']) ?> · <?= e($project['year']) ?></p>
        <p><?= e($project['excerpt']) ?></p>
      </div>
    </article>
    <?php endforeach; ?>
  </div>
</div>
<?php
    render(page_meta('Примеры проектов дачных построек', 'Примеры установки хозблока, купели и душа на участках Владимирской области. Пока нет реальных фото, карточки подписаны как примеры.', '/proekty'), ob_get_clean());
}

function page_project(string $slug): void
{
    $project = find_by(published(db()['projects']), 'slug', $slug);
    if (!$project) not_found();
    ob_start(); ?>
<article class="wrap page-hero">
  <?= breadcrumbs([['name' => 'Проекты', 'href' => '/proekty'], ['name' => $project['title'], 'href' => "/proekty/$slug"]]) ?>
  <div class="text-col">
    <?= !empty($project['demo']) ? '<p class="note">Пример проекта. Не является сдачей реального заказа и не описывает конкретного человека.</p>' : '' ?>
    <h1><?= e($project['title']) ?></h1>
    <p class="muted"><?= e($project['city']) ?> · <?= e($project['year']) ?></p>
    <?php if (!empty($project['photos'])): foreach ($project['photos'] as $photo): ?><div class="media-frame" style="margin-bottom:1rem"><img src="<?= e($photo) ?>" alt="<?= e($project['title']) ?>"></div><?php endforeach; else: ?><div class="media-frame"><?= illustration($project['categorySlug'], $project['title']) ?></div><?php endif; ?>
    <?= rich_text($project['description']) ?>
    <p><a href="/<?= e($project['categorySlug']) ?>">Смотреть модели этого типа</a></p>
  </div>
</article>
<?php
    render(page_meta($project['seoTitle'] ?: $project['title'], $project['seoDescription'] ?: $project['excerpt'], "/proekty/$slug"), ob_get_clean());
}
