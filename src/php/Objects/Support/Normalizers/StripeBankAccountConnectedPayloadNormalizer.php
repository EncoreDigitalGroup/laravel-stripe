<?php

namespace EncoreDigitalGroup\Stripe\Objects\Support\Normalizers;

use EncoreDigitalGroup\StdLib\Exceptions\ImproperBooleanReturnedException;
use EncoreDigitalGroup\Stripe\Objects\FinancialConnections\StripeBankAccount;
use EncoreDigitalGroup\Stripe\Objects\Support\SecurityKeyPair;
use EncoreDigitalGroup\Stripe\Objects\Support\StripeBankAccountConnectedPayload;
use InvalidArgumentException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class StripeBankAccountConnectedPayloadNormalizer extends AbstractNormalizer implements DenormalizerInterface, NormalizerInterface
{
    public function normalize(mixed $data, ?string $format = null, array $context = []): array
    {
        if (!$data instanceof StripeBankAccountConnectedPayload) {
            throw new InvalidArgumentException("The object must be an instance of StripeBankAccountConnectedPayload");
        }

        $result = [];

        if ($data->getSecurityKeys() instanceof SecurityKeyPair) {
            $result["securityKeys"] = $this->objectNormalizer->normalize($data->getSecurityKeys(), $format, $context);
        }

        $result["stripeCustomerId"] = $data->getStripeCustomerId();

        $result["accounts"] = array_map(
            fn($accountData): mixed => $this->objectNormalizer->denormalize($accountData, StripeBankAccount::class),
            $data->accounts
        );

        return $result;
    }

    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): StripeBankAccountConnectedPayload
    {
        $data = $this->extractData($data);

        $payload = new StripeBankAccountConnectedPayload;

        $payload->setStripeCustomerId($data["stripeCustomerId"]);

        $payload = $this->handleSecurityKeys($payload, $data, $format, $context);

        return $this->handleAccounts($payload, $data, $format, $context);
    }

    private function extractData(mixed $data): mixed
    {
        // If it's already the right type, extract its data for consistent processing
        if ($data instanceof StripeBankAccountConnectedPayload) {
            $extractedData = [
                "stripeCustomerId" => $data->getStripeCustomerId(),
                "accounts" => $data->accounts ?? [],
            ];

            if ($data->getSecurityKeys() instanceof SecurityKeyPair) {
                $extractedData["securityKeys"] = [
                    "publicKey" => $data->getSecurityKeys()->publicKey,
                    "privateKey" => $data->getSecurityKeys()->privateKey,
                ];
            }

            // Use extracted data for processing
            $data = $extractedData;
        } elseif (!is_array($data)) {
            // Existing non-array handling
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
            $data = $decodedData;
        }

        return $data;
    }

    private function handleSecurityKeys(StripeBankAccountConnectedPayload $payload, array $data, ?string $format, array $context): StripeBankAccountConnectedPayload
    {
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

        return $payload;
    }

    private function handleAccounts(StripeBankAccountConnectedPayload $payload, array $data, ?string $format, array $context): StripeBankAccountConnectedPayload
    {
        if (isset($data["accounts"]) && is_array($data["accounts"])) {
            $accounts = [];

            foreach ($data["accounts"] as $account) {
                $normalizer = new StripeBankAccountNormalizer($this->objectNormalizer);
                $denormalizedAccount = $normalizer->denormalize($account, StripeBankAccount::class, $format, $context);

                $accounts[] = $denormalizedAccount;
            }

            $payload->accounts = $accounts;
        }

        return $payload;
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof StripeBankAccountConnectedPayload;
    }

    public function supportsDenormalization($data, string $type, ?string $format = null, array $context = []): bool
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