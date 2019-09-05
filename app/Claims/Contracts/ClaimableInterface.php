<?php

namespace App\Claims\Contracts;

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