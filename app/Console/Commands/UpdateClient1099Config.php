<?php

namespace App\Console\Commands;

use App\Business;
use App\BusinessChain;
use App\ClientType;
use Illuminate\Console\Command;

class UpdateClient1099Config extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = '1099:sync-clients-to-chain-settings';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This will update the client 1099 settings based on the chain / client type settings.  Note: this does not update the can send option.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if (! $this->confirm('This will override 1099 payer and can edit 1099 settings for every client to the defaults specified by their client type and chain.  This WILL NOT OVERRIDE the SHOULD SEND 1099 option.  Are you sure you want to continue?')) {
            return 0;
        }

        BusinessChain::whereHas('clientTypeSettings')
            ->with('businesses')
            ->each(function ($chain) {
                $chain->businesses->each(function (Business $business) use ($chain) {
                    $business->clients()->where('client_type', ClientType::MEDICAID)
                        ->update([
                            'caregiver_1099' => $chain->clientTypeSettings->medicaid_1099_from, //ally or client
                            'can_edit_send_1099' => $chain->clientTypeSettings->medicaid_1099_edit, //can edit
//                            'send_1099' => $chain->clientTypeSettings->medicaid_1099_default, //send by default
                        ]);

                    $business->clients()->where('client_type', ClientType::PRIVATE_PAY)
                        ->update([
                            'caregiver_1099' => $chain->clientTypeSettings->private_pay_1099_from, //ally or client
                            'can_edit_send_1099' => $chain->clientTypeSettings->private_pay_1099_edit, //can edit
//                            'send_1099' => $chain->clientTypeSettings->private_pay_1099_default, //send by default
                        ]);

                    $business->clients()->whereNotIn('client_type', [ClientType::PRIVATE_PAY, ClientType::MEDICAID])
                        ->update([
                            'caregiver_1099' => $chain->clientTypeSettings->other_1099_from, //ally or client
                            'can_edit_send_1099' => $chain->clientTypeSettings->other_1099_edit, //can edit
//                            'send_1099' => $chain->clientTypeSettings->other_1099_default, //send by default
                        ]);
                });
            });

        return 0;
    }
}
