<?php

namespace App;

use App\Contracts\BelongsToChainsInterface;
use App\Traits\BelongsToOneChain;
use App\Traits\HasSSNAttribute;
use App\Signature;
use Carbon\Carbon;

/**
 * \App\CaregiverApplication
 *
 * @property int $id
 * @property string $last_name
 * @property string $first_name
 * @property string|null $middle_initial
 * @property string|null $date_of_birth
 * @property null|string $ssn
 * @property string|null $address
 * @property string|null $address_2
 * @property string|null $city
 * @property string|null $state
 * @property string|null $zip
 * @property string $cell_phone
 * @property string|null $cell_phone_provider
 * @property string|null $home_phone
 * @property string $email
 * @property string|null $emergency_contact_name
 * @property string|null $emergency_contact_phone
 * @property bool $worked_here_before
 * @property string|null $worked_before_location
 * @property string|null $preferred_start_date
 * @property string|null $preferred_days
 * @property string|null $preferred_times
 * @property string|null $preferred_shift_length
 * @property bool $work_weekends
 * @property int|null $travel_radius
 * @property string|null $vehicle
 * @property bool $dui
 * @property bool $reckless_driving
 * @property bool $moving_violation
 * @property int|null $moving_violation_count
 * @property bool $accidents
 * @property int|null $accident_count
 * @property string|null $driving_violations_desc
 * @property bool $felony_conviction
 * @property bool $theft_conviction
 * @property bool $drug_conviction
 * @property bool $violence_conviction
 * @property string|null $criminal_history_desc
 * @property bool $currently_injured
 * @property bool $previously_injured
 * @property bool $lift_25_lbs
 * @property bool $workmans_comp
 * @property string|null $workmans_comp_dates
 * @property string|null $injury_status_desc
 * @property string|null $employer_1_name
 * @property string|null $employer_1_city
 * @property string|null $employer_1_state
 * @property string|null $employer_1_approx_start_date
 * @property string|null $employer_1_approx_end_date
 * @property string|null $employer_1_phone
 * @property string|null $employer_1_job_title
 * @property string|null $employer_1_supervisor_name
 * @property string|null $employer_1_reason_for_leaving
 * @property string|null $employer_2_name
 * @property string|null $employer_2_city
 * @property string|null $employer_2_state
 * @property string|null $employer_2_approx_start_date
 * @property string|null $employer_2_approx_end_date
 * @property string|null $employer_2_phone
 * @property string|null $employer_2_job_title
 * @property string|null $employer_2_supervisor_name
 * @property string|null $employer_2_reason_for_leaving
 * @property string|null $employer_3_name
 * @property string|null $employer_3_city
 * @property string|null $employer_3_state
 * @property string|null $employer_3_approx_start_date
 * @property string|null $employer_3_approx_end_date
 * @property string|null $employer_3_phone
 * @property string|null $employer_3_job_title
 * @property string|null $employer_3_supervisor_name
 * @property string|null $employer_3_reason_for_leaving
 * @property string|null $reference_1_name
 * @property string|null $reference_1_phone
 * @property string|null $reference_1_relationship
 * @property string|null $reference_2_name
 * @property string|null $reference_2_phone
 * @property string|null $reference_2_relationship
 * @property string|null $reference_3_name
 * @property string|null $reference_3_phone
 * @property string|null $reference_3_relationship
 * @property string|null $heard_about
 * @property int $acknowledged_terms
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null $position
 * @property string $status
 * @property int $chain_id
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read \App\BusinessChain $businessChain
 * @property-read \App\Signature $signature
 * @property-read string $masked_ssn
 * @property-read mixed $name
 * @property null|string $w9_ssn
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverApplication forAuthorizedChain(\App\User $authorizedUser = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverApplication forChains($chains)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BaseModel ordered($direction = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverApplication whereAccidentCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverApplication whereAccidents($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverApplication whereAcknowledgedTerms($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverApplication whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverApplication whereAddress2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverApplication whereCellPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverApplication whereCellPhoneProvider($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverApplication whereChainId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverApplication whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverApplication whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverApplication whereCriminalHistoryDesc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverApplication whereCurrentlyInjured($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverApplication whereDateOfBirth($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverApplication whereDrivingViolationsDesc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverApplication whereDrugConviction($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverApplication whereDui($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverApplication whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverApplication whereEmergencyContactName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverApplication whereEmergencyContactPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverApplication whereEmployer1ApproxEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverApplication whereEmployer1ApproxStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverApplication whereEmployer1City($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverApplication whereEmployer1JobTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverApplication whereEmployer1Name($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverApplication whereEmployer1Phone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverApplication whereEmployer1ReasonForLeaving($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverApplication whereEmployer1State($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverApplication whereEmployer1SupervisorName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverApplication whereEmployer2ApproxEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverApplication whereEmployer2ApproxStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverApplication whereEmployer2City($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverApplication whereEmployer2JobTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverApplication whereEmployer2Name($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverApplication whereEmployer2Phone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverApplication whereEmployer2ReasonForLeaving($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverApplication whereEmployer2State($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverApplication whereEmployer2SupervisorName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverApplication whereEmployer3ApproxEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverApplication whereEmployer3ApproxStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverApplication whereEmployer3City($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverApplication whereEmployer3JobTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverApplication whereEmployer3Name($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverApplication whereEmployer3Phone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverApplication whereEmployer3ReasonForLeaving($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverApplication whereEmployer3State($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverApplication whereEmployer3SupervisorName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverApplication whereFelonyConviction($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverApplication whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverApplication whereHeardAbout($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverApplication whereHomePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverApplication whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverApplication whereInjuryStatusDesc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverApplication whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverApplication whereLift25Lbs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverApplication whereMiddleInitial($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverApplication whereMovingViolation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverApplication whereMovingViolationCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverApplication wherePosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverApplication wherePreferredDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverApplication wherePreferredShiftLength($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverApplication wherePreferredStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverApplication wherePreferredTimes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverApplication wherePreviouslyInjured($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverApplication whereRecklessDriving($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverApplication whereReference1Name($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverApplication whereReference1Phone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverApplication whereReference1Relationship($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverApplication whereReference2Name($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverApplication whereReference2Phone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverApplication whereReference2Relationship($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverApplication whereReference3Name($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverApplication whereReference3Phone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverApplication whereReference3Relationship($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverApplication whereSsn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverApplication whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverApplication whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverApplication whereTheftConviction($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverApplication whereTravelRadius($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverApplication whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverApplication whereVehicle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverApplication whereViolenceConviction($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverApplication whereWorkWeekends($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverApplication whereWorkedBeforeLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverApplication whereWorkedHereBefore($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverApplication whereWorkmansComp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverApplication whereWorkmansCompDates($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverApplication whereZip($value)
 * @mixin \Eloquent
 */
