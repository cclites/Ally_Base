<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SmsThreadReply extends Model
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
    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * Get the user that sent the message.
     *
     * @return Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the owning sms thread.
     *
     * @return Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function thread()
    {
        return $this->belongsTo(SmsThread::class);
    }
}
