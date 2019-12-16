
Route::group([
    'as' => 'admin.',
    'prefix' => 'admin',
    'middleware' => ['auth', 'roles'],
    'roles' => ['admin'],
], function () {
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
    Route::get('chains', "Admin\BusinessChainController@index")->name('businesses.chains');
    Route::get('chains/{chain}', "Admin\BusinessChainController@show")->name('businesses.chains.show');
    Route::patch('chains/{chain}', "Admin\BusinessChainController@update")->name('businesses.chains.update');
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
    Route::get('deposits/import', 'Admin\DepositsController@import')->name('deposits.import');
    Route::post('deposits/import', 'Admin\DepositsController@processImport')->name('deposits.process_import');
    Route::post('deposits/finalize-import', 'Admin\DepositsController@finalizeImport')->name('deposits.finalize_import');
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
    Route::get('reports/total_charges_report', 'Admin\Reports\TotalChargesReportController@index')->name('reports.total_charges_report');
    Route::get('reports/total_deposits_report', 'Admin\Reports\TotalDepositsReportController@index')->name('reports.total_deposits_report');
    Route::get('reports/charges-vs-deposits', 'Admin\Reports\ChargesVsDepositsReportController@index')->name('reports.charges-vs-deposits');

    Route::get('reports/client-caregiver-visits', 'Admin\ReportsController@clientCaregiverVisits')->name('reports.client_caregiver_visits');
    Route::post('reports/client-caregiver-visits', 'Admin\ReportsController@clientCaregiverVisitsData')->name('reports.client_caregiver_visits_data');
    Route::get('reports/active-clients', 'Admin\ReportsController@activeClients')->name('reports.active_clients');

    Route::get('reports/paid-billed-audit-report', 'Admin\Report\PaidBilledAuditReportController@index')->name('reports.paid_billed_audit_report');
    Route::get('reports/bad-ssn-report/{type}', 'Admin\Reports\AdminBadSsnReportController@index')->name('reports.bad_ssn_report');
    Route::get('reports/bad-1099-report', 'Admin\Reports\AdminBad1099ReportController@index')->name('reports.bad_1099_report');


    /* Caregiver 1099 preview related */
    Route::get('admin-1099-actions', 'Admin\Admin1099Controller@index')->name('admin-1099-actions');

    Route::get('registry-email-list', 'Admin\Admin1099Controller@RegistryEmailList')->name('registry-email-list');
    Route::get('preview-1099-report', 'Admin\Reports\Admin1099PreviewReportController@index')->name('preview-1099-report');
    Route::get('ally-preview-1099-report', 'Admin\Reports\Ally1099PayerReportController@index')->name('ally-1099-report');

    /* Caregiver 1099s */
    Route::get('business-1099', 'Admin\Caregiver1099Controller@index')->name('business-1099');
    Route::get('business-1099/edit/{caregiver1099}', 'Admin\Caregiver1099Controller@edit')->name('business-1099-edit');
    Route::get('business-1099/download/{caregiver1099}', 'Admin\Caregiver1099Controller@downloadPdf')->name('business-1099-download');
    Route::post('business-1099/create', 'Admin\Caregiver1099Controller@store')->name('business-1099-create');
    Route::patch('business-1099/{caregiver1099}', 'Admin\Caregiver1099Controller@update')->name('business-1099-update');

    Route::get('/business-1099/userEmails/{year}/{role}', 'Admin\Admin1099Controller@UserEmailsList')->name('business-1099-transmit');
    Route::get('business-1099/transmit/{year}', 'Admin\Caregiver1099Controller@transmit')->name('business-1099-transmit');
    Route::get('admin-1099', 'Admin\Caregiver1099Controller@admin')->name('admin-1099');
    Route::patch('business-1099-settings/{business}', 'Business\SettingController@updateBusiness1099Settings')->name('business-1099-settings');
    Route::patch('chain-1099-settings/{chainClientTypeSettings}', 'Admin\ChainSettingsController@update')->name('chain-1099-settings');
    //
    Route::get('admin-contact-info', 'Admin\SystemSettingsController@show')->name('admin-contact-info');
    Route::patch('admin-contact-info', 'Admin\SystemSettingsController@update')->name('admin-contact-info-update');

    // notes import
    Route::get('note-import', 'Admin\NoteImportController@view')->name('note-import');
    Route::post('note-import', 'Admin\NoteImportController@process');
    Route::post('note-import/save', 'Admin\NoteImportController@store')->name('note-import.save');
    Route::post('note-import/map/client', 'Admin\NoteImportController@storeClientMapping')->name('note-import.map.client');
    Route::post('note-import/map/caregiver', 'Admin\NoteImportController@storeCaregiverMapping')->name('note-import.map.caregiver');

    // shift import
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
    Route::get('reports/caregiver_payments', 'Admin\ReportsController@caregiverPayments')->name('reports.data.caregiver_payments');
    Route::get('reports/client_charges', 'Admin\ReportsController@clientCharges')->name('reports.data.client_charges');
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
    Route::patch('invoices/clients/{invoice}', 'Admin\ClientInvoiceController@update');
    Route::delete('invoices/clients/{invoice}', 'Admin\ClientInvoiceController@destroy');
    Route::get('invoices/deposits', 'Admin\DepositInvoiceController@index')->name('invoices.deposits');
    Route::post('invoices/deposits', 'Admin\DepositInvoiceController@generate');
    Route::patch('invoices/deposits/{invoice}/{type?}', 'Admin\DepositInvoiceController@update');
    Route::get('invoices/caregivers/{invoice}', 'Admin\DepositInvoiceController@showCaregiverInvoice');
    Route::delete('invoices/caregivers/{invoice}', 'Admin\DepositInvoiceController@destroyCaregiverInvoice');
    Route::get('invoices/businesses/{invoice}', 'Admin\DepositInvoiceController@showBusinessInvoice');
    Route::delete('invoices/businesses/{invoice}', 'Admin\DepositInvoiceController@destroyBusinessInvoice');
    // Route::get('invoices/claims/{claim}', 'Admin\ClaimInvoiceController@show');

    Route::get('communication-log', 'Admin\CommunicationLogController@index')->name('communication-log');
    Route::get('communication-log/{log}', 'Admin\CommunicationLogController@show')->name('communication-log.show');

    Route::resource('payment-holds', 'Admin\PaymentHoldController');

    Route::get('/control-file', 'Admin\ControlFileController@index')->name('control-file');
});

Route::get('impersonate/stop', 'Admin\ImpersonateController@stopImpersonating')->name('impersonate.stop');
Route::get('impersonate/business/{business}', 'Admin\ImpersonateController@business')->name('impersonate.business');

Route::group(['prefix' => '{slug}', 'as' => 'business_chain_routes.'], function () {
    Route::get('/', 'CaregiverApplicationController@create');
    Route::get('apply', 'CaregiverApplicationController@create')->name('apply');
    Route::get('done/{application}', 'CaregiverApplicationController@done')->name('applications.done');
    Route::post('apply', 'CaregiverApplicationController@store');
});
