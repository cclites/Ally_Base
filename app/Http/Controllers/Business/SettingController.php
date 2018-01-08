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
        $business = Business::find($id);
        $business->update($request->all());
        return new SuccessResponse('Business settings updated.');
    }

}
