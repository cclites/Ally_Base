<?php

namespace App\Http\Controllers;

use App\Http\Requests\SavePhoneNumberRequest;
use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;
use App\PhoneNumber;
use App\Rules\PhonePossible;
use App\User;
use Illuminate\Http\Request;

class PhoneController extends Controller
{
    public function index()
    {
        $numbers = PhoneNumber::where('user_id', auth()->id())->get();
        return response()->json($numbers);
    }

    public function store(SavePhoneNumberRequest $request)
    {
        $data = $request->validated();
        if (empty($data['user_id'])) $data['user_id'] = auth()->user()->id;

        $this->authorize('create', [PhoneNumber::class, $data]);

        $phone = new PhoneNumber($data);
        $phone->input($data['number'], $data['extension']);
        if ($phone->save()) {
            return response()->json($phone);
        }
        return new ErrorResponse(500, 'The phone number could not be saved.');
    }

    public function update(SavePhoneNumberRequest $request, PhoneNumber $phone)
    {
        $data = $request->validated();
        $phone->fill($data);
        $this->authorize('update', $phone);
        if ($phone->input($data['number'], $data['extension'])->save()) {
            return new SuccessResponse('The phone number has been saved.');
        }

        return new ErrorResponse(500, 'The phone number could not be saved.');
    }

    public function destroy(PhoneNumber $phone)
    {
        $this->authorize('delete', $phone);
        $phone->delete();
        return new SuccessResponse('Phone number deleted.');
    }
}
