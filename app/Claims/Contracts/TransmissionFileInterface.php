<?php

namespace App\Claims\Contracts;

use Carbon\Carbon;

/**
 * Interface TransmissionFileInterface
 * @package App\Claims\Contracts
 */
interface TransmissionFileInterface
{
    /**
     * Get the filename.
     *
     * @return string
     */
    public function getFilename(): string;

    /**
     * Get the results of the file.
     *
     * @return null|iterable
     */
    public function getResults(): ?iterable;

    /**
     * Get the date the file was created.
     *
     * @return Carbon
     */
    public function getDate(): Carbon;
}