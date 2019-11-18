<?php

namespace App;

use App\Services\QuickbooksOnlineService;
use Crypt;
use QuickBooksOnline\API\Core\OAuth\OAuth2\OAuth2AccessToken;

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
        $field = "fee_type_" . strtolower($client->client_type);

        return $this->$field;
    }
}
