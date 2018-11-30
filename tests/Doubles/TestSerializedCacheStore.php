<?php

namespace SoapBox\Idempotency\Tests\Doubles;

use Illuminate\Cache\ArrayStore;

class TestSerializedCacheStore extends ArrayStore
{
    /**
     * Retrieve an item from the cache by key.
     *
     * @param  string|array  $key
     * @return mixed
     */
    public function get($key)
    {
        $value = $this->storage[$key] ?? null;

        return $value ? unserialize($value) : null;
    }

    /**
     * Store an item in the cache for a given number of minutes.
     *
     * @param string $key
     * @param mixed $value
     * @param float|int $minutes
     *
     * @return void
     */
    public function put($key, $value, $minutes)
    {
        parent::put($key, serialize($value), $minutes);
    }
}
