<?php

namespace SoapBox\Idempotency;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    public function boot()
    {
        $this->publishes([__DIR__ . '../config/idempotency.php' => config_path('idempotency.php')]);
    }
}
