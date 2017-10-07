<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

// caregiver shift routes
Route::get('/caregiver/greeting', 'Api\CaregiverShiftController@greeting')->name('telefony.greeting');
Route::post('/caregiver/check-in-or-out', 'Api\CaregiverShiftController@checkInOrOut')->name('telefony.check_in_or_out');
Route::post('/caregiver/check-in', 'Api\CaregiverShiftController@checkIn')->name('telefony.check_in');
Route::post('/caregiver/check-out', 'Api\CaregiverShiftController@checkOut')->name('telefony.check_out');

