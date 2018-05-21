<?php
namespace App\Services;

class Microbilt {
    
    protected $member_id;
    protected $member_password;
    
    /**
     * Create new instance of the Microbilt API class.
     *
     * @param string $member_id
     * @param string $member_password
     */
    public function __construct($member_id, $member_password) 
    {
        $this->member_id = $member_id;
        $this->member_password = $member_password;
    }

    /**
     * Verify a Bank Account and Routing number.
     *
     * @param string $name
     * @param string $account_no
     * @param string $routing_no
     * 
     * @return array
     */
    public function verifyBankAccount($first_last, $account_number, $routing_number)
    {
        $names = self::splitName($first_last);
        if ($names === false) {
            return [
                'valid' => false,
                'exception' => 'Invalid name.',
            ];
        }

        $xml = <<<XML
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:mes="http://schema.microbilt.com/messages/" xmlns:glob="http://schema.microbilt.com/globals" xmlns:v1="http://schema.microbilt.com/messages/MBRVD/v1_0">
<soapenv:Header/>
<soapenv:Body>
    <mes:GetReport> 
        <mes:inquiry>
            <glob:MsgRqHdr>
                <glob:MemberId>{$this->member_id}</glob:MemberId> 
                <glob:MemberPwd>{$this->member_password}</glob:MemberPwd> 
                <glob:RequestType>N</glob:RequestType> 
                <glob:ReasonCode>3</glob:ReasonCode> 
                <glob:RefNum>1</glob:RefNum>
            </glob:MsgRqHdr>
            <glob:PersonInfo xmlns="http://schema.microbilt.com/globals">
                <glob:PersonName> 
                    <glob:LastName>{$names[0]}</glob:LastName> 
                    <glob:FirstName>{$names[1]}</glob:FirstName>
                </glob:PersonName> 
            </glob:PersonInfo>
            <glob:BankAccount xmlns="http://schema.microbilt.com/globals">
                <glob:RoutingNumber>$routing_number</glob:RoutingNumber>
                <glob:AccountNum>$account_number</glob:AccountNum>
                <glob:TypeOfBankAcct>1</glob:TypeOfBankAcct>
            </glob:BankAccount>
            <glob:CheckAmt xmlns="http://schema.microbilt.com/globals">
                <glob:Amt>1</glob:Amt>
            </glob:CheckAmt>
            <glob:RuleNum xmlns="http://schema.microbilt.com/globals">55</glob:RuleNum> 
            <glob:LaneId xmlns="http://schema.microbilt.com/globals">113</glob:LaneId>
        </mes:inquiry> 
    </mes:GetReport>
</soapenv:Body>
</soapenv:Envelope>
XML;

        $url = 'https://creditserver.microbilt.com/WebServices/MBRVD/MBRVD.svc?wsdl';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // TODO: fix to use actual ssl cert
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'SOAPAction: http://schema.microbilt.com/messages/GetReport',
            'Content-Type: text/xml;charset="utf-8"',
            'Accept: text/xml',
        ));
        
        $result = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $response = ['raw' => $result];
        try {
            // parse response
            $xml = self::cleanXml($result);
            $xml = simplexml_load_string($xml) or null;
            if (!$xml) {
                $response['exception'] = 'Could not parse XML response from server.';
                return $response;
            }

            // first check status in header for request error
            $header = $xml->Body->GetReportResponse->GetReportResult->MsgRsHdr;
            if ($header->Status->StatusCode == -1) {
                $response['error'] = trim((string) $header->Status->AdditionalStatus->StatusDesc);
                return $response;
            }

            // parse result
            $report = $xml->Body->GetReportResponse->GetReportResult->RESPONSE;
            $response['decision'] = (string) $report->CONTENT->DECISION->decision;

            if (is_object($report->CONTENT->DECISION->PROPERTIES)) {
                $response['message'] = (string) $report->CONTENT->DECISION->PROPERTIES->property;
            }

            switch($response['decision']) {
                case 'ACCEPT':
                    $response['valid'] = true;
                    break;
                case 'WARNING':
                    $response['valid'] = true;
                    break;
                case 'DECLINE':
                default:
                    $response['valid'] = false;
                    break;
            }
        }
        catch (\Exception $ex) {
            $response['exception'] = $ex->getMessage();
        }

        return $response;
    }

    /**
     * Helper function to strip colons in tags so SimpleXML can parse it.
     *
     * @param [type] $data
     * @return void
     */
    protected static function cleanXml($data) 
    {
        $data = str_ireplace(['<soapenv:', '<mes:', '<glob:', '<s:'], '<', $data);
        $data = str_ireplace(['</soapenv:', '</mes:', '</glob:', '</s:'], '</', $data);
        return $data;
    }

    /**
     * Parses first and last name from single string.  Returns an
     * array [first, last] if successful and false if invalid.
     *
     * @param string $first_last
     * @return mixed
     */
    protected static function splitName($first_last) 
    {
        $names = array_map('trim', explode(' ', $first_last, 2));

        if (count($names) < 2) {
            return false;
        }

        return $names;
    }
}
