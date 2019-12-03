<?php
namespace App\Services;

use App\Contracts\SFTPReaderWriterInterface;
use App\Billing\Exceptions\ClaimTransmissionException;
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
     * @var \phpseclib\Net\SFTP
     */
    protected $sftp;

    public const TYPECODE_DICTIONARY_FILENAME = 'tellus/typecode-dictionary.xlsx';
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

        $this->sftp = app(SFTPReaderWriterInterface::class, ['host' => config('services.tellus.sftp_host'), 'port' => config('services.tellus.sftp_port')]);
    }

    /**
     * Log in to the SFTP server.
     *
     * @return bool
     */
    public function login() : bool
    {
        $key = new \phpseclib\Crypt\RSA();
        $key->loadKey( file_get_contents( config( 'services.tellus.pem_path' ) ) );

        if (! $this->sftp->login($this->username, $key )) {
            return false;
        }

        return true;
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
    public function submitClaim(array $records) : string
    {
        $xml = $this->convertArrayToXML($records);

        if ($errors = $this->getValidationErrors($xml)) {
            throw new TellusValidationException('Claim file did not pass local XML validation.', $errors);
        }

        list($httpCode, $response) = $this->sendXml($xml);

        $xml = new SimpleXMLElement($response);
        if (isset($xml->xsdValidation) && (string) $xml->xsdValidation == 'FAILED') {
            \Log::error("Tellus API XML Error:\r\n$response");
            // TODO: add some sort of databased log so we can see other users errors
            throw new TellusValidationException('Claim file did not pass remote XML validation.');
        }

        if ($httpCode === 401) {
            throw new TellusApiException('Invalid credentials or otherwise not authorized.');
        }

        if ($httpCode != 200) {
            throw new TellusApiException("Unexpected response code from Tellus, code: $httpCode.  Please try again.");
        }

        if (! str_contains($response, 'Successfully submitted for processing')) {
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
    public function getValidationErrors(string $xml) : ?array
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

        $tasks = null;

        foreach( $record as $key => $value ) {

            if( $key == 'Tasks' ){

                $tasks = $service->addChild( $key );
            }
            else if( $key == 'Task' ){

                $child = $tasks->addChild( $key, $value[0] );
                $child->addAttribute('tc', $value[1]);
            }
            else if (is_array($value) && count($value) >= 2) {
                // Handle adding tc="" attribute

                $child = $service->addChild( $key, $value[0] );
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
     * @return mixed
     */
    public function downloadResponse(string $filename)
    {
        if (! $this->login()) {
            throw new \Exception('Your Tellus username and password was not accepted.  Please contact Tellus and let them know you are unable to login to their SFTP server.');
        }

        // dd( $this->sftp->nlist( config( 'services.tellus.sftp_directory' ) ) );

        for( $i = 0; $i < count( $list ); $i++ ){

            $accepted = $this->sftp->get(
                config('services.tellus.sftp_directory') . "/$filename" . "_ACCEPTED.XML",
                false
            );
        }
        $rejected = $this->sftp->get(
            config('services.tellus.sftp_directory') . "/$filename" . "_REJECTED.XML",
            false
        );

    }

    /**
     * Download remote resource files and store on public disk.
     *
     * @return bool
     */
    public static function downloadApiResources() : bool
    {
        $dictionary = download_file(
            config('services.tellus.dictionary_file'),
            \Storage::disk('public'),
            self::TYPECODE_DICTIONARY_FILENAME
        );

        $schema = download_file(
            config('services.tellus.schema_file'),
            \Storage::disk('public'),
            self::XML_SCHEMA_FILENAME
        );

        return $dictionary && $schema;
    }
}
