<?php


namespace App\Http\Controllers;

use App\BankAccount;
use App\Traits\Request\BankAccountRequest;
use Carbon\Carbon;
use App\Caregiver;
use App\Confirmations\Confirmation;
use App\PhoneNumber;
use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;
use App\Rules\ValidSSN;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CaregiverConfirmationController extends Controller
{
    use BankAccountRequest;

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

        //$caregiver = Caregiver::find($confirmation->user->id);
        $caregiver = $confirmation->user;

        $request->validate(['accepted_terms' => 'accepted'], ['accepted_terms.accepted' => 'You must accept the terms of service by checking the box.']);

        // Profile Data
        $profile_data = collect($request->validate([
            'firstname' => 'required',
            'lastname' => 'required',
            'email' => 'required|email',
            'date_of_birth' => 'nullable|date',
            'ssn' => ['required', new ValidSSN()]
        ]));
        if ($profile_data['date_of_birth']) $profile_data['date_of_birth'] = filter_date($profile_data['date_of_birth']);

        $w9_data = $this->validateW9Data();
        // Merge in W9 data if any is set
        if ($w9_data->count()) {
            $profile_data = $profile_data->merge($w9_data);
        }

        // Password Data
        $request->validate([
            'password' => 'required|confirmed|min:8',
        ]);

        // Phone Data
        $phone_data = $request->validate([
            'phone_number' => 'required|min:10'
        ]);

        // Save Bank Account
        $account = $this->validateBankAccount($request, null);
        $account->user_id = $caregiver->id;
        $account->save();
        $caregiver->update([
            'bank_account_id' => $account->id,
            'onboarded' => Carbon::now()
        ]);

        // Save Address
        $response = (new AddressController())->update($request, $caregiver->user, 'home', 'Your address');
        if ($response instanceof ErrorResponse) {
            return $response;
        }

        if ($caregiver->update($profile_data->toArray())) {
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

    /**
     * Validate and Transform W9 data
     *
     * @return \Illuminate\Support\Collection
     */
    protected function validateW9Data()
    {
        request()->validate([
            'w9.name' => 'nullable|string',
            'w9.business_name' => 'nullable|string',
            'w9.tax_classification' => 'nullable|string',
            'w9.llc_type' => 'nullable|string',
            'w9.exempt_payee_code' => 'nullable|string',
            'w9.exempt_fatca_reporting_code' => 'nullable|string',
            'w9.address' => 'nullable|string',
            'w9.city_state_zip' => 'nullable|string',
            'w9.account_numbers' => 'nullable|string',
            'w9.ssn' => ['nullable', new ValidSSN()],
            'w9.employer_id_number' => 'nullable|string'
        ]);
        // Transform the W9 data
        $w9_data = collect([]);
        foreach(collect(request()->only('w9')['w9'])->filter() as $key => $value) {
            $w9_data['w9_'.$key] = $value;
        }
        return $w9_data;
    }
}