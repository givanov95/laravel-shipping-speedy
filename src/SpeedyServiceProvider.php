<?php

declare(strict_types=1);

namespace Givanov95\Shipping\Speedy;

use Givanov95\Shipping\DeliveryMethods;
use Illuminate\Support\ServiceProvider;

final class SpeedyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/speedy.php', 'speedy');

        // Registered is not yet offered: the shop names the methods it offers.
        $this->callAfterResolving(DeliveryMethods::class, function (DeliveryMethods $methods): void {
            $methods->register('speedy', SpeedyMethod::class, 'Speedy');
        });
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([__DIR__.'/../config/speedy.php' => config_path('speedy.php')], 'speedy-config');
        }
    }
}
