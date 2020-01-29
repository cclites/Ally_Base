<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait CanHaveEmptyUsername
{
    /**
     * The prefix to be attached to fake usernames to 
     * identify them.
     *
     * @var string
     */
    protected static $fakeUsernamePrefix = 'no_login_';

    /**
     * Retrieve the fake username for a user.
     *
     * @return string
     */
    public static function getAutoUsername() : string
    {
        return self::$fakeUsernamePrefix . md5(microtime());
    }

    /**
     * Set the generated fake username for a user.
     *
     * @return $this
     */
    public function setAutoUsername()
    {
        $this->username = self::getAutoUsername();
        return $this;
    }

    /**
     * Check if the model has a fake username.
     *
     * @return bool
     */
    public function hasNoUsername()
    {
        return Str::startsWith($this->username, self::$fakeUsernamePrefix);
    }
}