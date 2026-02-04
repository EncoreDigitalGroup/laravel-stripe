<?php

namespace EncoreDigitalGroup\Stripe\Objects\Support;

use EncoreDigitalGroup\StdLib\Objects\Support\Types\Str;
use Illuminate\Support\Facades\Request;
use PHPGenesis\Common\Traits\HasMake;
use Stripe\Event as StripeEvent;
use Stripe\Webhook;

class StripeWebhook
{
    use HasMake;

    private ?string $url = null;
    private array $events = [];

    public static function getWebhookSignatureHeader(): string
    {
        return Request::header("stripe-signature", Str::empty());
    }

    public static function fromRequest(string $payload, string $signature, string $secret): StripeEvent
    {
        return Webhook::constructEvent($payload, $signature, $secret);
    }

    public function withUrl(?string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function url(): ?string
    {
        return $this->url;
    }

    public function withEvents(array $events): self
    {
        $this->events = $events;

        return $this;
    }

    public function events(): array
    {
        return $this->events;
    }

    public function toArray(): array
    {
        return [
            "enabled_events" => $this->events,
            "url" => $this->url,
        ];
    }
}