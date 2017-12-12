<?php
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App{
/**
 * App\Activity
 *
 * @property int $id
 * @property int|null $business_id
 * @property string|null $code
 * @property string|null $name
 * @property string|null $description
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \App\Business|null $business
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Activity whereBusinessId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Activity whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Activity whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Activity whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Activity whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Activity whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Activity whereUpdatedAt($value)
 */
	class Activity extends \Eloquent {}
}

namespace App{
/**
 * App\Address
 *
 * @property int $id
 * @property int $user_id
 * @property string|null $type
 * @property string|null $address1
 * @property string|null $address2
 * @property string|null $city
 * @property string|null $state
 * @property string|null $country
 * @property string|null $zip
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property float|null $latitude
 * @property float|null $longitude
 * @property-read \App\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Address whereAddress1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Address whereAddress2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Address whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Address whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Address whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Address whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Address whereLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Address whereLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Address whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Address whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Address whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Address whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Address whereZip($value)
 */
	class Address extends \Eloquent {}
}

namespace App{
/**
 * App\Admin
 *
 * @property int $id
 * @property string|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Address[] $addresses
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\BankAccount[] $bankAccounts
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\CreditCard[] $creditCards
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Document[] $documents
 * @property-read mixed $date_of_birth
 * @property-read mixed $email
 * @property-read mixed $first_name
 * @property-read mixed $last_name
 * @property-read mixed $name
 * @property-read mixed $name_last_first
 * @property-read mixed $username
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\PhoneNumber[] $phoneNumbers
 * @property-read \App\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Admin whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Admin whereId($value)
 */
	class Admin extends \Eloquent {}
}

namespace App{
/**
 * App\BankAccount
 *
 * @property int $id
 * @property int|null $user_id
 * @property string|null $nickname
 * @property mixed $routing_number
 * @property mixed $account_number
 * @property string $account_type
 * @property string $account_holder_type
 * @property string $name_on_account
 * @property int $verified
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read mixed $last_four
 * @property-read \App\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BankAccount whereAccountHolderType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BankAccount whereAccountNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BankAccount whereAccountType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BankAccount whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BankAccount whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BankAccount whereNameOnAccount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BankAccount whereNickname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BankAccount whereRoutingNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BankAccount whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BankAccount whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BankAccount whereVerified($value)
 */
	class BankAccount extends \Eloquent {}
}

namespace App{
/**
 * App\Business
 *
 * @property int $id
 * @property string $name
 * @property string $type
 * @property int|null $bank_account_id
 * @property int|null $active
 * @property string|null $address1
 * @property string|null $address2
 * @property string|null $city
 * @property string|null $state
 * @property string|null $zip
 * @property string|null $country
 * @property string|null $phone1
 * @property string|null $phone2
 * @property float|null $default_commission_rate
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string $timezone
 * @property int|null $payment_account_id
 * @property int $scheduling
 * @property float $mileage_rate
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Activity[] $activities
 * @property-read \App\BankAccount|null $bankAccount
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\CarePlan[] $carePlans
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\CaregiverApplication[] $caregiverApplications
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Caregiver[] $caregivers
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Client[] $clients
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Client[] $clientsUsingProviderPayment
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Deposit[] $deposits
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Note[] $notes
 * @property-read \App\BankAccount|null $paymentAccount
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Payment[] $payments
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Schedule[] $schedules
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Shift[] $shifts
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\PaymentQueue[] $upcomingPayments
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\OfficeUser[] $users
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business whereAddress1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business whereAddress2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business whereBankAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business whereDefaultCommissionRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business whereMileageRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business wherePaymentAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business wherePhone1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business wherePhone2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business whereScheduling($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business whereTimezone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business whereZip($value)
 */
	class Business extends \Eloquent {}
}

