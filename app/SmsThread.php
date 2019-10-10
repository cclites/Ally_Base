<?php

namespace App;

use App\Contracts\BelongsToBusinessesInterface;
use App\Traits\BelongsToOneBusiness;
use Carbon\Carbon;

/**
 * App\SmsThread
 *
 * @property int $id
 * @property int $business_id
 * @property string $from_number
 * @property string $message
 * @property int $can_reply
 * @property \Carbon\Carbon $sent_at
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \App\Business $business
 * @property-read \App\Audit $auditTrail
 * @property-read \Illuminate\Database\Eloquent\Relations\HasMany $unique_recipient_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\SmsThreadRecipient[] $recipients
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\SmsThreadReply[] $replies
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SmsThread forBusinesses($businessIds)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SmsThread forRequestedBusinesses($businessIds = null, \App\User $authorizedUser = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BaseModel ordered($direction = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SmsThread whereBusinessId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SmsThread whereCanReply($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SmsThread whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SmsThread whereFromNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SmsThread whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SmsThread whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SmsThread whereSentAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SmsThread whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class SmsThread extends AuditableModel implements BelongsToBusinessesInterface
{
    use BelongsToOneBusiness;

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
     * The attributes that should be automatically append to the model.
     *
     * @var array
     */
    protected $appends = ['unique_recipient_count', 'unread_replies_count'];

    // **********************************************************
    // RELATIONSHIPS
    // **********************************************************

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
     * Get the thread replies relation.
     *
     * @return Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function replies()
    {
        return $this->hasMany(SmsThreadReply::class);
    }

    /**
     * Get the unread thread replies relation.
     *
     * @return Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function unreadReplies()
    {
        return $this->hasMany(SmsThreadReply::class)
            ->whereNull('read_at');
    }

    // **********************************************************
    // MUTATORS
    // **********************************************************

    /**
     * Get the thread recipients.
     *
     * @return Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function getUniqueRecipientCountAttribute()
    {
        return $this->recipients()
            ->select('user_id')
            ->distinct()
            ->get()->count();
    }

    /**
     * Get the number of unread replies.
     *
     * @return int
     */
    public function getUnreadRepliesCountAttribute()
    {
        return $this->unreadReplies()->count();
    }

    // **********************************************************
    // QUERY SCOPES
    // **********************************************************

    /**
     * Gets shifts that are checked in between given given start and end dates.
     * Automatically applies timezone transformation.
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @param string $start
     * @param string $end
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeBetweenDates($query, $start, $end)
    {
        if (empty($start) || empty($end)) {
            return $query;
        }

        $startDate = (new Carbon($start . ' 00:00:00', 'America/New_York'))->setTimezone('UTC');
        $endDate = (new Carbon($end . ' 23:59:59', 'America/New_York'))->setTimezone('UTC');
        return $query->whereBetween('sent_at', [$startDate, $endDate]);
    }

    /**
     * Get the threads that have replies.
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @return \Illuminate\Database\Query\Builder
     */
    public function scopeWithReplies($query, bool $onOff = false)
    {
        if ($onOff) {
            return $query->whereHas('replies', function ($q) {
                $q->whereNotNull('id');
            });
        }

        return $query;
    }

    // **********************************************************
    // OTHER FUNCTIONS
    // **********************************************************

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

    /**
     * Check if the thread was created with the given user as a recipient.
     *
     * @param string|null $user_id
     * @return boolean
     */
    public function hasRecipient(?string $user_id = null) : bool
    {
        if (empty($user_id)) {
            return false;
        }

        return $this->recipients()->where('user_id', $user_id)->exists();
    }

    public function sentBy()
    {
        if ($this->sent_by_user_id) {
            return User::find($this->sent_by_user_id)->nameLastFirst();
        }

        return 'Unknown';
    }
}
