<?php

namespace App\Http\Controllers;

use App\Http\Requests\AccountSetup\Caregivers\CaregiverStep1Request;
use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;
use App\Traits\Request\PaymentMethodRequest;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use App\Caregiver;

class CaregiverSetupController extends Controller
{
    use PaymentMethodRequest;

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
        if (empty($caregiver)) {
            abort(404, 'Not Found');
        }
        
        $caregiver->load(['address', 'phoneNumber']);

        return view('account-setup.caregiver', compact('token', 'caregiver'));
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
        if (empty($caregiver)) {
            abort(404, 'Not Found');
        }

        // TODO: Refactor how addresses are upserted.
        $response = (new AddressController())->update(request(), $caregiver->user, 'evv', 'Your address');
        if ($response instanceof ErrorResponse) {
            return $response;
        }

        $data = $request->filtered($caregiver);

        \DB::beginTransaction();

        if ($caregiver->update($data)) {
            if (isset($data['agreement_status'])) {
                // only update the agreement status history if the status has changed
                $caregiver->agreementStatusHistory()->create(['status' => $data['agreement_status']]);
            }
            $caregiver->setupStatusHistory()->create(['status' => $data['setup_status']]);

            if (empty($this->evvPhone)) {
                $caregiver->phoneNumbers()->create([
                    'national_number' => $request->phone_number,
                    'country_code' => '1',
                    'type' => 'primary',
                ]);
            } else {
                $caregiver->evvPhone->update([
                    'national_number' => $request->phone_number,
                    'country_code' => '1',
                ]);
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

        $method = $this->validatePaymentMethod($request, $caregiver->defaultPayment);
        if (! $caregiver->setPaymentMethod($method)) {
            \DB::rollBack();
            return new ErrorResponse(500, 'There was an error saving your payment details.  Please try again.');
        }

        $caregiver->update([
            'setup_status' => Caregiver::SETUP_ADDED_PAYMENT
        ]);
        $caregiver->setupStatusHistory()->create(['status' => Caregiver::SETUP_ADDED_PAYMENT]);

        \DB::commit();

        $caregiver = $caregiver->fresh()->load(['address', 'phoneNumber']);
        return new SuccessResponse('Your account has been set up!', $caregiver);
    }

    /**
     * Get the terms and conditions text for the current Caregiver.
     *
     * @param string $token
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function terms($token)
    {
        $caregiver = Caregiver::findEncryptedOrFail($token);
        if (empty($caregiver)) {
            abort(404, 'Not Found');
        }

        $termsFile = 'terms-inc.html';
        $termsUrl = url($termsFile);
        $terms = str_after(file_get_contents($termsFile), '<body>');
        $terms = str_before($terms, '</body>');
        if (file_exists(public_path('terms-inc-' . $caregiver->business_id . '.html'))) {
            $termsFile = 'terms-inc-' . $caregiver->business_id . '.html';
            $termsUrl = url('terms-inc-' . $caregiver->business_id . '.html');
            $terms = str_after(file_get_contents($termsFile), '<body>');
            $terms = str_before($terms, '</body>');
        }

        return response()->json(['terms' => $terms, 'terms_url' => $termsUrl]);
    }

    /**
     * Check for the current setup step and return a fresh caregiver object.
     *
     * @param string $token
     * @return \Illuminate\Http\Response
     */
    public function checkStep($token)
    {
        $caregiver = Caregiver::findEncryptedOrFail($token);
        if (empty($caregiver)) {
            abort(404, 'Not Found');
        }
        $caregiver->load(['address', 'phoneNumber']);

        if (empty($caregiver->setup_status)) {
            return response()->json($caregiver);
        }
        
        $hasUsername = !$caregiver->hasNoUsername();
        $hasPaymentMethod = !empty($caregiver->getPaymentMethod());

        if (! $hasUsername) {
            $caregiver->setup_status = Caregiver::SETUP_ACCEPTED_TERMS;
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
}
