<?php
namespace App\Gateway;

use App\BankAccount;
use App\CreditCard;
use App\Exceptions\PaymentMethodDeclined;
use App\Exceptions\PaymentMethodError;
use App\GatewayTransaction;

class ECSPayment implements ACHDepositInterface, ACHPaymentInterface, CreditCardPaymentInterface {

    const APPROVED = 1;
    const DECLINED = 2;
    const ERROR = 3;

    public $params = [];
    public $order = [];
    public $billing = [];
    public $shipping = [];
    public $responses = [];

    private $login = [];
    private $cvvValidResponses = [
        'M'
    ];
    private $avsValidResponses = [
        'X',
        'Y',
        'D',
        'M',
        '2',
        '6',
        '3',
        '7',
    ];

    function __construct()
    {
        $this->setLogin(
            env('ECS_PAYMENTS_USERNAME'),
            env('ECS_PAYMENTS_PASSWORD')
        );
    }

    function setLogin($username, $password) {
        $this->login['username'] = $username;
        $this->login['password'] = $password;
    }

    function setOrder($orderid,
        $orderdescription,
        $tax,
        $shipping,
        $ponumber,
        $ipaddress) {
        $this->order['orderid']          = $orderid;
        $this->order['orderdescription'] = $orderdescription;
        $this->order['tax']              = $tax;
        $this->order['shipping']         = $shipping;
        $this->order['ponumber']         = $ponumber;
        $this->order['ipaddress']        = $ipaddress;
    }

    function setBilling($firstname,
        $lastname,
        $company,
        $address1,
        $address2,
        $city,
        $state,
        $zip,
        $country,
        $phone,
        $fax,
        $email,
        $website) {
        $this->billing['firstname'] = $firstname;
        $this->billing['lastname']  = $lastname;
        $this->billing['company']   = $company;
        $this->billing['address1']  = $address1;
        $this->billing['address2']  = $address2;
        $this->billing['city']      = $city;
        $this->billing['state']     = $state;
        $this->billing['zip']       = $zip;
        $this->billing['country']   = $country;
        $this->billing['phone']     = $phone;
        $this->billing['fax']       = $fax;
        $this->billing['email']     = $email;
        $this->billing['website']   = $website;
    }

    function setShipping($firstname,
        $lastname,
        $company,
        $address1,
        $address2,
        $city,
        $state,
        $zip,
        $country,
        $email) {
        $this->shipping['firstname'] = $firstname;
        $this->shipping['lastname']  = $lastname;
        $this->shipping['company']   = $company;
        $this->shipping['address1']  = $address1;
        $this->shipping['address2']  = $address2;
        $this->shipping['city']      = $city;
        $this->shipping['state']     = $state;
        $this->shipping['zip']       = $zip;
        $this->shipping['country']   = $country;
        $this->shipping['email']     = $email;
    }

    // TRANSACTION FUNCTIONS

    function doCapture($transactionid, $amount =0) {

        $query  = "";
        // Login Information
        $query .= "username=" . urlencode($this->login['username']) . "&";
        $query .= "password=" . urlencode($this->login['password']) . "&";
        // Transaction Information
        $query .= "transactionid=" . urlencode($transactionid) . "&";
        if ($amount>0) {
            $query .= "amount=" . urlencode(number_format($amount,2,".","")) . "&";
        }
        $query .= "type=capture";
        return $this->_doPost($query);
    }

    function doVoid($transactionid) {

        $query  = "";
        // Login Information
        $query .= "username=" . urlencode($this->login['username']) . "&";
        $query .= "password=" . urlencode($this->login['password']) . "&";
        // Transaction Information
        $query .= "transactionid=" . urlencode($transactionid) . "&";
        $query .= "type=void";
        return $this->_doPost($query);
    }

    function doRefund($transactionid, $amount = 0) {

        $query  = "";
        // Login Information
        $query .= "username=" . urlencode($this->login['username']) . "&";
        $query .= "password=" . urlencode($this->login['password']) . "&";
        // Transaction Information
        $query .= "transactionid=" . urlencode($transactionid) . "&";
        if ($amount>0) {
            $query .= "amount=" . urlencode(number_format($amount,2,".","")) . "&";
        }
        $query .= "type=refund";
        return $this->_doPost($query);
    }

    function _doPost($query) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://ecspayments.transactiongateway.com/api/transact.php");
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

        curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
        curl_setopt($ch, CURLOPT_POST, 1);

