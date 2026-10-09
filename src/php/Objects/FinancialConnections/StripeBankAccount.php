<?php

namespace EncoreDigitalGroup\Stripe\Objects\FinancialConnections;

use Carbon\CarbonImmutable;
use EncoreDigitalGroup\StdLib\Exceptions\NullExceptions\ClassPropertyNullException;
use EncoreDigitalGroup\StdLib\Objects\Support\Types\Arr;
use EncoreDigitalGroup\Stripe\Enums\FinancialConnectionsAccountStatus;
use EncoreDigitalGroup\Stripe\Services\StripeFinancialConnectionsAccountService;
use EncoreDigitalGroup\Stripe\Support\Traits\HasGet;
use EncoreDigitalGroup\Stripe\Support\Traits\HasTimestamps;
use PHPGenesis\Common\Traits\HasMake;
use Stripe\Exception\ApiErrorException;
use Stripe\FinancialConnections\Account;

class StripeBankAccount
{
    use HasGet;
    use HasMake;
    use HasTimestamps;

    protected ?FinancialConnectionsAccountStatus $status = null;
    private ?string $id = null;
    private ?string $category = null;
    private ?CarbonImmutable $created = null;
    private ?string $displayName = null;
    private ?string $institutionName = null;
    private ?string $last4 = null;
    private ?bool $liveMode = null;
    private array $permissions = [];
    private array $subscriptions = [];
    private array $supportedPaymentMethodTypes = [];
    private ?StripeTransactionRefresh $transactionRefresh = null;

    public static function fromStripeObject(Account $account): self
    {
        $instance = self::setBasicProperties(self::make(), $account);
        $instance = self::setArrayProperties($instance, $account);
        $instance = self::setTransactionRefresh($instance, $account);

        return self::setStatus($instance, $account);
    }

    protected static function setBasicProperties(self $instance, Account $account): self
    {
        if (isset($account->id)) {
            $instance = $instance->withId($account->id);
        }
        if (isset($account->category)) {
            $instance = $instance->withCategory($account->category);
        }
        if (isset($account->created)) {
            $created = self::timestampToCarbon($account->created);
            if ($created instanceof CarbonImmutable) {
                $instance = $instance->withCreated($created);
            }
        }
        if (isset($account->display_name)) {
            $instance = $instance->withDisplayName($account->display_name);
        }
        if (isset($account->institution_name)) {
            $instance = $instance->withInstitutionName($account->institution_name);
        }
        if (isset($account->last4)) {
            return $instance->withLast4($account->last4);
        }

        return $instance;
    }

    protected static function setArrayProperties(self $instance, Account $account): self
    {
        if (isset($account->livemode)) {
            $instance = $instance->withLiveMode($account->livemode);
        }
        if (isset($account->permissions)) {
            $instance = $instance->withPermissions($account->permissions);
        }
        if (isset($account->subscriptions)) {
            $instance = $instance->withSubscriptions($account->subscriptions);
        }
        if (isset($account->supported_payment_method_types)) {
            return $instance->withSupportedPaymentMethodTypes($account->supported_payment_method_types);
        }

        return $instance;
    }

    protected static function setTransactionRefresh(self $instance, Account $account): self
    {
        if (!isset($account->transaction_refresh)) {
            return $instance;
        }

        $refresh = $account->transaction_refresh;
        $transactionRefresh = StripeTransactionRefresh::make();

        if (isset($refresh->id)) {
            $transactionRefresh = $transactionRefresh->withId($refresh->id);
        }
        if (isset($refresh->last_attempted_at)) {
            $lastAttemptedAt = self::timestampToCarbon($refresh->last_attempted_at);
            if ($lastAttemptedAt instanceof CarbonImmutable) {
                $transactionRefresh = $transactionRefresh->withLastAttemptedAt($lastAttemptedAt);
            }
        }
        if (isset($refresh->next_refresh_available_at)) {
            $nextRefreshAvailableAt = self::timestampToCarbon($refresh->next_refresh_available_at);
            if ($nextRefreshAvailableAt instanceof CarbonImmutable) {
                $transactionRefresh = $transactionRefresh->withNextRefreshAvailableAt($nextRefreshAvailableAt);
            }
        }
        if (isset($refresh->status)) {
            $transactionRefresh = $transactionRefresh->withStatus($refresh->status);
        }

        return $instance->withTransactionRefresh($transactionRefresh);
    }

