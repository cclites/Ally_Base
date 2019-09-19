<?php

namespace App;

use App\Contracts\BelongsToBusinessesInterface;
use App\Traits\BelongsToBusinesses;
use Illuminate\Database\Eloquent\Builder;

/**
 * App\Prospect
 *
 * @property int $id
 * @property string $firstname
 * @property string $lastname
 * @property string|null $email
 * @property string|null $client_type
 * @property string|null $date_of_birth
 * @property string|null $phone
 * @property string|null $address1
 * @property string|null $address2
 * @property string|null $city
 * @property string|null $state
 * @property string|null $zip
 * @property string $country
 * @property string|null $referred_by
 * @property string|null $last_contacted
 * @property string|null $initial_call_date
 * @property int $had_initial_call
 * @property int $had_assessment_scheduled
 * @property int $had_assessment_performed
 * @property int $needs_contract
 * @property int $expecting_client_signature
 * @property int $needs_payment_info
 * @property int $ready_to_schedule
 * @property int $closed_loss
 * @property int $closed_win
 * @property int $business_id
 * @property int|null $client_id
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property int|null $referral_source_id
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read \App\Business $business
 * @property-read \App\Client|null $client
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Note[] $notes
 * @property-read mixed $name
 * @property-read mixed $name_last_first
 * @property-read mixed $full_address
 * @property-read \App\ReferralSource|null $referralSource
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Prospect forBusinesses($businessIds)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Prospect forRequestedBusinesses($businessIds = null, \App\User $authorizedUser = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Prospect ordered($direction = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Prospect whereAddress1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Prospect whereAddress2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Prospect whereBusinessId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Prospect whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Prospect whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Prospect whereClientType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Prospect whereClosedLoss($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Prospect whereClosedWin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Prospect whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Prospect whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Prospect whereDateOfBirth($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Prospect whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Prospect whereExpectingClientSignature($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Prospect whereFirstname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Prospect whereHadAssessmentPerformed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Prospect whereHadAssessmentScheduled($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Prospect whereHadInitialCall($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Prospect whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Prospect whereInitialCallDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Prospect whereLastContacted($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Prospect whereLastname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Prospect whereNeedsContract($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Prospect whereNeedsPaymentInfo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Prospect wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Prospect whereReadyToSchedule($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Prospect whereReferralSourceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Prospect whereReferredBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Prospect whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Prospect whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Prospect whereZip($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Prospect withConverted()
 * @mixin \Eloquent
 */
class Prospect extends AuditableModel implements BelongsToBusinessesInterface
{
    use BelongsToBusinesses;

    protected $table = 'prospects';
    protected $guarded = ['id'];
    protected $appends = ['full_address', 'nameLastFirst', 'name'];

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

    public function notes()
    {
        return $this->hasMany(Note::class);
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

    public function getFullAddressAttribute()
    {
        return $this->fullAddress();
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

    public function fullAddress()
    {
        $fullAddress = $this->address1 ?: '';

        if (!empty($this->address2)) {
            $fullAddress .= ' ' . $this->address2;
        }

        $fullAddress .= ' ' . $this->city . ', ' . $this->state . ' ' . $this->country . ' ' . $this->zip;

        return $fullAddress;
    }

    public function convert($username)
    {
        if ($this->client) {
            return $this->client;
        }

        return \DB::transaction(function () use ($username) {
            $client = $this->business->clients()->create([
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

            if($this->notes){
                foreach ($this->notes as $note){
                    $note->client_id = $client->id;
                    $note->save();
                }
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

    public function referralSource() {
        return $this->belongsTo('App\ReferralSource');
    }

    /**
     * Return an array of business IDs the entity is attached to
     *
     * @return array
     */
    public function getBusinessIds()
    {
        return [$this->business_id];
    }

    ////////////////////////////////////
    //// Query Scopes
    ////////////////////////////////////

    /**
     * Add a query scope "ordered()" to centralize the control of sorting order of model results in queries
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param string|null $direction
     */
    public function scopeOrdered(Builder $builder, string $direction = null)
    {
        $builder->orderBy('lastname', $direction ?? 'ASC')
            ->orderBy('firstname', $direction ?? 'ASC');
    }

    /**
     * A query scope for filtering results by related business IDs
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param array $businessIds
     * @return void
     */
    public function scopeForBusinesses(Builder $builder, array $businessIds)
    {
        $builder->whereIn('business_id', $businessIds);
    }
}
