<?php
namespace App\Services;

use QuickBooksOnline\API\Core\OAuth\OAuth2\OAuth2AccessToken;
use QuickBooksOnline\API\Data\IPPIntuitEntity;
use QuickBooksOnline\API\DataService\DataService;
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
    protected $accessToken;

    /**
     * @var \QuickBooksOnline\API\DataService\DataService
     */
    protected $service;

    /**
     * @var bool
     */
    protected $accessTokenUpdated = false;

    /**
     * QuickbooksOnlineService Constructor.
     *
     * @param string $clientId
     * @param string $clientSecret
     * @param string $mode
     * @throws \QuickBooksOnline\API\Exception\SdkException
     */
    public function __construct(string $clientId, string $clientSecret, string $mode = 'sandbox')
    {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->authRedirect = route('business.quickbooks.authorization');
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
     * @param string $code
     * @param string $realmId
     * @return \QuickBooksOnline\API\Core\OAuth\OAuth2\OAuth2AccessToken|null
     * @throws \QuickBooksOnline\API\Exception\SdkException
     * @throws \QuickBooksOnline\API\Exception\ServiceException
     */
    public function getAccessToken(string $code, string $realmId) : ?OAuth2AccessToken
    {
        return $this->service->getOAuth2LoginHelper()
            ->exchangeAuthorizationCodeForToken($code, $realmId);
    }

    /**
     * Check whether the service has updated the access token.
     *
     * @return bool
     */
    public function hasUpdatedAccessToken() : bool
    {
        return $this->accessTokenUpdated;
    }

    /**
     * Get a list of customer from the API.
     *
     * @return array|null
     * @throws \Exception
     */
    public function getCustomers() : ?array
    {
        return $this->autoRefreshToken()
            ->query('SELECT * FROM Customer ORDERBY GivenName');
    }

    /**
     * Get the name of the connected company.
     *
     * @return string
     * @throws \QuickBooksOnline\API\Exception\SdkException
     * @throws \QuickBooksOnline\API\Exception\ServiceException
     */
    public function getCompanyName() : string
    {
        $data = $this->autoRefreshToken()
            ->service
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
        $this->autoRefreshToken();

        if ($result = $this->service->Add(Invoice::create($data))) {
            return $result;
        }

        return null;
    }

    /**
     * Automatically handle token refreshes.
     *
     * @return QuickbooksOnlineService
     * @throws \QuickBooksOnline\API\Exception\SdkException
     * @throws \QuickBooksOnline\API\Exception\ServiceException
     */
    protected function autoRefreshToken() : self
    {
        $period = $this->accessToken->getAccessTokenValidationPeriodInSeconds();
        $now = strtotime(date('Y-m-d H:i:s'));
        $expires = strtotime($this->accessToken->getAccessTokenExpiresAt());

        if ($period + $now > $expires) {
            $this->accessToken = $this->service->getOAuth2LoginHelper()->refreshToken();
            $this->service->updateOAuth2Token($this->accessToken);
            $this->accessTokenUpdated = true;
        }

        return $this;
    }

    /**
     * Query the data service.
     *
     * @param string $query
     * @return array|null
     * @throws \Exception
     */
    protected function query(string $query) : ?array
    {
        return $this->service->Query($query);
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
