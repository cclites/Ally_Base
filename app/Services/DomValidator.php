<?php

namespace App\Services;

class DOMValidator
{
    /**
     * @var int
     */
    public $feedErrors = 0;

    /**
     * Formatted libxml Error details
     *
     * @var array
     */
    public $errorDetails;

    protected function getSchemaFilename(): string
    {
        return storage_path('app/public/tellus-schema.xsd');
    }

    /**
     * DOMValidator constructor.
     */
    public function __construct()
    {
        $this->handler = new \DOMDocument('1.0', 'utf-8');
    }

    /**
     * @param \libXMLError object $error
     *
     * @return string
     */
    private function libxmlDisplayError($error)
    {
        $errorString = "Error $error->code in $error->file (Line:{$error->line}):";
        $errorString .= trim($error->message);
        return $errorString;
    }

    /**
     * @return array
     */
    private function libxmlDisplayErrors()
    {
        $errors = libxml_get_errors();
        $result = [];
        foreach ($errors as $error) {
            $result[] = $this->libxmlDisplayError($error);
        }
        libxml_clear_errors();
        return $result;
    }

    /**
     * Validate Incoming Feeds against Listing Schema
     *
     * @param string $contents
     * @return bool
     *
     * @throws \Exception
     */
    public function validateXMLString(string $contents)
    {
        if (!class_exists('DOMDocument')) {
            throw new \Exception("'DOMDocument' class not found!");
        }
        if (!file_exists($this->getSchemaFilename())) {
            throw new \Exception('Schema is Missing at ' . $this->getSchemaFilename());
        }
        libxml_use_internal_errors(true);

        $this->handler->loadXML($contents, LIBXML_NOBLANKS);
        if (!$this->handler->schemaValidate($this->getSchemaFilename())) {
            $this->errorDetails = $this->libxmlDisplayErrors();
            $this->feedErrors = 1;
        } else {
            //The file is valid
            return true;
        }
    }

    /**
     * Display Error if Resource is not validated
     *
     * @return array
     */
    public function getErrors()
    {
        return collect($this->errorDetails)->map(function (string $item) {
            $matches = null;
            preg_match('/:Element \'([a-zA-Z0-9]+)\': (.*)/', $item, $matches);

            if (count($matches) < 3) {
                return ['error' => $item];
            }
            $field = $matches[1];
            $error = $matches[2];

            $matches = null;
            preg_match('/^\[facet[^]]+] (.*)/', $error, $matches);
            if (count($matches) > 1) {
                $error = $matches[1];
            }

            return ['field' => $field, 'error' => $error];
        })->toArray();
    }
}
