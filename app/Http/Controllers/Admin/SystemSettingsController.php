<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Responses\ErrorResponse;
use App\Responses\SuccessResponse;
use Illuminate\Http\Request;
use App\Http\Requests\UpdateAllyContactInfoRequest;

class SystemSettingsController extends Controller
{
    public function show(){
        $systemSettings = \DB::table('system_settings')->first();
        return view_component('admin-system-settings', 'System Settings', ['settings'=>$systemSettings]);
    }

    public function update(UpdateAllyContactInfoRequest $request){

        if(\DB::table('system_settings')->update($request->all())){
            return new SuccessResponse('System settings have been updated.');
        }

        return new ErrorResponse(500, 'Unable to update system settings.');
    }
}
