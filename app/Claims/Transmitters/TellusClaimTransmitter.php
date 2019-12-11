<?php

namespace App\Claims\Transmitters;

use App\Claims\Exceptions\ClaimTransmissionException;
use App\Claims\Contracts\ClaimTransmitterInterface;
use App\Services\TellusValidationException;
use Illuminate\Support\Collection;
use App\Claims\ClaimInvoiceItem;
use App\Claims\ClaimableService;
use App\Claims\ClaimInvoiceType;
use App\Services\TellusService;
use App\ClaimInvoiceTellusFile;
use App\Claims\ClaimInvoice;
use App\TellusTypecode;
use Carbon\Carbon;
use App\Business;
use App\Shift;

class TellusClaimTransmitter extends BaseClaimTransmitter implements ClaimTransmitterInterface
{
    /**
     * Timestamp format string.
     *
     * @var string
     */
    protected $timeFormat = 'm/d/Y H:i:s';

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
            return 'Transmitting Payer invoices with more than one client to Tellus is not currently supported.';
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

        // required for tellus:
        // tellus_username
        // tellus_password
        // caregiver EIN
        // business zip
        // client DOB
        // client diagnosis codes
        // plan code (see getPlanCode())
        // VisitID  shift id ?
        // client evv address

        $errors = collect(parent::validateClaim($claim));
        $editClaimUrl = route('business.claims.edit', ['claim' => $claim]);

        if (empty($claim->business->tellus_username) || empty($claim->business->getTellusPassword())) {
            throw new ClaimTransmissionException('Your Tellus Credentials have not been setup, please contact Ally.');
        }

        if (empty($claim->business->zip) || strlen($this->getBusinessZip($claim->business)) < 9) {
            $errors->push(['message' => 'Your full 9 digit Business zipcode is required.', 'url' => route('business-settings') . '#phone']);
        }

        if (empty($claim->plan_code)) {
            $errors->push(['message' => 'Payer Plan Identifier is required.', 'url' => $editClaimUrl]);
        }

        $claim->items->each(function (ClaimInvoiceItem $item) use (&$errors, $editClaimUrl) {
            if ($item->claimable_type != ClaimableService::class) {
                // Only services need to be validated.
                return;
            }

            /** @var ClaimableService $service */
            $service = $item->claimable;

            if (empty($item->caregiver_ssn)) {
                $errors->push([
                    'message' => 'Caregiver SSN/EIN is required for service ' . $service->getDisplayName(),
                    'url' => $editClaimUrl,
                ]);
            }

            if (empty($item->client_dob)) {
                $errors->push([
                    'message' => 'Client DOB is required for service ' . $service->getDisplayName(),
                    'url' => $editClaimUrl,
                ]);
            }

            if (empty($this->getDiagnosisCodes($item)[0])) {
                $errors->push([
                    'message' => 'At least one client medical diagnosis code is required for service ' . $service->getDisplayName(),
                    'url' => $editClaimUrl,
                ]);
            }

            if (empty($service->address1) || empty($service->city) || empty($service->state) || empty($service->zip)) {
                $errors->push([
                    'message' => 'A full service address is required for service ' . $service->getDisplayName(),
                    'url' => $editClaimUrl,
                ]);
            }
        });

