<?php

namespace App\Console\Commands;

use App\Address;
use App\Billing\ClientAuthorization;
use App\Billing\Payer;
use App\Billing\Service;
use App\BusinessChain;
use App\Caregiver;
use App\CaregiverLicense;
use App\Client;
use App\PhoneNumber;
use Carbon\Carbon;

class ImportAuthorizations extends BaseImport
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:authorizations {chain_id} {file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import an excel export of Service Authorizations';

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

    protected function matchClient(int $exportedId): ?Client
    {
        return Client::forBusinesses($this->businessChain()->businesses->pluck('id')->toArray())
            ->whereMeta('Exported_ID', $exportedId)
            ->first();
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
        $id = $this->resolve('Client ID', $row);
        if (!$client = $this->matchClient($id)) {
            $this->output->writeln("No matches for exported client ID $id");
            return false;
        }

        $serviceCode = trim($this->resolve("Service Code", $row));
        $serviceName = $this->resolve("Service Name", $row) ?? $this->resolve("Service Code Desc", $row);
        $service = $this->findOrCreateService($serviceCode, $serviceName);

        $auth = new ClientAuthorization();
        $auth->service_id = $service->id;
        $auth->effective_start = filter_date($this->resolve('Effective Start', $row));
        $auth->effective_end = filter_date($this->resolve('Effective End', $row));
        $auth->unit_type = $this->resolve('Unit Type', $row);
        $auth->period = $this->resolve('Period', $row);
        $auth->units = $this->resolve('Units', $row) ?? $this->resolve("Quantity", $row);
        $auth->notes = $this->resolve("Notes", $row);

        if (!$auth->effective_start || !$auth->effective_end) {
            throw new \Exception("Invalid start or end date on row $row");
        }

        return $client->serviceAuthorizations()->save($auth) ? $auth : false;
    }

    /**
     * The message to show before executing the import
     *
     * @return string
     */
    protected function warningMessage()
    {
        return 'Importing service authorizations into ' . $this->businessChain()->name . '..';
    }

    /**
     * Return true if the row is empty or should be skipped
     *
     * @param int $row
     * @return bool
     */
    protected function emptyRow(int $row)
    {
        return !$this->resolve('Service Code', $row);
    }

    protected function resolvePeriod(int $row, string $cellValue)
    {
        switch($cellValue) {
            case 'Every Week':
            case 'Every Work Week':
                return ClientAuthorization::PERIOD_WEEKLY;
            case 'Every Day':
                return ClientAuthorization::PERIOD_DAILY;
            case 'Every Month':
                return ClientAuthorization::PERIOD_MONTHLY;
            case 'Every Year':
            case 'Range':
                return ClientAuthorization::PERIOD_TERM;
        }

        if (in_array(strtolower($cellValue), ClientAuthorization::allPeriods())) {
            return strtolower($cellValue);
        }

        throw new \Exception("Invalid period found: " . $cellValue);
    }

    protected function resolveEffectiveEnd(int $row, string $cellValue)
    {
        if ($count = $this->resolve("Count", $row) && $period = $this->resolve("Period", $row)) {
            $start = Carbon::parse($this->resolve("Effective Start", $row));
            switch($period) {
                case ClientAuthorization::PERIOD_WEEKLY:
                    return $start->copy()->addWeeks($count)->toDateString();
                case ClientAuthorization::PERIOD_DAILY:
                    return $start->copy()->addDays($count)->toDateString();
                case ClientAuthorization::PERIOD_MONTHLY:
                    return $start->copy()->addMonths($count)->toDateString();
            }
        }

        return $cellValue;
    }

    protected function resolveUnitType(int $row, string $cellValue)
    {
        switch(strtolower($cellValue)) {
            case 'hourly':
                return ClientAuthorization::UNIT_TYPE_HOURLY;
            case 'visit':
            case 'fixed':
                return ClientAuthorization::UNIT_TYPE_FIXED;
            case '15m':
            case '15 min':
                return ClientAuthorization::UNIT_TYPE_FIFTEEN;
        }

        throw new \Exception("Invalid unit type: " . $cellValue);
    }

    protected function findOrCreateService(?string $serviceCode, ?string $serviceName)
    {
        if (!$serviceCode) {
            throw new \Exception("Missing service code");
        }

        if ($service = $this->businessChain()->services()->where('code', $serviceCode)->first()) {
            return $service;
        }

        $service = new Service();
        $service->code = $serviceCode;
        $service->name = $serviceName;
        $this->businessChain()->services()->save($service);

        return $service;
    }
}
