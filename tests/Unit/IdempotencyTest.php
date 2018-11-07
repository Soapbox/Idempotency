<?php

namespace SoapBox\Idempotency\Tests\Unit;

use Illuminate\Http\Response;
use SoapBox\Idempotency\Idempotency;
use Illuminate\Support\Facades\Cache;
use SoapBox\Idempotency\Tests\TestCase;
use SoapBox\Idempotency\Tests\Doubles\TestCacheStore;

class IdempotencyTest extends TestCase
{
    /**
     * @test
     */
    public function adding_a_response_to_the_cache_caches_the_response_to_the_specified_store()
    {
        config([
            'cache.stores' => [
                'store1' => ['driver' => 'array'],
                'store2' => ['driver' => 'array'],
            ],
            'idempotency.cache.store' => 'store1',
        ]);

        Idempotency::add('unique-key', new Response());

        $this->assertTrue(Cache::store('store1')->has('idempotency:unique-key'));
        $this->assertFalse(Cache::store('store2')->has('idempotency:unique-key'));

        Cache::store('store1')->flush();
        Cache::store('store2')->flush();

        config(['idempotency.cache.store' => 'store2']);

        Idempotency::add('unique-key', new Response());

        $this->assertFalse(Cache::store('store1')->has('idempotency:unique-key'));
        $this->assertTrue(Cache::store('store2')->has('idempotency:unique-key'));
    }

    /**
     * @test
     */
    public function adding_a_response_to_the_cache_prefixes_the_key_with_the_prefix()
    {
        Idempotency::add('unique-key', new Response());
        $this->assertTrue(Cache::has('idempotency:unique-key'));

        config(['idempotency.cache.prefix' => 'prefix:']);

        Idempotency::add('unique-key', new Response());
        $this->assertTrue(Cache::has('idempotency:prefix:unique-key'));
    }

    /**
     * @test
     */
    public function adding_a_response_to_the_cache_will_set_the_time_to_live_to_the_configured_amount()
    {
        config([
            'idempotency.cache' => [
                'ttl' => 60,
                'store' => 'test',
            ],
            'cache.stores' => ['test' => ['driver' => 'test']],
        ]);
        Cache::extend('test', function ($app) {
            return Cache::repository(new TestCacheStore());
        });

        Idempotency::add('unique-key', new Response());
        $this->assertSame(60, Cache::store('test')->getTimeToLive('idempotency:unique-key'));
    }

    /**
     * @test
     */
    public function adding_a_response_to_the_cache_will_set_the_time_to_live_to_a_day_when_none_is_configured()
    {
        config([
            'idempotency.cache.store' => 'test',
            'cache.stores' => ['test' => ['driver' => 'test']],
        ]);
        Cache::extend('test', function ($app) {
            return Cache::repository(new TestCacheStore());
        });

        Idempotency::add('unique-key', new Response());
        $this->assertSame(1440, Cache::store('test')->getTimeToLive('idempotency:unique-key'));
    }

    /**
     * @test
     */
    public function it_returns_with_null_when_trying_to_get_a_response_for_a_key_that_is_not_in_the_cache()
    {
        $this->assertNull(Idempotency::get('idempotency:unique-key'));
    }

    /**
     * @test
     */
    public function getting_a_response_from_the_cache_returns_an_equivalent_response()
    {
        $response = new Response('content');
        $response->headers->set('Header', 'Value');

        Idempotency::add('unique-key', $response);
        $newResponse = Idempotency::get('unique-key');

        $this->assertSame((string) $response, (string) $newResponse);
    }

    /**
     * @test
     */
    public function getting_a_response_from_the_cache_fetched_the_response_from_the_correct_store()
    {
        config([
            'cache.stores' => [
                'store1' => ['driver' => 'array'],
                'store2' => ['driver' => 'array'],
            ],
            'idempotency.cache.store' => 'store1',
        ]);

        Idempotency::add('unique-key', $response = new Response());

        $this->assertSame((string) $response, (string) Idempotency::get('unique-key'));

        config(['idempotency.cache.store' => 'store2']);
        $this->assertNull(Idempotency::get('unique-key'));
    }

    /**
     * @test
     */
    public function getting_a_response_from_the_cache_will_prefix_the_key_with_the_prefix()
    {
        config(['idempotency.cache.prefix' => 'prefix:']);

        Idempotency::add('unique-key', $response = new Response());

        $this->assertSame((string) $response, (string) Idempotency::get('unique-key'));

        config(['idempotency.cache.prefix' => '']);
        $this->assertNull(Idempotency::get('unique-key'));
    }
}
