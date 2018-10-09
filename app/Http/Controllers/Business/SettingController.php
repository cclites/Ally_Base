<?php

namespace App\Http\Controllers\Business;

use App\BankAccount;
use App\Business;
use App\Http\Requests\UpdateBusinessRequest;
use App\OfficeUser;
use App\Payments\PaymentMethodReplace;
use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;
use App\Traits\Request\BankAccountRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SettingController extends BaseController
{
    use BankAccountRequest;

    public function index(Request $request)
    {
        $business = $this->business();

        if ($request->expectsJson() && $request->input('json')) {
            return $business;
        }

        return view('business.settings.index', compact('business'));
    }

    public function bankAccounts()
    {
        $business = $this->business();
        $business->load(['bankAccount', 'paymentAccount']);

        return view('business.settings.bank_accounts', compact('business'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param Request $request
     * @param string $type
     * @return ErrorResponse|SuccessResponse
     * @throws \App\Exceptions\ExistingBankAccountException
     * @throws \Exception
     */
    public function storeBankAccount(Request $request, string $type)
    {
        switch($type) {
            case 'deposit':
                $relation = 'bankAccount';
                break;
            case 'payment':
                $relation = 'paymentAccount';
                break;
        }

        $newAccount = $this->validateBankAccount($request, $this->business()->getBankAccount($relation));
        $newAccount->business_id = $this->business()->id;
        if ($this->business()->setBankAccount($relation, $newAccount)) {
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
        app('settings')->set($this->business(), $request->validated());

        return new SuccessResponse('Business settings updated.');
    }

    public function updatePayrollPolicy(Request $request, $id) {
        $data = $request->all();
        $business = $this->business();
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
