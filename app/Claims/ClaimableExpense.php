<?php

namespace App\Claims;

use App\Claims\Contracts\ClaimableInterface;
use App\AuditableModel;
use App\Shift;
use Carbon\Carbon;

/**
 * App\Claims\ClaimableExpense
 *
 * @property int $id
 * @property int|null $shift_id
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

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        //
        parent::boot();
    }

    // **********************************************************
    // RELATIONSHIPS
    // **********************************************************

    public function shift()
    {
        return $this->belongsTo(Shift::class);
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
    public function getCaregiverName() : string
    {
        if (empty($this->caregiver_first_name) && empty($this->caregiver_last_name)) {
            return '';
        }

        return $this->caregiver_first_name . ' ' . $this->caregiver_last_name;
    }

    /**
     * Get the start time of the Claimable item.
     *
     * @return null|Carbon
     */
    public function getStartTime() : ?Carbon
    {
        return null;
    }

    /**
     * Get the end time of the Claimable item.
     *
     * @return null|Carbon
     */
    public function getEndTime() : ?Carbon
    {
        return null;
    }
}
