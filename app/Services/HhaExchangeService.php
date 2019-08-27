<?php

namespace App\Services;

use App\Contracts\SFTPReaderWriterInterface;
use Carbon\Carbon;

class HhaExchangeService
{
    /**
     * @var array
     */
    protected $rows = [];

    /**
     * @var \phpseclib\Net\SFTP
     */
    protected $sftp;

    /**
     * @var string
     */
    protected $taxId;

    /**
     * @var string
     */
    protected $username;

    /**
     * @var string
     */
    protected $password;

    /**
     * HhaExchangeService constructor.
     *
     * @param string $username
     * @param string $password
     * @param string $taxId
     * @throws \Exception
     */
    public function __construct(string $username, string $password, string $taxId)
    {
        $this->taxId = str_replace('-', '', $taxId);
        $this->username = $username;
        $this->password = $password;

        if ($this->username == 'test') {
            return;
        }

        $this->sftp = app(SFTPReaderWriterInterface::class, ['host' => config('services.hha-exchange.sftp_host'), 'port' => config('services.hha-exchange.sftp_port')]);
        if (! $this->login()) {
            throw new \Exception('Your HHA username and password was not accepted.  Please contact HHA and let them know you are unable to login to their SFTP server.');
        }
    }

    /**
     * Log in to the SFTP server.
     *
     * @return bool
     */
    public function login() : bool
    {
        if (! $this->sftp->login($this->username, $this->password)) {
            return false;
        }

        return true;
    }

    /**
     * Get the CSV output
     *
     * @return null|string
     */
    public function getCsv() : ?string
    {
        if (empty($this->rows)) {
            return null;
        }

        // Build header
        $csvArray[] = '"' . implode('","', $this->getHeaderRow()) . '"';

        // Build rows
        foreach($this->rows as $row) {
            $csvArray[] = '"' . implode('","', $row) . '"';
        }

        return implode("\r\n", $csvArray);
    }

    /**
     * Send the CSV file to the remote SFTP server.
     *
     * @param string $filename
     * @return bool
     */
    public function uploadCsv(string $filename) : bool
    {
        return $this->sftp->put(
            config('services.hha-exchange.sftp_directory') . "//Inbox//" . $filename,
            $this->getCsv()
        );
    }

    /**
     * Get the string results from a response CSV file.
     *
     * @param string $filename
     * @return mixed
     */
    public function downloadResponse(string $filename)
    {
        return $this->sftp->get(
            config('services.hha-exchange.sftp_directory') . "//Outbox//Responsefiles//$filename",
            false
        );
    }

    /**
     * Get the csv filename.
     *
     * @return string
     */
    public function getFilename() : string
    {
        // EDI_AgencyTaxID_YYYYMMDDHHMMSS.CSV
        $date = Carbon::now()->setTimezone('America/New_York')->format('YmdHis');
        return "EDI_{$this->taxId}_{$date}.csv";
    }

    /**
     * Add rows to the CSV data.
     *
     * @param null|array $data
     */
    public function addItems(?array $data) : void
    {
        foreach ($data as $item) {
            $this->rows[] = $item;
        }
    }

    /**
     * Get array of header data.
     *
     * @return array
     */
    public function getHeaderRow() : array
    {
        return [
            "Agency Tax ID",
            "Payer ID",
            "Medicaid Number",
            "Caregiver Code",
            "Caregiver First Name",
            "Caregiver Last Name",
            "Caregiver Gender",
            "Caregiver Date of Birth",
            "Caregiver SSN",
            "Schedule ID",
            "Procedure Code",
            "Schedule Start Time",
            "Schedule End Time",
            "Visit Start Time",
            "Visit End Time",
            "EVV Start Time",
            "EVV End Time",
            "Service Location",
            "Duties",
            "Clock-In Phone Number",
            "Clock-In Latitude",
            "Clock-In Longitude",
            "Clock-In EVV Other Info",
            "Clock-Out Phone Number",
            "Clock-Out Latitude",
            "Clock-Out Longitude",
            "Clock-Out EVV Other Info",
            "Invoice Number",
            "Visit Edit Reason Code",
            "Visit Edit Action Taken",
            "Notes",
            "Is Deletion",
            "Invoice Line Item ID",
            "Missed Visit",
            "Missed Visit Reason Code",
            "Missed Visit Action Taken Code",
            "Timesheet Required",
            "Timesheet Approved",
            "User Field 1",
            "User Field 2",
            "User Field 3",
            "User Field 4",
            "User Field 5",
        ];
    }
}
