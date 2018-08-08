<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class ClientGoal extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    
    protected $table = 'client_goals';

    protected $guarded = ['id'];

    ///////////////////////////////////////////
    /// Relationship Methods
    ///////////////////////////////////////////

    /**
     * A ClientGoal belongs to a Client
     *
     * @return BelongsTo
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * A ClientGoal can have many shifts.
     *
     * @return BelongsToMany
     */
    public function shifts()
    {
        return $this->belongsToMany(Shift::class);
    }
}
