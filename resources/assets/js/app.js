
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');
require('./ie-fix');
require('./custom');
require('./sidebarmenu');

import BootstrapVue from 'bootstrap-vue'
import Form from './classes/Form'
import store from './store'
import VueTheMask from 'vue-the-mask'
import VeeValidate from 'vee-validate'
import * as VueGoogleMaps from 'vue2-google-maps'
import 'vue-plyr';
import 'vue-plyr/dist/vue-plyr.css';

window.Form = Form;
window.Vue = require('vue');
window.Store = store;
window.DevelopmentMode = process.env.NODE_ENV === 'development';
Vue.use(BootstrapVue);
Vue.use(VueTheMask);
Vue.use(VeeValidate, {fieldsBagName: '_fields'});
Vue.use(VueGoogleMaps, {
    load: {
        key: window.gmapsKey,
    },

    //// If you intend to programmatically custom event listener code
    //// (e.g. `this.$refs.gmap.$on('zoom_changed', someFunc)`)
    //// instead of going through Vue templates (e.g. `<GmapMap @zoom_changed="someFunc">`)
    //// you might need to turn this on.
    // autobindAllEvents: false,

    //// If you want to manually install components, e.g.
    //// import {GmapMarker} from 'vue2-google-maps/src/components/marker'
    //// Vue.component('GmapMarker', GmapMarker)
    //// then disable the following:
    installComponents: true,
});

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

// App Components
Vue.component('date-picker', require('./components/DatePicker.vue'));
Vue.component('time-picker', require('./components/TimePicker.vue'));
Vue.component('activity-list', require('./components/ActivityList.vue'));
Vue.component('contact-list-tab', require('./components/users/ContactListTab'));
Vue.component('loading-card', require('./components/LoadingCard.vue'));
Vue.component('signature-pad', require('./components/SignaturePad'));
Vue.component('select2', require('./components/Select2'));
Vue.component('submit-button', require('./components/SubmitButton'));
Vue.component('quick-search', require('./components/QuickSearch'));
Vue.component('user-search-dropdown', require('./components/UserSearchDropdown'));
Vue.component('shift-map', require('./components/ShiftMap'));
Vue.component('edit-avatar', require('./components/EditAvatar'));
Vue.component('user-avatar', require('./components/UserAvatar'));
Vue.component('checkbox-group', require('./components/CheckboxGroup'));
Vue.component('notification-preferences', require('./components/NotificationPreferences'));
Vue.component('ally-table', require('./components/AllyTable'));
Vue.component('confirm-modal', require('./components/modals/ConfirmModal'));
Vue.component('client-contacts-tab', require('./components/ClientContactsTab'));
Vue.component('client-contacts-modal', require('./components/ClientContactsModal'));
Vue.component('shift-details', require('./components/shifts/ShiftDetails'));

// Client
Vue.component('ltc-shift-approval', require('./components/clients/LtcShiftApproval'));
Vue.component('client-invoice-history', require('./components/clients/ClientInvoiceHistory'));
Vue.component('client-payment-history', require('./components/clients/ClientPaymentHistory'));
Vue.component('client-payment-details', require('./components/clients/ClientPaymentDetails'));
Vue.component('client-payment-details-print', require('./components/clients/ClientPaymentDetailsPrint'));
Vue.component('client-phone-numbers-tab', require('./components/clients/profile/ClientPhoneNumbersTab'));
Vue.component('client-unconfirmed-shifts', require('./components/clients/UnconfirmedShifts'));
Vue.component('client-modify-shift-modal', require('./components/clients/ModifyShiftModal'));


// Caregiver
Vue.component('caregiver-deposit-history', require('./components/caregivers/CaregiverDepositHistory'));
Vue.component('caregiver-phone-numbers-tab', require('./components/caregivers/profile/CaregiverPhoneNumbersTab'));
Vue.component('caregiver-task-list', require('./components/caregivers/TaskList'));

