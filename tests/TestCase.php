<?php

declare(strict_types=1);

namespace Givanov95\Shipping\Speedy\Tests;

use Givanov95\Shipping\ShippingServiceProvider;
use Givanov95\Shipping\Speedy\SpeedyServiceProvider;
use Givanov95\TypedHttp\Laravel\TypedHttpServiceProvider;
use Illuminate\Support\Facades\Http;
use Orchestra\Testbench\TestCase as Testbench;

/**
 * An application with nothing of a shop in it: the delivery contract, the HTTP bridge and
 * this package. Every request to Speedy is faked.
 */
abstract class TestCase extends Testbench
{
    protected function setUp(): void
    {
        parent::setUp();

        Http::preventStrayRequests();

        config([
            'speedy.base_url' => 'https://speedy.test/v1',
            'speedy.username' => 'user',
            'speedy.password' => 'pass',
            'speedy.sender'   => ['client_id' => '555', 'office_id' => '12', 'name' => 'Shop Ltd', 'phone' => '+359111'],
        ]);
    }

    protected function getPackageProviders($app): array
    {
        return [TypedHttpServiceProvider::class, ShippingServiceProvider::class, SpeedyServiceProvider::class];
    }
}
