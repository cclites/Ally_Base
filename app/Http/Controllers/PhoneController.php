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
    public function update(Request $request, User $user, $type, $reference = 'The phone number')
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
        }
        else {
            $phone = new PhoneNumber();
            $phone->type = $type;
            $phone->input($data['number'], $data['extension']);
            if ($user->phoneNumbers()->save($phone)) {
                return new SuccessResponse($reference . ' has been saved.');
            }
        }

        return new ErrorResponse(500, $reference . ' could not be saved.');
    }
}
