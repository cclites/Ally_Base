<?php


namespace App\Billing\Gateway;


use App\Billing\Exceptions\PaymentAmountError;
use App\Billing\Payments\Methods\BankAccount;
use Maatwebsite\Excel\Facades\Excel;

class HeritageACHFile
{
    private $transactions = [];
    protected $storage_path;

    public function __construct(string $storage_path = null)
    {
        $this->storage_path = $storage_path ?? storage_path('heritage/exports');
    }

    public function addTransaction(string $id, string $type, BankAccount $account, string $amount)
    {
        if (!is_numeric($amount)) {
            throw new PaymentAmountError("The payment amount is not numeric");
        }
        if ($amount <= 0) {
            throw new PaymentAmountError("The payment amount must be greater than 0.");
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

        $filename = "heritage_export_" . date("Y_m_d_H_i_s_u");
        $format = 'xlsx';
        $filepath = $this->storage_path . DIRECTORY_SEPARATOR . $filename . '.' . $format;

        Excel::create($filename, function($excel) {

            $excel->sheet('Sheet1', function($sheet) {
                $sheet->fromArray($this->transactions);
            });

        })->store($format, $this->storage_path);

        if (!file_exists($filepath)) {
            throw new \Exception("Unable to write Heritage ACH file to: " . $filepath);
        }

        return $filepath;
    }
}