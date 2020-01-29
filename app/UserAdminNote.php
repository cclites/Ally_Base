<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\UserAdminNote
 *
 * @property int $id
 * @property int $creator_user_id
 * @property int $subject_user_id
 * @property string $body
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\User $creator
 * @property-read \App\User $subject
 * @method static \Illuminate\Database\Eloquent\Builder|\App\UserAdminNote forSubject($subject_user_id)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\UserAdminNote newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\UserAdminNote newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\UserAdminNote query()
 * @mixin \Eloquent
 */
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
