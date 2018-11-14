<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\SmsThreadReply
 *
 * @property int $id
 * @property int|null $business_id
 * @property int|null $sms_thread_id
 * @property int|null $user_id
 * @property string $from_number
 * @property string $to_number
 * @property string $message
 * @property string $twilio_message_id
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \App\Business|null $business
 * @property-read \App\SmsThread $thread
 * @property-read \App\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SmsThreadReply whereBusinessId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SmsThreadReply whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SmsThreadReply whereFromNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SmsThreadReply whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SmsThreadReply whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SmsThreadReply whereSmsThreadId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SmsThreadReply whereToNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SmsThreadReply whereTwilioMessageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SmsThreadReply whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SmsThreadReply whereUserId($value)
 * @mixin \Eloquent
 */
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
