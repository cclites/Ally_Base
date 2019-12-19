<?php

namespace App\Http\Controllers\Admin;

use App\Business;
use App\ClientType;
use App\Responses\SuccessResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateSystemSettingsRequest;

use App\ChainClientTypeSettings;
use App\Client;

class ChainSettingsController extends Controller
{
    /**
     * @param ChainClientTypeSettings $chainClientTypeSettings
     * @param Request $request
     * @return SuccessResponse
     * @throws \Exception
     */
    public function update(UpdateSystemSettingsRequest $request, ChainClientTypeSettings $chainClientTypeSettings )
    {
        $chainClientTypeSettings->update($request->validated());

        \DB::beginTransaction();

        $chainClientTypeSettings->chain->businesses->each(function(Business $business) use ($chainClientTypeSettings){
            $business->clients()->where('client_type', ClientType::MEDICAID)
                ->update([
                    'caregiver_1099' => $chainClientTypeSettings->medicaid_1099_from, //ally or client
                    'can_edit_send_1099' => $chainClientTypeSettings->medicaid_1099_edit, //can edit
                    'send_1099' => $chainClientTypeSettings->medicaid_1099_default, //send by default
                ]);

            $business->clients()->where('client_type', ClientType::PRIVATE_PAY)
                ->update([
                    'caregiver_1099' => $chainClientTypeSettings->private_pay_1099_from, //ally or client
                    'can_edit_send_1099' => $chainClientTypeSettings->private_pay_1099_edit, //can edit
                    'send_1099' => $chainClientTypeSettings->private_pay_1099_default, //send by default
                ]);

            $business->clients()->whereNotIn('client_type', [ClientType::PRIVATE_PAY, ClientType::MEDICAID])
                ->update([
                    'caregiver_1099' => $chainClientTypeSettings->other_1099_from, //ally or client
                    'can_edit_send_1099' => $chainClientTypeSettings->other_1099_edit, //can edit
                    'send_1099' => $chainClientTypeSettings->other_1099_default, //send by default
                ]);
        });

        \DB::commit();

        return new SuccessResponse("Settings successfully updated");
    }

}
