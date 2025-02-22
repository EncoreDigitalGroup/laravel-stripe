<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Common\Stripe\Providers;

use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . "/../../../resources/views", "stripe");
        Blade::componentNamespace('EncoreDigitalGroup\\Common\\Stripe\\Views', "stripe");

        FilamentAsset::register([
            Js::make("financialConnections", __DIR__ . "/../../../dist/bundle.js"),
        ], "encoredigitalgroup/common-stripe");
    }
}
