<?php

namespace EncoreDigitalGroup\Stripe\Support\Traits;

trait HasReadOnlyFields
{
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

    abstract protected function getReadOnlyFields(): array;

    protected function getUpdateOnlyReadOnlyFields(): array
    {
        return [];
    }
}