<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\PackageTemplate\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . "/../../resources/views/components", "stripe");
        Blade::componentNamespace('EncoreDigitalGroup\\Common\\Stripe\\Views\\Components', "stripe");
    }
}
