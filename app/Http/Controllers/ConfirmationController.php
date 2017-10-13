<?php


namespace App\Http\Controllers;


use App\Address;
use App\Client;
use App\OnboardStatusHistory;
use App\PhoneNumber;
use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ConfirmationController extends Controller
{

    protected $validStatuses = ['needs_agreement', 'emailed_reconfirmation'];


    public function reconfirm($encrypted_id)
    {
        $client = Client::findEncrypted($encrypted_id);

        if (!$client || !in_array($client->onboard_status, $this->validStatuses)) {
            abort(404);
        }

        $lastStatusDate = $client->onboardStatusHistory()->orderBy('created_at', 'DESC')->first();
        if (Carbon::now()->diffInDays($lastStatusDate->created_at) > 14) {
            return 'This link has expired.  Please ask the provider to re-send your confirmation email.';
        }

        $client->load(['user', 'evvAddress']);
        $phoneNumber = ($client->evvPhone) ? $client->evvPhone->national_number : null;

        return view('confirmation.reconfirm', compact('encrypted_id', 'client', 'phoneNumber'));
    }

    public function store(Request $request, $encrypted_id)
    {
        $client = Client::findEncrypted($encrypted_id);

        if (!$client || !in_array($client->onboard_status, $this->validStatuses)) {
            return new ErrorResponse(400, 'This link has expired.  Please ask the provider to re-send your confirmation email.');
        }

        $lastStatusDate = $client->onboardStatusHistory()->orderBy('created_at', 'DESC')->first();
        if (Carbon::now()->diffInDays($lastStatusDate->created_at) > 14) {
            return new ErrorResponse(400, 'This link has expired.  Please ask the provider to re-send your confirmation email.');
        }

        $request->validate(['accepted_terms' => 'accepted'], ['accepted_terms.accepted' => 'You must accept the terms of service by checking the box.']);

        $client_data = $request->validate([
            'firstname' => 'required',
            'lastname' => 'required',
            'email' => 'required|email',
            'date_of_birth' => 'nullable|date',
        ]);
        if ($client_data['date_of_birth']) $client_data['date_of_birth'] = filter_date($client_data['date_of_birth']);
        $client_data['onboard_status'] = 'reconfirmed_checkbox';


        $phone_data = $request->validate([
            'phone_number' => 'required|min:10'
        ]);

        // Attempt to update address data
        $response = (new AddressController())->update($request, $client->user, 'evv', 'Your address');
        if ($response instanceof ErrorResponse) {
            return $response;
        }

        if ($client->update($client_data)) {
            $history = new OnboardStatusHistory(['status' => 'reconfirmed_checkbox']);
            $client->onboardStatusHistory()->save($history);

            if (!$client->evvPhone) {
                $phone = new PhoneNumber([
                    'national_number' => $phone_data['phone_number'],
                    'country_code' => '1',
                    'type' => 'evv',
                ]);
                $client->phoneNumbers()->save($phone);
            }
            else {
                $client->evvPhone->update(['national_number' => $phone_data['phone_number']]);
            }

            return new SuccessResponse('You have successfully confirmed your information.');
        }

        return new ErrorResponse(500, 'Unknown system error. Please contact your provider.');
    }

    public function saved()
    {
        return view('confirmation.saved');
    }

}