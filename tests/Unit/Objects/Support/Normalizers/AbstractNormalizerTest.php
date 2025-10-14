<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

use EncoreDigitalGroup\Stripe\Objects\Support\Normalizers\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

test("can instantiate AbstractNormalizer with default ObjectNormalizer", function (): void {
    $normalizer = new AbstractNormalizer;

    $reflection = new ReflectionClass($normalizer);
    $property = $reflection->getProperty("objectNormalizer");
    $property->setAccessible(true);

    expect($property->getValue($normalizer))->toBeInstanceOf(ObjectNormalizer::class);
});

test("can instantiate AbstractNormalizer with custom ObjectNormalizer", function (): void {
    $customNormalizer = new ObjectNormalizer;
    $normalizer = new AbstractNormalizer($customNormalizer);

    $reflection = new ReflectionClass($normalizer);
    $property = $reflection->getProperty("objectNormalizer");
    $property->setAccessible(true);

    expect($property->getValue($normalizer))->toBe($customNormalizer);
});
