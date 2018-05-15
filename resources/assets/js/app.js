
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');
require('./ie-fix');
require('./custom');
require('./sidebarmenu');

import Form from './classes/Form';
window.Form = Form;
import Countries from './classes/Countries';
window.Countries = Countries;

window.Vue = require('vue');

// Vue Third Party Components

import BootstrapVue from 'bootstrap-vue';
Vue.use(BootstrapVue);

import VueTheMask from 'vue-the-mask';
Vue.use(VueTheMask);

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

// App Components
Vue.component('date-picker', require('./components/DatePicker.vue'));
Vue.component('time-picker', require('./components/TimePicker.vue'));
Vue.component('activity-list', require('./components/ActivityList.vue'));
Vue.component('emergency-contacts-tab', require('./components/users/EmergencyContactsTab'));
Vue.component('loading-card', require('./components/LoadingCard.vue'));
Vue.component('signature-pad', require('./components/SignaturePad'));
Vue.component('select2', require('./components/Select2'));
Vue.component('submit-button', require('./components/SubmitButton'));
Vue.component('quick-search', require('./components/QuickSearch'));


// Client
Vue.component('ltc-shift-approval', require('./components/clients/LtcShiftApproval'));
Vue.component('client-payment-history', require('./components/clients/ClientPaymentHistory'));
Vue.component('client-payment-details', require('./components/clients/ClientPaymentDetails'));
Vue.component('client-payment-details-print', require('./components/clients/ClientPaymentDetailsPrint'));
Vue.component('client-phone-numbers-tab', require('./components/clients/profile/ClientPhoneNumbersTab'));


// Caregiver
Vue.component('caregiver-phone-numbers-tab', require('./components/caregivers/profile/CaregiverPhoneNumbersTab'));

// Admin
Vue.component('admin-business-select', require('./components/admin/AdminBusinessSelect'));
Vue.component('admin-import', require('./components/admin/import/AdminImport'));
Vue.component('admin-import-report', require('./components/admin/import/AdminImportReport'));
Vue.component('admin-bucket-report', require('./components/admin/AdminBucketReport'));
Vue.component('admin-charges-report', require('./components/admin/AdminChargesReport.vue'));
Vue.component('admin-deposit-report', require('./components/admin/AdminDepositReport.vue'));
Vue.component('admin-evv-report', require('./components/admin/AdminEvvReport'));
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
Vue.component('admin-pending-charges', require('./components/admin/AdminPendingCharges.vue'));
Vue.component('admin-pending-deposits', require('./components/admin/AdminPendingDeposits.vue'));
Vue.component('admin-pending-shifts', require('./components/admin/AdminPendingShifts.vue'));
Vue.component('admin-reconciliation-report', require('./components/admin/AdminReconciliationReport'));
Vue.component('admin-transaction', require('./components/admin/AdminTransaction.vue'));
Vue.component('admin-transactions-report', require('./components/admin/AdminTransactionsReport.vue'));
Vue.component('admin-active-clients-report', require('./components/admin/reports/ActiveClientsReport.vue'));
Vue.component('authorized-payment-checkbox', require('./components/admin/AuthorizePaymentCheckbox.vue'));
Vue.component('charge-payment-button', require('./components/admin/ChargePaymentButton.vue'));
Vue.component('business-create', require('./components/BusinessCreate.vue'));
Vue.component('business-edit', require('./components/BusinessEdit.vue'));
Vue.component('business-list', require('./components/BusinessList.vue'));
Vue.component('business-office-user-list', require('./components/admin/BusinessOfficeUserList'));
Vue.component('business-office-user-modal', require('./components/admin/BusinessOfficeUserModal'));
Vue.component('business-contact-info-tab', require('./components/admin/BusinessContactInfoTab'));
Vue.component('business-caregiver-deposits-missing-bank-account', require('./components/admin/reports/CaregiverDepositsMissingBankAccounts'));
Vue.component('admin-financial-summary', require('./components/admin/reports/FinancialSummary'));

