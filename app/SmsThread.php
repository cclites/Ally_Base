<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\SmsThreadRecipient;
use Carbon\Carbon;

class SmsThread extends Model
{
    /**
     * The attributes that should not be mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * The attributes that should be automatically casts as dates.
     *
     * @var array
     */
    protected $dates = ['sent_at'];

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
     * Get the thread recipients.
     *
     * @return Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function recipients()
    {
        return $this->hasMany(SmsThreadRecipient::class);
    }

    /**
     * Determine if the thread was flagged to accept replies
     * and is still within the reply threshold time limit.
     *
     * @return boolean
     */
    public function isAcceptingReplies()
    {
        if (! $this->can_reply) {
            return false;
        }

        $expiresAt = Carbon::now()->subMinutes(config('sms.reply_threshold', 120));

        if ($this->sent_at->lt($expiresAt)) {
            return false;
        }

        return true;
    }
}
