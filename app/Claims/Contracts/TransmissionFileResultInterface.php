<?php

namespace App\Claims\Contracts;

use Carbon\Carbon;

/**
 * Interface TransmissionFileResultInterface
 * @package App\Claims\Contracts
 */
interface TransmissionFileResultInterface
{
    /**
     * Get the filename of the response file.
     *
     * @return string
     */
    public function getServiceCode(): string;

    /**
     * Get the date of service.
     *
     * @return Carbon
     */
    public function getServiceDate(): Carbon;

    /**
     * Get the status code of the result.
     *
     * @return string
     */
    public function getStatusCode(): string;

    /**
     * Get the status of the result.
     *
     * @return string
     */
    public function getStatus(): string;
}