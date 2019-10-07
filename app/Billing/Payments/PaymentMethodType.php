<?php


namespace App\Billing\Payments;


use MyCLabs\Enum\Enum;

class PaymentMethodType extends Enum
{
    private const NONE = "NONE";
    private const MANUAL = "MANUAL";
    private const CC = "CC"; // Generic credit card
    private const AMEX = "AMEX";
    private const ACH = "ACH";
    private const ACH_P = "ACH-P";
    private const TRUST = "TRUST";

    static function NONE() { return new self(self::NONE); }
    static function MANUAL() { return new self(self::MANUAL); }
    static function CC() { return new self(self::CC); }
    static function AMEX() { return new self(self::AMEX); }
    static function ACH() { return new self(self::ACH); }
    static function ACH_P() { return new self(self::ACH_P); }
    static function TRUST() { return new self(self::TRUST); }

    /**
     * Get all of the payment types in array form.
     *
     * @return array
     */
    public static function all(): array
    {
        try {
            $cls = new \ReflectionClass(__CLASS__);
            return $cls->getConstants();
        } catch (\ReflectionException $ex) {
            app('sentry')->captureException($ex);
            return [];
        }
    }
}