<?php

namespace App\Claims\Transmitters;

use App\Claims\Exceptions\ClaimTransmissionException;
use App\Claims\Contracts\ClaimTransmitterInterface;
use App\Services\HhaExchangeService;
use App\Claims\ClaimInvoiceType;
use App\Claims\ClaimInvoiceItem;
use App\Claims\ClaimableService;
use App\Claims\ClaimInvoice;
use App\HhaFile;

class HhaClaimTransmitter extends BaseClaimTransmitter implements ClaimTransmitterInterface
{
    /**
     * Timestamp format string.
     *
     * @var string
     */
    protected $timeFormat = 'Y-m-d H:i';

    /**
     * Indicates the reason a claim should be prevented
     * from transmission.
     *
     * @param \App\Claims\ClaimInvoice $claim
     * @return null|string
     */
    public function prevent(ClaimInvoice $claim): ?string
    {
        if ($claim->getType() == ClaimInvoiceType::PAYER()) {
            return 'Transmitting Payer invoices with more than one client to HHA is not currently supported.';
        }

        return null;
    }

    /**
     * Validate a ClaimInvoice has all the required parameters to
     * be transmitted to the service.
     *
     * @param ClaimInvoice $claim
     * @return null|array
     * @throws ClaimTransmissionException
     */
    public function validateClaim(ClaimInvoice $claim): ?array
    {
        // required for all:
        // business_ein
        // client_medicaid_id
        // payer_code (if payer != private pay)
        // service_code

        // required for hha:
        // hha_username
        // hha_password
        // caregiver ID

        $errors = collect(parent::validateClaim($claim));

        if (empty($claim->business->hha_username) || empty($claim->business->getHhaPassword())) {
            $errors->push(['message' => 'Your HHA Credentials have not been setup.', 'url' => route('business-settings') . '#claims']);
        }

        return $errors->isEmpty() ? null : $errors->toArray();
    }

    /**
     * Submit the claim using the service.
     *
     * @param ClaimInvoice $claim
     * @return bool
     * @throws \App\Claims\Exceptions\ClaimTransmissionException
     */
    public function send(ClaimInvoice $claim): bool
    {
        try {
            $hha = new HhaExchangeService(
                $claim->business->hha_username,
                $claim->business->getHhaPassword(),
                $claim->business->ein
            );
        } catch (\Exception $ex) {
            app('sentry')->captureException($ex);
            throw new ClaimTransmissionException('Unable to login to HHAeXchange SFTP server.  Please check your credentials and try again.');
        }

        $filename = $hha->getFilename();
        $hha->addItems($this->getData($claim));
        if ($hha->uploadCsv($filename)) {
            // Success

            // create new HhaFile for the Claim
            $claim->hhaFiles()->create([
                'filename' => substr($filename, 0, strlen($filename) - 4),
                'status' => HhaFile::STATUS_PENDING,
            ]);

            return true;
        }

        throw new ClaimTransmissionException('An unexpected error occurred.');
    }

