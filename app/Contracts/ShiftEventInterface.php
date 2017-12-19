<?php
namespace App\Contracts;


use App\Shift;

interface ShiftEventInterface
{
    /**
     * @param \App\Shift $shift
     */
    public function __construct(Shift $shift);

    /**
     * @return \App\Shift
     */
    public function shift();

}