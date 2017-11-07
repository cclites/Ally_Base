<?php

namespace App\Http\Controllers\Business;

use App\BankAccount;
use App\Business;
use App\OfficeUser;
use App\Responses\SuccessResponse;
use Illuminate\Http\Request;

class SettingController extends BaseController
{

    public function index()
    {
        $business = OfficeUser::find(auth()->id())->businesses()->first();

        return view('business.settings.index', compact('business'));
    }

    public function bankAccounts()
    {
        $business = OfficeUser::find(auth()->id())->businesses()->with('bankAccount', 'paymentAccount')->first();
        return view('business.settings.bank_accounts', compact('business'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param string $type
     * @return void
     */
    public function storeBankAccount(string $type)
    {
        $this->validate(request(), [
            'routing_number' => 'required',
            'account_number' => 'required'
        ]);

        $account_data = request()->except('account_number_confirmation', 'routing_number_confirmation');
        $account_data['user_id'] = auth()->id();

        $business = OfficeUser::find(auth()->id())->businesses()->first();
        switch($type) {
            case 'deposit':
                if ($business->bank_account_id) {
                    $account = BankAccount::find($business->bank_account_id);
                    $account->update($account_data);
                } else {
                    $account = BankAccount::create($account_data);
                }
                $business->bank_account_id = $account->id;
                $business->save();
                break;
            case 'payment':
                if ($business->payment_account_id) {
                    $account = BankAccount::find($business->payment_account_id);
                    $account->update($account_data);
                } else {
                    $account = BankAccount::create($account_data);
                }
                $business->payment_account_id = $account->id;
                $business->save();
                break;
        }
        return new SuccessResponse( ucfirst($type) . ' Account updated.');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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

        $business->update([
            'scheduling' => $request->scheduling
        ]);
        return new SuccessResponse('Business settings updated.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
