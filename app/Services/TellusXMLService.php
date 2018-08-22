<?php
namespace App\Services;

use App\Shift;
use Carbon\Carbon;
use DOMDocument;
use Response;
use SimpleXMLElement;

class TellusXMLService
{
    protected $useSchedule = true;
    protected $shifts = [];

    /**
     * @param Shift|Shift[] $shifts
     */
    public function addShift($shifts)
    {
        if (!is_array($shifts)) $shifts = [$shifts];
        $this->shifts = array_merge($this->shifts, $shifts);
    }

    /**
     * @return \Illuminate\Http\Response
     */
    public function downloadXml()
    {
        return Response::make($this->getXml(), '200')->header('Content-Type', 'application/xml');
    }

    /**
     * @return array
     */
    public function getShifts()
    {
        return $this->shifts;
    }

    /**
     * Return a formatted XML string from all of the shifts
     *
     * @return string
     */
    public function getXml()
    {
        $dom = new DOMDocument("1.0", "UTF-8");
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($this->getSimpleXml()->asXML());
        return $dom->saveXML();
    }

    /**
     * Return the SimpleXMLElement object from all the shifts
     *
     * @return \SimpleXMLElement
     */
    public function getSimpleXml()
    {
        $xml = new SimpleXMLElement('<RenderedServices xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="Rendered Service XML Sample Schema 20180712.xsd" />');
        foreach($this->getShifts() as $shift) {
            $this->shiftToXml($shift, $xml);
        }
        return $xml;
    }


    /**
     * Produce XML output from a single shift which can be added to an existing SimpleXMLElement
     *
     * @param \App\Shift $shift
     * @param SimpleXMLElement|null $parent
     * @return string
     */
    public function shiftToXml(Shift $shift, SimpleXMLElement $parent = null)
    {
        if ($parent === null) {
            $service = new SimpleXMLElement('<RenderedService />');
        }
        else {
            var_dump('Adding child to parent');
            $service = $parent->addChild('RenderedService');
        }
        $array = $this->shiftToArray($shift);
        foreach($array as $key=>$value) {
            $service->addChild($key, $value);
        }
        return $service;
    }

    /**
     * Map a shift to the Tellus fields
     *
     * @param \App\Shift $shift
     * @return array
     */
    public function shiftToArray(Shift $shift)
    {
        /** @var \App\Business $business */
        $business = $shift->business;

        /** @var \App\Client $client */
        $client = $shift->client;

        /** @var \App\Caregiver $caregiver */
        $caregiver = $shift->caregiver;

        /** @var \App\Address $address */
        $address = $shift->client->addresses()->where('type', 'evv')->first();
        $geocode = $address->getGeocode();

        return [
            'SourceSystem' => 'ALLY',
            'Juridiction' => $business->state ?: 'FL',
            'Payer' => '',
            'Plan' => '',
            'Program' => '',
            'DeliverySystem' => 'ALLY',
            'ProviderName' => $business->name,
            'ProviderMedicaidID' => '',
            'ProviderNpi' => '',
            'ProviderNpiTaxonomy' => '',
            'ProviderEin' => '',
            'CaregiverFirstName' => $caregiver->firstname,
            'CaregiverLastName' => $caregiver->lastname,
            'CaregiverLicenseNumber' => '',
            'RecipientMedicaidId' => '',
            'RecipientMemberId' => '',
            'RecipientFirstName' => $client->firstname,
            'RecipientLastName' => $client->lastname,
            'RecipientDob' => $client->date_of_birth ? Carbon::parse($client->date_of_birth)->format('m/d/Y') : '',
            'ServiceAddress1' => $address->address1,
            'ServiceAddress2' => $address->address2,
            'ServiceCity' => $address->city,
            'ServiceState' => $address->state,
            'ServiceZip' => $address->zip,
            'VisitId' => $shift->id,
            'ServiceCode' => '',
            'ServiceCodeMod1' => '',
            'ServiceCodeMod2' => '',
            'DiagnosisCode1' => '',
            'DiagnosisCode2' => '',
            'DiagnosisCode3' => '',
            'DiagnosisCode4' => '',
            'StartVerificationType' => $this->getVerificationMethod($shift),
            'EndVerificationType' => $this->getVerificationMethod($shift),
            'ScheduledStartDateTime' => $this->getScheduledStartTime($shift),
            'ScheduledEndDateTime' => $this->getScheduledEndTime($shift),
            'ScheduledLatitude' => $geocode->latitude ?? '',
            'ScheduledLongitude' => $geocode->longitude ?? '',
            'ActualStartDatetime' => $this->formatDateTime($shift->checked_in_time),
            'ActualEndDatetime' => $this->formatDateTime($shift->checked_out_time),
            'ActualStartLatitude' => $shift->checked_in_latitude,
            'ActualStartLongitude' => $shift->checked_in_longitude,
            'ActualEndLatitude' => $shift->checked_out_latitude,
            'ActualEndLongitude' => $shift->checked_out_longitude,
            'UserField1' => '',
            'UserField2' => '',
            'UserField3' => '',
            'ReasonCode1' => '',
            'ReasonCode2' => '',
            'ReasonCode3' => '',
            'ReasonCode4' => '',
            'TimeZone' => 'NEWY',
        ];
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

    protected function getVerificationMethod(Shift $shift)
    {
        return 'GPS';
    }
}