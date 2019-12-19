<?php

namespace App;

/**
 * Class Caregiver1099Payer
 *
 * @method static ALLY()
 * @method static ALLY_LOCKED()
 * @method static CLIENT()
 * @method static NONE()
 */
class Caregiver1099Payer extends BaseEnum
{
    private const ALLY = 'ally';
    private const ALLY_LOCKED = 'ally_locked';
    private const CLIENT = 'client';
    private const NONE = 'no';
}