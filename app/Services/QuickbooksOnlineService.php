<?php
namespace App\Services;

use App\Client;
use App\QuickbooksConnection;
use QuickBooksOnline\API\Core\OAuth\OAuth2\OAuth2AccessToken;
use QuickBooksOnline\API\Data\IPPIntuitEntity;
use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\Facades\Customer;
use QuickBooksOnline\API\Facades\Invoice;

class QuickbooksOnlineService
{
    /**
     * @var string
     */
    protected $clientId;

    /**
     * @var string
     */
    protected $clientSecret;

    /**
     * @var string
     */
    protected $authRedirect;

    /**
     * @var string
     */
    protected $mode;

    /**
     * @var \QuickBooksOnline\API\Core\OAuth\OAuth2\OAuth2AccessToken
     */
    public $accessToken;

    /**
     * @var \QuickBooksOnline\API\DataService\DataService
     */
    protected $service;

    /**
     * QuickbooksOnlineService Constructor.
     *
     * @param string $clientId
     * @param string $clientSecret
     * @param string $authRedirect
     * @param string $mode
     * @throws \QuickBooksOnline\API\Exception\SdkException
     */
    public function __construct(string $clientId, string $clientSecret, string $authRedirect, string $mode = 'sandbox')
    {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->authRedirect = $authRedirect;
        $this->mode = $mode;
        $this->configureDataService();
    }

    /**
     * Set the current OAuth access token.
     *
     * @param OAuth2AccessToken $tokenObject
     * @return self
     * @throws \QuickBooksOnline\API\Exception\SdkException
     */
    public function setAccessToken(OAuth2AccessToken $tokenObject) : self
    {
        $this->accessToken = $tokenObject;
        $this->configureDataService();

        return $this;
    }

    /**
     * Get the auth code URL.
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function getAuthorizationUrl()
    {
        return $this->service->getOAuth2LoginHelper()
            ->getAuthorizationCodeURL();
    }

    /**
     * Exchange authorization code for access token.
     *
     * @param null|string $code
     * @param null|string $realmId
     * @return \QuickBooksOnline\API\Core\OAuth\OAuth2\OAuth2AccessToken|null
     * @throws \QuickBooksOnline\API\Exception\SdkException
     * @throws \QuickBooksOnline\API\Exception\ServiceException
     */
    public function getAccessToken(?string $code, ?string $realmId) : ?OAuth2AccessToken
    {
        if (empty($code) || empty($realmId)) {
            return null;
        }

        return $this->service->getOAuth2LoginHelper()
            ->exchangeAuthorizationCodeForToken($code, $realmId);
    }

    /**
     * Get a list of customer from the API.
     *
     * @return array|null
     * @throws \Exception
     */
    public function getCustomers() : ?array
    {
        return $this->queryAll('SELECT * FROM Customer ORDERBY GivenName');
    }

    /**
     * Get ALL results from a query by paginating the results
     * until there are no more.
     *
     * @param string $query
     * @param int $perPage
     * @return array|null
     * @throws \Exception
     */
    public function queryAll(string $query, int $perPage = 250) : ?array
    {
        $data = collect([]);
        $page = 0;
        $perPage = 250;
        while (true) {
            $offset = $page * $perPage + ($page > 0 ? 1 : 0);
            $results = $this->query($query, $offset, $perPage);

            if (empty($results)) {
                break;
            }

            $data = $data->merge($results);
            $page++;
        }

        return $data->toArray();
    }

    /**
     * Get a list of items from the API.
     *
     * @return array|null
     * @throws \Exception
     */
    public function getItems() : ?array
    {
        return $this->queryAll('SELECT * FROM Item where Type = \'Service\'');
    }

    /**
     * Get the name of the connected company.
     *
     * @return string
     * @throws \QuickBooksOnline\API\Exception\SdkException
     */
    public function getCompanyName() : string
    {
        $data = $this->service
            ->getCompanyInfo();

        return $data->CompanyName;
    }

