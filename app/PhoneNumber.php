<?php
namespace App;

use App\Traits\ScrubsForSeeding;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;

/**
 * App\PhoneNumber
 *
 * @property int $id
 * @property int $user_id
 * @property string|null $type
 * @property string|null $country_code
 * @property string|null $national_number
 * @property string|null $extension
 * @property string|null $notes
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read \App\Client $client
 * @property mixed $number
 * @property-read \App\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BaseModel ordered($direction = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PhoneNumber whereCountryCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PhoneNumber whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PhoneNumber whereExtension($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PhoneNumber whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PhoneNumber whereNationalNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PhoneNumber whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PhoneNumber whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PhoneNumber whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PhoneNumber whereUserId($value)
 * @mixin \Eloquent
 * @property int $receives_sms
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PhoneNumber whereReceivesSms($value)
 */
class PhoneNumber extends AuditableModel
{
    const DEFAULT_REGION = 'US';
    const DEFAULT_FORMAT = PhoneNumberFormat::NATIONAL;

    protected $table = 'phone_numbers';
    protected $guarded = ['id'];
    protected $appends = ['number'];

    /**
     * @var \libphonenumber\PhoneNumberUtil
     */
    protected $phoneNumberUtil;

    public function __construct(array $attributes = [])
    {
        $this->phoneNumberUtil = PhoneNumberUtil::getInstance();
        parent::__construct($attributes);
    }

    public static function fromInput(string $type, string $number, string $extension = null)
    {
        $phoneNumber = new self();
        $phoneNumber->type = $type;
        $phoneNumber->input($number, $extension);
        return $phoneNumber;
    }

    ///////////////////////////////////////////
    /// Relationship Methods
    ///////////////////////////////////////////

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'user_id');
    }

    ///////////////////////////////////////////
    /// Mutators
    ///////////////////////////////////////////

    public function setNumberAttribute($value)
    {
        $this->input($value);
    }

    public function getNumberAttribute()
    {
        return $this->number();
    }

    ////////////////////////////////////////////
    /// Other Methods
    ///////////////////////////////////////////

    /**
     * Output the full phone number in a formatted or unformatted manner
     *
     * @param bool $formatted
     *
     * @return string
     */
    public function number($formatted = true, $format = self::DEFAULT_FORMAT, $includeExtension = true)
    {
        if ($formatted) {
            return $this->format($this->number(false, 0, $includeExtension), null, $format);
        }
        $number = '+' . $this->country_code  . $this->national_number;
        if ($includeExtension && $this->extension) {
            $number .= ' x' . $this->extension;
        }
        return $number;
    }

    /**
     * Output the number without the extension
     *
     * @param bool $formatted
     * @param int $format
     *
     * @return string
     */
    public function numberOnly($formatted = true, $format = self::DEFAULT_FORMAT)
    {
        return $this->number($formatted, $format, false);
    }

    /**
     * Input a new phone number, will be parsed into database fields automatically
     *
     * @param $number
     * @param null $extension
     *
     * @return $this
     * @throws \Exception
     */
    public function input($number, $extension = null)
    {
        $parsed = $this->parse($number, $extension);
        $this->country_code = $parsed->getCountryCode();
        $this->national_number = $parsed->getNationalNumber();
        $this->extension = $parsed->getExtension();
        if (!$this->national_number) throw new \Exception('Invalid phone number input: ' . $number);
        return $this;
    }

    /**
     * Parse a phone number
     *
     * @param $number
     * @param null $extension
     *
     * @return \libphonenumber\PhoneNumber
     */
    public function parse($number, $extension = null)
    {
        if ($extension) $number .= ' x' . ltrim($extension, 'x');
        return $this->phoneNumberUtil->parse($number, self::DEFAULT_REGION);
    }

    /**
     * Reformat a phone number
     *
     * @param $number
     * @param null $extension
     * @param int $format
     *
     * @return string
     */
    public function format($number, $extension = null, $format = self::DEFAULT_FORMAT)
    {
        $parsed = $this->parse($number, $extension);
        return $this->phoneNumberUtil->format($parsed, $format);
    }

    /**
     * Check to see if the phone number is possible (enough digits, etc)
     *
     * @return bool
     */
    public function isPossible()
    {
        $parsed = $this->parse($this->number(false));
        return $this->phoneNumberUtil->isPossibleNumber($parsed);
    }

    /**
     * Check to see if the phone number seems valid (correct region, area code, etc)
     *
     * @param null $region
     *
     * @return bool
     */
    public function isValid($region = null)
    {
        $parsed = $this->parse($this->number(false));
        if ($region) {
            return $this->phoneNumberUtil->isValidNumberForRegion($parsed, $region);
        }
        return $this->phoneNumberUtil->isValidNumber($parsed);
    }

    /**
     * Return phone number formatted as e164 format.
     *
     * @param string $number
     * @return string
     */
    public static function formatE164($number)
    {
        $phone = new self();
        $phone->input($number);
        return '+1' . $phone->national_number;
    }

    /**
     * Return phone number formatted as e164 format.
     *
     * @param string $number
     * @return string
     */
    public static function formatNational($number)
    {
        $phone = new self();
        $phone->input($number);
        return $phone->national_number;
    }

    // **********************************************************
    // ScrubsForSeeding Methods
    // **********************************************************
    use ScrubsForSeeding;

    /**
     * Get an array of scrubbed data to replace the original.
     *
     * @param \Faker\Generator $faker
     * @param bool $fast
     * @return array
     */
    public static function getScrubbedData(\Faker\Generator $faker, bool $fast) : array
    {
        return [
            'national_number' => $faker->simple_phone,
        ];
    }
}