namespace App{
/**
 * App\Caregiver
 *
 * @property int $id
 * @property null|string $ssn
 * @property int|null $bank_account_id
 * @property string|null $title
 * @property string|null $deleted_at
 * @property string|null $hire_date
 * @property string|null $gender
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Address[] $addresses
 * @property-read \App\BankAccount|null $bankAccount
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\BankAccount[] $bankAccounts
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Business[] $businesses
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Client[] $clients
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\CreditCard[] $creditCards
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Deposit[] $deposits
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Document[] $documents
 * @property-read mixed $date_of_birth
 * @property-read mixed $email
 * @property-read mixed $first_name
 * @property-read mixed $last_name
 * @property-read mixed $name
 * @property-read mixed $name_last_first
 * @property-read mixed $username
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\CaregiverLicense[] $licenses
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Note[] $notes
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Payment[] $payments
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\PhoneNumber[] $phoneNumbers
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Schedule[] $schedules
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Shift[] $shifts
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\PaymentQueue[] $upcomingPayments
 * @property-read \App\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Caregiver whereBankAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Caregiver whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Caregiver whereGender($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Caregiver whereHireDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Caregiver whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Caregiver whereSsn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Caregiver whereTitle($value)
 */
	class Caregiver extends \Eloquent {}
}

namespace App{
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
 */
	class CaregiverApplication extends \Eloquent {}
}

namespace App{
/**
 * App\CaregiverApplicationStatus
 *
 * @property int $id
 * @property string $name
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\CaregiverApplication[] $caregiverApplications
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverApplicationStatus whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverApplicationStatus whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverApplicationStatus whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverApplicationStatus whereUpdatedAt($value)
 */
	class CaregiverApplicationStatus extends \Eloquent {}
}

namespace App{
/**
 * App\CaregiverLicense
 *
 * @property int $id
 * @property int $caregiver_id
 * @property string $name
 * @property string $description
 * @property string $expires_at
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \App\Caregiver $caregiver
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverLicense whereCaregiverId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverLicense whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverLicense whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverLicense whereExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverLicense whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverLicense whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverLicense whereUpdatedAt($value)
 */
	class CaregiverLicense extends \Eloquent {}
}

namespace App{
/**
 * App\CaregiverPosition
 *
 * @property int $id
 * @property string $name
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\CaregiverApplication[] $caregiverApplications
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverPosition whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverPosition whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverPosition whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CaregiverPosition whereUpdatedAt($value)
 */
	class CaregiverPosition extends \Eloquent {}
}

namespace App{
/**
 * App\CarePlan
 *
 * @property int $id
 * @property string $name
 * @property int $business_id
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Activity[] $activities
 * @property-read \App\Business $business
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\CarePlan onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlan whereBusinessId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlan whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlan whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlan whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlan whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlan whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\CarePlan withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\CarePlan withoutTrashed()
 */
	class CarePlan extends \Eloquent {}
}

namespace App{
/**
 * App\Client
 *
 * @property int $id
 * @property int $business_id
 * @property float|null $business_fee
 * @property string|null $default_payment_type
 * @property string|null $default_payment_id
 * @property string|null $backup_payment_type
 * @property string|null $backup_payment_id
 * @property string $client_type
 * @property null|string $ssn
 * @property string|null $onboard_status
 * @property string|null $deleted_at
 * @property float|null $fee_override
 * @property float $max_weekly_hours
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Address[] $addresses
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $backupPayment
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\BankAccount[] $bankAccounts
 * @property-read \App\Business $business
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Caregiver[] $caregivers
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\CreditCard[] $creditCards
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $defaultPayment
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Document[] $documents
 * @property-read \App\Address $evvAddress
 * @property-read \App\PhoneNumber $evvPhone
 * @property-read string $ally_percentage
 * @property-read mixed $date_of_birth
 * @property-read mixed $email
 * @property-read mixed $first_name
 * @property-read mixed $last_name
 * @property-read mixed $name
 * @property-read mixed $name_last_first
 * @property-read string $payment_type
 * @property-read mixed $username
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Note[] $notes
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\OnboardStatusHistory[] $onboardStatusHistory
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Payment[] $payments
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\PhoneNumber[] $phoneNumbers
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Schedule[] $schedules
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Shift[] $shifts
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\PaymentQueue[] $upcomingPayments
 * @property-read \App\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Client whereBackupPaymentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Client whereBackupPaymentType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Client whereBusinessFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Client whereBusinessId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Client whereClientType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Client whereDefaultPaymentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Client whereDefaultPaymentType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Client whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Client whereFeeOverride($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Client whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Client whereMaxWeeklyHours($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Client whereOnboardStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Client whereSsn($value)
 */
	class Client extends \Eloquent {}
}

