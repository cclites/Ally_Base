<?php


namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Caregiver;
use App\Confirmations\Confirmation;
use App\PhoneNumber;
use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;
use App\Rules\ValidSSN;
use App\Traits\Request\PaymentMethodUpdate;
use Illuminate\Http\Request;

class CaregiverConfirmationController extends Controller
{
    use PaymentMethodUpdate;

    public function show($token)
    {
        $confirmation = Confirmation::createFromToken($token);
        if (!$confirmation || !$confirmation->isValid('caregiver')) {
            return view('confirmation.expired');
        }

        $caregiver = Caregiver::find($confirmation->user->id);
        $business = $caregiver->businesses->first();
        $phoneNumber = $caregiver->phoneNumbers->where('type', 'work')->first();
        if ($phoneNumber) $phoneNumber = $phoneNumber->national_number;
        $address = $caregiver->addresses->first();
        $termsUrl = '';

        return view('confirmation.caregiver', compact('token', 'caregiver', 'business', 'phoneNumber', 'address', 'termsUrl'));
    }

    public function store(Request $request, $token)
    {
        $confirmation = Confirmation::createFromToken($token);
        if (!$confirmation || !$confirmation->isValid('caregiver')) {
            return new ErrorResponse(400, 'This link has expired.  Please ask the provider to re-send your confirmation email.');
        }

        $caregiver = Caregiver::find($confirmation->user->id);

        $request->validate(['accepted_terms' => 'accepted'], ['accepted_terms.accepted' => 'You must accept the terms of service by checking the box.']);

        // Profile Data
        $profile_data = $request->validate([
            'firstname' => 'required',
            'lastname' => 'required',
            'email' => 'required|email',
            'date_of_birth' => 'nullable|date',
            'ssn' => ['required', new ValidSSN()],
        ]);
        if ($profile_data['date_of_birth']) $profile_data['date_of_birth'] = filter_date($profile_data['date_of_birth']);

        // Password Data
        $request->validate([
            'password' => 'required|confirmed|min:8',
        ]);

        // Phone Data
        $phone_data = $request->validate([
            'phone_number' => 'required|min:10'
        ]);

        // Save Bank Account
        $account = $this->updateBankAccount($request, $caregiver);
        $caregiver->update([
            'bank_account_id' => $account->id,
            'onboarded' => Carbon::now()
        ]);

        // Save Address
        $response = (new AddressController())->update($request, $caregiver->user, 'home', 'Your address');
        if ($response instanceof ErrorResponse) {
            return $response;
        }

        if ($caregiver->update($profile_data)) {
            // Save Password
            $caregiver->user->changePassword($request->input('password'));

            // Save Phone Number
            if (!$phone = $caregiver->phoneNumbers->where('type', 'work')->first()) {
                $phone = new PhoneNumber([
                    'national_number' => $phone_data['phone_number'],
                    'country_code' => '1',
                    'type' => 'work',
                ]);
                $caregiver->phoneNumbers()->save($phone);
            } else {
                $phone->update(['national_number' => $phone_data['phone_number']]);
            }

            // Expire confirmation
            $confirmation->expire();

            return new SuccessResponse('You have successfully confirmed your information.');
        }

        return new ErrorResponse(500, 'Unknown system error. Please contact your provider.');
    }

    public function saved()
    {
        return view('confirmation.saved');
    }
}