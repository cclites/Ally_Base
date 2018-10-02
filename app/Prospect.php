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
        static::addGlobalScope('ignore_clients', function (Builder $builder) {
            $builder->whereNull('client_id');
        });
    }

    ///////////////////////////////////////////
    /// Relationship Methods
    ///////////////////////////////////////////

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    ///////////////////////////////////////////
    /// Instance Methods
    ///////////////////////////////////////////

    public function convert($username)
    {
        if ($this->client) {
            return $this->client;
        }

        return \DB::transaction(function () use ($username) {
            $client = Client::create([
                'firstname' => $this->firstname,
                'lastname' => $this->lastname,
                'username' => $username,
                'email' => $this->email,
                'date_of_birth' => $this->date_of_birth,
                'client_type' => $this->client_type,
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
