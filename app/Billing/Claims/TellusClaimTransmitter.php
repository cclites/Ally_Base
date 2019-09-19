<?php
namespace App\Billing\Claims;

use App\Address;
use App\Billing\Claim;
use App\Billing\ClientInvoice;
use App\Billing\Contracts\ClaimTransmitterInterface;
use App\Billing\Exceptions\ClaimTransmissionException;
use App\Billing\Invoiceable\ShiftService;
use App\Business;
use App\Client;
use App\Services\TellusApiException;
use App\Services\TellusService;
use App\Shift;
use App\TellusTypecode;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class TellusClaimTransmitter extends BaseClaimTransmitter implements ClaimTransmitterInterface
{
    /**
     * Timestamp format string.
     *
     * @var string
     */
    protected $timeFormat = 'm/d/Y H:i:s';

    /**
     * Validate an invoice has all the required parameters to
     * be transmitted as a claim.
     *
     * @param \App\Billing\ClientInvoice $invoice
     * @return null|array
     * @throws \App\Billing\Exceptions\ClaimTransmissionException
     */
    public function validateInvoice(ClientInvoice $invoice) : ?array
    {
        $errors = parent::validateInvoice($invoice);

        if (empty($invoice->client->business->tellus_username) || empty($invoice->client->business->getTellusPassword())) {
            array_push($errors['credentials'], 'tellus_username');
            array_push($errors['credentials'], 'tellus_password');
        }

        if (empty($invoice->client->business->medicaid_id)) {
            array_push($errors['business'], 'medicaid_id');
        }

        if (empty($invoice->client->business->medicaid_npi_number)) {
            array_push($errors['business'], 'medicaid_npi_number');
        }

        if (empty($invoice->client->business->medicaid_npi_taxonomy)) {
            array_push($errors['business'], 'medicaid_npi_taxonomy');
        }

        if (empty($invoice->client->business->zip)) {
            array_push($errors['business'], 'zip');
        }

        if (empty($invoice->client->date_of_birth)) {
            array_push($errors['client'], 'date_of_birth');
        }

        if (empty($invoice->client->medicaid_diagnosis_codes)) {
            array_push($errors['client'], 'medicaid_diagnosis_codes');
        }

        if (empty($invoice->getPayerCode())) {
            if (optional($invoice->clientPayer)->isPrivatePay()) {
                array_push($errors['client'], 'medicaid_payer_id');
            } else {
                array_push($errors['payer'], 'payer_code');
            }
        }

        if (empty($invoice->getPlanCode())) {
            if (optional($invoice->clientPayer)->isPrivatePay()) {
                array_push($errors['client'], 'medicaid_plan_id');
            } else {
                array_push($errors['payer'], 'plan_code');
            }
        }

        if (collect($errors)->flatten(1)->isEmpty()) {
            return null;
        }

        return $errors;
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
        $tellus = new TellusService(
            $claim->invoice->client->business->tellus_username,
            $claim->invoice->client->business->getTellusPassword(),
            config('services.tellus.endpoint')
        );

        try {

            if ($tellus->submitClaim($this->getData($claim))) {
                // Success
                dd( 'hey' );

                return true;
            }
        } catch (TellusApiException $ex) {

            throw new ClaimTransmissionException('Error connecting to Tellus: ' . $ex->getMessage());
        } catch (\Exception $ex) {

            \Log::info($ex->getMessage());
            app('sentry')->captureException($ex);
            throw new ClaimTransmissionException( $ex );
            // throw new ClaimTransmissionException('An error occurred while trying to submit data to the Tellus API server.  Please try again or contact Ally.');
        }

        throw new ClaimTransmissionException('An unexpected error occurred.');
    }

    /**
     * Check transmitter is in test mode.
     *
     * @param Claim $claim
     * @return bool
     */
    public function isTestMode(Claim $claim) : bool
    {
        return $claim->invoice->client->business->tellus_username == "test";
    }

    /**
     * Create and return the Claim path of the file that would be transmitted.
     *
     * @param Claim $claim
     * @return null|string
     * @throws \Exception
     */
    public function test(Claim $claim) : ?string
    {
        $tellus = new TellusService(
            $claim->invoice->client->business->tellus_username,
            $claim->invoice->client->business->getTellusPassword(),
            config('services.tellus.endpoint')
        );

        $xml = $tellus->convertArrayToXML($this->getData($claim));
        $filename = 'test-claims/tellus_' . md5($claim->id . uniqid() . microtime()) . '.xml';
        \Storage::disk('public')->put($filename, $xml);
        return "/storage/$filename";
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
        /** @var \App\Business $business */
        $business = $shift->business;

        /** @var \App\Client $client */
        $client = $shift->client;

        /** @var \App\Caregiver $caregiver */
        $caregiver = $shift->caregiver;

        /** @var \App\Address $address */
        $address = $shift->address ?? $shift->client->evvAddress;

        /** @var \Packages\GMaps\GeocodeCoordinates|false */
        $geocode = optional($address)->getGeocode();

        $diagnosisCodes = $this->getDiagnosisCodes($client);

        /** @var ClientInvoice $clientInvoice */
        $clientInvoice = $claim->invoice;

        return [

            'SourceSystem'           => $this->tcLookup('SourceSystem', 'ALLY'),
            'Jurisdiction'           => $this->tcLookup('Jurisdiction', $address->state ?? 'None'),
            'Payer'                  => $this->tcLookup('Payer', $clientInvoice->getPayerCode() ?? 'UHTH'),
            'Plan'                   => $this->tcLookup('Plan', $clientInvoice->getPlanCode() ?? 'FMSP'),
            'Program'                => $this->tcLookup('Program', 'PACE'), // N/A
            'DeliverySystem'         => $this->tcLookup( 'DeliverySystem', 'FFFS'), // 'ALLY',
            'ProviderName'           => $business->name,
            'ProviderMedicaidId'     => $business->medicaid_id,
            'ProviderNPI'            => $business->medicaid_npi_number,
            'ProviderNPITaxonomy'    => $business->medicaid_npi_taxonomy,
            'ProviderNPIZipCode'     => $business->zip,
            'ProviderEin'            => $business->ein,
            'CaregiverFirstName'     => $caregiver->firstname,
            'CaregiverLastName'      => $caregiver->lastname,
            'CaregiverLicenseNumber' => $caregiver->medicaid_id,
            'RecipientMedicaidId'    => $client->medicaid_id,
            'RecipientMemberId'      => '', // N/A
            'RecipientFirstName'     => $client->firstname,
            'RecipientLastName'      => $client->lastname,
            'RecipientDob'           => Carbon::parse($client->date_of_birth)->format('m/d/Y'),
            'ServiceAddress1'        => $address->address1,
            'ServiceAddress2'        => $address->address2,
            'ServiceCity'            => $address->city,
            'ServiceState'           => $address->state,
            'ServiceZip'             => $address->zip,
            'VisitId'                => $shift->id,
            'ServiceCode'            => optional($shift->service)->code,
            'ServiceCodeMod1'        => '', // N/A
            'ServiceCodeMod2'        => '', // N/A
            'DiagnosisCode1'         => $diagnosisCodes[0],
            'DiagnosisCode2'         => $diagnosisCodes[1],
            'DiagnosisCode3'         => $diagnosisCodes[2],
            'DiagnosisCode4'         => $diagnosisCodes[3],
            'StartVerificationType'  => $this->tcLookup( 'StartVerificationType', $this->getVerificationType($shift->checked_in_method) ),
            'EndVerificationType'    => $this->tcLookup( 'EndVerificationType', $this->getVerificationType($shift->checked_out_method) ),
            'ScheduledStartDateTime' => $this->getScheduledStartTime($shift),
            'ScheduledEndDateTime'   => $this->getScheduledEndTime($shift),
            'ScheduledLatitude'      => optional($geocode)->latitude ?? '',
            'ScheduledLongitude'     => optional($geocode)->longitude ?? '',
            'ActualStartDateTime'    => $shift->checked_in_time->format($this->timeFormat),
            'ActualEndDateTime'      => $shift->checked_out_time->format($this->timeFormat),
            'ActualStartLatitude'    => optional($geocode)->latitude ?? '', // these are not right, correct values TODO
            'ActualStartLongitude'   => optional($geocode)->longitude ?? '', // these are not right, correct values TODO
            'ActualEndLatitude'      => optional($geocode)->latitude ?? '', // these are not right, correct values TODO
            'ActualEndLongitude'     => optional($geocode)->longitude ?? '', // these are not right, correct values TODO
            'UserField1'             => '', // N/A
            'UserField2'             => '', // N/A
            'UserField3'             => '', // N/A
            'ReasonCode1'            => $this->tcLookup( 'ReasonCode', '105' ), // N/A
            // 'ReasonCode2' => '', // N/A
            // 'ReasonCode3' => '', // N/A
            // 'ReasonCode4' => '', // N/A
            'TimeZone'               => $this->tcLookup( 'TimeZone', $this->getBusinessTimezone($business) ),
            'VisitNote'              => $shift->caregiver_comments ?? '',
            'EndAddress1'            => $address->address1,
            'EndAddress2'            => $address->address2,
            'EndCity'                => $address->city,
            'EndState'               => $address->state,
            'EndZip'                 => $address->zip,
            'VisitStatus'            => $this->tcLookup( 'VisitStatus', 'COMP' ),
            'MissedVisitReason'      => $this->tcLookup( 'MissedVisitReason', 'PCAN' ), // N/A
            'MissedVisitActionTaken' => $this->tcLookup( 'MissedVisitActionTaken', 'SCHS' ), // N/A
            'InvoiceUnits'           => '', // TODO: find out how to fill this
            'InvoiceAmount'          => '13.37', // TODO: find out how to fill this
            'ScheduledEndLatitude'   => optional($geocode)->latitude ?? '', // these are not right, correct values TODO
            'ScheduledEndLongitude'  => optional($geocode)->longitude ?? '', // these are not right, correct values TODO
            'PaidAmount'             => '13.37', // N/A
            'CareDirectionType'      => $this->tcLookup( 'CareDirectionType', 'PROV' ), // N/A

            'Tasks'                  => '',
            'Task'                   => $this->tcLookup( 'Task', 'MBED' ),
        ];
    }

    /**
     * Lookup typecode data from the database.
     *
     * @param string $category
     * @param string $textCode
     * @return array|string
     * @throws TellusApiException
     */
    public function tcLookup(string $category, string $textCode)
    {
        $typeCode = TellusTypecode::where('category', $category)
            ->where('text_code', 'LIKE', $textCode)
            ->first();

        if (empty($typeCode)) {
            throw new TellusApiException("Invalid text code value \"$textCode\" for $category.");
        }

        return [$typeCode->description, $typeCode->code];
    }

    /**
     * Map a claim's shift service into importable data for the service.
     *
     * @param \App\Billing\Claim $claim
     * @param ShiftService $shiftService
     * @return array
     */
    public function mapServiceRecord(Claim $claim, ShiftService $shiftService) : array
    {
        // Get the base data from the related shift.
        $data = $this->mapShiftRecord($claim, $shiftService->shift);
        $data['ServiceCode'] = optional($shiftService->service)->code;
        return $data;
    }

    /**
     * Convert the shifts check in or out method into
     * a valid VerificationType.
     *
     * @param string|null $checkedInOutMethod
     * @return string
     */
    protected function getVerificationType(?string $checkedInOutMethod)
    {
        switch ($checkedInOutMethod) {
            case Shift::METHOD_TELEPHONY:
                return 'IVR';
            case Shift::METHOD_GEOLOCATION:
                return 'GPS';
            default:
                return 'NON';
        }
    }

    /**
     * Get the scheduled start time if a schedule exists, otherwise
     * just return the shift start time.
     *
     * @param \App\Shift $shift
     * @return string
     */
    protected function getScheduledStartTime(Shift $shift) : string
    {
        if ($schedule = $shift->schedule) {
            return $schedule->getStartDateTime()->format($this->timeFormat);
        }

        return $shift->checked_in_time->format($this->timeFormat);
    }

    /**
     * Get the scheduled end time if a schedule exists, otherwise
     * just return the shift end time.
     *
     * @param \App\Shift $shift
     * @return string
     */
    protected function getScheduledEndTime(Shift $shift)
    {
        if ($schedule = $shift->schedule) {
            return $schedule->getEndDateTime()->format($this->timeFormat);
        }

        return $shift->checked_out_time->format($this->timeFormat);
    }

    /**
     * Split client diagnosis codes from databased array.
     *
     * @param Client $client
     * @return array
     */
    protected function getDiagnosisCodes(Client $client) : array
    {
        return array_pad(
            array_map('trim', explode(',', $client->medicaid_diagnosis_codes)),
            4,
            ''
        );
    }

    /**
     * Convert the business timezone into the corresponding code.
     *
     * @param Business $business
     * @return string
     */
    protected function getBusinessTimezone(Business $business) : string
    {
        switch ($business->timezone) {
            case 'America/Chicago':
                return 'CHIC';
            case 'America/Denver':
                return 'DENV';
            case 'America/Phoenix':
                return 'PHOE';
            case 'America/Los_Angeles':
                return 'LANG';
            case 'America/New_York':
            default:
                return 'NEWY';
        }
    }

    /**
     * Map collection of activities to their corresponding duties codes.
     *
     * @param \Illuminate\Support\Collection $activities
     * @return string
     */
    public function mapActivities(Collection $activities) : string
    {
        // TODO: re-work this to read from hcpcs_procedure_code field in DB: https://jtrsolutions.atlassian.net/browse/ALLY-1151
        if ($activities->isEmpty()) {
            return '';
        }

        $activities = $activities->map(function ($activity) {
            if (! empty($activity->business_id)) {
                // return a default code for any custom activity
                return 'S9122';
            }

            switch ($activity->code) {
                case '001': // Bathing - Shower
                    return 'S5199';
                case '002': // Bathing - Bed
                    return 'S5199';
                case '003': // Dressing
                    return 'S5199';
                case '005': // Hygiene - Hair Care
                    return 'S5199';
                case '006': // Shave
                    return 'S5199';
                case '004': // Hygiene - Mouth Care
                    return 'S5199';
                case '007': // Incontinence Care
                    return 'S5131';
                case '021': // Medication Reminders
                    return 'S5185';
                case '020': // Turning & Repositioning
                    return 'S5131';
                case '022': // Safety Supervision
                    return 'S5131';
                case '008': // Toileting
                    return 'S5199';
                case '009': // Catheter Care
                    return 'C1729';
                case '023': // Meal Preparation
                    return 'S5131';
                case '025': // Homemaker Services
                    return 'S5131';
                case '026': // Transportation
                    return 'S5131';
                case '024': // Feeding
                    return 'S5131';
                case '010': // Ostomy Care
                    return 'S5199';
                case '027': // Ambulation
                    return 'S5131';
                case '011': // Companion Care
                    return 'S5136';
                case '028': // Wound Care
                    return 'S9097';
                case '029': // Respite Care (Skilled Nursing)
                    return 'S9125';
                case '030': // Respite Care (General)
                    return 'S5151';
                default:
                    return 'S9122';
            }
        });

        return implode('|', $activities->toArray());
    }
}