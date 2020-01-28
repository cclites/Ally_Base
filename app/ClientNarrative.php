<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\ClientNarrative
 *
 * @property int $id
 * @property int $client_id
 * @property int $creator_id
 * @property string $notes
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read \App\User $creator
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientNarrative whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientNarrative whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientNarrative whereCreatorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientNarrative whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientNarrative whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientNarrative whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientNarrative whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property-read \App\Client $client
 * @property-read void $is_owner
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientNarrative newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientNarrative newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ClientNarrative query()
 */
class ClientNarrative extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'client_narrative';

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
    public $with = ['creator'];
    
    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['is_owner'];
    
    // **********************************************************
    // RELATIONSHIPS
    // **********************************************************
    
    /**
     * Get the creating user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
    */
    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    /**
     * Get the client relatioship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\
    */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    // **********************************************************
    // MUTATORS
    // **********************************************************

    /**
     * Flag for if the note is owned by the current user.
     *
     * @return void
     */
    public function getIsOwnerAttribute()
    {
        return $this->creator_id === auth()->id();
    }

    // **********************************************************
    // QUERY SCOPES
    // **********************************************************
    
    // **********************************************************
    // OTHER FUNCTIONS
    // **********************************************************

    // **********************************************************
    // ScrubsForSeeding Methods
    // **********************************************************
    use \App\Traits\ScrubsForSeeding { getScrubQuery as parentGetScrubQuery; }

    /**
     * Get the query used to identify records that will be scrubbed.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function getScrubQuery() : \Illuminate\Database\Eloquent\Builder
    {
        return static::parentGetScrubQuery()->whereNotNull('notes');
    }

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
            'notes' => $faker->sentence,
        ];
    }
}
