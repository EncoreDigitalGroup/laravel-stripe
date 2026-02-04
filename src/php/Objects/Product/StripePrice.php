<?php

namespace EncoreDigitalGroup\Stripe\Objects\Product;

use Carbon\CarbonImmutable;
use EncoreDigitalGroup\StdLib\Objects\Support\Types\Arr;
use EncoreDigitalGroup\Stripe\Enums\BillingScheme;
use EncoreDigitalGroup\Stripe\Enums\PriceType;
use EncoreDigitalGroup\Stripe\Enums\RecurringAggregateUsage;
use EncoreDigitalGroup\Stripe\Enums\RecurringInterval;
use EncoreDigitalGroup\Stripe\Enums\RecurringUsageType;
use EncoreDigitalGroup\Stripe\Enums\TaxBehavior;
use EncoreDigitalGroup\Stripe\Enums\TiersMode;
use EncoreDigitalGroup\Stripe\Services\StripePriceService;
use EncoreDigitalGroup\Stripe\Support\Traits\HasGet;
use EncoreDigitalGroup\Stripe\Support\Traits\HasReadOnlyFields;
use EncoreDigitalGroup\Stripe\Support\Traits\HasSave;
use EncoreDigitalGroup\Stripe\Support\Traits\HasTimestamps;
use Illuminate\Support\Collection;
use PHPGenesis\Common\Traits\HasMake;
use Stripe\Price;
use Stripe\StripeObject;

class StripePrice
{
    use HasGet;
    use HasMake;
    use HasReadOnlyFields;
    use HasSave;
    use HasTimestamps;

    private ?string $id = null;
    private ?string $product = null;
    private ?bool $active = null;
    private ?string $currency = null;
    private ?int $unitAmount = null;
    private ?string $unitAmountDecimal = null;
    private ?PriceType $type = null;
    private ?BillingScheme $billingScheme = null;
    private ?StripeRecurring $recurring = null;
    private ?string $nickname = null;
    private ?array $metadata = null;
    private ?string $lookupKey = null;

    /** @var Collection<int, StripeProductTier>|null */
    private ?Collection $tiers = null;

    private ?TiersMode $tiersMode = null;
    private ?int $transformQuantity = null;
    private ?StripeCustomUnitAmount $customUnitAmount = null;
    private ?TaxBehavior $taxBehavior = null;
    private ?CarbonImmutable $created = null;

    public static function fromStripeObject(Price $stripePrice): self
    {
        $instance = self::make();
        $instance = self::setBasicProperties($instance, $stripePrice);

        return self::setAdvancedProperties($instance, $stripePrice);
    }

    private static function setBasicProperties(self $instance, Price $stripePrice): self
    {
        if ($stripePrice->id) {
            $instance = $instance->withId($stripePrice->id);
        }

        $product = is_string($stripePrice->product) ? $stripePrice->product : $stripePrice->product->id;
        $instance = $instance->withProduct($product);

        if (isset($stripePrice->active)) {
            $instance = $instance->withActive($stripePrice->active);
        }

        if ($stripePrice->currency) {
            $instance = $instance->withCurrency($stripePrice->currency);
        }

        if (isset($stripePrice->unit_amount)) {
            $instance = $instance->withUnitAmount($stripePrice->unit_amount);
        }

        if (isset($stripePrice->unit_amount_decimal)) {
            $instance = $instance->withUnitAmountDecimal($stripePrice->unit_amount_decimal);
        }

        return self::setEnumProperties($instance, $stripePrice);
    }

    public function withId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function withProduct(string $product): self
    {
        $this->product = $product;

        return $this;
    }

    public function withActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    public function withCurrency(string $currency): self
    {
        $this->currency = $currency;

        return $this;
    }

    public function withUnitAmount(int $unitAmount): self
    {
        $this->unitAmount = $unitAmount;

        return $this;
    }

    public function withUnitAmountDecimal(string $unitAmountDecimal): self
    {
        $this->unitAmountDecimal = $unitAmountDecimal;

        return $this;
    }

    private static function setEnumProperties(self $instance, Price $stripePrice): self
    {
        if (isset($stripePrice->type)) {
            $instance = $instance->withType(PriceType::from($stripePrice->type));
        }

        if (isset($stripePrice->billing_scheme)) {
            $instance = $instance->withBillingScheme(BillingScheme::from($stripePrice->billing_scheme));
        }

        if ($stripePrice->nickname ?? null) {
            $instance = $instance->withNickname($stripePrice->nickname);
        }

        if (isset($stripePrice->metadata)) {
            $instance = $instance->withMetadata($stripePrice->metadata->toArray());
        }

        if ($stripePrice->lookup_key ?? null) {
            return $instance->withLookupKey($stripePrice->lookup_key);
        }

        return $instance;
    }

