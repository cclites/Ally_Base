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

Route::view('check-my-time', 'check-my-time');

Route::get('/{business}/caregiver-application/create', 'CaregiverApplicationController@oldRedirect');
Route::get('/confirm-shifts/{token}', 'ConfirmShiftsController@confirmToken')->name('token-confirm-shifts');
Route::get('/confirm-shifts/all/{token}', 'ConfirmShiftsController@confirmAllWithToken')->name('token-confirm-all-shifts');
Route::redirect('/twilio/incoming', url('/api/telefony/sms/incoming'))->name('twilio.incoming');

Route::get('/account-setup/clients/{token}', 'ClientSetupController@show')->name('setup.clients');
Route::post('/account-setup/clients/{token}/step1', 'ClientSetupController@step1');
Route::post('/account-setup/clients/{token}/step2', 'ClientSetupController@step2');
Route::post('/account-setup/clients/{token}/step3', 'ClientSetupController@step3');
Route::get('/account-setup/clients/{token}/terms', 'ClientSetupController@terms');
Route::get('/account-setup/clients/{token}/check', 'ClientSetupController@checkStep');

Route::get('/account-setup/caregivers/{token}', 'CaregiverSetupController@show')->name('setup.caregivers');
Route::post('/account-setup/caregivers/{token}/step1', 'CaregiverSetupController@step1');
Route::post('/account-setup/caregivers/{token}/step2', 'CaregiverSetupController@step2');
Route::post('/account-setup/caregivers/{token}/step3', 'CaregiverSetupController@step3');
Route::get('/account-setup/caregivers/{token}/terms', 'CaregiverSetupController@terms');
Route::get('/account-setup/caregivers/{token}/check', 'CaregiverSetupController@checkStep');

Auth::routes();
Route::get('/logout', 'Auth\LoginController@logout');

Route::group(['middleware' => 'auth'], function() {
    Route::get('/home', 'HomeController@index')->name('home');

    Route::get('/profile', 'ProfileController@index')->name('profile');
    Route::post('/profile', 'ProfileController@update');
    Route::post('/profile/password', 'ProfileController@password');
    Route::post('/profile/address/{type}', 'ProfileController@address');
    Route::resource('/profile/phone', 'PhoneController');
    Route::patch('/profile/phone/{phone}/sms', 'PhoneController@updateSmsNumber');
    Route::patch('/profile/notification-options', 'ProfileController@updateNotificationOptions');
    Route::post('/profile/notification-preferences', 'ProfileController@updateNotificationPreferences');

    Route::get('emergency-contacts/{user}/{contact}', 'EmergencyContactController@show');
    Route::get('emergency-contacts/{user}', 'EmergencyContactController@index');
    Route::post('emergency-contacts/{user}', 'EmergencyContactController@store');
    Route::put('emergency-contacts/{user}/{contact}', 'EmergencyContactController@update');
    Route::delete('emergency-contacts/{contact}', 'EmergencyContactController@destroy');
    Route::patch('emergency-contacts/{user}/{contact}', 'EmergencyContactController@updatePriority');

    Route::get('business-settings', 'Business\SettingController@index')->name('business-settings');

    Route::get('knowledge-base', 'KnowledgeBaseController@index')->name('knowledge.base');
    Route::get('knowledge-base/attachments/{attachment}', 'KnowledgeBaseController@attachment')->name('knowledge.attachment');
});

Route::group([
    'middleware' => ['auth', 'roles'],
    'roles' => ['client'],
], function () {
    Route::get('client/caregivers', 'Clients\CaregiverController@index')->name('clients.caregivers');
    Route::get('unconfirmed-shifts', 'Clients\UnconfirmedShiftsController@index')->name('client.unconfirmed-shifts');
    Route::post('unconfirmed-shifts/{shift}/confirm', 'Clients\UnconfirmedShiftsController@confirm')->name('client.unconfirmed-shifts.confirm');
    Route::patch('unconfirmed-shifts/{shift}', 'Clients\UnconfirmedShiftsController@update')->name('client.unconfirmed-shifts.update');
    Route::get('unconfirmed-shifts/{shift}', 'Clients\UnconfirmedShiftsController@show')->name('client.unconfirmed-shifts.show');
    Route::post('shift-history/approve', 'Clients\ShiftController@approveWeek');
    Route::get('shift-history/{week?}', 'Clients\ShiftController@index')->name('client.shift-history');
    Route::get('payment-history/{id}/print', 'Clients\PaymentHistoryController@printDetails');
    Route::resource('payment-history', 'Clients\PaymentHistoryController');
    Route::post('/profile/payment/{type}', 'ProfileController@paymentMethod');
    Route::delete('/profile/payment/{type}', 'ProfileController@destroyPaymentMethod');
    Route::get('payment-type', 'Clients\UnconfirmedShiftsController@getPaymentType')->name('client.payment_type');
    Route::get('contacts', 'Clients\ClientContactController@index');
    Route::post('contacts', 'Clients\ClientContactController@store');
    Route::patch('contacts/{clientContact}', 'Clients\ClientContactController@update');
    Route::delete('contacts/{clientContact}', 'Clients\ClientContactController@destroy');
    Route::patch('contacts/{clientContact}/priority', 'Clients\ClientContactController@raisePriority');
    Route::get('client/payments', 'Clients\PaymentController@index')->name('client.payments');
    Route::get('client/payments/{payment}/{view?}', 'Clients\PaymentController@show')->name('client.payments.show');
    Route::get('client/invoices', 'Clients\InvoiceController@index')->name('client.invoices');
    Route::get('client/invoices/{invoice}/{view?}', 'Clients\InvoiceController@show')->name('client.invoices.show');

});

