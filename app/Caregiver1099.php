<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use App\Caregiver;
use App\Client;
use App\Business;

class Caregiver1099 extends BaseModel
{
    protected $table = 'caregiver_1099s';
    protected $guarded = [];

    // Relations
    public function caregiver(){
        return $this->belongsTo(Caregiver::class);
    }

    public function client(){
        return $this->hasOne(Client::class, 'id', 'client_id');
    }

    public function business(){
        return $this->hasOne(Business::class, 'id', 'business_id');
    }

    public function client_address3(){
        return $this->client_city . ", " . $this->client_state . " " . $this->client_zip;
    }

    public function caregiver_address3(){
        return $this->caregiver_city . ", " . $this->caregiver_state . " " . $this->caregiver_zip;
    }

    public static function getErrors($cg1099){
        $errors = [];

        if(! $cg1099->client_fname){
            $errors[] = "Client First Name";
        }

        if(! $cg1099->client_lname){
            $errors[] = "Client Last Name";
        }

        if(! $cg1099->client_address1){
            $errors[] = "Client Address";
        }

        if(! $cg1099->client_city){
            $errors[] = "Client City";
        }

        if(! $cg1099->client_state){
            $errors[] = "Client State";
        }

        if(! $cg1099->client_zip){
            $errors[] = "Client Zip";
        }

        if(! $cg1099->client_ssn){
            $errors[] = "Client Ssn";
        }

        if(! $cg1099->client_email){
            $errors[] = "Client Email";
        }

        if($cg1099->caregiver_1099 === 'ally'){
            return $errors;
        }

        if(! $cg1099->caregiver_fname){
            $errors[] = "Caregiver First Name";
        }

        if(! $cg1099->caregiver_lname){
            $errors[] = "Caregiver Last Name";
        }

        if(! $cg1099->caregiver_address1){
            $errors[] = "Caregiver Address";
        }

        if(! $cg1099->caregiver_city){
            $errors[] = "Caregiver City";
        }

        if(! $cg1099->caregiver_state){
            $errors[] = "Caregiver State";
        }

        if(! $cg1099->caregiver_zip){
            $errors[] = "Caregiver Zip";
        }

        if(! $cg1099->caregiver_ssn){
            $errors[] = "Caregiver Ssn";
        }

        if(! $cg1099->caregiver_email){
            $errors[] = "Caregiver Email";
        }elseif(strpos($cg1099->caregiver_email, 'noemail') !== false){
            $errors[] = "Caregiver has no Email";
        }

        return $errors;
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
            'client_fname' => $faker->firstName,
            'client_lname' => $faker->lastName,
            //'client_ssn' => '', This will get set via cleanEncrypted1099Data() function
            'client_address1' => $faker->streetAddress,
            'client_address2' => '',
            'client_city' => $faker->city,
            'client_state' => $faker->stateAbbr,
            'client_zip' => $faker->postcode,
            'caregiver_fname' => $faker->firstName,
            'caregiver_lname' => $faker->lastName,
            //'caregiver_ssn' => '', This will get set via cleanEncrypted1099Data() function
            'caregiver_address1' => $faker->streetAddress,
            'caregiver_address2' => '',
            'caregiver_city' => $faker->city,
            'caregiver_state' => $faker->stateAbbr,
            'caregiver_zip' => $faker->postcode,
            'created_by' => $faker->name,
        ];
    }
}
