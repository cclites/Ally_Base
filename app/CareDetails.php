<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\CareDetails
 *
 * @property int $id
 * @property int $client_id
 * @property int $lives_alone
 * @property string|null $pets
 * @property int $smoker
 * @property int $alcohol
 * @property int $incompetent
 * @property string|null $competency_level
 * @property int $can_provide_direction
 * @property int $assist_medications
 * @property string|null $medication_overseer
 * @property string|null $safety_measures
 * @property string|null $safety_instructions
 * @property string|null $mobility
 * @property string|null $mobility_instructions
 * @property string|null $toileting
 * @property string|null $toileting_instructions
 * @property string|null $bathing
 * @property string|null $bathing_frequency
 * @property string|null $bathing_instructions
 * @property string|null $vision
 * @property string|null $hearing
 * @property string|null $hearing_instructions
 * @property string|null $diet
 * @property string|null $diet_likes
 * @property string|null $feeding_instructions
 * @property string|null $skin
 * @property string|null $skin_conditions
 * @property string|null $hair
 * @property string|null $hair_frequency
 * @property string|null $oral
 * @property string|null $shaving
 * @property string|null $shaving_instructions
 * @property string|null $nails
 * @property string|null $dressing
 * @property string|null $dressing_instructions
 * @property string|null $housekeeping
 * @property string|null $housekeeping_instructions
 * @property string|null $errands
 * @property string|null $supplies
 * @property string|null $supplies_instructions
 * @property string|null $comments
 * @property string|null $instructions
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read \App\Client $client
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BaseModel ordered($direction = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CareDetails whereAlcohol($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CareDetails whereAssistMedications($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CareDetails whereBathing($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CareDetails whereBathingFrequency($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CareDetails whereBathingInstructions($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CareDetails whereCanProvideDirection($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CareDetails whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CareDetails whereComments($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CareDetails whereCompetencyLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CareDetails whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CareDetails whereDiet($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CareDetails whereDietLikes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CareDetails whereDressing($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CareDetails whereDressingInstructions($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CareDetails whereErrands($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CareDetails whereFeedingInstructions($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CareDetails whereHair($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CareDetails whereHairFrequency($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CareDetails whereHearing($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CareDetails whereHearingInstructions($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CareDetails whereHousekeeping($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CareDetails whereHousekeepingInstructions($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CareDetails whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CareDetails whereIncompetent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CareDetails whereInstructions($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CareDetails whereLivesAlone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CareDetails whereMedicationOverseer($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CareDetails whereMobility($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CareDetails whereMobilityInstructions($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CareDetails whereNails($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CareDetails whereOral($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CareDetails wherePets($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CareDetails whereSafetyInstructions($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CareDetails whereSafetyMeasures($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CareDetails whereShaving($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CareDetails whereShavingInstructions($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CareDetails whereSkin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CareDetails whereSkinConditions($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CareDetails whereSmoker($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CareDetails whereSupplies($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CareDetails whereSuppliesInstructions($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CareDetails whereToileting($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CareDetails whereToiletingInstructions($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CareDetails whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CareDetails whereVision($value)
 * @mixin \Eloquent
 */
class CareDetails extends AuditableModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'client_care_details';

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
    public $with = [];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [];

    /**
     * The list of attributes that should be specifically cast.
     *
     * @var array
     */
//    protected $casts = [
//        'lives_alone' => 'int',
//        'smoker' => 'int',
//        'alcohol' => 'int',
//        'incompetent' => 'int',
//        'can_provide_direction' => 'int',
//        'assist_medications' => 'int',
//    ];

    // **********************************************************
    // ENUM KEYS
    // **********************************************************

    const PET_CATS = 'cats';
    const PET_DOGS = 'dogs';
    const PET_BIRDS = 'birds';
    const PET_OTHER = 'other';
    const PETS = [
        self::PET_CATS,
        self::PET_DOGS,
        self::PET_BIRDS,
        self::PET_OTHER,
    ];

    const COMPETENCY_LEVEL_ALERT = 'alert';
    const COMPETENCY_LEVEL_FORGETFUL = 'forgetful';
    const COMPETENCY_LEVEL_CONFUSED = 'confused';
    const COMPETENCY_LEVEL_OTHER = 'other';
    const COMPETENCY_LEVELS = [
        self::COMPETENCY_LEVEL_ALERT,
        self::COMPETENCY_LEVEL_FORGETFUL,
        self::COMPETENCY_LEVEL_CONFUSED,
        self::COMPETENCY_LEVEL_OTHER,
    ];

    const SAFETY_CAN_LEAVE_ALONE = 'can_leave_alone';
    const SAFETY_CONTACT_GUARD = 'contact_guard';
    const SAFETY_GAIT_BELT = 'gait_belt';
    const SAFETY_CAN_USE_STAIRS = 'can_use_stairs';
    const SAFETY_STAIR_LIFT = 'stair_lift';
    const SAFETY_OTHER = 'other';
    const SAFETY = [
        self::SAFETY_CAN_LEAVE_ALONE,
        self::SAFETY_CONTACT_GUARD,
        self::SAFETY_GAIT_BELT,
        self::SAFETY_CAN_USE_STAIRS,
        self::SAFETY_STAIR_LIFT,
        self::SAFETY_OTHER,
    ];

    const TOILETING_CONTINENT = 'continent';
    const TOILETING_CATHETER = 'catheter';
    const TOILETING_BEDPAN = 'bedpan';
    const TOILETING_INCONTINENT = 'incontinent';
    const TOILETING_COLOSTOMY = 'colostomy';
    const TOILETING_URINAL = 'urinal';
    const TOILETING_ADULT_BRIEFS = 'adult_briefs';
    const TOILETING_BATHROOM = 'bathroom';
    const TOILETING_BEDSIDE_COMMODE = 'bedside_commode';
    const TOILETING = [
        self::TOILETING_CONTINENT,
        self::TOILETING_CATHETER,
        self::TOILETING_BEDPAN,
        self::TOILETING_INCONTINENT,
        self::TOILETING_COLOSTOMY,
        self::TOILETING_URINAL,
        self::TOILETING_ADULT_BRIEFS,
        self::TOILETING_BATHROOM,
        self::TOILETING_BEDSIDE_COMMODE,
    ];

    const BATHING_PARTIAL = 'partial';
    const BATHING_SHOWER = 'shower';
    const BATHING_SHOWER_CHAIR = 'shower_chair';
    const BATHING_COMPLETE = 'complete';
    const BATHING_SPONGE_BATH = 'sponge_bath';
    const BATHING_BED_BATH = 'bed_bath';
    const BATHING_TUB = 'tub';
    const BATHING_SINK = 'sink';
    const BATHING = [
        self::BATHING_PARTIAL,
        self::BATHING_SHOWER,
        self::BATHING_SHOWER_CHAIR,
        self::BATHING_COMPLETE,
        self::BATHING_SPONGE_BATH,
        self::BATHING_BED_BATH,
        self::BATHING_TUB,
        self::BATHING_SINK,
    ];

    const VISION_RIGHT = 'right';
    const VISION_LEFT = 'left';
    const VISION_GLASSES = 'glasses';
    const VISION_NORMAL = 'normal';
    const VISION_PERIPHERAL = 'peripheral';
    const VISION_NO_PERIPHERAL = 'no_peripheral';
    const VISION_BLIND = 'blind';
    const VISION = [
        self::VISION_RIGHT,
        self::VISION_LEFT,
        self::VISION_GLASSES,
        self::VISION_NORMAL,
        self::VISION_PERIPHERAL,
        self::VISION_NO_PERIPHERAL,
        self::VISION_BLIND,
    ];

    const HEARING_NORMAL = 'normal';
    const HEARING_HARD = 'hard';
    const HEARING_HEARING_AID = 'hearing_aid';
    const HEARING_DEAF = 'deaf';
    const HEARING = [
        self::HEARING_NORMAL,
        self::HEARING_HARD,
        self::HEARING_HEARING_AID,
        self::HEARING_DEAF,
    ];

    const DIET_NORMAL = 'normal';
    const DIET_LIQUID = 'liquid';
    const DIET_ENCOURAGE_FLUIDS = 'encourage_fluids';
    const DIET_LUNCH = 'lunch';
    const DIET_DIABETIC = 'diabetic';
    const DIET_ASSIST_MEALS = 'assist_meals';
    const DIET_LIMIT_FLUIDS = 'limit_fluids';
    const DIET_SNACKS = 'snacks';
    const DIET_LOW_SODIUM = 'low_sodium';
    const DIET_ASSIST_FEEDING = 'assist_feeding';
    const DIET_BREAKFAST = 'breakfast';
    const DIET_DINNER = 'dinner';
    const DIET = [
        self::DIET_NORMAL,
        self::DIET_LIQUID,
        self::DIET_ENCOURAGE_FLUIDS,
        self::DIET_LUNCH,
        self::DIET_DIABETIC,
        self::DIET_ASSIST_MEALS,
        self::DIET_LIMIT_FLUIDS,
        self::DIET_SNACKS,
        self::DIET_LOW_SODIUM,
        self::DIET_ASSIST_FEEDING,
        self::DIET_BREAKFAST,
        self::DIET_DINNER,
    ];

    const SKIN_MOISTURIZER = 'moisturizer';
    const SKIN_INTACT = 'intact';
    const SKIN_POWDER = 'powder';
    const SKIN_BREAKDOWN = 'breakdown';
    const SKIN_PREVENTATIVE = 'preventative';
    const SKIN = [
        self::SKIN_MOISTURIZER,
        self::SKIN_INTACT,
        self::SKIN_POWDER,
        self::SKIN_BREAKDOWN,
        self::SKIN_PREVENTATIVE,
    ];

    const HAIR_DRY = 'dry';
    const HAIR_SET = 'set';
    const HAIR_BRUSH = 'brush';
    const HAIR_HAIR_DRESSER = 'hair_dresser';
    const HAIR = [
        self::HAIR_DRY,
        self::HAIR_SET,
        self::HAIR_BRUSH,
        self::HAIR_HAIR_DRESSER,
    ];

    const ORAL_BRUSH = 'brush';
    const ORAL_DENTURES = 'dentures';
    const ORAL = [
        self::ORAL_BRUSH,
        self::ORAL_DENTURES,
    ];

    const SHAVING_YES = 'yes';
    const SHAVING_NO = 'no';
    const SHAVING_SELF = 'self';
    const SHAVING_ASSISTED = 'assisted';
    const SHAVING = [
        self::SHAVING_YES,
        self::SHAVING_NO,
        self::SHAVING_SELF,
        self::SHAVING_ASSISTED,
    ];

    const NAILS_CLEAN = 'clean';
    const NAILS_FILE = 'file';
    const NAILS_POLISH = 'polish';
    const NAILS = [
        self::NAILS_CLEAN,
        self::NAILS_FILE,
        self::NAILS_POLISH,
    ];

    const DRESSING_SELF = 'self';
    const DRESSING_CLOTHES = 'clothes';
    const DRESSING_ASSIST = 'assist';
    const DRESSING = [
        self::DRESSING_SELF,
        self::DRESSING_CLOTHES,
        self::DRESSING_ASSIST,
    ];

    const HOUSEKEEPING_VACUUMING = 'vacuuming';
    const HOUSEKEEPING_DUSTING = 'dusting';
    const HOUSEKEEPING_TRASH = 'trash';
    const HOUSEKEEPING_MAKE_BED = 'make_bed';
    const HOUSEKEEPING_BED_LINENS = 'bed_linens';
    const HOUSEKEEPING_LAUNDRY = 'laundry';
    const HOUSEKEEPING_CLEAN_BATHROOM = 'clean_bathroom';
    const HOUSEKEEPING_BATHROOM_LINENS = 'bathroom_linens';
    const HOUSEKEEPING_DISHES = 'dishes';
    const HOUSEKEEPING_CLEAN_KITCHEN = 'clean_kitchen';
    const HOUSEKEEPING_MOP = 'mop';
    const HOUSEKEEPING_OTHER = 'other';
    const HOUSEKEEPING = [
        self::HOUSEKEEPING_VACUUMING,
        self::HOUSEKEEPING_DUSTING,
        self::HOUSEKEEPING_TRASH,
        self::HOUSEKEEPING_MAKE_BED,
        self::HOUSEKEEPING_BED_LINENS,
        self::HOUSEKEEPING_LAUNDRY,
        self::HOUSEKEEPING_CLEAN_BATHROOM,
        self::HOUSEKEEPING_BATHROOM_LINENS,
        self::HOUSEKEEPING_DISHES,
        self::HOUSEKEEPING_CLEAN_KITCHEN,
        self::HOUSEKEEPING_MOP,
        self::HOUSEKEEPING_OTHER,
    ];

    const ERRANDS_DRIVES = 'drives';
    const ERRANDS_AUTHORIZED_TAKE_OUT = 'authorized_take_out';
    const ERRANDS_CALL_TAKE_OUT = 'call_take_out';
    const ERRANDS_HAS_WAIVER = 'has_waiver';
    const ERRANDS_TAXI = 'taxi';
    const ERRANDS_CAREGIVER_CAR = 'caregiver_car';
    const ERRANDS_CLIENT_CAR = 'client_car';
    const ERRANDS = [
        self::ERRANDS_DRIVES,
        self::ERRANDS_AUTHORIZED_TAKE_OUT,
        self::ERRANDS_CALL_TAKE_OUT,
        self::ERRANDS_HAS_WAIVER,
        self::ERRANDS_TAXI,
        self::ERRANDS_CAREGIVER_CAR,
        self::ERRANDS_CLIENT_CAR,
    ];

    const SUPPLIES_GLOVES = 'gloves';
    const SUPPLIES_SANITIZER = 'sanitizer';
    const SUPPLIES_CAREGIVER = 'caregiver';
    const SUPPLIES_OTHER = 'other';
    const SUPPLIES = [
        self::SUPPLIES_GLOVES,
        self::SUPPLIES_SANITIZER,
        self::SUPPLIES_CAREGIVER,
        self::SUPPLIES_OTHER,
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

    public function getPetsAttribute()
    {
        return self::stringToArray($this->attributes['pets']);
    }

    public function getSafetyMeasuresAttribute()
    {
        return self::stringToArray($this->attributes['safety_measures']);
    }

    public function getToiletingAttribute()
    {
        return self::stringToArray($this->attributes['toileting']);
    }

    public function getBathingAttribute()
    {
        return self::stringToArray($this->attributes['bathing']);
    }

    public function getDietAttribute()
    {
        return self::stringToArray($this->attributes['diet']);
    }

    public function getSkinAttribute()
    {
        return self::stringToArray($this->attributes['skin']);
    }

    public function getOralAttribute()
    {
        return self::stringToArray($this->attributes['oral']);
    }

    public function getNailsAttribute()
    {
        return self::stringToArray($this->attributes['nails']);
    }

    public function getDressingAttribute()
    {
        return self::stringToArray($this->attributes['dressing']);
    }

    public function getHousekeepingAttribute()
    {
        return self::stringToArray($this->attributes['housekeeping']);
    }

    public function getErrandsAttribute()
    {
        return self::stringToArray($this->attributes['errands']);
    }

    public function getSuppliesAttribute()
    {
        return self::stringToArray($this->attributes['supplies']);
    }

    // **********************************************************
    // QUERY SCOPES
    // **********************************************************

    // **********************************************************
    // OTHER FUNCTIONS
    // **********************************************************

    /**
     * Attributes that are booleans.
     *
     * @var array
     */
    protected static $boolKeys = ['lives_alone', 'smoker', 'alcohol', 'incompetent', 'can_provide_direction', 'assist_medications'];

    /**
     * Attributes that are imploded arrays.
     *
     * @var array
     */
    protected static $arrayKeys = [
        'pets',
        'safety_measures',
        'toileting',
        'bathing',
        'diet',
        'skin',
        'oral',
        'nails',
        'dressing',
        'housekeeping',
        'errands',
        'supplies',

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
}
