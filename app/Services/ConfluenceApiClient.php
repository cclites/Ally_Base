<?php

namespace App\Services;

use App\Client;

class ConfluenceApiClient
{
    public $username = '';
    public $apiToken = '';
    public $host;

    /**
     * ConfluenceApiClient Constructor.
     * @param string $host
     * @param string $username
     * @param string $apiToken
     */
    public function __construct(string $host, string $username, string $apiToken)
    {
        $this->host = $host;
        $this->username = $username;
        $this->apiToken = $apiToken;
    }

    /**
     * Upload attachment to the given document.
     *
     * @param string $contentId
     * @param string $filename
     * @param string $attachmentName
     * @return bool
     */
    public function uploadAttachment(string $contentId, string $filename, string $attachmentName) : bool
    {
        $result = $this->_call(
            'POST',
            "content/{$contentId}/child/attachment?",
            ['file' => new \CURLFile($filename, mime_content_type($filename), $attachmentName)],
            true
        );

        if ($result[0] != 200) {
            // error
            return false;
        }

        return true;
    }

    public function getContentAttachments(string $contentId) : ?array
    {
        $result = $this->_call(
            'GET', "content/{$contentId}/child/attachment"
        );

        if ($result[0] != 200) {
            // error
            return null;
        }

        try {
            return collect(json_decode($result[1])->results)
                ->map(function ($result) {
                    return [
                        'url' => $result->_links->download,
                        'filename' => $result->title,
                    ];
                })
                ->toArray();
        } catch (\Exception $ex) {
            // parse error
            return null;
        }
    }

    /**
     * Execute a Confluence API request call.
     *
     * @param string $verb
     * @param string $action
     * @param null|array $data
     * @param bool $isFileUpload
     * @return array
     */
    protected function _call(string $verb, string $action, ?array $data = null, bool $isFileUpload = false) : array
    {
        $ch = curl_init();
        $headers = [
            'Accept: application/json',
            'X-Atlassian-Token: nocheck',
        ];
        curl_setopt($ch, CURLOPT_URL, "https://{$this->host}/wiki/rest/api/$action");
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $verb);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_TIMEOUT, 300);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_USERPWD, $this->username . ":" . $this->apiToken);
        curl_setopt($ch, CURLOPT_POST, 1);

        if ($isFileUpload) {
            array_push($headers, 'Content-Type: multipart/form-data');
        } else {
            array_push($headers, 'Content-Type: application/json');
        }

        if (! empty($data)) {
            $data = json_encode($data);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        if (! ($result = curl_exec($ch))) {
            $error = "Confluence API error:  Invalid Response.\r\n" . print_r(curl_getinfo($ch), true);
            \Log::error($error);
            return [-1, $error];
        }
        $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        curl_close($ch);

        $body = substr($result, $header_size);

        return [$responseCode, $body];
    }

    /**
     * Download a confluence attachment to the given file path.
     *
     * @param string $subUrl
     * @param string $localFilename
     * @return bool
     */
    public function download(string $subUrl, string $localFilename) : bool
    {
        $ch = curl_init();
        $headers = [
            'Accept: application/json',
            'X-Atlassian-Token: nocheck',
        ];
        curl_setopt($ch, CURLOPT_URL, "https://{$this->host}/wiki{$subUrl}");
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_TIMEOUT, 300);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_USERPWD, $this->username . ":" . $this->apiToken);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $fp = fopen($localFilename, 'w+');
        curl_setopt($ch, CURLOPT_FILE, $fp);

        if (! ($result = curl_exec($ch))) {
            $error = "Confluence download error:  Invalid Response.\r\n" . print_r(curl_getinfo($ch), true);
            \Log::error($error);
            return false;
        }
        $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        fclose($fp);

        return $responseCode == 200;
    }
}