        return $errors->isEmpty() ? null : $errors->toArray();
    }

    /**
     * Submit the claim using the service.
     *
     * @param ClaimInvoice $claim
     * @return bool
     * @throws \App\Claims\Exceptions\ClaimTransmissionException
     * @throws TellusValidationException
     */
    public function send(ClaimInvoice $claim): bool
    {
        $tellus = new TellusService(
            $claim->business->tellus_username,
            $claim->business->getTellusPassword(),
            config('services.tellus.endpoint')
        );

        try {
            if ($filename = $tellus->submitClaim($this->getData($claim))) {
                // Success

                // create new TellusFile for the Claim
                $claim->tellusFiles()->create([
                    'filename' => $filename,
                    'status'   => ClaimInvoiceTellusFile::STATUS_PENDING,
                ]);

                return true;
            }
        } catch (TellusValidationException $ex) {
            if ($ex->hasErrors()) {
                throw $ex;
            }
            throw new ClaimTransmissionException('Error submitting claim XML to Tellus: ' . $ex->getMessage());
        } catch (TellusApiException $ex) {
            throw new ClaimTransmissionException('Error connecting to Tellus: ' . $ex->getMessage());
        } catch (ClaimTransmissionException $ex) {
            throw $ex;
        } catch (\Exception $ex) {
            app('sentry')->captureException($ex);
            \Log::info($ex);
            throw new ClaimTransmissionException('An error occurred while trying to submit data to the Tellus API server.  Please try again or contact Ally.');
        }

        throw new ClaimTransmissionException('An unexpected error occurred.');
    }

    /**
     * Map a ClaimInvoiceItem record to the importable
     * data for the service.
     *
     * @param ClaimInvoiceItem $item
     * @return null|array
     * @throws ClaimTransmissionException
     */
    public function mapClaimableRecord(ClaimInvoiceItem $item): ?array
    {
        if ($item->claimable_type != ClaimableService::class) {
            // TELLUS DOES NOT SUPPORT EXPENSES
            return null;
        }

        $claim = $item->claim;
        /** @var ClaimableService $service */
        $service = $item->claimable;
        /** @var \App\Business $business */
        $business = $claim->business;

        $diagnosisCodes = $this->getDiagnosisCodes($item);

        $data = [
            'SourceSystem' => $this->tcLookup('SourceSystem', 'ALLY'),
            'Jurisdiction' => $this->tcLookup('Jurisdiction', $service->state),
            'Payer' => $this->tcLookup('Payer', $claim->payer_code),
            'Plan' => $this->tcLookup('Plan', $claim->plan_code), // FMSP is only Acceptable Value
            // 'Program'                => $this->tcLookup('Program', 'PACE'), // OPTIONAL, PACE is only Acceptable Value
            'DeliverySystem' => $this->tcLookup('DeliverySystem', 'MCOR'), // FFFS or MCOR.. no way to derive this from our system yet.
            'ProviderName' => $business->name,
            'ProviderMedicaidId' => $business->medicaid_id,
            'ProviderNPI' => $business->medicaid_npi_number, // OPTIONAL
            'ProviderNPITaxonomy' => $business->medicaid_npi_taxonomy, // OPTIONAL
            'ProviderNPIZipCode' => $this->getBusinessZip($business), // OPTIONAL - 9 digit zipcode, no dashes
            'ProviderEin' => str_replace('-', '', $business->ein), // REQUIRED
            'CaregiverFirstName' => $item->caregiver_first_name,
            'CaregiverLastName' => $item->caregiver_last_name,
            'CaregiverLicenseNumber' => $item->caregiver_medicaid_id, // OPTIONAL
            'RecipientMedicaidId' => $item->client_medicaid_id,
            'RecipientMemberId' => '', // OPTIONAL
            'RecipientFirstName' => $item->client_first_name,
            'RecipientLastName' => $item->client_last_name,
            'RecipientDob' => $item->client_dob ? Carbon::parse($item->client_dob)->format('m/d/Y') : '',
            'ServiceAddress1' => $service->address1,
            'ServiceAddress2' => $service->address2, // OPTIONAL
            'ServiceCity' => $service->city,
            'ServiceState' => $service->state,
            'ServiceZip' => $service->zip,
            'VisitId' => $item->id,
            'ServiceCode' => $service->service_code,
            'ServiceCodeMod1' => '', // OPTIONAL
            'ServiceCodeMod2' => '', // OPTIONAL
            'DiagnosisCode1' => $diagnosisCodes[0],
            'DiagnosisCode2' => $diagnosisCodes[1], // OPTIONAL && TODO
            'DiagnosisCode3' => $diagnosisCodes[2], // OPTIONAL && TODO
            'DiagnosisCode4' => $diagnosisCodes[3], // OPTIONAL && TODO
            'StartVerificationType' => $this->tcLookup('StartVerificationType', $this->getVerificationType($service->evv_method_in)), // OPTIONAL
            'EndVerificationType' => $this->tcLookup('EndVerificationType', $this->getVerificationType($service->evv_method_out)), // OPTIONAL
            'ScheduledStartDateTime' => $service->scheduled_start_time->format($this->timeFormat), // OPTIONAL
            'ScheduledEndDateTime' => $service->scheduled_end_time->format($this->timeFormat), // OPTIONAL
            'ScheduledLatitude' => '',
            'ScheduledLongitude' => '',
            'ActualStartDateTime' => $service->evv_start_time->setTimezone($business->timezone)->format($this->timeFormat), // OPTIONAL
            'ActualEndDateTime' => $service->evv_end_time->setTimezone($business->timezone)->format($this->timeFormat), // OPTIONAL
            'ActualStartLatitude' => '',
            'ActualStartLongitude' => '',
            'ActualEndLatitude' => '',
            'ActualEndLongitude' => '',
            'UserField1' => $claim->id, // OPTIONAL
            'UserField2' => $item->id, // OPTIONAL
            'UserField3' => $service->visit_start_time->setTimezone($business->timezone)->format($this->timeFormat), // OPTIONAL
            // 'ReasonCode1'            => $this->tcLookup( 'ReasonCode', '105' ), // OPTIONAL && TODO
            // 'ReasonCode2' => '', // OPTIONAL && TODO
            // 'ReasonCode3' => '', // OPTIONAL && TODO
            // 'ReasonCode4' => '', // OPTIONAL && TODO
            'TimeZone' => $this->tcLookup('TimeZone', $this->getBusinessTimezone($business)),
            'VisitNote' => $service->caregiver_comments ?? '', // OPTIONAL
            'EndAddress1' => $service->address1, // OPTIONAL
            'EndAddress2' => $service->address2, // OPTIONAL
            'EndCity' => $service->city, // OPTIONAL
            'EndState' => $service->state, // OPTIONAL
            'EndZip' => $service->zip, // OPTIONAL
            'VisitStatus' => $this->tcLookup('VisitStatus', 'COMP'), // OPTIONAL, Hardcoded to 'Completed' on purpose
            // 'MissedVisitReason'      => $this->tcLookup( 'MissedVisitReason', 'PCAN' ), // OPTIONAL, TODO
            // 'MissedVisitActionTaken' => $this->tcLookup( 'MissedVisitActionTaken', 'SCHS' ), // OPTIONAL, TODO
            // 'InvoiceUnits'           => '', // OPTIONAL && TODO
            // 'InvoiceAmount'          => '13.37', // OPTIONAL && TODO
            'ScheduledEndLatitude' => '',
            'ScheduledEndLongitude' => '',
            // 'PaidAmount'             => '13.37', // OPTIONAL && TODO
            'CareDirectionType' => $this->tcLookup('CareDirectionType', 'PROV'), // OPTIONAL
            'Tasks' => '', // a wrapper element for the tasks
            // 'Task'                   => $this->tcLookup( 'Task', 'MBED' ),
        ];

        // Set start EVV data (if exists)
        if ($service->evv_method_in == Shift::METHOD_GEOLOCATION && filled($service->checked_in_latitude) && filled($service->checked_in_longitude)) {
            $data['ScheduledLatitude'] = $service->latitude ?? ''; // OPTIONAL
            $data['ScheduledLongitude'] = $service->longitude ?? ''; // OPTIONAL
            $data['ActualStartLatitude'] = $service->checked_in_latitude; //OPTIONAL
            $data['ActualStartLongitude'] = $service->checked_in_longitude; //OPTIONAL
        }

        // Set end EVV data (if exists)
        if ($service->evv_method_out == Shift::METHOD_GEOLOCATION && filled($service->checked_out_latitude) && filled($service->checked_out_longitude)) {
            $data['ActualEndLatitude'] = $service->checked_out_latitude; // OPTIONAL
            $data['ActualEndLongitude'] = $service->checked_out_longitude; //OPTIONAL
            $data['ScheduledEndLatitude'] = $service->latitude ?? ''; // OPTIONAL
            $data['ScheduledEndLongitude'] = $service->longitude ?? ''; // OPTIONAL
        }

        // clean empty values and return
        return collect($data)->filter()->toArray();
    }

    /**
     * Map collection of activities to their corresponding duties codes.
     *
     * @param \Illuminate\Support\Collection $activities
     * @return string
     */
    public function mapActivities(Collection $activities): string
    {
        // TODO: re-work this to read from hcpcs_procedure_code field in DB:
        // https://jtrsolutions.atlassian.net/browse/ALLY-1151
        if ($activities->isEmpty()) {
            return '';
        }

        $activities = $activities->map(function ($activity) {
            if (!empty($activity->business_id)) {
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

    /**
     * Lookup typecode data from the database.
     *
     * @param string $category
     * @param string $textCode
     * @return array
     * @throws ClaimTransmissionException
     */
    public function tcLookup(string $category, string $textCode): array
    {
        $typeCode = TellusTypecode::where('category', $category)
            ->where('text_code', 'LIKE', $textCode)
            ->first();

        if (empty($typeCode)) {
            throw new ClaimTransmissionException("Error mapping values to Tellus dictionary: Invalid text code value \"$textCode\" for $category.");
        }

        return [$typeCode->description, $typeCode->code];
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
     * Convert the business timezone into the corresponding code.
     *
     * @param Business $business
     * @return string
     */
    protected function getBusinessTimezone(Business $business): string
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
     * Get proper formatted business zipcode.
     *
     * @param Business $business
     * @return string
     */
    public function getBusinessZip(Business $business)
    {
        return trim(str_replace('-', '', $business->zip));
    }

    /**
     * Split client diagnosis codes from databased array.
     *
     * @param ClaimInvoiceItem $item
     * @return array
     */
    protected function getDiagnosisCodes(ClaimInvoiceItem $item): array
    {
        return array_pad(
            array_map('trim', explode(',', $item->client_medicaid_diagnosis_codes)),
            4,
            ''
        );
    }

    /**
     * Check transmitter is in test mode.
     *
     * @param ClaimInvoice $claim
     * @return bool
     */
    public function isTestMode(ClaimInvoice $claim): bool
    {
        return $claim->business->tellus_username == "test";
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
        $tellus = new TellusService(
            $claim->business->tellus_username,
            $claim->business->getTellusPassword(),
            config('services.tellus.endpoint')
        );

        $xml = $tellus->convertArrayToXML($this->getData($claim));

        if ($errors = $tellus->getValidationErrors($xml)) {
            throw new TellusValidationException('Claim file did not pass local XML validation.', $errors);
        }

        $filename = 'test-claims/tellus_' . md5($claim->id . uniqid() . microtime()) . '.xml';
        \Storage::disk('public')->put($filename, $xml);
        return "/storage/$filename";
    }
}