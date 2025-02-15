<?php

/*
 * Copyright (c) 2024-2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Common\Stripe\Views;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class FinancialConnections extends Component
{
    public function __construct(public ?string $stripePublicKey = null, public ?string $stripeSessionSecret = null) {}

    public function render(): View|Closure|string
    {
        return $this->view("stripe::financialConnections");
    }
}
