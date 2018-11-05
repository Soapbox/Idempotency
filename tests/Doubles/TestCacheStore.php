<?php

namespace SoapBox\Idempotency\Tests\Doubles;

use Illuminate\Cache\ArrayStore;

class TestCacheStore extends ArrayStore
{
    /**
     * @var array
     */
    private $timeToLive = [];

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
        $this->timeToLive[$key] = $minutes;
        parent::put($key, $value, $minutes);
    }

    /**
     * Remove an item from the cache.
     *
     * @param string $key
     *
     * @return bool
     */
    public function forget($key)
    {
        unset($this->timeToLive[$key]);
        return parent::forget($key);
    }

    /**
     * Remove all items from the cache.
     *
     * @return bool
     */
    public function flush()
    {
        $this->timeToLive = [];
        return parent::flush();
    }

    /**
     * Get the time to live for an item in the cache
     *
     * @param string $key
     *
     * @return float|int|null
     */
    public function getTimeToLive(string $key)
    {
        return $this->timeToLive[$key] ?? null;
    }
}
