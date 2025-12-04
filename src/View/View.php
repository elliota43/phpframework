<?php

declare(strict_types=1);

namespace Framework\View;

class View
{
    protected static string $basePath;

    public static function setBasePath(string $path): void
    {
        static::$basePath = rtrim($path, '/');
    }

    public static function make(string $name, array $data = []): string
    {
        if (!isset(static::$basePath)) {
            throw new \RuntimeException('View base path not set. Call View::setBasePath() in bootstrap.');
        }

        // "home" -> "home.php"
        // "users.index" -> "users/index.php"
        $relativePath = str_replace('.', '/', $name) .'.php';

        $fullPath = static::$basePath . '/' . $relativePath;

        if (!file_exists($fullPath)) {
            throw new \RuntimeException("View [{$name}] not found at [{$fullPath}].");
        }

        // make $data keys available as variables inside the template
        extract($data, EXTR_SKIP);

        // simple escape helper available in all views
        $e = fn ($value) => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');

        ob_start();
        require $fullPath;
        return ob_get_clean();
    }
}