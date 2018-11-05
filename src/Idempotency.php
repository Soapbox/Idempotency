<?php

namespace SoapBox\Idempotency;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Contracts\Cache\Repository;

class Idempotency
{
    /**
     * Fetch the cache repository to use
     *
     * @return \Illuminate\Contracts\Cache\Repository
     */
    private static function getCache(): Repository
    {
        return Cache::store(config('idempotency.cache.store'));
    }

    /**
     * Get the prefix to use for the cache
     *
     * @return string
     */
    private static function getPrefix(): string
    {
        return config('idempotency.cache.prefix', '');
    }

    /**
     * Get a response from the cache for the given key
     *
     * @param string $idempotencyKey
     *
     * @return \Illuminate\Http\Response|null
     */
    public static function get(string $idempotencyKey): ?Response
    {
        $prefix = self::getPrefix();

        if ($cached = self::getCache()->get("{$prefix}{$idempotencyKey}")) {
            return new Response(...$cached);
        }

        return null;
    }

    /**
     * Add a response to the cache for the given key
     *
     * @param string $idempotencyKey
     * @param \Illuminate\Http\Response $response
     *
     * @return void
     */
    public static function add(string $idempotencyKey, Response $response): void
    {
        $prefix = self::getPrefix();

        $cached = [
            $response->getContent(),
            $response->getStatusCode(),
            $response->headers->allPreserveCase(),
        ];
        self::getCache()->put("{$prefix}{$idempotencyKey}", $cached, config('idempotency.cache.ttl', 1440));
    }

    /**
     * Determing if the given request method is a request method that supports idempotency
     *
     * @param string $method
     *
     * @return bool
     */
    public static function supportedRequestMethod(string $method): bool
    {
        return in_array(strtolower($method), ['post', 'put', 'patch']);
    }
}