    /**
     * Map a ClaimInvoiceItem record to the importable
     * data for the service.
     *
     * @param ClaimInvoiceItem $item
     * @return null|array
     */
    public function mapClaimableRecord(ClaimInvoiceItem $item): ?array
    {
        if ($item->claimable_type != ClaimableService::class) {
            // HHA DOES NOT SUPPORT EXPENSES
            return null;
        }

        $claim = $item->claim;
        /** @var ClaimableService $service */
        $service = $item->claimable;

        return [
            $claim->business->ein ? str_replace('-', '', $claim->business->ein) : '', //    "Agency Tax ID", (required)
            $claim->payer_code, //    "Payer ID", (required)
            $item->client_medicaid_id ?? $claim->getClientMedicaidId(), //    "Medicaid Number", (required)
            $item->caregiver_id, //    "Caregiver Code", (required)
            $item->caregiver_first_name, //    "Caregiver First Name",
            $item->caregiver_last_name, //    "Caregiver Last Name",
            $item->caregiver_gender ? strtoupper($item->caregiver_gender) : '', //    "Caregiver Gender",
            $item->caregiver_dob ?? '', //    "Caregiver Date of Birth",
            $this->cleanSsn($item->caregiver_ssn), //    "Caregiver SSN",
            $item->id, //    "Schedule ID", (required)
            $service->service_code, //    "Procedure Code", (required)
            $service->scheduled_start_time->setTimezone($claim->getTimezone())->format($this->timeFormat), //    "Schedule Start Time", (required)
            $service->scheduled_end_time->setTimezone($claim->getTimezone())->format($this->timeFormat), //    "Schedule End Time", (required)
            $service->visit_start_time->setTimezone($claim->getTimezone())->format($this->timeFormat), //    "Visit Start Time", (required)
            $service->visit_end_time->setTimezone($claim->getTimezone())->format($this->timeFormat), //    "Visit End Time", (required)
            $service->getHasEvv() ? $service->evv_start_time->setTimezone($claim->getTimezone())->format($this->timeFormat) : '', //    "EVV Start Time",
            $service->getHasEvv() ? $service->evv_end_time->setTimezone($claim->getTimezone())->format($this->timeFormat) : '', //    "EVV End Time",
            str_limit($service->getAddress(), 100), //    "Service Location",
            $this->mapActivities($service->activities), //    "Duties",
            $service->checked_in_number, //    "Clock-In Phone Number",
            $service->checked_in_latitude, //    "Clock-In Latitude",
            $service->checked_in_longitude, //    "Clock-In Longitude",
            '', //    "Clock-In EVV Other Info",
            $service->checked_out_number, //    "Clock-Out Phone Number",
            $service->checked_out_latitude, //    "Clock-Out Latitude",
            $service->checked_out_longitude, //    "Clock-Out Longitude",
            '', //    "Clock-Out EVV Other Info",
            $claim->name, //    "Invoice Number",
            // TODO: implement reason codes:
            $service->getHasEvv() ? '' : '910', //    "Visit Edit Reason Code",
            $service->getHasEvv() ? '' : '14', //    "Visit Edit Action Taken",
            $service->caregiver_comments, //    "Notes",
            'N', //    "Is Deletion",
            $item->id, //    "Invoice Line Item ID",
            'N', //    "Missed Visit",
            // TODO: implement reason codes?
            '', //    "Missed Visit Reason Code",
            '', //    "Missed Visit Action Taken Code",
            $service->getHasEvv() ? '' : 'Y', //    "Timesheet Required",
            $service->getHasEvv() ? '' : 'Y', //    "Timesheet Approved",
            $service->shift_id, //    "User Field 1",
            $item->client_id, //    "User Field 2",
            $item->caregiver_id, //    "User Field 3",
            '', //    "User Field 4",
            '', //    "User Field 5",
        ];
    }

    /**
     * Map claimable service activities to their corresponding duties codes.
     *
     * @param null|string $activities
     * @return string
     */
    public function mapActivities(?string $activities): string
    {
        // TODO: re-work this to read from hha_duty_code_id field in DB: https://jtrsolutions.atlassian.net/browse/ALLY-1151
        if (empty($activities)) {
            return '';
        }

        // Check here for duties codes: https://s3.amazonaws.com/hhaxsupport/SupportDocs/EDI+Guides/EDI+Code+Table+Guides/EDI+Code+Table+Guide_PACHC.pdf
        $duties = collect(explode(',', $activities))->map(function ($code) {
            switch (trim($code)) {
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
                    // return a default code for any custom (or missing) activity
                    return '201';
            }
        });

        return implode('|', $duties->toArray());
    }

    /**
     * Format the SSN.
     *
     * @param string|null $ssn
     * @return string
     */
    private function cleanSsn(?string $ssn): string
    {
        if (empty($ssn)) {
            return '';
        }

        if (strpos($ssn, '-') >= 0) {
            $ssn = str_replace('-', '', $ssn);
        }

        if (strpos($ssn, '*') >= 0) {
            $ssn = str_replace('*', '0', $ssn);
        }

        return $ssn[0] . $ssn[1] . $ssn[2] . '-' . $ssn[3] . $ssn[4] . '-' . $ssn[5] . $ssn[6] . $ssn[7] . $ssn[8];
    }

    /**
     * Check transmitter is in test mode.
     *
     * @param ClaimInvoice $claim
     * @return bool
     */
    public function isTestMode(ClaimInvoice $claim): bool
    {
        return $claim->business->hha_username == "test";
    }

    /**
     * Create and return the Claim path of the file that would be transmitted.
     *
     * @param ClaimInvoice $claim
     * @return null|string
     * @throws \Exception
     */
    public function test(ClaimInvoice $claim): ?string
    {
        $hha = new HhaExchangeService(
            $claim->business->hha_username,
            $claim->business->getHhaPassword(),
            $claim->business->ein
        );

        $hha->addItems($this->getData($claim));
        $csv = $hha->getCsv();
        $filename = 'test-claims/hha_' . md5($claim->id . uniqid() . microtime()) . '.csv';
        \Storage::disk('public')->put($filename, $csv);
        return "/storage/$filename";
    }
}