<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ClientContact extends Model
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
        parent::boot();

        static::creating(function ($model) {
            // Automatically set the priority value for new emergency contacts.
            if ($model->is_emergency) {
                $model->emergency_priority = self::getNextPriority($model->client_id);
            }
        });

        static::updating(function ($model) {
            if ($model->isDirty('is_emergency')) {
                // Automatically set the priority value for regular contacts
                // that are converted to emergency contacts.
                if ($model->is_emergency == 1) {
                    $model->emergency_priority = self::getNextPriority($model->client_id);
                }
                // Automatically clear the priority value when an emergency contact
                // is removed and re-order the rest of the emergency contact priorities.
                else {
                    $priority = $model->getOriginal('emergency_priority');
                    $model->emergency_priority = null;
                    self::shiftPriorityUpAt($model->client_id, $priority);
                }
            }
        });
    }
    
    // **********************************************************
    // Contact Relationship Options
    // **********************************************************
    
    const RELATION_FAMILY = 'family';
    const RELATION_POA = 'poa';
    const RELATION_PHYSICIAN = 'physician';
    const RELATION_OTHER = 'other';
    const RELATION_CUSTOM = 'custom';

    // **********************************************************
    // RELATIONSHIPS
    // **********************************************************
    
    /**
     * Get the Client relation.
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
    
    /**
     * Get the contact's first name.
     *
     * @return string
     */
    public function getFirstNameAttribute()
    {
        if (strpos($this->name, ' ') >= 0) {
            return substr($this->name, 0, strpos($this->name, ' '));
        }

        return $this->name;
    }

    /**
     * Get the contact's last name.
     *
     * @return string
     */
    public function getLastNameAttribute()
    {
        if (strpos($this->name, ' ') >= 0) {
            return substr($this->name, strpos($this->name, ' ') + 1);
        }

        return null;
    }





    // **********************************************************
    // QUERY SCOPES
    // **********************************************************
    
    // **********************************************************
    // OTHER FUNCTIONS
    // **********************************************************

    /**
     * Get the valid relationship values.
     *
     * @return array
     */
    public static function validRelationships() : array
    {
        return [
            self::RELATION_CUSTOM,
            self::RELATION_FAMILY,
            self::RELATION_PHYSICIAN,
            self::RELATION_POA,
            self::RELATION_OTHER,
        ];
    }

    /**
     * Returns the next free number for priority in the order sequence.
     *
     * @return int
     */
    public static function getNextPriority(int $client_id) : int
    {
        return self::select(\DB::raw('coalesce(max(`emergency_priority`), 0) as max_priority'))
            ->where('client_id', $client_id)
            ->get()
            ->first()
            ->max_priority + 1;
    }

    /**
     * Increases the priority value for all of the emergency contacts
     * at the given index and above, while skipping the excluded contact ID.
     * Used to shift priority down in order to raise the priority for a specific contact.
     *
     * @param int $client_id
     * @param int $priority
     * @param int $exclude_id
     * @return void
     */
    public static function shiftPriorityDownAt(int $client_id, int $priority, int $exclude_id) : void
    {
        $index = $priority;

        self::where('client_id', $client_id)
            ->where('emergency_priority', '>=', $priority)
            ->where('id', '!=', $exclude_id)
            ->orderBy('emergency_priority')
            ->get()
            ->each(function ($item, $key) use (&$index) {
                $index = $index + 1;
                $item->update(['emergency_priority' => $index]);
            });
    }

    /**
     * Decreases the priority value for all of the emergency contacts
     * above the given index.  Used to bump the priority up when a 
     * contact is deleted.
     *
     * @param int $client_id
     * @param int $priority
     * @return void
     */
    public static function shiftPriorityUpAt(int $client_id, int $priority) : void
    {
        $index = $priority;

        self::where('client_id', $client_id)
            ->where('emergency_priority', '>', $priority)
            ->orderBy('emergency_priority')
            ->get()
            ->each(function ($item, $key) use (&$index) {
                $item->update(['emergency_priority' => $index]);
                $index = $index + 1;
            });
    }
}
