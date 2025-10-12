<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

use EncoreDigitalGroup\Common\Stripe\Enums\CollectionMethod;
use EncoreDigitalGroup\Common\Stripe\Enums\SubscriptionStatus;
use EncoreDigitalGroup\Common\Stripe\Objects\Subscription\StripeSubscription;

test('can create StripeSubscription using make method', function () {
    $subscription = StripeSubscription::make(
        customer: 'cus_123',
        status: SubscriptionStatus::Active,
        items: [
            ['price' => 'price_123', 'quantity' => 1],
        ]
    );

    expect($subscription)
        ->toBeInstanceOf(StripeSubscription::class)
        ->and($subscription->customer)->toBe('cus_123')
        ->and($subscription->status)->toBe(SubscriptionStatus::Active)
        ->and($subscription->items)->toBeArray();
});

test('can create StripeSubscription from Stripe object', function () {
    $stripeObject = \Stripe\Util\Util::convertToStripeObject([
        'id' => 'sub_123',
        'object' => 'subscription',
        'customer' => 'cus_123',
        'status' => 'active',
        'current_period_start' => 1234567890,
        'current_period_end' => 1237159890,
        'cancel_at' => null,
        'canceled_at' => null,
        'trial_start' => null,
        'trial_end' => null,
        'items' => [
            'data' => [
                [
                    'id' => 'si_123',
                    'price' => ['id' => 'price_123'],
                    'quantity' => 1,
                    'metadata' => ['key' => 'value'],
                ],
            ],
        ],
        'default_payment_method' => 'pm_123',
        'metadata' => ['subscription_key' => 'subscription_value'],
        'currency' => 'usd',
        'collection_method' => 'charge_automatically',
        'billing_cycle_anchor' => 1234567890,
        'cancel_at_period_end' => false,
        'days_until_due' => null,
        'description' => 'Test Subscription',
    ], []);

    $subscription = StripeSubscription::fromStripeObject($stripeObject);

    expect($subscription)
        ->toBeInstanceOf(StripeSubscription::class)
        ->and($subscription->id)->toBe('sub_123')
        ->and($subscription->customer)->toBe('cus_123')
        ->and($subscription->status)->toBe(SubscriptionStatus::Active)
        ->and($subscription->currentPeriodStart)->toBe(1234567890)
        ->and($subscription->currentPeriodEnd)->toBe(1237159890)
        ->and($subscription->items)->toBeArray()
        ->and($subscription->items)->toHaveCount(1)
        ->and($subscription->defaultPaymentMethod)->toBe('pm_123')
        ->and($subscription->collectionMethod)->toBe(CollectionMethod::ChargeAutomatically)
        ->and($subscription->cancelAtPeriodEnd)->toBeFalse()
        ->and($subscription->description)->toBe('Test Subscription');
});

test('fromStripeObject handles nested customer object', function () {
    $stripeObject = \Stripe\Util\Util::convertToStripeObject([
        'id' => 'sub_123',
        'object' => 'subscription',
        'customer' => [
            'id' => 'cus_123',
            'object' => 'customer',
        ],
        'status' => 'active',
        'items' => ['data' => []],
        'metadata' => [],
    ], []);

    $subscription = StripeSubscription::fromStripeObject($stripeObject);

    expect($subscription->customer)->toBe('cus_123');
});

test('fromStripeObject handles nested payment method object', function () {
    $stripeObject = \Stripe\Util\Util::convertToStripeObject([
        'id' => 'sub_123',
        'object' => 'subscription',
        'customer' => 'cus_123',
        'status' => 'active',
        'default_payment_method' => [
            'id' => 'pm_123',
            'object' => 'payment_method',
        ],
        'items' => ['data' => []],
        'metadata' => [],
    ], []);

    $subscription = StripeSubscription::fromStripeObject($stripeObject);

    expect($subscription->defaultPaymentMethod)->toBe('pm_123');
});

test('toArray converts enums to values', function () {
    $subscription = StripeSubscription::make(
        customer: 'cus_123',
        status: SubscriptionStatus::Active,
        collectionMethod: CollectionMethod::ChargeAutomatically
    );

    $array = $subscription->toArray();

    expect($array)->toBeArray()
        ->and($array['status'])->toBe('active')
        ->and($array['collection_method'])->toBe('charge_automatically');
});

test('toArray filters null values', function () {
    $subscription = StripeSubscription::make(
        customer: 'cus_123'
    );

    $array = $subscription->toArray();

    expect($array)->toBeArray()
        ->and($array)->toHaveKey('customer')
        ->and($array)->not->toHaveKey('id')
        ->and($array)->not->toHaveKey('status')
        ->and($array)->not->toHaveKey('description');
});

test('fromStripeObject handles items correctly', function () {
    $stripeObject = \Stripe\Util\Util::convertToStripeObject([
        'id' => 'sub_123',
        'object' => 'subscription',
        'customer' => 'cus_123',
        'status' => 'active',
        'items' => [
            'data' => [
                [
                    'id' => 'si_1',
                    'price' => ['id' => 'price_1'],
                    'quantity' => 2,
                    'metadata' => ['item_key' => 'item_value'],
                ],
                [
                    'id' => 'si_2',
                    'price' => ['id' => 'price_2'],
                    'quantity' => 1,
                    'metadata' => [],
                ],
            ],
        ],
        'metadata' => [],
    ], []);

    $subscription = StripeSubscription::fromStripeObject($stripeObject);

    expect($subscription->items)->toBeArray()
        ->and($subscription->items)->toHaveCount(2)
        ->and($subscription->items[0]['price'])->toBe('price_1')
        ->and($subscription->items[0]['quantity'])->toBe(2)
        ->and($subscription->items[1]['price'])->toBe('price_2');
});