        if (!($data = curl_exec($ch))) {
            return ERROR;
        }
        curl_close($ch);
        unset($ch);
//        print "\n$data\n";
        parse_str($data, $this->responses);
        return $this->responses['response'];
    }

    // ALLY APP INTEGRATION

    function buildQuery()
    {
        return http_build_query(
            $this->login
            + $this->params
            + $this->billing
            + $this->shipping
            + $this->order
        );
    }

    public function post()
    {
        if (!$this->params['type']) {
            throw new PaymentMethodError('Missing transaction type');
        }

        $response = $this->_doPost($this->buildQuery());
        $data = $this->responses;
        $raw = http_build_query($data);
        $text = $data['responsetext'] ?? 'UNKNOWN';

        if (!$response || empty($data['transactionid'])) {
            throw new PaymentMethodError('Error processing transaction: ' . $text);
        }

        $transaction = new GatewayTransaction([
            'gateway_id' => 'ecs',
            'transaction_id' => $data['transactionid'],
            'transaction_type' => $this->params['type'],
            'amount' => $this->params['amount'] ?? 0,
            'success' => ($response == ECSPayment::APPROVED),
            'cvv_pass' => (!empty($data['cvvresponse']) && in_array($data['cvvresponse'], $this->cvvValidResponses)),
            'avs_pass' => (!empty($data['avsresponse']) && in_array($data['avsresponse'], $this->avsValidResponses)),
            'response_text' => $data['responsetext'] ?? null,
            'response_data' => $raw
        ]);

        $transaction->save();

        if ($response == ECSPayment::DECLINED) {
            throw new PaymentMethodDeclined('Payment method declined: ' . $text);
        }
        else if ($response == ECSPayment::ERROR) {
            throw new PaymentMethodError('Payment method error: ' . $text);
        }

        return $transaction;
    }


    /**
     * Validate, but do not authorize, the payment method
     *
     * @param \App\BankAccount $account
     *
     * @return bool
     * @throws \App\Exceptions\PaymentMethodDeclined|\App\Exceptions\PaymentMethodError
     */
    public function validateAccount(BankAccount $account)
    {
        $this->setParamsFromAccount($account);
        $this->params['type'] = 'validate';
        return $this->post();
    }

    /**
     * Deposit (credit) the account with $amount
     *
     * @param \App\BankAccount $account
     * @param float $amount
     * @param string $currency
     * @param string $secCode
     *
     * @return bool
     * @throws \App\Exceptions\PaymentMethodDeclined|\App\Exceptions\PaymentMethodError
     */
    public function depositFunds(BankAccount $account, $amount, $currency = 'USD', $secCode = 'PPD')
    {
        $this->setParamsFromAccount($account, $secCode);
        $this->params += [
            'currency' => $currency,
            'amount' => $amount,
        ];
        $this->params['type'] = 'credit';
        return $this->post();
    }

    /**
     * Authorize, but do not charge, the payment method
     *
     * @param \App\CreditCard $card
     * @param float $amount
     * @param string $currency
     * @param string $secCode
     *
     * @return bool
     * @throws \App\Exceptions\PaymentMethodDeclined|\App\Exceptions\PaymentMethodError
     */
    public function authorizeAccount(BankAccount $account, $amount, $currency = 'USD', $secCode = 'PPD')
    {
        $this->setParamsFromAccount($account, $secCode);
        $this->params += [
            'currency' => $currency,
            'amount' => $amount,
        ];
        $this->params['type'] = 'auth';
        return $this->post();
    }

    /**
     * Charge the payment method
     *
     * @param \App\BankAccount $account
     * @param float $amount
     * @param string $currency
     * @param string $secCode
     *
     * @return bool
     * @throws \App\Exceptions\PaymentMethodDeclined|\App\Exceptions\PaymentMethodError
     */
    public function chargeAccount(BankAccount $account, $amount, $currency = 'USD', $secCode = 'PPD')
    {
        $this->setParamsFromAccount($account, $secCode);
        $this->params += [
            'currency' => $currency,
            'amount' => $amount,
        ];
        $this->params['type'] = 'sale';
        return $this->post();
    }

    /**
     * Validate, but do not authorize, the payment method
     *
     * @param \App\CreditCard $card
     * @param mixed $cvv
     *
     * @return bool
     * @throws \App\Exceptions\PaymentMethodDeclined|\App\Exceptions\PaymentMethodError
     */
    public function validateCard(CreditCard $card, $cvv = null)
    {
        $this->setParamsFromCard($card, $cvv);
        $this->params['type'] = 'validate';
        return $this->post();
    }

    /**
     * Authorize, but do not charge, the payment method
     *
     * @param \App\CreditCard $card
     * @param float $amount
     * @param string $currency
     * @param mixed $cvv
     *
     * @return bool
     * @throws \App\Exceptions\PaymentMethodDeclined|\App\Exceptions\PaymentMethodError
     */
    public function authorizeCard(CreditCard $card, $amount, $currency = 'USD', $cvv = null)
    {
        $this->setParamsFromCard($card, $cvv);
        $this->params += [
            'currency' => $currency,
            'amount' => $amount,
        ];
        $this->params['type'] = 'auth';
        return $this->post();
    }

    /**
     * Charge the payment method
     *
     * @param \App\CreditCard $card
     * @param float $amount
     * @param string $currency
     * @param mixed $cvv
     *
     * @return bool
     * @throws \App\Exceptions\PaymentMethodDeclined|\App\Exceptions\PaymentMethodError
     */
    public function chargeCard(CreditCard $card, $amount, $currency = 'USD', $cvv = null)
    {
        $this->setParamsFromCard($card, $cvv);
        $this->params += [
            'currency' => $currency,
            'amount' => $amount,
        ];
        $this->params['type'] = 'sale';
        return $this->post();
    }

    protected function setParamsFromAccount(BankAccount $account, $secCode = 'PPD') {
        $this->params = array_merge($this->params, [
            'checkname' => $account->name_on_account,
            'checkaba' => $account->routing_number,
            'checkaccount' => $account->account_number,
            'account_holder_type' => $account->account_holder_type,
            'account_type' => $account->account_type,
            'sec_code' => $secCode,
            'payment' => 'check'
        ]);
    }

    protected function setParamsFromCard(CreditCard $card, $cvv=null) {
        $nameOnCard = $card->name_on_card;
        $nameOnCardParts = explode(' ', $nameOnCard);
        $firstname = array_shift($nameOnCardParts);
        $lastname = array_pop($nameOnCardParts);

        $this->params = array_merge($this->params, [
            'firstname' => $firstname,
            'lastname' => $lastname,
            'ccnumber' => $card->number,
            'ccexp' => str_pad($card->expiration_month, 2, '0', STR_PAD_LEFT) . substr($card->expiration_year, -2),
            'cvv' => $cvv ?? '',
            'payment' => 'creditcard'
        ]);
    }
}