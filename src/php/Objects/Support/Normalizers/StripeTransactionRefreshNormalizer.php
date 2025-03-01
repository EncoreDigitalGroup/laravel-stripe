<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Common\Stripe\Objects\Support\Normalizers;

use EncoreDigitalGroup\Common\Stripe\Objects\FinancialConnections\StripeTransactionRefresh;
use InvalidArgumentException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class StripeTransactionRefreshNormalizer extends AbstractNormalizer implements DenormalizerInterface, NormalizerInterface
{
    public function normalize(mixed $data, ?string $format = null, array $context = []): array
    {
        if (!$data instanceof StripeTransactionRefresh) {
            throw new InvalidArgumentException("The object must be an instance of StripeTransactionRefresh");
        }

        return [
            "id" => $data->id,
            "lastAttemptedAt" => $data->lastAttemptedAt,
            "nextRefreshAvailableAt" => $data->nextRefreshAvailableAt,
            "status" => $data->status,
        ];
    }

    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): StripeTransactionRefresh
    {
        if ($data instanceof StripeTransactionRefresh) {
            return $data;
        }

        if (!is_array($data)) {
            throw new InvalidArgumentException("Data must be an array for denormalization");
        }

        $transactionRefresh = new StripeTransactionRefresh;
        $transactionRefresh->id = $data["id"] ?? null;
        $transactionRefresh->nextRefreshAvailableAt = $data["next_refresh_available_at"] ?? null;
        $transactionRefresh->lastAttemptedAt = $data["last_attempted_at"] ?? null;
        $transactionRefresh->status = $data["status"] ?? null;

        return $transactionRefresh;
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof StripeTransactionRefresh;
    }

    public function supportsDenormalization($data, string $type, ?string $format = null, array $context = []): bool
    {
        return $type === StripeTransactionRefresh::class;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            StripeTransactionRefresh::class => true,
        ];
    }
}