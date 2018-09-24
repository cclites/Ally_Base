<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * Class EmergencyContact
 *
 * @package App
 * @property string $name
 * @property string $phone_number
 * @property string $relationship
 * @property User $user
 * @property int $id
 * @property int $user_id
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\EmergencyContact whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\EmergencyContact whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\EmergencyContact whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\EmergencyContact wherePhoneNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\EmergencyContact whereRelationship($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\EmergencyContact whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\EmergencyContact whereUserId($value)
 * @mixin \Eloquent
 */
class EmergencyContact extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Returns the next free number for priority in the order sequence.
     *
     * @return int
     */
    public static function getNextPriorityForUser($user_id)
    {
        return self::select(\DB::raw('coalesce(max(`priority`), 0) as max_priority'))
            ->where('user_id', $user_id)
            ->get()
            ->first()
            ->max_priority + 1;
    }

    /**
     * Increases the priority value for all of the users contacts
     * at the given index and above, while skipping the excluded contact ID.
     * Used to shift priority down in order to raise the priority for a specific contact.
     *
     * @param \App\User $user_id
     * @param int $priority
     * @param int $exclude_id
     * @return void
     */
    public static function shiftPriorityDownAt($user_id, $priority, $exclude_id)
    {
        $index = $priority;

        self::where('user_id', $user_id)
            ->where('priority', '>=', $priority)
            ->where('id', '!=', $exclude_id)
            ->orderBy('priority')
            ->get()
            ->each(function ($item, $key) use (&$index) {
                $index = $index + 1;
                $item->update(['priority' => $index]);
            });
    }

    /**
     * Decreases the priority value for all of the users contacts
     * above the given index.  Used to bump the priority up when a 
     * contact is deleted.
     *
     * @param \App\User $user_id
     * @param int $priority
     * @return void
     */
    public static function shiftPriorityUpAt($user_id, $priority)
    {
        $index = $priority;

        self::where('user_id', $user_id)
            ->where('priority', '>', $priority)
            ->orderBy('priority')
            ->get()
            ->each(function ($item, $key) use (&$index) {
                $item->update(['priority' => $index]);
                $index = $index + 1;
            });
    }
}
