<?php

namespace App\Http\Controllers\Business;

use App\BankAccount;
use App\Business;
use App\OfficeUser;
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
     * @param string $type
     */
    public function storeBankAccount(Request $request, string $type)
    {
        $business = $this->business();
        switch($type) {
            case 'deposit':
                $account = $business->bankAccount;
                $account_data = $this->validateBankAccount($request, $account);
                if ($account) {
                    $account->update($account_data);
                } else {
                    $account = new BankAccount($account_data);
                    $business->setBankAccount($account);
                }
                break;
            case 'payment':
                $account = $business->paymentAccount;
                $account_data = $this->validateBankAccount($request, $account);
                if ($account) {
                    $account->update($account_data);
                } else {
                    $account = BankAccount::create($account_data);
                    $business->payment_account_id = $account->id;
                    $business->save();
                }
                break;
        }
        return new SuccessResponse( ucfirst($type) . ' Account updated.');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $business = Business::find($id);
        $data = $request->validate([
            'scheduling' => 'required|bool',
            'mileage_rate' => 'required|numeric',
            'calendar_default_view' => 'required',
            'calendar_caregiver_filter' => 'required|in:all,unassigned'
        ]);
        $business->update($data);
        return new SuccessResponse('Business settings updated.');
    }

}
