<?php

namespace App\Http\Controllers\Business;

use App\Responses\SuccessResponse;
use App\User;
use DB;

class QuickSearchController extends BaseController
{
    public function index()
    {
        if (!request()->has('q')) {
            return new SuccessResponse(null, []);
        }

        $roles = ['client', 'caregiver'];
        
        if (request()->role == 'caregiver') {
            $roles = ['caregiver'];
        } else if (request()->role == 'client') {
            $roles = ['client'];
        }

        $query = User::select(['users.id', 'firstname', 'lastname', 'role_type', 'email'])
            ->whereIn('role_type', $roles)
            ->where('active', 1)
            ->orderBy('firstname')
            ->orderBy('lastname')
            ->where(function($query) {
                $query->whereHas('caregiver', function($q) {
                    $q->forAuthorizedChain();
                })->orWhereHas('client', function($q) {
                    $q->forRequestedBusinesses();
                });
            });


        $query->where(function($query) {
            $q = request('q');
            if (\App::runningUnitTests()) {
                // check if testing enviornment because sqlite doesn't have CONCAT function
                $query->whereRaw("printf('%s %s', firstname, lastname) like ?", ["%$q%"]);
            }
            else {
                $query->whereRaw("CONCAT(firstname, ' ', lastname) like ?", ["%$q%"]);
            }
            $query->orWhere('email', 'LIKE', "%$q%");
        });

        switch(request('type')) {
            case 'sms':
            case 'phone':
                $query->whereHas('phoneNumbers');
                $query->with('phoneNumbers');
                $keys = ['id', 'name', 'role_type', 'phone'];
                break;
            default:
                $keys = ['id', 'name', 'role_type', 'email'];
        }

        $users = $query->get()->map(function($user) use ($keys) {
            if ($user->relationLoaded('phoneNumbers')) {
                if (request('type') == 'sms') {
                    $user->phone = $user->smsNumber ? $user->smsNumber->number : $user->default_phone;
                } else {
                    $user->phone = $user->default_phone;
                }
            }
            return $user->only($keys);
        });

        return new SuccessResponse(null, $users);
    }
}