namespace App{
/**
 * App\CreditCard
 *
 * @property int $id
 * @property int $user_id
 * @property string|null $nickname
 * @property string|null $name_on_card
 * @property string|null $type
 * @property mixed|null $number
 * @property int|null $expiration_month
 * @property int|null $expiration_year
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read mixed $last_four
 * @property-read \App\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CreditCard whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CreditCard whereExpirationMonth($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CreditCard whereExpirationYear($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CreditCard whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CreditCard whereNameOnCard($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CreditCard whereNickname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CreditCard whereNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CreditCard whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CreditCard whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CreditCard whereUserId($value)
 */
	class CreditCard extends \Eloquent {}
}

namespace App{
/**
 * App\Deposit
 *
 * @property int $id
 * @property string $deposit_type
 * @property int|null $caregiver_id
 * @property int|null $business_id
 * @property string|null $method_type
 * @property string|null $method_id
 * @property float|null $amount
 * @property string|null $transaction_id
 * @property string|null $transaction_code
 * @property int|null $success
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \App\Business|null $business
 * @property-read \App\Caregiver|null $caregiver
 * @property-read mixed $week
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $method
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Shift[] $shifts
 * @property-read \App\GatewayTransaction|null $transaction
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Deposit whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Deposit whereBusinessId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Deposit whereCaregiverId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Deposit whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Deposit whereDepositType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Deposit whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Deposit whereMethodId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Deposit whereMethodType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Deposit whereSuccess($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Deposit whereTransactionCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Deposit whereTransactionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Deposit whereUpdatedAt($value)
 */
	class Deposit extends \Eloquent {}
}

namespace App{
/**
 * Class Document
 *
 * @package App
 * @property string $name
 * @property string $filename
 * @property string $original_filename
 * @property string $type
 * @property string $description
 * @property int $id
 * @property int|null $user_id
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \App\User|null $user
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\Document onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Document whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Document whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Document whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Document whereFilename($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Document whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Document whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Document whereOriginalFilename($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Document whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Document whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Document whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Document withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Document withoutTrashed()
 */
	class Document extends \Eloquent {}
}

namespace App{
/**
 * App\GatewayTransaction
 *
 * @property int $id
 * @property string $gateway_id
 * @property string $transaction_id
 * @property string $transaction_type
 * @property float $amount
 * @property int $success
 * @property int $declined
 * @property int|null $cvv_pass
 * @property int|null $avs_pass
 * @property string|null $response_text
 * @property string|null $response_data
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \App\Deposit $deposit
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\GatewayTransactionHistory[] $history
 * @property-read \App\GatewayTransactionHistory $lastHistory
 * @property-read \App\Payment $payment
 * @method static \Illuminate\Database\Eloquent\Builder|\App\GatewayTransaction whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\GatewayTransaction whereAvsPass($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\GatewayTransaction whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\GatewayTransaction whereCvvPass($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\GatewayTransaction whereDeclined($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\GatewayTransaction whereGatewayId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\GatewayTransaction whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\GatewayTransaction whereResponseData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\GatewayTransaction whereResponseText($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\GatewayTransaction whereSuccess($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\GatewayTransaction whereTransactionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\GatewayTransaction whereTransactionType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\GatewayTransaction whereUpdatedAt($value)
 */
	class GatewayTransaction extends \Eloquent {}
}