class CaregiverApplication extends AuditableModel implements BelongsToChainsInterface
{
    use BelongsToOneChain;
    use HasSSNAttribute;

    protected $guarded = ['id'];

    protected $casts = [
        'worked_here_before' => 'boolean',
        'work_weekends' => 'boolean',
        'dui' => 'boolean',
        'reckless_driving' => 'boolean',
        'moving_violation' => 'boolean',
        'accidents' => 'boolean',
        'felony_conviction' => 'boolean',
        'theft_conviction' => 'boolean',
        'drug_conviction' => 'boolean',
        'violence_conviction' => 'boolean',
        'currently_injured' => 'boolean',
        'previously_injured' => 'boolean',
        'lift_25_lbs' => 'boolean',
        'workmans_comp' => 'boolean',
    ];

    ////////////////////////////////////
    //// Application Statuses
    ////////////////////////////////////

    const STATUS_NEW = 'New';
    const STATUS_OPEN = 'Open';
    const STATUS_CONVERTED = 'Converted';

    ///////////////////////////////////////////
    /// Mutators
    ///////////////////////////////////////////


    public function getNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    ////////////////////////////////////
    //// Instance Methods
    ////////////////////////////////////

    public function updateStatus($status = self::STATUS_OPEN)
    {
        if ($this->status !== self::STATUS_CONVERTED) {
            return $this->update(['status' => $status]);
        }
        return false;
    }

    public function convertToCaregiver()
    {
        return \DB::transaction(function() {
            $caregiver = Caregiver::create([
                'firstname' => $this->first_name,
                'lastname' => $this->last_name,
                'ssn' => $this->ssn,
                'email' => $this->email,
                'username' => Caregiver::getAutoUsername(),
                'date_of_birth' => $this->date_of_birth,
                'password' => bcrypt(random_bytes(32)),
                'application_date' => Carbon::now(),
            ]);

            $this->businessChain->assignCaregiver($caregiver);
            $caregiver->setAvailability([]); // sets default availability

            $address = new Address([
                'address1' => $this->address,
                'address2' => $this->address_2,
                'city' => $this->city,
                'state' => $this->state,
                'zip' => $this->zip,
                'country' => 'US',
                'type' => 'home',
            ]);
            $caregiver->addresses()->save($address);

            $cellPhone = new PhoneNumber(['type' => 'primary']);
            $cellPhone->input($this->cell_phone);
            $caregiver->phoneNumbers()->save($cellPhone);

            if ($this->home_phone) {
                $homePhone = new PhoneNumber(['type' => 'home']);
                $homePhone->input($this->home_phone);
                $caregiver->phoneNumbers()->save($homePhone);
            }

            $this->updateStatus(self::STATUS_CONVERTED);

            return $caregiver;
        });
    }

    public function signature()
    {
        return $this->morphOne(Signature::class, 'signable');
    }
}
