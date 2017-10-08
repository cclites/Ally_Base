<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

//Auth::loginUsingId(2);

Route::get('/', function () {
    return Auth::check() ? redirect()->route('home') : redirect()->route('login');
});

Auth::routes();

Route::group(['middleware' => 'auth'], function() {
    Route::get('/home', 'HomeController@index')->name('home');

    Route::get('/profile', 'ProfileController@index')->name('profile');
    Route::post('/profile', 'ProfileController@update');
    Route::post('/profile/password', 'ProfileController@password');
    Route::post('/profile/address/{type}', 'ProfileController@address');
    Route::post('/profile/phone/{type}', 'ProfileController@phone');
});

Route::group([
    'middleware' => ['auth', 'roles'],
    'roles' => ['caregiver'],
], function() {
    Route::get('schedule', 'ScheduleController@index')->name('schedule');
    Route::get('schedule/events', 'ScheduleController@events')->name('schedule.events');
    Route::get('clock-in/{schedule_id?}', 'ShiftController@index')->name('shift.index');
    Route::post('clock-in/{schedule_id?}', 'ShiftController@clockIn')->name('clock_in');
    Route::get('clock-out', 'ShiftController@clockedIn')->name('clocked_in');
    Route::post('clock-out', 'ShiftController@clockOut')->name('clock_out');

    Route::get('reports/deposits', 'Caregivers\ReportsController@deposits')->name('caregivers.reports.deposits');
    Route::get('reports/scheduled_payments', 'Caregivers\ReportsController@scheduled')->name('caregivers.reports.scheduled');
    Route::get('reports/shifts', 'Caregivers\ReportsController@shifts')->name('caregivers.reports.shifts');
});

Route::group([
    'as' => 'business.',
    'prefix' => 'business',
    'middleware' => ['auth', 'roles'],
    'roles' => ['office_user'],
], function() {
    Route::resource('activities', 'Business\ActivityController')->only(['index', 'store', 'update', 'destroy']);

    Route::resource('caregivers', 'Business\CaregiverController');
    Route::post('caregivers/{id}/address/{type}', 'Business\CaregiverController@address')->name('caregivers.address');
    Route::post('caregivers/{id}/phone/{type}', 'Business\CaregiverController@phone')->name('caregivers.phone');
    Route::get('caregivers/{id}/schedule', 'Business\CaregiverController@schedule')->name('caregivers.schedule');
    Route::resource('clients', 'Business\ClientController');
    Route::post('clients/{id}/address/{type}', 'Business\ClientController@address')->name('clients.address');
    Route::post('clients/{id}/phone/{type}', 'Business\ClientController@phone')->name('clients.phone');
    Route::get('clients/{id}/schedule', 'Business\ClientScheduleController@index')->name('clients.schedule');
    Route::post('clients/{id}/schedule', 'Business\ClientScheduleController@create')->name('clients.schedule.create');
    Route::post('clients/{id}/schedule/single', 'Business\ClientScheduleController@createSingle')->name('clients.schedule.create.single');
    Route::get('clients/{id}/schedule/{schedule_id}', 'Business\ClientScheduleController@show')->name('clients.schedule.show');
    Route::patch('clients/{id}/schedule/{schedule_id}', 'Business\ClientScheduleController@update')->name('clients.schedule.update');
    Route::patch('clients/{id}/schedule/{schedule_id}/single', 'Business\ClientScheduleController@updateSingle')->name('clients.schedule.update.single');
    Route::post('clients/{id}/schedule/{schedule_id}/delete', 'Business\ClientScheduleController@destroy')->name('clients.schedule.destroy');
    Route::post('clients/{id}/schedule/{schedule_id}/single/delete', 'Business\ClientScheduleController@destroySingle')->name('clients.schedule.destroy.single');
    Route::post('clients/{id}/payment/{type}', 'Business\ClientController@paymentMethod')->name('clients.paymentMethod');

    Route::get('reports/deposits', 'Business\ReportsController@deposits')->name('reports.deposits');
    Route::get('reports/payments', 'Business\ReportsController@payments')->name('reports.payments');
    Route::get('reports/scheduled_payments', 'Business\ReportsController@scheduled')->name('reports.scheduled');
    Route::get('reports/shifts', 'Business\ReportsController@shifts')->name('reports.shifts');

    Route::get('schedule', 'Business\ScheduleController@index')->name('schedule');
    Route::get('schedule/events', 'Business\ScheduleController@events')->name('schedule.events');

    Route::get('shifts/{id}', 'Business\ShiftController@show')->name('shifts.show');
    Route::post('shifts/{id}/verify', 'Business\ShiftController@verify')->name('shifts.verify');

    Route::get('users/{user}/documents', 'Business\DocumentController@index');
    Route::post('documents', 'Business\DocumentController@store');
    Route::get('documents/{document}/download', 'Business\DocumentController@download');
});
