<?php

namespace EncoreDigitalGroup\Stripe\Views;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Config;
use Illuminate\View\Component;

class FinancialConnections extends Component
{
    public function __construct(
        public ?string $stripePublicKey = null,
        public ?string $stripeSessionSecret = null,
        public ?string $stripeCustomerId = null,
        public ?string $redirectSuccessUrl = null,
        public ?string $redirectErrorUrl = null,
        public ?string $postSuccessUrl = null,
        public ?string $publicSecurityKey = null,
        public ?string $privateSecurityKey = null
    )
    {
        $this->redirectUrlIsNull("redirectSuccessUrl");
        $this->redirectUrlIsNull("redirectErrorUrl");
        $this->redirectUrlIsNull("postSuccessUrl");
    }

    protected function redirectUrlIsNull(string $property): void
    {
        if (is_null($this->{$property})) {
            Config::get("app.url");
        }
    }

    public function render(): View|Closure|string
    {
        return $this->view("stripe::financialConnections");
    }
}
