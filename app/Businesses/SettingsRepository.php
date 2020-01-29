<?php

namespace App\Businesses;

use App\Business;
use Illuminate\Cache\CacheManager;
use Illuminate\Support\Arr;

class SettingsRepository
{
    /**
     * @var \Illuminate\Cache\CacheManager
     */
    protected $cache;

    /**
     * @var array
     */
    protected $memory = [];

    /**
     * Business fields that do not need caching
     *
     * @var array
     */
    protected $removed = [
        'id',
        'active',
    ];

    public function __construct(CacheManager $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @param $business
     * @param null|string $setting
     * @param mixed $default
     * @return mixed
     */
    public function get($business, $setting = null, $default = null)
    {
        $key = $this->getCacheKey($business);

        if (!isset($this->memory[$key])) {
            $this->memory[$key] = $this->cache->remember($key, 1440, function () use ($business) {
                $business = $this->resolveBusinessModel($business);
                return Arr::except(optional($business)->getAttributes() ?? [], $this->removed);
            });
        }

        return $setting ? Arr::get($this->memory[$key], $setting, $default) : $this->memory[$key];
    }

    /**
     * @param $business
     * @param array|string $setting
     * @param mixed $value
     * @return bool
     */
    public function set($business, $setting, $value = null)
    {
        $business = $this->resolveBusinessModel($business);

        if (!is_array($setting)) {
            $setting = [$setting => $value];
        }

        $this->forget($business);
        return $business->update($setting);
    }

    /**
     * @param $business
     * @return void
     */
    public function forget($business)
    {
        $key = $this->getCacheKey($business);
        unset($this->memory[$key]);
        $this->cache->forget($key);
    }

    /**
     * @param $business
     * @return Business
     */
    protected function resolveBusinessModel($business)
    {
        if ($business instanceof Business) {
            return $business;
        }

        $id = $this->resolveBusinessId($business);
        return Business::find($id);
    }

    /**
     * @param $business
     * @return int
     */
    protected function resolveBusinessId($business)
    {
        if (is_object($business)) {
            $business = $business->id;
        }

        return $business;
    }

    /**
     * @param $business
     * @return string
     */
    protected function getCacheKey($business)
    {
        $id = $this->resolveBusinessId($business);

        return "business_settings_{$id}";
    }
}