    /**
     * Create an invoice from array of data.
     *
     * @param array $data
     * @return null|\QuickBooksOnline\API\Data\IPPIntuitEntity
     * @throws \Exception
     */
    public function createInvoice(array $data) : ?IPPIntuitEntity
    {
        if ($result = $this->service->Add(Invoice::create($data))) {
            return $result;
        }

        return null;
    }

    /**
     * Create quickbooks customer from Client object and return
     * an array containing the customer id & name.
     *
     * @param \App\Client $client
     * @param QuickbooksConnection $connection
     * @return array
     * @throws \QuickBooksOnline\API\Exception\IdsException
     */
    public function createCustomer(Client $client, QuickbooksConnection $connection) : array
    {
        $address = $client->getBillingAddress();
        $displayName = $connection->getNameFormat() == QuickbooksConnection::NAME_FORMAT_LAST_FIRST ? $client->nameLastFirst : $client->name;
        $data = [
            'PrimaryEmailAddr' => [
                'Address' => $client->email,
            ],
            'GivenName' => $client->firstname,
            'FamilyName' => $client->lastname,
            'DisplayName' => $displayName,
            'PrimaryPhone' => [
                'FreeFormNumber' => optional($client->evvPhone)->number,
            ],
            'Active' => true,
            'BillAddr' => [
                'Line1' => optional($address)->address1,
                'City' => optional($address)->city,
                'CountrySubDivisionCode' => optional($address)->state,
                'PostalCode' => optional($address)->zip,
                'Lat' => optional($address)->latitude,
                'Long' => optional($address)->longitude,
            ],
        ];

        if ($result = $this->service->Add(Customer::create($data))) {
            return [$result->Id, $result->DisplayName];
        }

        return [null, null];
    }

    /**
     * Automatically handle token refreshes.
     *
     * @return bool
     * @throws \QuickBooksOnline\API\Exception\SdkException
     * @throws \QuickBooksOnline\API\Exception\ServiceException
     */
    public function autoRefreshToken() : bool
    {
        $period = $this->accessToken->getAccessTokenValidationPeriodInSeconds();
        $now = strtotime(date('Y-m-d H:i:s'));
        $expires = strtotime($this->accessToken->getAccessTokenExpiresAt());

        if ($period + $now > $expires) {
            $this->accessToken = $this->service->getOAuth2LoginHelper()->refreshToken();
            $this->service->updateOAuth2Token($this->accessToken);
            return true;
        }

        return false;
    }

    /**
     * Query the data service.
     *
     * @param string $query
     * @param null|int $startPosition Starting page number
     * @param null|int $maxResults Page size
     * @return array|null
     * @throws \Exception
     */
    protected function query(string $query, ?int $startPosition = null, ?int $maxResults = null) : ?array
    {
        return $this->service->Query($query, $startPosition, $maxResults);
    }

    /**
     * Set the configured DataService object.
     *
     * @return void
     * @throws \QuickBooksOnline\API\Exception\SdkException
     */
    protected function configureDataService() : void
    {
        $config = array(
            'auth_mode' => 'oauth2',
            'ClientID' => $this->clientId,
            'ClientSecret' => $this->clientSecret,
            'RedirectURI' => $this->authRedirect,
            'scope' => "com.intuit.quickbooks.accounting",
            'baseUrl' => $this->mode == 'production' ? "Production" : "Development"
        );

        // Automatically fill authentication field when an access token set.
        if (filled($this->accessToken)) {
            $config['accessTokenKey'] = $this->accessToken->getAccessToken();
            $config['refreshTokenKey'] = $this->accessToken->getRefreshToken();
            $config['QBORealmID'] = $this->accessToken->getRealmID();
        }

        $this->service = DataService::Configure($config);
        $this->service->throwExceptionOnError(true);
    }
}
