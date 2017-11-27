<?php

namespace App\Http\Controllers;

use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;
use App\PhoneNumber;
use App\Rules\PhonePossible;
use App\User;
use Illuminate\Http\Request;

class PhoneController
{
    public function index()
    {
        $numbers = PhoneNumber::where('user_id', auth()->id())->get();
        return response()->json($numbers);
    }

    public function store()
    {
        $data = request()->validate([
            'number' => ['required', new PhonePossible()],
            'extension' => 'nullable|numeric',
            'type' => 'required'
        ]);

        $user = request()->has('user_id') ? User::find(request('user_id')) : auth()->user();

        $phone = new PhoneNumber();
        $phone->type = $data['type'];
        $phone->input($data['number'], $data['extension']);
        if ($phone = $user->phoneNumbers()->save($phone)) {
            return response()->json($phone);
        }
        return new ErrorResponse(500, 'The phone number could not be saved.');
    }

    public function upsert(Request $request, User $user, $type, $reference = 'The phone number')
    {
        $data = $request->validate([
            'number' => ['required', new PhonePossible()],
            'extension' => 'nullable|numeric',
        ]);

        if (!isset($data['extension'])) $data['extension'] = null;

        $phone = $user->phoneNumbers->where('type', $type)->first();
        if ($phone) {
            if ($phone->input($data['number'], $data['extension'])->save()) {
                return new SuccessResponse($reference . ' has been saved.');
            }
        } else {
            $phone = new PhoneNumber();
            $phone->type = $type;
            $phone->input($data['number'], $data['extension']);
            if ($user->phoneNumbers()->save($phone)) {
                return new SuccessResponse($reference . ' has been saved.');
            }
        }

        return new ErrorResponse(500, $reference . ' could not be saved.');
    }

    public function update($id)
    {
        $data = request()->validate([
            'number' => ['required', new PhonePossible()],
            'extension' => 'nullable|numeric',
            'type' => 'required'
        ]);

        if (!isset($data['extension'])) $data['extension'] = null;

        $phone = PhoneNumber::find($id);
        if ($phone->user_id == auth()->id() || auth()->user()->role_type == 'office_user') {
            $phone->type = request('type');
            if ($phone->input($data['number'], $data['extension'])->save()) {
                return new SuccessResponse('The phone number has been saved.');
            }
        }

        return new ErrorResponse(500, 'The phone number could not be saved.');
    }

    public function destroy($id)
    {
        $number = PhoneNumber::find($id);
        if (auth()->id() == $number->user_id && $number->type != 'primary') {
            PhoneNumber::destroy($id);
            return new SuccessResponse('Phone number deleted.');
        }
        return new ErrorResponse(403,'Not authorized.');
    }
}
