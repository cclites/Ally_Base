<?php

namespace App\Contracts;

interface HasTimezone
{
    /**
     * Get the model's Timezone.
     *
     * @return string
     */
    public function getTimezone(): string;
}