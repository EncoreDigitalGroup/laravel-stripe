<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Stripe\Services;

use EncoreDigitalGroup\Stripe\Enums\SubscriptionScheduleEndBehavior;
use EncoreDigitalGroup\Stripe\Objects\Subscription\Schedules\StripeSubscriptionSchedule;
use EncoreDigitalGroup\Stripe\Support\Traits\HasStripe;

/** @internal */
class StripeSubscriptionScheduleService
{
    use HasStripe;

    public function create(StripeSubscriptionSchedule $subscriptionSchedule): StripeSubscriptionSchedule
    {
        $data = $subscriptionSchedule->toArray();

        unset($data["id"], $data["object"], $data["created"], $data["canceled_at"], $data["completed_at"], $data["released_at"], $data["status"]);

        $stripeSubscriptionSchedule = $this->stripe->subscriptionSchedules->create($data);

        return StripeSubscriptionSchedule::fromStripeObject($stripeSubscriptionSchedule);
    }

    public function get(string $subscriptionScheduleId): StripeSubscriptionSchedule
    {
        $stripeSubscriptionSchedule = $this->stripe->subscriptionSchedules->retrieve($subscriptionScheduleId);

        return StripeSubscriptionSchedule::fromStripeObject($stripeSubscriptionSchedule);
    }

    public function update(string $subscriptionScheduleId, StripeSubscriptionSchedule $subscriptionSchedule): StripeSubscriptionSchedule
    {
        $data = $subscriptionSchedule->toArray();

        unset($data["id"], $data["object"], $data["created"], $data["canceled_at"], $data["completed_at"], $data["released_at"], $data["status"], $data["customer"]);

        $stripeSubscriptionSchedule = $this->stripe->subscriptionSchedules->update($subscriptionScheduleId, $data);

        return StripeSubscriptionSchedule::fromStripeObject($stripeSubscriptionSchedule);
    }

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

    public function release(string $subscriptionScheduleId, ?bool $preserveCancelDate = null): StripeSubscriptionSchedule
    {
        $params = [];

        if ($preserveCancelDate !== null) {
            $params["preserve_cancel_date"] = $preserveCancelDate;
        }

        $stripeSubscriptionSchedule = $this->stripe->subscriptionSchedules->release($subscriptionScheduleId, $params);

        return StripeSubscriptionSchedule::fromStripeObject($stripeSubscriptionSchedule);
    }

    public function forSubscription(string $subscriptionId): ?StripeSubscriptionSchedule
    {
        $params = ["subscription" => $subscriptionId];

        $stripeSubscriptionSchedules = $this->stripe->subscriptionSchedules->all($params);

        if (empty($stripeSubscriptionSchedules->data)) {
            return null;
        }

        return StripeSubscriptionSchedule::fromStripeObject($stripeSubscriptionSchedules->data[0]);
    }

    public function fromSubscription(string $subscriptionId): StripeSubscriptionSchedule
    {
        $response = $this->stripe->subscriptionSchedules->create([
            "from_subscription" => $subscriptionId,
        ]);

        return StripeSubscriptionSchedule::fromStripeObject($response);
    }
}