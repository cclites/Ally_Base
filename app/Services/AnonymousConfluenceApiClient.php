<?php

namespace App\Services;

/**
 * This is a small abstraction to the Confluence API Client to
 * indicate there is no user and it can only access public URLs.
 *
 * Class AnonymousConfluenceApiClient
 * @package App\Services
 */
class AnonymousConfluenceApiClient extends ConfluenceApiClient
{
    /**
     * AnonymousConfluenceApiClient Constructor.
     * @param null|string $host
     */
    public function __construct(?string $host)
    {
        $this->host = $host;
        $this->username = null;
        $this->apiToken = null;
    }
}
