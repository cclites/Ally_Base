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

Route::get('/{business}/caregiver-application/create', 'CaregiverApplicationController@create');
Route::post('/{business}/caregiver-application', 'CaregiverApplicationController@store');
Route::get('/reconfirm/saved', 'ConfirmationController@saved')->name('reconfirm.saved');
Route::get('/reconfirm/{encrypted_id}', 'ConfirmationController@reconfirm')->name('reconfirm.encrypted_id');
Route::post('/reconfirm/{encrypted_id}', 'ConfirmationController@store')->name('reconfirm.store');

Auth::routes();
Route::get('/logout', 'Auth\LoginController@logout');

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
    'roles' => ['client'],
    'namespace' => 'Clients',
], function () {
    Route::post('shift-history/approve', 'ShiftController@approveWeek');
    Route::get('shift-history/{week?}', 'ShiftController@index');
    Route::get('payment-history/{id}/print', 'PaymentHistoryController@printDetails');
    Route::resource('payment-history', 'PaymentHistoryController');
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

    Route::get('reports/payment-history', 'Caregivers\ReportsController@paymentHistory')->name('caregivers.reports.payment_history');
    Route::get('reports/payment-history/{id}', 'Caregivers\ReportsController@paymentDetails')->name('caregivers.reports.payment_details');
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

    Route::resource('care_plans', 'Business\CarePlanController');

    Route::get('settings/bank-accounts', 'Business\SettingController@bankAccounts')->name('settings.bank_accounts.index');
    Route::post('settings/bank-account/{type}', 'Business\SettingController@storeBankAccount')->name('settings.bank_accounts.update');
    Route::get('settings', 'Business\SettingController@index')->name('settings.index');
    Route::put('settings/{id}', 'Business\SettingController@update')->name('settings.update');

    Route::get('caregivers/applications', 'CaregiverApplicationController@index')->name('caregivers.applications');
    Route::post('caregivers/applications/search', 'CaregiverApplicationController@search')->name('caregivers.applications.search');
    Route::get('caregivers/applications/{id}', 'CaregiverApplicationController@show')->name('caregivers.applications.show');
    Route::get('caregivers/distance_report', 'Business\CaregiverLocationController@report')->name('caregivers.distance_report');
    Route::post('caregivers/distances', 'Business\CaregiverLocationController@distances')->name('caregivers.distances');
    Route::resource('caregivers', 'Business\CaregiverController');
    Route::post('caregivers/{id}/address/{type}', 'Business\CaregiverController@address')->name('caregivers.address');
    Route::post('caregivers/{id}/phone/{type}', 'Business\CaregiverController@phone')->name('caregivers.phone');
    Route::get('caregivers/{id}/schedule', 'Business\CaregiverController@schedule')->name('caregivers.schedule');
    Route::post('caregivers/{id}/bank_account', 'Business\CaregiverController@bankAccount')->name('caregivers.bank_account');
    Route::patch('caregivers/{caregiver}/password', 'Business\CaregiverController@changePassword')->name('caregivers.reset_password');

    Route::resource('caregivers/{caregiver}/licenses', 'Business\CaregiverLicenseController');

    Route::get('clients/list', 'Business\ClientController@listNames')->name('clients.list');
    Route::resource('clients', 'Business\ClientController');
    Route::post('clients/{id}/address/{type}', 'Business\ClientController@address')->name('clients.address');
    Route::post('clients/{id}/phone/{type}', 'Business\ClientController@phone')->name('clients.phone');
    Route::post('clients/{id}/caregivers', 'Business\ClientCaregiverController@store')->name('clients.caregivers.store');
    Route::get('clients/{id}/caregivers', 'Business\ClientCaregiverController@index')->name('clients.caregivers');
    Route::get('clients/{id}/schedule', 'Business\ClientScheduleController@index')->name('clients.schedule');
    Route::post('clients/{id}/schedule', 'Business\ClientScheduleController@create')->name('clients.schedule.create');
    Route::post('clients/{id}/schedule/single', 'Business\ClientScheduleController@createSingle')->name('clients.schedule.create.single');
    Route::get('clients/{id}/schedule/{schedule_id}', 'Business\ClientScheduleController@show')->name('clients.schedule.show');
    Route::patch('clients/{id}/schedule/{schedule_id}', 'Business\ClientScheduleController@update')->name('clients.schedule.update');
    Route::patch('clients/{id}/schedule/{schedule_id}/single', 'Business\ClientScheduleController@updateSingle')->name('clients.schedule.update.single');
    Route::post('clients/{id}/schedule/{schedule_id}/delete', 'Business\ClientScheduleController@destroy')->name('clients.schedule.destroy');
    Route::post('clients/{id}/schedule/{schedule_id}/single/delete', 'Business\ClientScheduleController@destroySingle')->name('clients.schedule.destroy.single');
    Route::post('clients/{id}/payment/{type}', 'Business\ClientController@paymentMethod')->name('clients.paymentMethod');
    Route::post('clients/{id}/send_confirmation_email', 'Business\ClientController@sendConfirmationEmail')->name('clients.send_confirmation_email');
    Route::get('clients/{id}/ally_pct', 'Business\ClientController@getAllyPercentage')->name('clients.ally_pct');
    Route::patch('clients/{client}/password', 'Business\ClientController@changePassword')->name('clients.reset_password');

    Route::get('reports/certification_expirations', 'Business\ReportsController@certificationExpirations')->name('reports.certification_expirations');
    Route::get('reports/deposits', 'Business\ReportsController@deposits')->name('reports.deposits');
    Route::get('reports/payments', 'Business\ReportsController@payments')->name('reports.payments');
    Route::get('reports/overtime', 'Business\ReportsController@overtime')->name('reports.overtime');
    Route::get('reports/scheduled_payments', 'Business\ReportsController@scheduled')->name('reports.scheduled');
    Route::get('reports/shifts', 'Business\ReportsController@shifts')->name('reports.shifts');
    Route::get('reports/medicaid', 'Business\ReportsController@medicaid')->name('reports.medicaid');

    Route::get('schedule', 'Business\ScheduleController@index')->name('schedule');
    Route::get('schedule/events', 'Business\ScheduleController@events')->name('schedule.events');
    Route::get('schedule/events/{schedule_id}', 'Business\ScheduleController@show')->name('schedule.show');

    Route::get('shifts/{id}', 'Business\ShiftController@show')->name('shifts.show');
    Route::post('shifts/{id}/verify', 'Business\ShiftController@verify')->name('shifts.verify');
    Route::post('shifts/{id}', 'Business\ShiftController@update')->name('shifts.update');
    Route::post('shifts/{id}/issues', 'Business\ShiftController@storeIssue')->name('shifts.issues.store');
    Route::patch('shifts/{id}/issues/{issue_id}', 'Business\ShiftController@updateIssue')->name('shifts.issues.update');

    Route::get('exceptions', 'Business\ExceptionController@index')->name('exceptions.index');
    Route::get('exceptions/{id}', 'Business\ExceptionController@show')->name('exceptions.show');
    Route::post('exceptions/{id}/acknowledge', 'Business\ExceptionController@acknowledge')->name('exceptions.acknowledge');

    Route::get('users/{user}/documents', 'Business\DocumentController@index');
    Route::post('documents', 'Business\DocumentController@store');
    Route::get('documents/{document}/download', 'Business\DocumentController@download');
    Route::delete('documents/{document}', 'Business\DocumentController@destroy');
});

Route::group(['middleware' => ['auth', 'roles'], 'roles' => ['office_user']], function () {
    Route::post('/notes/search', 'NoteController@search');
    Route::resource('notes', 'NoteController');
});

Route::group([
    'as' => 'admin.',
    'prefix' => 'admin',
    'middleware' => ['auth', 'roles'],
    'roles' => ['admin'],
], function() {
    Route::resource('businesses', 'Admin\BusinessController');
});