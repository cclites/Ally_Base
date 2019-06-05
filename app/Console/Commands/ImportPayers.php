<?php

namespace App\Console\Commands;

use App\Address;
use App\Billing\Payer;
use App\BusinessChain;
use App\PhoneNumber;

class ImportPayers extends BaseImport
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:payers {chain_id} {file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import an excel export of Payers';

    /**
     * @var \App\BusinessChain
     */
    protected $businessChain;

    /**
     * Return the current business model for who the data should be imported in to
     *
     * @return \App\BusinessChain
     */
    protected function businessChain()
    {
        if ($this->businessChain) {
            return $this->businessChain;
        }
        return $this->businessChain = BusinessChain::findOrFail($this->argument('chain_id'));
    }

    /**
     * Return the current business model for who the data should be imported in to
     * NOTE: Business Chain should be used for caregivers.  This is only for compatibility with business-only resources.
     *
     * @return \App\Business
     */
    protected function business()
    {
        return $this->businessChain()->businesses->first();
    }

    /**
     * Import the specified row of data from the sheet and return the related model
     *
     * @param int $row
     * @return \Illuminate\Database\Eloquent\Model|false
     * @throws \Exception
     */
    protected function importRow(int $row)
    {
//        $address = $this->parseAddress($this->resolve("Full Address", $row) ?? "");
        $payer = new Payer();
        $payer->payment_method_type = "businesses";
        $payer->chain_id = $this->businessChain()->id;
        $payer->week_start = 1;

        $payer->name = $this->resolve("Payer Name", $row);
        $payer->address1 = $this->resolve("Address 1", $row);
        $payer->address2 = $this->resolve("Address 2", $row);
        $payer->city = $this->resolve("City", $row);
        $payer->state = $this->resolve("State", $row);
        $payer->zip = $this->resolve("Zip", $row);
        $payer->contact_name = $this->resolve('Contact Name', $row);
        $payer->invoice_format = $this->resolve("Invoice Format", $row);

        if ($number = $this->resolve("Phone", $row)) {
            $phone = PhoneNumber::fromInput("phone", $number);
        }
        if ($number = $this->resolve("Fax", $row)) {
            $fax = PhoneNumber::fromInput("phone", $number);
        }
        $payer->phone_number = isset($phone) ? $phone->number() : null;
        $payer->fax_number = isset($fax) ? $fax->number() : null;

        // Prevent Duplicates
        if (Payer::where('chain_id', $payer->chain_id)->where('name', $payer->name)->exists()) {
            $this->output->writeln('Skipping duplicate payer: ' . $payer->name);
            return false;
        }

        return $payer->save() ? $payer : false;
    }

    /**
     * The message to show before executing the import
     *
     * @return string
     */
    protected function warningMessage()
    {
        return 'Importing payers into ' . $this->businessChain()->name . '..';
    }

    /**
     * Return true if the row is empty or should be skipped
     *
     * @param int $row
     * @return bool
     * @throws \PHPExcel_Exception
     */
    protected function emptyRow(int $row)
    {
        return !$this->resolve('Payer Name', $row);
    }

    /**
     * Parse the address field into a usable address.
     *
     * @param string $text
     * @return Address
     */
    protected function parseAddress(string $text): Address
    {
        $lines = explode("\n", $text);
        $address = new Address();
        $address->address1 = $lines[0];
        $address->address2 = count($lines) > 2 ? $lines[1] : null;

        $parts = explode(",", trim(end($lines)));
        if (count($parts) > 1) {
            $address->city = $parts[0];
            $address->state = preg_replace("/[^A-Z]/i", "", $parts[1]);
            $address->zip = preg_replace("/[^0-9]/", "", $parts[1]);
        }

        return $address;
    }
}
