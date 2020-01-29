<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait CanHaveEmptyEmail
{
    /**
     * The domain to be used when setting a fake email address.  This
     * should always be a domain in our control that drops the emails
     * to prevent leaking of sensitive information and bounces.
     *
     * @var string
     */
    protected $fakeEmailDomain = 'noemail.allyms.com';

    /**
     * Retrieve the fake email address for a user that does
     * not have an email address.
     *
     * @return string
     */
    public function getAutoEmail()
    {
        return $this->id . '@' . $this->fakeEmailDomain;
    }

    /**
     * Set the generated fake email address for a caregiver that does not have an email address.
     *
     * @return $this
     */
    public function setAutoEmail()
    {
        $this->email = $this->getAutoEmail();
        return $this;
    }

    /**
     * Check if the model has a fake email address.
     *
     * @return bool
     */
    public function hasNoEmail()
    {
        return Str::endsWith($this->email, '@' . $this->fakeEmailDomain);
    }
}