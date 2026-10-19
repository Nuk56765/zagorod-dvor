<?php
// Простая разметка текстов из админки: ## заголовки, - списки, **жирный**, [ссылка](/адрес).

function safe_href(string $href): string
{
    if (starts_with($href, '/') && !starts_with($href, '//')) return $href;
    if (starts_with($href, 'https://') || starts_with($href, 'http://')) return $href;
    return '/';
}

function rich_inline(string $text): string
{
    $parts = preg_split('/(\*\*[^*]+\*\*|\[[^\]]+\]\([^)]+\))/u', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
    $out = '';
    foreach ($parts as $part) {
        if (preg_match('/^\*\*(.+)\*\*$/su', $part, $m)) {
            $out .= '<strong>' . e($m[1]) . '</strong>';
        } elseif (preg_match('/^\[([^\]]+)\]\(([^)]+)\)$/u', $part, $m)) {
            $href = safe_href($m[2]);
            $out .= starts_with($href, '/')
                ? '<a href="' . e($href) . '">' . e($m[1]) . '</a>'
                : '<a href="' . e($href) . '" rel="nofollow noopener">' . e($m[1]) . '</a>';
        } else {
            $out .= e($part);
        }
    }
    return $out;
}

function rich_text(string $text): string
{
    $html = '';
    foreach (preg_split('/\n\n+/', trim(str_replace("\r", '', $text))) as $block) {
        $lines = explode("\n", $block);
        if (starts_with($block, '## ')) {
            $html .= '<h2>' . rich_inline(substr($block, 3)) . '</h2>';
        } elseif (starts_with($block, '### ')) {
            $html .= '<h3>' . rich_inline(substr($block, 4)) . '</h3>';
        } elseif (count(array_filter($lines, function ($line) { return starts_with($line, '- '); })) === count($lines)) {
            $html .= '<ul>' . implode('', array_map(function ($line) { return '<li>' . rich_inline(substr($line, 2)) . '</li>'; }, $lines)) . '</ul>';
        } elseif (count(array_filter($lines, function ($line) { return (bool) preg_match('/^\d+\.\s/', $line); })) === count($lines)) {
            $html .= '<ol>' . implode('', array_map(function ($line) { return '<li>' . rich_inline(preg_replace('/^\d+\.\s+/', '', $line)) . '</li>'; }, $lines)) . '</ol>';
        } else {
            $html .= '<p>' . rich_inline(str_replace("\n", ' ', $block)) . '</p>';
        }
    }
    return '<div class="prose">' . $html . '</div>';
}

function paragraphs(string $text): string
{
    $html = '';
    foreach (preg_split('/\n\n+/', trim(str_replace("\r", '', $text))) as $paragraph) {
        if ($paragraph !== '') $html .= '<p>' . e($paragraph) . '</p>';
    }
    return $html;
}
