<?php

namespace SoapBox\Idempotency\Guzzle;

use Ramsey\Uuid\Uuid;
use SoapBox\Idempotency\Idempotency;
use Psr\Http\Message\RequestInterface;

class Middleware
{
    /**
     * Invokes the logger middleware.
     *
     * @param callable $handler
     *
     * @return callable
     */
    public function __invoke(callable $handler): callable
    {
        return function (RequestInterface &$request, array $options) use ($handler) {
            if (Idempotency::supportedRequestMethod($request->getMethod())) {
                $request = $request->withHeader(
                    config('idempotency.header'),
                    $request->getHeaderLine(config('idempotency.header')) ?: Uuid::uuid4()
                );
            }
            return $handler($request, $options);
        };
    }
}