// Admin
Vue.component('admin-communication-log', require('./components/admin/CommunicationLog'));
Vue.component('admin-business-select', require('./components/admin/AdminBusinessSelect'));
Vue.component('admin-import', require('./components/admin/import/AdminImport'));
Vue.component('admin-import-report', require('./components/admin/import/AdminImportReport'));
Vue.component('admin-bucket-report', require('./components/admin/AdminBucketReport'));
Vue.component('admin-charges-report', require('./components/admin/AdminChargesReport.vue'));
Vue.component('admin-deposit-report', require('./components/admin/AdminDepositReport.vue'));
Vue.component('admin-deposit-invoices', require('./components/admin/AdminDepositInvoices'));
Vue.component('admin-client-invoices', require('./components/admin/AdminClientInvoices'));
Vue.component('admin-evv-report', require('./components/admin/AdminEvvReport'));
Vue.component('admin-emails-report', require('./components/admin/reports/AdminEmailsReport'));
Vue.component('admin-failed-deposit-report', require('./components/admin/AdminFailedDepositReport'));
Vue.component('admin-failed-transactions-report', require('./components/admin/AdminFailedTransactionsReport'));
Vue.component('admin-unsettled-report', require('./components/admin/AdminUnsettledReport.vue'));
Vue.component('admin-user-list', require('./components/admin/AdminUserList.vue'));
Vue.component('admin-manual-deposit', require('./components/admin/AdminManualDeposit'));
Vue.component('admin-manual-charge', require('./components/admin/AdminManualCharge'));
Vue.component('admin-missing-transactions', require('./components/admin/AdminMissingTransactions'));
Vue.component('admin-pending-transactions-report', require('./components/admin/AdminPendingTransactionsReport'));
Vue.component('admin-on-hold-report', require('./components/admin/AdminOnHoldReport'));
Vue.component('admin-shared-shifts-report', require('./components/admin/AdminSharedShiftsReport'));
Vue.component('admin-unpaid-shifts-report', require('./components/admin/AdminUnpaidShiftsReport'));
// Vue.component('admin-pending-charges', require('./components/admin/AdminPendingCharges.vue'));
Vue.component('admin-payments', require('./components/admin/AdminPayments'));
Vue.component('admin-deposits', require('./components/admin/AdminDeposits'));
Vue.component('admin-pending-shifts', require('./components/admin/AdminPendingShifts.vue'));
Vue.component('admin-reconciliation-report', require('./components/admin/AdminReconciliationReport'));
Vue.component('admin-transaction', require('./components/admin/AdminTransaction.vue'));
Vue.component('admin-transactions-report', require('./components/admin/AdminTransactionsReport.vue'));
Vue.component('admin-active-clients-report', require('./components/admin/reports/ActiveClientsReport.vue'));
Vue.component('admin-audit-log', require('./components/admin/reports/AuditLog'));
Vue.component('authorized-payment-checkbox', require('./components/admin/AuthorizePaymentCheckbox.vue'));
Vue.component('charge-payment-button', require('./components/admin/ChargePaymentButton.vue'));
Vue.component('business-create', require('./components/admin/BusinessCreate.vue'));
Vue.component('business-edit', require('./components/admin/BusinessEdit.vue'));
Vue.component('business-list', require('./components/admin/BusinessList.vue'));
Vue.component('business-chain-edit', require('./components/admin/BusinessChainEdit'));
Vue.component('business-chain-list', require('./components/admin/BusinessChainList'));
Vue.component('business-office-user-list', require('./components/admin/BusinessOfficeUserList'));
Vue.component('business-office-user-modal', require('./components/admin/BusinessOfficeUserModal'));
Vue.component('business-contact-info-tab', require('./components/admin/BusinessContactInfoTab'));
Vue.component('business-caregiver-deposits-missing-bank-account', require('./components/admin/reports/CaregiverDepositsMissingBankAccounts'));
Vue.component('admin-financial-summary', require('./components/admin/reports/FinancialSummary'));
Vue.component('business-sms-settings', require('./components/admin/BusinessSmsSettings.vue'));

