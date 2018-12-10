<?php

use Faker\Generator as Faker;
use App\Admin;
use App\User;

require_once 'UserFactory.php';

$factory->define(Admin::class, function(Faker $faker) {
    return array_merge(userFactory($faker), []);
});