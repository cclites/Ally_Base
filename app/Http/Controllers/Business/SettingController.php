<?php

namespace App\Http\Controllers\Business;

use App\Billing\Payments\Methods\BankAccount;
use App\Business;
use App\Http\Requests\UpdateBusinessBankRequest;
use App\Http\Requests\UpdateBusinessRequest;
use App\OfficeUser;
use App\Payments\PaymentMethodReplace;
use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;
use App\Traits\Request\BankAccountRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Http\Requests\BusinessRequest;
use App\Http\Requests\UpdateBusinessOvertimeRequest;

class SettingController extends BaseController
{
    use BankAccountRequest;

    public function index(Request $request)
    {
        if ($request->expectsJson() && $request->input('json')) {
            if (auth()->user()->role_type == 'client') {
                return response()->json(auth()->user()->role->business);
            }
            
            return $this->business();
        }

        return view('business.settings.index');
    }

    public function bankAccounts(Business $business = null)
    {
        if ($business) {
            $this->authorize('update', $business);
            $business->load('bankAccount', 'paymentAccount');
        }

        return view('business.settings.bank_accounts', compact('business'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param UpdateBusinessBankRequest $request
     * @param string $type
     * @return ErrorResponse|SuccessResponse
     * @throws \App\Exceptions\ExistingBankAccountException
     * @throws \Exception
     */
    public function storeBankAccount(UpdateBusinessBankRequest $request, string $type)
    {
        switch($type) {
            case 'deposit':
                $relation = 'bankAccount';
                break;
            case 'payment':
                $relation = 'paymentAccount';
                break;
        }

        $business = $request->getBusiness();
        $newAccount = $request->getBankAccount($business->getBankAccount($relation));
        if ($business->setBankAccount($relation, $newAccount)) {
            return new SuccessResponse('The bank account has been updated.');
        }

        return new ErrorResponse(500, 'Unable to replace bank account');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateBusinessRequest $request
     * @param  int $id
     * @return SuccessResponse
     */
    public function update(UpdateBusinessRequest $request, $id)
    {
        $business = $request->getBusiness();
        $this->authorize('update', $business);

        $data = $request->validated();
        
        if ($data['auto_confirm'] == 1) {
            // turn off related other confirm settings
            $data['allow_client_confirmations'] = 0;
            $data['auto_confirm_modified'] = 0;
            $data['shift_confirmation_email'] = 0;
            $data['charge_diff_email'] = 0;
            $data['auto_append_hours'] = 0;
            $data['auto_confirm_unmodified_shifts'] = 0;
        }

        app('settings')->set($business, $data);

        return new SuccessResponse('Business settings updated.', $request->getBusiness());
    }

    /**
     * Update the business's overtime settings.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function updateOvertime(UpdateBusinessOvertimeRequest $request)
    {
        $business = $request->getBusiness();
        $this->authorize('update', $business);

        app('settings')->set($business, $request->filtered());

        return new SuccessResponse('Overtime settings updated.', $request->getBusiness());
    }

    public function updatePayrollPolicy(Request $request, $id)
    {
        $business = Business::findOrFail($request->business_id);
        $this->authorize('update', $business);
        $data = $request->all();
        $business->pay_cycle = $data['pay_cycle'];
        $business->last_day_of_cycle = $data['last_day_of_cycle'];
        $business->last_day_of_first_period = $data['last_day_of_first_period'];
        $business->mileage_reimbursement_rate = $data['mileage_reimbursement_rate'];
        $business->overtime_method = $data['overtime_method'];

        if(!empty($data['unpaired_pay_rates'])) {
            $business->unpaired_pay_rates = json_encode($data['unpaired_pay_rates']);
        }

        if(!empty($data['overtime'])) {
            if(in_array('overtime_hours_day', $data['overtime'])) {
                $business->overtime_hours_day = $data['overtime_hours_day'];
            }

            if(in_array('overtime_hours_week', $data['overtime'])) {
                $business->overtime_hours_week = $data['overtime_hours_week'];
            }

            if(in_array('overtime_consecutive_days', $data['overtime'])) {
                $business->overtime_consecutive_days = $data['overtime_consecutive_days'];
            }
        }

        if(!empty($data['dbl_overtime'])) {
            if(in_array('dbl_overtime_hours_day', $data['dbl_overtime'])) {
                $business->dbl_overtime_hours_day = $data['dbl_overtime_hours_day'];
            }

            if(in_array('dbl_overtime_consecutive_days', $data['dbl_overtime'])) {
                $business->dbl_overtime_consecutive_days = $data['dbl_overtime_consecutive_days'];
            }
        }

        $business->save();

        return new SuccessResponse('Payroll Policy successfully saved.');
    }

}
