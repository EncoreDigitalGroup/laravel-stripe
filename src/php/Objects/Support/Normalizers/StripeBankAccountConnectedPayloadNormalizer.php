<?php
/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Common\Stripe\Objects\Support\Normalizers;

use EncoreDigitalGroup\Common\Stripe\Objects\Support\SecurityKeyPair;
use EncoreDigitalGroup\Common\Stripe\Objects\Support\StripeBankAccountConnectedPayload;
use EncoreDigitalGroup\StdLib\Exceptions\ImproperBooleanReturnedException;
use InvalidArgumentException;
use EncoreDigitalGroup\Common\Stripe\Objects\FinancialConnections\StripeBankAccount;
use PHPGenesis\Logger\Logger;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class StripeBankAccountConnectedPayloadNormalizer implements NormalizerInterface, DenormalizerInterface
{
    private ObjectNormalizer $objectNormalizer;

    public function __construct(ObjectNormalizer $objectNormalizer)
    {
        $this->objectNormalizer = $objectNormalizer;
    }

    public function normalize(mixed $data, string $format = null, array $context = []): array
    {
        if (!$data instanceof StripeBankAccountConnectedPayload) {
            throw new InvalidArgumentException("The object must be an instance of StripeBankAccountConnectedPayload");
        }

        $result = [];

        // Only include securityKeys if it exists
        if ($data->getSecurityKeys() instanceof \EncoreDigitalGroup\Common\Stripe\Objects\Support\SecurityKeyPair) {
            $result["securityKeys"] = $this->objectNormalizer->normalize($data->getSecurityKeys(), $format, $context);
        }

        $result["stripeCustomerId"] = $data->getStripeCustomerId();

        $result["accounts"] = array_map(
            fn($account): array|\ArrayObject|bool|float|int|string|null => $this->objectNormalizer->normalize($account, $format, $context),
            $data->accounts
        );

        return $result;
    }

    public function denormalize(mixed $data, string $type, string $format = null, array $context = []): StripeBankAccountConnectedPayload
    {
        if ($data instanceof StripeBankAccountConnectedPayload) {
            return $data;
        }
        if (!is_array($data)) {
            $stripeCustomerId = $data->getStripeCustomerId();
            $securityKeys = $data->getSecurityKeys();
            $encodedData = json_encode($data);
            if (!$encodedData) {
                throw new ImproperBooleanReturnedException;
            }
            $decodedData = json_decode($encodedData, true);
            $decodedData["stripeCustomerId"] = $stripeCustomerId;
            $decodedData["securityKeys"]["publicKey"] = $securityKeys->publicKey;
            $decodedData["securityKeys"]["privateKey"] = $securityKeys->privateKey;
        }

        $payload = new StripeBankAccountConnectedPayload();

        // Handle stripeCustomerId
        $payload->setStripeCustomerId($data["stripeCustomerId"]);

        // Handle securityKeys
        if (isset($data["securityKeys"])) {
            $securityKeys = $this->objectNormalizer->denormalize(
                $data["securityKeys"],
                SecurityKeyPair::class,
                $format,
                $context
            );
            $payload->setSecurityKeys([
                "publicKey" => $securityKeys->publicKey,
                "privateKey" => $securityKeys->privateKey,
            ]);
        }

        // Handle accounts
        if (isset($data["accounts"]) && is_array($data["accounts"])) {
            $payload->accounts = array_map(
                fn($accountData): mixed => $this->objectNormalizer->denormalize(
                    $accountData,
                    StripeBankAccount::class,
                    $format,
                    $context
                ),
                $data["accounts"]
            );
        }

        return $payload;
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof StripeBankAccountConnectedPayload;
    }

    public function supportsDenormalization($data, string $type, string $format = null, array $context = []): bool
    {
        return $type === StripeBankAccountConnectedPayload::class || $type === StripeBankAccountConnectedPayload::class . "[]";
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            StripeBankAccountConnectedPayload::class => true,
            StripeBankAccountConnectedPayload::class . "[]" => true,
        ];
    }
}