// Office User
Vue.component('business-caregiver-preferences-tab', require('./components/business/caregivers/BusinessCaregiverPreferencesTab'));
Vue.component('business-caregiver-phone-numbers-tab', require('./components/business/caregivers/BusinessCaregiverPhoneNumbersTab'));
Vue.component('business-client-phone-numbers-tab', require('./components/business/clients/BusinessClientPhoneNumbersTab'));
Vue.component('business-client-care-plans-tab', require('./components/business/clients/ClientCarePlansTab'));
Vue.component('business-client-addresses-tab', require('./components/business/clients/ClientAddressesTab'));
Vue.component('business-certification-expirations', require('./components/BusinessCertificationExpirations.vue'));
Vue.component('cc-expiration-report', require('./components/business/reports/CreditCardExpirationReport'));
Vue.component('caregivers-missing-bank-accounts', require('./components/business/reports/CaregiversMissingBankAccounts'));
Vue.component('business-client-caregivers-report', require('./components/BusinessClientCaregiversReport.vue'));
Vue.component('business-client-service-orders', require('./components/business/ClientServiceOrders.vue'));
Vue.component('business-deposit-history', require('./components/BusinessDepositHistory.vue'));
Vue.component('business-exception', require('./components/BusinessException.vue'));
Vue.component('business-exception-list', require('./components/BusinessExceptionList.vue'));
Vue.component('business-issue-modal', require('./components/BusinessIssueModal.vue'));
Vue.component('business-medicaid-report', require('./components/business/reports/MedicaidReport.vue'));
Vue.component('business-payment-history', require('./components/BusinessPaymentHistory.vue'));
Vue.component('business-overtime-report', require('./components/BusinessOvertimeReport.vue'));
Vue.component('business-reconciliation-report', require('./components/BusinessReconciliationReport'));
Vue.component('business-shift', require('./components/BusinessShift.vue'));
Vue.component('business-shift-history', require('./components/BusinessShiftHistory.vue'));
Vue.component('business-shift-report', require('./components/BusinessShiftReport.vue'));
Vue.component('business-scheduled-payments', require('./components/business/reports/ScheduledPaymentsReport.vue'));
// Vue.component('business-scheduled-vs-actual', require('./components/BusinessScheduledVsActual.vue'));
// Vue.component('business-convert-schedule-modal', require('./components/BusinessConvertScheduleModal.vue'));
Vue.component('business-schedule', require('./components/business/schedule/BusinessSchedule.vue'));
Vue.component('business-schedule-modal', require('./components/business/schedule/BusinessScheduleModal'));
Vue.component('business-settings', require('./components/business/BusinessSettings.vue'));
Vue.component('business-transaction', require('./components/BusinessTransaction.vue'));
Vue.component('business-clients-onboarded', require('./components/business/reports/ClientOnboarded'));
Vue.component('business-caregivers-onboarded', require('./components/business/reports/CaregiverOnboarded'));
Vue.component('business-caregiver-misc-tab', require('./components/business/caregivers/CaregiverMiscTab'));
Vue.component('business-export-timesheets', require('./components/business/reports/ExportTimesheets'));
Vue.component('bulk-edit-schedule-modal', require('./components/business/schedule/BulkEditScheduleModal'));
Vue.component('bulk-delete-schedule-modal', require('./components/business/schedule/BulkDeleteScheduleModal'));
Vue.component('schedule-notes-modal', require('./components/business/schedule/ScheduleNotesModal'));
Vue.component('business-caregiver-payment-history', require('./components/business/caregivers/CaregiverPaymentHistoryTab'));
Vue.component('business-caregiver-pay-statements', require('./components/business/caregivers/CaregiverPayStatementsTab'));
Vue.component('business-client-caregiver-visits-report', require('./components/admin/reports/ClientCaregiverVisitsReport'));
Vue.component('caregiver-application-edit', require('./components/caregivers/CaregiverApplicationEdit'));
Vue.component('ltci-claims-report', require('./components/business/reports/LtciClaimsReport'));

