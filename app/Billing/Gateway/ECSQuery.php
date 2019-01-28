<?php
namespace App\Billing\Gateway;

class ECSQuery
{
    private $login = [];
    private $constraints = [];

    function __construct()
    {
        $this->setLogin(
            config('services.ecs.username'),
            config('services.ecs.password')
        );
    }

    function setLogin($username, $password)
    {
        $this->login['username'] = $username;
        $this->login['password'] = $password;
    }

    function find($transaction_id)
    {
        $this->reset();
        $this->where('transaction_id', $transaction_id);
        return $this->get();
    }

    function where($field, $value)
    {
        $this->constraints[$field] = $value;
        return $this;
    }

    function get()
    {
        $xml = $this->query();
        return $xml;
    }

    function reset()
    {
        $this->constraints = [];
    }

    private function buildQueryString()
    {
        return http_build_query(
            $this->login
            + $this->constraints
        );
    }

    /**
     * @return \SimpleXMLElement
     */
    private function query()
    {
        $url="https://ecspayments.transactiongateway.com/api/query.php?". $this->buildQueryString();

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

        $responseXML=curl_exec($ch);
        curl_close($ch);

        return new \SimpleXMLElement($responseXML);
    }

}