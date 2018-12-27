<?php
namespace App\Confirmations;

use App\Contracts\CanBeConfirmedInterface;
use App\User;
use Carbon\Carbon;

class Confirmation
{
    public $user;

    public static function createFromToken($token) {
        $user = User::findEncrypted($token);
        if (!$user) return null;
        return new static($user->role);
    }

    public function __construct(CanBeConfirmedInterface $user)
    {
        $this->user = $user;
    }

    public function getToken()
    {
        return $this->user->getEncryptedKey();
    }

    public function touchTimestamp()
    {
        $this->user->update(['email_sent_at' => Carbon::now()]);
    }

    public function isValid($type = 'caregiver')
    {
        if (strcasecmp($this->user->getRoleType(), $type) !== 0) {
            return false;
        }

        if(Carbon::now()->diffInDays($this->user->created_at) > 7) {
            if (!$timestamp = $this->user->email_sent_at) {
                return false;
            }

            $timestamp = new Carbon($timestamp);
            if (Carbon::now()->diffInHours($timestamp) > 72) {
                return false;
            }
        }

        return true;
    }

    public function expire()
    {
        $this->user->update(['email_sent_at' => null]);
    }
}