<?php

/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Stripe\Services;

use EncoreDigitalGroup\Stripe\Objects\Subscription\Schedules\StripeSubscriptionSchedule;
use EncoreDigitalGroup\Stripe\Support\HasMake;
use EncoreDigitalGroup\Stripe\Support\HasStripe;
use Illuminate\Support\Collection;

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

    public function retrieve(string $subscriptionScheduleId): StripeSubscriptionSchedule
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
            $params['invoice_now'] = $invoiceNow;
        }

        if ($prorate !== null) {
            $params['prorate'] = $prorate;
        }

        $stripeSubscriptionSchedule = $this->stripe->subscriptionSchedules->cancel($subscriptionScheduleId, $params);
        return StripeSubscriptionSchedule::fromStripeObject($stripeSubscriptionSchedule);
    }

    public function release(string $subscriptionScheduleId, ?bool $preserveCancelDate = null): StripeSubscriptionSchedule
    {
        $params = [];

        if ($preserveCancelDate !== null) {
            $params['preserve_cancel_date'] = $preserveCancelDate;
        }

        $stripeSubscriptionSchedule = $this->stripe->subscriptionSchedules->release($subscriptionScheduleId, $params);
        return StripeSubscriptionSchedule::fromStripeObject($stripeSubscriptionSchedule);
    }

    public function all(?string $customer = null, ?int $limit = null): Collection
    {
        $params = [];

        if ($customer !== null) {
            $params['customer'] = $customer;
        }

        if ($limit !== null) {
            $params['limit'] = $limit;
        }

        $stripeSubscriptionSchedules = $this->stripe->subscriptionSchedules->all($params);

        return collect($stripeSubscriptionSchedules->data)->map(function ($stripeSubscriptionSchedule) {
            return StripeSubscriptionSchedule::fromStripeObject($stripeSubscriptionSchedule);
        });
    }
}