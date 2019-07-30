<?php

namespace App;

use App\Services\QuickbooksOnlineService;
use Crypt;
use QuickBooksOnline\API\Core\OAuth\OAuth2\OAuth2AccessToken;

class QuickbooksConnection extends BaseModel
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
        return !empty($this->attributes['access_token']);
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
}
