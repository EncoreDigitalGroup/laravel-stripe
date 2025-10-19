<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Stripe\Objects\Product;

use Carbon\CarbonImmutable;
use EncoreDigitalGroup\StdLib\Objects\Support\Types\Arr;
use EncoreDigitalGroup\Stripe\Services\StripeProductService;
use EncoreDigitalGroup\Stripe\Support\Traits\HasGet;
use EncoreDigitalGroup\Stripe\Support\Traits\HasSave;
use EncoreDigitalGroup\Stripe\Support\Traits\HasTimestamps;
use PHPGenesis\Common\Traits\HasMake;
use Stripe\Product;
use Stripe\StripeObject;

class StripeProduct
{
    use HasGet;
    use HasMake;
    use HasSave;
    use HasTimestamps;

    private ?string $id = null;
    private ?string $name = null;
    private ?string $description = null;
    private ?bool $active = null;
    private ?array $images = null;
    private ?array $metadata = null;
    private ?string $defaultPrice = null;
    private ?string $taxCode = null;
    private ?string $unitLabel = null;
    private ?string $url = null;
    private ?bool $shippable = null;
    private ?array $packageDimensions = null;
    private ?CarbonImmutable $created = null;
    private ?CarbonImmutable $updated = null;

    /**
     * Create a StripeProduct instance from a Stripe API Product object
     */
    public static function fromStripeObject(Product $stripeProduct): self
    {
        $instance = self::make();
        $instance = self::setBasicProperties($instance, $stripeProduct);

        return self::setExtendedProperties($instance, $stripeProduct);
    }

    private static function setBasicProperties(self $instance, Product $stripeProduct): self
    {
        if ($stripeProduct->id) {
            $instance = $instance->withId($stripeProduct->id);
        }

        if ($stripeProduct->name) {
            $instance = $instance->withName($stripeProduct->name);
        }

        if ($stripeProduct->description ?? null) {
            $instance = $instance->withDescription($stripeProduct->description);
        }

        if (isset($stripeProduct->active)) {
            $instance = $instance->withActive($stripeProduct->active);
        }

        if ($stripeProduct->images ?? null) {
            $instance = $instance->withImages($stripeProduct->images);
        }

        if (isset($stripeProduct->metadata)) {
            $instance = $instance->withMetadata($stripeProduct->metadata->toArray());
        }

        $instance = self::setRelatedIds($instance, $stripeProduct);

        return $instance;
    }

    private static function setRelatedIds(self $instance, Product $stripeProduct): self
    {
        if (isset($stripeProduct->default_price)) {
            $defaultPrice = is_string($stripeProduct->default_price)
                ? $stripeProduct->default_price
                : $stripeProduct->default_price->id;
            $instance = $instance->withDefaultPrice($defaultPrice);
        }

        if (isset($stripeProduct->tax_code)) {
            $taxCode = is_string($stripeProduct->tax_code) ? $stripeProduct->tax_code : $stripeProduct->tax_code->id;
            $instance = $instance->withTaxCode($taxCode);
        }

        return $instance;
    }

    private static function setExtendedProperties(self $instance, Product $stripeProduct): self
    {
        if ($stripeProduct->unit_label ?? null) {
            $instance = $instance->withUnitLabel($stripeProduct->unit_label);
        }

        if ($stripeProduct->url ?? null) {
            $instance = $instance->withUrl($stripeProduct->url);
        }

        if (isset($stripeProduct->shippable)) {
            $instance = $instance->withShippable($stripeProduct->shippable);
        }

        if (isset($stripeProduct->package_dimensions)) {
            /** @var StripeObject $pkgDim */
            $pkgDim = $stripeProduct->package_dimensions;
            $packageDimensions = [
                "height" => $pkgDim->height ?? null,
                "length" => $pkgDim->length ?? null,
                "weight" => $pkgDim->weight ?? null,
                "width" => $pkgDim->width ?? null,
            ];
            $instance = $instance->withPackageDimensions($packageDimensions);
        }

        if ($stripeProduct->created ?? null) {
            $created = self::timestampToCarbon($stripeProduct->created);
            if ($created instanceof CarbonImmutable) {
                $instance = $instance->withCreated($created);
            }
        }

        if ($stripeProduct->updated ?? null) {
            $updated = self::timestampToCarbon($stripeProduct->updated);
            if ($updated instanceof CarbonImmutable) {
                $instance = $instance->withUpdated($updated);
            }
        }

        return $instance;
    }

    public function service(): StripeProductService
    {
        return app(StripeProductService::class);
    }

    public function toArray(): array
    {
        $array = [
            "id" => $this->id,
            "name" => $this->name,
            "description" => $this->description,
            "active" => $this->active,
            "images" => $this->images,
            "metadata" => $this->metadata,
            "default_price" => $this->defaultPrice,
            "tax_code" => $this->taxCode,
            "unit_label" => $this->unitLabel,
            "url" => $this->url,
            "shippable" => $this->shippable,
            "package_dimensions" => $this->packageDimensions,
            "created" => self::carbonToTimestamp($this->created),
            "updated" => self::carbonToTimestamp($this->updated),
        ];

        return Arr::whereNotNull($array);
    }

    // Fluent setters
    public function withId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function withName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function withDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function withActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    public function withImages(array $images): self
    {
        $this->images = $images;

        return $this;
    }

    public function withMetadata(array $metadata): self
    {
        $this->metadata = $metadata;

        return $this;
    }

    public function withDefaultPrice(string $defaultPrice): self
    {
        $this->defaultPrice = $defaultPrice;

        return $this;
    }

    public function withTaxCode(string $taxCode): self
    {
        $this->taxCode = $taxCode;

        return $this;
    }

    public function withUnitLabel(string $unitLabel): self
    {
        $this->unitLabel = $unitLabel;

        return $this;
    }

    public function withUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function withShippable(bool $shippable): self
    {
        $this->shippable = $shippable;

        return $this;
    }

    public function withPackageDimensions(array $packageDimensions): self
    {
        $this->packageDimensions = $packageDimensions;

        return $this;
    }

    public function withCreated(CarbonImmutable $created): self
    {
        $this->created = $created;

        return $this;
    }

    public function withUpdated(CarbonImmutable $updated): self
    {
        $this->updated = $updated;

        return $this;
    }

    // Getters
    public function id(): ?string
    {
        return $this->id;
    }

    public function name(): ?string
    {
        return $this->name;
    }

    public function description(): ?string
    {
        return $this->description;
    }

    public function active(): ?bool
    {
        return $this->active;
    }

    public function images(): ?array
    {
        return $this->images;
    }

    public function metadata(): ?array
    {
        return $this->metadata;
    }

    public function defaultPrice(): ?string
    {
        return $this->defaultPrice;
    }

    public function taxCode(): ?string
    {
        return $this->taxCode;
    }

    public function unitLabel(): ?string
    {
        return $this->unitLabel;
    }

    public function url(): ?string
    {
        return $this->url;
    }

    public function shippable(): ?bool
    {
        return $this->shippable;
    }

    public function packageDimensions(): ?array
    {
        return $this->packageDimensions;
    }

    public function created(): ?CarbonImmutable
    {
        return $this->created;
    }

    public function updated(): ?CarbonImmutable
    {
        return $this->updated;
    }
}
