<?php

namespace App\Http\Controllers;

use App\Address;
use App\BankAccount;
use App\Client;
use App\CreditCard;
use App\Http\Requests\UpdatePaymentMethodRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\PhoneNumber;
use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;
use App\Rules\PhonePossible;
use Illuminate\Http\Request;
use App\Traits\Request\BankAccountRequest;
use App\Http\Requests\UpdateCaregiverAvailabilityRequest;
use App\Http\Requests\UpdateNotificationOptionsRequest;
use App\Http\Requests\UpdateNotificationPreferencesRequest;

class ProfileController extends Controller
{
    use BankAccountRequest;

    public function index()
    {
        $type = auth()->user()->role_type;
        $user = auth()->user()->load(['phoneNumbers', 'notificationPreferences']);
        
        // include a placeholder for the primary number if one doesn't already exist
        if ($user->phoneNumbers->where('type', 'primary')->count() == 0) {
            $user->phoneNumbers->push(['type' => 'primary', 'extension' => '', 'number' => '']);
        }

        // include a placeholder for the billing number if one doesn't already exist
        if ($type == 'client' && $user->phoneNumbers->where('type', 'billing')->count() == 0) {
            $user->phoneNumbers->push(['type' => 'billing', 'extension' => '', 'number' => '']);
        }

        $payment_type_message = [];
        if ($type == 'client') {
            $payment_type_message = [
                'default' => "Active Payment Type: " . $user->role->getPaymentType() . " (" .
                    round($user->role->getAllyPercentage() * 100, 2) .
                    "% Processing Fee)",
                'backup' => "Active Payment Type: " . $user->role->getPaymentType($user->role->backupPayment) . " (" .
                    round($user->role->getAllyPercentage($user->role->backupPayment) * 100, 2) .
                    "% Processing Fee)"
            ];
        } else if ($type == 'caregiver') {
            $user->role->load(['availability', 'skills']);
        }

        $notifications = $user->getAvailableNotifications()->map(function ($cls) {
            return [
                'class' => $cls,
                'key' => $cls::getKey(),
                'title' => $cls::getTitle(),
            ];
        });

        return view('profile.' . $type, compact('user', 'payment_type_message', 'notifications'));
    }

    public function update(UpdateProfileRequest $request)
    {
        $this->authorize('update', auth()->user());

        $data = $request->validated();

        if(auth()->user()->role_type == 'client') {
            $client_data = request()->validate([
                'poa_first_name' => 'nullable|string',
                'poa_last_name' => 'nullable|string',
                'poa_phone' => 'nullable|string',
                'poa_relationship' => 'nullable|string',
                'receive_summary_email' => 'boolean',
                'caregiver_1099' => 'boolean',
            ]);
            auth()->user()->role->update($client_data);
        }

        $data['date_of_birth'] = filter_date($data['date_of_birth']);

        if (auth()->user()->update($data)) {
            return new SuccessResponse('Your profile has been updated.');
        }
        return new ErrorResponse(500, 'Unable to update profile.');
    }

    public function password(Request $request)
    {
        $messages = ['password.regex' => "Your password must contain one lower case, one upper case, and one number"];
        $request->validate([
            'password' => 'required|confirmed|min:8|regex:/^(?=\S*[a-z])(?=\S*[A-Z])(?=\S*[\d])\S*$/'
        ], $messages);

        if (auth()->user()->changePassword($request->input('password'))) {
            return new SuccessResponse('Your password has been updated.');
        }
        return new ErrorResponse(500, 'Unable to update password.');
    }

    public function address(Request $request, $type)
    {
        $this->authorize('update', auth()->user());

        $user = auth()->user();
        return (new AddressController())->update($request, $user, $type, 'Your address');
    }

    public function phone(Request $request, $type)
    {
        $this->authorize('update', auth()->user());

        $user = auth()->user();
        return (new PhoneController())->upsert($request, $user, $type, 'Your phone number');
    }

