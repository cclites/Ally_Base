<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class ShiftGoal extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    
    protected $table = 'shift_goals';

    protected $guarded = ['id'];

    protected $with = ['goal'];

    ///////////////////////////////////////////
    /// Relationship Methods
    ///////////////////////////////////////////

    /**
     * A ShiftGoal belongs to a Client.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * A ShiftGoal has one Goal.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function goal()
    {
        return $this->hasOne(ClientGoal::class, 'client_goal_id', 'id');
    }
}
