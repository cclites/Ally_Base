<?php

namespace App\Http\Controllers\Admin;

use App\AdminPin;
use App\Business;
use App\ClientType;
use App\ChainClientTypeSettings;
use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateSystemSettingsRequest;

class ChainSettingsController extends Controller
{
    /**
     * @param UpdateSystemSettingsRequest $request
     * @param ChainClientTypeSettings $chainClientTypeSettings
     * @return SuccessResponse
     * @throws \Exception
     */
    public function update(UpdateSystemSettingsRequest $request, ChainClientTypeSettings $chainClientTypeSettings)
    {
        if (! AdminPin::verify(request()->pin, 'update-chain-1099-settings')) {
            return new ErrorResponse(422, "Invalid PIN.");
        }

        $chainClientTypeSettings->update($request->validated());

        \DB::beginTransaction();

        $chainClientTypeSettings->chain->businesses->each(function (Business $business) use ($chainClientTypeSettings) {
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
