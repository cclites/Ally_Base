<?php


namespace App\Http\Controllers;


use App\Address;
use App\Client;
use App\Confirmations\Confirmation;
use App\OnboardStatusHistory;
use App\PhoneNumber;
use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ClientConfirmationController extends Controller
{

    protected $validStatuses = ['needs_agreement', 'emailed_reconfirmation'];

    public function show($token)
    {
        $confirmation = Confirmation::createFromToken($token);
        if (!$confirmation || !$confirmation->isValid('client')) {
            return view('confirmation.expired');
        }

        $client = Client::find($confirmation->user->id);

        if (!in_array($client->onboard_status, $this->validStatuses)) {
            return view('confirmation.expired');
        }

        $lastStatusDate = $client->onboardStatusHistory()->orderBy('created_at', 'DESC')->first();
        if (Carbon::now()->diffInDays($lastStatusDate->created_at) > 14) {
            return 'This link has expired.  Please ask the provider to re-send your confirmation email.';
        }

        $phoneNumber = ($client->evvPhone) ? $client->evvPhone->national_number : null;

        // Allow custom terms per business (terms-inc-$id.html)
        $businessId = $client->business_id;
        $termsUrl = url('terms-inc.html');
        if (file_exists(public_path('terms-inc-' . $businessId . '.html'))) {
            $termsUrl = url('terms-inc-' . $businessId . '.html');
        }

        return view('confirmation.client', compact('token', 'client', 'phoneNumber', 'termsUrl'));
    }

    public function store(Request $request, $token)
    {
        $confirmation = Confirmation::createFromToken($token);
        if (!$confirmation || !$confirmation->isValid('client')) {
            return new ErrorResponse(400, 'This link has expired.  Please ask the provider to re-send your confirmation email.');
        }

        $client = Client::find($confirmation->user->id);

        $request->validate(['accepted_terms' => 'accepted'], ['accepted_terms.accepted' => 'You must accept the terms of service by checking the box.']);

        // Profile Data
        $client_data = $request->validate([
            'firstname' => 'required',
            'lastname' => 'required',
            'email' => 'required|email',
            'date_of_birth' => 'nullable|date',
        ]);
        if ($client_data['date_of_birth']) $client_data['date_of_birth'] = filter_date($client_data['date_of_birth']);
        $client_data['onboard_status'] = 'reconfirmed_checkbox';

        // Password Data
        $request->validate([
            'password' => 'required|confirmed|min:8',
        ]);

        // Phone Data
        $phone_data = $request->validate([
            'phone_number' => 'required|min:10'
        ]);

        // Save Address
        $response = (new AddressController())->update($request, $client->user, 'evv', 'Your address');
        if ($response instanceof ErrorResponse) {
            return $response;
        }

        if ($client->update($client_data)) {
            // Save Password
            $client->user->changePassword($request->input('password'));

            // Save Onboard Status
            $history = new OnboardStatusHistory(['status' => 'reconfirmed_checkbox']);
            $client->onboardStatusHistory()->save($history);

            // Save Phone Number
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