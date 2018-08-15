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

        $query = User::select(['users.id', 'firstname', 'lastname', 'role_type', 'email'])
            ->whereIn('role_type', ['client', 'caregiver'])
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

        $keys = ['id', 'name', 'role_type'];

        $users = $query->get()->map(function($user) use ($keys) {
            return $user->only($keys);
        });

        return new SuccessResponse(null, $users);
    }
}
