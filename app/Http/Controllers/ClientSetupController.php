<?php

namespace App\Http\Controllers;

use App\Http\Requests\AccountSetup\Clients\ClientStep1Request;
use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;
use App\Traits\Request\PaymentMethodRequest;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use App\Client;
use File;
use App\PhoneNumber;
use function Composer\Autoload\includeFile;

class ClientSetupController extends Controller
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
        $client = Client::findEncryptedOrFail($token);

        $client->load(['address', 'phoneNumber']);

        $props = [
            'client-data' => $client,
            'token' => $token,
        ];
        return view_component('client-setup-wizard', 'Client Account Setup', $props, [], 'guest');
    }

    /**
     * Submit info and agree to terms step.
     *
     * @param ClientStep1Request $request
     * @param string $token
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function step1(ClientStep1Request $request, $token)
    {
        /** @var Client $client */
        $client = Client::findEncryptedOrFail($token);

        // TODO: Refactor how addresses are upserted.
        $response = (new AddressController())->update(request(), $client->user, 'evv', 'Your address');
        if ($response instanceof ErrorResponse) {
            return $response;
        }

        $data = $request->filtered($client);

        \DB::beginTransaction();

        if ($client->update($data)) {
            if (isset($data['agreement_status'])) {
                // only update the agreement status history if the status has changed
                $client->agreementStatusHistory()->create(['status' => $data['agreement_status']]);
            }
            $client->setupStatusHistory()->create(['status' => $data['setup_status']]);

            if (empty($client->evvPhone)) {
                $phoneNumber = PhoneNumber::fromInput('primary', $request->phone_number);
                $client->phoneNumbers()->save($phoneNumber);
            } else {
                $client->evvPhone->input($request->phone_number);
                $client->evvPhone->save();
            }
        }

        \DB::commit();

        $client = $client->fresh()->load(['address', 'phoneNumber']);
        return new SuccessResponse('Your information has been updated, please continue.', $client);
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
        $client = Client::findEncryptedOrFail($token);

        $request->validate([
            'password' => 'required|confirmed|min:8',
            'username' => ['required', 'min:3', 'max:255', Rule::unique('users')->ignore($client->id)],
        ]);

        \DB::beginTransaction();

        $client->update([
            'username' => $request->username,
            'setup_status' => Client::SETUP_CREATED_ACCOUNT
        ]);
        $client->setupStatusHistory()->create(['status' => Client::SETUP_CREATED_ACCOUNT]);
        $client->user->changePassword($request->password);

        \DB::commit();

        $client = $client->fresh()->load(['address', 'phoneNumber']);
        return new SuccessResponse('Your information has been updated, please continue.', $client);
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
        $client = Client::findEncryptedOrFail($token);

        \DB::beginTransaction();

        $method = $this->validatePaymentMethod($request, $client->defaultPayment);
        if (! $client->setPaymentMethod($method)) {
            \DB::rollBack();
            return new ErrorResponse(500, 'There was an error saving your payment details.  Please try again.');
        }

        $client->update([
            'setup_status' => Client::SETUP_ADDED_PAYMENT
        ]);
        $client->setupStatusHistory()->create(['status' => Client::SETUP_ADDED_PAYMENT]);

        \DB::commit();

        $client = $client->fresh()->load(['address', 'phoneNumber']);

        $this->renderClientAgreementDocument($client);

        return new SuccessResponse('Your account has been set up!', $client);
    }

    /**
     * Get the terms and conditions text for the current Client.
     *
     * @param string $token
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function terms($token)
    {
        $client = Client::findEncryptedOrFail($token);

        $termsFile = 'terms-inc.html';
        $termsUrl = url($termsFile);
        $terms = str_after(file_get_contents($termsFile), '<body>');
        $terms = str_before($terms, '</body>');
        if (file_exists(public_path('terms-inc-' . $client->business_id . '.html'))) {
            $termsFile = 'terms-inc-' . $client->business_id . '.html';
            $termsUrl = url('terms-inc-' . $client->business_id . '.html');
            $terms = str_after(file_get_contents($termsFile), '<body>');
            $terms = str_before($terms, '</body>');
        }

        return response()->json(['terms' => $terms, 'terms_url' => $termsUrl]);
    }

    /**
     * Check for the current setup step and return a fresh client object.
     *
     * @param string $token
     * @return \Illuminate\Http\Response
     */
    public function checkStep($token)
    {
        $client = Client::findEncryptedOrFail($token);
        $client->load(['address', 'phoneNumber']);

        if (empty($client->setup_status)) {
            return response()->json($client);
        }
        
        $hasUsername = !$client->hasNoUsername();
        $hasPaymentMethod = !empty($client->getPaymentMethod());

        if (! $hasUsername) {
            $client->setup_status = Client::SETUP_ACCEPTED_TERMS;
        } else if ($hasUsername && !$hasPaymentMethod) {
            $client->setup_status = Client::SETUP_CREATED_ACCOUNT;
        } else if ($hasUsername && $hasPaymentMethod) {
            $client->setup_status = Client::SETUP_ADDED_PAYMENT;
        } else {
            $client->setup_status = Client::SETUP_NONE;
        }
        $client->save();

        return response()->json($client);
    }

    public function renderClientAgreementDocument(Client $client){

        $client->load(['addresses', 'defaultPayment', 'backupPayment', 'phoneNumbers']);

        \Log::info($client);


        $termsFile = 'terms-inc.html';
        $termsUrl = url($termsFile);

        if (file_exists(public_path('terms-inc-' . $client->business_id . '.html'))) {
            $termsFile = 'terms-inc-' . $client->business_id . '.html';
            $termsUrl = url($termsFile);
        }

        $terms = file_get_contents($termsUrl);
        $paymentInfo = (string)view('business.clients.payment_details', compact('client'))->render();

        $pdf = \PDF::loadView('business.clients.client_agreement_document', ['terms'=>$terms, 'paymentInfo'=>$paymentInfo]);

        $dir = storage_path('app/documents/');
        if (!File::exists($dir)) {
            File::makeDirectory($dir, 493, true);
        }
        $filename = str_slug($client->id . ' ' . $client->name.' Client Agreement').'.pdf';
        $filePath = $dir . '/' . $filename;
        if (config('app.env') == 'local') {
            if (File::exists($filePath)) {
                File::delete($filePath);
            }
        }
        $response = $pdf->save($filePath);

        if ($response) {
            \DB::transaction(function() use ($response, $filePath, $client) {
                $client->documents()->create([
                    'filename' => File::basename($filePath),
                    'original_filename' => File::basename($filePath),
                    'description' => 'Client Agreement',
                    'user_id' => $client->id
                ]);
            });
        }
    }
}
