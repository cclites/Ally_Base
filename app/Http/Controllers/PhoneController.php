<?php

namespace App\Http\Controllers;

use App\Http\Requests\SavePhoneNumberRequest;
use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;
use App\PhoneNumber;
use App\Rules\PhonePossible;
use App\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
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

    /**
     * Set given phone number as the user's SMS number.
     * Only used for Caregivers at this time.
     *
     * @param \App\PhoneNumber $phone
     * @return SuccessResponse
     * @throws AuthorizationException
     */
    public function updateSmsNumber(PhoneNumber $phone)
    {
        $this->authorize('update', $phone);

        if (! $phone->receives_sms) {
            // only allow one sms number at a time
            $phone->user->smsNumber()->update(['receives_sms' => false]);
            
            $phone->update(['receives_sms' => true]);
        }

        return new SuccessResponse('Text message number updated.');
    }
}
