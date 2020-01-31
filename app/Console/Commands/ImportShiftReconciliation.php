<?php

namespace App\Console\Commands;

use App\Business;
use App\Businesses\Timezone;
use App\Caregiver;
use App\Client;
use App\Billing\Deposit;
use App\Billing\GatewayTransaction;
use App\Billing\Payment;
use App\Shift;
use Carbon\Carbon;
use Illuminate\Console\Command;
use \PhpOffice\PhpSpreadsheet\IOFactory;


class ImportShiftReconciliation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:shift_reconcile {file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import an internal shift reconciliation spreadsheet into the system.';

    /**
     * Convert empty strings to null values in getValue()
     *
     * @var bool
     */
    protected $allowEmptyStrings = false;

    /**
     * @var \PhpOffice\PhpSpreadsheet\Spreadsheet
     */
    protected $file;

    /**
     * @var \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet
     */
    protected $sheet;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        exit('This command is not compatible with new shift properties.');
        $objPHPExcel = $this->loadFile();

        $this->importShifts();
        $this->importCharges();
        $this->importDeposits();

    }

    public function importShifts()
    {
        $this->output->writeln('Processing SHIFTS..');

        $sheet = $this->loadSheet('SHIFTS');

        if (!$sheet) {
            $this->output->error('Error loading "SHIFTS" sheet.');
            exit();
        }

        $lastRow = $this->getRowCount();
        if (!$lastRow) {
            $this->output->error('Error getting row count for "SHIFTS".  Is this spreadsheet empty?');
            sleep(2);
            return;
        }

        for ($row = 2; $row < $lastRow; $row++) {

            if ($this->isRowEmpty($row)) continue;

            $data['business_id'] = $this->getValue('Business ID', $row);
            $data['client_id'] = $this->getValue('Client ID', $row);
            $data['caregiver_id'] = $this->getValue('CG ID', $row);
            $data['caregiver_rate'] = floatval($this->getValue('CG Rate', $row));
            $data['provider_fee'] = floatval($this->getValue('Provider Fee', $row));
            $data['hours_type'] = $this->getValue('Hours Type', $row) ?? 'default';
            if ($data['hours_type'] == 'OT') {
                $data['hours_type'] = 'overtime';
            }
            $clockIn = $this->getValue('Clocked-In', $row);  // This is in business timezone!!
            $hours  = $this->getValue('Duration', $row);
            $data['status'] = 'PAID';

            try {
                $business = Business::findOrFail($data['business_id']);
                $caregiver = Caregiver::findOrFail($data['caregiver_id']);
                $client = Client::findOrFail($data['client_id']);
            } catch(\Exception $e) {
                $this->output->error('Shifts Row ' . $row . ': could not find a relationship... '  . $e->getMessage());
                continue;
            }

            $clockIn = Carbon::createFromFormat('Y-m-d H:i:s', $clockIn, Timezone::getTimezone($data['business_id']))->setTimezone('UTC');
            $data['checked_in_time'] = $clockIn->format('Y-m-d H:i:s');
            $data['checked_out_time'] = $clockIn->copy()->addMinutes(round($hours * 60))->format('Y-m-d H:i:s');

            // Check for existing shift
            $oldShift = Shift::where('business_id', $data['business_id'])
                ->where('client_id', $data['client_id'])
                ->where('caregiver_id', $data['caregiver_id'])
                ->whereBetween('checked_in_time', [
                    $clockIn->copy()->subMinutes(30),
                    $clockIn->copy()->addMinutes(30)
                ]);

            if ($oldShift->exists()) {
                $this->output->warning('Shifts Row ' . $row . ': Found existing shift ' . $oldShift->first()->id . ' conflicting with ' . $data['checked_in_time']);
                continue;
            }

            print_r($data);
            sleep(2);

            Shift::create($data);
        }
    }

    public function importCharges()
    {
        $this->output->writeln('Processing CHARGES..');
        $sheet = $this->loadSheet('CHARGES');

        if (!$sheet) {
            $this->output->error('Error loading "CHARGES" sheet.');
            sleep(2);
            return;
        }

        $lastRow = $this->getRowCount();
        if (!$lastRow) {
            $this->output->error('Error getting row count for "CHARGES".  Is this spreadsheet empty?');
            sleep(2);
            return;
        }

        for ($row = 2; $row < $lastRow; $row++) {

            if ($this->isRowEmpty($row)) continue;

            $data['business_id'] = $this->getValue('Business ID', $row);
            $data['client_id'] = $this->getValue('Client ID', $row);
            $data['payment_type'] = $this->getValue('Pay Type', $row);
            $data['amount'] = (float) $this->getValue('Amount', $row);
            $data['created_at'] = $this->getValue('created_at', $row);
            $data['business_allotment'] = (float) $this->getValue('business_amt', $row);
            $data['caregiver_allotment'] = (float) $this->getValue('caregiver_amt', $row);
            $data['system_allotment'] = (float) $this->getValue('system_amt', $row);

            // Check for an existing payment
            $date = explode(' ', $data['created_at'])[0];
            $oldPayment = Payment::where('client_id', $data['client_id'])
                ->whereBetween('created_at', [$this->getStartOfWeek($date), $this->getEndOfWeek($date)]);
            if ($oldPayment->exists()) {
                $this->output->warning('Charges Row ' . $row . ': Found existing payment ' . $oldPayment->first()->id . ' conflicting with ' . $date);
                $payment = $oldPayment->first();
            } else {
                // Check for a matching transaction
                $searchForType = 'sale';
                $searchAmount = $data['amount'];
                if ($data['amount'] < 0) {
                    $searchAmount *= -1;
                    $searchForType = 'credit';
                }
                $transaction = GatewayTransaction::where('amount', $searchAmount)
                    ->whereBetween('created_at', [$date . ' 00:00:00', $date . ' 23:59:59'])
                    ->where('transaction_type', $searchForType)
                    ->first();
                if (!$transaction) {
                    $this->output->warning('Charges Row ' . $row . ': Transaction not found for  ' . $searchAmount. '(' . $searchForType . ') on ' . $date);
                }
                $data['transaction_id'] = $transaction->id ?? null;

                // SAVE PAYMENT
                $payment = Payment::create($data);
            }


            $shifts = Shift::where('client_id', $payment->client_id)
                ->whereNull('payment_id')
                ->whereBetween('checked_in_time', [$this->getStartOfWeek($payment->created_at->subWeek()), $this->getEndOfWeek($payment->created_at->subWeek())])
                ->get();

            foreach ($shifts as $shift) {
                $shift->update(['payment_id' => $payment->id]);
            }
        }
    }

    public function importDeposits()
    {
        $this->output->writeln('Processing DEPOSITS..');
        $sheet = $this->loadSheet('DEPOSITS');

        if (!$sheet) {
            $this->output->error('Error loading "DEPOSITS" sheet.');
            sleep(2);
            return;
        }

        $lastRow = $this->getRowCount();
        if (!$lastRow) {
            $this->output->error('Error getting row count for "DEPOSITS".  Is this spreadsheet empty?');
            sleep(2);
            return;
        }

        for ($row = 2; $row < $lastRow; $row++) {

            if ($this->isRowEmpty($row)) continue;

            $data['deposit_type'] = strtolower($this->getValue('deposit_type', $row));
            $data['business_id'] = $this->getValue('business_id', $row);
            $data['caregiver_id'] = $this->getValue('cg_id', $row);
            $data['amount'] = (float) $this->getValue('amount', $row);
            $data['created_at'] = $this->getValue('created_at', $row);
            $data['success'] = $this->getValue('success', $row) ?? 1;

            // Check for an existing deposit
            $date = explode(' ', $data['created_at'])[0];
            $oldDeposit = null;
            if ($data['deposit_type'] === 'caregiver') {
                $oldDeposit = Deposit::where('caregiver_id', $data['caregiver_id'])
                    ->whereBetween('created_at', [$this->getStartOfWeek($date), $this->getEndOfWeek($date)]);
            } elseif ($data['deposit_type'] === 'business') {
                $oldDeposit = Deposit::where('business_id', $data['business_id'])
                    ->whereBetween('created_at', [$this->getStartOfWeek($date), $this->getEndOfWeek($date)]);
            }

            if ($oldDeposit && $oldDeposit->exists()) {
                $this->output->warning('Deposits Row ' . $row . ': Found existing payment ' . $oldDeposit->first()->id . ' conflicting with ' . $date);
                $deposit = $oldDeposit->first();
            } else {
                // Check for a matching transaction
                $searchForType = 'credit';
                $searchAmount = $data['amount'];
                if ($data['amount'] < 0) {
                    $searchAmount *= -1;
                    $searchForType = 'sale';
                }
                $transaction = GatewayTransaction::where('amount', $searchAmount)
                    ->whereBetween('created_at', [$date . ' 00:00:00', $date . ' 23:59:59'])
                    ->where('transaction_type', $searchForType)
                    ->first();
                if (!$transaction) {
                    $this->output->warning('Deposits Row ' . $row . ': Transaction not found for  ' . $searchAmount. '(' . $searchForType . ') on ' . $date);
                }
                $data['transaction_id'] = $transaction->id ?? null;

                print_r($data);

                // SAVE DEPOSIT
                $deposit = Deposit::create($data);
            }

            $shifts = [];
            switch($deposit->deposit_type) {
                case 'caregiver':
                    $shifts = Shift::where('caregiver_id', $deposit->caregiver_id)
                        ->whereBetween('checked_in_time', [$this->getStartOfWeek($deposit->created_at), $this->getEndOfWeek($deposit->created_at)])
                        ->get();
                    break;
                case 'business':
                    $shifts = Shift::where('business_id', $deposit->business_id)
                        ->whereBetween('checked_in_time', [$this->getStartOfWeek($deposit->created_at), $this->getEndOfWeek($deposit->created_at)])
                        ->get();
                    break;
            }

            foreach ($shifts as $shift) {
                $shift->deposits()->attach($deposit);
            }
        }
    }

    public function getStartOfWeek($utc_date)
    {
        Carbon::setWeekStartsAt(Carbon::MONDAY);
        return (new Carbon($utc_date, 'UTC'))
            ->setTimezone('America/New_York')
            ->startOfWeek()
            ->setTimezone('UTC');
     //       ->format('Y-m-d H:i:s');
    }

    public function getEndOfWeek($utc_date)
    {
        Carbon::setWeekStartsAt(Carbon::MONDAY);
        return (new Carbon($utc_date, 'UTC'))
            ->setTimezone('America/New_York')
            ->endOfWeek()
            ->setTimezone('UTC');
            //->format('Y-m-d H:i:s');
    }

    /**
     * @return \PhpOffice\PhpSpreadsheet\Spreadsheet
     */
    public function loadFile()
    {
        if (!$objPHPExcel = \PhpOffice\PhpSpreadsheet\IOFactory::load($this->argument('file'))) {
            $this->output->error('Could not load file: ' . $this->argument('file'));
            exit;
        }
        $this->file = $objPHPExcel;
        return $this->file;
    }

    /**
     * @return \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet
     */
    public function loadSheet($name)
    {
        $this->sheet = $this->file->getSheetByName($name);
        return $this->sheet;
    }

    public function getRowCount()
    {
        $lastRow = (int) $this->sheet->getHighestRow();
        if (!$lastRow) {
            $this->output->error('Error getting row count.  Is this spreadsheet empty?');
            exit;
        }
        return $lastRow;
    }

    public function isRowEmpty($rowNo)
    {
        // Only checks columns A-Z
        $a_z = range('A', 'Z');
        foreach($a_z as $column) {
            $value = $this->sheet->getCell($column . $rowNo)->getValue();
            if ($value !== null && (is_string($value) && trim($value) !== '')) {
                return false;
            }
        }
        return true;
    }

    public function getValue($header, $rowNo)
    {
        $column = $this->findColumn($header);
        if ($column === false) {
            return null;
        }
        $cell = $this->sheet->getCell($column . $rowNo);
        $value = $cell->getValue();

        if(\PhpOffice\PhpSpreadsheet\Shared\Date::isDateTime($cell)) {
            //return date('Y-m-d H:i:s', \PhpOffice\PhpSpreadsheet\Shared\Date::excelToTimestamp($value));
            return date('Y-m-d H:i:s', strtotime($value));
        }

        if (!$this->allowEmptyStrings && is_string($value) && trim($value) === '') {
            return null;
        }

        return $value;
    }

    public function findColumn($header)
    {
        // Get range from A to BZ
        $a_z = range('A', 'Z');
        $range = array_merge(
            $a_z,
            array_map(function($val) { return 'A' . $val; }, $a_z),
            array_map(function($val) { return 'B' . $val; }, $a_z)
        );

        foreach($range as $column) {
            $value = $this->sheet->getCell($column . '1')->getValue();
            if ($value == $header) {
                return $column;
            }
        }

        return false;
    }
}
