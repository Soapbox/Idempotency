<?php

namespace SoapBox\Idempotency\Tests\Unit\Laravel;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use SoapBox\Idempotency\Tests\TestCase;
use SoapBox\Idempotency\Laravel\Middleware;

class MiddlewareTest extends TestCase
{
    private $middleware;

    private $response;

    protected function setUp(): void
    {
        parent::setUp();

        config(['idempotency.header' => 'Idempotency-Key']);
        $this->middleware = new Middleware();
        $this->response = new Response('content');
    }
    /**
     * @test
     */
    public function it_will_return_the_cached_response_the_second_time_through_for_post_requests()
    {
        $request = Request::create('http://test.test', 'POST');
        $request->headers->set('Idempotency-Key', 'unique-key');

        $result = $this->middleware->handle($request, function () {
            return $this->response;
        });

        $this->assertSame((string) $this->response, (string) $result);

        $result = $this->middleware->handle($request, function () {
            return new Response('different content');
        });

        $this->assertSame((string) $this->response, (string) $result);
    }

    /**
     * @test
     */
    public function it_will_return_the_cached_response_the_second_time_through_for_put_requests()
    {
        $request = Request::create('http://test.test', 'PUT');
        $request->headers->set('Idempotency-Key', 'unique-key');


        $result = $this->middleware->handle($request, function () {
            return $this->response;
        });

        $this->assertSame((string) $this->response, (string) $result);

        $result = $this->middleware->handle($request, function () {
            return new Response('different content');
        });

        $this->assertSame((string) $this->response, (string) $result);
    }

    /**
     * @test
     */
    public function it_will_return_the_cached_response_the_second_time_through_for_patch_requests()
    {
        $request = Request::create('http://test.test', 'PATCH');
        $request->headers->set('Idempotency-Key', 'unique-key');

        $result = $this->middleware->handle($request, function () {
            return $this->response;
        });

        $this->assertSame((string) $this->response, (string) $result);

        $result = $this->middleware->handle($request, function () {
            return new Response('different content');
        });

        $this->assertSame((string) $this->response, (string) $result);
    }

    /**
     * @test
     */
    public function it_will_not_return_a_cached_response_if_there_is_not_cached_response_for_the_provided_idempotency_key()
    {
        $request = Request::create('http://test.test', 'POST');
        $request->headers->set('Idempotency-Key', 'unique-key');

        $result = $this->middleware->handle($request, function () {
            return $this->response;
        });

        $this->assertSame((string) $this->response, (string) $result);

        $request = Request::create('http://test.test', 'POST');
        $request->headers->set('Idempotency-Key', 'other-key');

        $result = $this->middleware->handle($request, function () {
            return new Response('different content');
        });

        $this->assertNotSame((string) $this->response, (string) $result);
    }

    /**
     * @test
     */
    public function it_will_not_cache_the_response_if_no_idempotency_key_is_provided()
    {
        $request = Request::create('http://test.test', 'POST');

        $result = $this->middleware->handle($request, function () {
            return $this->response;
        });

        $this->assertSame((string) $this->response, (string) $result);

        $result = $this->middleware->handle($request, function () {
            return new Response('different content');
        });

        $this->assertNotSame((string) $this->response, (string) $result);
    }

    /**
     * @test
     */
    public function it_will_not_cache_the_response_for_a_get_request()
    {
        $request = Request::create('http://test.test', 'GET');
        $request->headers->set('Idempotency-Key', 'unique-key');

        $result = $this->middleware->handle($request, function () {
            return $this->response;
        });

        $this->assertSame((string) $this->response, (string) $result);

        $result = $this->middleware->handle($request, function () {
            return new Response('different content');
        });

        $this->assertNotSame((string) $this->response, (string) $result);
    }

    /**
     * @test
     */
    public function it_will_not_cache_the_response_for_a_delete_request()
    {
        $request = Request::create('http://test.test', 'DELETE');
        $request->headers->set('Idempotency-Key', 'unique-key');

        $result = $this->middleware->handle($request, function () {
            return $this->response;
        });

        $this->assertSame((string) $this->response, (string) $result);

        $result = $this->middleware->handle($request, function () {
            return new Response('different content');
        });

        $this->assertNotSame((string) $this->response, (string) $result);
    }

    /**
     * @test
     */
    public function it_will_not_returned_the_cached_response_if_the_header_is_wrong()
    {
        $request = Request::create('http://test.test', 'POST');
        $request->headers->set('Idempotency-Key', 'unique-key');

        $result = $this->middleware->handle($request, function () {
            return $this->response;
        });

        $this->assertSame((string) $this->response, (string) $result);

        $request = Request::create('http://test.test', 'POST');
        $request->headers->set('Incorrect-Key', 'unique-key');

        $result = $this->middleware->handle($request, function () {
            return new Response('different content');
        });

        $this->assertNotSame((string) $this->response, (string) $result);
    }

    /**
     * @test
     */
    public function it_does_not_execute_the_next_middleware_when_it_returns_a_cached_response()
    {
        $request = Request::create('http://test.test', 'POST');
        $request->headers->set('Idempotency-Key', 'unique-key');

        $result = $this->middleware->handle($request, function () {
            return $this->response;
        });

        $this->assertSame((string) $this->response, (string) $result);

        $executed = false;
        $result = $this->middleware->handle($request, function () use (&$executed) {
            $executed = true;
            return new Response('different content');
        });

        $this->assertFalse($executed);
        $this->assertSame((string) $this->response, (string) $result);
    }

    /**
     * @test
     */
    public function it_executes_the_next_middleware_when_it_does_not_return_a_cached_response()
    {
        $request = Request::create('http://test.test', 'POST');

        $result = $this->middleware->handle($request, function () {
            return $this->response;
        });

        $this->assertSame((string) $this->response, (string) $result);

        $executed = false;
        $result = $this->middleware->handle($request, function () use (&$executed) {
            $executed = true;
            return new Response('different content');
        });

        $this->assertTrue($executed);
        $this->assertNotSame((string) $this->response, (string) $result);
    }
}
