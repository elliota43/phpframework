<?php

namespace Framework\Http;

class Request
{

    public function __construct(
        protected array $get,
        protected array $post,
        protected array $server,
        protected array $cookies,
        protected array $files
    )
    {}
    public static function fromGlobals()
    {
        return new self($_GET, $_POST, $_SERVER, $_COOKIE, $_FILES);
    }

    public function method(): string
    {
        return strtoupper($this->server['REQUEST_METHOD'] ?? 'GET');
    }

    public function uri(): string
    {
        // @todo refine to strip query string
        return $this->server['REQUEST_URI'] ?? '/';
    }

    public function query(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->get;
        }

        return $this->get[$key] ?? $default;
    }

    public function getMethod(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    public function getPath(): string
    {
        // Parse request URI and strip query string
        $uri = $_SERVER['REQUEST_URI'] ?? '/';

        $path = parse_url($uri, PHP_URL_PATH);

        return $path ?: '/';
    }

   
}