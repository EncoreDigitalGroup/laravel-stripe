<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Common\Stripe\Objects\Support\Normalizers;

use EncoreDigitalGroup\Common\Stripe\Objects\FinancialConnections\StripeBankAccount;
use InvalidArgumentException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class StripeBankAccountNormalizer extends AbstractNormalizer implements NormalizerInterface, DenormalizerInterface
{
    public function normalize(mixed $data, ?string $format = null, array $context = []): array
    {
        if (!$data instanceof StripeBankAccount) {
            throw new InvalidArgumentException("The object must be an instance of StripeBankAccount");
        }

        return [
            "id" => $data->id,
            "category" => $data->category,
            "created" => $data->created,
            "display_name" => $data->displayName,
            "institution_name" => $data->institutionName,
            "last4" => $data->last4,
            "livemode" => $data->liveMode,
            "permissions" => $data->permissions,
            "subscriptions" => $data->subscriptions,
            "supported_payment_method_types" => $data->supportedPaymentMethodTypes,
            "transaction_refresh" => $data->transactionRefresh,
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

        $bankAccount = new StripeBankAccount();

        $bankAccount->id = $data["id"] ?? null;
        $bankAccount->category = $data["category"] ?? null;
        $bankAccount->created = $data["created"] ?? null;
        $bankAccount->displayName = $data["display_name"] ?? null;
        $bankAccount->institutionName = $data["institution_name"] ?? null;
        $bankAccount->last4 = $data["last4"] ?? null;
        $bankAccount->liveMode = $data["livemode"] ?? null;
        $bankAccount->permissions = $data["permissions"] ?? [];
        $bankAccount->subscriptions = $data["subscriptions"] ?? [];
        $bankAccount->supportedPaymentMethodTypes = $data["supported_payment_method_types"] ?? [];
        $bankAccount->transactionRefresh = $data["transaction_refresh"] ?? null;

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