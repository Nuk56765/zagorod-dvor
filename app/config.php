<?php
// Настройки сайта.
return [
    // Адрес сайта без слеша в конце. Нужен для canonical, карты сайта и robots.txt.
    'site_url' => 'https://zagorod-dvor.ru',

    // Перенаправлять http -> https и www -> без www.
    'force_https' => false,

    // Почта для уведомлений о заявках. Пусто — уведомления не отправляются.
    'notify_email' => '',

    // Номер счётчика Яндекс Метрики, 0 — выключить.
    'metrika_id' => 0,
];