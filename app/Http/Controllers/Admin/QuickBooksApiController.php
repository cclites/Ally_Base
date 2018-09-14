<?php

namespace App\Http\Controllers\Admin;

use Session;
use URL;
use Redirect;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

use QuickBooksOnline\API\DataService\DataService;
use QuickBooksOnline\API\Core\Http\Serialization\XmlObjectSerializer;
use QuickBooksOnline\API\Facades\Invoice;

class QuickBooksApiController extends Controller
{
    public function index() {
        $auth = false;
        if(session()->get('access')) {
            $access = unserialize(session()->get('access'));
            $auth = true;
        }

        $dataService = $this->getDataServiceMerged();
        $invoices = json_encode($dataService->Query("SELECT * FROM Invoice"));
        $customers = json_encode($dataService->Query("SELECT * FROM Customer ORDERBY GivenName"));
        $authorization = json_encode(['auth' => $auth]);

        return view('admin.quickbooks-api.index', compact('authorization', 'invoices', 'customers'));
    }

    public function getInvoices() {
        $dataService = $this->getDataServiceMerged();
        return $dataService->Query("SELECT * FROM Invoice");
    }

    public function createInvoice(Request $request) {
        $request->validate([
            'customer' => 'required',
            'amount' => 'required|numeric',
        ]);

        $data = $request->all();
        $dataService = $this->getDataServiceMerged();
        $dataService->throwExceptionOnError(true);

        $invoiceToCreate = Invoice::create([
            "DocNumber" => "101",
            "Line" => [
                [
                    "Description" => $data['description'],
                    "Amount" => (int) $data['amount'],
                    "DetailType" => "SalesItemLineDetail",
                    "SalesItemLineDetail" => [
                        "ItemRef" => [
                            "value" => 1,
                            "name" => "Services"
                        ]
                    ]
                ]
            ],
            "CustomerRef" => [
                "value" => $data['customer'],
                "name" => $data['customer_name']
            ]
        ]);

        $dataService->Add($invoiceToCreate);
        $error = $dataService->getLastError();

        if($error) {
            return response()->json($error->getResponseBody(), $error->getHttpStatusCode());
        } else {
            $invoices = json_encode($dataService->Query("SELECT * FROM Invoice"));
            return response()->json($invoices, 200);
        }
    }

    public function connection() {
        $dataService = $this->getDataService();

        $OAuth2LoginHelper = $dataService->getOAuth2LoginHelper();
        $authorizationCodeUrl = $OAuth2LoginHelper->getAuthorizationCodeURL();

        return redirect($authorizationCodeUrl);
    }

    public function authorization(Request $request) {
        $data = $request->all();
        $connect = 'failed';

        if(!empty($data)) {
            $dataService = $this->getDataService();
            $OAuth2LoginHelper = $dataService->getOAuth2LoginHelper();
            $access = $OAuth2LoginHelper->exchangeAuthorizationCodeForToken($data['code'], $data['realmId']);

            session()->put('access', serialize($access));
            $connect = 'connected';
        }

        return redirect('admin/quickbooks-api')->with('connect', json_encode(['status' => $connect]));
    }

    private function checkAccessToken() {
        $access = unserialize(Session::get('access'));
        $dataService = $this->getDataService([
            'accessTokenKey' => $access->getAccessToken(),
            'refreshTokenKey' => $access->getRefreshToken(),
            'QBORealmID' => $access->getRealmID()
        ]);

        $period = $access->getAccessTokenValidationPeriodInSeconds();
        $now = strtotime(date('Y-m-d H:i:s'));
        $expires = strtotime($access->getAccessTokenExpiresAt());

        if($period + $now > $expires) {
            $OAuth2LoginHelper = $dataService->getOAuth2LoginHelper();
            $refreshedAccessTokenObj = $OAuth2LoginHelper->refreshToken();
            $dataService->updateOAuth2Token($refreshedAccessTokenObj);

            session()->put('access', serialize($refreshedAccessTokenObj));

            return  $refreshedAccessTokenObj;
        }

        return $access;
    }

    private function getDataService($data = []) {
        $config = array(
            'auth_mode' => 'oauth2',
            'ClientID' => "Q0mb9dx2UVRhA6DgUFeVH8xfJCRd0FloGxBxZMgCQg8Tc4YlJA",
            'ClientSecret' => "7s5i2I4dm4txjTGQU1elNriVQuAWskIUv8HadQ8r",
            'RedirectURI' => 'http://krioscare.loc/admin/quickbooks-api/authorization',
            'scope' => "com.intuit.quickbooks.accounting",
            'baseUrl' => "Development"
        );

        if(!empty($data)) {
            $config = array_merge($config, $data);
        }

        return DataService::Configure($config);
    }

    private function getDataServiceMerged() {
        $access = $this->checkAccessToken();
        return $this->getDataService([
            'accessTokenKey' => $access->getAccessToken(),
            'refreshTokenKey' => $access->getRefreshToken(),
            'QBORealmID' => $access->getRealmID()
        ]);
    }

}
