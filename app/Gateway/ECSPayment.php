<?php
namespace App\Gateway;

use App\Address;
use App\BankAccount;
use App\CreditCard;
use App\Exceptions\PaymentMethodDeclined;
use App\Exceptions\PaymentMethodError;
use App\GatewayTransaction;
use App\PhoneNumber;

/**
 * Class ECSPayment
 * Documented at https://ecspayments.transactiongateway.com/merchants/resources/integration/integration_portal.php
 *
 * @package App\Gateway
 */
class ECSPayment implements ACHDepositInterface, ACHPaymentInterface, CreditCardPaymentInterface, RefundInterface {

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
            config('services.ecs.username'),
            config('services.ecs.password')
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
            + $this->order
        );
    }

    /**
     * @return \App\GatewayTransaction
     * @throws \App\Exceptions\PaymentMethodDeclined
     * @throws \App\Exceptions\PaymentMethodError
     */
    public function post($method)
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
            'declined' => ($response == ECSPayment::DECLINED),
            'cvv_pass' => (!empty($data['cvvresponse']) && in_array($data['cvvresponse'], $this->cvvValidResponses)),
            'avs_pass' => (!empty($data['avsresponse']) && in_array($data['avsresponse'], $this->avsValidResponses)),
            'response_text' => $data['responsetext'] ?? null,
            'response_data' => $raw
        ]);

        $transaction->method()->associate($method);

        $transaction->save();

        if ($response == ECSPayment::ERROR) {
            echo 'Payment method error: ' . $text . "\n";
        }

        return $transaction;
    }


    /**
     * Validate, but do not authorize, the payment method
     *
     * @param \App\BankAccount $account
     *
     * @return GatewayTransaction|false
     * @throws \App\Exceptions\PaymentMethodDeclined|\App\Exceptions\PaymentMethodError
     */
    public function validateAccount(BankAccount $account)
    {
        $this->setParamsFromAccount($account);
        $this->params['type'] = 'validate';
        return $this->post($account);
    }

    /**
     * Deposit (credit) the account with $amount
     *
     * @param \App\BankAccount $account
     * @param float $amount
     * @param string $currency
     * @param string $secCode
     *
     * @return GatewayTransaction|false
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
        return $this->post($account);
    }

    /**
     * Authorize, but do not charge, the payment method
     *
     * @param \App\CreditCard $card
     * @param float $amount
     * @param string $currency
     * @param string $secCode
     *
     * @return GatewayTransaction|false
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
        return $this->post($account);
    }

    /**
     * Charge the payment method
     *
     * @param \App\BankAccount $account
     * @param float $amount
     * @param string $currency
     * @param string $secCode
     *
     * @return GatewayTransaction|false
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
        return $this->post($account);
    }

    /**
     * Validate, but do not authorize, the payment method
     *
     * @param \App\CreditCard $card
     * @param mixed $cvv
     *
     * @return GatewayTransaction|false
     * @throws \App\Exceptions\PaymentMethodDeclined|\App\Exceptions\PaymentMethodError
     */
    public function validateCard(CreditCard $card, $cvv = null)
    {
        $this->setParamsFromCard($card, $cvv);
        $this->params['type'] = 'validate';
        return $this->post($card);
    }

    /**
     * Authorize, but do not charge, the payment method
     *
     * @param \App\CreditCard $card
     * @param float $amount
     * @param string $currency
     * @param mixed $cvv
     *
     * @return GatewayTransaction|false
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
        return $this->post($card);
    }

    /**
     * Charge the payment method
     *
     * @param \App\CreditCard $card
     * @param float $amount
     * @param string $currency
     * @param mixed $cvv
     *
     * @return GatewayTransaction|false
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
        return $this->post($card);
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

        $this->billing['firstname'] = $firstname;
        $this->billing['lastname'] = $firstname;
    }


    /**
     * @param \App\Address $address
     * @return $this
     */
    public function setBillingAddress(Address $address)
    {
        $this->billing['address1'] = $address->address1;
        $this->billing['address2'] = $address->address2;
        $this->billing['city'] = $address->city;
        $this->billing['state'] = $address->state;
        $this->billing['zip'] = $address->zip;
        $this->billing['country'] = $address->country;
        return $this;
    }

    /**
     * @param \App\PhoneNumber $phone
     * @return $this
     */
    public function setBillingPhone(PhoneNumber $phone)
    {
        $this->billing['phone'] = $phone->national_number;
        return $this;
    }

    /**
     * @param \App\GatewayTransaction $transaction
     * @param float $amount
     * @return \App\GatewayTransaction|false
     */
    public function refund(GatewayTransaction $transaction, $amount)
    {
        $this->params['transactionid'] = $transaction->transaction_id;
        $this->params['amount'] = $amount;
        $this->params['type'] = 'refund';
        return $this->post($transaction->method);
    }
}