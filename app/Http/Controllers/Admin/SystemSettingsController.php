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
        return view_component('admin-system-settings',
                        'System Settings',
                              ['settings'=>$systemSettings],
                              [
                                  'Home' => route('home'),
                                  '1099' => route('admin.admin-1099-actions')
                              ]);
    }

    public function update(UpdateAllyContactInfoRequest $request){

        if(\DB::table('system_settings')->update($request->validated())){
            return new SuccessResponse('System settings have been updated.');
        }

        return new ErrorResponse(500, 'Unable to update system settings.');
    }
}
