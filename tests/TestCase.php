<?php
/*
 * Copyright (c) 2024-2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace Tests;

use Illuminate\Encryption\Encrypter;
use Illuminate\Support\Facades\Config;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

class TestCase extends OrchestraTestCase
{
    protected function setUp(): void
    {
        $this->enablesPackageDiscoveries = true;
        parent::setUp();

        $this->setupAppKey();
    }

    public function ignorePackageDiscoveriesFrom(): array
    {
        return [];
    }

    private function setupAppKey(): void
    {
        Config::set("app.key", "base64:" . base64_encode(Encrypter::generateKey(Config::get("app.cipher"))));
    }
}
