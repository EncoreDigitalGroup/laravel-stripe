<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Stripe\Services;

use EncoreDigitalGroup\Stripe\Enums\SubscriptionScheduleEndBehavior;
use EncoreDigitalGroup\Stripe\Objects\Subscription\Schedules\StripeSubscriptionSchedule;
use EncoreDigitalGroup\Stripe\Support\Traits\HasStripe;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\ApiErrorException;

/** @internal */
class StripeSubscriptionScheduleService
{
    use HasStripe;

    /** @throws ApiErrorException */
    public function create(StripeSubscriptionSchedule $subscriptionSchedule): StripeSubscriptionSchedule
    {
        $data = $subscriptionSchedule->toArray();

        unset($data["id"], $data["object"], $data["created"], $data["canceled_at"], $data["completed_at"], $data["released_at"], $data["status"]);

        $stripeSubscriptionSchedule = $this->stripe->subscriptionSchedules->create($data);

        return StripeSubscriptionSchedule::fromStripeObject($stripeSubscriptionSchedule);
    }

    /** @throws ApiErrorException */
    public function get(string $subscriptionScheduleId): StripeSubscriptionSchedule
    {
        $stripeSubscriptionSchedule = $this->stripe->subscriptionSchedules->retrieve($subscriptionScheduleId);

        return StripeSubscriptionSchedule::fromStripeObject($stripeSubscriptionSchedule);
    }

    /** @throws ApiErrorException */
    public function update(string $subscriptionScheduleId, StripeSubscriptionSchedule $subscriptionSchedule): StripeSubscriptionSchedule
    {
        $data = $subscriptionSchedule->toArray();

        unset($data["id"], $data["object"], $data["created"], $data["canceled_at"], $data["completed_at"], $data["released_at"], $data["status"], $data["customer"], $data["subscription"], $data["released_subscription"]);

        $stripeSubscriptionSchedule = $this->stripe->subscriptionSchedules->update($subscriptionScheduleId, $data);

        return StripeSubscriptionSchedule::fromStripeObject($stripeSubscriptionSchedule);
    }

    /** @throws ApiErrorException */
    public function cancel(string $subscriptionScheduleId, ?bool $invoiceNow = null, ?bool $prorate = null): StripeSubscriptionSchedule
    {
        $params = [];

        if ($invoiceNow !== null) {
            $params["invoice_now"] = $invoiceNow;
        }

        if ($prorate !== null) {
            $params["prorate"] = $prorate;
        }

        $stripeSubscriptionSchedule = $this->stripe->subscriptionSchedules->cancel($subscriptionScheduleId, $params);

        return StripeSubscriptionSchedule::fromStripeObject($stripeSubscriptionSchedule);
    }

    /** @throws ApiErrorException */
    public function release(string $subscriptionScheduleId, ?bool $preserveCancelDate = null): StripeSubscriptionSchedule
    {
        $params = [];

        if ($preserveCancelDate !== null) {
            $params["preserve_cancel_date"] = $preserveCancelDate;
        }

        $stripeSubscriptionSchedule = $this->stripe->subscriptionSchedules->release($subscriptionScheduleId, $params);

        return StripeSubscriptionSchedule::fromStripeObject($stripeSubscriptionSchedule);
    }

    /** @throws ApiErrorException */
    public function fromSubscription(string $subscriptionId): StripeSubscriptionSchedule
    {
        $response = $this->stripe->subscriptionSchedules->create([
            "from_subscription" => $subscriptionId,
        ]);

        return StripeSubscriptionSchedule::fromStripeObject($response);
    }
}