<?php

namespace App\Http\Controllers;

use App\PhoneNumber;
use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use App\Caregiver;
use App\Http\Requests\AccountSetup\Caregivers\CaregiverStep1Request;
use App\Traits\Request\BankAccountRequest;
use App\Rules\ValidSSN;
use Carbon\Carbon;

class CaregiverSetupController extends Controller
{
    use BankAccountRequest;

    /**
     * Display the specified resource.
     *
     * @param string $token
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function show($token)
    {
        $caregiver = Caregiver::findEncryptedOrFail($token);
        
        $caregiver->load(['address', 'phoneNumber']);

        $props = [
            'caregiver-data' => $caregiver,
            'token' => $token,
        ];
        return view_component('caregiver-setup-wizard', 'Caregiver Account Setup', $props, [], 'guest');
    }

    /**
     * Submit info and agree to terms step.
     *
     * @param CaregiverStep1Request $request
     * @param string $token
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */ 
    public function step1(CaregiverStep1Request $request, $token)
    {
        $caregiver = Caregiver::findEncryptedOrFail($token);

        // TODO: Refactor how addresses are upserted.
        $response = (new AddressController())->update(request(), $caregiver->user, 'home', 'Your address');
        if ($response instanceof ErrorResponse) {
            return $response;
        }

        $data = $request->filtered();

        \DB::beginTransaction();

        if ($caregiver->update($data)) {
            $caregiver->setupStatusHistory()->create(['status' => $data['setup_status']]);

            if (empty($this->phoneNumber)) {
                $phoneNumber = PhoneNumber::fromInput('primary', $request->phone_number);
                $caregiver->phoneNumbers()->save($phoneNumber);
            } else {
                $caregiver->phoneNumber->input($request->phone_number);
                $caregiver->phoneNumber->save();
            }
        }

        \DB::commit();

        $caregiver = $caregiver->fresh()->load(['address', 'phoneNumber']);
        return new SuccessResponse('Your information has been updated, please continue.', $caregiver);
    }

    /**
     * Submit create username/password step.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $token
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function step2(Request $request, $token)
    {
        $caregiver = Caregiver::findEncryptedOrFail($token);

        $request->validate([
            'password' => 'required|confirmed|min:8',
            'username' => ['required', 'min:3', 'max:255', Rule::unique('users')->ignore($caregiver->id)],
        ]);

        \DB::beginTransaction();

        $caregiver->update([
            'username' => $request->username,
            'setup_status' => Caregiver::SETUP_CREATED_ACCOUNT
        ]);
        $caregiver->setupStatusHistory()->create(['status' => Caregiver::SETUP_CREATED_ACCOUNT]);
        $caregiver->user->changePassword($request->password);

        \DB::commit();

        $caregiver = $caregiver->fresh()->load(['address', 'phoneNumber']);
        return new SuccessResponse('Your information has been updated, please continue.', $caregiver);
    }

    /**
     * Submit payment settings step.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $token
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function step3(Request $request, $token)
    {
        $caregiver = Caregiver::findEncryptedOrFail($token);

        \DB::beginTransaction();

        $request->validate(['accepted_terms' => 'accepted'], ['accepted_terms.accepted' => 'You must accept the terms of service by checking the box.']);

        $bankAccount = $this->validateBankAccount($request, null);
        if (! $caregiver->setBankAccount($bankAccount)) {
            \DB::rollBack();
            return new ErrorResponse(500, 'There was an error saving your payment details.  Please try again.');
        }

        $data = $this->validateW9Data();
        $data = $data->merge([
            'onboarded' => Carbon::now(),
            'setup_status' => Caregiver::SETUP_ADDED_PAYMENT,
        ])->toArray();
        
        $caregiver->update($data);
        $caregiver->setupStatusHistory()->create(['status' => Caregiver::SETUP_ADDED_PAYMENT]);

        \DB::commit();

        $caregiver = $caregiver->fresh()->load(['address', 'phoneNumber']);

        return new SuccessResponse('Your account has been set up!', $caregiver);
    }

    /**
     * Check for the current setup step and return a fresh caregiver object.
     *
     * @param string $token
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function checkStep($token)
    {
        $caregiver = Caregiver::findEncryptedOrFail($token);
        $caregiver->load(['address', 'phoneNumber']);

        if (empty($caregiver->setup_status)) {
            return response()->json($caregiver);
        }
        
        $hasUsername = !$caregiver->hasNoUsername();
        $hasPaymentMethod = !empty($caregiver->bankAccount);

        if (! $hasUsername) {
            $caregiver->setup_status = Caregiver::SETUP_CONFIRMED_PROFILE;
        } else if ($hasUsername && !$hasPaymentMethod) {
            $caregiver->setup_status = Caregiver::SETUP_CREATED_ACCOUNT;
        } else if ($hasUsername && $hasPaymentMethod) {
            $caregiver->setup_status = Caregiver::SETUP_ADDED_PAYMENT;
        } else {
            $caregiver->setup_status = Caregiver::SETUP_NONE;
        }
        $caregiver->save();

        return response()->json($caregiver);
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
