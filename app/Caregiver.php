<?php

namespace App;

use App\Confirmations\Confirmation;
use App\Contracts\CanBeConfirmedInterface;
use App\Contracts\UserRole;
use App\Exceptions\ExistingBankAccountException;
use App\Mail\CaregiverConfirmation;
use App\Scheduling\ScheduleAggregator;
use App\Traits\IsUserRole;
use Crypt;
use Illuminate\Database\Eloquent\Model;

class Caregiver extends Model implements UserRole, CanBeConfirmedInterface
{
    use IsUserRole;

    protected $table = 'caregivers';
    public $timestamps = false;
    public $hidden = ['ssn'];
    public $fillable = [
        'ssn',
        'bank_account_id',
        'title',
        'hire_date',
        'gender'
    ];

    ///////////////////////////////////////////
    /// Relationship Methods
    ///////////////////////////////////////////

    public function bankAccount()
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function businesses()
    {
        return $this->belongsToMany(Business::class, 'business_caregivers');
    }

    public function clients()
    {
        return $this->belongsToMany(Client::class, 'client_caregivers')
                    ->withTimestamps()
                    ->withPivot([
                        'caregiver_hourly_rate',
                        'caregiver_daily_rate',
                        'provider_hourly_fee',
                        'provider_daily_fee',
                    ]);
    }

    public function deposits()
    {
        return $this->hasMany(Deposit::class);
    }

    public function licenses()
    {
        return $this->hasMany(CaregiverLicense::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function upcomingPayments()
    {
        return $this->hasMany(PaymentQueue::class);
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }

    public function shifts()
    {
        return $this->hasMany(Shift::class);
    }

    public function notes()
    {
        return $this->hasMany(Note::class);
    }

    ///////////////////////////////////////////
    /// Mutators
    ///////////////////////////////////////////

    /**
     * Encrypt ssn on entry
     *
     * @param $value
     */
    public function setSsnAttribute($value)
    {
        $this->attributes['ssn'] = Crypt::encrypt($value);
    }

    /**
     * Decrypt ssn on retrieval
     *
     * @return null|string
     */
    public function getSsnAttribute()
    {
        return empty($this->attributes['ssn']) ? null : Crypt::decrypt($this->attributes['ssn']);
    }

    ///////////////////////////////////////////
    /// Other Methods
    ///////////////////////////////////////////

    /**
     * Retrieve the fake email address for a caregiver that does not have an email address.
     * This should always be a domain in our control that drops the emails to prevent leaking of sensitive information and bounces.
     *
     * @return string
     */
    public function getAutoEmail()
    {
        return $this->id . '@noemail.allyms.com';
    }

    /**
     * Set the generated fake email address for a caregiver that does not have an email address.
     *
     * @return $this
     */
    public function setAutoEmail()
    {
        $this->email = $this->getAutoEmail();
        return $this;
    }

    /**
     * Set the caregiver's primary deposit account
     *
     * @param \App\BankAccount $account
     * @return bool
     * @throws \App\Exceptions\ExistingBankAccountException
     * @throws \Exception
     */
    public function setBankAccount(BankAccount $account)
    {
        if ($account->id && $account->user_id != $this->id) {
            throw new ExistingBankAccountException('Bank account is owned by another user.');
        }

        if (!$account->id) {
            if (!$this->bankAccounts()->save($account)) {
                throw new \Exception('Unable to save bank account to database.');
            }
        }

        return $this->update(['bank_account_id' => $account->id]);
    }

    /**
     * Check if the caregiver is currently clocked in to a shift
     *
     * @return bool
     */
    public function isClockedIn()
    {
        return $this->shifts()->whereNull('checked_out_time')->exists();
    }

    /**
     * If clocked in, return the active shift model
     *
     * @return \App\Shift|null
     */
    public function getActiveShift()
    {
        return $this->shifts()->whereNull('checked_out_time')->first();
    }

    /**
     * Aggregate schedules for this caregiver and return an array of events
     *
     * @param string|\DateTime $start
     * @param string|\DateTime $end
     *
     * @return array
     */
    public function getEvents($start, $end)
    {
        $aggregator = new ScheduleAggregator();
        foreach($this->schedules as $schedule) {
            $title = ($schedule->client) ? $schedule->client->name() : 'Unknown Client';
            $aggregator->add($title, $schedule);
        }

        return $aggregator->events($start, $end);
    }

    public function sendConfirmationEmail()
    {
        $confirmation = new Confirmation($this);
        $confirmation->touchTimestamp();
        \Mail::to($this->email)->send(new CaregiverConfirmation($this, $this->businesses()->first()));
    }

    /**
     * Override name to suffix title
     *
     * @return string
     */
    public function name()
    {
        return trim($this->user->name() . ' ' . $this->title);
    }

    /**
     * Override nameFirstLast to suffix title
     *
     * @return string
     */
    public function nameLastFirst()
    {
        return trim($this->user->nameLastFirst() . ' ' . $this->title);
    }
}