namespace App{
/**
 * App\GatewayTransactionHistory
 *
 * @property int $id
 * @property int $internal_transaction_id
 * @property string $action
 * @property string $status
 * @property float $amount
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \App\GatewayTransaction $transaction
 * @method static \Illuminate\Database\Eloquent\Builder|\App\GatewayTransactionHistory whereAction($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\GatewayTransactionHistory whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\GatewayTransactionHistory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\GatewayTransactionHistory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\GatewayTransactionHistory whereInternalTransactionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\GatewayTransactionHistory whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\GatewayTransactionHistory whereUpdatedAt($value)
 */
	class GatewayTransactionHistory extends \Eloquent {}
}

namespace App{
/**
 * App\Note
 *
 * @property int $id
 * @property int|null $caregiver_id
 * @property int|null $client_id
 * @property string $body
 * @property string|null $tags
 * @property int $created_by
 * @property int $business_id
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \App\Business $business
 * @property-read \App\Caregiver|null $caregiver
 * @property-read \App\Client|null $client
 * @property-read \App\User $creator
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Note whereBody($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Note whereBusinessId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Note whereCaregiverId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Note whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Note whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Note whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Note whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Note whereTags($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Note whereUpdatedAt($value)
 */
	class Note extends \Eloquent {}
}

namespace App{
/**
 * App\OfficeUser
 *
 * @property int $id
 * @property string|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Address[] $addresses
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\BankAccount[] $bankAccounts
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Business[] $businesses
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\CreditCard[] $creditCards
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Document[] $documents
 * @property-read mixed $date_of_birth
 * @property-read mixed $email
 * @property-read mixed $first_name
 * @property-read mixed $last_name
 * @property-read mixed $name
 * @property-read mixed $name_last_first
 * @property-read mixed $username
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\PhoneNumber[] $phoneNumbers
 * @property-read \App\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OfficeUser whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OfficeUser whereId($value)
 */
	class OfficeUser extends \Eloquent {}
}

namespace App{
/**
 * App\OnboardStatusHistory
 *
 * @property int $id
 * @property int $client_id
 * @property string $status
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OnboardStatusHistory whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OnboardStatusHistory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OnboardStatusHistory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OnboardStatusHistory whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OnboardStatusHistory whereUpdatedAt($value)
 */
	class OnboardStatusHistory extends \Eloquent {}
}

namespace App{
/**
 * App\Payment
 *
 * @property int $id
 * @property int|null $client_id
 * @property int $business_id
 * @property string|null $method_type
 * @property string|null $method_id
 * @property string|null $payment_type
 * @property float|null $amount
 * @property string|null $transaction_id
 * @property string|null $transaction_code
 * @property int|null $success
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property float $business_allotment
 * @property float $caregiver_allotment
 * @property float $system_allotment
 * @property-read \App\Business $business
 * @property-read \App\Caregiver $caregiver
 * @property-read \App\Client|null $client
 * @property-read mixed $week
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $method
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Shift[] $shifts
 * @property-read \App\GatewayTransaction|null $transaction
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Payment whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Payment whereBusinessAllotment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Payment whereBusinessId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Payment whereCaregiverAllotment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Payment whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Payment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Payment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Payment whereMethodId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Payment whereMethodType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Payment wherePaymentType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Payment whereSuccess($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Payment whereSystemAllotment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Payment whereTransactionCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Payment whereTransactionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Payment whereUpdatedAt($value)
 */
	class Payment extends \Eloquent {}
}

namespace App{
/**
 * App\PaymentQueue
 *
 * @property int $id
 * @property int $client_id
 * @property int|null $caregiver_id
 * @property int|null $business_id
 * @property string|null $reference_type
 * @property string|null $reference_id
 * @property float|null $amount
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null $process_at
 * @property float $business_allotment
 * @property float $caregiver_allotment
 * @property float $system_allotment
 * @property-read \App\Business|null $business
 * @property-read \App\Caregiver|null $caregiver
 * @property-read \App\Client $client
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $method
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $reference
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PaymentQueue whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PaymentQueue whereBusinessAllotment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PaymentQueue whereBusinessId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PaymentQueue whereCaregiverAllotment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PaymentQueue whereCaregiverId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PaymentQueue whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PaymentQueue whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PaymentQueue whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PaymentQueue whereProcessAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PaymentQueue whereReferenceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PaymentQueue whereReferenceType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PaymentQueue whereSystemAllotment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PaymentQueue whereUpdatedAt($value)
 */
	class PaymentQueue extends \Eloquent {}
}