Route::group([
    'middleware' => ['auth', 'roles'],
    'roles' => ['caregiver']
], function() {
    Route::get('activities', 'Caregivers\ActivityController@index')->name('caregivers.activities');
    Route::get('caregiver/clients', 'Caregivers\ClientController@index')->name('caregivers.clients');
    Route::get('caregiver/clients/{client}', 'Caregivers\ClientController@show')->name('caregivers.clients.show');
    Route::get('caregiver/clients/{client}/narrative', 'Caregivers\ClientNarrativeController@index')->name('caregivers.clients.narrative');
    Route::patch('caregiver/clients/{client}/narrative/{narrative}', 'Caregivers\ClientNarrativeController@update')->name('caregivers.clients.narrative.update');
    Route::post('caregiver/clients/{client}/narrative', 'Caregivers\ClientNarrativeController@store')->name('caregivers.clients.narrative.store');
    Route::delete('caregiver/clients/{client}/narrative/{narrative}', 'Caregivers\ClientNarrativeController@destroy')->name('caregivers.clients.narrative.store');
    Route::get('caregiver/schedules/{client}/adjoining', 'Caregivers\ClientController@adjoiningSchedules')->name('clients.schedules.adjoining');
    Route::get('caregiver/schedules/{client}', 'Caregivers\ClientController@currentSchedules')->name('clients.schedules');
    Route::post('caregiver/verify_location/{client}', 'Caregivers\ClientController@verifyLocation')->name('clients.verify_location');

    Route::get('caregiver/deposits', 'Caregivers\DepositController@index')->name('caregiver.deposits');
    Route::get('caregiver/deposits/{deposit}/{view?}', 'Caregivers\DepositController@show')->name('caregiver.deposits.show');

    Route::get('clock-in/{schedule?}', 'Caregivers\ClockInController@index')->name('shift.index');
    Route::post('clock-in/{schedule?}', 'Caregivers\ClockInController@clockIn')->name('clock_in');
    Route::get('clocked-in', 'Caregivers\ClockInController@clockedIn')->name('clocked_in');

    Route::get('clock-out', 'Caregivers\ClockOutController@index')->name('clock_out');
    Route::get('clock-out/{shift}', 'Caregivers\ClockOutController@show')->name('clock_out.show');
    Route::post('clock-out/{shift}', 'Caregivers\ClockOutController@clockOut');

    Route::get('shifts/{shift}', 'Caregivers\ShiftController@show')->name('caregivers.shift.show');

    Route::get('schedule', 'Caregivers\ScheduleController@index')->name('schedule');
    Route::get('schedule/events', 'Caregivers\ScheduleController@events')->name('schedule.events');
    Route::resource('timesheets', 'Caregivers\TimesheetController');

    Route::get('reports/payment-history/print/{year}/{view?}', 'Caregivers\ReportsController@printPaymentHistory')->name('caregivers.reports.print_payment_history');
    Route::get('reports/payment-history/{id}/print', 'Caregivers\ReportsController@printPaymentDetails')->name('caregivers.reports.print_payment_details');
    Route::get('reports/payment-history/{id}', 'Caregivers\ReportsController@paymentDetails')->name('caregivers.reports.payment_details');
    Route::get('reports/scheduled_payments', 'Caregivers\ReportsController@scheduled')->name('caregivers.reports.scheduled');
    Route::get('reports/shifts', 'Caregivers\ReportsController@shifts')->name('caregivers.reports.shifts');

    Route::post('/profile/bank-account', 'ProfileController@bankAccount');
    Route::put('/profile/preferences', 'ProfileController@preferences');
    Route::put('/profile/skills', 'ProfileController@skills');

    Route::get('tasks', 'Caregivers\TasksController@index')->name('caregivers.tasks');
    Route::get('tasks/{task}', 'Caregivers\TasksController@show');
    Route::patch('tasks/{task}', 'Caregivers\TasksController@update');
});

