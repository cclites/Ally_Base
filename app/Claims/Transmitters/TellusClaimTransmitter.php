<?php

namespace App\Claims\Transmitters;

use App\Business;
use App\Claims\Exceptions\ClaimTransmissionException;
use App\Claims\Contracts\ClaimTransmitterInterface;
use App\Services\TellusValidationException;
use App\Claims\ClaimInvoiceItem;
use App\Claims\ClaimableService;
use App\Services\TellusService;
use App\Claims\ClaimInvoice;
use App\Shift;
use App\TellusTypecode;
use Illuminate\Support\Collection;

class TellusClaimTransmitter extends BaseClaimTransmitter implements ClaimTransmitterInterface
{
    /**
     * Timestamp format string.
     *
     * @var string
     */
    protected $timeFormat = 'Y-m-d H:i';

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

        if (empty($claim->business->tellus_username) || empty($claim->business->getTellusPassword())) {
            $errors->push(['message' => 'Your Tellus Credentials have not been setup.', 'url' => route('business-settings') . '#claims']);
        }

        // TODO: finish adding validation

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
            if ($tellus->submitClaim($this->getData($claim))) {
                // Success
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
            $claim->client_medicaid_id, //    "Medicaid Number", (required)
            $service->caregiver_id, //    "Caregiver Code", (required)
            $service->caregiver_first_name, //    "Caregiver First Name",
            $service->caregiver_last_name, //    "Caregiver Last Name",
            $service->caregiver_gender ? strtoupper($service->caregiver_gender) : '', //    "Caregiver Gender",
            $service->caregiver_dob ?? '', //    "Caregiver Date of Birth",
            $this->cleanSsn($service->caregiver_ssn), //    "Caregiver SSN",
            $claim->id, //    "Schedule ID", (required)
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
            '', //    "Notes",
            'N', //    "Is Deletion",
            $item->id, //    "Invoice Line Item ID",
            'N', //    "Missed Visit",
            // TODO: implement reason codes?
            '', //    "Missed Visit Reason Code",
            '', //    "Missed Visit Action Taken Code",
            $service->getHasEvv() ? '' : 'Y', //    "Timesheet Required",
            $service->getHasEvv() ? '' : 'Y', //    "Timesheet Approved",
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
    public function mapActivities(Collection $activities) : string
    {
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

    /**
     * Lookup typecode data from the database.
     *
     * @param string $category
     * @param string $textCode
     * @return array
     * @throws ClaimTransmissionException
     */
    public function tcLookup(string $category, string $textCode) : array
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