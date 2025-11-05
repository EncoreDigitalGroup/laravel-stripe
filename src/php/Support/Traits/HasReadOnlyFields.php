<?php
/*
 * Copyright (c) 2025. Encore Digital Group.
 * All Right Reserved.
 */

namespace EncoreDigitalGroup\Stripe\Support\Traits;

trait HasReadOnlyFields
{
    abstract protected function getReadOnlyFields(): array;

    protected function getUpdateOnlyReadOnlyFields(): array
    {
        return [];
    }

    public function toCreateArray(): array
    {
        $array = $this->toArray();
        $readOnlyFields = $this->getReadOnlyFields();

        foreach ($readOnlyFields as $field) {
            unset($array[$field]);
        }

        return $array;
    }

    public function toUpdateArray(): array
    {
        $array = $this->toArray();
        $readOnlyFields = array_merge(
            $this->getReadOnlyFields(),
            $this->getUpdateOnlyReadOnlyFields()
        );

        foreach ($readOnlyFields as $field) {
            unset($array[$field]);
        }

        return $array;
    }
}