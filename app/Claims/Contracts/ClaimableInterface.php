<?php

namespace App\Claims\Contracts;

use Carbon\Carbon;

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

    /**
     * Get the Caregiver's name that performed the service.
     *
     * @return string
     */
    public function getCaregiverName(): string;

    /**
     * Get the Client's name.
     *
     * @return string
     */
    public function getClientName(): string;

    /**
     * Get the start time of the Claimable item.
     *
     * @return null|Carbon
     */
    public function getStartTime(): ?Carbon;

    /**
     * Get the end time of the Claimable item.
     *
     * @return null|Carbon
     */
    public function getEndTime(): ?Carbon;
}