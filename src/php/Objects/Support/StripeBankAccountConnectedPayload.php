<?php

namespace EncoreDigitalGroup\Stripe\Objects\Support;

use EncoreDigitalGroup\Stripe\Objects\FinancialConnections\StripeBankAccount;
use Symfony\Component\Serializer\Attribute\SerializedName;

class StripeBankAccountConnectedPayload
{
    /** @var StripeBankAccount[] */
    public array $accounts;

    #[SerializedName("securityKeys")]
    private ?SecurityKeyPair $securityKeys = null;

    #[SerializedName("stripeCustomerId")]
    private ?string $stripeCustomerId = null;

    public function getSecurityKeys(): ?SecurityKeyPair
    {
        return $this->securityKeys;
    }

    public function setSecurityKeys(array $securityKeys): static
    {
        $this->securityKeys = new SecurityKeyPair;
        $this->securityKeys->publicKey = $securityKeys["publicKey"];
        $this->securityKeys->privateKey = $securityKeys["privateKey"];

        return $this;
    }

    public function getStripeCustomerId(): ?string
    {
        return $this->stripeCustomerId;
    }

    public function setStripeCustomerId(string $stripeCustomerId): static
    {
        $this->stripeCustomerId = $stripeCustomerId;

        return $this;
    }
}