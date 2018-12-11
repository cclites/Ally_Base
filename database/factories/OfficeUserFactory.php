<?php

use Faker\Generator as Faker;
use App\OfficeUser;

require_once 'UserFactory.php';

$factory->define(OfficeUser::class, function(Faker $faker) {
    return array_merge(userFactory($faker), []);
});