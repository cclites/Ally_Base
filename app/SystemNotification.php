<?php

namespace App;

use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use function Sentry\addBreadcrumb;


/**
 * App\SystemNotification
 *
 * @property int $id
 * @property int $user_id
 * @property string|null $key
 * @property string|null $message
 * @property string|null $action
 * @property string|null $action_url
 * @property string|null $reference_type
 * @property string|null $reference_id
 * @property string|null $acknowledged_at
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $event_id
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read int|null $audits_count
 * @property-read string $title
 * @property-read \App\SystemNotification|null $reference
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SystemNotification newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SystemNotification newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SystemNotification notAcknowledged()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BaseModel ordered($direction = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SystemNotification query()
 * @mixin \Eloquent
 */
class SystemNotification extends AuditableModel
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
    protected $appends = ['title'];

    // **********************************************************
    // RELATIONSHIPS
    // **********************************************************

    /**
     * A system notification morphs to a reference.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
    */
    public function reference()
    {
        return $this->morphTo('reference');
    }

    // **********************************************************
    // MUTATORS
    // **********************************************************

    /**
     * Convert the notification ID to a title.
     *
     * @return string
     */
    public function getTitleAttribute()
    {
        return str_replace('_', ' ', ucwords($this->key, '_'));
    }

    // **********************************************************
    // QUERY SCOPES
    // **********************************************************

    /**
     * Get only un-acknowledged notifications.
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeNotAcknowledged($query)
    {
        return $query->whereNull('acknowledged_at');
    }

    // **********************************************************
    // OTHER FUNCTIONS
    // **********************************************************

    /**
     * Set notification as acknowledged and update the notes.
     *
     * @return bool
     */
    public function acknowledge()
    {
        return $this->update([
            'acknowledged_at' => Carbon::now(),
        ]);
    }

    /**
     * Generate a unique event ID.
     *
     * @return string
     */
    public static function generateUniqueEventId() : string
    {
        while (true) {
            $id = Str::random(12);
            if (SystemNotification::where('event_id', $id)->exists()) {
                continue;
            }

            break;
        }

        return $id;
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
            'message' => $faker->sentence,
        ];
    }
}
