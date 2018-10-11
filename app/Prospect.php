<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Prospect extends Model
{
    protected $table = 'prospects';
    protected $guarded = ['id'];

    /**
     * Boot the model with the global scope to ignore converted records.
     *
     * @return void
     */
    public static function boot()
    {
        // Add global scope to remove revised shifts from results
        static::addGlobalScope('ignore_clients', function ($builder) {
            $builder->whereNull('client_id');
        });
    }

    ///////////////////////////////////////////
    /// Relationship Methods
    ///////////////////////////////////////////

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    ///////////////////////////////////////////
    /// Mutators
    ///////////////////////////////////////////

    public function getNameAttribute()
    {
        return $this->name();
    }

    public function getNameLastFirstAttribute()
    {
        return $this->nameLastFirst();
    }

    ///////////////////////////////////////////
    /// Instance Methods
    ///////////////////////////////////////////

    public function name()
    {
        return $this->firstname . ' ' . $this->lastname;
    }

    public function nameLastFirst()
    {
        return $this->lastname . ', ' . $this->firstname;
    }

    public function convert($username)
    {
        if ($this->client) {
            return $this->client;
        }

        return \DB::transaction(function () use ($username) {
            $client = $this->business->clients()->make([
                'firstname' => $this->firstname,
                'lastname' => $this->lastname,
                'username' => $username,
                'email' => $this->email ?: (new Client)->getAutoEmail(),  // temporary until we have their ID below
                'date_of_birth' => $this->date_of_birth,
                'client_type' => $this->client_type,
                'password' => bcrypt(str_random(32)),
            ]);

            if (!$this->email) {
                $client->setAutoEmail()->save();
            }

            if ($this->address1) {
                $address = new Address([
                    'address1' => $this->address1,
                    'address2' => $this->address2,
                    'city' => $this->city,
                    'state' => $this->state,
                    'zip' => $this->zip,
                    'country' => $this->country,
                    'type' => 'evv',
                ]);
                $client->addresses()->save($address);
            }

            if ($this->phone) {
                $phone = new PhoneNumber(['type' => 'primary']);
                $phone->input($this->phone);
                $client->phoneNumbers()->save($phone);
            }

            $this->update(['client_id' => $client->id]);
            $this->load('client');

            return $this->client;
        });
    }

    ////////////////////////////////////
    //// Query Scopes
    ////////////////////////////////////

    public function scopeWithConverted(Builder $builder)
    {
        return $builder->withoutGlobalScope('ignore_clients');
    }
}
