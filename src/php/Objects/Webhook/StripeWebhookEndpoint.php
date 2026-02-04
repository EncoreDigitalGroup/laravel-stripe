<?php

namespace EncoreDigitalGroup\Stripe\Objects\Webhook;

use Carbon\CarbonImmutable;
use EncoreDigitalGroup\StdLib\Objects\Support\Types\Arr;
use EncoreDigitalGroup\Stripe\Services\StripeWebhookEndpointService;
use EncoreDigitalGroup\Stripe\Support\Traits\HasGet;
use EncoreDigitalGroup\Stripe\Support\Traits\HasIdentifier;
use EncoreDigitalGroup\Stripe\Support\Traits\HasLivemode;
use EncoreDigitalGroup\Stripe\Support\Traits\HasMetadata;
use EncoreDigitalGroup\Stripe\Support\Traits\HasSave;
use EncoreDigitalGroup\Stripe\Support\Traits\HasTimestamps;
use PHPGenesis\Common\Traits\HasMake;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Stripe\Exception\ApiErrorException;
use Stripe\WebhookEndpoint;

class StripeWebhookEndpoint
{
    use HasGet;
    use HasIdentifier;
    use HasLivemode;
    use HasMake;
    use HasMetadata;
    use HasSave;
    use HasTimestamps;

    private ?string $url = null;
    private ?array $enabledEvents = null;
    private ?string $description = null;
    private ?bool $disabled = null;
    private ?string $secret = null;
    private ?string $status = null;
    private ?CarbonImmutable $created = null;

    /** Create a StripeWebhookEndpoint instance from a Stripe API WebhookEndpoint object */
    public static function fromStripeObject(WebhookEndpoint $stripeEndpoint): self
    {
        $instance = self::make();
        $instance = self::setBasicProperties($instance, $stripeEndpoint);

        return self::setOptionalProperties($instance, $stripeEndpoint);
    }

    private static function setBasicProperties(self $instance, WebhookEndpoint $stripeEndpoint): self
    {
        if ($stripeEndpoint->id) {
            $instance = $instance->withId($stripeEndpoint->id);
        }
        if ($stripeEndpoint->url ?? null) {
            $instance = $instance->withUrl($stripeEndpoint->url);
        }
        if (isset($stripeEndpoint->enabled_events)) {
            $instance = $instance->withEnabledEvents($stripeEndpoint->enabled_events);
        }
        if ($stripeEndpoint->description ?? null) {
            $instance = $instance->withDescription($stripeEndpoint->description);
        }

        $created = self::timestampToCarbon($stripeEndpoint->created ?? null);
        if ($created instanceof CarbonImmutable) {
            return $instance->withCreated($created);
        }

        return $instance;
    }

    private static function setOptionalProperties(self $instance, WebhookEndpoint $stripeEndpoint): self
    {
        if (isset($stripeEndpoint->disabled)) {
            $instance = $instance->withDisabled($stripeEndpoint->disabled);
        }
        if (isset($stripeEndpoint->livemode)) {
            $instance = $instance->withLivemode($stripeEndpoint->livemode);
        }
        if (isset($stripeEndpoint->metadata)) {
            $metadataArray = $stripeEndpoint->metadata->toArray();
            $instance = $instance->withMetadata($metadataArray);
        }
        if ($stripeEndpoint->secret ?? null) {
            $instance = $instance->withSecret($stripeEndpoint->secret);
        }
        if ($stripeEndpoint->status ?? null) {
            return $instance->withStatus($stripeEndpoint->status);
        }

        return $instance;
    }

    public function withUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function withEnabledEvents(array $enabledEvents): self
    {
        $this->enabledEvents = $enabledEvents;

        return $this;
    }

    public function withDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function withCreated(CarbonImmutable $created): self
    {
        $this->created = $created;

        return $this;
    }

    public function withDisabled(bool $disabled): self
    {
        $this->disabled = $disabled;

        return $this;
    }

    // Fluent setters

    public function toArray(): array
    {
        $array = [
            "id" => $this->id,
            "url" => $this->url,
            "enabled_events" => $this->enabledEvents,
            "description" => $this->description,
            "disabled" => $this->disabled,
            "livemode" => $this->livemode,
            "metadata" => $this->metadata,
            "secret" => $this->secret,
            "status" => $this->status,
            "created" => self::carbonToTimestamp($this->created),
        ];

        return Arr::whereNotNull($array);
    }

    public function withSecret(string $secret): self
    {
        $this->secret = $secret;

        return $this;
    }

    public function withStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function service(): StripeWebhookEndpointService
    {
        return app(StripeWebhookEndpointService::class);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws ApiErrorException
     * @throws NotFoundExceptionInterface
     */
    public function get(string $id): self
    {
        $service = app(StripeWebhookEndpointService::class);

        return $service->get($id);
    }

    /** @throws ApiErrorException */
    public function save(): self
    {
        $service = app(StripeWebhookEndpointService::class);

        return is_null($this->id) ? $service->create($this) : $service->update($this->id, $this);
    }

    /** @throws ApiErrorException */
    public function delete(): self
    {
        if (is_null($this->id)) {
            return $this;
        }

        $service = app(StripeWebhookEndpointService::class);

        return $service->delete($this->id);
    }

    // Getter methods

    public function url(): ?string
    {
        return $this->url;
    }

    public function enabledEvents(): ?array
    {
        return $this->enabledEvents;
    }

    public function description(): ?string
    {
        return $this->description;
    }

    public function disabled(): ?bool
    {
        return $this->disabled;
    }

    public function secret(): ?string
    {
        return $this->secret;
    }

    public function status(): ?string
    {
        return $this->status;
    }

    public function created(): ?CarbonImmutable
    {
        return $this->created;
    }
}
