<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserAdminNote extends Model
{
    protected $guarded = [];

    public function creator()
    {
        return $this->belongsTo( User::class, 'creator_user_id', 'id' );
    }

    public function subject()
    {
        return $this->belongsTo( User::class, 'subject_user_id', 'id' );
    }


    public function scopeForSubject( $query, $subject_user_id )
    {
        return $query->where( 'subject_user_id', $subject_user_id );
    }
}
