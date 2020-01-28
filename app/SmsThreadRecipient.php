<?php
namespace App;

use App\Traits\ScrubsForSeeding;
use Illuminate\Database\Eloquent\Model;

/**
 * App\SmsThreadRecipient
 *
 * @property int $id
 * @property int $sms_thread_id
 * @property int $user_id
 * @property string $number
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \App\SmsThread $thread
 * @property-read \App\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BaseModel ordered($direction = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SmsThreadRecipient whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SmsThreadRecipient whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SmsThreadRecipient whereNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SmsThreadRecipient whereSmsThreadId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SmsThreadRecipient whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SmsThreadRecipient whereUserId($value)
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SmsThreadRecipient newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SmsThreadRecipient newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\SmsThreadRecipient query()
 */
class SmsThreadRecipient extends BaseModel
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
            'number' => $faker->simple_phone,
        ];
    }
}