// Office User
Vue.component('business-overtime-settings', require('./components/business/settings/OvertimeSettings'));
Vue.component('business-status-alias-manager', require('./components/business/settings/StatusAliasManager'));
Vue.component('business-bank-accounts', require('./components/business/settings/BusinessBankAccounts'));
Vue.component('business-care-match', require('./components/business/BusinessCareMatch'));
Vue.component('business-caregiver-availability-tab', require('./components/business/caregivers/BusinessCaregiverAvailabilityTab'));
Vue.component('business-caregiver-skills-tab', require('./components/business/caregivers/BusinessCaregiverSkillsTab'));
Vue.component('business-caregiver-phone-numbers-tab', require('./components/business/caregivers/BusinessCaregiverPhoneNumbersTab'));
Vue.component('business-client-phone-numbers-tab', require('./components/business/clients/BusinessClientPhoneNumbersTab'));
Vue.component('business-client-care-plans-tab', require('./components/business/clients/ClientCarePlansTab'));
Vue.component('client-medication', require('./components/business/clients/Medication'));
Vue.component('business-client-goals', require('./components/business/clients/ClientGoals'));
Vue.component('business-client-care-details', require('./components/business/clients/ClientCareDetails'));
Vue.component('business-client-addresses-tab', require('./components/business/clients/ClientAddressesTab'));
Vue.component('business-caregiver-clients-tab', require('./components/business/caregivers/CaregiverClientsTab'));
Vue.component('business-caregiver-office-locations-tab', require('./components/business/caregivers/CaregiverOfficeLocationsTab'));
Vue.component('business-caregiver-expirations-report', require('./components/BusinessCaregiverExpirationsReport.vue'));
Vue.component('cc-expiration-report', require('./components/business/reports/CreditCardExpirationReport'));
Vue.component('caregivers-missing-bank-accounts', require('./components/business/reports/CaregiversMissingBankAccounts'));
Vue.component('business-clients-missing-payment-methods-report', require('./components/business/reports/ClientsMissingPaymentMethods'));
Vue.component('business-client-caregivers-report', require('./components/BusinessClientCaregiversReport.vue'));
//Vue.component('business-client-service-orders', require('./components/business/ClientServiceOrders.vue'));
Vue.component('business-deposit-history', require('./components/BusinessDepositHistory.vue'));
Vue.component('business-notification', require('./components/BusinessNotification.vue'));
Vue.component('business-notification-list', require('./components/BusinessNotificationList.vue'));
Vue.component('business-medicaid-report', require('./components/business/reports/MedicaidReport.vue'));
Vue.component('business-payment-history', require('./components/BusinessPaymentHistory.vue'));
Vue.component('business-printable-schedules', require('./components/business/reports/PrintableSchedules'));
Vue.component('business-overtime-report', require('./components/BusinessOvertimeReport.vue'));
Vue.component('business-rate-codes', require('./components/business/rate_codes/BusinessRateCodes'));
Vue.component('business-reconciliation-report', require('./components/BusinessReconciliationReport'));
Vue.component('business-shift', require('./components/BusinessShift.vue'));
Vue.component('shift-evv-data-table', require('./components/shifts/EvvDataTable'));
Vue.component('business-shift-report', require('./components/BusinessShiftReport.vue'));
Vue.component('business-scheduled-payments', require('./components/business/reports/ScheduledPaymentsReport.vue'));
// Vue.component('business-scheduled-vs-actual', require('./components/BusinessScheduledVsActual.vue'));
// Vue.component('business-convert-schedule-modal', require('./components/BusinessConvertScheduleModal.vue'));
Vue.component('business-schedule', require('./components/business/schedule/BusinessSchedule.vue'));
Vue.component('business-schedule-modal', require('./components/business/schedule/BusinessScheduleModal'));
Vue.component('business-settings', require('./components/business/BusinessSettings.vue'));
Vue.component('deactivation-reason-manager', require('./components/business/modals/DeactivationReasonManager'));
Vue.component('custom-field-form', require('./components/business/custom_fields/CustomFieldForm.vue'));
Vue.component('custom-field-list', require('./components/business/custom_fields/CustomFieldList'));
Vue.component('custom-field-edit', require('./components/business/custom_fields/CustomFieldEdit.vue'));
Vue.component('business-transaction', require('./components/BusinessTransaction.vue'));
Vue.component('itemized-payment', require('./components/business/reports/ItemizedPayment.vue'));
Vue.component('itemized-deposit', require('./components/business/reports/ItemizedDeposit.vue'));
Vue.component('business-caregiver-misc-tab', require('./components/business/caregivers/CaregiverMiscTab'));
Vue.component('business-caregiver-restrictions-tab', require('./components/business/caregivers/BusinessCaregiverRestrictionsTab'));
Vue.component('business-export-timesheets', require('./components/business/reports/ExportTimesheets'));
Vue.component('business-franchisor-dashboard', require('./components/business/franchise/FranchisorDashboard.vue'));
Vue.component('business-franchisees', require('./components/business/franchise/Franchisees.vue'));
Vue.component('business-franchise-reports', require('./components/business/franchise/FranchiseReports.vue'));
Vue.component('business-franchise-payments', require('./components/business/franchise/FranchisePayments.vue'));
Vue.component('bulk-update-schedule-modal', require('./components/business/schedule/BulkUpdateScheduleModal'));
Vue.component('bulk-delete-schedule-modal', require('./components/business/schedule/BulkDeleteScheduleModal'));
Vue.component('schedule-notes-modal', require('./components/business/schedule/ScheduleNotesModal'));
Vue.component('schedule-clock-out-modal', require('./components/business/schedule/ScheduleClockOutModal'));
Vue.component('business-caregiver-pay-statements', require('./components/business/caregivers/CaregiverPayStatementsTab'));
Vue.component('business-client-caregiver-visits-report', require('./components/admin/reports/ClientCaregiverVisitsReport'));
Vue.component('caregiver-application-edit', require('./components/caregivers/CaregiverApplicationEdit'));
Vue.component('case-manager-report', require('./components/business/reports/CaseManager'));
Vue.component('ltci-claims-report', require('./components/business/reports/LtciClaimsReport'));
Vue.component('referral-sources-report', require('./components/business/reports/ReferralSources'));
Vue.component('prospects-report', require('./components/business/reports/Prospects'));
Vue.component('shift-summary-report', require('./components/business/reports/ShiftSummary'));
Vue.component('onboard-status-report', require('./components/business/reports/OnboardStatus'));
Vue.component('business-evv-report', require('./components/business/reports/Evv'));
Vue.component('contacts-report', require('./components/business/reports/Contacts'));
Vue.component('business-payroll-report', require('./components/business/reports/PayrollReport'));
Vue.component('revenue-report', require('./components/business/reports/Revenue'));
Vue.component('sales-pipeline-report', require('./components/business/reports/SalesPipeline'));
Vue.component('client-directory', require('./components/business/reports/ClientDirectory'));
Vue.component('caregiver-directory', require('./components/business/reports/CaregiverDirectory'));
Vue.component('prospect-directory', require('./components/business/reports/ProspectDirectory'));
Vue.component('user-birthday-report', require('./components/business/reports/UserBirthday'));
Vue.component('caregiver-anniversary-report', require('./components/business/reports/CaregiverAnniversary'));
Vue.component('report-column-picker', require('./components/business/reports/ReportColumnPicker'));
Vue.component('client-stats', require('./components/business/reports/ClientStats'));
Vue.component('caregiver-stats', require('./components/business/reports/CaregiverStats'));
Vue.component('projected-billing-report', require('./components/business/reports/ProjectedBillingReport'));
Vue.component('business-payroll-export-report', require('./components/business/reports/BusinessPayrollExportReport'));
Vue.component('business-medicaid-billing-report', require('./components/business/reports/BusinessMedicaidBillingReport'));
Vue.component('business-payer-list', require('./components/business/PayerList'));
Vue.component('business-payer-modal', require('./components/business/PayerModal'));
Vue.component('business-payer-rates-table', require('./components/business/PayerRatesTable'));
Vue.component('business-salesperson-list', require('./components/business/sales_people/SalesPersonList.vue'));
Vue.component('business-claims-ar', require('./components/business/BusinessClaimsAr'));
Vue.component('business-disaster-plan-report', require('./components/business/reports/BusinessDisasterPlanReport'));
Vue.component('business-communications-tab', require('./components/business/BusinessCommunicationsTab'));
Vue.component('business-offline-invoice-ar', require('./components/business/BusinessOfflineInvoiceAr'));
Vue.component('business-offline-ar-aging-report', require('./components/business/reports/BusinessOfflineArAgingReport'));
Vue.component('business-claims-ar-aging-report', require('./components/business/reports/BusinessClaimsArAgingReport'));
Vue.component('sales-people-commission-report', require('./components/business/reports/SalespersonCommissionReport'));
Vue.component('business-account-setup-report', require('./components/business/reports/BusinessAccountSetupReport'));
Vue.component('business-service-auth-ending-report', require('./components/business/reports/BusinessServiceAuthEndingReport'));


