<?php

namespace App;

class CaregiverYearlyEarnings extends BaseModel
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    // **********************************************************
    // RELATIONSHIPS
    // **********************************************************

    /**
     * Get the owning business.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * Get the owning client.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get the owning caregiver.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function caregiver()
    {
        return $this->belongsTo(Caregiver::class);
    }

    // **********************************************************
    // MUTATORS
    // **********************************************************

    // **********************************************************
    // QUERY SCOPES
    // **********************************************************

    /**
     *
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeWhereUsesClientPayer($query)
    {
        return $query->whereHas('client', function ($q) {
            $q->where('caregiver_1099', Caregiver1099Payer::CLIENT());
        });
    }

    /**
     *
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeWhereUsesAllyPayer($query)
    {
        return $query->whereHas('client', function ($q) {
            $q->whereIn('caregiver_1099', [Caregiver1099Payer::ALLY(), Caregiver1099Payer::ALLY_LOCKED()]);
        });
    }

    /**
     *
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @param int $threshold
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeOverThreshold($query, int $threshold)
    {
        return $query->where('earnings', '>=', $threshold);
    }

    // **********************************************************
    // OTHER FUNCTIONS
    // **********************************************************

    /**
     * Returns a list of errors that must be fixed before this
     * earnings report can be converted into a 1099.
     *
     * @return array
     */
    public function getMissing1099Errors() : array
    {
        $errors = [];

        if (empty($this->caregiver->first_name)) {
            $errors[] = "Caregiver First Name";
        }

        if (empty($this->caregiver->last_name)) {
            $errors[] = "Caregiver Last Name";
        }

        if (empty($this->caregiver->ssn)) {
            $errors[] = "Caregiver SSN";
        }

        if (empty($this->caregiver->email) || $this->caregiver->hasNoEmail()) {
            $errors[] = "Caregiver Email";
        }

        if (empty($this->caregiver->address)) {
            $errors[] = "Caregiver Address";
        } else {
            /** @var \App\Address $address */
            $address = $this->caregiver->address;
            if (empty($address->address1)) {
                $errors[] = "Caregiver Street Address";
            }
            if (empty($address->city)) {
                $errors[] = "Caregiver City";
            }
            if (empty($address->state)) {
                $errors[] = "Caregiver State";
            }
            if (empty($address->zip)) {
                $errors[] = "Caregiver Zip";
            }
        }

        if($this->client->caregiver_1099 == Caregiver1099Payer::ALLY()){
          return $errors;
        }

        if (empty($this->client->first_name)) {
            $errors[] = 'Client First Name';
        }

        if (empty($this->client->last_name)) {
            $errors[] = "Client Last Name";
        }

        if (empty($this->client->address)) {
            $errors[] = "Client Address";
        } else {
            /** @var \App\Address $address */
            $address = $this->client->address;
            if (empty($address->address1)) {
                $errors[] = "Client Street Address";
            }
            if (empty($address->city)) {
                $errors[] = "Client City";
            }
            if (empty($address->state)) {
                $errors[] = "Client State";
            }
            if (empty($address->zip)) {
                $errors[] = "Client Zip";
            }
        }

        if (empty($this->client->ssn)) {
            $errors[] = "Client SSN";
        } else if (strlen(str_replace('-', '', $this->client->ssn)) <> 9) {
            $errors[] = "Client SSN Invalid";
        }

        if (empty($this->client->email)) {
            $errors[] = "Client Email";
        }

        return $errors;
    }

    /**
     * Convert yearly earnings record into a Caregiver1099 object.
     *
     * @return Caregiver1099
     */
    public function make1099Record() : Caregiver1099
    {

        $systemSettings = \DB::table('system_settings')->first();

        $allyPayer = ($this->client->caregiver_1099 == Caregiver1099Payer::ALLY()) ? true : false;

        $payerFirstName = $allyPayer ? $systemSettings->company_name : $this->client->first_name;
        $payerLastName = $allyPayer ? "ALLY" : $this->client->last_name;
        $payerAddress1 = $allyPayer ? $systemSettings->company_address1 : $this->client->address->address1;
        $payerAddress2 = $allyPayer ? $systemSettings->company_address2 : $this->client->address->address2;
        $payerCity = $allyPayer ? $systemSettings->company_city : $this->client->address->city;
        $payerState = $allyPayer ? $systemSettings->company_state : $this->client->address->state;
        $payerZip = $allyPayer ? $systemSettings->company_zip : $this->client->address->zip;
        $payerTin = $allyPayer ? $systemSettings->company_ein : $this->client->ssn;

        return Caregiver1099::make([
            'year' => $this->year,
            'business_id' => $this->business_id,
            'payment_total' => $this->earnings,
            'client_id' => $this->client_id,
            'client_first_name' => $payerFirstName,
            'client_last_name' => $payerLastName,
            'client_address1' => $payerAddress1,
            'client_address2' => $payerAddress2,
            'client_city' => $payerCity,
            'client_state' => $payerState,
            'client_zip' => $payerZip,
            'client_ssn' => encrypt($payerTin),
            'caregiver_id' => $this->caregiver_id,
            'caregiver_first_name' => $this->caregiver->first_name,
            'caregiver_last_name' => $this->caregiver->last_name,
            'caregiver_address1' => $this->caregiver->address->address1,
            'caregiver_address2' => $this->caregiver->address->address2,
            'caregiver_city' => $this->caregiver->address->city,
            'caregiver_state' => $this->caregiver->address->state,
            'caregiver_zip' => $this->caregiver->address->zip,
            'caregiver_ssn' => encrypt($this->caregiver->ssn),
            'created_by' => auth()->user()->nameLastFirst(),
            'caregiver_1099_payer' => $this->client->caregiver_1099,
        ]);
    }
}
