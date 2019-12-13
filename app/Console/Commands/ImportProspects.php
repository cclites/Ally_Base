<?php

namespace App\Console\Commands;

use App\Prospect;
use App\PhoneNumber;

class ImportProspects extends ImportClients
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:prospects {business_id} {file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import an excel export of prospects into the system.';

    /**
     * Import the specified row of data from the sheet and return the related model
     *
     * @param int $row
     * @return \Illuminate\Database\Eloquent\Model|false
     * @throws \Exception
     */
    protected function importRow(int $row)
    {
        $data = [
            'firstname' => $this->resolve('First Name', $row),
            'lastname' => $this->resolve('Last Name', $row),
            'client_type' => $this->resolve('Client Type', $row) ?: 'private_pay',
            'date_of_birth' => $this->resolve('Date of Birth', $row),
            'email' => $this->resolve('Email', $row),
            'address1' => $this->resolve('Address1', $row),
            'address2' => $this->resolve('Address2', $row),
            'city' => $this->resolve('City', $row),
            'state' => $this->resolve('State', $row),
            'zip' => $this->resolve('Zip', $row) ?: $this->resolve('PostalCode', $row),
            'country' => 'US',
            'phone' => $this->resolve('Phone', $row) ?: $this->resolve('Phone1', $row),
            // New import fields 2019-12-10:
            'closed_loss' => $this->resolve('ClosedLoss', $row) == 'Y' ? true : false,
        ];

        // Format and validate phone number
        if ($data['phone']) {
            try {
                $number = preg_replace('/[^\d\-]/', '', $data['phone']);
                $phone = new PhoneNumber();
                $phone->input($number);
                $data['phone'] = $phone->number();
            }
            catch (\Exception $e) {
                $data['phone'] = null;
            }
        }

        // Attempt to find referral source
        $data['referral_source_id'] = $this->findOrCreateReferralSource($row);

        /** @var Prospect $caregiver */
        $prospect = $this->business()->prospects()->create($data);
        if ($prospect) {
            return $prospect;
        }

        return false;
    }

    /**
     * Look up the referrer by name or create a new entry.
     *
     * @param int $row
     * @return int|null
     * @throws \PHPExcel_Exception
     */
    public function findOrCreateReferralSource(int $row) : ?int
    {
        $referrer = $this->resolve('Client Referrer', $row);
        $sourceType = $this->resolve('Referral Source Type', $row);

        $source = $this->business->chain->referralSources()
            ->where('contact_name', 'LIKE', $referrer)
            ->first();

        if (empty($source)) {
            $source = $this->business->chain->referralSources()
                ->where('organization', 'LIKE', $referrer)
                ->first();
        }

        if (empty($source)) {
            $source = $this->business->chain->referralSources()->create([
                'type' => 'client',
                'organization' => $referrer,
                'contact_name' => 'Default',
                'active' => 1,
                'is_company' => true,
                'source_type' => $sourceType,
            ]);
        }

        return optional($source)->id;
    }

    /**
     * The message to show before executing the import
     *
     * @return string
     */
    protected function warningMessage()
    {
        return 'Importing prospects into ' . $this->business()->name . '..';
    }

}
