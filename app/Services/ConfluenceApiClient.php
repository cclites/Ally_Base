<?php

namespace App\Services;

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
        return $this->post(
            "content/{$contentId}/child/attachment?",
            ['file' => new \CURLFile($filename, mime_content_type($filename), $attachmentName)],
            true
        );
    }

    /**
     * Execute a Confluence POST API Request.
     *
     * @param string $action
     * @param array $data
     * @param bool $isFileUpload
     * @return bool
     */
    protected function post(string $action, array $data, bool $isFileUpload = false) : bool
    {
        $ch = curl_init();
        $headers = [
            'Accept: application/json',
            'X-Atlassian-Token: nocheck',
        ];
        curl_setopt($ch, CURLOPT_URL, "https://{$this->host}/wiki/rest/api/$action");
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_USERPWD, $this->username . ":" . $this->apiToken);
        curl_setopt($ch, CURLOPT_POST, 1);

        if ($isFileUpload) {
            array_push($headers, 'Content-Type: multipart/form-data');
        } else {
            $data = json_encode($data);
            array_push($headers, 'Content-Type: application/json');
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        if (!($data = curl_exec($ch))) {
            dd(curl_getinfo($ch));
            \Log::error('Confluence post error:  Invalid Response. ' . print_r(curl_getinfo($ch), true));
            return false;
        }
        $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $responseCode == 200;
    }
}
