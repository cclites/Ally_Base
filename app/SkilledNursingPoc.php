<?php

namespace App;

class SkilledNursingPoc extends AuditableModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'skilled_nursing_pocs';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    // **********************************************************
    // ENUM KEYS
    // **********************************************************

    const MOBILITY_BEDREST = 'bedrest';
    const MOBILITY_BEDREST_BRP = 'bedrest_brp';
    const MOBILITY_HOYER_LIFT = 'hoyer_lift';
    const MOBILITY_INDEPENDENT = 'independent';
    const MOBILITY_WHEELCHAIR = 'wheelchair';
    const MOBILITY_NO_RESTRICTIONS = 'no_restrictions';
    const MOBILITY_TURN = 'turn';
    const MOBILITY_ASSIST_TRANSFERS = 'assist_transfers';
    const MOBILITY_ASSIST_AMBULATION = 'assist_ambulation';
    const MOBILITY_CANE = 'cane';
    const MOBILITY_UP_AS_TOLERATED = 'up_as_tolerated';
    const MOBILITY_PARTIAL_WEIGHT = 'partial_weight';
    const MOBILITY_WALKER = 'walker';
    const MOBILITY_HOSPITAL_BED = 'hospital_bed';
    const MOBILITY_CRUTCHES = 'crutches';
    const MOBILITY_EXERCISES_PRESCRIBED = 'exercises_prescribed';
    const MOBILITY_OTHER = 'other';
    const MOBILITY = [
        self::MOBILITY_BEDREST,
        self::MOBILITY_BEDREST_BRP,
        self::MOBILITY_HOYER_LIFT,
        self::MOBILITY_INDEPENDENT,
        self::MOBILITY_WHEELCHAIR,
        self::MOBILITY_NO_RESTRICTIONS,
        self::MOBILITY_TURN,
        self::MOBILITY_ASSIST_TRANSFERS,
        self::MOBILITY_ASSIST_AMBULATION,
        self::MOBILITY_CANE,
        self::MOBILITY_UP_AS_TOLERATED,
        self::MOBILITY_PARTIAL_WEIGHT,
        self::MOBILITY_WALKER,
        self::MOBILITY_HOSPITAL_BED,
        self::MOBILITY_CRUTCHES,
        self::MOBILITY_EXERCISES_PRESCRIBED,
        self::MOBILITY_OTHER,
    ];

    const FUNCTIONAL_AMPUTATION = 'amputation';
    const FUNCTIONAL_INCONTINENCE = 'incontinence';
    const FUNCTIONAL_CONTRACTURE = 'contracture';
    const FUNCTIONAL_HEARING = 'hearing';
    const FUNCTIONAL_PARALYSIS = 'paralysis';
    const FUNCTIONAL_ENDURANCE = 'endurance';
    const FUNCTIONAL_AMBULATION = 'ambulation';
    const FUNCTIONAL_SPEECH = 'speech';
    const FUNCTIONAL_BLIND = 'blind';
    const FUNCTIONAL_DYSPNEA_WITH_MINIMAL_EXERTION = 'dyspnea';
    const FUNCTIONAL_OTHER = 'other';
    const FUNCTIONAL = [
        self::FUNCTIONAL_AMPUTATION,
        self::FUNCTIONAL_CONTRACTURE,
        self::FUNCTIONAL_PARALYSIS,
        self::FUNCTIONAL_ENDURANCE,
        self::FUNCTIONAL_AMBULATION,
        self::FUNCTIONAL_SPEECH,
        self::FUNCTIONAL_DYSPNEA_WITH_MINIMAL_EXERTION,
        self::FUNCTIONAL_OTHER,
        self::FUNCTIONAL_HEARING,
        self::FUNCTIONAL_BLIND,
        self::FUNCTIONAL_INCONTINENCE,
    ];

    const PROGNOSIS_POOR = 'poor';
    const PROGNOSIS_GUARDED = 'guarded';
    const PROGNOSIS_FAIR = 'fair';
    const PROGNOSIS_GOOD = 'good';
    const PROGNOSIS_EXCELLENT = 'excellent';
    const PROGNOSIS = [
        self::PROGNOSIS_POOR,
        self::PROGNOSIS_GUARDED,
        self::PROGNOSIS_FAIR,
        self::PROGNOSIS_GOOD,
        self::PROGNOSIS_EXCELLENT
    ];

    const MENTAL_STATUS_ORIENTED = 'oriented';
    const MENTAL_STATUS_DEPRESSED = 'depressed';
    const MENTAL_STATUS_DISORIENTED = 'disoriented';
    const MENTAL_STATUS_LETHARGIC = 'lethargic';
    const MENTAL_STATUS_COMATOSE = 'comatose';
    const MENTAL_STATUS_AGITATED = 'agitated';
    const MENTAL_STATUS_POOR = 'poor';
    const MENTAL_STATUS_GOOD = 'good';
    const MENTAL_STATUS_FAIR = 'fair';
    const MENTAL_STATUS_FORGETFUL = 'forgetful';
    const MENTAL_STATUS_EXCELLENT = 'excellent';
    const MENTAL_STATUS_OTHER = 'other';
    const MENTAL_STATUS = [
        self::MENTAL_STATUS_ORIENTED,
        self::MENTAL_STATUS_DEPRESSED,
        self::MENTAL_STATUS_DISORIENTED,
        self::MENTAL_STATUS_LETHARGIC,
        self::MENTAL_STATUS_COMATOSE,
        self::MENTAL_STATUS_AGITATED,
        self::MENTAL_STATUS_POOR,
        self::MENTAL_STATUS_GOOD,
        self::MENTAL_STATUS_EXCELLENT,
        self::MENTAL_STATUS_FAIR,
        self::MENTAL_STATUS_FORGETFUL,
        self::MENTAL_STATUS_OTHER,
    ];


    // **********************************************************
    // RELATIONSHIPS
    // **********************************************************

    /**
     * Get the client relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
    */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    // **********************************************************
    // MUTATORS
    // **********************************************************

    public function getMobilityAttribute()
    {
        return self::stringToArray($this->attributes['mobility']);
    }

    public function getFunctionalAttribute()
    {
        return self::stringToArray($this->attributes['functional']);
    }

    public function getMentalStatusAttribute()
    {
        return self::stringToArray($this->attributes['mental_status']);
    }


    // **********************************************************
    // OTHER FUNCTIONS
    // **********************************************************

    /**
     * Attributes that are booleans.
     *
     * @var array
     */
    protected static $boolKeys = [];

    /**
     * Attributes that are imploded arrays.
     *
     * @var array
     */
    protected static $arrayKeys = [
        'functional',
        'mental_status',
        'mobility',
    ];

    /**
     * Converts form data array into mass assignable array.
     *
     * @param array $data
     * @return array
     */
    public static function convertFormData($data)
    {
        // fix boolean values
        foreach (self::$boolKeys as $key) {
            $data[$key] = $data[$key] ?? false;
        }

        // implode all array fields
        foreach (self::$arrayKeys as $key) {
            $data[$key] = self::arrayToString($data[$key]);
        }

        return $data;
    }

    /**
     * Explodes string into an array.
     *
     * @param string $value
     * @return array
     */
    public static function stringToArray($value)
    {
        if (empty($value)) {
            return [];
        }

        return explode(',', $value);
    }

    /**
     * Implodes array into string.
     *
     * @param array $value
     * @return null|string
     */
    public static function arrayToString($value)
    {
        if (empty($value)) {
            return null;
        }

        return implode(',', $value);
    }

    // **********************************************************
    // ScrubsForSeeding Methods
    // **********************************************************
    use \App\Traits\ScrubsForSeeding;

    /**
     * Get an array of scrubbed data to replace the original.
     *
     * @param \Faker\Generator $faker
     * @param bool $fast
     * @param null|\Illuminate\Database\Eloquent\Model $item
     * @return array
     */
    public static function getScrubbedData(\Faker\Generator $faker, bool $fast, ?\Illuminate\Database\Eloquent\Model $item) : array
    {
        return [
            'medical_record_number' => $faker->randomNumber(9),
            'provider_number' => $faker->randomNumber(9),
            'principal_diagnosis_icd_cm' => $faker->sentence,
            'principal_diagnosis' => $faker->sentence,
            'surgical_procedure_icd_cm' => $faker->sentence,
            'surgical_procedure' => $faker->sentence,
            'other_diagnosis_icd_cm' => $faker->sentence,
            'other_diagnosis' => $faker->sentence,
            'other_diagnosis_icd_cm1' => $faker->sentence,
            'other_diagnosis1' => $faker->sentence,
            'other_diagnosis_icd_cm2' => $faker->sentence,
            'other_diagnosis2' => $faker->sentence,
            'physician_name' => $faker->name,
            'physician_address' => $faker->streetAddress,
            'physician_phone' => $faker->simple_phone,
            'mobility_instructions' => $faker->sentence,
            'mobility_other' => $faker->sentence,
        ];
    }
}
