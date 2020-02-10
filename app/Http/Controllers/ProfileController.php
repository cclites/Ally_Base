<?php

namespace App\Http\Controllers;

use App\Address;
use App\Billing\Payments\Methods\BankAccount;
use App\CaregiverAvailability;
use App\CaregiverDayOff;
use App\Client;
use App\Billing\Payments\Methods\CreditCard;
use App\Http\Requests\UpdatePaymentMethodRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Events\CaregiverAvailabilityChanged;
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
            $user->role->load(['availability', 'skills', 'daysOff']);
        } else if ($type == 'office_user') {
            $user->role->load(['businesses']);
        }

        $notifications = $user->getAvailableNotifications()->map(function ($cls) {
            return [
                'class' => $cls,
                'key' => $cls::getKey(),
                'title' => $cls::getTitle(),
                'disabled' => $cls::DISABLED,
            ];
        });

        $timezones = $this->getAvailableTimezones();

        return view('profile.' . $type, compact('user', 'payment_type_message', 'notifications', 'timezones'));
    }

    public function update(UpdateProfileRequest $request)
    {
        $this->authorize('update', auth()->user());

        $data = $request->validated();

        switch(auth()->user()->role_type) {
            case 'client':
                if (auth()->user()->role->can_edit_send_1099 == true) {
                    $client_data = request()->validate([
                        'send_1099' => 'nullable|string|in:yes,no',
                    ]);
                    auth()->user()->role->update($client_data);
                }
                break;
            case 'office_user':
                $officeUserData = request()->validate([
                    'default_business_id' => 'required|exists:businesses,id',
                    'timezone' => 'required|string|in:' . $this->getAvailableTimezones()->implode('value', ','),
                ]);
                auth()->user()->role->update($officeUserData);
                break;
        }

        $data['date_of_birth'] = filter_date($data['date_of_birth']);

        if (auth()->user()->update($data)) {
            return new SuccessResponse(
                'Your profile has been updated.',
                [], 
                auth()->user()->role_type == 'office_user' ? '.' : null
            );
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
     * @return mixed
     */
    public function preferences(UpdateCaregiverAvailabilityRequest $request)
    {
        if (auth()->user()->role_type != 'caregiver' || auth()->user()->active == 0) {
            abort(403);
        }

        $caregiver = auth()->user()->role;

        \DB::beginTransaction();

        ///This call looks at scheduled vacation days saved in the system and compares
        //them against scheduled vacation days to find the difference.
        //$diffDaysOff represents those days.
        $diffDaysOff = CaregiverDayOff::arrayDiffCustom($request->daysOffData(), $caregiver);

        //This call looks at CG available days saved in the system and compares against
        //available days stored in the system. If any days were previously marked available
        //and are now unavailable, $diffAvailability represents those days.
        $diffAvailability = CaregiverAvailability::arrayDiffAvailability($request->availabilityData(), $caregiver);

        $vacationConflict = $availabilityConflict = [];

        if($diffDaysOff){
            $vacationConflict = CaregiverDayOff::checkAddedVacationConflict($caregiver, $diffDaysOff);
        }

        if($diffAvailability){
            $availabilityConflict = CaregiverAvailability::checkRemovedAvailableDaysConflict($caregiver, $diffAvailability);
        }

        if( $vacationConflict || $availabilityConflict){
            event(new CaregiverAvailabilityChanged($caregiver));
            return new ErrorResponse('401', 'Unable to update availability. Please contact your registry.');
        }

        $caregiver->update(['preferences' => $request->preferencesData()]);
        $caregiver->setAvailability($request->availabilityData());

        if($diffDaysOff){
            $caregiver->daysOff()->delete();
            $caregiver->daysoff()->createMany($request->daysOffData());
        }

        $caregiver->daysOff()->delete();
        $caregiver->daysoff()->createMany($request->daysOffData());

        \DB::commit();

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
        auth()->user()->syncNotificationPreferences($request->validated());

        return new SuccessResponse('Notification preferences have been saved.');
    }

    /**
     * Get a list of the available timezones.
     *
     * @return \Illuminate\Support\Collection
     */
    protected function getAvailableTimezones()
    {
        $zones = array();
        $timestamp = time();
        foreach(timezone_identifiers_list() as $key => $zone) {
            date_default_timezone_set($zone);
            $zones[$key]['diff'] = date('P', $timestamp);
            $zones[$key]['value'] = $zone;
            $zones[$key]['text'] = 'GMT ' . $zones[$key]['diff'] . ' ' . $zones[$key]['value'];
        }
        return collect($zones)->sortBy('diff');
    }
}
