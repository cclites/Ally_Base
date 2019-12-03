<?php

namespace App\Claims;

use App\Caregiver;
use App\Claims\Contracts\ClaimableInterface;
use App\AuditableModel;
use App\Client;
use App\Shift;
use App\Traits\ScrubsForSeeding;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Claims\ClaimableExpense
 *
 * @property int $id
 * @property int|null $shift_id
 * @property int|null $caregiver_id
 * @property string $caregiver_first_name
 * @property string $caregiver_last_name
 * @property string $name
 * @property string $date
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read \App\Shift|null $shift
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Claims\ClaimableExpense newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Claims\ClaimableExpense newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BaseModel ordered($direction = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Claims\ClaimableExpense query()
 * @mixin \Eloquent
 */
class ClaimableExpense extends AuditableModel implements ClaimableInterface
{
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

    // **********************************************************
    // RELATIONSHIPS
    // **********************************************************

    /**
     * Get the related Client.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get the related Shift.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    /**
     * Get the related Caregiver.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function caregiver()
    {
        return $this->belongsTo(Caregiver::class);
    }

    // **********************************************************
    // MUTATORS
    // **********************************************************

    // **********************************************************
    // QUERY SCOPES
    // **********************************************************

    // **********************************************************
    // OTHER FUNCTIONS
    // **********************************************************

    // **********************************************************
    // ClaimableInterface
    // **********************************************************

    /**
     * Get the name of the Claimable Item.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get the Caregiver's name that performed the service.
     *
     * @return string
     */
    public function getCaregiverName(): string
    {
        if (empty($this->caregiver_first_name) && empty($this->caregiver_last_name)) {
            return '';
        }

        return $this->caregiver_last_name . ', ' . $this->caregiver_first_name;
    }

    /**
     * Get the start time of the Claimable item.
     *
     * @return null|Carbon
     */
    public function getStartTime(): ?Carbon
    {
        return null;
    }

    /**
     * Get the end time of the Claimable item.
     *
     * @return null|Carbon
     */
    public function getEndTime(): ?Carbon
    {
        return null;
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
     * @param null|Model $item
     * @return array
     */
    public static function getScrubbedData(\Faker\Generator $faker, bool $fast, ?\Illuminate\Database\Eloquent\Model $item) : array
    {
        return [
            'caregiver_last_name' => $faker->lastName,
            'notes' => $faker->sentence,
        ];
    }
}
