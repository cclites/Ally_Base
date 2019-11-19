<?php

namespace App;

use App\Traits\BelongsToOneBusiness;
use App\Contracts\BelongsToBusinessesInterface;

class SalesPerson extends BaseModel implements BelongsToBusinessesInterface
{
    use BelongsToOneBusiness;

    protected $guarded = ['id'];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['nameLastFirst'];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function clients()
    {
        return $this->hasMany(Client::class);
    }

    /**
     * Get only the active sales people.
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeWhereActive($query)
    {
        return $query->where('active', 1);
    }

    /**
     * Concatenate the salesperson's name
     *
     * @return string
     */
    public function fullName()
    {
        return $this->firstname . " " . $this->lastname;
    }

    public function getNameLastFirstAttribute()
    {
        return $this->lastname . ', ' . $this->firstname;
    }

    // **********************************************************
    // ScrubsForSeeding Methods
    // **********************************************************
    use \App\Traits\ScrubsForSeeding;

    /**
     * Get an array of scrubbed data to replace the original.
     *
     * @param \Faker\Generator $faker
     * @param bool $fast
     * @param null|\Illuminate\Database\Eloquent\Model $item
     * @return array
     */
    public static function getScrubbedData(\Faker\Generator $faker, bool $fast, ?\Illuminate\Database\Eloquent\Model $item) : array
    {
        return [
            'email' => $faker->email,
            'lastname' => $faker->lastName,
        ];
    }
}