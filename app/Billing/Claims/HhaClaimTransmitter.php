<?php

namespace App\Billing\Claims;

use App\Billing\Claim;
use App\Billing\ClientInvoice;
use App\Billing\Contracts\ClaimTransmitterInterface;
use App\Billing\Exceptions\ClaimTransmissionException;
use App\Services\HhaExchangeService;
use App\Shift;
use Illuminate\Support\Collection;

class HhaClaimTransmitter extends BaseClaimTransmitter implements ClaimTransmitterInterface
{
    /**
     * Validate an invoice has all the required parameters to
     * be transmitted as a claim.
     *
     * @param \App\Billing\ClientInvoice $invoice
     * @return bool
     * @throws \App\Billing\Exceptions\ClaimTransmissionException
     */
    public function validateInvoice(ClientInvoice $invoice): bool
    {
        if (empty($invoice->client->business->hha_username) || empty($invoice->client->business->getHhaPassword())) {
            throw new ClaimTransmissionException('You cannot submit a claim because you do not have your HHAeXchange credentials set.  You can edit this information under Settings > General > Claims, or contact Ally for assistance.');
        }

        if (empty(optional($invoice->clientPayer)->payer->getPayerCode())) {
            throw new ClaimTransmissionException('You cannot submit a claim because there is not MCO/Payer Identifier set for the Payer of this invoice.  You can edit this information under Billing > Payers, or contact Ally for assistance.');
        }

        return parent::validateInvoice($invoice);
    }

    /**
     * Submit the claim using the service.
     *
     * @param Claim $claim
     * @return bool
     * @throws \App\Billing\Exceptions\ClaimTransmissionException
     */
    public function send(Claim $claim): bool
    {
        try {
            $hha = new HhaExchangeService(
                $claim->invoice->client->business->hha_username,
                $claim->invoice->client->business->getHhaPassword(),
                $claim->invoice->client->business->ein
            );
        } catch (\Exception $ex) {
            app('sentry')->captureException($ex);
            throw new ClaimTransmissionException('Unable to login to HHAeXchange SFTP server.  Please check your credentials and try again.');
        }

        $hha->addItems($this->getData($claim));
        if ($hha->uploadCsv()) {
            // Success
            return true;
        }

        throw new ClaimTransmissionException('An unexpected error occurred.');
    }

    /**
     * Map a claim's shift into importable data for the service.
     *
     * @param \App\Billing\Claim $claim
     * @param \App\Shift $shift
     * @return array
     */
    public function mapShiftRecord(Claim $claim, Shift $shift): array
    {
        $timeFormat = 'Y-m-d H:i';

        return [
            $claim->invoice->client->business->ein ? str_replace('-', '', $claim->invoice->client->business->ein) : '', //    "Agency Tax ID",
            optional($claim->invoice->clientPayer)->payer->getPayerCode(), //    "Payer ID",
            $claim->invoice->client->medicaid_id, //    "Medicaid Number",
            $shift->caregiver_id, //    "Caregiver Code",
            $shift->caregiver->firstname, //    "Caregiver First Name",
            $shift->caregiver->lastname, //    "Caregiver Last Name",
            $shift->caregiver->gender ? strtoupper($shift->caregiver->gender) : '', //    "Caregiver Gender",
            $shift->caregiver->date_of_birth ?? '', //    "Caregiver Date of Birth",
            $shift->caregiver->ssn, //    "Caregiver SSN",
            $shift->id, //    "Schedule ID",
            'S5135U2', //    "Procedure Code",
            $shift->checked_in_time->format($timeFormat), //    "Schedule Start Time",
            $shift->checked_out_time->format($timeFormat), //    "Schedule End Time",
            $shift->checked_in_time->format($timeFormat), //    "Visit Start Time",
            $shift->checked_out_time->format($timeFormat), //    "Visit End Time",
            $shift->checked_in_time->format($timeFormat), //    "EVV Start Time",
            $shift->checked_out_time->format($timeFormat), //    "EVV End Time",
            optional($shift->client->evvAddress)->full_address, //    "Service Location",
            $this->mapActivities($shift->activities), //    "Duties",
            $shift->checked_in_number, //    "Clock-In Phone Number",
            $shift->checked_in_latitude, //    "Clock-In Latitude",
            $shift->checked_in_longitude, //    "Clock-In Longitude",
            '', //    "Clock-In EVV Other Info",
            $shift->checked_out_number, //    "Clock-Out Phone Number",
            $shift->checked_out_latitude, //    "Clock-Out Latitude",
            $shift->checked_out_longitude, //    "Clock-Out Longitude",
            '', //    "Clock-Out EVV Other Info",
            $claim->client_invoice_id, //    "Invoice Number",
            '', //    "Visit Edit Reason Code",
            '', //    "Visit Edit Action Taken",
            '', //    "Notes",
            'N', //    "Is Deletion",
            '', //    "Invoice Line Item ID",
            'N', //    "Missed Visit",
            '', //    "Missed Visit Reason Code",
            '', //    "Missed Visit Action Taken Code",
            '', //    "Timesheet Required",
            '', //    "Timesheet Approved",
            '', //    "User Field 1",
            '', //    "User Field 2",
            '', //    "User Field 3",
            '', //    "User Field 4",
            '', //    "User Field 5",
        ];
    }

    /**
     * Map collection of activities to their corresponding duties codes.
     *
     * @param \Illuminate\Support\Collection $activities
     * @return string
     */
    public function mapActivities(Collection $activities): string
    {
        // TODO: re-work this to read from hha_duty_code_id field in DB: https://jtrsolutions.atlassian.net/browse/ALLY-1151
        if ($activities->isEmpty()) {
            return '';
        }

        // Check here for duties codes: https://s3.amazonaws.com/hhaxsupport/SupportDocs/EDI+Guides/EDI+Code+Table+Guides/EDI+Code+Table+Guide_PACHC.pdf
        $duties = $activities->map(function ($activity) {
            if (!empty($activity->business_id)) {
                // return a default code for any custom activity
                return '201';
            }

            switch ($activity->code) {
                case '001': // Bathing - Shower
                case '002': // Bathing - Bed
                    return '304';
                case '003': // Dressing
                    return '123';
                case '005': // Hygiene - Hair Care
                case '006': // Shave
                case '004': // Hygiene - Mouth Care
                    return '122';
                case '007': // Incontinence Care
                    return '141';
                case '021': // Medication Reminders
                    return '118';
                case '020': // Turning & Repositioning
                    return '125';
                case '022': // Safety Supervision
                    return '140';
                case '008': // Toileting
                    return '127';
                case '009': // Catheter Care
                    return '142';
                case '023': // Meal Preparation
                    return '115';
                case '025': // Homemaker Services
                    return '116';
                case '026': // Transportation
                    return '120';
                case '024': // Feeding
                    return '129';
                case '010': // Ostomy Care
                    return '143';
                case '027': // Ambulation
                    return '128';
                case '011': // Companion Care
                default:
                    return '201';
            }
        });

        return implode('|', $duties->toArray());
    }
}