    public function withType(PriceType $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function withBillingScheme(BillingScheme $billingScheme): self
    {
        $this->billingScheme = $billingScheme;

        return $this;
    }

    // Fluent setters

    public function withNickname(string $nickname): self
    {
        $this->nickname = $nickname;

        return $this;
    }

    public function withMetadata(array $metadata): self
    {
        $this->metadata = $metadata;

        return $this;
    }

    public function toArray(): array
    {
        $array = [
            "id" => $this->id,
            "product" => $this->product,
            "active" => $this->active,
            "currency" => $this->currency,
            "unit_amount" => $this->unitAmount,
            "unit_amount_decimal" => $this->unitAmountDecimal,
            "type" => $this->type?->value,
            "billing_scheme" => $this->billingScheme?->value,
            "recurring" => $this->recurring?->toArray(),
            "nickname" => $this->nickname,
            "metadata" => $this->metadata,
            "lookup_key" => $this->lookupKey,
            "tiers" => $this->tiers?->map(fn(StripeProductTier $tier): array => $tier->toArray())->values()->all(),
            "tiers_mode" => $this->tiersMode?->value,
            "transform_quantity" => $this->transformQuantity,
            "custom_unit_amount" => $this->customUnitAmount?->toArray(),
            "tax_behavior" => $this->taxBehavior?->value,
            "created" => self::carbonToTimestamp($this->created),
        ];

        return Arr::whereNotNull($array);
    }

    public function withLookupKey(string $lookupKey): self
    {
        $this->lookupKey = $lookupKey;

        return $this;
    }

    private static function setAdvancedProperties(self $instance, Price $stripePrice): self
    {
        $instance = self::setTierProperties($instance, $stripePrice);

        return self::setMiscProperties($instance, $stripePrice);
    }

    private static function setTierProperties(self $instance, Price $stripePrice): self
    {
        if (isset($stripePrice->tiers)) {
            $tiers = self::extractTiers($stripePrice);
            if ($tiers instanceof Collection) {
                $instance = $instance->withTiers($tiers);
            }
        }

        if (isset($stripePrice->tiers_mode)) {
            return $instance->withTiersMode(TiersMode::from($stripePrice->tiers_mode));
        }

        return $instance;
    }

    /** @return ?Collection<StripeProductTier> */
    private static function extractTiers(Price $stripePrice): ?Collection
    {
        if (!isset($stripePrice->tiers)) {
            return null;
        }

        $tiers = [];
        /** @var StripeObject $tier */
        foreach ($stripePrice->tiers as $tier) {
            $tierInstance = StripeProductTier::make();

            if (isset($tier->up_to)) {
                $tierInstance = $tierInstance->withUpTo($tier->up_to);
            }

            if (isset($tier->unit_amount)) {
                $tierInstance = $tierInstance->withUnitAmount($tier->unit_amount);
            }

            if (isset($tier->unit_amount_decimal)) {
                $tierInstance = $tierInstance->withUnitAmountDecimal($tier->unit_amount_decimal);
            }

            if (isset($tier->flat_amount)) {
                $tierInstance = $tierInstance->withFlatAmount($tier->flat_amount);
            }

            if (isset($tier->flat_amount_decimal)) {
                $tierInstance = $tierInstance->withFlatAmountDecimal($tier->flat_amount_decimal);
            }

            $tiers[] = $tierInstance;
        }

        return new Collection($tiers);
    }

    /** @param Collection<StripeProductTier> $tiers */
    public function withTiers(Collection $tiers): self
    {
        $this->tiers = $tiers;

        return $this;
    }

    public function withTiersMode(TiersMode $tiersMode): self
    {
        $this->tiersMode = $tiersMode;

        return $this;
    }

    private static function setMiscProperties(self $instance, Price $stripePrice): self
    {
        if (isset($stripePrice->recurring)) {
            $recurring = self::extractRecurring($stripePrice);
            if ($recurring instanceof StripeRecurring) {
                $instance = $instance->withRecurring($recurring);
            }
        }

        if (isset($stripePrice->custom_unit_amount)) {
            $customUnitAmount = self::extractCustomUnitAmount($stripePrice);
            if ($customUnitAmount instanceof StripeCustomUnitAmount) {
                $instance = $instance->withCustomUnitAmount($customUnitAmount);
            }
        }

        if (isset($stripePrice->tax_behavior)) {
            $instance = $instance->withTaxBehavior(TaxBehavior::from($stripePrice->tax_behavior));
        }

        if ($stripePrice->created ?? null) {
            $created = self::timestampToCarbon($stripePrice->created);
            if ($created instanceof CarbonImmutable) {
                $instance = $instance->withCreated($created);
            }
        }

        return $instance;
    }

    private static function extractRecurring(Price $stripePrice): ?StripeRecurring
    {
        if (!isset($stripePrice->recurring)) {
            return null;
        }

        /** @var StripeObject $recurringObj */
        $recurringObj = $stripePrice->recurring;

        $recurring = StripeRecurring::make();

        if (isset($recurringObj->interval)) {
            $recurring = $recurring->withInterval(RecurringInterval::from($recurringObj->interval));
        }

        if (isset($recurringObj->interval_count)) {
            $recurring = $recurring->withIntervalCount($recurringObj->interval_count);
        }

        if (isset($recurringObj->trial_period_days)) {
            $recurring = $recurring->withTrialPeriodDays($recurringObj->trial_period_days);
        }

        if (isset($recurringObj->usage_type)) {
            $recurring = $recurring->withUsageType(RecurringUsageType::from($recurringObj->usage_type));
        }

        if (isset($recurringObj->aggregate_usage)) {
            return $recurring->withAggregateUsage(RecurringAggregateUsage::from($recurringObj->aggregate_usage));
        }

        return $recurring;
    }

    public function withRecurring(StripeRecurring $recurring): self
    {
        $this->recurring = $recurring;

        return $this;
    }

    private static function extractCustomUnitAmount(Price $stripePrice): ?StripeCustomUnitAmount
    {
        if (!isset($stripePrice->custom_unit_amount)) {
            return null;
        }

        /** @var StripeObject $customUnitAmountObj */
        $customUnitAmountObj = $stripePrice->custom_unit_amount;

        $customUnitAmount = StripeCustomUnitAmount::make();

        if (isset($customUnitAmountObj->minimum)) {
            $customUnitAmount = $customUnitAmount->withMinimum($customUnitAmountObj->minimum);
        }

        if (isset($customUnitAmountObj->maximum)) {
            $customUnitAmount = $customUnitAmount->withMaximum($customUnitAmountObj->maximum);
        }

        if (isset($customUnitAmountObj->preset)) {
            return $customUnitAmount->withPreset($customUnitAmountObj->preset);
        }

        return $customUnitAmount;
    }

    public function withCustomUnitAmount(StripeCustomUnitAmount $customUnitAmount): self
    {
        $this->customUnitAmount = $customUnitAmount;

        return $this;
    }

    public function withTaxBehavior(TaxBehavior $taxBehavior): self
    {
        $this->taxBehavior = $taxBehavior;

        return $this;
    }

    public function withCreated(CarbonImmutable $created): self
    {
        $this->created = $created;

        return $this;
    }

    public function service(): StripePriceService
    {
        return app(StripePriceService::class);
    }

    public function withTransformQuantity(int $transformQuantity): self
    {
        $this->transformQuantity = $transformQuantity;

        return $this;
    }

    // Getters

    public function id(): ?string
    {
        return $this->id;
    }

    public function product(): ?string
    {
        return $this->product;
    }

    public function active(): ?bool
    {
        return $this->active;
    }

    public function currency(): ?string
    {
        return $this->currency;
    }

    public function unitAmount(): ?int
    {
        return $this->unitAmount;
    }

    public function unitAmountDecimal(): ?string
    {
        return $this->unitAmountDecimal;
    }

    public function type(): ?PriceType
    {
        return $this->type;
    }

    public function billingScheme(): ?BillingScheme
    {
        return $this->billingScheme;
    }

    public function recurring(): ?StripeRecurring
    {
        return $this->recurring;
    }

    public function nickname(): ?string
    {
        return $this->nickname;
    }

    public function metadata(): ?array
    {
        return $this->metadata;
    }

    public function lookupKey(): ?string
    {
        return $this->lookupKey;
    }

    /** @return ?Collection<StripeProductTier> */
    public function tiers(): ?Collection
    {
        return $this->tiers;
    }

    public function tiersMode(): ?TiersMode
    {
        return $this->tiersMode;
    }

    public function transformQuantity(): ?int
    {
        return $this->transformQuantity;
    }

    public function customUnitAmount(): ?StripeCustomUnitAmount
    {
        return $this->customUnitAmount;
    }

    public function taxBehavior(): ?TaxBehavior
    {
        return $this->taxBehavior;
    }

    public function created(): ?CarbonImmutable
    {
        return $this->created;
    }

    protected function getReadOnlyFields(): array
    {
        return [
            "id",
            "created",
        ];
    }

    protected function getUpdateOnlyReadOnlyFields(): array
    {
        return [
            "product",
            "currency",
            "unit_amount",
            "unit_amount_decimal",
            "type",
            "billing_scheme",
            "recurring",
            "tiers",
            "tiers_mode",
            "transform_quantity",
            "custom_unit_amount",
        ];
    }
}
