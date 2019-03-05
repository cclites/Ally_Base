<?php

namespace App\Http\Controllers\Business;

use App\Business;
use App\BusinessChain;
use App\Http\Controllers\Controller;
use App\Traits\ActiveBusiness;
use App\Users\OfficeUserSettings;
use App\Users\SettingsRepository;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class BaseController extends Controller
{
    use ActiveBusiness;

    private $activeBusinessChain;
    private $settings;

    public function userSettings(): OfficeUserSettings
    {
        if (!$this->settings) {
            return $this->settings = app(SettingsRepository::class)->getOfficeUserSettings(\Auth::user());
        }

        return $this->settings;
    }

    /**
     * Return the active business chain
     *
     * @return \App\BusinessChain
     * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
     */
    protected function businessChain(): BusinessChain
    {
        if ($this->activeBusinessChain) {
            return $this->activeBusinessChain;
        }

        $chain = optional(\Auth::user()->officeUser)->businessChain;
        if (!$chain) {
            throw new AccessDeniedHttpException('A business chain was not found.');
        }

        return $chain;
    }

    /**
     * Override the active business chain (used for Admins)
     *
     * @param \App\BusinessChain $businessChain
     */
    protected function setBusinessChainAs(BusinessChain $businessChain)
    {
        $this->activeBusinessChain = $businessChain;
    }

    /**
     * Override the active business (used for Admins)
     *
     * @param \App\Business $business
     */
    protected function setBusinessAs(Business $business)
    {
        $this->setBusinessChainAs($business->chain);

        // @deprecated below
        $activeBusiness = app()->make(\App\ActiveBusiness::class);
        $activeBusiness->set($business);
    }

    /**
     * Check if the office user has access to $business
     *
     * @param int|\App\Business $business   A business model or a business id
     * @return mixed
     */
    public function canAccessBusiness($business)
    {
        return auth()->user()->belongsToBusiness($business);
    }

}
