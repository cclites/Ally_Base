<?php

use App\CaregiverApplicationStatus;
use Illuminate\Database\Seeder;

class CaregiverApplicationStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            ['name' => 'New'],
            ['name' => 'Shortlisted'],
            ['name' => 'Saved'],
            ['name' => 'Denied'],
            ['name' => 'Interview Scheduled']
        ];

        foreach ($data as $status) {
            CaregiverApplicationStatus::create($status);
        }
    }
}
