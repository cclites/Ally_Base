<?php

namespace App;

use App\Confirmations\Confirmation;
use App\Contracts\CanBeConfirmedInterface;
use App\Contracts\UserRole;
use App\Mail\ClientConfirmation;
use App\Scheduling\AllyFeeCalculator;
use App\Scheduling\ScheduleAggregator;
use App\Traits\IsUserRole;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Client extends Model implements UserRole, CanBeConfirmedInterface
{
    use IsUserRole;

    protected $table = 'clients';
    public $timestamps = false;
    public $hidden = ['ssn'];
    public $appends = ['payment_type', 'ally_percentage'];
    public $fillable = [
        'business_id',
        'business_fee',
        'client_type',
        'default_payment_type',
        'default_payment_id',
        'backup_payment_type',
        'backup_payment_id',
        'ssn',
        'onboard_status',
        'fee_override',
        'max_weekly_hours',
    ];

    ///////////////////////////////////////////
    /// Relationship Methods
    ///////////////////////////////////////////

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function upcomingPayments()
    {
        return $this->hasMany(PaymentQueue::class);
    }

    public function evvAddress()
    {
        return $this->hasOne(Address::class, 'user_id', 'id')
                    ->where('type', 'evv');
    }

    public function evvPhone()
    {
        return $this->hasOne(PhoneNumber::class, 'user_id', 'id')
                    ->where('type', 'evv');
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function caregivers()
    {
        return $this->belongsToMany(Caregiver::class, 'client_caregivers')
            ->with('user')
            ->withTimestamps()
            ->withPivot([
                'caregiver_hourly_rate',
                'caregiver_daily_rate',
                'provider_hourly_fee',
                'provider_daily_fee',
            ]);
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }

    public function shifts()
    {
        return $this->hasMany(Shift::class);
    }

    public function defaultPayment()
    {
        return $this->morphTo('default_payment');
    }

    public function backupPayment()
    {
        return $this->morphTo('backup_payment', 'backup_payment_type', 'backup_payment_id');
    }

    public function onboardStatusHistory()
    {
        return $this->hasMany(OnboardStatusHistory::class);
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

    /**
     * @return string
     */
    public function getPaymentTypeAttribute()
    {
        return $this->getPaymentType();
    }

    /**
     * @return string
     */
    public function getAllyPercentageAttribute()
    {
        return $this->getAllyPercentage();
    }

    ///////////////////////////////////////////
    /// Other Methods
    ///////////////////////////////////////////

    /**
     * Aggregate schedules for this client and return an array of events
     *
     * @param string|\DateTime $start
     * @param string|\DateTime $end
     * @param bool $onlyStartTime  Only include events matching the start time within the date range, otherwise include events that match start or end time
     *
     * @return array
     */
    public function getEvents($start, $end, $onlyStartTime = false)
    {
        $aggregator = new ScheduleAggregator();
        foreach($this->schedules as $schedule) {
            $title = ($schedule->caregiver) ? $schedule->caregiver->name() : 'No Caregiver Assigned';
            $aggregator->add($title, $schedule);
        }

        return $aggregator->onlyStartTime($onlyStartTime)->events($start, $end);
    }

    public function hasActiveShift()
    {
        return $this->shifts()->whereNull('checked_out_time')->exists();
    }

    public function clearFutureSchedules()
    {
        $yesterday = (new Carbon('yesterday'))->format('Y-m-d');
        $this->schedules()
            ->where('end_date', '>', $yesterday)
            ->update(['end_date' => $yesterday]);
    }

    public function getPaymentMethod($backup = false)
    {
        $method = ($backup) ? $this->backupPayment : $this->defaultPayment;
        if ($method instanceof Business) {
            return $method->paymentAccount;
        }
        return $method;
    }

    /**
     * @param $method
     * @return mixed|null|string
     */
    public function getPaymentType($method = null) {
        switch($this->client_type) {
            case 'private_pay':
            case 'LTCI':
            case 'medicaid':
            case 'VA':
                if (!$method) $method = $this->getPaymentMethod();
                if ($method instanceof BankAccount) {
                    return 'ACH';
                }
                if ($method instanceof CreditCard) {
                    if ($method->type == 'amex') return 'AMEX';
                    return 'CC';
                }
                return 'NONE';
            default:
                return $this->client_type;
        }
    }

    /**
     * @param $method
     * @return float
     */
    public function getAllyPercentage($method = null) {
        return AllyFeeCalculator::getPercentage($this, $method);
    }

    public function sendConfirmationEmail()
    {
        $confirmation = new Confirmation($this);
        $confirmation->touchTimestamp();

        $status = 'emailed_reconfirmation';
        $this->update(['onboard_status' => $status]);
        $history = new OnboardStatusHistory(compact('status'));
        $this->onboardStatusHistory()->save($history);

        \Mail::to($this->email)->send(new ClientConfirmation($this, $this->business));
    }
}
