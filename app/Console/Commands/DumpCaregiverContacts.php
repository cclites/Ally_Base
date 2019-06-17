<?php

namespace App\Console\Commands;

use App\Caregiver;
use App\PhoneNumber;
use Illuminate\Console\Command;

class DumpCaregiverContacts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dump:caregiver-contacts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dump table of caregiver contacts.';

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
        $query = Caregiver::forChains(51)
            ->active()
            ->whereNotSetup();

        $data = $query->get()->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->nameLastFirst,
                'email' => $item->user->email,
                'numbers' => $item->user->phoneNumbers->map(function (PhoneNumber $item) {
                    return $item->number;
                })->implode(', '),
            ];
        })
        ->sortBy('name');

        $headers = ['ID', 'Name', 'Email', 'Phone Number(s)'];
        $this->table($headers, $data);
    }
}
