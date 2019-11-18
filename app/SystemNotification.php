<?php

namespace App;

use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use function Sentry\addBreadcrumb;


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
     * @param string $note
     * @return bool
     */
    public function acknowledge($note = null)
    {
        return $this->update([
            'acknowledged_at' => Carbon::now(),
            'notes' => $note,
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
