<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Common\Stripe\Objects\Support;

use EncoreDigitalGroup\Common\Stripe\Objects\FinancialConnections\StripeBankAccount;
use Symfony\Component\Serializer\Attribute\SerializedName;

class StripeBankAccountConnectedPayload
{
    #[SerializedName("securityKeys")]
    private ?SecurityKeyPair $securityKeys = null;

    #[SerializedName("stripeCustomerId")]
    private ?string $stripeCustomerId = null;

    /** @var StripeBankAccount[] $accounts */
    public array $accounts;

    public function setSecurityKeys(array $securityKeys): static
    {
        $this->securityKeys = new SecurityKeyPair;
        $this->securityKeys->publicKey = $securityKeys["publicKey"];
        $this->securityKeys->privateKey = $securityKeys["privateKey"];

        return $this;
    }

    public function getSecurityKeys(): ?SecurityKeyPair
    {
        return $this->securityKeys;
    }

    public function setStripeCustomerId(string $stripeCustomerId): static
    {
        $this->stripeCustomerId = $stripeCustomerId;

        return $this;
    }

    public function getStripeCustomerId(): ?string
    {
        return $this->stripeCustomerId;
    }
}