<?php

use Illuminate\Database\Seeder;

class OnboardingActivitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $activities = [
            ['name' => 'Feed Client', 'category' => 'hands_on'],
            ['name' => 'Bed Rest', 'category' => 'hands_on'],
            ['name' => 'Ambulation', 'category' => 'hands_on'],
            ['name' => 'ROM/Assist with Exercise', 'category' => 'hands_on'],
            ['name' => 'Transfer', 'category' => 'hands_on'],
            ['name' => 'Positioning', 'category' => 'hands_on'],
            ['name' => 'Turning', 'category' => 'hands_on'],
            ['name' => 'Medication Reminders', 'category' => 'hands_on'],
            ['name' => 'Toileting', 'category' => 'hands_on'],
            ['name' => 'Incontinence Care', 'category' => 'hands_on'],
            ['name' => 'Dressing', 'category' => 'hands_on'],
            ['name' => 'Mouth Care', 'category' => 'hands_on'],
            ['name' => 'Shave', 'category' => 'hands_on'],
            ['name' => 'Brush/Comb Hair', 'category' => 'hands_on'],
            ['name' => 'Shampoo Hair', 'category' => 'hands_on'],
            ['name' => 'Bathing', 'category' => 'hands_on'],
            ['name' => 'Transportation', 'category' => 'household'],
            ['name' => 'Laundry for Client', 'category' => 'household'],
            ['name' => 'Light Cleaning', 'category' => 'household'],
            ['name' => 'Errands', 'category' => 'household'],
            ['name' => 'Grocery Shopping', 'category' => 'household'],
            ['name' => 'Serve Meal', 'category' => 'household'],
            ['name' => 'Prepare Meal', 'category' => 'household'],
            ['name' => 'Bed Made', 'category' => 'household']
        ];

        foreach ($activities as $activity) {
            \App\OnboardingActivity::firstOrCreate($activity);
        }
    }
}
