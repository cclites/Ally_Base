<?php
namespace App\Services;

use DOMDocument;
use SimpleXMLElement;

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
     * Send XML to the Tellus Endpoint.
     *
     * @param string $xml
     * @return string|bool
     * @throws \Exception
     */
    public function sendXml(string $xml)
    {
        $process = curl_init($this->endpoint);
        curl_setopt($process, CURLOPT_HTTPHEADER, ['Content-Type: application/xml']);
        curl_setopt($process, CURLOPT_HEADER, 1);
        curl_setopt($process, CURLOPT_USERPWD, $this->username . ":" . $this->password);
        curl_setopt($process, CURLOPT_TIMEOUT, 60);
        curl_setopt($process, CURLOPT_POST, 1);
        curl_setopt($process, CURLOPT_POSTFIELDS, $xml);
        curl_setopt($process, CURLOPT_RETURNTRANSFER, true);

        $result = curl_exec($process);
        curl_close($process);

        return $result;
    }

    /**
     * Return a formatted XML string from the given data.
     *
     * @param array $records
     * @return string
     * @throws \Exception
     */
    public function convertArrayToXML(array $records)
    {
        $dom = new DOMDocument("1.0", "UTF-8");
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($this->getSimpleXml($records)->asXML());
        return $dom->saveXML();
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
