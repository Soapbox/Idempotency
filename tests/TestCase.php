<?php

namespace SoapBox\Idempotency\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app)
    {
        return ['SoapBox\Idempotency\ServiceProvider'];
    }
}
