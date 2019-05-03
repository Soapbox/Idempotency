<?php

namespace SoapBox\Idempotency;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Contracts\Cache\Repository;
use Symfony\Component\HttpFoundation\Response;

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
        return 'idempotency:' . config('idempotency.cache.prefix', '');
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
        return self::getCache()->get("{$prefix}{$idempotencyKey}");
    }

    /**
     * Add a response to the cache for the given key
     *
     * @param string $idempotencyKey
     * @param \Symfony\Component\HttpFoundation\Response $response
     *
     * @return void
     */
    public static function add(string $idempotencyKey, Response $response): void
    {
        if (property_exists($response, 'exception')) {
            $response = clone $response;
            $response->exception = null;
        }

        $prefix = self::getPrefix();
        $ttl = Carbon::now()->addMinutes(config('idempotency.cache.ttl', 1440));
        self::getCache()->put("{$prefix}{$idempotencyKey}", $response, $ttl);
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
