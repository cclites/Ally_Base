<?php

namespace App\Billing\Gateway;

use App\Billing\Exceptions\PaymentAmountError;
use App\Billing\Payments\Methods\BankAccount;
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
     * AchExportFile constructor.
     * @param string|null $storage_path
     * @param string $bank
     */
    public function __construct(string $storage_path = null, string $bank = 'heritage')
    {
        $this->storage_path = $storage_path ?? storage_path('ach/exports');
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
            'SSN-ID' => $id,
            'Name' => $account->getBillingName(),
            'ABA Routing' => $account->getRoutingNumber(),
            'Account' => $account->getAccountNumber(),
            'Transaction Type' => $type,
            'Amount' => $amount,
            'Checking or Savings' => ucwords($account->getAccountType()),
        ];
    }

    /**
     * Write file output.
     *
     * @return string
     * @throws \Exception
     */
    public function write(): string
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
        $format = 'xlsx';
        $filepath = $this->storage_path . DIRECTORY_SEPARATOR . $filename . '.' . $format;

        Excel::create($filename, function($excel) {

            $excel->sheet('Sheet1', function($sheet) {
                $sheet->fromArray($this->transactions);
            });

        })->store($format, $this->storage_path);

        if (!file_exists($filepath)) {
            throw new \Exception("Unable to write ACH Export file to: " . $filepath);
        }

        return $filepath;
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
}