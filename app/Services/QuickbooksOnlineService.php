<?php
namespace App\Services;

use QuickBooksOnline\API\Core\OAuth\OAuth2\OAuth2AccessToken;
use QuickBooksOnline\API\DataService\DataService;

class QuickbooksOnlineService
{
    protected $clientId;
    protected $clientSecret;
    protected $authRedirect;
    protected $mode;

    /**
     * QuickbooksOnlineService Constructor.
     *
     * @param string $clientId
     * @param string $clientSecret
     * @param string $mode
     */
    public function __construct(string $clientId, string $clientSecret, string $mode = 'sandbox')
    {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->authRedirect = route('business.quickbooks.authorization');
        $this->mode = $mode;
    }

    /**
     * Get the auth code URL.
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * @throws \QuickBooksOnline\API\Exception\SdkException
     */
    public function getAuthorizationCodeURL()
    {
        $dataService = $this->getDataService();
        $OAuth2LoginHelper = $dataService->getOAuth2LoginHelper();
        return $OAuth2LoginHelper->getAuthorizationCodeURL();
    }

    /**
     * Get the configured DataService object.
     *
     * @param array $data
     * @return DataService
     * @throws \QuickBooksOnline\API\Exception\SdkException
     */
    protected function getDataService($data = [])
    {
        $config = array(
            'auth_mode' => 'oauth2',
            'ClientID' => $this->clientId,
            'ClientSecret' => $this->clientSecret,
            'RedirectURI' => $this->authRedirect,
            'scope' => "com.intuit.quickbooks.accounting",
            'baseUrl' => $this->mode == 'production' ? "Production" : "Development"
        );

        if(!empty($data)) {
            $config = array_merge($config, $data);
        }

        return DataService::Configure($config);
    }

    /**
     * Exchange authorization code for access token.
     *
     * @param string $code
     * @param string $realmId
     * @return OAuth2AccessToken|null
     * @throws \QuickBooksOnline\API\Exception\SdkException
     * @throws \QuickBooksOnline\API\Exception\ServiceException
     */
    public function getAccessToken(string $code, string $realmId) : ?OAuth2AccessToken
    {
        $dataService = $this->getDataService();
        $OAuth2LoginHelper = $dataService->getOAuth2LoginHelper();
        return $OAuth2LoginHelper->exchangeAuthorizationCodeForToken($code, $realmId);
    }
}
