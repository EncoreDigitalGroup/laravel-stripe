<?php

namespace EncoreDigitalGroup\Stripe\Services;

use EncoreDigitalGroup\Stripe\Objects\FinancialConnections\StripeBankAccount;
use EncoreDigitalGroup\Stripe\Support\Traits\HasStripe;
use Stripe\Exception\ApiErrorException;

/** @internal */
class StripeFinancialConnectionsAccountService
{
    use HasStripe;

    /** @throws ApiErrorException */
    public function get(string $accountId): StripeBankAccount
    {
        return StripeBankAccount::fromStripeObject(
            $this->stripe->financialConnections->accounts->retrieve($accountId)
        );
    }

    /** @throws ApiErrorException */
    public function subscribeToTransactions(string $accountId): StripeBankAccount
    {
        return StripeBankAccount::fromStripeObject(
            $this->stripe->financialConnections->accounts->subscribe($accountId, ["features" => ["transactions"]])
        );
    }

    /** @throws ApiErrorException */
    public function refreshTransactions(string $accountId): StripeBankAccount
    {
        return StripeBankAccount::fromStripeObject(
            $this->stripe->financialConnections->accounts->refresh($accountId, ["features" => ["transactions"]])
        );
    }
}