Route::group([
    'as' => 'business.',
    'prefix' => 'business',
    'middleware' => ['auth', 'roles'],
    'roles' => ['office_user', 'admin']
], function() {
    Route::resource('activities', 'Business\ActivityController')->only(['index', 'store', 'update', 'destroy']);

    Route::post('custom-fields/options/{field}', 'Business\CustomFieldController@storeOptions')->name('custom_fields.options.create');
    Route::put('custom-fields/options/{field}', 'Business\CustomFieldController@updateOptions')->name('custom_fields.options.update');
    Route::post('custom-fields/{account}/{id}', 'Business\CustomFieldController@storeValue')->name('custom_fields.value.update');
    Route::resource('custom-fields', 'Business\CustomFieldController');
    Route::get('settings/bank-accounts/{business?}', 'Business\SettingController@bankAccounts')->name('settings.bank_accounts.index');
    Route::post('settings/bank-account/{type}', 'Business\SettingController@storeBankAccount')->name('settings.bank_accounts.update');
    Route::get('settings', 'Business\SettingController@index')->name('settings.index');
    Route::put('settings/overtime', 'Business\SettingController@updateOvertime')->name('settings.overtime');
    Route::put('settings/{id}', 'Business\SettingController@update')->name('settings.update');
    Route::get('settings/deactivation-reasons', 'Business\DeactivationReasonController@index')->name('deactivation_reasons');
    Route::post('settings/deactivation-reasons', 'Business\DeactivationReasonController@store')->name('deactivation_reasons.store');
    Route::delete('settings/deactivation-reasons/{reason}', 'Business\DeactivationReasonController@destroy')->name('deactivation_reasons.destroy');
    Route::put('update-payroll-policy/{id}', 'Business\SettingController@updatePayrollPolicy')->name('settings.updatePayrollPolicy');
    Route::resource('status-aliases', 'Business\StatusAliasController');
    Route::get('search', 'Business\QuickSearchController@index')->name('quick-search');

    Route::get('sales-people/{business}', 'Business\SalesPersonController@index')->name('sales-people.index');
    Route::post('sales-people', 'Business\SalesPersonController@store')->name('sales-people.store');
    Route::delete('sales-people/{salesPerson}', 'Business\SalesPersonController@destroy')->name('sales-people.destroy');
    Route::put('sales-people/{salesPerson}', 'Business\SalesPersonController@update')->name('sales-people.update');


    Route::get('care-match', 'Business\CareMatchController@index')->name('care-match');
    Route::post('care-match/client-matches/{client}', 'Business\CareMatchController@clientMatch')->name('care-match.client-matches');

    Route::get('caregivers/applications', 'CaregiverApplicationController@index')->name('caregivers.applications');
    Route::post('caregivers/applications/search', 'CaregiverApplicationController@search')->name('caregivers.applications.search');
    Route::get('caregivers/applications/{application}', 'CaregiverApplicationController@show')->name('caregivers.applications.show');
    Route::get('caregivers/applications/{application}/edit', 'CaregiverApplicationController@edit')->name('caregivers.applications.edit');
    Route::put('caregivers/applications/{application}', 'CaregiverApplicationController@update')->name('caregivers.applications.update');
    Route::post('caregivers/applications/{application}/convert', 'CaregiverApplicationController@convert')->name('caregivers.applications.convert');
    Route::get('caregivers/distance_report', 'Business\CaregiverLocationController@report')->name('caregivers.distance_report');
    Route::post('caregivers/distances', 'Business\CaregiverLocationController@distances')->name('caregivers.distances');
    Route::get('caregivers/paginate', 'Business\PaginatedCaregiverController@index')->name('caregivers.paginate');
    Route::resource('caregivers', 'Business\CaregiverController');
    Route::post('caregivers/{caregiver}/reactivate', 'Business\CaregiverController@reactivate')->name('caregivers.reactivate');
    Route::post('caregivers/{caregiver}/address/{type}', 'Business\CaregiverController@address')->name('caregivers.address');
    Route::post('caregivers/{caregiver}/phone/{type}', 'Business\CaregiverController@phone')->name('caregivers.phone');
    Route::get('caregivers/{caregiver}/schedule', 'Business\CaregiverController@schedule')->name('caregivers.schedule');
    Route::post('caregivers/{caregiver}/bank_account', 'Business\CaregiverController@bankAccount')->name('caregivers.bank_account');
    Route::patch('caregivers/{caregiver}/password', 'Business\CaregiverController@changePassword')->name('caregivers.reset_password');
    Route::put('caregivers/{caregiver}/misc', 'Business\CaregiverController@misc')->name("caregivers.update_misc");
    Route::put('caregivers/{caregiver}/preferences', 'Business\CaregiverController@preferences')->name("caregivers.update_preferences");
    Route::put('caregivers/{caregiver}/skills', 'Business\CaregiverController@skills')->name("caregivers.update_skills");
    Route::get('caregivers/licenses/{license}/send-reminder', 'Business\CaregiverLicenseController@expirationReminder');
    Route::get('caregivers/{caregiver}/phones', 'Business\CaregiverPhoneController@index')->name('caregivers.phones');
    Route::resource('caregivers/{caregiver}/licenses', 'Business\CaregiverLicenseController');
    Route::put('caregivers/{caregiver}/default-rates', 'Business\CaregiverController@defaultRates')->name('caregivers.default-rates');
    Route::get('caregivers/{caregiver}/clients', 'Business\CaregiverClientController@index')->name('caregivers.clients');
    Route::patch('caregivers/{caregiver}/notification-options', 'Business\CaregiverController@updateNotificationOptions');
    Route::post('caregivers/{caregiver}/notification-preferences', 'Business\CaregiverController@updateNotificationPreferences');
    Route::post('/caregivers/{caregiver}/welcome-email', 'Business\CaregiverController@welcomeEmail');
    Route::post('/caregivers/{caregiver}/training-email', 'Business\CaregiverController@trainingEmail');
    Route::resource('caregivers/{caregiver}/restrictions', 'Business\BusinessCaregiverRestrictionController');
    Route::patch('caregivers/{caregiver}/office-locations', 'Business\CaregiverController@updateOfficeLocations');

    Route::resource('clients/{client}/medications', 'Business\ClientMedicationController');
    Route::get('clients/{client}/onboarding', 'Business\ClientOnboardingController@create')->name('clients.onboarding.create');
    Route::post('clients/{client}/onboarding', 'Business\ClientOnboardingController@store')->name('clients.onboarding.store');
    Route::put('clients/onboarding/{clientOnboarding}', 'Business\ClientOnboardingController@update')->name('clients.onboarding.update');
    Route::get('clients/onboarding/{clientOnboarding}/intake-pdf', 'Business\ClientOnboardingController@intakePdf')->name('clients.onboarding.intake-pdf');
    Route::get('clients/referral-service-agreement/{rsa}/agreement-pdf', 'Business\ClientReferralServiceAgreementController@agreementPdf')->name('clients.referral-service-agreement.pdf');
    Route::post('clients/referral-service-agreement', 'Business\ClientReferralServiceAgreementController@store')->name('clients.referral-service-agreement.store');
    Route::get('clients/list', 'Business\ClientController@listNames')->name('clients.list');
    Route::resource('clients', 'Business\ClientController');
    Route::put('clients/{client}/ltci', 'Business\ClientController@ltci')->name('clients.ltci');
    Route::resource('clients/{client}/care-plans', 'Business\ClientCarePlanController');
    Route::resource('clients/{client}/goals', 'Business\ClientGoalsController');
    Route::post('clients/{client}/care-details', 'Business\ClientCareDetailsController@update')->name('clients.care-details.update');
    Route::post('clients/{client}/exclude-caregiver', 'Business\ClientExcludedCaregiverController@store')->name('clients.exclude-caregiver');
    Route::patch('clients/{client}/exclude-caregiver/{clientExcludedCaregiver}', 'Business\ClientExcludedCaregiverController@update')->name('clients.exclude-caregiver');
    Route::get('clients/{client}/excluded-caregivers', 'Business\ClientExcludedCaregiverController@index')->name('clients.excluded-caregivers');
    Route::delete('clients/excluded-caregiver/{id}', 'Business\ClientExcludedCaregiverController@destroy')->name('clients.remove-excluded-caregiver');
    Route::get('clients/{client}/potential-caregivers', 'Business\ClientCaregiverController@potentialCaregivers')->name('clients.potential-caregivers');
    Route::post('clients/{client}/reactivate', 'Business\ClientController@reactivate')->name('clients.reactivate');
    Route::post('clients/{client}/deactivate', 'Business\ClientController@destroy')->name('clients.deactivate');
    Route::post('clients/{client}/service_orders', 'Business\ClientController@serviceOrders')->name('clients.service_orders');
    Route::patch('clients/{client}/preferences', 'Business\ClientController@preferences')->name('clients.preferences');
    Route::get('clients/{client}/contacts', 'Business\ClientContactController@index');
    Route::post('clients/{client}/contacts', 'Business\ClientContactController@store');
    Route::patch('clients/{client}/contacts/{clientContact}', 'Business\ClientContactController@update');
    Route::delete('clients/{client}/contacts/{clientContact}', 'Business\ClientContactController@destroy');
    Route::patch('clients/{client}/contacts/{clientContact}/priority', 'Business\ClientContactController@raisePriority');
    Route::post('/clients/{client}/welcome-email', 'Business\ClientController@welcomeEmail');
    Route::post('/clients/{client}/training-email', 'Business\ClientController@trainingEmail');

    Route::get('clients/{client}/addresses', 'Business\ClientAddressController@index')->name('clients.addresses');
    Route::post('clients/{client}/address/{type}', 'Business\ClientController@address')->name('clients.address');
    Route::get('clients/{client}/phones', 'Business\ClientPhoneController@index')->name('clients.phones');
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
    Route::post('clients/{client}/payment/{type}', 'Business\ClientPaymentMethodController@store')->name('clients.paymentMethod');
    Route::delete('clients/{client}/payment/{type}', 'Business\ClientPaymentMethodController@delete');
    Route::post('clients/{client}/send_confirmation_email', 'Business\ClientController@sendConfirmationEmail')->name('clients.send_confirmation_email');
    Route::get('clients/{client}/payment_type', 'Business\ClientController@getPaymentType')->name('clients.payment_type');
    Route::patch('clients/{client}/password', 'Business\ClientController@changePassword')->name('clients.reset_password');
    Route::post('clients/{client}/detach-caregiver', 'Business\ClientCaregiverController@detachCaregiver')->name('clients.detach-caregiver');
    Route::put('clients/{client}/default-rates', 'Business\ClientController@defaultRates')->name('clients.default-rates');
    Route::get('clients/{client}/payers', 'Business\ClientPayerController@index')->name('clients.payers.index');
    Route::get('clients/{client}/payers/unique', 'Business\ClientPayerController@uniquePayers')->name('clients.payers.unique');
    Route::patch('clients/{client}/payers', 'Business\ClientPayerController@update')->name('clients.payers.update');
    Route::patch('clients/{client}/payers/{payer}/priority', 'Business\ClientPayerController@updatePriority')->name('clients.payers.priority');
    Route::get('clients/{client}/rates', 'Business\ClientRatesController@index')->name('clients.rates.index');
    Route::patch('clients/{client}/rates', 'Business\ClientRatesController@update')->name('clients.rates.update');
    Route::get('clients/{client}/can-unassign/{caregiver}', 'Business\ClientRatesController@canUnassign');

    Route::get('clients/{client}/narrative', 'Business\ClientNarrativeController@index')->name('clients.narrative');
    Route::patch('clients/{client}/narrative/{narrative}', 'Business\ClientNarrativeController@update')->name('clients.narrative.update');
    Route::post('clients/{client}/narrative', 'Business\ClientNarrativeController@store')->name('clients.narrative.store');
    Route::delete('clients/{client}/narrative/{narrative}', 'Business\ClientNarrativeController@destroy')->name('clients.narrative.store');

    Route::get('clients/{client}/communications', 'Business\Clients\ClientCommunicationsController@show');
    Route::post('clients/{client}/communications', 'Business\Clients\ClientCommunicationsController@create');

    Route::resource('rate-codes', 'Business\RateCodeController');

    Route::get('reports', 'Business\ReportsController@index')->name('reports.index');
    Route::get('reports/birthdays', 'Business\ReportsController@userBirthday')->name('reports.user_birthday');
    Route::get('reports/anniversary', 'Business\ReportsController@caregiverAnniversary')->name('reports.caregiver_anniversary');
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
    Route::get('reports/onboard-status', 'Business\ReportsController@onboardStatus')->name('reports.onboard_status');
    Route::get('reports/caregivers-missing-bank-accounts', 'Business\ReportsController@caregiversMissingBankAccounts')->name('reports.caregivers_missing_bank_accounts');
    Route::get('reports/clients-missing-payment-methods', 'Business\ReportsController@clientsMissingPaymentMethods')->name('reports.clients-missing-payment-methods');
    Route::get('reports/printable-schedule', 'Business\ReportsController@printableSchedule')->name('reports.printable_schedule');
    Route::get('reports/export-timesheets', 'Business\ReportsController@exportTimesheets')->name('reports.export_timesheets');
    Route::post('reports/print/timesheet-data', 'Business\ReportsController@timesheetData')->name('reports.timesheet_data');
    Route::get('reports/caregivers/{caregiver_id}/payment-history/print/{year}', 'Business\ReportsController@printPaymentHistory')->name('reports.caregivers.reports.print_payment_history');

    Route::get('reports/claims-report', 'Business\ClaimController@report')->name('reports.claims_report');
    Route::post('reports/claims-report', 'Business\ClaimController@data');
    Route::get('reports/claims-report/print', 'Business\ClaimController@print')->name('reports.claims_report.print');
    Route::get('reports/client-referral-sources', 'Business\ReportsController@clientReferralSources')->name('reports.client_referral_sources');
    Route::post('reports/client-referral-sources', 'Business\ReportsController@clientReferralSources');
    Route::get('reports/caregiver-referral-sources', 'Business\ReportsController@caregiverReferralSources')->name('reports.caregiver_referral_sources');
    Route::post('reports/caregiver-referral-sources', 'Business\ReportsController@caregiverReferralSources');
    Route::get('reports/case-manager', 'Business\ReportsController@caseManager')->name('reports.case_manager');
    Route::get('reports/caregiver-shifts', 'Business\ReportsController@caregiverShifts')->name('reports.caregiver_shifts');
    Route::get('reports/client-shifts', 'Business\ReportsController@clientShifts')->name('reports.client_shifts');
    Route::get('reports/prospects', 'Business\ReportsController@prospects')->name('reports.prospects');
    Route::get('reports/evv', 'Business\ReportsController@evv')->name('reports.evv');
    Route::get('reports/contacts', 'Business\ReportsController@contacts')->name('reports.contacts');
    Route::get('reports/payroll', 'Business\ReportsController@payrollReport')->name('reports.payroll');
    Route::get('reports/revenue', 'Business\ReportsController@revenuePage')->name('reports.revenue');
    Route::post('reports/revenue', 'Business\ReportsController@revenueReport')->name('reports.generate-revenue');
    Route::get('reports/sales-pipeline', 'Business\ReportsController@showSalesPipeline')->name('reports.pipeline');
    Route::get('reports/client-directory', 'Business\ReportsController@clientDirectory')->name('reports.client_directory');
    Route::get('reports/client-directory/download', 'Business\ReportsController@generateClientDirectoryReport')->name('reports.client_directory.download');
    Route::get('reports/caregiver-directory', 'Business\ReportsController@caregiverDirectory')->name('reports.caregiver_directory');
    Route::get('reports/caregiver-directory/download', 'Business\ReportsController@generateCaregiverDirectoryReport')->name('reports.caregiver_directory.download');
    Route::get('reports/prospect-directory', 'Business\ReportsController@prospectDirectory')->name('reports.prospect_directory');
    Route::get('reports/prospect-directory/download', 'Business\ReportsController@generateProspectDirectoryReport')->name('reports.prospect_directory.download');

    Route::get('reports/data/shifts', 'Business\ReportsController@shifts')->name('reports.data.shifts');
    Route::get('reports/data/birthdays', 'Business\ReportsController@userBirthdayData')->name('reports.data.user_birthday');
    Route::get('reports/data/shift/{id}', 'Business\ReportsController@shift')->name('reports.data.shift');
    Route::get('reports/data/caregiver_payments', 'Business\ReportsController@caregiverPayments')->name('reports.data.caregiver_payments');
    Route::get('reports/data/client_charges', 'Business\ReportsController@clientCharges')->name('reports.data.client_charges');
    Route::get('reports/client-stats', 'Business\Report\ClientStatsController@index')->name('reports.client_stats');
    Route::post('reports/client-stats', 'Business\Report\ClientStatsController@reportData')->name('reports.client_stats.data');
    Route::get('reports/caregiver-stats', 'Business\Report\CaregiverStatsController@index')->name('reports.caregiver_stats');
    Route::post('reports/caregiver-stats', 'Business\Report\CaregiverStatsController@reportData')->name('reports.caregiver_stats.data');

    Route::get('reports/projected-billing', 'Business\Report\ProjectedBillingReportController@index')->name('reports.projected-billing');
    Route::post('reports/projected-billing', 'Business\Report\ProjectedBillingReportController@reportData')->name('reports.projected-billing.data');
    Route::get('reports/projected-billing/print', 'Business\Report\ProjectedBillingReportController@print')->name('reports.projected-billing.print');
    Route::get('reports/payroll-export', 'Business\Report\BusinessPayrollExportReportController@index')->name('reports.payroll-export');
    Route::get('reports/disaster-plan-report', 'Business\Report\BusinessDisasterPlanReportController@index')->name('reports.disaster-plan');
    Route::get('reports/medicaid-billing', 'Business\Report\BusinessMedicaidBillingReportController@index')->name('reports.medicaid-billing');

    Route::get('client/payments/{payment}/{view?}', 'Clients\PaymentController@show')->name('payments.show');
    Route::get('client/invoices/{invoice}/{view?}', 'Clients\InvoiceController@show')->name('invoices.show');
    Route::get('statements/payments/{payment}/itemized', 'Business\StatementController@itemizePayment')->name('statements.payment.itemized');
    Route::get('statements/payments/{payment}/{view?}', 'Business\StatementController@payment')->name('statements.payment');
    Route::get('statements/deposits/{deposit}/itemized', 'Business\StatementController@itemizeDeposit')->name('statements.deposit.itemized');
    Route::get('statements/deposits/{deposit}/{view?}', 'Business\StatementController@deposit')->name('statements.deposit');

    Route::get('services', 'Business\ServiceController@index')->name('services.index');
    Route::post('services', 'Business\ServiceController@store');
    Route::patch('services/{service}', 'Business\ServiceController@update');
    Route::delete('services/{service}', 'Business\ServiceController@destroy');

    Route::post('authorization', 'Business\ClientAuthController@store');
    Route::patch('authorization/{auth}', 'Business\ClientAuthController@update');
    Route::delete('authorization/{auth}', 'Business\ClientAuthController@destroy');

    Route::post('schedule/warnings', 'Business\ScheduleController@warnings')->name('schedule.warnings');
    Route::post('schedule/print', 'Business\ScheduleController@print')->name('printable.schedule');
    Route::get('schedule/events', 'Business\ScheduleController@events')->name('schedule.events');
    Route::post('schedule/bulk_update', 'Business\ScheduleController@bulkUpdate')->name('schedule.bulk_update');
    Route::post('schedule/bulk_delete', 'Business\ScheduleController@bulkDestroy')->name('schedule.bulk_delete');
    Route::patch('schedule/{schedule}/status', 'Business\ScheduleController@updateStatus')->name('schedule.update_status');
    Route::resource('schedule', 'Business\ScheduleController');
    Route::get('schedule/{schedule}/preview', 'Business\ScheduleController@preview')->name('schedule.preview');

    Route::resource('shifts', 'Business\ShiftController');
    Route::post('shifts/{shift}/confirm', 'Business\ShiftController@confirm')->name('shifts.confirm');
    Route::get('shifts/{shift}/print', 'Business\ShiftController@printPage')->name('shifts.print');
    Route::post('shifts/{shift}/unconfirm', 'Business\ShiftController@unconfirm')->name('shifts.unconfirm');
    Route::get('shifts/{shift}/duplicate', 'Business\ShiftController@duplicate')->name('shifts.duplicate');
    Route::post('shifts/{shift}/verify', 'Business\ShiftController@verify')->name('shifts.verify');
    Route::post('shifts/{shift}/clockout', 'Business\ShiftController@officeClockOut')->name('shifts.clockout');

    Route::get('transactions/{transaction}', 'Business\TransactionController@show')->name('transactions.show');

    Route::get('notifications', 'Business\SystemNotificationController@index')->name('notifications.index');
    Route::get('notifications/{notification}', 'Business\SystemNotificationController@show')->name('notifications.show');
    Route::post('notifications/{notification}/acknowledge', 'Business\SystemNotificationController@acknowledge')->name('notifications.acknowledge');
    Route::post('notifications/acknowledge-all', 'Business\SystemNotificationController@acknowledgeAll')->name('notifications.acknowledge-all');

    Route::get('users/{user}/documents', 'Business\DocumentController@index');
    Route::post('documents', 'Business\DocumentController@store');
    Route::get('documents/{document}/download', 'Business\DocumentController@download');
    Route::delete('documents/{document}', 'Business\DocumentController@destroy');

    Route::get('timesheet', 'Business\TimesheetController@create')->name('timesheet.create');
    Route::post('timesheet', 'Business\TimesheetController@store')->name('timesheet.store');
    Route::get('timesheet/{timesheet}', 'Business\TimesheetController@edit')->name('timesheet');
    Route::post('timesheet/{timesheet}', 'Business\TimesheetController@update')->name('timesheet.update');
    Route::post('timesheet/{timesheet}/deny', 'Business\TimesheetController@deny')->name('timesheet.deny');

    Route::resource('questions', 'Business\QuestionController');
    Route::get('communication/text-caregivers', 'Business\CommunicationController@createText')->name('communication.text-caregivers');
    Route::post('communication/text-caregivers', 'Business\CommunicationController@sendText')->name('communication.text-caregivers.store');
    Route::put('communication/text-caregivers', 'Business\CommunicationController@saveRecipients')->name('communication.text-caregivers.recipients');
    Route::get('communication/sms-threads', 'Business\CommunicationController@threadIndex')->name('communication.sms-threads');
    Route::get('communication/sms-threads/{thread}', 'Business\CommunicationController@threadShow')->name('communication.sms-threads.show');
    Route::get('communication/sms-other-replies', 'Business\CommunicationController@otherReplies')->name('communication.sms-other-replies');
    Route::resource('tasks', 'Business\TasksController');

    Route::get('accounting/apply-payment', 'Business\ApplyPaymentController@index')->name('accounting.apply-payment.index');
    Route::get('accounting/claims', function() {
        return view('business.accounting.claims');
    })->name('accounting.claims');
    // Route::get('accounting/receivables', 'Business\ReceivablesController@index')->name('accounting.receivables.index');
    // Route::get('accounting/export', 'Business\AccountingExportController@index')->name('accounting.export.index');

    Route::get('franchisees', 'Business\FranchiseController@franchisees')->name('franchisees');
    Route::get('franchise/reports', 'Business\FranchiseController@reports')->name('franchise.reports');
    Route::get('franchise/payments', 'Business\FranchiseController@payments')->name('franchise.payments');

    Route::post('prospects/{prospect}/convert', 'Business\ProspectController@convert')->name('prospects.convert');
    Route::resource('prospects', 'Business\ProspectController');
    Route::resource('contacts', 'Business\OtherContactController');

    /*Quickbooks*/
    Route::get('quickbooks', 'Business\QuickbooksSettingsController@index')->name('quickbooks.index');
    Route::get('quickbooks/{business}/connect', 'Business\QuickbooksSettingsController@connect')->name('quickbooks.connect');
    Route::get('quickbooks/authorization', 'Business\QuickbooksSettingsController@authorization')->name('quickbooks.authorization');
    Route::post('quickbooks/{business}/disconnect', 'Business\QuickbooksSettingsController@disconnect');
    Route::get('quickbooks/{business}/customers', 'Business\QuickbooksSettingsController@customersList');
    Route::patch('quickbooks/{business}/customers', 'Business\QuickbooksSettingsController@customersUpdate');
    Route::post('quickbooks/{business}/customer', 'Business\QuickbooksSettingsController@customerCreate');
    Route::post('quickbooks/{business}/customers/sync', 'Business\QuickbooksSettingsController@customersSync');
    Route::get('quickbooks/{business}/services', 'Business\QuickbooksSettingsController@servicesList');
    Route::post('quickbooks/{business}/services/sync', 'Business\QuickbooksSettingsController@servicesSync');
    Route::patch('quickbooks/{business}/settings', 'Business\QuickbooksSettingsController@updateSettings');

    Route::get('quickbooks-queue', 'Business\QuickbooksQueueController@index')->name('quickbooks-queue');
    Route::post('quickbooks-queue/{invoice}/transfer', 'Business\QuickbooksQueueController@transfer')->name('quickbooks-queue.transfer');

    Route::resource('referral-sources', 'Business\ReferralSourceController');
    Route::get('{business}/office-users', 'Business\OfficeUserController@listForBusiness');

    Route::resource('payers', 'Business\PayerController');

    /* Claims & AR */
    Route::get('claims-ar', 'Business\ClaimsController@index')->name('claims-ar');
    Route::post('claims-ar/{invoice}/transmit', 'Business\ClaimsController@transmitInvoice')->name('claims-ar.transmit');
    Route::post('claims-ar/{invoice}/pay', 'Business\ClaimsController@pay')->name('claims-ar.pay');
});

