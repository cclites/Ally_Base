<?php
namespace App\Billing;

use App\Billing\Exceptions\ClaimTransmissionException;
use App\Services\HhaExchangeManager;
use App\Shift;

class ClaimTransmitter
{
    protected $service;

    /**
     * ClaimTransmitter Constructor.
     *
     * @param \App\Billing\ClaimService $service
     */
    public function __construct(ClaimService $service)
    {
        $this->service = $service;
    }

    /**
     * Validate an invoice has all the required parameters to
     * be transmitted.
     *
     * @param \App\Billing\ClientInvoice $invoice
     * @return bool
     * @throws \App\Billing\Exceptions\ClaimTransmissionException
     */
    public function validateInvoice(ClientInvoice $invoice) : bool
    {
        if (empty($invoice->client->business->ein)) {
            throw new ClaimTransmissionException('You cannot submit a claim because you do not have an EIN set.  You can edit this information under Settings > General > Medicaid.');
        }

        if (empty($invoice->client->medicaid_id)) {
            throw new ClaimTransmissionException('You cannot submit a claim because the client does not have a Medicaid ID set.  You can edit this information under the Insurance & Service Auths section of the Client\'s profile.');
        }

        switch ($this->service) {
            case ClaimService::HHA():
                if (empty($invoice->client->business->hha_username) || empty($invoice->client->business->getHhaPassword())) {
                    throw new ClaimTransmissionException('You cannot submit a claim because you do not have your HHAeXchange credentials set.  You can edit this information under Settings > General > Claims, or contact Ally for assistance.');
                }
                break;
            case ClaimService::TELLUS():
                throw new ClaimTransmissionException('Tellus is not yet integrated.');
                break;
        }

        $shiftCount = Shift::whereIn('id', $invoice->items->where('invoiceable_type', 'shifts')->pluck('invoiceable_id'))->count();
        if ($shiftCount === 0) {
            throw new ClaimTransmissionException('You cannot create a claim because there are no shifts attached to this invoice.');
        }

        return true;
    }

    /**
     * Submit the claim to the selected service.
     *
     * @param Claim $claim
     * @return bool
     * @throws \App\Billing\Exceptions\ClaimTransmissionException
     */
    public function transmitClaim(Claim $claim) : bool
    {
        switch ($this->service) {
            case ClaimService::HHA():
                return $this->sendClaimToHHA($claim);
            case ClaimService::TELLUS():
                return $this->sendClaimToTellus($claim);
        }

        throw new ClaimTransmissionException('Claim service not supported.');
    }

    /**
     * Submit the claim to HHA service and return the
     * error message on failure.
     *
     * @param Claim $claim
     * @return bool
     * @throws \App\Billing\Exceptions\ClaimTransmissionException
     */
    public function sendClaimToHHA(Claim $claim) : bool
    {
        try {
            $hha = new HhaExchangeManager(
                $claim->invoice->client->business->hha_username,
                $claim->invoice->client->business->getHhaPassword(),
                $claim->invoice->client->business->ein
            );
        } catch (\Exception $ex) {
            throw new ClaimTransmissionException('Unable to login to HHAeXchange SFTP server.  Please check your credentials and try again.');
        }

        $hha->addItems($this->getHhaExchangeDataFromClaim($claim));
        if ($hha->uploadCsv()) {
            // Success
            return true;
        }

        throw new ClaimTransmissionException('An unexpected error occurred.');
    }

    /**
     * Convert claim into HHA import row data.
     *
     * @param \App\Billing\Claim $claim
     * @return array
     */
    public function getHhaExchangeDataFromClaim(Claim $claim) : array
    {
        $timeFormat = 'Y-m-d H:i:s';
        $data = [];
        $shifts = Shift::whereIn('id', $claim->invoice->items->where('invoiceable_type', 'shifts')->pluck('invoiceable_id'))
            ->get();

        foreach ($shifts as $shift) {
            $activities = $shift->activities->pluck('id')->toArray();
            $data[] = [
                $claim->invoice->client->business->ein ? str_replace('-', '', $claim->invoice->client->business->ein) : '', //    "Agency Tax ID",
                $claim->invoice->clientPayer->payer_id, //    "Payer ID",
                $claim->invoice->client->medicaid_id, //    "Medicaid Number",
                $shift->caregiver_id, //    "Caregiver Code",
                $shift->caregiver->firstname, //    "Caregiver First Name",
                $shift->caregiver->lastname, //    "Caregiver Last Name",
                $shift->caregiver->gender ? strtoupper($shift->caregiver->gender) : '', //    "Caregiver Gender",
                $shift->caregiver->date_of_birth ?? '', //    "Caregiver Date of Birth",
                $shift->caregiver->ssn, //    "Caregiver SSN",
                $shift->id, //    "Schedule ID",
                // TODO: implement Procedure Code
                'Respite Care', //    "Procedure Code",
                $shift->checked_in_time->format($timeFormat), //    "Schedule Start Time",
                $shift->checked_out_time->format($timeFormat), //    "Schedule End Time",
                $shift->checked_in_time->format($timeFormat), //    "Visit Start Time",
                $shift->checked_out_time->format($timeFormat), //    "Visit End Time",
                $shift->checked_in_time->format($timeFormat), //    "EVV Start Time",
                $shift->checked_out_time->format($timeFormat), //    "EVV End Time",
                optional($shift->client->evvAddress)->full_address, //    "Service Location",
                empty($activities) ? '' : implode('|', $activities), //    "Duties",
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

        return $data;
    }

    public function sendClaimToTellus(Claim $claim) : ?string
    {

    }
}