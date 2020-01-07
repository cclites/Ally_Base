<?php

namespace App\Services;

use App\Contracts\SFTPReaderWriterInterface;
use App\ClaimInvoiceTellusFile;
use SimpleXMLElement;
use DOMDocument;

class TellusApiException extends \Exception{}
class TellusSftpException extends \Exception{}

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

    public const XML_SCHEMA_FILENAME = 'tellus/xml-schema.xsd';

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
        // Automatically handle replacing the username variable in the endpoint.
        $this->endpoint = str_replace("{username}", strtoupper($username), $endpoint);
    }

    /**
     * Log in to the SFTP server.
     *
     * @return SFTPReaderWriterInterface|null
     */
    public function loginToSftp(): ?SFTPReaderWriterInterface
    {
        $sftp = app(SFTPReaderWriterInterface::class, [
            'host' => config('services.tellus.sftp_host'),
            'port' => config('services.tellus.sftp_port')
        ]);

        $key = new \phpseclib\Crypt\RSA();
        $key->loadKey(file_get_contents(config('services.tellus.pem_path')));

        if ($sftp->login($this->username, $key)) {
            return $sftp;
        }

        return null;
    }

    /**
     * Submit an array of claim records through the
     * Tellus API service using an XML file.
     *
     * @param array $records
     * @return string
     * @throws TellusApiException
     * @throws TellusValidationException
     */
    public function submitClaim(array $records): string
    {
        $xml = $this->convertArrayToXML($records);

        if ($errors = $this->getValidationErrors($xml)) {
            throw new TellusValidationException('Claim file did not pass local XML validation.', $errors);
        }

        list($httpCode, $response) = $this->sendXml($xml);

        if ($httpCode === 401) {
            throw new TellusApiException('Invalid credentials or otherwise not authorized.');
        }

        if ($httpCode != 200) {
            throw new TellusApiException("Unexpected response code from Tellus, code: $httpCode.  Please try again.");
        }

        \Log::info($response);
        $xml = new SimpleXMLElement($response);
        if (isset($xml->xsdValidation) && (string)$xml->xsdValidation == 'FAILED') {
            \Log::error("Tellus API XML Error:\r\n$response");
            // TODO: add some sort of databased log so we can see other users errors
            throw new TellusValidationException('Claim file did not pass remote XML validation.');
        }

        if (!str_contains($response, 'Successfully submitted for processing')) {
            throw new TellusApiException("Unexpected response from Tellus.  Please try again.");
        }

        // any extra error checking? I think the above exception serves as a catch-all and guarantees that this field is populated
        return $xml->batchId;
    }

    /**
     * Send XML to the Tellus Endpoint.
     *
     * @param string $xml
     * @return array
     * @throws TellusApiException
     */
    protected function sendXml(string $xml): array
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

            if (!($result = curl_exec($process))) {
                throw new TellusApiException('Invalid response from Tellus API.');
            }
            $responseCode = curl_getinfo($process, CURLINFO_HTTP_CODE);
            $header_size = curl_getinfo($process, CURLINFO_HEADER_SIZE);
            curl_close($process);

            $body = substr($result, $header_size);

            return [$responseCode, $body];
        } catch (\Exception $ex) {
            app('sentry')->captureException($ex);
            throw new TellusApiException('Error connecting to the Tellus API.');
        }
    }

    /**
     * Validate the XML schema and return the errors if any.
     *
     * @param string $xml
     * @return array|null
     * @throws TellusValidationException
     */
    public function getValidationErrors(string $xml): ?array
    {
        try {
            $validator = new DomValidator;
            $validated = $validator->validateXMLString($xml);
            if (!$validated) {
                return $validator->getErrors();
//                return collect($validator->getErrors())->map(function (array $item) {
//                    return ($item['field'] ? $item['field'].': ' : '') . $item['error'];
//                })->toArray();
            }
            return null;
        } catch (\Exception $ex) {
            app('sentry')->captureException($ex);
            throw new TellusValidationException("Unexpected error while validating XML Schema");
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
            throw new TellusApiException('Error generating Tellus XML claim file.');
        }
    }

    /**
     * Return the SimpleXMLElement object from all the shifts
     *
     * @param array $records
     * @return \SimpleXMLElement
     */
    protected function getSimpleXml(array $records): SimpleXMLElement
    {
        $xml = new SimpleXMLElement('<RenderedServices xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="Rendered Service XML Sample Schema 20180712.xsd" />');

        foreach ($records as $record) {
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
    protected function mapRecordToXML(array $record, ?SimpleXMLElement $parent = null): SimpleXMLElement
    {
        if ($parent === null) {
            $service = new SimpleXMLElement('<RenderedService />');
        } else {
            $service = $parent->addChild('RenderedService');
        }

        $tasks = null;

        foreach ($record as $key => $value) {

            if ($key == 'Tasks') {

                $tasks = $service->addChild($key);
            } else if ($key == 'Task') {

                $child = $tasks->addChild($key, $value[0]);
                $child->addAttribute('tc', $value[1]);
            } else if (is_array($value) && count($value) >= 2) {
                // Handle adding tc="" attribute

                $child = $service->addChild($key, $value[0]);
                $child->addAttribute('tc', $value[1]);
            } else {
                $service->addChild($key, $value);
            }
        }

        return $service;
    }

    /**
     * Get the string results from a response xml file.
     *
     * @param string $filename
     * @return array
     * @throws TellusSftpException
     */
    public function getFileResult(string $filename): array
    {
        if (!$sftp = $this->loginToSftp()) {
            throw new TellusSftpException('Could not connect to Tellus SFTP, please check the credentials.');
        }

        $acceptedFile = $this->getResultFilePath("{$filename}_ACCEPTED.XML");
        if ($contents = $sftp->get($acceptedFile, false)) {
            return [$contents, ClaimInvoiceTellusFile::STATUS_ACCEPTED];
        }

        $rejectedFile = $this->getResultFilePath("{$filename}_REJECTED.XML");
        if ($contents = $sftp->get($rejectedFile, false)) {
            return [$contents, ClaimInvoiceTellusFile::STATUS_REJECTED];
        }

        return [null, null];
    }

    /**
     * Get absolute path of filename on the SFTP server using the
     * config setting to for base directory.
     *
     * @param string $filename
     * @return string
     */
    public function getResultFilePath(string $filename)
    {
        if (str_contains($this->endpoint, 'edi.stg.4tellus.net')) {
            // using staging server - should check test directory
            return $this->getSftpPath("local/outbound/test/{$filename}");
        }

        return $this->getSftpPath("local/outbound/{$filename}");
    }

    /**
     * Get absolute path of filename on the SFTP server using the
     * config setting to for base directory.
     *
     * @param string $filename
     * @return string
     */
    public function getSftpPath(string $filename)
    {
        $root = str_replace('{username}', strtolower($this->username), config('services.tellus.sftp_directory'));

        $root = ends_with($root, '/') ? $root : $root . '/';

        return $root . $filename;
    }
}
