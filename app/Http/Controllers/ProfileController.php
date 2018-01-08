<?php

namespace App\Http\Controllers;

use App\Address;
use App\Client;
use App\Http\Requests\UpdateProfileRequest;
use App\PhoneNumber;
use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;
use App\Rules\PhonePossible;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function index()
    {
        $type = auth()->user()->role_type;
        $user = auth()->user()->load('phoneNumbers');
        
        // include a placeholder for the primary number if one doesn't already exist
        if ($user->phoneNumbers->where('type', 'primary')->count() == 0) {
            $user->phoneNumbers->push(['type' => 'primary', 'extension' => '', 'number' => '']);
        }

        // include a placeholder for the billing number if one doesn't already exist
        if ($type == 'client' && $user->phoneNumbers->where('type', 'billing')->count() == 0) {
            $user->phoneNumbers->push(['type' => 'billing', 'extension' => '', 'number' => '']);
        }

        return view('profile.' . $type, compact('user'));
    }

    public function update(UpdateProfileRequest $request)
    {
        $poa_fields = ['poa_first_name', 'poa_last_name', 'poa_phone', 'poa_relationship'];
        $data = $request->except($poa_fields);

        if(auth()->user()->role_type == 'client') {
            $client_data = $request->only($poa_fields);
            if(!empty($client_data)) {
                $client = Client::find(auth()->id());
                $client->update($client_data);
            }
        }

        $data['date_of_birth'] = filter_date($data['date_of_birth']);

        if (auth()->user()->update($data)) {
            return new SuccessResponse('Your profile has been updated.');
        }
        return new ErrorResponse(500, 'Unable to update profile.');
    }

    public function password(Request $request)
    {
        $messages = ['password.regex' => "Your password must contain one lower case, one upper case, and one number"];
        $request->validate([
            'password' => 'required|confirmed|min:8|regex:/^(?=\S*[a-z])(?=\S*[A-Z])(?=\S*[\d])\S*$/'
        ], $messages);

        if (auth()->user()->changePassword($request->input('password'))) {
            return new SuccessResponse('Your password has been updated.');
        }
        return new ErrorResponse(500, 'Unable to update password.');
    }

    public function address(Request $request, $type)
    {
        $user = auth()->user();
        return (new AddressController())->update($request, $user, $type, 'Your address');
    }

    public function phone(Request $request, $type)
    {
        $user = auth()->user();
        return (new PhoneController())->upsert($request, $user, $type, 'Your phone number');
    }
}
