<?php

namespace App\Http\Controllers;

use App\Responses\SuccessResponse;
use Illuminate\Http\Request;
use App\Client;
use App\Http\Requests\AccountSetup\Clients\ClientStep1Request;

class ClientSetupController extends Controller
{
    /**
     * Display the specified resource.
     *
     * @param string $token
     * @return \Illuminate\Http\Response
     */
    public function show($token)
    {
        $client = Client::findEncrypted($token);
        if (empty($client)) {
            abort(404, 'Not Found');
        }

        $client->load(['address', 'phoneNumber']);

        return view('account-setup.client', compact('token', 'client'));
    }

    /**
     * Submit step 1 form.
     *
     * @param ClientStep1Request $request
     * @param string $token
     * @return \Illuminate\Http\Response
     */
    public function step1(ClientStep1Request $request, $token)
    {
        $client = Client::findEncrypted($token);
        if (empty($client)) {
            abort(404, 'Not Found');
        }

        // TODO: Refactor how addresses are upserted.
        $response = (new AddressController())->update(request(), $client->user, 'evv', 'Your address');
        if ($response instanceof ErrorResponse) {
            return $response;
        }

        $data = $request->filtered();
        $data['agreement_status'] = Client::SIGNED_ELECTRONICALLY;
        $data['setup_status'] = Client::SETUP_ACCEPTED_TERMS;

        \DB::beginTransaction();

        if ($client->update($data)) {
            $client->agreementStatusHistory()->create(['status' => Client::SIGNED_ELECTRONICALLY]);
            $client->setupStatusHistory()->create(['status' => Client::SETUP_ACCEPTED_TERMS]);

            if (empty($this->evvPhone)) {
                $client->phoneNumbers()->create([
                    'national_number' => $request->phone_number,
                    'country_code' => '1',
                    'type' => 'primary',
                ]);
            } else {
                $client->evvPhone->update([
                    'national_number' => $request->phone_number,
                    'country_code' => '1',
                ]);
            }
        }

        \DB::commit();

        $client = $client->fresh()->load(['address', 'phoneNumber']);
        return new SuccessResponse('Your information has been updated, please continue.', $client);
    }

    /**
     * Submit step 2 form.
     *
     * @param ClientStep1Request $request
     * @param string $token
     * @return \Illuminate\Http\Response
     */
    public function step2(ClientStep1Request $request, $token)
    {
        $client = Client::findEncrypted($token);
        if (empty($client)) {
            abort(404, 'Not Found');
        }

        $data = $request->validated();

        $client = $client->fresh()->load(['address', 'phoneNumber']);
        return new SuccessResponse('Your information has been updated, please continue.', $client);
    }

    /**
     * Get the terms and conditions text for the current Client.
     *
     * @param string $token
     * @return \Illuminate\Http\Response
     */
    public function terms($token)
    {
        $client = Client::findEncrypted($token);
        if (empty($client)) {
            abort(404, 'Not Found');
        }

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
}
