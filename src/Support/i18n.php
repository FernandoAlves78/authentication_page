<?php

declare(strict_types=1);

/**
 * Traduções em lang/{locale}.php — chaves flat com ponto (ex.: login.title).
 */
function supported_locales(): array
{
    return ['pt', 'en', 'es', 'it'];
}

function current_locale(): string
{
    static $resolved = null;
    if ($resolved !== null) {
        return $resolved;
    }
    $lang = strtolower(trim((string) ($_COOKIE['auth_lang'] ?? '')));
    if (!in_array($lang, supported_locales(), true)) {
        $lang = 'pt';
    }
    $resolved = $lang;

    return $resolved;
}

function lang_directory(): string
{
    return dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'lang';
}

/** @return array<string, string> */
function load_translations(string $locale): array
{
    static $cache = [];
    if (isset($cache[$locale])) {
        return $cache[$locale];
    }
    $requested = $locale;
    $path = lang_directory() . DIRECTORY_SEPARATOR . $requested . '.php';
    if (!is_file($path)) {
        $path = lang_directory() . DIRECTORY_SEPARATOR . 'pt.php';
    }
    /** @var array<string, string> $data */
    $data = require $path;
    $cache[$locale] = $data;

    return $data;
}

function t(string $key): string
{
    $locale = current_locale();
    $translations = load_translations($locale);
    if (isset($translations[$key])) {
        return $translations[$key];
    }
    $fallback = load_translations('pt');
    if (isset($fallback[$key])) {
        return $fallback[$key];
    }

    return $key;
}

function html_lang_attribute(): string
{
    $map = [
        'pt' => 'pt-BR',
        'en' => 'en',
        'es' => 'es',
        'it' => 'it',
    ];
    $loc = current_locale();

    return $map[$loc] ?? 'pt-BR';
}
