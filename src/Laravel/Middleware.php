<?php

namespace SoapBox\Idempotency\Laravel;

use Closure;
use Illuminate\Http\Request;
use SoapBox\Idempotency\Idempotency;

class Middleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (!($request->supportsIdempotency() && $key = $request->getIdempotencyKey())) {
            return $next($request);
        }

        if ($response = Idempotency::get($key)) {
            return $response;
        }

        $response = $next($request);

        Idempotency::add($key, $response);

        return $response;
    }
}
