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
     * @return array|null
     */
    public function getMissing1099Errors() : ?array
    {
        $errors = [];

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
        }
        else if (strlen(str_replace('-', '', $this->client->ssn)) <> 9) {
            $errors[] = "Client SSN Invalid";
        }

        if (empty($this->client->email)) {
            $errors[] = "Client Email";
        }

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

        return $errors;
    }
}