Vue.component('caregiver-create', require('./components/CaregiverCreate.vue'));
Vue.component('caregiver-edit', require('./components/CaregiverEdit.vue'));
Vue.component('caregiver-client-list', require('./components/caregivers/CaregiverClientList'));
Vue.component('client-narrative', require('./components/ClientNarrative'));
Vue.component('caregiver-license-list', require('./components/CaregiverLicenseList.vue'));
Vue.component('caregiver-license-modal', require('./components/CaregiverLicenseModal.vue'));
Vue.component('caregiver-list', require('./components/CaregiverList.vue'));
Vue.component('caregiver-schedule', require('./components/CaregiverSchedule.vue'));
Vue.component('caregiver-payment-details', require('./components/caregivers/CaregiverPaymentDetails'));
Vue.component('caregiver-payment-details', require('./components/caregivers/CaregiverPaymentDetails'));
Vue.component('caregiver-timesheet', require('./components/caregivers/CaregiverTimesheet'));
Vue.component('caregiver-timesheet-list', require('./components/caregivers/CaregiverTimesheetList'));
Vue.component('caregiver-distance-report', require('./components/CaregiverDistanceReport.vue'));

Vue.component('change-password', require('./components/ChangePassword.vue'));

Vue.component('clock-in', require('./components/ClockIn.vue'));
Vue.component('clock-out', require('./components/ClockOut.vue'));
Vue.component('clocked-in', require('./components/ClockedIn.vue'));
Vue.component('adjoining-caregivers-card', require('./components/caregivers/AdjoiningCaregiversCard.vue'));


