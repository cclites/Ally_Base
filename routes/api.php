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
Route::get('/caregiver/greeting', 'Api\CaregiverShiftController@greeting');
Route::post('/caregiver/checkin', 'Api\CaregiverShiftController@checkin');
