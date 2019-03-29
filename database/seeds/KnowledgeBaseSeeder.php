<?php

use Illuminate\Database\Seeder;

class KnowledgeBaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(\App\Knowledge::class, 50)->create()->each(function ($knowledge) {
            $roles = array_rand(array_flip(['caregiver', 'client', 'office_user']), random_int(1, 3));
            if (! is_array($roles)) {
                $roles = [$roles];
            }

            foreach ($roles as $role) {
                $knowledge->roles()->create(['role' => $role]);
            }
        });
    }
}
