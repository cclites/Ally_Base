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

Route::group(['prefix' => 'qb', 'as' => 'qb.'], function() {
    Route::get('/ping', 'Api\Quickbooks\QuickbooksDesktopController@ping')->name('ping');
    Route::post('/sync', 'Api\Quickbooks\QuickbooksDesktopController@sync')->name('sync');
    Route::get('/invoices/fetch', 'Api\Quickbooks\QuickbooksDesktopController@fetchInvoices')->name('invoices.fetch');
    Route::post('/invoices/process', 'Api\Quickbooks\QuickbooksDesktopController@processInvoices')->name('invoices.process');
    Route::post('/invoices/results', 'Api\Quickbooks\QuickbooksDesktopController@invoiceResults')->name('invoices.results');
});

// Backwards Compatibility with Telefony v1
Route::redirect('/caregiver/greeting', url('/api/telefony'));

Route::group(['prefix' => 'telefony', 'as' => 'telefony.'], function() {
    Route::get('/', 'Api\Telefony\TelefonyGreetingController@greeting')->name('greeting');
    Route::post('/check-in-or-out', 'Api\Telefony\TelefonyGreetingController@checkInOrOut')->name('check-in-or-out');

    Route::post('check-in/response', 'Api\Telefony\TelefonyCheckInController@checkInResponse')->name('check-in.response');
    Route::post('check-in/enter-digits', 'Api\Telefony\TelefonyCheckInController@enterPhoneNumberDigits')->name('check-in.enter-digits');
    Route::post('check-in/accept-digits', 'Api\Telefony\TelefonyCheckInController@acceptPhoneNumberDigits')->name('check-in.accept-digits');
    Route::post('check-in/{caregiver}', 'Api\Telefony\TelefonyCheckInController@checkIn')->name('check-in');

    Route::post('check-out/response', 'Api\Telefony\TelefonyCheckOutController@checkOutResponse')->name('check-out.response');
    Route::post('check-out/enter-digits', 'Api\Telefony\TelefonyCheckOutController@enterPhoneNumberDigits')->name('check-out.enter-digits');
    Route::post('check-out/accept-digits', 'Api\Telefony\TelefonyCheckOutController@acceptPhoneNumberDigits')->name('check-out.accept-digits');
    Route::post('check-out/check-for-injury/{shift}', 'Api\Telefony\TelefonyCheckOutController@checkForInjuryAction')->name('check-out.check-for-injury');
    Route::post('check-out/check-for-mileage/{shift}', 'Api\Telefony\TelefonyCheckOutController@checkForMileageAction')->name('check-out.check-for-mileage');
    Route::post('check-out/confirm-mileage/{shift}', 'Api\Telefony\TelefonyCheckOutController@confirmMileage')->name('check-out.confirm-mileage');
    Route::post('check-out/record-mileage/{shift}/{mileage}', 'Api\Telefony\TelefonyCheckOutController@recordMileage')->name('check-out.record-mileage');
    Route::post('check-out/check-for-activities/{shift}', 'Api\Telefony\TelefonyCheckOutController@checkForActivitiesResponse')->name('check-out.check-for-activities');
    Route::post('check-out/confirm-activity/{shift}', 'Api\Telefony\TelefonyCheckOutController@confirmActivity')->name('check-out.confirm-activity');
    Route::post('check-out/record-activity/{shift}/{activity}', 'Api\Telefony\TelefonyCheckOutController@recordActivity')->name('check-out.record-activity');
    Route::post('check-out/finalize/{shift}', 'Api\Telefony\TelefonyCheckOutController@finalizeCheckOut')->name('check-out.finalize');
    Route::post('check-out/{shift}', 'Api\Telefony\TelefonyCheckOutController@checkOut')->name('check-out');

    Route::post('sms/incoming', 'Api\Telefony\TelephonySMSController@incoming')->name('sms.incoming');
});

