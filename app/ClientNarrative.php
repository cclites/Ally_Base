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
}
