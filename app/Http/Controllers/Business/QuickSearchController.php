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

        $q = request('q');
        $type = request('type');

        //If we are searching for a phone number, change the type and remove
        //any non-digit separators from the string.
        if(1 === preg_match('~[0-9]~', $q)){
            $type = 'phone';
            $q = preg_replace("/[^0-9]/", "", $q);

            //Load phone numbers relation
            $query->with('phoneNumbers');
        }

        if($type === 'role'){
            $query->where(function($query) use($q){
                if (\App::runningUnitTests()) {
                    // check if testing enviornment because sqlite doesn't have CONCAT function
                    $query->whereRaw("printf('%s %s', firstname, lastname) like ?", ["%$q%"]);
                }
                else {
                    $query->whereRaw("CONCAT(firstname, ' ', lastname) like ?", ["%$q%"]);
                }
                $query->orWhere('email', 'LIKE', "%$q%");
            });
        }elseif($type === 'phone'){
            $query->whereHas('phoneNumbers', function($query) use($q){
                $query->where('national_number', 'LIKE', "%$q%");
            });
        }

        switch($type) {
            case 'sms':
            case 'phone':
                $keys = ['id', 'name', 'role_type', 'phone'];
                break;
            default:
                $keys = ['id', 'name', 'role_type', 'email'];
        }

        $users = $query->get()->map(function($user) use ($keys) {
            return $user->only($keys);
        });

        return new SuccessResponse(null, $users);
    }
}
