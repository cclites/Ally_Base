<?php

use Faker\Generator as Faker;

if (!function_exists('userFactory')) {
    /**
     * Return the basic fields required to create a user instance
     *
     * @param Faker\Generator $faker
     * @return array
     */
    function userFactory(Faker $faker) {
        $email = $faker->unique()->safeEmail;
        return [
            'firstname' => $faker->firstName,
            'lastname' => $faker->lastName,
            'email' => $email,
            'username' => $email,
            'password' => $password = bcrypt('demo'),
            'remember_token' => str_random(10),
            'date_of_birth' => $faker->date('Y-m-d', '-20 years'),
        ];
    }
}
