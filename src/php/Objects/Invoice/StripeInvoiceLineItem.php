<?php

namespace EncoreDigitalGroup\Stripe\Objects\Invoice;

use EncoreDigitalGroup\StdLib\Objects\Support\Types\Arr;
use EncoreDigitalGroup\Stripe\Support\Traits\HasIdentifier;
use EncoreDigitalGroup\Stripe\Support\Traits\HasMetadata;
use PHPGenesis\Common\Traits\HasMake;
use Stripe\InvoiceLineItem;

class StripeInvoiceLineItem
{
    use HasIdentifier;
    use HasMake;
    use HasMetadata;

    private ?string $description = null;
    private ?string $currency = null;
    private ?int $amount = null;
    private ?int $quantity = null;
    private ?int $unitAmount = null;
    private ?string $priceId = null;
    private ?string $productId = null;
    private ?string $subscriptionId = null;
    private ?bool $proration = null;

    public static function fromStripeObject(InvoiceLineItem $lineItem): self
    {
        $priceId = null;
        $productId = null;

        if (isset($lineItem->price)) {
            $priceId = is_string($lineItem->price->id ?? null) ? $lineItem->price->id : null;
            $productId = is_string($lineItem->price->product ?? null) ? $lineItem->price->product : null;
        }

        $subscriptionId = self::extractSubscriptionId($lineItem->subscription ?? null);

        return self::make()
            ->withId($lineItem->id ?? null)
            ->withDescription($lineItem->description ?? null)
            ->withCurrency($lineItem->currency ?? null)
            ->withAmount($lineItem->amount ?? null)
            ->withQuantity($lineItem->quantity ?? null)
            ->withUnitAmount($lineItem->unit_amount ?? null)
            ->withPriceId($priceId)
            ->withProductId($productId)
            ->withSubscriptionId($subscriptionId)
            ->withProration($lineItem->proration ?? null)
            ->withMetadata(self::extractMetadata($lineItem));
    }

    private static function extractSubscriptionId(mixed $subscription): ?string
    {
        if ($subscription === null) {
            return null;
        }

        if (is_string($subscription)) {
            return $subscription;
        }

        return $subscription->id ?? null;
    }

    public function toArray(): array
    {
        $array = [
            "id" => $this->id,
            "description" => $this->description,
            "currency" => $this->currency,
            "amount" => $this->amount,
            "quantity" => $this->quantity,
            "unit_amount" => $this->unitAmount,
            "price_id" => $this->priceId,
            "product_id" => $this->productId,
            "subscription" => $this->subscriptionId,
            "proration" => $this->proration,
            "metadata" => $this->metadata,
        ];

        return Arr::whereNotNull($array);
    }

    public function withDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function description(): ?string
    {
        return $this->description;
    }

    public function withCurrency(?string $currency): self
    {
        $this->currency = $currency;

        return $this;
    }

    public function currency(): ?string
    {
        return $this->currency;
    }

    public function withAmount(?int $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function amount(): ?int
    {
        return $this->amount;
    }

    public function withQuantity(?int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function quantity(): ?int
    {
        return $this->quantity;
    }

    public function withUnitAmount(?int $unitAmount): self
    {
        $this->unitAmount = $unitAmount;

        return $this;
    }

    public function unitAmount(): ?int
    {
        return $this->unitAmount;
    }

    public function withPriceId(?string $priceId): self
    {
        $this->priceId = $priceId;

        return $this;
    }

    public function priceId(): ?string
    {
        return $this->priceId;
    }

    public function withProductId(?string $productId): self
    {
        $this->productId = $productId;

        return $this;
    }

    public function productId(): ?string
    {
        return $this->productId;
    }

    public function withSubscriptionId(?string $subscriptionId): self
    {
        $this->subscriptionId = $subscriptionId;

        return $this;
    }

    public function subscriptionId(): ?string
    {
        return $this->subscriptionId;
    }

    public function withProration(?bool $proration): self
    {
        $this->proration = $proration;

        return $this;
    }

    public function proration(): ?bool
    {
        return $this->proration;
    }
}
