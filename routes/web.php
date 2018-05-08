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
Route::get('/confirm/saved', 'CaregiverConfirmationController@saved')->name('confirm.saved');
Route::get('/confirm/caregiver/{token}', 'CaregiverConfirmationController@show')->name('confirm.caregiver');
Route::post('/confirm/caregiver/{token}', 'CaregiverConfirmationController@store')->name('confirm.caregiver.store');
Route::get('/confirm/client/{token}', 'ClientConfirmationController@show')->name('confirm.client');
Route::post('/confirm/client/{token}', 'ClientConfirmationController@store')->name('confirm.client.store');
Route::get('/reconfirm/saved', 'ClientConfirmationController@saved')->name('reconfirm.saved');
Route::get('/reconfirm/{token}', 'ClientConfirmationController@show')->name('reconfirm.encrypted_id');
Route::post('/reconfirm/{token}', 'ClientConfirmationController@store')->name('reconfirm.store');

Auth::routes();
Route::get('/logout', 'Auth\LoginController@logout');

Route::group(['middleware' => 'auth'], function() {
    Route::get('/home', 'HomeController@index')->name('home');

    Route::get('/profile', 'ProfileController@index')->name('profile');
    Route::post('/profile', 'ProfileController@update');
    Route::post('/profile/password', 'ProfileController@password');
    Route::post('/profile/address/{type}', 'ProfileController@address');
    Route::get('/profile/phone', 'PhoneController@index');
    Route::post('/profile/phone', 'PhoneController@store');
    Route::put('/profile/phone/{id}', 'PhoneController@update');
    Route::delete('/profile/phone/{id}', 'PhoneController@destroy');

    Route::get('emergency-contacts/{user}/{contact}', 'EmergencyContactController@show');
    Route::get('emergency-contacts/{user}', 'EmergencyContactController@index');
    Route::post('emergency-contacts/{user}', 'EmergencyContactController@store');
    Route::put('emergency-contacts/{user}/{contact}', 'EmergencyContactController@update');
    Route::delete('emergency-contacts/{contact}', 'EmergencyContactController@destroy');
});

Route::group([
    'middleware' => ['auth', 'roles'],
    'roles' => ['client'],
], function () {
    Route::post('shift-history/approve', 'Clients\ShiftController@approveWeek');
    Route::get('shift-history/{week?}', 'Clients\ShiftController@index');
    Route::get('payment-history/{id}/print', 'Clients\PaymentHistoryController@printDetails');
    Route::resource('payment-history', 'Clients\PaymentHistoryController');
    Route::post('/profile/payment/{type}', 'ProfileController@paymentMethod');
    Route::delete('/profile/payment/{type}', 'ProfileController@destroyPaymentMethod');
});

Route::group([
    'middleware' => ['auth', 'roles'],
    'roles' => ['caregiver']
], function() {
    Route::get('schedule', 'ScheduleController@index')->name('schedule');
    Route::get('schedule/events', 'ScheduleController@events')->name('schedule.events');
    Route::get('clock-in/{schedule_id?}', 'ShiftController@index')->name('shift.index');
    Route::post('clock-in/{schedule_id?}', 'ShiftController@clockIn')->name('clock_in');
    Route::get('clock-out', 'ShiftController@clockedIn')->name('clocked_in');
    Route::post('clock-out', 'ShiftController@clockOut')->name('clock_out');
    Route::get('shifts/{shift}', 'ShiftController@shift')->name('caregivers.shift.show');

    Route::get('reports/payment-history', 'Caregivers\ReportsController@paymentHistory')->name('caregivers.reports.payment_history');
    Route::get('reports/payment-history/print/{year}', 'Caregivers\ReportsController@printPaymentHistory')->name('caregivers.reports.print_payment_history');
    Route::get('reports/payment-history/{id}/print', 'Caregivers\ReportsController@printPaymentDetails')->name('caregivers.reports.print_payment_details');
    Route::get('reports/payment-history/{id}', 'Caregivers\ReportsController@paymentDetails')->name('caregivers.reports.payment_details');
    Route::get('reports/scheduled_payments', 'Caregivers\ReportsController@scheduled')->name('caregivers.reports.scheduled');
    Route::get('reports/shifts', 'Caregivers\ReportsController@shifts')->name('caregivers.reports.shifts');

    Route::post('/profile/bank-account', 'ProfileController@bankAccount');
});

