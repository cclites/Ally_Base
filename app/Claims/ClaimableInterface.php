<?php

namespace App\Claims;

/**
 * Interface ClaimableInterface
 * @package App\Claims
 */
interface ClaimableInterface
{
    /**
     * Get the name of the Claimable Item.
     *
     * @return string
     */
    public function getName(): string;
}