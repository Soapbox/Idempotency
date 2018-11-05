<?php

namespace SoapBox\Idempotency;

use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    public function boot()
    {
        $this->publishes([__DIR__ . '/../config/idempotency.php' => config_path('idempotency.php')]);

        Request::macro('getIdempotencyKey', function () {
            return $this->header(config('idempotency.header'));
        });

        Request::macro('supportsIdempotency', function () {
            return Idempotency::supportedRequestMethod($this->method());
        });
    }
}
