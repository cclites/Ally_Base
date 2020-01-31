<?php
namespace App;

use App\Traits\BelongsToOneBusiness;
use App\Contracts\BelongsToBusinessesInterface;
use App\Events\SmsThreadReplyCreated;
use App\Traits\ScrubsForSeeding;
use Carbon\Carbon;
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
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BaseModel ordered($direction = null)
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
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SmsThreadReply forBusinesses($businessIds)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SmsThreadReply forRequestedBusinesses($businessIds = null, \App\User $authorizedUser = null)
 * @property string|null $read_at
 * @property string|null $media_url
 * @property int|null $continued_thread_id
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SmsThreadReply betweenDates($start, $end)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SmsThreadReply newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SmsThreadReply newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SmsThreadReply query()
 */
class SmsThreadReply extends BaseModel implements BelongsToBusinessesInterface
{
    use BelongsToOneBusiness;

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

    protected $dispatchesEvents = [
        'created' => SmsThreadReplyCreated::class,
    ];

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
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    // **********************************************************
    // ScrubsForSeeding Methods
    // **********************************************************
    use ScrubsForSeeding;

    /**
     * Get an array of scrubbed data to replace the original.
     *
     * @param \Faker\Generator $faker
     * @param bool $fast
     * @param null|Model $item
     * @return array
     */
    public static function getScrubbedData(\Faker\Generator $faker, bool $fast, ?\Illuminate\Database\Eloquent\Model $item) : array
    {
        return [
            'from_number' => $faker->simple_phone,
            'to_number' => $faker->simple_phone,
            'message' => $faker->sentence,
            'media_url' => $faker->url,
        ];
    }
}
