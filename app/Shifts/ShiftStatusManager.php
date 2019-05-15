<?php
namespace App\Shifts;

use App\Shift;
use App\ShiftStatusHistory;

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
        Shift::WAITING_FOR_CONFIRMATION,
        Shift::WAITING_FOR_AUTHORIZATION,
        Shift::WAITING_FOR_INVOICE,
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
        $this->shift = $shift;
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
    public function update($newStatus, $otherAttributes = [])
    {
        $data = $otherAttributes;
        $data['status'] = $newStatus;
        return $this->shift->update($data);
    }

    ///////////////////////////////////////////
    /// Static Methods
    ///////////////////////////////////////////

    public static function getAllStatuses()
    {
        return self::$statuses;
    }

    public static function getReadOnlyStatuses()
    {
        return [
            Shift::WAITING_FOR_INVOICE,
            Shift::WAITING_FOR_CHARGE,
            Shift::WAITING_FOR_PAYOUT,
            Shift::PAID_NOT_CHARGED,
            Shift::PAID_BUSINESS_ONLY,
            Shift::PAID_CAREGIVER_ONLY,
            Shift::PAID_BUSINESS_ONLY_NOT_CHARGED,
            Shift::PAID_CAREGIVER_ONLY_NOT_CHARGED,
            Shift::PAID,
        ];
    }
    
    public static function getUnsettledStatuses()
    {
        return [
            Shift::WAITING_FOR_CONFIRMATION,
            Shift::WAITING_FOR_AUTHORIZATION,
            Shift::WAITING_FOR_INVOICE,
            Shift::WAITING_FOR_CHARGE,
            Shift::WAITING_FOR_PAYOUT,
            Shift::PAID_NOT_CHARGED,
            Shift::PAID_BUSINESS_ONLY,
            Shift::PAID_CAREGIVER_ONLY,
            Shift::PAID_BUSINESS_ONLY_NOT_CHARGED,
            Shift::PAID_CAREGIVER_ONLY_NOT_CHARGED,
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

    public static function getUnconfirmedStatuses()
    {
        return [ Shift::WAITING_FOR_CONFIRMATION ] + self::getClockedInStatuses();
    }

    public static function getConfirmedStatuses($pendingOnly = false)
    {
        $statuses = array_diff(self::$statuses, self::getUnconfirmedStatuses());
        if ($pendingOnly) {
            $statuses = array_intersect($statuses, self::getPendingStatuses());
        }
        return $statuses;
    }

    public static function getClockedInStatuses()
    {
        return [
            null,
            Shift::CLOCKED_IN,
        ];
    }

    ///////////////////////////////////////////
    /// Check Methods
    ///////////////////////////////////////////

    /**
     * Returns true if a shift should no longer be modified
     * @return bool
     */
    public function isReadOnly()
    {
        return in_array(
            $this->status(),
            self::getReadOnlyStatuses()
        );
    }

    /**
     * Returns true if a shift is pending (has not been charged)
     * @return bool
     */
    public function isPending()
    {
        return in_array(
            $this->status(),
            self::getPendingStatuses()
        );
    }

    /**
     * Returns true if a shift is ready to be charged
     * @return bool
     */
    public function isAwaitingCharge()
    {
        return in_array(
            $this->status(),
            self::getAwaitingChargeStatuses()
        );
    }

    /**
     * Returns true if a shift is ready for a business deposit
     * @return bool
     */
    public function isAwaitingBusinessDeposit()
    {
        return in_array(
            $this->status(),
            self::getAwaitingBusinessDepositStatuses()
        );
    }

    /**
     * Returns true if a shift is ready for a caregiver deposit
     * @return bool
     */
    public function isAwaitingCaregiverDeposit()
    {
        return in_array(
            $this->status(),
            self::getAwaitingCaregiverDepositStatuses()
        );
    }

    /**
     * Returns true if a shift is confirmed (was clocked in or has been recognized by the registry)
     * @return bool
     */
    public function isConfirmed()
    {
        return !in_array(
            $this->status(),
            self::getUnconfirmedStatuses()
        );
    }

    /**
     * Returns true if a shift has a clocked in status
     * @return bool
     */
    public function isClockedIn()
    {
        return in_array(
            $this->status(),
            self::getClockedInStatuses()
        );
    }


    ///////////////////////////////////////////
    /// Acknowledgements (Status Updates)
    ///////////////////////////////////////////

    /**
     * Acknowledge a clock in
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
        if (app('settings')->get($this->shift->business, 'auto_confirm') 
            || ($verified && app('settings')->get($this->shift->business, 'auto_confirm_verified_shifts'))) {
            return $this->update(Shift::WAITING_FOR_AUTHORIZATION);
        }
        return $this->update(Shift::WAITING_FOR_CONFIRMATION);
    }

    /**
     * Acknowledge a confirmation of an unconfirmed shift
     */
    public function ackConfirmation()
    {
        if ($this->status() === Shift::WAITING_FOR_CONFIRMATION) {
            return $this->update(Shift::WAITING_FOR_AUTHORIZATION);
        }
        return false;
    }

    /**
     * Revert a shift back to unconfirmed
     */
    public function unconfirm()
    {
        if ($this->isReadOnly()) return false;
        return $this->update(Shift::WAITING_FOR_CONFIRMATION);
    }

    /**
     * Acknowledge an authorization
     * @return bool
     */
    public function ackAuthorization()
    {
        if (in_array($this->status(), [Shift::WAITING_FOR_AUTHORIZATION])) {
            return $this->update(Shift::WAITING_FOR_INVOICE);
        }
        return false;
    }

    /**
     * Unauthorize a shift (revert authorization)
     * @return bool
     */
    public function unauthorize()
    {
        if ($this->status() === Shift::WAITING_FOR_INVOICE) {
            return $this->update(Shift::WAITING_FOR_AUTHORIZATION);
        }
        return false;
    }


    /**
     * Acknowledge that the shift has been fully invoiced to clients/payers
     * @return bool
     */
    public function ackClientInvoice()
    {
        switch($this->status()) {
            case Shift::WAITING_FOR_INVOICE:
                return $this->update(Shift::WAITING_FOR_CHARGE);
        }
        return false;
    }

    /**
     * Acknowledge that the shift's related invoice has been uninvoiced/deleted
     * @return bool
     */
    public function ackClientInvoiceDeleted()
    {
        switch($this->status()) {
            case Shift::WAITING_FOR_CHARGE:
                return $this->update(Shift::WAITING_FOR_INVOICE);
        }
        return false;
    }

    /**
     * Acknowledge a successful payment  (payment id deprecated)
     * @return bool
     */
    public function ackPayment($payment_id = null)
    {
        switch($this->status()) {
            case Shift::PAID_NOT_CHARGED:
                return $this->update(Shift::PAID);
            case Shift::PAID_BUSINESS_ONLY_NOT_CHARGED:
                return $this->update(Shift::PAID_BUSINESS_ONLY);
            case Shift::PAID_CAREGIVER_ONLY_NOT_CHARGED:
                return $this->update(Shift::PAID_CAREGIVER_ONLY);
            case Shift::WAITING_FOR_CHARGE:
                return $this->update(Shift::WAITING_FOR_PAYOUT);
        }
        return false;
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
     * Acknowledge a returned (failed) caregiver deposit
     * @return bool
     */
    public function ackReturnedCaregiverDeposit()
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
