<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Stripe\Objects\Support\Normalizers;

use Carbon\CarbonImmutable;
use EncoreDigitalGroup\Stripe\Objects\FinancialConnections\StripeBankAccount;
use EncoreDigitalGroup\Stripe\Objects\FinancialConnections\StripeTransactionRefresh;
use InvalidArgumentException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class StripeBankAccountNormalizer extends AbstractNormalizer implements DenormalizerInterface, NormalizerInterface
{
    public function normalize(mixed $data, ?string $format = null, array $context = []): array
    {
        if (!$data instanceof StripeBankAccount) {
            throw new InvalidArgumentException("The object must be an instance of StripeBankAccount");
        }

        return [
            "id" => $data->id(),
            "category" => $data->category(),
            "created" => $data->created()?->timestamp,
            "display_name" => $data->displayName(),
            "institution_name" => $data->institutionName(),
            "last4" => $data->last4(),
            "livemode" => $data->liveMode(),
            "permissions" => $data->permissions(),
            "subscriptions" => $data->subscriptions(),
            "supported_payment_method_types" => $data->supportedPaymentMethodTypes(),
            "transaction_refresh" => $data->transactionRefresh(),
        ];
    }

    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): StripeBankAccount
    {
        if ($data instanceof StripeBankAccount) {
            return $data;
        }

        if (!is_array($data)) {
            throw new InvalidArgumentException("Data must be an array for denormalization");
        }

        $bankAccount = $this->setBasicProperties(StripeBankAccount::make(), $data);
        $bankAccount = $this->setArrayProperties($bankAccount, $data);
        $bankAccount = $this->setTransactionRefresh($bankAccount, $data, $format, $context);

        return $bankAccount;
    }

    private function setBasicProperties(StripeBankAccount $bankAccount, array $data): StripeBankAccount
    {
        if (isset($data["id"])) {
            $bankAccount = $bankAccount->withId($data["id"]);
        }
        if (isset($data["category"])) {
            $bankAccount = $bankAccount->withCategory($data["category"]);
        }
        if (isset($data["created"])) {
            $bankAccount = $bankAccount->withCreated(CarbonImmutable::createFromTimestamp($data["created"]));
        }
        if (isset($data["display_name"])) {
            $bankAccount = $bankAccount->withDisplayName($data["display_name"]);
        }
        if (isset($data["institution_name"])) {
            $bankAccount = $bankAccount->withInstitutionName($data["institution_name"]);
        }
        if (isset($data["last4"])) {
            $bankAccount = $bankAccount->withLast4($data["last4"]);
        }
        if (isset($data["livemode"])) {
            $bankAccount = $bankAccount->withLiveMode($data["livemode"]);
        }

        return $bankAccount;
    }

    private function setArrayProperties(StripeBankAccount $bankAccount, array $data): StripeBankAccount
    {
        if (isset($data["permissions"])) {
            $bankAccount = $bankAccount->withPermissions($data["permissions"]);
        }
        if (isset($data["subscriptions"])) {
            $bankAccount = $bankAccount->withSubscriptions($data["subscriptions"]);
        }
        if (isset($data["supported_payment_method_types"])) {
            $bankAccount = $bankAccount->withSupportedPaymentMethodTypes($data["supported_payment_method_types"]);
        }

        return $bankAccount;
    }

    private function setTransactionRefresh(StripeBankAccount $bankAccount, array $data, ?string $format, array $context): StripeBankAccount
    {
        if (isset($data["transaction_refresh"])) {
            $normalizer = new StripeTransactionRefreshNormalizer($this->objectNormalizer);
            $transactionRefresh = $normalizer->denormalize($data["transaction_refresh"], StripeTransactionRefresh::class, $format, $context);
            $bankAccount = $bankAccount->withTransactionRefresh($transactionRefresh);
        }

        return $bankAccount;
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof StripeBankAccount;
    }

    public function supportsDenormalization($data, string $type, ?string $format = null, array $context = []): bool
    {
        return $type === StripeBankAccount::class || $type === StripeBankAccount::class . "[]";
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            StripeBankAccount::class => true,
            StripeBankAccount::class . "[]" => true,
        ];
    }
}