Vue.component('caregiver-setup-wizard', require('./components/account-setup/CaregiverSetupWizard.vue'));

Vue.component('client-caregiver-list', require('./components/clients/ClientCaregiverList'));
Vue.component('client-create', require('./components/ClientCreate.vue'));
Vue.component('client-edit', require('./components/ClientEdit.vue'));
Vue.component('client-insurance-service-auth', require('./components/business/clients/InsuranceServiceAuthTab'));
Vue.component('client-list', require('./components/ClientList.vue'));
Vue.component('client-misc-tab', require('./components/business/clients/ClientMiscTab'));
Vue.component('client-onboarding-wizard', require('./components/business/clients/onboarding/ClientOnboardingWizard'));
Vue.component('client-payers-tab', require('./components/business/clients/ClientPayersTab'));
Vue.component('client-preferences-tab', require('./components/business/clients/ClientPreferencesTab'));
Vue.component('client-profile', require('./components/ClientProfile.vue'));
Vue.component('client-rates-tab', require('./components/business/clients/ClientRatesTab'));
Vue.component('client-setup-wizard', require('./components/account-setup/ClientSetupWizard.vue'));
Vue.component('clients-without-emails-report', require('./components/business/reports/ClientsWithoutEmailsReport'));


Vue.component('prospect-edit', require('./components/business/prospects/ProspectEdit.vue'));
Vue.component('prospect-list', require('./components/business/prospects/ProspectList.vue'));

Vue.component('contact-edit', require('./components/business/contacts/ContactEdit.vue'));
Vue.component('contact-list', require('./components/business/contacts/ContactList.vue'));

Vue.component('credit-card-form', require('./components/CreditCardForm.vue'));
Vue.component('bank-account-form', require('./components/BankAccountForm.vue'));

Vue.component('dashboard-metric', require('./components/DashboardMetric.vue'));

Vue.component('full-calendar', require('./components/FullCalendar.vue'));

Vue.component('input-help', require('./components/InputHelp.vue'));

Vue.component('message', require('./components/Message.vue'));

Vue.component('payment-method', require('./components/PaymentMethod.vue'));
Vue.component('payment-method-provider', require('./components/PaymentMethodProvider.vue'));

Vue.component('reset-password-modal', require('./components/ResetPasswordModal.vue'));

Vue.component('shift-history', require('./components/ShiftHistory.vue'));

Vue.component('system-notifications', require('./components/SystemNotifications.vue'));
Vue.component('tasks-icon', require('./components/TasksIcon.vue'));

Vue.component('phone-number', require('./components/PhoneNumber.vue'));
Vue.component('user-address', require('./components/UserAddress.vue'));
Vue.component('document-list', require('./components/DocumentList.vue'));
// notes
Vue.component('note-list', require('./components/notes/NoteList'));
Vue.component('notes-tab', require('./components/notes/NotesTab'));
Vue.component('note-form', require('./components/notes/NoteForm'));
Vue.component('note-create', require('./components/notes/NoteCreate'));

Vue.component('note-template-list', require('./components/notes/NoteTemplateList'));
Vue.component('note-template-form', require('./components/notes/NoteTemplateForm'));

// caregiver applications
Vue.component('caregiver-application-create', require('./components/caregivers/CaregiverApplicationCreate'));
Vue.component('caregiver-application-list', require('./components/caregivers/CaregiverApplicationList'));
Vue.component('caregiver-application', require('./components/caregivers/CaregiverApplication'));

