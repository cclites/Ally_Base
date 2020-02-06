<?php

namespace App\Billing\Gateway;

use App\Billing\Exceptions\PaymentAmountError;
use App\Billing\Payments\Methods\BankAccount;
use App\Exports\GenericExport;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class AchExportFile
{
    /**
     * @var array
     */
    protected $transactions = [];

    /**
     * @var string
     */
    protected $storage_path = '';

    /**
     * @var string
     */
    protected $bank = '';

    /**
     * The path of the file after it has been created.
     *
     * @var string
     */
    protected $filepath = '';

    /**
     * AchExportFile constructor.
     * @param string|null $storage_path
     * @param string $bank
     */
    public function __construct(string $storage_path = null, string $bank = 'heritage')
    {
        $this->storage_path = $storage_path ?? 'ach' . DIRECTORY_SEPARATOR . 'exports';
        $this->bank = $bank;
    }

    /**
     * Add transaction line to the file.
     *
     * @param string $id
     * @param string $type
     * @param BankAccount $account
     * @param float $amount
     * @throws PaymentAmountError
     */
    public function addTransaction(string $id, string $type, BankAccount $account, float $amount)
    {
        if (!is_numeric($amount)) {
            throw new PaymentAmountError("The payment amount is not numeric");
        }
        if ($type == 'credit' && $amount <= 0) {
            throw new PaymentAmountError("The payment amount must be greater than 0.");
        }
        if ($type == 'sale' && $amount >= 0) {
            throw new PaymentAmountError("The payment amount must be less than 0.");
        }

        $this->transactions[] = [
            'SSN-ID' => $id . ' ',
            'Name' => $this->sanitizeString($account->getBillingName()),
            'ABA Routing' => $account->getRoutingNumber() . ' ',
            'Account' => $account->getAccountNumber() . ' ',
            'Transaction Type' => $this->sanitizeString($type),
            'Amount' => $amount . ' ',
            'Checking or Savings' => $this->sanitizeString(ucwords($account->getAccountType())),
        ];
    }

    /**
     * Write file output.
     *
     * @param string $fileIdentifier
     * @return string
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function write(string $fileIdentifier = ""): string
    {
        if (!count($this->transactions)) {
            throw new \Exception("There were no transactions to write.");
        }

        if (!\File::isDirectory($this->storage_path)) {
            if (!\File::makeDirectory($this->storage_path, 493, true)) {
                throw new \Exception("Unable to create storage directory: " . $this->storage_path);
            }
        }

        $filename = $this->getBankName() . "_export_" . Carbon::now()->format("Y_m_d_H_i_s_u");
        if (filled($fileIdentifier)) {
            $filename .= "_" . $fileIdentifier;
        }
        $this->filepath = $this->storage_path . DIRECTORY_SEPARATOR . $filename . '.xlsx';

        Excel::store(new GenericExport($this->transactions), $this->filepath);

        if (! \Storage::exists($this->filepath)) {
            throw new \Exception("Unable to write ACH Export file to: " . $this->filepath);
        }

        return \Storage::path($this->filepath);
    }

    /**
     * Upload the created file.
     *
     * @return bool
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function upload() : bool
    {
        if (empty($this->filepath)) {
            // File not yet created.
            return false;
        }

        $filename = basename($this->filepath);
        $file = \Storage::disk('local')->get($this->filepath);

        return \Storage::disk('sftp-ach')->put($filename, $file);
    }

    /**
     * Get the name of the bank used for the export.
     *
     * @return string
     */
    public function getBankName() : string
    {
        return $this->bank;
    }

    /**
     * Remove unwanted chars from a string.  Use this to
     * ensure no unwanted chars appear in the output Heritage file.
     *
     * @param string $str
     * @return string
     */
    public function sanitizeString(string $str) : string
    {
        return preg_replace("/[^A-Za-z ]+/", '', $str);
    }
}