    protected static function setStatus(self $instance, Account $account): self
    {
        if (!isset($account->status)) {
            return $instance;
        }

        $status = FinancialConnectionsAccountStatus::tryFrom($account->status);

        return $status instanceof FinancialConnectionsAccountStatus
            ? $instance->withStatus($status)
            : $instance;
    }

    public function toArray(): array
    {
        $array = [
            "id" => $this->id,
            "category" => $this->category,
            "created" => self::carbonToTimestamp($this->created),
            "display_name" => $this->displayName,
            "institution_name" => $this->institutionName,
            "last4" => $this->last4,
            "live_mode" => $this->liveMode,
            "permissions" => $this->permissions,
            "subscriptions" => $this->subscriptions,
            "supported_payment_method_types" => $this->supportedPaymentMethodTypes,
            "transaction_refresh" => $this->transactionRefresh?->toArray(),
            "status" => $this->status?->value,
        ];

        return Arr::whereNotNull($array);
    }

    // Fluent setters
    public function withId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function withCategory(string $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function withCreated(CarbonImmutable $created): self
    {
        $this->created = $created;

        return $this;
    }

    public function withDisplayName(string $displayName): self
    {
        $this->displayName = $displayName;

        return $this;
    }

    public function withInstitutionName(string $institutionName): self
    {
        $this->institutionName = $institutionName;

        return $this;
    }

    public function withLast4(string $last4): self
    {
        $this->last4 = $last4;

        return $this;
    }

    public function withLiveMode(bool $liveMode): self
    {
        $this->liveMode = $liveMode;

        return $this;
    }

    public function withPermissions(array $permissions): self
    {
        $this->permissions = $permissions;

        return $this;
    }

    public function withSubscriptions(array $subscriptions): self
    {
        $this->subscriptions = $subscriptions;

        return $this;
    }

    public function withSupportedPaymentMethodTypes(array $supportedPaymentMethodTypes): self
    {
        $this->supportedPaymentMethodTypes = $supportedPaymentMethodTypes;

        return $this;
    }

    public function withTransactionRefresh(StripeTransactionRefresh $transactionRefresh): self
    {
        $this->transactionRefresh = $transactionRefresh;

        return $this;
    }

    public function withStatus(FinancialConnectionsAccountStatus $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function service(): StripeFinancialConnectionsAccountService
    {
        return app(StripeFinancialConnectionsAccountService::class);
    }

    /**
     * @throws ApiErrorException
     * @throws ClassPropertyNullException
     */
    public function subscribeToTransactions(): self
    {
        if (is_null($this->id)) {
            throw new ClassPropertyNullException("id");
        }

        return $this->service()->subscribeToTransactions($this->id);
    }

    /**
     * @throws ApiErrorException
     * @throws ClassPropertyNullException
     */
    public function refreshTransactions(): self
    {
        if (is_null($this->id)) {
            throw new ClassPropertyNullException("id");
        }

        return $this->service()->refreshTransactions($this->id);
    }

    // Getter methods
    public function id(): ?string
    {
        return $this->id;
    }

    public function category(): ?string
    {
        return $this->category;
    }

    public function created(): ?CarbonImmutable
    {
        return $this->created;
    }

    public function displayName(): ?string
    {
        return $this->displayName;
    }

    public function institutionName(): ?string
    {
        return $this->institutionName;
    }

    public function last4(): ?string
    {
        return $this->last4;
    }

    public function liveMode(): ?bool
    {
        return $this->liveMode;
    }

    public function permissions(): array
    {
        return $this->permissions;
    }

    public function subscriptions(): array
    {
        return $this->subscriptions;
    }

    public function supportedPaymentMethodTypes(): array
    {
        return $this->supportedPaymentMethodTypes;
    }

    public function transactionRefresh(): ?StripeTransactionRefresh
    {
        return $this->transactionRefresh;
    }

    public function status(): ?FinancialConnectionsAccountStatus
    {
        return $this->status;
    }
}