namespace App{
/**
 * App\PhoneNumber
 *
 * @property int $id
 * @property int $user_id
 * @property string|null $type
 * @property string|null $country_code
 * @property string|null $national_number
 * @property string|null $extension
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \App\Client $client
 * @property mixed $number
 * @property-read \App\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PhoneNumber whereCountryCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PhoneNumber whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PhoneNumber whereExtension($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PhoneNumber whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PhoneNumber whereNationalNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PhoneNumber whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PhoneNumber whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PhoneNumber whereUserId($value)
 */
	class PhoneNumber extends \Eloquent {}
}

namespace App{
/**
 * App\Schedule
 *
 * @property int $id
 * @property int $business_id
 * @property int|null $caregiver_id
 * @property int $client_id
 * @property string $start_date
 * @property string $end_date
 * @property string $time
 * @property int $duration
 * @property mixed $rrule
 * @property string|null $notes
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property float|null $caregiver_rate
 * @property float|null $provider_fee
 * @property int $all_day
 * @property string $hours_type
 * @property int|null $care_plan_id
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Activity[] $activities
 * @property-read \App\Business $business
 * @property-read \App\CarePlan|null $carePlan
 * @property-read \App\Caregiver|null $caregiver
 * @property-read \App\Client $client
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\ScheduleException[] $exceptions
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Shift[] $shifts
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Schedule whereAllDay($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Schedule whereBusinessId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Schedule whereCarePlanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Schedule whereCaregiverId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Schedule whereCaregiverRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Schedule whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Schedule whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Schedule whereDuration($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Schedule whereEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Schedule whereHoursType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Schedule whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Schedule whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Schedule whereProviderFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Schedule whereRrule($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Schedule whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Schedule whereTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Schedule whereUpdatedAt($value)
 */
	class Schedule extends \Eloquent {}
}

namespace App{
/**
 * App\ScheduleException
 *
 * @property int $id
 * @property int $schedule_id
 * @property string $date
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \App\Schedule $schedule
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ScheduleException whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ScheduleException whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ScheduleException whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ScheduleException whereScheduleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ScheduleException whereUpdatedAt($value)
 */
	class ScheduleException extends \Eloquent {}
}

namespace App{
/**
 * App\Shift
 *
 * @property int $id
 * @property int|null $caregiver_id
 * @property int|null $client_id
 * @property int|null $business_id
 * @property \Carbon\Carbon|null $checked_in_time
 * @property float|null $checked_in_latitude
 * @property float|null $checked_in_longitude
 * @property string|null $checked_in_number evv phone number
 * @property \Carbon\Carbon|null $checked_out_time
 * @property float|null $checked_out_latitude
 * @property float|null $checked_out_longitude
 * @property string|null $checked_out_number evv phone number
 * @property string|null $caregiver_comments
 * @property string|null $hours_type
 * @property float $mileage
 * @property float $other_expenses
 * @property int $verified
 * @property int|null $schedule_id
 * @property int $all_day
 * @property float $caregiver_rate
 * @property float $provider_fee
 * @property string|null $status
 * @property \Carbon\Carbon|null $signature
 * @property int|null $payment_id
 * @property string|null $other_expenses_desc
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Activity[] $activities
 * @property-read \App\Business|null $business
 * @property-read \App\Caregiver|null $caregiver
 * @property-read \App\Client|null $client
 * @property-read \App\ShiftCostHistory $costHistory
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Deposit[] $deposits
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\SystemException[] $exceptions
 * @property-read mixed $read_only
 * @property-read mixed $rounded_shift_length
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\ShiftIssue[] $issues
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\ShiftActivity[] $otherActivities
 * @property-read \App\Payment|null $payment
 * @property-read \App\Schedule|null $schedule
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereAllDay($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereAwaitingBusinessDeposit()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereAwaitingCaregiverDeposit()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereAwaitingCharge()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereBusinessId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereCaregiverComments($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereCaregiverId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereCaregiverRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereCheckedInLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereCheckedInLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereCheckedInNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereCheckedInTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereCheckedOutLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereCheckedOutLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereCheckedOutNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereCheckedOutTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereHoursType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereMileage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereOtherExpenses($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereOtherExpensesDesc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift wherePaymentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift wherePending()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereProviderFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereReadOnly()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereScheduleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereSignature($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereVerified($value)
 */
	class Shift extends \Eloquent {}
}

