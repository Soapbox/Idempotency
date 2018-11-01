<?php

namespace SoapBox\Idempotency\Tests\Unit\Guzzle;

use GuzzleHttp\RetryMiddleware;
use JSHayes\FakeRequests\ClientFactory;
use SoapBox\Idempotency\Tests\TestCase;
use SoapBox\Idempotency\Guzzle\Middleware;
use JSHayes\FakeRequests\Traits\Laravel\FakeRequests;

class MiddlewareTest extends TestCase
{
    use FakeRequests;

    /**
     * @test
     */
    public function it_applies_the_idempotency_header_for_post_requests()
    {
        config(['idempotency.header' => 'Idempotency-Key']);

        $handler = $this->fakeRequests();
        $client = resolve(ClientFactory::class)->make();
        $client->getConfig('handler')->push(new Middleware());

        $expectation = $handler->expects('post', 'https://test.test');

        $client->post('https://test.test');

        $this->assertNotEmpty($expectation->getRequest()->getHeaderLine('Idempotency-Key'));
    }

    /**
     * @test
     */
    public function it_applies_the_idempotency_header_for_put_requests()
    {
        config(['idempotency.header' => 'Idempotency-Key']);

        $handler = $this->fakeRequests();
        $client = resolve(ClientFactory::class)->make();
        $client->getConfig('handler')->push(new Middleware());

        $expectation = $handler->expects('put', 'https://test.test');

        $client->put('https://test.test');

        $this->assertNotEmpty($expectation->getRequest()->getHeaderLine('Idempotency-Key'));
    }

    /**
     * @test
     */
    public function it_applies_the_idempotency_header_for_patch_requests()
    {
        config(['idempotency.header' => 'Idempotency-Key']);

        $handler = $this->fakeRequests();
        $client = resolve(ClientFactory::class)->make();
        $client->getConfig('handler')->push(new Middleware());

        $expectation = $handler->expects('patch', 'https://test.test');

        $client->patch('https://test.test');

        $this->assertNotEmpty($expectation->getRequest()->getHeaderLine('Idempotency-Key'));
    }

    /**
     * @test
     */
    public function it_does_not_apply_the_idempotency_header_for_get_requests()
    {
        config(['idempotency.header' => 'Idempotency-Key']);

        $handler = $this->fakeRequests();
        $client = resolve(ClientFactory::class)->make();
        $client->getConfig('handler')->push(new Middleware());

        $expectation = $handler->expects('get', 'https://test.test');

        $client->get('https://test.test');

        $this->assertFalse($expectation->getRequest()->hasHeader('Idempotency-Key'));
    }

    /**
     * @test
     */
    public function it_does_not_apply_the_idempotency_header_for_delete_requests()
    {
        config(['idempotency.header' => 'Idempotency-Key']);

        $handler = $this->fakeRequests();
        $client = resolve(ClientFactory::class)->make();
        $client->getConfig('handler')->push(new Middleware());

        $expectation = $handler->expects('delete', 'https://test.test');

        $client->delete('https://test.test');

        $this->assertFalse($expectation->getRequest()->hasHeader('Idempotency-Key'));
    }

    /**
     * @test
     */
    public function it_applies_separate_idempotency_keys_to_each_request()
    {
        config(['idempotency.header' => 'Idempotency-Key']);

        $handler = $this->fakeRequests();
        $client = resolve(ClientFactory::class)->make();
        $client->getConfig('handler')->push(new Middleware());

        $expectation1 = $handler->expects('post', 'https://test.test');
        $expectation2 = $handler->expects('post', 'https://test.test');

        $client->post('https://test.test');
        $client->post('https://test.test');

        $key1 = $expectation1->getRequest()->getHeaderLine('Idempotency-Key');
        $key2 = $expectation2->getRequest()->getHeaderLine('Idempotency-Key');
        $this->assertNotEmpty($key1);
        $this->assertNotEmpty($key2);
        $this->assertNotSame($key1, $key2);
    }

    /**
     * @test
     */
    public function it_works_when_previous_middleware_doesnt_pass_the_request_by_reference()
    {
        config(['idempotency.header' => 'Idempotency-Key']);

        $handler = $this->fakeRequests();
        $middleware = function ($handler) {
            return function ($request, $options) use ($handler) {
                return $handler(with($request), $options);
            };
        };
        $client = resolve(ClientFactory::class)->make();
        $client->getConfig('handler')->push($middleware);
        $client->getConfig('handler')->push(new Middleware());

        $expectation = $handler->expects('post', 'https://test.test');

        $client->post('https://test.test');

        $this->assertNotEmpty($expectation->getRequest()->getHeaderLine('Idempotency-Key'));
    }
}
