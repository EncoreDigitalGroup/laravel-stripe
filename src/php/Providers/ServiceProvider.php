<?php

namespace EncoreDigitalGroup\Stripe\Providers;

use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

/** @codeCoverageIgnore */
class ServiceProvider extends BaseServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . "/../../../resources/views", "stripe");
        Blade::componentNamespace('EncoreDigitalGroup\\Common\\Stripe\\Views', "stripe");

        if (class_exists(FilamentAsset::class)) {
            FilamentAsset::register([
                Js::make("financialConnections", __DIR__ . "/../../../dist/bundle.js"),
            ], "encoredigitalgroup/common-stripe");
        }
    }

    public function register(): void {}
}
