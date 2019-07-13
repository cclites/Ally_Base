<?php
namespace App\Services;

use DOMDocument;
use SimpleXMLElement;

class TellusApiException extends \Exception {}

/**
 * Tellus XML API v2.0 implementation.
 *
 * Class TellusService
 * @package App\Services
 */
class TellusService
{
    /**
     * @var string
     */
    protected $username;

    /**
     * @var string
     */
    protected $password;

    /**
     * @var string
     */
    protected $endpoint;

    /**
     * TellusService constructor.
     *
     * @param string $username
     * @param string $password
     * @param string $endpoint
     */
    public function __construct(string $username, string $password, string $endpoint)
    {
        $this->username = $username;
        $this->password = $password;
        $this->endpoint = $endpoint;
    }

    /**
     * Submit an array of claim records through the
     * Tellus API service using an XML file.
     *
     * @param array $records
     * @return bool
     * @throws TellusApiException
     */
    public function submitClaim(array $records) : bool
    {
        $xml = $this->convertArrayToXML($records);

        list($httpCode, $response) = $this->sendXml($xml);

        if ($httpCode === 401) {
            throw new TellusApiException('Invalid credentials or otherwise not authorized.');
        }

        return true;
    }

    /**
     * Send XML to the Tellus Endpoint.
     *
     * @param string $xml
     * @return array
     * @throws TellusApiException
     */
    protected function sendXml(string $xml) : array
    {
        try {
            $process = curl_init($this->endpoint);
            curl_setopt($process, CURLOPT_HTTPHEADER, ['Content-Type: application/xml']);
            curl_setopt($process, CURLOPT_HEADER, 1);
            curl_setopt($process, CURLOPT_USERPWD, $this->username . ":" . $this->password);
            curl_setopt($process, CURLOPT_TIMEOUT, 60);
            curl_setopt($process, CURLOPT_POST, 1);
            curl_setopt($process, CURLOPT_POSTFIELDS, $xml);
            curl_setopt($process, CURLOPT_RETURNTRANSFER, true);

            if (! ($result = curl_exec($process))) {
                throw new TellusApiException('Invalid response from Tellus API.');
            }
            $responseCode = curl_getinfo($process, CURLINFO_HTTP_CODE);
            curl_close($process);

            return [$responseCode, $result];
        } catch (\Exception $ex) {
            app('sentry')->captureException($ex);
            throw new TellusApiException('Error connecting to the Tellus API.');
        }
    }

    /**
     * Return a formatted XML string from the given data.
     *
     * @param array $records
     * @return string
     * @throws TellusApiException
     */
    public function convertArrayToXML(array $records)
    {
        try {
            $dom = new DOMDocument("1.0", "UTF-8");
            $dom->preserveWhiteSpace = false;
            $dom->formatOutput = true;
            $dom->loadXML($this->getSimpleXml($records)->asXML());
            return $dom->saveXML();
        } catch (\Exception $ex) {
            app('sentry')->captureException($ex);
            throw new TellusApiException('Error generating Tellus XML claim file..');
        }
    }

    /**
     * Return the SimpleXMLElement object from all the shifts
     *
     * @param array $records
     * @return \SimpleXMLElement
     */
    protected function getSimpleXml(array $records) : SimpleXMLElement
    {
        $xml = new SimpleXMLElement('<RenderedServices xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="Rendered Service XML Sample Schema 20180712.xsd" />');

        foreach($records as $record) {
            $this->mapRecordToXML($record, $xml);
        }

        return $xml;
    }

    /**
     * Produce XML output from a single array which can be added to an existing SimpleXMLElement.
     *
     * @param array $record
     * @param \SimpleXMLElement|null $parent
     * @return \SimpleXMLElement
     */
    protected function mapRecordToXML(array $record, ?SimpleXMLElement $parent = null) : SimpleXMLElement
    {
        if ($parent === null) {
            $service = new SimpleXMLElement('<RenderedService />');
        }
        else {
            $service = $parent->addChild('RenderedService');
        }

        foreach($record as $key => $value) {
            $service->addChild($key, $value);
        }

        return $service;
    }
}
