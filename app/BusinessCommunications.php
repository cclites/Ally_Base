<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToOneBusiness;
use App\Business;

/**
 * App\BusinessCommunications
 *
 * @property int $id
 * @property string $reply_option
 * @property string $week_start
 * @property string $week_end
 * @property string $weekend_start
 * @property string $weekend_end
 * @property string|null $message
 * @property int $business_id
 * @property-read \App\Business $business
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BusinessCommunications forBusinesses($businessIds)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BusinessCommunications forRequestedBusinesses($businessIds = null, \App\User $authorizedUser = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BusinessCommunications newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BusinessCommunications newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BaseModel ordered($direction = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BusinessCommunications query()
 * @mixin \Eloquent
 */
class BusinessCommunications extends BaseModel{

    use BelongsToOneBusiness;

    protected $table = "business_communications_settings";
    protected $fillable = ['business_id'];

    public $timestamps = false;

    public function business(){
        return $this->belongsTo(Business::class);
    }

}
