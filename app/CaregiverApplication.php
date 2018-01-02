<?php

namespace App;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Database\Eloquent\Model;

/**
 * App\CaregiverApplication
 *
 * @property int $id
 * @property int $business_id
 * @property int $caregiver_application_status_id
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
 * @property int|null $worked_here_before
 * @property string|null $worked_before_location
 * @property int|null $caregiver_position_id
 * @property string|null $preferred_start_date
 * @property string|null $preferred_days
 * @property string|null $preferred_times
 * @property string|null $preferred_shift_length
 * @property int|null $work_weekends
 * @property int|null $travel_radius
 * @property string|null $vehicle
 * @property int|null $dui
 * @property int|null $reckless_driving
 * @property int|null $moving_violation
 * @property int|null $moving_violation_count
 * @property int|null $accidents
 * @property int|null $accident_count
 * @property string|null $driving_violations_desc
 * @property int|null $felony_conviction
 * @property int|null $theft_conviction
 * @property int|null $drug_conviction
 * @property int|null $violence_conviction
 * @property string|null $criminal_history_desc
 * @property int|null $currently_injured
 * @property int|null $previously_injured
 * @property int|null $lift_25_lbs
 * @property int|null $workmans_comp
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
 * @property-read mixed $name
 * @property-read \App\CaregiverPosition|null $position
 * @property-read \App\CaregiverApplicationStatus $status
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverApplication whereAccidentCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverApplication whereAccidents($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverApplication whereAcknowledgedTerms($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverApplication whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverApplication whereAddress2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverApplication whereBusinessId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverApplication whereCaregiverApplicationStatusId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverApplication whereCaregiverPositionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverApplication whereCellPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverApplication whereCellPhoneProvider($value)
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
class CaregiverApplication extends Model
{
    protected $guarded = ['id'];

    public function position()
    {
        return $this->belongsTo(CaregiverPosition::class, 'caregiver_position_id');
    }

    public function status()
    {
        return $this->belongsTo(CaregiverApplicationStatus::class, 'caregiver_application_status_id');
    }

    ///////////////////////////////////////////
    /// Mutators
    ///////////////////////////////////////////


    public function getNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

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
}