Vue.component('mask-input', require('./components/MaskInput'));
Vue.component('caregiver-timesheet', require('./components/caregivers/CaregiverTimesheet'));
Vue.component('business-timesheet', require('./components/BusinessTimesheet'));
Vue.component('microbilt-test', require('./components/admin/MicrobiltTest'));
Vue.component('question-list', require('./components/business/QuestionList'));
Vue.component('question-form', require('./components/business/QuestionForm'));
Vue.component('business-text-caregivers', require('./components/business/TextCaregivers'));
Vue.component('business-sms-thread-list', require('./components/business/SmsThreadList'));
Vue.component('business-sms-thread', require('./components/business/SmsThread'));
Vue.component('business-sms-reply-table', require('./components/business/SmsReplyTable'));
Vue.component('business-task-list', require('./components/business/tasks/TaskList'));
Vue.component('business-task-form', require('./components/business/tasks/TaskForm'));
Vue.component('business-task-details', require('./components/business/tasks/TaskDetails'));
Vue.component('business-service', require('./components/business/Service'));
Vue.component('business-service-modal', require('./components/business/ServiceModal'));
Vue.component('report-list', require('./components/ReportList'));

/* Nacha Ach */
Vue.component('admin-nachaach', require('./components/admin/NachaAch'));
Vue.component('admin-nachaach-batch', require('./components/admin/nacha-ach/Batch'));
Vue.component('admin-nachaach-batch-modal', require('./components/admin/nacha-ach/modal/NewBatch'));
Vue.component('admin-nachaach-entry-details', require('./components/admin/nacha-ach/EntryDetail'));
Vue.component('admin-nachaach-entry-detail-modal', require('./components/admin/nacha-ach/modal/NewEntryDetail'));

/* Quickbooks */
Vue.component('business-quickbooks-settings', require('./components/business/quickbooks/QuickbooksSettings'));
Vue.component('business-quickbooks-connect-settings', require('./components/business/quickbooks/QuickbooksConnectSettings'));
Vue.component('business-quickbooks-general-settings', require('./components/business/quickbooks/QuickbooksGeneralSettings'));
Vue.component('business-quickbooks-client-map-settings', require('./components/business/quickbooks/QuickbooksClientMapSettings'));
// Vue.component('quickbooks-general-mapping', require('./components/business/quickbooks/tabs/GeneralMapping'));
// Vue.component('quickbooks-rate-mapping', require('./components/business/quickbooks/tabs/RateMapping'));
// Vue.component('quickbooks-caregiver-mapping', require('./components/business/quickbooks/tabs/CaregiverMapping'));
Vue.component('business-quickbooks-queue', require('./components/business/quickbooks/QuickbooksInvoiceQueue'));

Vue.component('business-apply-payment', require('./components/business/accounting/ApplyPayment'));
Vue.component('business-referral-source-manager', require('./components/business/referral/ReferralSourceManager'));
Vue.component('business-referral-source-modal', require('./components/business/referral/ReferralSourceModal'));
Vue.component('business-referral-source', require('./components/business/referral/ReferralSource'));
Vue.component('business-referral-source-select', require('./components/business/referral/ReferralSourceSelect'));

/* Payroll Policy */
Vue.component('payroll-policy', require('./components/business/tabs/PayrollPolicy'));

Vue.component('knowledge-manager', require('./components/knowledge/KnowledgeManager'));
Vue.component('knowledge-editor', require('./components/knowledge/KnowledgeEditor'));
Vue.component('knowledge-item', require('./components/knowledge/KnowledgeItem'));
Vue.component('knowledge-base', require('./components/knowledge/KnowledgeBase'));

Vue.component('chain-expirations-autocomplete', require("./components/business/chains/ExpirationTypesAutocomplete"));
Vue.component('default-expirations-manager', require("./components/business/chains/DefaultExpirationsManager"));

Vue.filter('date', value => {
    return moment.utc(value).local().format('L');
});

Vue.filter('datetime', value => {
    return moment.utc(value).local().format('L LT');
});

Vue.filter('capitalize', value => {
    if (! value && value !== 0) {
        return '';
    }

    return value.toString().charAt(0).toUpperCase() + value.slice(1);
});

Vue.filter('uppercase', value => {
    return value.toString().toUpperCase();
});

Vue.filter('lowercase', value => {
    return value.toString().toLowerCase();
});

Vue.filter('nl2br', value => {
    return value.toString().replace(/(?:\r\n|\r|\n)/g, '<br />');
});

Vue.directive('tooltip', function(el, binding){
    $(el).tooltip({
        title: binding.value,
        placement: binding.arg,
        trigger: 'hover'
    })
});

const app = new Vue({
    el: '#main-wrapper',
    store,
});
require('./alerts');
