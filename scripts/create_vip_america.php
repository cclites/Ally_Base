<?php

use Illuminate\Support\Str;

require __DIR__ . '/bootstrap.php';

\DB::beginTransaction();

$chain = \App\BusinessChain::create([
    'name' => 'VIP America',
    'slug' => 'vip-america',
]);

$locations = ['Southeast', 'Central', 'Southwest'];

$users = [
    [
        'firstname' => 'Jill',
        'lastname' => 'Ball',
        'email' => 'jill.ball@vipamerica.com',
        'location' => 'Southeast',
    ],
    [
        'firstname' => 'Brenda',
        'lastname' => 'Pluguez',
        'email' => 'brenda.pluguez@vipamerica.com',
        'location' => 'Southeast',
    ],
    [
        'firstname' => 'Sandra',
        'lastname' => 'Poeppel',
        'email' => 'Sandra.Poeppel@vipamerica.com',
        'location' => 'Southeast',
    ],
    [
        'firstname' => 'Krista',
        'lastname' => 'Sliwka',
        'email' => 'Krista.Sliwka@vipamerica.com',
        'location' => 'Southeast',
    ],
    [
        'firstname' => 'Austin',
        'lastname' => 'Younger',
        'email' => 'Austin.Younger@vipamerica.com',
        'location' => 'Southeast',
    ],
    [
        'firstname' => 'Patricia',
        'lastname' => 'Worrow',
        'email' => 'Patricia.Worrow@vipamerica.com',
        'location' => 'Southeast',
    ],
    [
        'firstname' => 'Maritza',
        'lastname' => 'Karwoski',
        'email' => 'Maritza.Karwoski@vipamerica.com',
        'location' => 'Southeast',
    ],
    [
        'firstname' => 'Jodi',
        'lastname' => 'Oliveira',
        'email' => 'Jodi.Oliveira@vipamerica.com',
        'location' => 'Southeast',
    ],
    [
        'firstname' => 'Cindy',
        'lastname' => 'Sheedy',
        'email' => 'Cindy.sheedy@vipamerica.com',
        'location' => 'Southeast',
    ],
    [
        'firstname' => 'Wendy',
        'lastname' => 'Moniz',
        'email' => 'Wendy.Moniz@vipamerica.com',
        'location' => 'Southeast',
    ],
    [
        'firstname' => 'Ciara',
        'lastname' => 'Harris',
        'email' => 'Ciara.Harris@vipamerica.com',
        'location' => 'Southeast',
    ],
    [
        'firstname' => 'Janice',
        'lastname' => 'Bigby',
        'email' => 'Janice.Bigby@vipamerica.com',
        'location' => 'Southeast',
    ],
    [
        'firstname' => 'Ashley',
        'lastname' => 'Skipper',
        'email' => 'Ashley.Skipper@vipamerica.com',
        'location' => 'Southeast',
    ],
    [
        'firstname' => 'Sally',
        'lastname' => 'Weaver',
        'email' => 'Sally.weaver@vipamerica.com',
        'location' => 'Central',
    ],
    [
        'firstname' => 'Starr',
        'lastname' => 'Reilly',
        'email' => 'Starr.Reilly@vipamerica.com',
        'location' => 'Central',
    ],
    [
        'firstname' => 'Kimberly',
        'lastname' => 'Vanderzee',
        'email' => 'Kimberly.Vanderzee@vipamerica.com',
        'location' => 'Southwest',
    ],
    [
        'firstname' => 'Shanon',
        'lastname' => 'Badders',
        'email' => 'Shanon.Badders@vipamerica.com',
        'location' => 'Southwest',
    ],
    [
        'firstname' => 'Samantha',
        'lastname' => 'Santiago',
        'email' => 'Samantha.Santiago@vipamerica.com',
        'location' => 'Southwest',
    ],
    [
        'firstname' => 'Dennise',
        'lastname' => 'Valesquez',
        'email' => 'Dennise.Valesquez@vipamerica.com',
        'location' => 'Southwest',
    ],
    [
        'firstname' => 'Nicole',
        'lastname' => 'Guzman',
        'email' => 'Nicole.Guzman@vipamerica.com',
        'location' => 'Southwest',
    ],
    [
        'firstname' => 'Jennifer',
        'lastname' => 'Rodriguez',
        'email' => 'Jennifer.Rodriguez@vipamerica.com',
        'location' => 'Southwest',
    ],
    [
        'firstname' => 'Linda',
        'lastname' => 'Mraz',
        'email' => 'Linda.Mraz@vipamerica.com',
        'location' => 'Southwest',
    ],
];

$businesses = [];
foreach($locations as $location) {
    $businesses[$location] = \App\Business::create([
        'name' => $chain->name . ' ' . $location,
        'timezone' => 'America/New_York',
        'chain_id' => $chain->id,
    ]);
}

foreach($users as $user) {
    $user = \App\OfficeUser::create([
        'username' => $user['email'],
        'email' => $user['email'],
        'password' => bcrypt(Str::random()),
        'firstname' => $user['firstname'],
        'lastname' => $user['lastname'],
        'chain_id' => $chain->id,
    ]);

    foreach($businesses as $business) {
        $business->users()->attach($user);
    }
}

\DB::commit();