Vue.component('notes-tab', require('./components/notes/NotesTab'));

Vue.component('caregiver-create', require('./components/CaregiverCreate.vue'));
Vue.component('caregiver-edit', require('./components/CaregiverEdit.vue'));
Vue.component('caregiver-license-list', require('./components/CaregiverLicenseList.vue'));
Vue.component('caregiver-license-modal', require('./components/CaregiverLicenseModal.vue'));
Vue.component('caregiver-list', require('./components/CaregiverList.vue'));
Vue.component('caregiver-schedule', require('./components/CaregiverSchedule.vue'));
Vue.component('caregiver-payment-history', require('./components/caregivers/CaregiverPaymentHistory'));
Vue.component('caregiver-payment-details', require('./components/caregivers/CaregiverPaymentDetails'));

Vue.component('caregiver-distance-report', require('./components/CaregiverDistanceReport.vue'));

Vue.component('change-password', require('./components/ChangePassword.vue'));

Vue.component('clock-in', require('./components/ClockIn.vue'));
Vue.component('clock-out', require('./components/ClockOut.vue'));

Vue.component('caregiver-confirmation', require('./components/CaregiverConfirmation.vue'));
Vue.component('client-confirmation', require('./components/ClientConfirmation.vue'));

Vue.component('business-client-caregivers', require('./components/business/clients/ClientCaregivers.vue'));
Vue.component('client-create', require('./components/ClientCreate.vue'));
Vue.component('client-edit', require('./components/ClientEdit.vue'));
Vue.component('client-list', require('./components/ClientList.vue'));
Vue.component('client-profile', require('./components/ClientProfile.vue'));
Vue.component('client-statements-tab', require('./components/business/clients/ClientStatementsTab'));
Vue.component('client-addresses-tab', require('./components/business/clients/ClientStatementsTab'));
Vue.component('clients-without-emails-report', require('./components/business/reports/ClientsWithoutEmailsReport'));
Vue.component('client-ltc-insurance', require('./components/business/clients/LTCInsuranceTab'));

Vue.component('credit-card-form', require('./components/CreditCardForm.vue'));
Vue.component('bank-account-form', require('./components/BankAccountForm.vue'));

Vue.component('dashboard-metric', require('./components/DashboardMetric.vue'));

Vue.component('full-calendar', require('./components/FullCalendar.vue'));

Vue.component('input-help', require('./components/InputHelp.vue'));

Vue.component('message', require('./components/Message.vue'));

Vue.component('payment-method', require('./components/PaymentMethod.vue'));
Vue.component('payment-method-provider', require('./components/PaymentMethodProvider.vue'));

Vue.component('reset-password-modal', require('./components/ResetPasswordModal.vue'));
Vue.component('send-welcome-email-modal', require('./components/SendWelcomeEmailModal.vue'));

Vue.component('shift-history', require('./components/ShiftHistory.vue'));
Vue.component('shift-history', require('./components/ShiftHistory.vue'));

Vue.component('system-notifications', require('./components/SystemNotifications.vue'));

Vue.component('phone-number', require('./components/PhoneNumber.vue'));
Vue.component('user-address', require('./components/UserAddress.vue'));
Vue.component('document-list', require('./components/DocumentList.vue'));
// notes
Vue.component('note-create', require('./components/notes/NoteCreate'));
Vue.component('note-list', require('./components/notes/NoteList'));
Vue.component('note-edit', require('./components/notes/NoteEdit'));
// caregiver applications
Vue.component('caregiver-application-create', require('./components/caregivers/CaregiverApplicationCreate'));
Vue.component('caregiver-application-list', require('./components/caregivers/CaregiverApplicationList'));
Vue.component('caregiver-application', require('./components/caregivers/CaregiverApplication'));

Vue.component('mask-input', require('./components/MaskInput'));
Vue.component('microbilt-test', require('./components/admin/MicrobiltTest'));

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
});
require('./alerts');
