<?php
namespace App\Policies;

use App\Traits\ActiveBusiness;
use Illuminate\Auth\Access\HandlesAuthorization;

abstract class BasePolicy
{
    use HandlesAuthorization;
    use ActiveBusiness;

    protected function isAdmin() {
        return auth()->user()->role_type === 'admin';
    }

    protected function isOfficeUser() {
        return auth()->user()->role_type === 'office_user';
    }
}