<?php
namespace App\Billing;

use App\Billing\Exceptions\ClaimTransmissionException;
use App\Billing\Invoiceable\ShiftService;
use App\Services\HhaExchangeManager;
use App\Shift;
use Illuminate\Support\Collection;

class ClaimTransmitter
{
    protected $claimService;

    /**
     * ClaimTransmitter Constructor.
     *
     * @param \App\Billing\ClaimService $claimService
     */
    public function __construct(ClaimService $claimService)
    {
        $this->claimService = $claimService;
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

        switch ($this->claimService) {
            case ClaimService::HHA():
                if (empty($invoice->client->business->hha_username) || empty($invoice->client->business->getHhaPassword())) {
                    throw new ClaimTransmissionException('You cannot submit a claim because you do not have your HHAeXchange credentials set.  You can edit this information under Settings > General > Claims, or contact Ally for assistance.');
                }
                break;
            case ClaimService::TELLUS():
                throw new ClaimTransmissionException('Tellus is not yet integrated.');
                break;
        }

        $invoiceableShiftIds = $invoice->items->where('invoiceable_type', 'shifts')
            ->pluck('invoiceable_id');

        $invoiceableServiceIds = $invoice->items->where('invoiceable_type', 'shift_services')
            ->pluck('invoiceable_id');

        $shiftCount = Shift::whereIn('id', $invoiceableShiftIds)->count();

        // check for split shifts by checking dupe invoiceable id
        $splitShiftsCount = ClientInvoiceItem::where('invoiceable_type', 'shifts')
            ->whereIn('invoiceable_id', $invoiceableShiftIds)
            ->select('invoiceable_id')
            ->groupBy('invoiceable_id')
            ->havingRaw('count(invoiceable_id) > 1')
            ->count();

        if ($splitShiftsCount > 0) {
            throw new ClaimTransmissionException('You cannot create a claim because of split shifts, you must contact Ally.');
        }

        if ($invoiceableServiceIds->count() > 0) {
            if ($this->checkForSplitServiceBreakoutShifts($invoice, $invoiceableServiceIds)) {
                throw new ClaimTransmissionException('You cannot create a claim because of split shifts, you must contact Ally.');
            }

            $shiftCount += ShiftService::whereIn('id', $invoiceableServiceIds) // add shifts via shift_services
                ->get()
                ->unique('shift_id')
                ->count();
        }

        if ($shiftCount === 0) {
            throw new ClaimTransmissionException('You cannot create a claim because there are no shifts attached to this invoice.');
        }

        return true;
    }

    /**
     * Check if the services of a shift are split on to
     * multiple invoices.
     *
     * @param ClientInvoice $invoice
     * @param Collection $shiftServiceIds
     * @return bool
     */
    public function checkForSplitServiceBreakoutShifts(ClientInvoice $invoice, Collection $shiftServiceIds) : bool
    {
        // check if a shift service has been split
        $count = ClientInvoiceItem::where('invoiceable_type', 'shift_services')
            ->whereIn('invoiceable_id', $shiftServiceIds)
            ->select('invoiceable_id')
            ->groupBy('invoiceable_id')
            ->havingRaw('count(invoiceable_id) > 1')
            ->count();

        if ($count > 0) {
            return true;
        }

        $relatedIds = collect([]);
        $services = ShiftService::where('id', $shiftServiceIds)->get();
        foreach ($services as $service) {
            $relatedIds = $relatedIds->merge($service->shift->services->pluck('id'));
        }

        // check for an invoice item that belongs to a different invoice
        // that contains one of the related service IDs.
        $count = ClientInvoiceItem::where('invoiceable_type', 'shift_services')
            ->whereIn('invoiceable_id', $relatedIds->unique())
            ->where('invoice_id', '<>', $invoice->id)
            ->count();

        if ($count > 0) {
            return true;
        }

        return false;
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
        switch ($this->claimService) {
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
     * Map collection of activities to their corresponding duties codes.
     *
     * @param \Illuminate\Support\Collection $activities
     * @return string
     */
    public function mapActivitiesToDuties(Collection $activities) : string
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

        $serviceShiftIds = ShiftService::whereIn('id', $claim->invoice->items->where('invoiceable_type', 'shift_services')->pluck('invoiceable_id'))
            ->get()
            ->unique('shift_id');

        $shifts = $shifts->merge(Shift::whereIn('id', $serviceShiftIds)->get());

        foreach ($shifts as $shift) {
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
                'S5135U2', //    "Procedure Code",
                $shift->checked_in_time->format($timeFormat), //    "Schedule Start Time",
                $shift->checked_out_time->format($timeFormat), //    "Schedule End Time",
                $shift->checked_in_time->format($timeFormat), //    "Visit Start Time",
                $shift->checked_out_time->format($timeFormat), //    "Visit End Time",
                $shift->checked_in_time->format($timeFormat), //    "EVV Start Time",
                $shift->checked_out_time->format($timeFormat), //    "EVV End Time",
                optional($shift->client->evvAddress)->full_address, //    "Service Location",
                $this->mapActivitiesToDuties($shift->activities), //    "Duties",
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