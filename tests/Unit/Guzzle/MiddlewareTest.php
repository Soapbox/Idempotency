<?php

namespace SoapBox\Idempotency\Tests\Unit\Guzzle;

use JSHayes\FakeRequests\ClientFactory;
use SoapBox\Idempotency\Tests\TestCase;
use SoapBox\Idempotency\Guzzle\Middleware;
use JSHayes\FakeRequests\Traits\Laravel\FakeRequests;

class MiddlewareTest extends TestCase
{
    use FakeRequests;

    private $handler;

    private $client;

    protected function setUp(): void
    {
        parent::setUp();

        config(['idempotency.header' => 'Idempotency-Key']);

        $this->handler = $this->fakeRequests();
        $this->client = resolve(ClientFactory::class)->make();
        $this->client->getConfig('handler')->push(new Middleware());
    }
    /**
     * @test
     */
    public function it_applies_the_idempotency_header_for_post_requests()
    {
        $expectation = $this->handler->expects('post', 'https://test.test');

        $this->client->post('https://test.test');

        $this->assertNotEmpty($expectation->getRequest()->getHeaderLine('Idempotency-Key'));
    }

    /**
     * @test
     */
    public function it_applies_the_idempotency_header_for_put_requests()
    {
        $expectation = $this->handler->expects('put', 'https://test.test');

        $this->client->put('https://test.test');

        $this->assertNotEmpty($expectation->getRequest()->getHeaderLine('Idempotency-Key'));
    }

    /**
     * @test
     */
    public function it_applies_the_idempotency_header_for_patch_requests()
    {
        $expectation = $this->handler->expects('patch', 'https://test.test');

        $this->client->patch('https://test.test');

        $this->assertNotEmpty($expectation->getRequest()->getHeaderLine('Idempotency-Key'));
    }

    /**
     * @test
     */
    public function it_does_not_apply_the_idempotency_header_for_get_requests()
    {
        $expectation = $this->handler->expects('get', 'https://test.test');

        $this->client->get('https://test.test');

        $this->assertFalse($expectation->getRequest()->hasHeader('Idempotency-Key'));
    }

    /**
     * @test
     */
    public function it_does_not_apply_the_idempotency_header_for_delete_requests()
    {
        $expectation = $this->handler->expects('delete', 'https://test.test');

        $this->client->delete('https://test.test');

        $this->assertFalse($expectation->getRequest()->hasHeader('Idempotency-Key'));
    }

    /**
     * @test
     */
    public function it_applies_separate_idempotency_keys_to_each_request()
    {
        $expectation1 = $this->handler->expects('post', 'https://test.test');
        $expectation2 = $this->handler->expects('post', 'https://test.test');

        $this->client->post('https://test.test');
        $this->client->post('https://test.test');

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
        $middleware = function ($handler) {
            return function ($request, $options) use ($handler) {
                return $handler(with($request), $options);
            };
        };
        
        $this->client->getConfig('handler')->push($middleware);
        

        $expectation = $this->handler->expects('post', 'https://test.test');

        $this->client->post('https://test.test');

        $this->assertNotEmpty($expectation->getRequest()->getHeaderLine('Idempotency-Key'));
    }
}
