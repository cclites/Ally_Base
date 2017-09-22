<?php

namespace App\Http\Controllers;

use App\Address;
use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function index()
    {
        $type = auth()->user()->role_type;
        return view('profile.' . $type);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'firstname' => 'required',
            'lastname' => 'required',
            'email' => 'required|email',
            'date_of_birth' => 'nullable|date',
        ]);

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
        $data = $request->validate([
            'address1' => 'required',
            'address2' => 'nullable',
            'city' => 'required',
            'state' => 'required',
            'country' => 'required|size:2',
            'zip' => 'required|min:5'
        ]);

        $address = auth()->user()->addresses->where('type', $type)->first();
        if ($address) {
            if ($address->update($data)) {
                return new SuccessResponse('Your address has been saved.');
            }
        }
        else {
            $address = new Address($data);
            $address->type = $type;
            if (auth()->user()->addresses()->save($address)) {
                return new SuccessResponse('Your address has been saved.');
            }
        }

        return new ErrorResponse(500, 'Unable to save address.');
    }
}
