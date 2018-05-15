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
     * @return bool
     */
    public function verifyBankAccount($first_last, $account_number, $routing_number)
    {
        $names = self::splitName($first_last);

        if ($names === false) {
            return false;
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
            <glob:RuleNum xmlns="http://schema.microbilt.com/globals">55</glob:RuleNum> 
            <glob:LaneId xmlns="http://schema.microbilt.com/globals">113</glob:LaneId>
        </mes:inquiry> 
    </mes:GetReport>
</soapenv:Body>
</soapenv:Envelope>
XML;

// return '<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/"><s:Body xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema"><GetReportResponse xmlns="http://schema.microbilt.com/messages/"><GetReportResult><MsgRsHdr xmlns="http://schema.microbilt.com/globals"><RqUID>{E87DB715-4F57-4FF1-BAF6-85DE2C97EE0D}</RqUID><Status><StatusCode>0</StatusCode><Severity>Info</Severity><StatusDesc>OK</StatusDesc></Status></MsgRsHdr><RESPONSE transaction="MICR" subTransaction="INQUIRY" timeStamp="2018-05-15 11:11:18" xmlns="http://schema.microbilt.com/messages/MBRVD/v1_0"><REQUESTINGSYSTEM id="1" appName="MBRVD" originationTimestamp="2018-05-15 11:11:17"/><HEADER><HIERARCHY corporation="ALHC" company="ALHC" division="ALHC" market="ALHC"/></HEADER><STATUS action="DONE" type="SUCCESS"><applicationNumber>77905522265C</applicationNumber></STATUS><CONTENT><DECISION><decision code="A">ACCEPT</decision><decisionTimestamp>2018-05-15 11:11:18</decisionTimestamp><REASONS/><PROPERTIES/></DECISION><SERVICEDETAILS/></CONTENT></RESPONSE></GetReportResult></GetReportResponse></s:Body></s:Envelope>';

        // $url = 'https://sdkstage.microbilt.com/WebServices/MBrVd/MBRVD.svc?wsdl';
        $url = 'https://creditserver.microbilt.com/WebServices/MBRVD/MBRVD.svc?wsdl';
        $ch = curl_init($xml);
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
        
        $curlResult = curl_exec($ch);

        parse_str($curlResult, $result);
        // $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        dd($result);
        
        $response = ['raw' => $result];
        try {
            $xml = self::cleanXml($result);
            dd($xml);
            $xml = simplexml_load_string($xml) or null;
            $report = $xml->Body->GetReportResponse->GetReportResult;
            
            $response['action'] = (string) $report->RESPONSE->STATUS->attributes()->action;
            $response['status'] = (string) $report->RESPONSE->STATUS->attributes()->type;
            // $response['decision'] = (string) $report->RESPONSE->CONTENT->DECISION->decision;
            // $response['reasons'] = (array) $report->RESPONSE->CONTENT->DECISION->REASONS;
        }
        catch (\Exception $ex) {
            $response['error'] = $ex->getMessage();
        }

        return $response;
    }

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