    public function paymentMethod(UpdatePaymentMethodRequest $request, $type)
    {
        $this->authorize('update', auth()->user());

        $client = $request->user()->role;
        $backup = ($type === 'backup');

        if ($request->filled('number')) {
            $method = new CreditCard(collect($request->validated())->except('cvv')->toArray());
        } else if ($request->filled('account_number')) {
            $method = new BankAccount($request->validated());
        }

        if ($client->setPaymentMethod($method, $backup)) {
            $paymentTypeMessage = "Active Payment Type: " . $client->fresh()->getPaymentType() . " (" . round($client->fresh()->getAllyPercentage() * 100, 2) . "% Processing Fee)";
            return response()->json($paymentTypeMessage);
        }
        return new ErrorResponse(500, 'The payment method could not be updated.');
    }

    public function bankAccount(Request $request)
    {
        $this->authorize('update', auth()->user());
        
        $caregiver = $request->user()->role;

        $existing = $caregiver->bankAccount;
        $account = $this->validateBankAccount($request, $existing);
        
        if ($caregiver->setBankAccount($account)) {
            return new SuccessResponse('The bank account has been saved.');
        }
        return new ErrorResponse(500, 'The bank account could not be saved.');
    }

    public function destroyPaymentMethod($type) {
        $this->authorize('update', auth()->user());
        
        /**
         * @var Client $client
         */
        $client = \Auth::user()->role;
        if ($type == 'backup') {
            $client->backupPayment()->dissociate();
        }
        else {
            $client->defaultPayment()->dissociate();
        }
        $client->save();
        return new SuccessResponse('The payment method has been deleted.');
    }

    /**
     * Update caregiver availability preferences.
     *
     * @param UpdateCaregiverAvailabilityRequest $request
     * @return \Illuminate\Http\Response
     */
    public function preferences(UpdateCaregiverAvailabilityRequest $request)
    {
        if (auth()->user()->role_type != 'caregiver' || auth()->user()->active == 0) {
            abort(403);
        }

        $caregiver = auth()->user()->role;

        $caregiver->update(['preferences' => $request->input('preferences')]);
        $caregiver->setAvailability($request->validated() + ['updated_by' => auth()->id()]);
        return new SuccessResponse('Your availability preferences have been saved.');
    }

    /**
     * Update caregiver skills preferences.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function skills(Request $request)
    {
        if (auth()->user()->role_type != 'caregiver' || auth()->user()->active == 0) {
            abort(403);
        }

        $caregiver = auth()->user()->role;

        $request->validate([
            'skills' => 'array',
            'skills.*' => 'integer',
        ]);

        $caregiver->skills()->sync($request->skills);

        return new SuccessResponse('Caregiver skills updated');
    }

    /**
     * Update user notification settings.
     *
     * @param UpdateNotificationOptionsRequest $request
     * @return \Illuminate\Http\Response
     */
    public function updateNotificationOptions(UpdateNotificationOptionsRequest $request)
    {
        $data = $request->validated();

        if (! $data['allow_sms_notifications'] && ! $data['notification_email'] && ! $data['allow_system_notifications']) {
            return new ErrorResponse(422, 'You must select at least one notification type');
        }

        if (auth()->user()->update($data)) {
            return new SuccessResponse('Notification options have been updated.');
        }

        return new ErrorResponse(500, 'Unexpected error updating notification options.  Please try again.');
    }

    /**
     * Update user notification preferences.
     *
     * @param UpdateNotificationPreferencesRequest $request
     * @return \Illuminate\Http\Response
     */
    public function updateNotificationPreferences(UpdateNotificationPreferencesRequest $request)
    {
        $notifications = $request->validated();

        \DB::beginTransaction();

        foreach ($notifications as $key => $data) {
            auth()->user()->notificationPreferences()
                ->where('key', $key)
                ->update($data);
        }

        \DB::commit();

        return new SuccessResponse('Notification preferences have been saved.');
    }
}
