<?php
namespace App\Billing\Claims;

use App\Billing\Claim;
use App\Billing\ClientInvoice;
use App\Billing\Contracts\ClaimTransmitterInterface;
use App\Billing\Exceptions\ClaimTransmissionException;
use App\Client;
use App\Services\TellusService;
use App\Shift;
use Carbon\Carbon;
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
        if (empty($invoice->client->business->tellus_username) || empty($invoice->client->business->getTellusPassword())) {
            throw new ClaimTransmissionException('You cannot submit a claim because you do not have your Tellus credentials set.  You can edit this information under Settings > General > Claims, or contact Ally for assistance.');
        }

        // TODO: add tellus specific mapping requirements

        return parent::validateInvoice($invoice);
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
        try {
            $tellus = new TellusService(
                $claim->invoice->client->business->tellus_username,
                $claim->invoice->client->business->getTellusPassword(),
                config('services.tellus.endpoint')
            );
        } catch (\Exception $ex) {
            app('sentry')->captureException($ex);
            throw new ClaimTransmissionException('Unable to login to HHAeXchange SFTP server.  Please check your credentials and try again.');
        }

        try {
            $xml = $tellus->convertArrayToXML($this->getData($claim));
            if ($tellus->sendXml($xml)) {
                // Success
                return true;
            }
        } catch (\Exception $ex) {











            // TODO: remove this!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!1
            // TODO: remove this!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!1
            // TODO: remove this!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!1
            // TODO: remove this!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!1
            // TODO: remove this!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!1
            // TODO: remove this!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!1
            // TODO: remove this!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!1
            // TODO: remove this!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!1
            // TODO: remove this!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!1
            // TODO: remove this!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!1
            // TODO: remove this!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!1
            // TODO: remove this!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!1
            // TODO: remove this!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!1
            // TODO: remove this!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!1
            dd($ex->getMessage());












            app('sentry')->captureException($ex);
            throw new ClaimTransmissionException('An error occurred while trying to submit data to the Tellus API server.  Please try again or contact Ally.');
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
    public function mapShiftRecord(Claim $claim, Shift $shift) : array
    {
        /** @var \App\Business $business */
        $business = $shift->business;

        /** @var \App\Client $client */
        $client = $shift->client;

        /** @var \App\Caregiver $caregiver */
        $caregiver = $shift->caregiver;

        /** @var \App\Address $address */
        $address = $shift->client->evvAddress();

        /** @var \Packages\GMaps\GeocodeCoordinates|false */
        $geocode = $address->getGeocode();

        $diagnosisCodes = $this->getDiagnosisCodes($client);

        /** @var @var \App\Billing\ClientInvoice $invoice */
        $invoice = $claim->invoice;

        return [
            'SourceSystem' => 'ALLY',
            'Jurisdiction' => optional($shift->client->address)->state ?: 'NN',
            'Payer' => '', // TODO: figure out how to map from data codes
            'Plan' => '', // TODO: figure out how to map from data codes
            'Program' => '', // N/A
            'DeliverySystem' => 'ALLY',
            'ProviderName' => $business->name,
            'ProviderMedicaidId' => $business->medicaid_id, // TODO: add validation
            'ProviderNpi' => $business->medicaid_npi_number, // TODO: add validation
            'ProviderNPITaxonomy' => $business->medicaid_npi_taxonomy, // TODO: add validation
            'ProviderNPIZipCode' => $business->getAddress()->zip, // TODO: add validation
            'ProviderEin' => $business->ein,
            'CaregiverFirstName' => $caregiver->firstname,
            'CaregiverLastName' => $caregiver->lastname,
            'CaregiverLicenseNumber' => $caregiver->medicaid_id,
            'RecipientMedicaidId' => $client->medicaid_id,
            'RecipientMemberId' => '', // N/A
            'RecipientFirstName' => $client->firstname,
            'RecipientLastName' => $client->lastname,
            'RecipientDob' => Carbon::parse($client->date_of_birth)->format('m/d/Y'),
            'ServiceAddress1' => $address->address1,
            'ServiceAddress2' => $address->address2,
            'ServiceCity' => $address->city,
            'ServiceState' => $address->state,
            'ServiceZip' => $address->zip,
            'VisitId' => $shift->id,
            'ServiceCode' => $this->mapActivities($shift->activities),
            'ServiceCodeMod1' => '', // N/A
            'ServiceCodeMod2' => '', // N/A
            'DiagnosisCode1' => $diagnosisCodes[0], // TODO: add validation
            'DiagnosisCode2' => $diagnosisCodes[1],
            'DiagnosisCode3' => $diagnosisCodes[2],
            'DiagnosisCode4' => $diagnosisCodes[3],
            'StartVerificationType' => '',
            'EndVerificationType' => '',
            'ScheduledStartDateTime' => '',
            'ScheduledEndDateTime' => '',
            'ScheduledLatitude' => '',
            'ScheduledLongitude' => '',
            'ActualStartDatetime' => '',
            'ActualEndDatetime' => '',
            'ActualStartLatitude' => '',
            'ActualStartLongitude' => '',
            'ActualEndLatitude' => '',
            'ActualEndLongitude' => '',
            'UserField1' => '',
            'UserField2' => '',
            'UserField3' => '',
            'ReasonCode1' => '',
            'ReasonCode2' => '',
            'ReasonCode3' => '',
            'ReasonCode4' => '',
            'Time Zone' => '',
            'visitNote' => '',
            'EndAddress1' => '',
            'EndAddress2' => '',
            'EndCity' => '',
            'EndState' => '',
            'EndZip' => '',
            'VisitStatus' => '',
            'MissedVisitReason' => '',
            'MissedVisitActionTaken' => '',
            'InvoiceUnits' => '',
            'InvoiceAmount' => $invoice->total,
            'ScheduledEndLatitude' => '',
            'ScheduledEndLongitude' => '',
            'PaidAmount' => '',
            'CareDirectionType' => '',
            
//            'SourceSystem' => 'ALLY',
//            'Jurisdiction' => $business->state ?: 'FL',
//            'Payer' => 'AHCA',
//            'Plan' => 'ALLY',
//            'Program' => '',
//            'DeliverySystem' => 'ALLY',
//            'ProviderName' => $business->name,
//            'ProviderMedicaidID' => $business->medicaid_id,
//            'ProviderNpi' => $business->medicaid_npi_number,
//            'ProviderNpiTaxonomy' => $business->medicaid_npi_taxonomy,
//            'ProviderEin' => $business->ein,
//            'CaregiverFirstName' => $caregiver->firstname,
//            'CaregiverLastName' => $caregiver->lastname,
//            'CaregiverLicenseNumber' => $caregiver->medicaid_id ?: $business->medicaid_id,
//            'RecipientMedicaidId' => $client->medicaid_id,
//            'RecipientMemberId' => '',
//            'RecipientFirstName' => $client->firstname,
//            'RecipientLastName' => $client->lastname,
//            'RecipientDob' => $client->date_of_birth ? Carbon::parse($client->date_of_birth)->format('m/d/Y') : '',
//            'ServiceAddress1' => $address->address1,
//            'ServiceAddress2' => $address->address2,
//            'ServiceCity' => $address->city,
//            'ServiceState' => $address->state,
//            'ServiceZip' => $address->zip,
//            'VisitId' => $shift->id,
//            'ServiceCode' => 'S9122',
//            'ServiceCodeMod1' => '',
//            'ServiceCodeMod2' => '',
//            'DiagnosisCode1' => $diagnosisCodes[0],
//            'DiagnosisCode2' => $diagnosisCodes[1],
//            'DiagnosisCode3' => $diagnosisCodes[2],
//            'DiagnosisCode4' => $diagnosisCodes[3],
//            'StartVerificationType' => $this->getVerificationMethod($shift),
//            'EndVerificationType' => $this->getVerificationMethod($shift),
//            'ScheduledStartDateTime' => $this->getScheduledStartTime($shift),
//            'ScheduledEndDateTime' => $this->getScheduledEndTime($shift),
//            'ScheduledLatitude' => $geocode->latitude ?? '',
//            'ScheduledLongitude' => $geocode->longitude ?? '',
//            'ActualStartDatetime' => $this->formatDateTime($shift->checked_in_time),
//            'ActualEndDatetime' => $this->formatDateTime($shift->checked_out_time),
//            'ActualStartLatitude' => $shift->checked_in_latitude,
//            'ActualStartLongitude' => $shift->checked_in_longitude,
//            'ActualEndLatitude' => $shift->checked_out_latitude,
//            'ActualEndLongitude' => $shift->checked_out_longitude,
//            'UserField1' => '',
//            'UserField2' => '',
//            'UserField3' => '',
//            'ReasonCode1' => '',
//            'ReasonCode2' => '',
//            'ReasonCode3' => '',
//            'ReasonCode4' => '',
//            'TimeZone' => 'NEWY',
        ];
    }




    protected function getVerificationMethod(Shift $shift)
    {
        return 'GPS';
    }

    protected function getScheduledStartTime(Shift $shift)
    {
        if ($this->useSchedule && $schedule = $shift->schedule) {
            $startsAt = $schedule->starts_at->copy();
            return $this->formatDateTime($startsAt, $shift->business->timezone);
        }

        return $this->formatDateTime($shift->checked_in_time);
    }

    protected function getScheduledEndTime(Shift $shift)
    {
        if ($this->useSchedule && $schedule = $shift->schedule) {
            $startsAt = $schedule->starts_at->copy();
            return $this->formatDateTime($startsAt->addMinutes($schedule->duration), $shift->business->timezone);
        }

        return $this->formatDateTime($shift->checked_out_time);
    }

    protected function formatDateTime($date, $inputTimezone = 'UTC', $outputTimezone = 'UTC')
    {
        return Carbon::parse($date, $inputTimezone)->setTimezone($outputTimezone)->format('m/d/Y g:i:s');
    }

    protected function getDiagnosisCodes(Client $client)
    {
        return array_pad(
            array_map('trim', explode(',', $client->medicaid_diagnosis_codes)),
            4,
            ''
        );
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
                case '002': // Bathing - Bed
                case '003': // Dressing
                case '005': // Hygiene - Hair Care
                case '006': // Shave
                case '004': // Hygiene - Mouth Care
                case '007': // Incontinence Care
                case '021': // Medication Reminders
                case '020': // Turning & Repositioning
                case '022': // Safety Supervision
                case '008': // Toileting
                case '009': // Catheter Care
                case '023': // Meal Preparation
                case '025': // Homemaker Services
                case '026': // Transportation
                case '024': // Feeding
                case '010': // Ostomy Care
                case '027': // Ambulation
                case '011': // Companion Care
                default:
                    return 'S9122';
            }
        });

        return implode('|', $activities->toArray());
    }
}