<?php
namespace App\Shifts;

use App\Shift;

class ShiftStatusManager
{
    /**
     * @var \App\Shift
     */
    protected $shift;

    /**
     * @var array
     */
    protected static $statuses = [
        Shift::CLOCKED_IN,
        Shift::CLOCKED_OUT,
        Shift::WAITING_FOR_APPROVAL,
        Shift::WAITING_FOR_AUTHORIZATION,
        Shift::WAITING_FOR_CHARGE,
        Shift::WAITING_FOR_PAYOUT,
        Shift::PAID_NOT_CHARGED,
        Shift::PAID_BUSINESS_ONLY,
        Shift::PAID_CAREGIVER_ONLY,
        Shift::PAID_BUSINESS_ONLY_NOT_CHARGED,
        Shift::PAID_CAREGIVER_ONLY_NOT_CHARGED,
        Shift::PAID,
    ];

    public function __construct(Shift $shift)
    {
        // Always load a fresh instance from the database to avoid outdated info
        $this->shift = $shift->fresh();
    }

    public function __toString()
    {
        return $this->status();
    }

    /**
     * Return the status string
     *
     * @return string
     */
    public function status()
    {
        return $this->shift->status;
    }

    /**
     * Update the shift status
     *
     * @param string $newStatus
     * @return bool
     */
    public function update($newStatus)
    {
        return $this->shift->update(['status' => $newStatus]);
    }

    ///////////////////////////////////////////
    /// Static Methods
    ///////////////////////////////////////////

    public static function getReadOnlyStatuses()
    {
        return [
            Shift::WAITING_FOR_PAYOUT,
            Shift::PAID_NOT_CHARGED,
            Shift::PAID_BUSINESS_ONLY,
            Shift::PAID_CAREGIVER_ONLY,
            Shift::PAID_BUSINESS_ONLY_NOT_CHARGED,
            Shift::PAID_CAREGIVER_ONLY_NOT_CHARGED,
            Shift::PAID,
        ];
    }

    public static function getPendingStatuses()
    {
        return array_diff(self::$statuses, self::getReadOnlyStatuses());
    }

    public static function getAwaitingChargeStatuses()
    {
        return [
            Shift::WAITING_FOR_CHARGE,
            Shift::PAID_NOT_CHARGED,
            Shift::PAID_BUSINESS_ONLY_NOT_CHARGED,
            Shift::PAID_CAREGIVER_ONLY_NOT_CHARGED,
        ];
    }

    public static function getAwaitingBusinessDepositStatuses()
    {
        return [
            Shift::WAITING_FOR_PAYOUT,
            Shift::PAID_CAREGIVER_ONLY,
        ];
    }

    public static function getAwaitingCaregiverDepositStatuses()
    {
        return [
            Shift::WAITING_FOR_PAYOUT,
            Shift::PAID_BUSINESS_ONLY,
        ];
    }


    ///////////////////////////////////////////
    /// Check Methods
    ///////////////////////////////////////////

    /**
     * Returns true if a shift should no longer be modified
     *
     * @return bool
     */
    public function isReadOnly()
    {
        return in_array(
            $this->status(),
            self::getReadOnlyStatuses()
        );
    }

    public function isPending()
    {
        return in_array(
            $this->status(),
            self::getPendingStatuses()
        );
    }


    public function isAwaitingCharge()
    {
        return in_array(
            $this->status(),
            self::getAwaitingChargeStatuses()
        );
    }

    public function isAwaitingBusinessDeposit()
    {
        return in_array(
            $this->status(),
            self::getAwaitingBusinessDepositStatuses()
        );
    }

    public function isAwaitingCaregiverDeposit()
    {
        return in_array(
            $this->status(),
            self::getAwaitingCaregiverDepositStatuses()
        );
    }


    ///////////////////////////////////////////
    /// Acknowledgements (Status Updates)
    ///////////////////////////////////////////

    /**
     * Acknowledge a clock out
     * @return bool
     */
    public function ackClockIn()
    {
        return $this->update(Shift::CLOCKED_IN);
    }

    /**
     * Acknowledge a clock out
     * @return bool
     */
    public function ackClockOut($verified)
    {
        if ($verified) {
            return $this->update(Shift::WAITING_FOR_AUTHORIZATION);
        }
        return $this->update(Shift::WAITING_FOR_APPROVAL);
    }

    /**
     * Acknowledge a manual approval
     *
     * @return bool
     */
    public function ackApproval()
    {
        if ($this->status() === Shift::WAITING_FOR_APPROVAL) {
            return $this->update(Shift::WAITING_FOR_AUTHORIZATION);
        }
        return false;
    }

    /**
     * Acknowledge a successful payment
     * @return bool
     */
    public function ackPayment()
    {
        switch($this->status()) {
            case Shift::PAID_NOT_CHARGED:
                return $this->update(Shift::PAID);
            default:
                return $this->update(Shift::WAITING_FOR_PAYOUT);
        }
    }

    /**
     * Acknowledge a successful business deposit
     * @return bool
     */
    public function ackBusinessDeposit()
    {
        switch($this->status()) {
            case Shift::WAITING_FOR_PAYOUT:
                return $this->update(Shift::PAID_BUSINESS_ONLY);
            case Shift::PAID_CAREGIVER_ONLY:
                return $this->update(Shift::PAID);
        }
        return false;
    }

    /**
     * Acknowledge a successful caregiver deposit
     * @return bool
     */
    public function ackCaregiverDeposit()
    {
        switch($this->status()) {
            case Shift::WAITING_FOR_PAYOUT:
                return $this->update(Shift::PAID_CAREGIVER_ONLY);
            case Shift::PAID_BUSINESS_ONLY:
                return $this->update(Shift::PAID);
        }
        return false;
    }

    /**
     * Acknowledge a returned (failed) payment
     * @return bool
     */
    public function ackReturnedPayment()
    {
        switch($this->status()) {
            case Shift::WAITING_FOR_PAYOUT:
                return $this->update(Shift::WAITING_FOR_CHARGE);
            case Shift::PAID_BUSINESS_ONLY:
                return $this->update(Shift::PAID_BUSINESS_ONLY_NOT_CHARGED);
            case Shift::PAID_CAREGIVER_ONLY:
                return $this->update(Shift::PAID_CAREGIVER_ONLY_NOT_CHARGED);
            case Shift::PAID:
                return $this->update(Shift::PAID_NOT_CHARGED);
        }
        return false;
    }

    /**
     * Acknowledge a returned (failed) business deposit
     * @return bool
     */
    public function ackReturnedBusinessDeposit()
    {
        switch($this->status()) {
            case Shift::PAID_BUSINESS_ONLY:
                return $this->update(Shift::WAITING_FOR_PAYOUT);
            case Shift::PAID:
                return $this->update(Shift::PAID_CAREGIVER_ONLY);
        }
        return false;
    }

    /**
     * Acknowledge a returned (failed) caregiver deposit\
     * @return bool
     */
    public function ackFailedCaregiverDeposit()
    {
        switch($this->status()) {
            case Shift::PAID_CAREGIVER_ONLY:
                return $this->update(Shift::WAITING_FOR_PAYOUT);
            case Shift::PAID:
                return $this->update(Shift::PAID_BUSINESS_ONLY);
        }
        return false;
    }
}