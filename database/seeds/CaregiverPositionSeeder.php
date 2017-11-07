<?php

use Illuminate\Database\Seeder;
use App\CaregiverPosition;

class CaregiverPositionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            ['name' => 'Companion'],
            ['name' => 'HHA'],
            ['name' => 'CNA'],
            ['name' => 'Other']
        ];

        foreach ($data as $position) {
            CaregiverPosition::create($position);
        }
    }
}