namespace App{
/**
 * App\ShiftActivity
 *
 * @property int $id
 * @property int $shift_id
 * @property int|null $activity_id
 * @property string|null $other
 * @property int $completed
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShiftActivity whereActivityId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShiftActivity whereCompleted($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShiftActivity whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShiftActivity whereOther($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShiftActivity whereShiftId($value)
 */
	class ShiftActivity extends \Eloquent {}
}

namespace App{
/**
 * App\ShiftCostHistory
 *
 * @property int $id
 * @property float $caregiver_shift
 * @property float $caregiver_expenses
 * @property float $caregiver_mileage
 * @property float $caregiver_total
 * @property float $provider_fee
 * @property float $ally_fee
 * @property float $total_cost
 * @property float $ally_pct
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \App\Shift $shift
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShiftCostHistory whereAllyFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShiftCostHistory whereAllyPct($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShiftCostHistory whereCaregiverExpenses($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShiftCostHistory whereCaregiverMileage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShiftCostHistory whereCaregiverShift($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShiftCostHistory whereCaregiverTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShiftCostHistory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShiftCostHistory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShiftCostHistory whereProviderFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShiftCostHistory whereTotalCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShiftCostHistory whereUpdatedAt($value)
 */
	class ShiftCostHistory extends \Eloquent {}
}

namespace App{
/**
 * App\ShiftIssue
 *
 * @property int $id
 * @property int $shift_id
 * @property int $client_injury
 * @property int $caregiver_injury
 * @property string|null $comments
 * @property-read \App\Shift $shift
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShiftIssue whereCaregiverInjury($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShiftIssue whereClientInjury($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShiftIssue whereComments($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShiftIssue whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ShiftIssue whereShiftId($value)
 */
	class ShiftIssue extends \Eloquent {}
}

namespace App{
/**
 * App\SystemException
 *
 * @property int $id
 * @property string|null $title
 * @property string|null $description
 * @property string|null $reference_url
 * @property string|null $reference_type
 * @property string|null $reference_id
 * @property string|null $acknowledged_at
 * @property int|null $acknowledged_by
 * @property int $business_id
 * @property string|null $notes
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \App\User|null $acknowledger
 * @property-read \App\Business $business
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $reference
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SystemException whereAcknowledgedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SystemException whereAcknowledgedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SystemException whereBusinessId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SystemException whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SystemException whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SystemException whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SystemException whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SystemException whereReferenceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SystemException whereReferenceType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SystemException whereReferenceUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SystemException whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SystemException whereUpdatedAt($value)
 */
	class SystemException extends \Eloquent {}
}

namespace App{
/**
 * App\User
 *
 * @property int $id
 * @property string $email
 * @property string|null $username
 * @property string $password
 * @property string|null $firstname
 * @property string|null $lastname
 * @property string|null $date_of_birth
 * @property string $role_type
 * @property int|null $access_group_id
 * @property int $active
 * @property string|null $remember_token
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null $email_sent_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Address[] $addresses
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\BankAccount[] $bankAccounts
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\CreditCard[] $creditCards
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Document[] $documents
 * @property-read mixed $name
 * @property-read mixed $name_last_first
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\PhoneNumber[] $phoneNumbers
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereAccessGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereDateOfBirth($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereEmailSentAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereFirstname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereLastname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereRoleType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereUsername($value)
 */
	class User extends \Eloquent {}
}

