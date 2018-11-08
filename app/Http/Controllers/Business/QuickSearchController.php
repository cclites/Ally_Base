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
            ->orderBy('lastname');

        if (!is_admin_now()) {
            $query->leftJoin('clients', 'clients.id', '=', 'users.id')
                ->leftJoin('business_caregivers', function($join) {
                    $join->on('business_caregivers.business_id', '=', DB::raw((int) $this->business()->id));
                    $join->on('business_caregivers.caregiver_id', '=', 'users.id');
                })
                ->where(function($query) {
                    $query->where('business_caregivers.business_id', $this->business()->id)
                        ->orWhere('clients.business_id', $this->business()->id);
                });
        }

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
                $user->phone = $user->default_phone;
            }
            return $user->only($keys);
        });

        return new SuccessResponse(null, $users);
    }
}