Route::group(['middleware' => ['auth', 'roles'], 'roles' => ['office_user']], function () {
    Route::post('/notes/search', 'NoteController@search');
    Route::resource('notes', 'NoteController');
    Route::resource('note-templates', 'NoteTemplateController');
    Route::get('/business/office-users', 'Business\OfficeUserController@index');
});

Route::group([
    'as' => 'admin.',
    'prefix' => 'admin',
    'middleware' => ['auth', 'roles'],
    'roles' => ['admin'],
], function() {
    Route::get('microbilt', 'Admin\MicrobiltController@index')->name('microbilt');
    Route::post('microbilt', 'Admin\MicrobiltController@test');
    Route::post('users/{user}/hold', 'Admin\UserController@addHold');
    Route::delete('users/{user}/hold', 'Admin\UserController@removeHold');
    Route::post('businesses/active_business', 'Admin\BusinessController@setActiveBusiness');
    Route::post('businesses/{business}/hold', 'Admin\BusinessController@addHold');
    Route::delete('businesses/{business}/hold', 'Admin\BusinessController@removeHold');
    Route::resource('businesses', 'Admin\BusinessController');
    Route::put('businesses/{business}/contact-info', 'Admin\BusinessController@updateContactInfo');
    Route::patch('businesses/{business}/sms-settings', 'Admin\BusinessController@updateSmsSettings');
    Route::get('chains', "Admin\BusinessChainController@index")->name("businesses.chains");
    Route::get('chains/{chain}', "Admin\BusinessChainController@show")->name("businesses.chains.show");
    Route::patch('chains/{chain}', "Admin\BusinessChainController@update")->name("businesses.chains.update");
    Route::resource('chains/{chain}/users', 'Admin\OfficeUserController');

    Route::resource('clients', 'Admin\ClientController');
    Route::resource('caregivers', 'Admin\CaregiverController');
    Route::resource('failed_transactions', 'Admin\FailedTransactionController');

    Route::resource('users', 'Admin\UserController');
    Route::get('charges', 'Admin\ChargesController@index')->name('charges');
    Route::post('charges/successful/{payment}', 'Admin\ChargesController@markSuccessful')->name('charges.mark_successful');
    Route::post('charges/failed/{payment}', 'Admin\ChargesController@markFailed')->name('charges.mark_failed');
    Route::get('charges/pending', 'Admin\ChargesController@pending')->name('charges.pending');
    Route::post('charges/charge/{chain}', 'Admin\ChargesController@processCharges')->name('charges.processCharges');
    Route::get('charges/pending_shifts', 'Admin\PendingShiftsController@index')->name('charges.pending_shifts');
    Route::post('charges/pending_shifts/{shift?}', 'Admin\PendingShiftsController@update')->name('charges.update_shift_status');
    Route::view('charges/manual', 'admin.charges.manual')->name('charges.manual');
    Route::post('charges/manual', 'Admin\ChargesController@manualCharge');
    Route::get('charges/{payment}/{view?}', 'Admin\ChargesController@show')->name('charges.show');
    Route::get('deposits', 'Admin\DepositsController@index')->name('deposits');
    Route::get('deposits/failed', 'Admin\DepositsController@failed')->name('deposits.failed');
    Route::post('deposits/successful/{deposit}', 'Admin\DepositsController@markSuccessful')->name('deposits.mark_successful');
    Route::post('deposits/failed/{deposit}', 'Admin\DepositsController@markFailed')->name('deposits.mark_failed');
    Route::get('deposits/pending', 'Admin\DepositsController@pendingIndex')->name('deposits.pending');
    Route::get('deposits/adjustment', 'Admin\DepositsController@depositAdjustment')->name('deposits.adjustment');
    Route::post('deposits/adjustment', 'Admin\DepositsController@manualDeposit');
    Route::post('deposits/deposit/{chain}', 'Admin\DepositsController@processDeposits')->name('deposits.deposit');
    Route::get('deposits/missing_accounts/{business}', 'Admin\DepositsController@missingBankAccount')->name('deposits.missing_accounts');
    Route::get('deposits/{deposit}/{view?}', 'Admin\DepositsController@show')->name('deposits.show');
    Route::get('impersonate/{user}', 'Admin\ImpersonateController@impersonate')->name('impersonate');
    Route::get('shifts/data', 'Admin\ShiftsController@data')->name('shifts.data');
    Route::get('transactions', 'Admin\TransactionsController@index')->name('transactions');
    Route::get('transactions/report', 'Admin\TransactionsController@report')->name('transactions.report');
    Route::post('transactions/refund/{transaction}', 'Admin\TransactionsController@refund')->name('transactions.refund');
    Route::get('transactions/{transaction}', 'Admin\TransactionsController@show')->name('transactions.show');
    Route::get('missing_transactions', 'Admin\MissingTransactionsController@index')->name('missing_transactions');
//    Route::redirect('reports', 'reports/unsettled');
    Route::get('reports', 'Admin\ReportsController@index')->name('reports.index');
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
    Route::get('import/description/{provider}', 'Admin\ShiftImportController@getDescription')->name('import.description');
    Route::resource('imports', 'Admin\ShiftImportController');

    Route::resource('businesses.clients', 'Admin\BusinessClientController');
    Route::resource('businesses.caregivers', 'Admin\BusinessCaregiverController');

    Route::get('reports/caregivers/deposits-missing-bank-account', 'Admin\ReportsController@caregiversDepositsWithoutBankAccount')
        ->name('reports.caregivers.deposits_missing_bank_account');

    Route::get('reports/bucket', 'Admin\BucketController@index')->name('reports.bucket');
    Route::get('reports/evv', 'Admin\ReportsController@evv')->name('reports.evv');
    Route::get('reports/emails/{type?}', 'Admin\ReportsController@emails')->name('reports.emails');
    Route::get('reports/finances', 'Admin\ReportsController@finances')->name('reports.finances');
    Route::post('reports/finances', 'Admin\ReportsController@financesData')->name('reports.finances.data');
    Route::get('reports/data/shifts', 'Admin\ReportsController@shifts')->name('reports.data.shifts');
    Route::get('reports/data/caregiver_payments', 'Admin\ReportsController@caregiverPayments')->name('reports.data.caregiver_payments');
    Route::get('reports/data/client_charges', 'Admin\ReportsController@clientCharges')->name('reports.data.client_charges');
    Route::get('audit-log', 'Admin\AuditLogController@index')->name('reports.audit-log');

    /*Nacha Ach*/
    Route::get('nacha-ach', 'Admin\NachaAchController@index')->name('nacha_ach');
    Route::post('nacha-ach/generate', 'Admin\NachaAchController@generate')->name('nacha_ach.generate');

    Route::get('knowledge-manager', 'Admin\KnowledgeManagerController@index')->name('knowledge.manager');
    Route::post('knowledge-manager', 'Admin\KnowledgeManagerController@store');
    Route::get('knowledge-manager/create', 'Admin\KnowledgeManagerController@create');
    Route::get('knowledge-manager/{knowledge}', 'Admin\KnowledgeManagerController@edit')->name('knowledge.edit');
    Route::patch('knowledge-manager/{knowledge}', 'Admin\KnowledgeManagerController@update');
    Route::delete('knowledge-manager/{knowledge}', 'Admin\KnowledgeManagerController@destroy');
    Route::post('knowledge-manager/attachments', 'Admin\KnowledgeAttachmentController@store');
    Route::post('knowledge-manager/video', 'Admin\KnowledgeAttachmentController@storeVideo');

    /* Invoices */
    Route::get('invoices/clients', 'Admin\ClientInvoiceController@index')->name('invoices.clients');
    Route::post('invoices/clients', 'Admin\ClientInvoiceController@generate');
    Route::get('invoices/clients/{invoice}', 'Admin\ClientInvoiceController@show');
    Route::delete('invoices/clients/{invoice}', 'Admin\ClientInvoiceController@destroy');
    Route::get('invoices/deposits', 'Admin\DepositInvoiceController@index')->name('invoices.deposits');
    Route::post('invoices/deposits', 'Admin\DepositInvoiceController@generate');
    Route::get('invoices/caregivers/{invoice}', 'Admin\DepositInvoiceController@showCaregiverInvoice');
    Route::delete('invoices/caregivers/{invoice}', 'Admin\DepositInvoiceController@destroyCaregiverInvoice');
    Route::get('invoices/businesses/{invoice}', 'Admin\DepositInvoiceController@showBusinessInvoice');
    Route::delete('invoices/businesses/{invoice}', 'Admin\DepositInvoiceController@destroyBusinessInvoice');

    Route::get('communication-log', 'Admin\CommunicationLogController@index')->name('communication-log');
    Route::get('communication-log/{log}', 'Admin\CommunicationLogController@show')->name('communication-log.show');

    Route::resource('payment-holds', 'Admin\PaymentHoldController');
});

Route::get('impersonate/stop', 'Admin\ImpersonateController@stopImpersonating')->name('impersonate.stop');
Route::get('impersonate/business/{business}', 'Admin\ImpersonateController@business')->name('impersonate.business');

Route::group(['prefix' => '{slug}', 'as' => 'business_chain_routes.'], function() {
    Route::get('/', 'CaregiverApplicationController@create');
    Route::get('apply', 'CaregiverApplicationController@create')->name('apply');
    Route::get('done/{application}', 'CaregiverApplicationController@done')->name('applications.done');
    Route::post('apply', 'CaregiverApplicationController@store');
});
