<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Stripe\Objects\Product;

use EncoreDigitalGroup\Stripe\Support\HasMake;
use EncoreDigitalGroup\StdLib\Objects\Support\Types\Arr;
use Stripe\Product;

class StripeProduct
{
    use HasMake;

    public function __construct(
        public ?string $id = null,
        public ?string $name = null,
        public ?string $description = null,
        public ?bool   $active = null,
        public ?array  $images = null,
        public ?array  $metadata = null,
        public ?string $defaultPrice = null,
        public ?string $taxCode = null,
        public ?string $unitLabel = null,
        public ?string $url = null,
        public ?bool   $shippable = null,
        public ?array  $packageDimensions = null,
        public ?int    $created = null,
        public ?int    $updated = null
    ) {}

    /**
     * Create a StripeProduct instance from a Stripe API Product object
     */
    public static function fromStripeObject(Product $stripeProduct): self
    {
        $packageDimensions = null;
        if (isset($stripeProduct->package_dimensions)) {
            /** @var \Stripe\StripeObject $pkgDim */
            $pkgDim = $stripeProduct->package_dimensions;
            $packageDimensions = [
                "height" => $pkgDim->height ?? null,
                "length" => $pkgDim->length ?? null,
                "weight" => $pkgDim->weight ?? null,
                "width" => $pkgDim->width ?? null,
            ];
        }

        $defaultPrice = null;
        if (isset($stripeProduct->default_price)) {
            if (is_string($stripeProduct->default_price)) {
                $defaultPrice = $stripeProduct->default_price;
            } else {
                $defaultPrice = $stripeProduct->default_price->id;
            }
        }

        $taxCode = null;
        if (isset($stripeProduct->tax_code)) {
            $taxCode = is_string($stripeProduct->tax_code) ? $stripeProduct->tax_code : $stripeProduct->tax_code->id;
        }

        return self::make(
            id: $stripeProduct->id,
            name: $stripeProduct->name,
            description: $stripeProduct->description ?? null,
            active: $stripeProduct->active ?? null,
            images: $stripeProduct->images ?? null,
            metadata: $stripeProduct->metadata->toArray(),
            defaultPrice: $defaultPrice,
            taxCode: $taxCode,
            unitLabel: $stripeProduct->unit_label ?? null,
            url: $stripeProduct->url ?? null,
            shippable: $stripeProduct->shippable ?? null,
            packageDimensions: $packageDimensions,
            created: $stripeProduct->created ?? null,
            updated: $stripeProduct->updated ?? null
        );
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
        ];

        return Arr::whereNotNull($array);
    }
}