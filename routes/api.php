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

// Backwards Compatibility with Telefony v1
Route::get('/caregiver/greeting', 'Api\TelefonyGreetingController@greeting');

Route::group(['prefix' => 'telefony', 'as' => 'telefony.'], function() {
    Route::get('/', 'Api\TelefonyGreetingController@greeting')->name('greeting');

    Route::post('check-in/response', 'Api\TelefonyCheckInController@checkInResponse')->name('check-in.response');
    Route::post('check-in/enter-digits', 'Api\TelefonyCheckInController@enterPhoneNumberDigits')->name('check-in.enter-digits');
    Route::post('check-in/accept-digits', 'Api\TelefonyCheckInController@acceptPhoneNumberDigits')->name('check-in.accept-digits');
    Route::post('check-in/{caregiver}', 'Api\TelefonyCheckInController@checkIn')->name('check-in');

    Route::post('check-out/response', 'Api\TelefonyCheckOutController@checkOutResponse')->name('check-out.response');
    Route::post('check-out/enter-digits', 'Api\TelefonyCheckOutController@enterPhoneNumberDigits')->name('check-out.enter-digits');
    Route::post('check-out/accept-digits', 'Api\TelefonyCheckOutController@acceptPhoneNumberDigits')->name('check-out.accept-digits');
    Route::post('check-out/check-for-injury', 'Api\TelefonyCheckOutController@checkForInjuryAction')->name('check-out.check-for-injury');
    Route::post('check-out/check-for-activities', 'Api\TelefonyCheckOutController@checkForActivitiesResponse')->name('check-out.check-for-activities');
    Route::post('check-out/confirm-activity', 'Api\TelefonyCheckOutController@confirmActivity')->name('check-out.confirm-activity');
    Route::post('check-out/record-activity', 'Api\TelefonyCheckOutController@recordActivity')->name('check-out.record-activity');
    Route::post('check-out/finalize', 'Api\TelefonyCheckOutController@finalizeCheckOut')->name('check-out.finalize');
    Route::post('check-out', 'Api\TelefonyCheckOutController@checkOut')->name('check-out');
});

