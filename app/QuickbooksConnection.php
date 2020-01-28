<?php

namespace App;

use App\Services\QuickbooksOnlineService;
use Crypt;
use QuickBooksOnline\API\Core\OAuth\OAuth2\OAuth2AccessToken;

/**
 * App\QuickbooksConnection
 *
 * @property int $id
 * @property int $business_id
 * @property string|null $company_name
 * @property mixed|null $access_token
 * @property int $is_desktop
 * @property string|null $desktop_api_key
 * @property string $name_format
 * @property string $fee_type_lead_agency
 * @property string $fee_type_ltci
 * @property string $fee_type_medicaid
 * @property string $fee_type_private_pay
 * @property string $fee_type_va
 * @property int|null $shift_service_id
 * @property int|null $adjustment_service_id
 * @property int|null $refund_service_id
 * @property int|null $mileage_service_id
 * @property int|null $expense_service_id
 * @property int $allow_shift_overrides
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $last_connected_at
 * @property-read \App\QuickbooksService $adjustmentService
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read int|null $audits_count
 * @property-read \App\Business $business
 * @property-read \App\QuickbooksService $expenseService
 * @property-read \App\QuickbooksService $mileageService
 * @property-read \App\QuickbooksService $refundService
 * @property-read \App\QuickbooksService $shiftService
 * @method static \Illuminate\Database\Eloquent\Builder|\App\QuickbooksConnection newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\QuickbooksConnection newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\BaseModel ordered($direction = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\QuickbooksConnection query()
 * @mixin \Eloquent
 */
class QuickbooksConnection extends AuditableModel
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    public $with = [];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['last_connected_at'];

    const FEE_TYPE_REGISTRY = 'registry';
    const FEE_TYPE_CLIENT = 'client';
    const NAME_FORMAT_LAST_FIRST = 'last_first';
    const NAME_FORMAT_FIRST_LAST = 'first_last';

    // **********************************************************
    // RELATIONSHIPS
    // **********************************************************

    /**
     * Get the business relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
    */
    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * Get the mileage service relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
    */
    public function mileageService()
    {
        return $this->hasOne(QuickbooksService::class, 'id', 'mileage_service_id');
    }

    /**
     * Get the expense service relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
    */
    public function expenseService()
    {
        return $this->hasOne(QuickbooksService::class, 'id', 'expense_service_id');
    }

    /**
     * Get the shift service relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
    */
    public function shiftService()
    {
        return $this->hasOne(QuickbooksService::class, 'id', 'shift_service_id');
    }

    /**
     * Get the adjustment service relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
    */
    public function adjustmentService()
    {
        return $this->hasOne(QuickbooksService::class, 'id', 'adjustment_service_id');
    }

    /**
     * Get the refund service relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
    */
    public function refundService()
    {
        return $this->hasOne(QuickbooksService::class, 'id', 'refund_service_id');
    }

    // **********************************************************
    // MUTATORS
    // **********************************************************

    /**
     * Get and decrypt the access token object.
     *
     * @return mixed|null
     */
    public function getAccessTokenAttribute()
    {
        return empty($this->attributes['access_token']) ? null : unserialize(Crypt::decrypt($this->attributes['access_token']));
    }

    /**
     * Set and encrypt the access token object.
     *
     * @param OAuth2AccessToken|null $value
     */
    public function setAccessTokenAttribute(?OAuth2AccessToken $value)
    {
        $this->attributes['access_token'] = $value ? Crypt::encrypt(serialize($value)) : null;
    }

    // **********************************************************
    // QUERY SCOPES
    // **********************************************************

    // **********************************************************
    // OTHER FUNCTIONS
    // **********************************************************

    /**
     * Get a configured API service.
     *
     * @return QuickbooksOnlineService|null
     */
    public function getApiService()
    {
        try {
            $service = app(QuickbooksOnlineService::class)
                ->setAccessToken($this->access_token);

            // Automatically handle all token refreshes from this access point.
            if ($service->autoRefreshToken()) {
                $this->update(['access_token' => $service->accessToken]);
            }

            return $service;
        }
        catch (\Exception $ex) {
            app('sentry')->captureException($ex);
            return null;
        }
    }

    /**
     * Check if the quickbooks connection is authenticated.
     *
     * @return bool
     */
    public function isAuthenticated() : bool
    {
        return (!$this->attributes['is_desktop'] && !empty($this->attributes['access_token']))
            || ($this->attributes['is_desktop'] && !empty($this->attributes['desktop_api_key']));
    }

    /**
     * Validate that the connection is fully configured.
     *
     * @return bool
     */
    public function isConfigured() : bool
    {
        if (! $this->isAuthenticated()) {
            return false;
        }

        if (empty($this->mileage_service_id)) {
            return false;
        }

        if (empty($this->shift_service_id)) {
            return false;
        }

        if (empty($this->refund_service_id)) {
            return false;
        }

        if (empty($this->expense_service_id)) {
            return false;
        }

        if (empty($this->adjustment_service_id)) {
            return false;
        }

        return true;
    }
    
    /**
     * Get the name_format setting for the connection.
     *
     * @return string
     */
    public function getNameFormat() : string
    {
        return $this->name_format;
    }

    /**
     * Get the connections fee type based on a client's client type.
     *
     * @param Client $client
     * @return string
     */
    public function getFeeType(Client $client) : string
    {
        $field = 'fee_type_' . strtolower($client->client_type);

        return $this->$field;
    }
    
    // **********************************************************
    // ScrubsForSeeding Methods
    // **********************************************************
    use \App\Traits\ScrubsForSeeding;
    
    /**
     * Get an array of scrubbed data to replace the original.
     *
     * @param \Faker\Generator $faker
     * @param bool $fast
     * @param null|\Illuminate\Database\Eloquent\Model $item
     * @return array
     */
    public static function getScrubbedData(\Faker\Generator $faker, bool $fast, ?\Illuminate\Database\Eloquent\Model $item) : array
    {
        return [
            'company_name' => $faker->company,
        ];
    }
}
