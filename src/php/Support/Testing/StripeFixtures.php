<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Stripe\Support\Testing;

/**
 * StripeFixtures provides common test data for Stripe API responses
 *
 * These fixtures represent typical responses from the Stripe API and can be used
 * to fake API responses in tests.
 */
class StripeFixtures
{
    public static function customer(array $overrides = []): array
    {
        return array_merge([
            "id" => "cus_" . self::randomId(),
            "object" => "customer",
            "address" => null,
            "balance" => 0,
            "created" => time(),
            "currency" => "usd",
            "default_source" => null,
            "delinquent" => false,
            "description" => "Test Customer",
            "discount" => null,
            "email" => "test@example.com",
            "invoice_prefix" => self::randomString(8),
            "invoice_settings" => [
                "custom_fields" => null,
                "default_payment_method" => null,
                "footer" => null,
                "rendering_options" => null,
            ],
            "livemode" => false,
            "metadata" => [],
            "name" => "Test Customer",
            "phone" => null,
            "preferred_locales" => [],
            "shipping" => null,
            "tax_exempt" => "none",
            "test_clock" => null,
        ], $overrides);
    }

    public static function customerList(array $customers = [], array $overrides = []): array
    {
        return array_merge([
            "object" => "list",
            "data" => $customers,
            "has_more" => false,
            "url" => "/v1/customers",
        ], $overrides);
    }

    public static function product(array $overrides = []): array
    {
        return array_merge([
            "id" => "prod_" . self::randomId(),
            "object" => "product",
            "active" => true,
            "attributes" => [],
            "created" => time(),
            "default_price" => null,
            "description" => "Test Product",
            "images" => [],
            "livemode" => false,
            "metadata" => [],
            "name" => "Test Product",
            "package_dimensions" => null,
            "shippable" => null,
            "statement_descriptor" => null,
            "tax_code" => null,
            "type" => "service",
            "unit_label" => null,
            "updated" => time(),
            "url" => null,
        ], $overrides);
    }

    public static function productList(array $products = [], array $overrides = []): array
    {
        return array_merge([
            "object" => "list",
            "data" => $products,
            "has_more" => false,
            "url" => "/v1/products",
        ], $overrides);
    }

    public static function price(array $overrides = []): array
    {
        return array_merge([
            "id" => "price_" . self::randomId(),
            "object" => "price",
            "active" => true,
            "billing_scheme" => "per_unit",
            "created" => time(),
            "currency" => "usd",
            "custom_unit_amount" => null,
            "livemode" => false,
            "lookup_key" => null,
            "metadata" => [],
            "nickname" => null,
            "product" => "prod_" . self::randomId(),
            "recurring" => [
                "aggregate_usage" => null,
                "interval" => "month",
                "interval_count" => 1,
                "trial_period_days" => null,
                "usage_type" => "licensed",
            ],
            "tax_behavior" => "unspecified",
            "tiers_mode" => null,
            "transform_quantity" => null,
            "type" => "recurring",
            "unit_amount" => 1000,
            "unit_amount_decimal" => "1000",
        ], $overrides);
    }

    public static function priceList(array $prices = [], array $overrides = []): array
    {
        return array_merge([
            "object" => "list",
            "data" => $prices,
            "has_more" => false,
            "url" => "/v1/prices",
        ], $overrides);
    }

    public static function subscription(array $overrides = []): array
    {
        $now = time();

        return array_merge([
            "id" => "sub_" . self::randomId(),
            "object" => "subscription",
            "application" => null,
            "application_fee_percent" => null,
            "automatic_tax" => [
                "enabled" => false,
            ],
            "billing_cycle_anchor" => $now,
            "billing_cycle_anchor_config" => null,
            "billing_thresholds" => null,
            "cancel_at" => null,
            "cancel_at_period_end" => false,
            "canceled_at" => null,
            "cancellation_details" => [
                "comment" => null,
                "feedback" => null,
                "reason" => null,
            ],
            "collection_method" => "charge_automatically",
            "created" => $now,
            "currency" => "usd",
            "current_period_end" => $now + 2592000, // 30 days
            "current_period_start" => $now,
            "customer" => "cus_" . self::randomId(),
            "days_until_due" => null,
            "default_payment_method" => null,
            "default_source" => null,
            "default_tax_rates" => [],
            "description" => null,
            "discount" => null,
            "ended_at" => null,
            "items" => [
                "object" => "list",
                "data" => [
                    [
                        "id" => "si_" . self::randomId(),
                        "object" => "subscription_item",
                        "billing_thresholds" => null,
                        "created" => $now,
                        "metadata" => [],
                        "plan" => self::price(),
                        "price" => self::price(),
                        "quantity" => 1,
                        "subscription" => "sub_" . self::randomId(),
                        "tax_rates" => [],
                    ],
                ],
                "has_more" => false,
                "url" => "/v1/subscription_items",
            ],
            "latest_invoice" => null,
            "livemode" => false,
            "metadata" => [],
            "next_pending_invoice_item_invoice" => null,
            "on_behalf_of" => null,
            "pause_collection" => null,
            "payment_settings" => [
                "payment_method_options" => null,
                "payment_method_types" => null,
                "save_default_payment_method" => "off",
            ],
            "pending_invoice_item_interval" => null,
            "pending_setup_intent" => null,
            "pending_update" => null,
            "proration_behavior" => "create_prorations",
            "schedule" => null,
            "start_date" => $now,
            "status" => "active",
            "test_clock" => null,
            "transfer_data" => null,
            "trial_end" => null,
            "trial_settings" => [
                "end_behavior" => [
                    "missing_payment_method" => "create_invoice",
                ],
            ],
            "trial_start" => null,
        ], $overrides);
    }

