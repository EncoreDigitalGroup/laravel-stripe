<?php

/*
 * Copyright (c) 2024-2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Common\Stripe\Views;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Config;
use Illuminate\View\Component;

class FinancialConnections extends Component
{
    public function __construct(
        public ?string $stripePublicKey = null,
        public ?string $stripeSessionSecret = null,
        public ?string $redirectSuccessUrl = null,
        public ?string $redirectErrorUrl = null
    ) {
        $this->redirectUrlIsNull("redirectSuccessUrl");
        $this->redirectUrlIsNull("redirectErrorUrl");
    }

    public function render(): View|Closure|string
    {
        return $this->view("stripe::financialConnections");
    }

    private function redirectUrlIsNull(string $property): void
    {
        if (is_null($this->{$property})) {
            Config::get("app.url");
        }
    }
}
