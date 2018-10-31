<?php

namespace SoapBox\Idempotency\Tests\Unit\Laravel;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use SoapBox\Idempotency\Tests\TestCase;
use SoapBox\Idempotency\Laravel\Middleware;

class MiddlewareTest extends TestCase
{
    /**
     * @test
     */
    public function it_will_return_the_cached_response_the_second_time_through_for_post_requests()
    {
        config(['idempotency.header' => 'Idempotency-Key']);

        $request = Request::create('http://test.test', 'POST');
        $request->headers->set('Idempotency-Key', 'unique-key');

        $middleware = new Middleware();
        $response = new Response('content');

        $result = $middleware->handle($request, function () use ($response) {
            return $response;
        });

        $this->assertSame((string) $response, (string) $result);

        $result = $middleware->handle($request, function () use ($response) {
            return new Response('different content');
        });

        $this->assertSame((string) $response, (string) $result);
    }

    /**
     * @test
     */
    public function it_will_return_the_cached_response_the_second_time_through_for_put_requests()
    {
        config(['idempotency.header' => 'Idempotency-Key']);

        $request = Request::create('http://test.test', 'PUT');
        $request->headers->set('Idempotency-Key', 'unique-key');

        $middleware = new Middleware();
        $response = new Response('content');

        $result = $middleware->handle($request, function () use ($response) {
            return $response;
        });

        $this->assertSame((string) $response, (string) $result);

        $result = $middleware->handle($request, function () use ($response) {
            return new Response('different content');
        });

        $this->assertSame((string) $response, (string) $result);
    }

    /**
     * @test
     */
    public function it_will_return_the_cached_response_the_second_time_through_for_patch_requests()
    {
        config(['idempotency.header' => 'Idempotency-Key']);

        $request = Request::create('http://test.test', 'PATCH');
        $request->headers->set('Idempotency-Key', 'unique-key');

        $middleware = new Middleware();
        $response = new Response('content');

        $result = $middleware->handle($request, function () use ($response) {
            return $response;
        });

        $this->assertSame((string) $response, (string) $result);

        $result = $middleware->handle($request, function () use ($response) {
            return new Response('different content');
        });

        $this->assertSame((string) $response, (string) $result);
    }

    /**
     * @test
     */
    public function it_will_not_return_a_cached_response_if_there_is_not_cached_response_for_the_provided_idempotency_key()
    {
        config(['idempotency.header' => 'Idempotency-Key']);

        $request = Request::create('http://test.test', 'POST');
        $request->headers->set('Idempotency-Key', 'unique-key');

        $middleware = new Middleware();
        $response = new Response('content');

        $result = $middleware->handle($request, function () use ($response) {
            return $response;
        });

        $this->assertSame((string) $response, (string) $result);

        $request = Request::create('http://test.test', 'POST');
        $request->headers->set('Idempotency-Key', 'other-key');

        $result = $middleware->handle($request, function () use ($response) {
            return new Response('different content');
        });

        $this->assertNotSame((string) $response, (string) $result);
    }

    /**
     * @test
     */
    public function it_will_not_cache_the_response_if_no_idempotency_key_is_provided()
    {
        config(['idempotency.header' => 'Idempotency-Key']);

        $request = Request::create('http://test.test', 'POST');

        $middleware = new Middleware();
        $response = new Response('content');

        $result = $middleware->handle($request, function () use ($response) {
            return $response;
        });

        $this->assertSame((string) $response, (string) $result);

        $result = $middleware->handle($request, function () use ($response) {
            return new Response('different content');
        });

        $this->assertNotSame((string) $response, (string) $result);
    }

    /**
     * @test
     */
    public function it_will_not_cache_the_response_for_a_get_request()
    {
        config(['idempotency.header' => 'Idempotency-Key']);

        $request = Request::create('http://test.test', 'GET');
        $request->headers->set('Idempotency-Key', 'unique-key');

        $middleware = new Middleware();
        $response = new Response('content');

        $result = $middleware->handle($request, function () use ($response) {
            return $response;
        });

        $this->assertSame((string) $response, (string) $result);

        $result = $middleware->handle($request, function () use ($response) {
            return new Response('different content');
        });

        $this->assertNotSame((string) $response, (string) $result);
    }

    /**
     * @test
     */
    public function it_will_not_cache_the_response_for_a_delete_request()
    {
        config(['idempotency.header' => 'Idempotency-Key']);

        $request = Request::create('http://test.test', 'DELETE');
        $request->headers->set('Idempotency-Key', 'unique-key');

        $middleware = new Middleware();
        $response = new Response('content');

        $result = $middleware->handle($request, function () use ($response) {
            return $response;
        });

        $this->assertSame((string) $response, (string) $result);

        $result = $middleware->handle($request, function () use ($response) {
            return new Response('different content');
        });

        $this->assertNotSame((string) $response, (string) $result);
    }

    /**
     * @test
     */
    public function it_will_not_returned_the_cached_response_if_the_header_is_wrong()
    {
        config(['idempotency.header' => 'Idempotency-Key']);

        $request = Request::create('http://test.test', 'POST');
        $request->headers->set('Idempotency-Key', 'unique-key');

        $middleware = new Middleware();
        $response = new Response('content');

        $result = $middleware->handle($request, function () use ($response) {
            return $response;
        });

        $this->assertSame((string) $response, (string) $result);

        $request = Request::create('http://test.test', 'POST');
        $request->headers->set('Incorrect-Key', 'unique-key');

        $result = $middleware->handle($request, function () use ($response) {
            return new Response('different content');
        });

        $this->assertNotSame((string) $response, (string) $result);
    }

    /**
     * @test
     */
    public function it_does_not_execute_the_next_middleware_when_it_returns_a_cached_response()
    {
        config(['idempotency.header' => 'Idempotency-Key']);

        $request = Request::create('http://test.test', 'POST');
        $request->headers->set('Idempotency-Key', 'unique-key');

        $middleware = new Middleware();
        $response = new Response('content');

        $result = $middleware->handle($request, function () use ($response) {
            return $response;
        });

        $this->assertSame((string) $response, (string) $result);

        $executed = false;
        $result = $middleware->handle($request, function () use ($response, &$executed) {
            $executed = true;
            return new Response('different content');
        });

        $this->assertFalse($executed);
        $this->assertSame((string) $response, (string) $result);
    }

    /**
     * @test
     */
    public function it_executes_the_next_middleware_when_it_does_not_return_a_cached_response()
    {
        config(['idempotency.header' => 'Idempotency-Key']);

        $request = Request::create('http://test.test', 'POST');

        $middleware = new Middleware();
        $response = new Response('content');

        $result = $middleware->handle($request, function () use ($response) {
            return $response;
        });

        $this->assertSame((string) $response, (string) $result);

        $executed = false;
        $result = $middleware->handle($request, function () use ($response, &$executed) {
            $executed = true;
            return new Response('different content');
        });

        $this->assertTrue($executed);
        $this->assertNotSame((string) $response, (string) $result);
    }
}
