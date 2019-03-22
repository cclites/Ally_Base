<?php

namespace App\Billing;

use App\AuditableModel;
use Carbon\Carbon;
use App\Shift;

class Claim extends AuditableModel
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    public $with = ['statuses'];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [];

    ///////////////////////////////////////
    /// Claim Statuses
    ///////////////////////////////////////
    const NOT_SENT = 'NOT_SENT';
    const CREATED = 'CREATED';
    const TRANSMITTED = 'TRANSMITTED';
    const RETRANSMITTED = 'RETRANSMITTED';
    const ACCEPTED = 'ACCEPTED';
    const REJECTED = 'REJECTED';

    // **********************************************************
    // RELATIONSHIPS
    // **********************************************************

    /**
     * Get the ClientInvoice relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
    */
    public function invoice()
    {
        return $this->belongsTo(ClientInvoice::class, 'client_invoice_id', 'id');
    }

    /**
     * Get the status relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
    */
    public function statuses()
    {
        return $this->hasMany(ClaimStatus::class);
    }

    // **********************************************************
    // MUTATORS
    // **********************************************************

    /**
     * Get the current balance of the Claim.
     *
     * @return float
     */
    public function getBalanceAttribute()
    {
        // TODO: calculate based on payments
        return $this->amount;
    }

    // **********************************************************
    // QUERY SCOPES
    // **********************************************************

    // **********************************************************
    // OTHER FUNCTIONS
    // **********************************************************

    /**
     * Set the status of the claim, and add to it's status history.
     *
     * @param string $status
     */
    public function updateStatus(string $status) : void
    {
        $this->update(['status' => $status]);
        $this->statuses()->create(['status' => $status]);
    }

    /**
     * Convert claim into hha import row data.
     *
     * @return array
     */
    public function getHhaExchangeData() : array
    {
        $timeFormat = 'Y-m-d H:i:s';
        $data = [];
        $shifts = Shift::whereIn('id', $this->invoice->items->where('invoiceable_type', 'shifts')->pluck('invoiceable_id'))
            ->get();

        foreach ($shifts as $shift) {
            $activities = $shift->activities->pluck('id')->toArray();
            $data[] = [
                $this->invoice->client->business->ein ? str_replace('-', '', $this->invoice->client->business->ein) : '', //    "Agency Tax ID",
                $this->invoice->clientPayer->payer_id, //    "Payer ID",
                $this->invoice->client->business->medicaid_id, //    "Medicaid Number",
                $shift->caregiver_id, //    "Caregiver Code",
                $shift->caregiver->firstname, //    "Caregiver First Name",
                $shift->caregiver->lastname, //    "Caregiver Last Name",
                $shift->caregiver->gender ? strtoupper($shift->caregiver->gender) : '', //    "Caregiver Gender",
                $shift->caregiver->date_of_birth ?? '', //    "Caregiver Date of Birth",
                $shift->caregiver->ssn, //    "Caregiver SSN",
                $shift->id, //    "Schedule ID",
                // TODO: implement Procedure Code
                'Respite Care', //    "Procedure Code",
                $shift->checked_in_time->format($timeFormat), //    "Schedule Start Time",
                $shift->checked_out_time->format($timeFormat), //    "Schedule End Time",
                $shift->checked_in_time->format($timeFormat), //    "Visit Start Time",
                $shift->checked_out_time->format($timeFormat), //    "Visit End Time",
                $shift->checked_in_time->format($timeFormat), //    "EVV Start Time",
                $shift->checked_out_time->format($timeFormat), //    "EVV End Time",
                optional($shift->client->evvAddress)->full_address, //    "Service Location",
                empty($activities) ? '' : implode('|', $activities), //    "Duties",
                $shift->checked_in_number, //    "Clock-In Phone Number",
                $shift->checked_in_latitude, //    "Clock-In Latitude",
                $shift->checked_in_longitude, //    "Clock-In Longitude",
                '', //    "Clock-In EVV Other Info",
                $shift->checked_out_number, //    "Clock-Out Phone Number",
                $shift->checked_out_latitude, //    "Clock-Out Latitude",
                $shift->checked_out_longitude, //    "Clock-Out Longitude",
                '', //    "Clock-Out EVV Other Info",
                $this->client_invoice_id, //    "Invoice Number",
                '', //    "Visit Edit Reason Code",
                '', //    "Visit Edit Action Taken",
                '', //    "Notes",
                'N', //    "Is Deletion",
                '', //    "Invoice Line Item ID",
                'N', //    "Missed Visit",
                '', //    "Missed Visit Reason Code",
                '', //    "Missed Visit Action Taken Code",
                '', //    "Timesheet Required",
                '', //    "Timesheet Approved",
                '', //    "User Field 1",
                '', //    "User Field 2",
                '', //    "User Field 3",
                '', //    "User Field 4",
                '', //    "User Field 5",
            ];
        }

        return $data;
    }
}