    public static function subscriptionList(array $subscriptions = [], array $overrides = []): array
    {
        return array_merge([
            "object" => "list",
            "data" => $subscriptions,
            "has_more" => false,
            "url" => "/v1/subscriptions",
        ], $overrides);
    }

    public static function deleted(string $id, string $object = "customer"): array
    {
        return [
            "id" => $id,
            "object" => $object,
            "deleted" => true,
        ];
    }

    public static function error(string $type = "card_error", string $message = "Your card was declined."): array
    {
        return [
            "error" => [
                "type" => $type,
                "message" => $message,
                "code" => "card_declined",
            ],
        ];
    }

    public static function bankAccount(array $overrides = []): array
    {
        return array_merge([
            "id" => "ba_" . self::randomId(),
            "object" => "bank_account",
            "account_holder_name" => "Test Account",
            "account_holder_type" => "individual",
            "account_type" => "checking",
            "bank_name" => "STRIPE TEST BANK",
            "country" => "US",
            "currency" => "usd",
            "customer" => "cus_" . self::randomId(),
            "fingerprint" => self::randomString(16),
            "last4" => "6789",
            "metadata" => [],
            "routing_number" => "110000000",
            "status" => "verified",
        ], $overrides);
    }

    public static function financialConnectionsAccount(array $overrides = []): array
    {
        return array_merge([
            "id" => "fca_" . self::randomId(),
            "object" => "financial_connections.account",
            "account_holder" => [
                "customer" => "cus_" . self::randomId(),
                "type" => "customer",
            ],
            "balance" => [
                "as_of" => time(),
                "current" => [
                    "usd" => 10000,
                ],
                "type" => "cash",
            ],
            "balance_refresh" => null,
            "category" => "cash",
            "created" => time(),
            "display_name" => "Test Bank Account",
            "institution_name" => "Test Bank",
            "last4" => "6789",
            "livemode" => false,
            "ownership" => null,
            "permissions" => ["balances", "transactions"],
            "status" => "active",
            "subcategory" => "checking",
            "supported_payment_method_types" => ["us_bank_account"],
        ], $overrides);
    }

    public static function subscriptionSchedule(array $overrides = []): array
    {
        $now = \Carbon\CarbonImmutable::now()->timestamp;

        return array_merge([
            "id" => "sub_sched_" . self::randomId(),
            "object" => "subscription_schedule",
            "canceled_at" => null,
            "completed_at" => null,
            "created" => $now,
            "customer" => "cus_" . self::randomId(),
            "default_settings" => [
                "application_fee_percent" => null,
                "automatic_tax" => [
                    "enabled" => false,
                ],
                "billing_cycle_anchor" => "automatic",
                "billing_thresholds" => null,
                "collection_method" => "charge_automatically",
                "default_payment_method" => null,
                "default_source" => null,
                "default_tax_rates" => [],
                "description" => null,
                "invoice_settings" => [
                    "account_tax_ids" => null,
                    "custom_fields" => null,
                    "days_until_due" => null,
                    "default_payment_method" => null,
                    "footer" => null,
                    "issuer" => null,
                    "rendering_options" => null,
                ],
                "on_behalf_of" => null,
                "transfer_data" => null,
            ],
            "end_behavior" => "release",
            "livemode" => false,
            "metadata" => [],
            "phases" => [
                [
                    "add_invoice_items" => [],
                    "application_fee_percent" => null,
                    "automatic_tax" => [
                        "enabled" => false,
                    ],
                    "billing_cycle_anchor" => null,
                    "billing_thresholds" => null,
                    "collection_method" => null,
                    "coupon" => null,
                    "currency" => "usd",
                    "default_payment_method" => null,
                    "default_tax_rates" => [],
                    "description" => null,
                    "discounts" => [],
                    "end_date" => \Carbon\CarbonImmutable::now()->addMonth()->timestamp,
                    "invoice_settings" => null,
                    "items" => [
                        [
                            "billing_thresholds" => null,
                            "metadata" => [],
                            "plan" => null,
                            "price" => "price_" . self::randomId(),
                            "quantity" => 1,
                            "tax_rates" => [],
                        ],
                    ],
                    "iterations" => null,
                    "metadata" => [],
                    "on_behalf_of" => null,
                    "proration_behavior" => "create_prorations",
                    "start_date" => $now,
                    "transfer_data" => null,
                    "trial_end" => null,
                ],
            ],
            "released_at" => null,
            "released_subscription" => null,
            "status" => "not_started",
            "subscription" => null,
            "test_clock" => null,
        ], $overrides);
    }

    public static function subscriptionScheduleList(array $subscriptionSchedules = [], array $overrides = []): array
    {
        return array_merge([
            "object" => "list",
            "data" => $subscriptionSchedules,
            "has_more" => false,
            "url" => "/v1/subscription_schedules",
        ], $overrides);
    }

    protected static function randomId(int $length = 24): string
    {
        return self::randomString($length);
    }

    protected static function randomString(int $length = 16): string
    {
        $characters = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $string = "";
        for ($i = 0; $i < $length; $i++) {
            $string .= $characters[rand(0, strlen($characters) - 1)];
        }

        return $string;
    }
}
