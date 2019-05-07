<?php
namespace App\Billing\Claims;

use App\Billing\Claim;
use App\Billing\ClientInvoice;
use App\Billing\Contracts\ClaimTransmitterInterface;
use App\Billing\Exceptions\ClaimTransmissionException;
use App\Services\HhaExchangeService;
use App\Shift;
use Illuminate\Support\Collection;

class TellusClaimTransmitter extends BaseClaimTransmitter implements ClaimTransmitterInterface
{
    /**
     * Validate an invoice has all the required parameters to
     * be transmitted as a claim.
     *
     * @param \App\Billing\ClientInvoice $invoice
     * @return bool
     * @throws \App\Billing\Exceptions\ClaimTransmissionException
     */
    public function validateInvoice(ClientInvoice $invoice) : bool
    {
        // TODO: remove this exception and handle tellus credential validator
        throw new ClaimTransmissionException('Tellus is not yet integrated.');
//        return parent::validateInvoice($invoice);
    }

    /**
     * Submit the claim using the service.
     *
     * @param Claim $claim
     * @return bool
     * @throws \App\Billing\Exceptions\ClaimTransmissionException
     */
    public function send(Claim $claim) : bool
    {
        throw new ClaimTransmissionException('Tellus is not yet integrated.');
    }

    /**
     * Map a claim's shift into importable data for the service.
     *
     * @param \App\Billing\Claim $claim
     * @param \App\Shift $shift
     * @return array
     */
    public function mapShiftRecord(Claim $claim, Shift $shift) : array
    {
        return [];
    }

    /**
     * Map collection of activities to their corresponding duties codes.
     *
     * @param \Illuminate\Support\Collection $activities
     * @return string
     */
    public function mapActivities(Collection $activities) : string
    {
        // TODO: re-work this to read from tellus_duty_code_id field in DB: https://jtrsolutions.atlassian.net/browse/ALLY-1151
        if ($activities->isEmpty()) {
            return '';
        }

        $activities = $activities->map(function ($activity) {
            if (! empty($activity->business_id)) {
                // return a default code for any custom activity
                return '';
            }

            switch ($activity->code) {
                case '001': // Bathing - Shower
                    return '';
                case '002': // Bathing - Bed
                    return '';
                case '003': // Dressing
                    return '';
                case '005': // Hygiene - Hair Care
                    return '';
                case '006': // Shave
                    return '';
                case '004': // Hygiene - Mouth Care
                    return '';
                case '007': // Incontinence Care
                    return '';
                case '021': // Medication Reminders
                    return '';
                case '020': // Turning & Repositioning
                    return '';
                case '022': // Safety Supervision
                    return '';
                case '008': // Toileting
                    return '';
                case '009': // Catheter Care
                    return '';
                case '023': // Meal Preparation
                    return '';
                case '025': // Homemaker Services
                    return '';
                case '026': // Transportation
                    return '';
                case '024': // Feeding
                    return '';
                case '010': // Ostomy Care
                    return '';
                case '027': // Ambulation
                    return '';
                case '011': // Companion Care
                    return '';
                default:
                    return '';
            }
        });

        return implode('|', $activities->toArray());
    }
}