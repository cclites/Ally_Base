<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SmsThreadRecipient extends Model
{
    /**
     * The attributes that should not be mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * The relations that should be loaded automatically.
     *
     * @var array
     */
    protected $with = ['user'];

    /**
     * Get the business relation.
     *
     * @return Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function thread()
    {
        return $this->belongsTo(SmsThread::class, 'sms_thread_id');
    }

    /**
     * Get the business relation.
     *
     * @return Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(\App\User::class);
    }
}