Route::group([
    'as' => 'business.',
    'prefix' => 'business',
    'middleware' => ['auth', 'roles'],
    'roles' => ['office_user', 'admin']
], function() {
    Route::get('phone-numbers/{user}', 'UserController@phoneNumbers');

    Route::resource('activities', 'Business\ActivityController')->only(['index', 'store', 'update', 'destroy']);

    Route::get('settings/bank-accounts', 'Business\SettingController@bankAccounts')->name('settings.bank_accounts.index');
    Route::post('settings/bank-account/{type}', 'Business\SettingController@storeBankAccount')->name('settings.bank_accounts.update');
    Route::get('settings', 'Business\SettingController@index')->name('settings.index');
    Route::put('settings/{id}', 'Business\SettingController@update')->name('settings.update');

    Route::get('caregivers/applications', 'CaregiverApplicationController@index')->name('caregivers.applications');
    Route::post('caregivers/applications/search', 'CaregiverApplicationController@search')->name('caregivers.applications.search');
    Route::get('caregivers/applications/{id}', 'CaregiverApplicationController@show')->name('caregivers.applications.show');
    Route::get('caregivers/applications/{id}/edit', 'CaregiverApplicationController@edit')->name('caregivers.applications.edit');
    Route::put('caregivers/applications/{id}', 'CaregiverApplicationController@update')->name('caregivers.applications.update');
    Route::get('caregivers/distance_report', 'Business\CaregiverLocationController@report')->name('caregivers.distance_report');
    Route::post('caregivers/distances', 'Business\CaregiverLocationController@distances')->name('caregivers.distances');
    Route::resource('caregivers', 'Business\CaregiverController');
    Route::post('caregivers/{caregiver}/reactivate', 'Business\CaregiverController@reactivate')->name('caregivers.reactivate');
    Route::post('caregivers/{caregiver}/address/{type}', 'Business\CaregiverController@address')->name('caregivers.address');
    Route::post('caregivers/{caregiver}/phone/{type}', 'Business\CaregiverController@phone')->name('caregivers.phone');
    Route::get('caregivers/{caregiver}/schedule', 'Business\CaregiverController@schedule')->name('caregivers.schedule');
    Route::post('caregivers/{caregiver}/bank_account', 'Business\CaregiverController@bankAccount')->name('caregivers.bank_account');
    Route::post('caregivers/{caregiver}/send_confirmation_email', 'Business\CaregiverController@sendConfirmationEmail')->name('caregivers.send_confirmation_email');
    Route::patch('caregivers/{caregiver}/password', 'Business\CaregiverController@changePassword')->name('caregivers.reset_password');
    Route::put('caregivers/{caregiver}/misc', 'Business\CaregiverController@misc')->name("caregivers.update_misc");
    Route::put('caregivers/{caregiver}/preferences', 'Business\CaregiverController@preferences')->name("caregivers.update_preferences");
    Route::get('caregivers/licenses/{license}/send-reminder', 'Business\CaregiverLicenseController@expirationReminder');
    Route::resource('caregivers/{caregiver}/licenses', 'Business\CaregiverLicenseController');

    Route::get('clients/list', 'Business\ClientController@listNames')->name('clients.list');
    Route::resource('clients', 'Business\ClientController');
    Route::put('clients/{client}/ltci', 'Business\ClientController@ltci')->name('clients.ltci');
    Route::resource('clients/{client}/care-plans', 'Business\ClientCarePlanController');
    Route::post('clients/{client}/exclude-caregiver', 'Business\ClientExcludedCaregiverController@store')->name('clients.exclude-caregiver');
    Route::get('clients/{client}/excluded-caregivers', 'Business\ClientExcludedCaregiverController@index')->name('clients.excluded-caregivers');
    Route::delete('clients/excluded-caregiver/{id}', 'Business\ClientExcludedCaregiverController@destroy')->name('clients.remove-excluded-caregiver');
    Route::get('clients/{client}/potential-caregivers', 'Business\ClientCaregiverController@potentialCaregivers')->name('clients.potential-caregivers');
    Route::post('clients/{client}/reactivate', 'Business\ClientController@reactivate')->name('clients.reactivate');
    Route::post('clients/{client}/service_orders', 'Business\ClientController@serviceOrders')->name('clients.service_orders');
    Route::post('clients/{client}/address/{type}', 'Business\ClientController@address')->name('clients.address');
    Route::post('clients/{client}/phone/{type}', 'Business\ClientController@phone')->name('clients.phone');
    Route::post('clients/{client}/caregivers', 'Business\ClientCaregiverController@store')->name('clients.caregivers.store');
    Route::get('clients/{client}/caregivers', 'Business\ClientCaregiverController@index')->name('clients.caregivers');
    Route::get('clients/{client}/caregivers/{caregiver}', 'Business\ClientCaregiverController@show')->name('clients.caregivers.show');
    Route::post('clients/{client}/caregivers/{caregiver}/schedule', 'Business\ClientCaregiverController@updateScheduleRates')->name('clients.caregivers.schedule.update');
    Route::get('clients/{client}/schedule', 'Business\ClientScheduleController@index')->name('clients.schedule');
    Route::post('clients/{client}/schedule', 'Business\ClientScheduleController@create')->name('clients.schedule.create');
    Route::post('clients/{client}/schedule/single', 'Business\ClientScheduleController@createSingle')->name('clients.schedule.create.single');
    Route::get('clients/{client}/schedule/{schedule}', 'Business\ClientScheduleController@show')->name('clients.schedule.show');
    Route::patch('clients/{client}/schedule/{schedule}', 'Business\ClientScheduleController@update')->name('clients.schedule.update');
    Route::patch('clients/{client}/schedule/{schedule}/single', 'Business\ClientScheduleController@updateSingle')->name('clients.schedule.update.single');
    Route::post('clients/{client}/schedule/{schedule}/delete', 'Business\ClientScheduleController@destroy')->name('clients.schedule.destroy');
    Route::post('clients/{client}/schedule/{schedule}/single/delete', 'Business\ClientScheduleController@destroySingle')->name('clients.schedule.destroy.single');
    Route::post('clients/{client}/payment/{type}', 'Business\ClientController@paymentMethod')->name('clients.paymentMethod');
    Route::delete('clients/{client}/payment/{type}', 'Business\ClientController@destroyPaymentMethod');
    Route::post('clients/{client}/send_confirmation_email', 'Business\ClientController@sendConfirmationEmail')->name('clients.send_confirmation_email');
    Route::get('clients/{client}/payment_type', 'Business\ClientController@getPaymentType')->name('clients.payment_type');
    Route::patch('clients/{client}/password', 'Business\ClientController@changePassword')->name('clients.reset_password');
    Route::post('clients/{client}/detach-caregiver', 'Business\ClientCaregiverController@detachCaregiver')->name('clients.detach-caregiver');
    Route::get('clients/payments/{payment}', 'Clients\PaymentHistoryController@show');
    Route::get('clients/payments/{payment}/print', 'Clients\PaymentHistoryController@printDetails');

    Route::get('reports/ltci-print', 'Business\ReportsController@ltciClaimsPrint')->name('clients.ltci_print');
    Route::get('reports/certification_expirations', 'Business\ReportsController@certificationExpirations')->name('reports.certification_expirations');
    Route::get('reports/credit-card-expiration', 'Business\ReportsController@creditCardExpiration')->name('reports.cc_expiration');
    Route::post('reports/credit-cards', 'Business\ReportsController@creditCards')->name('reports.credit_cards');
    Route::get('reports/client_caregivers', 'Business\ReportsController@clientCaregivers')->name('reports.client_caregivers');
    Route::get('reports/deposits', 'Business\ReportsController@deposits')->name('reports.deposits');
    Route::get('reports/payments', 'Business\ReportsController@payments')->name('reports.payments');
    Route::get('reports/overtime', 'Business\ReportsController@overtime')->name('reports.overtime');
    Route::post('reports/overtime', 'Business\ReportsController@overtimeData')->name('reports.overtime_data');
    Route::get('reports/scheduled_payments', 'Business\ReportsController@scheduled')->name('reports.scheduled');
    Route::get('reports/shifts', 'Business\ReportsController@shiftsReport')->name('reports.shifts');
    Route::get('reports/medicaid', 'Business\ReportsController@medicaidReport')->name('reports.medicaid');
    Route::post('reports/medicaid', 'Business\ReportsController@medicaid');
    Route::get('reports/scheduled_vs_actual', 'Business\ReportsController@scheduledVsActual')->name('reports.scheduled_vs_actual');
    Route::get('reports/client-email-missing', 'Business\ReportsController@clientEmailMissing')->name('reports.client_email_missing');
    Route::get('reports/reconciliation', 'Business\ReportsController@reconciliation')->name('reports.reconciliation');
    Route::get('reports/clients-onboarded', 'Business\ReportsController@clientOnboardedReport')->name('reports.client_onboarded');
    Route::post('reports/clients-onboarded', 'Business\ReportsController@clientOnboardedData')->name('reports.client_onboarded_data');
    Route::get('reports/caregivers-onboarded', 'Business\ReportsController@caregiverOnboardedReport')->name('reports.caregiver_onboarded');
    Route::post('reports/caregivers-onboarded', 'Business\ReportsController@caregiverOnboardedData')->name('reports.caregiver_onboarded_data');
    Route::get('reports/caregivers-missing-bank-accounts', 'Business\ReportsController@caregiversMissingBankAccounts')->name('reports.caregivers_missing_bank_accounts');
    Route::get('reports/printable-schedule', 'Business\ReportsController@printableSchedule')->name('reports.printable_schedule');
    Route::get('reports/export-timesheets', 'Business\ReportsController@exportTimesheets')->name('reports.export_timesheets');
    Route::post('reports/print/timesheet-data', 'Business\ReportsController@timesheetData')->name('reports.timesheet_data');
    Route::get('reports/caregivers/payment-history/{id}/print/{caregiver_id}', 'Business\ReportsController@printPaymentDetails')->name('reports.caregivers.print_payment_details');
    Route::get('reports/caregivers/{caregiver_id}/payment-history/print/{year}', 'Business\ReportsController@printPaymentHistory')->name('reports.caregivers.reports.print_payment_history');
    Route::get('reports/ltci-claims', 'Business\ReportsController@ltciClaims')->name('reports.ltci_claims');
    Route::Post('reports/ltci-claims', 'Business\ReportsController@ltciClaimsData')->name('reports.ltci_claims_data');

    Route::get('reports/data/shifts', 'Business\ReportsController@shifts')->name('reports.data.shifts');
    Route::get('reports/data/caregiver_payments', 'Business\ReportsController@caregiverPayments')->name('reports.data.caregiver_payments');
    Route::get('reports/data/client_charges', 'Business\ReportsController@clientCharges')->name('reports.data.client_charges');

    Route::post('schedule/print', 'Business\ScheduleController@print')->name('printable.schedule');
    Route::get('schedule/events', 'Business\ScheduleController@events')->name('schedule.events');
    Route::post('schedule/bulk_update', 'Business\ScheduleController@bulkUpdate')->name('schedule.bulk_update');
    Route::post('schedule/bulk_delete', 'Business\ScheduleController@bulkDestroy')->name('schedule.bulk_delete');
    Route::resource('schedule', 'Business\ScheduleController');

    Route::post('shifts/convert/{schedule}', 'Business\ShiftController@convertSchedule')->name('shifts.convert');
    Route::resource('shifts', 'Business\ShiftController');
    Route::post('shifts/{shift}/confirm', 'Business\ShiftController@confirm')->name('shifts.confirm');
    Route::get('shifts/{shift}/print', 'Business\ShiftController@printPage')->name('shifts.print');
    Route::post('shifts/{shift}/unconfirm', 'Business\ShiftController@unconfirm')->name('shifts.unconfirm');
    Route::get('shifts/{shift}/duplicate', 'Business\ShiftController@duplicate')->name('shifts.duplicate');
    Route::post('shifts/{shift}/verify', 'Business\ShiftController@verify')->name('shifts.verify');
    Route::post('shifts/{shift}/issues', 'Business\ShiftController@storeIssue')->name('shifts.issues.store');
    Route::patch('shifts/{shift}/issues/{issue_id}', 'Business\ShiftController@updateIssue')->name('shifts.issues.update');

    Route::get('transactions/{transaction}', 'Business\TransactionController@show')->name('transactions.show');

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
    Route::post('users/{user}/hold', 'Admin\UserController@addHold');
    Route::delete('users/{user}/hold', 'Admin\UserController@removeHold');
    Route::post('businesses/active_business', 'Admin\BusinessController@setActiveBusiness');
    Route::post('businesses/{business}/hold', 'Admin\BusinessController@addHold');
    Route::delete('businesses/{business}/hold', 'Admin\BusinessController@removeHold');
    Route::resource('businesses/{business}/users', 'Admin\OfficeUserController');
    Route::resource('businesses', 'Admin\BusinessController');
    Route::put('businesses/{business}/contact-info', 'Admin\BusinessController@updateContactInfo');
    Route::resource('clients', 'Admin\ClientController');
    Route::resource('caregivers', 'Admin\CaregiverController');
    Route::resource('failed_transactions', 'Admin\FailedTransactionController');

    Route::resource('users', 'Admin\UserController');
    Route::get('charges', 'Admin\ChargesController@index')->name('charges');
    Route::post('charges/successful/{payment}', 'Admin\ChargesController@markSuccessful')->name('charges.mark_successful');
    Route::post('charges/failed/{payment}', 'Admin\ChargesController@markFailed')->name('charges.mark_failed');
    Route::get('charges/pending', 'Admin\ChargesController@pending')->name('charges.pending');
    Route::get('charges/pending/{business}', 'Admin\ChargesController@pendingData')->name('charges.pending.data');
    Route::get('charges/pending/{business}/per-client', 'Admin\ChargesController@pendingDataPerClient')->name('charges.pending.data_per_client');
    Route::post('charges/pending/{business}', 'Admin\ChargesController@processCharges')->name('charges.process_charges');
    Route::get('charges/pending_shifts', 'Admin\PendingShiftsController@index')->name('charges.pending_shifts');
    Route::post('charges/pending_shifts/{shift?}', 'Admin\PendingShiftsController@update')->name('charges.update_shift_status');
    Route::view('charges/manual', 'admin.charges.manual')->name('charges.manual');
    Route::post('charges/manual', 'Admin\ChargesController@manualCharge');
    Route::get('deposits', 'Admin\DepositsController@index')->name('deposits');
    Route::get('deposits/failed', 'Admin\DepositsController@failed')->name('deposits.failed');
    Route::post('deposits/successful/{deposit}', 'Admin\DepositsController@markSuccessful')->name('deposits.mark_successful');
    Route::post('deposits/failed/{deposit}', 'Admin\DepositsController@markFailed')->name('deposits.mark_failed');
    Route::get('deposits/pending', 'Admin\DepositsController@pendingIndex')->name('deposits.pending');
    Route::get('deposits/adjustment', 'Admin\DepositsController@depositAdjustment')->name('deposits.adjustment');
    Route::post('deposits/adjustment', 'Admin\DepositsController@manualDeposit');
    Route::get('deposits/pending/{business}', 'Admin\DepositsController@pendingDeposits')->name('deposits.pending.business');
    Route::post('deposits/pending/{business}', 'Admin\DepositsController@deposit')->name('deposits.submit.business');
    Route::get('deposits/missing_accounts/{business}', 'Admin\DepositsController@missingBankAccount')->name('deposits.missing_accounts');
    Route::get('impersonate/{user}', 'Admin\ImpersonateController@impersonate')->name('impersonate');
    Route::get('shifts/data', 'Admin\ShiftsController@data')->name('shifts.data');
    Route::get('transactions', 'Admin\TransactionsController@index')->name('transactions');
    Route::get('transactions/report', 'Admin\TransactionsController@report')->name('transactions.report');
    Route::get('transactions/{transaction}', 'Admin\TransactionsController@show')->name('transactions.show');
    Route::get('missing_transactions', 'Admin\MissingTransactionsController@index')->name('missing_transactions');
    Route::redirect('reports', 'reports/unsettled');
    Route::get('reports/reconciliation', 'Admin\ReconciliationController@index')->name('reports.reconciliation');
    Route::get('reports/reconciliation/business/{business}', 'Admin\ReconciliationController@business')->name('reports.reconciliation.business');
    Route::get('reports/reconciliation/caregiver/{caregiver}', 'Admin\ReconciliationController@caregiver')->name('reports.reconciliation.caregiver');
    Route::get('reports/reconciliation/client/{client}', 'Admin\ReconciliationController@client')->name('reports.reconciliation.client');
    Route::view('reports/unsettled', 'admin.reports.unsettled')->name('reports.unsettled');
    Route::get('reports/unsettled/{data}', 'Admin\ReportsController@unsettled')->name('reports.unsettled.data');
    Route::get('reports/on_hold', 'Admin\ReportsController@onHold')->name('reports.on_hold');
    Route::get('reports/pending_transactions', 'Admin\ReportsController@pendingTransactions')->name('reports.pending_transactions');
    Route::get('reports/shared_shifts', 'Admin\ReportsController@sharedShifts')->name('reports.shared_shifts');
    Route::get('reports/unpaid_shifts', 'Admin\ReportsController@unpaidShifts')->name('reports.unpaid_shifts');

    Route::get('reports/client-caregiver-visits', 'Admin\ReportsController@clientCaregiverVisits')->name('reports.client_caregiver_visits');
    Route::post('reports/client-caregiver-visits', 'Admin\ReportsController@clientCaregiverVisitsData')->name('reports.client_caregiver_visits_data');
    Route::get('reports/active-clients', 'Admin\ReportsController@activeClients')->name('reports.active_clients');

    Route::get('import', 'Admin\ShiftImportController@view')->name('import');
    Route::post('import', 'Admin\ShiftImportController@process');
    Route::post('import/save', 'Admin\ShiftImportController@store')->name('import.save');
    Route::post('import/map/client', 'Admin\ShiftImportController@storeClientMapping')->name('import.map.client');
    Route::post('import/map/caregiver', 'Admin\ShiftImportController@storeCaregiverMapping')->name('import.map.caregiver');
    Route::resource('imports', 'Admin\ShiftImportController');

    Route::resource('businesses.clients', 'Admin\BusinessClientController');
    Route::resource('businesses.caregivers', 'Admin\BusinessCaregiverController');

    Route::get('reports/caregivers/deposits-missing-bank-account', 'Admin\ReportsController@caregiversDepositsWithoutBankAccount')
        ->name('reports.caregivers.deposits_missing_bank_account');

    Route::get('reports/bucket', 'Admin\BucketController@index')->name('reports.bucket');
    Route::get('reports/evv', 'Admin\ReportsController@evv')->name('reports.evv');
    Route::get('reports/finances', 'Admin\ReportsController@finances')->name('reports.finances');
    Route::post('reports/finances', 'Admin\ReportsController@financesData')->name('reports.finances.data');
    Route::get('reports/data/shifts', 'Admin\ReportsController@shifts')->name('reports.data.shifts');
    Route::get('reports/data/caregiver_payments', 'Admin\ReportsController@caregiverPayments')->name('reports.data.caregiver_payments');
    Route::get('reports/data/client_charges', 'Admin\ReportsController@clientCharges')->name('reports.data.client_charges');
});

Route::get('impersonate/stop', 'Admin\ImpersonateController@stopImpersonating')->name('impersonate.stop');
Route::get('impersonate/business/{business}', 'Admin\ImpersonateController@business')->name('impersonate.business');
