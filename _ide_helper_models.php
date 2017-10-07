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
 * @property int $business_id
 * @property string|null $code
 * @property string|null $name
 * @property string|null $description
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \App\Business $business
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
 * @property-read \App\User $user
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
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Activity[] $activities
 * @property-read \App\BankAccount|null $bankAccount
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Caregiver[] $caregivers
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Client[] $clients
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Deposit[] $deposits
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
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business wherePhone1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business wherePhone2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Business whereState($value)
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
 * @property mixed|null $ssn
 * @property int|null $bank_account_id
 * @property-read \App\BankAccount|null $bankAccount
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Business[] $businesses
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Deposit[] $deposits
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Payment[] $payments
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Schedule[] $schedules
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Shift[] $shifts
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\PaymentQueue[] $upcomingPayments
 * @property-read \App\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Caregiver whereBankAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Caregiver whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Caregiver whereSsn($value)
 */
	class Caregiver extends \Eloquent {}
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
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $backupPayment
 * @property-read \App\Business $business
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $defaultPayment
 * @property-read \App\Address $evvAddress
 * @property-read \App\PhoneNumber $evvPhone
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Payment[] $payments
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Schedule[] $schedules
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\PaymentQueue[] $upcomingPayments
 * @property-read \App\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Client whereBackupPaymentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Client whereBackupPaymentType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Client whereBusinessFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Client whereBusinessId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Client whereDefaultPaymentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Client whereDefaultPaymentType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Client whereId($value)
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
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $method
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Payment[] $payments
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
 * App\Document
 *
 * @property-read \App\User $user
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
 * App\OfficeUser
 *
 * @property int $id
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Business[] $businesses
 * @property-read \App\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\OfficeUser whereId($value)
 */
	class OfficeUser extends \Eloquent {}
}

namespace App{
/**
 * App\Payment
 *
 * @property int $id
 * @property int $client_id
 * @property int|null $caregiver_id
 * @property int $business_id
 * @property string|null $reference_type
 * @property string|null $reference_id
 * @property string|null $method_type
 * @property string|null $method_id
 * @property float|null $amount
 * @property string|null $transaction_id
 * @property string|null $transaction_code
 * @property int|null $success
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property int $deposited
 * @property float $business_allotment
 * @property float $caregiver_allotment
 * @property float $system_allotment
 * @property-read \App\Business $business
 * @property-read \App\Caregiver|null $caregiver
 * @property-read \App\Client $client
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $method
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $reference
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Payment whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Payment whereBusinessAllotment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Payment whereBusinessId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Payment whereCaregiverAllotment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Payment whereCaregiverId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Payment whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Payment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Payment whereDeposited($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Payment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Payment whereMethodId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Payment whereMethodType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Payment whereReferenceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Payment whereReferenceType($value)
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
 * @property string|null $rrule
 * @property string|null $notes
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property float|null $scheduled_rate
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Activity[] $activities
 * @property-read \App\Business $business
 * @property-read \App\Caregiver|null $caregiver
 * @property-read \App\Client $client
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\ScheduleException[] $exceptions
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Shift[] $shifts
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Schedule whereBusinessId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Schedule whereCaregiverId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Schedule whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Schedule whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Schedule whereDuration($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Schedule whereEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Schedule whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Schedule whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Schedule whereRrule($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Schedule whereScheduledRate($value)
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
 * @property string|null $checked_in_time
 * @property float|null $checked_in_latitude
 * @property float|null $checked_in_longitude
 * @property string|null $checked_in_number evv phone number
 * @property string|null $checked_out_time
 * @property float|null $checked_out_latitude
 * @property float|null $checked_out_longitude
 * @property string|null $checked_out_number evv phone number
 * @property string|null $caregiver_comments
 * @property string|null $hours_type
 * @property float $mileage
 * @property float $other_expenses
 * @property int $verified
 * @property int $paid
 * @property int|null $schedule_id
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Activity[] $activities
 * @property-read \App\Business|null $business
 * @property-read \App\Caregiver|null $caregiver
 * @property-read \App\Client|null $client
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\ShiftIssue[] $issues
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\ShiftActivity[] $otherActivities
 * @property-read \App\Schedule|null $schedule
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereBusinessId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereCaregiverComments($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereCaregiverId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereCheckedInLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereCheckedInLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereCheckedInNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereCheckedInTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereCheckedOutLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereCheckedOutLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereCheckedOutNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereCheckedOutTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereHoursType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereMileage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereOtherExpenses($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift wherePaid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Shift whereScheduleId($value)
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
 * App\User
 *
 * @property int $id
 * @property string $email
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
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Address[] $addresses
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\BankAccount[] $bankAccounts
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\CreditCard[] $creditCards
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Document[] $documents
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\PhoneNumber[] $phoneNumbers
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereAccessGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereDateOfBirth($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereFirstname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereLastname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereRoleType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereUpdatedAt($value)
 */
	class User extends \Eloquent {}
}

