<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\ScrubsForSeeding;

class ChainClientTypeSettings extends Model
{
    use ScrubsForSeeding;

    protected $table = 'chain_client_type_settings';

    protected $fillable = [
        'medicaid_1099_default',
        'medicaid_1099_edit',
        'medicaid_1099_from',
        'private_pay_1099_default',
        'private_pay_1099_edit',
        'private_pay_1099_from',
        'other_1099_default',
        'other_1099_edit',
        'other_1099_from',
    ];

    public function chain(){
        return $this->belongsTo(BusinessChain::class, 'business_chain_id');
    }

    /**
     * Get an array of scrubbed data to replace the original.
     *
     * @param \Faker\Generator $faker
     * @param bool $fast
     * @param null|\Illuminate\Database\Eloquent\Model $item
     * @return array
     */
    public static function getScrubbedData(\Faker\Generator $faker, bool $fast, \Illuminate\Database\Eloquent\Model $item): array
    {
        return [
            'medicaid_1099_default' => $faker->boolean,
            'medicaid_1099_edit' => $faker->boolean,
            'medicaid_1099_from' => $faker->randomElement(['ally', 'client']),

            'private_pay_1099_default' => $faker->boolean,
            'private_pay_1099_edit' => $faker->boolean,
            'private_pay_1099_from' => $faker->randomElement(['ally', 'client']),

            'other_1099_default' => $faker->boolean,
            'other_1099_edit' => $faker->boolean,
            'other_1099_from' => $faker->randomElement(['ally', 'client']),
        ];
    }
}
