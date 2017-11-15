
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');
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

// import vSelect from "vue-select"
// Vue.component('v-select', vSelect);

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

// App Components
Vue.component('date-picker', require('./components/DatePicker.vue'));
Vue.component('time-picker', require('./components/TimePicker.vue'));

Vue.component('activity-list', require('./components/ActivityList.vue'));


// Client
Vue.component('ltc-shift-approval', require('./components/clients/LtcShiftApproval'));
Vue.component('client-payment-history', require('./components/clients/ClientPaymentHistory'));
Vue.component('client-payment-details', require('./components/clients/ClientPaymentDetails'));
Vue.component('client-payment-details-print', require('./components/clients/ClientPaymentDetailsPrint'));

// Admin
Vue.component('admin-user-list', require('./components/admin/AdminUserList.vue'));
Vue.component('admin-pending-charges', require('./components/admin/AdminPendingCharges.vue'));
Vue.component('admin-pending-shifts', require('./components/admin/AdminPendingShifts.vue'));
Vue.component('authorized-payment-checkbox', require('./components/admin/AuthorizePaymentCheckbox.vue'));
Vue.component('charge-payment-button', require('./components/admin/ChargePaymentButton.vue'));
Vue.component('business-create', require('./components/BusinessCreate.vue'));
Vue.component('business-edit', require('./components/BusinessEdit.vue'));
Vue.component('business-list', require('./components/BusinessList.vue'));

// Office User
Vue.component('business-certification-expirations', require('./components/BusinessCertificationExpirations.vue'));
Vue.component('business-client-caregivers-report', require('./components/BusinessClientCaregiversReport.vue'));
Vue.component('business-deposit-history', require('./components/BusinessDepositHistory.vue'));
Vue.component('business-exception', require('./components/BusinessException.vue'));
Vue.component('business-exception-list', require('./components/BusinessExceptionList.vue'));
Vue.component('business-issue-modal', require('./components/BusinessIssueModal.vue'));
Vue.component('business-medicaid-report-caregivers', require('./components/BusinessMedicaidReportCaregivers.vue'));
Vue.component('business-payment-history', require('./components/BusinessPaymentHistory.vue'));
Vue.component('business-overtime-report', require('./components/BusinessOvertimeReport.vue'));
Vue.component('business-shift', require('./components/BusinessShift.vue'));
Vue.component('business-shift-history', require('./components/BusinessShiftHistory.vue'));
Vue.component('business-shift-report', require('./components/BusinessShiftReport.vue'));
Vue.component('business-scheduled-payments', require('./components/BusinessScheduledPayments.vue'));
Vue.component('business-scheduled-vs-actual', require('./components/BusinessScheduledVsActual.vue'));
Vue.component('business-schedule', require('./components/BusinessSchedule.vue'));
Vue.component('business-settings', require('./components/business/BusinessSettings.vue'));
Vue.component('notes-tab', require('./components/notes/NotesTab'));
Vue.component('clients-without-emails-report', require('./components/business/reports/ClientsWithoutEmailsReport'));

Vue.component('care-plan-edit', require('./components/CarePlanEdit.vue'));
Vue.component('care-plan-list', require('./components/CarePlanList.vue'));

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

Vue.component('client-confirmation', require('./components/ClientConfirmation.vue'));

Vue.component('client-caregivers', require('./components/ClientCaregivers.vue'));
Vue.component('client-create', require('./components/ClientCreate.vue'));
Vue.component('client-edit', require('./components/ClientEdit.vue'));
Vue.component('client-list', require('./components/ClientList.vue'));
Vue.component('client-profile', require('./components/ClientProfile.vue'));
Vue.component('client-schedule', require('./components/ClientSchedule.vue'));

Vue.component('create-schedule-modal', require('./components/CreateScheduleModal.vue'));
Vue.component('edit-schedule-modal', require('./components/EditScheduleModal.vue'));

Vue.component('credit-card-form', require('./components/CreditCardForm.vue'));
Vue.component('bank-account-form', require('./components/BankAccountForm.vue'));

Vue.component('dashboard-metric', require('./components/DashboardMetric.vue'));

Vue.component('full-calendar', require('./components/FullCalendar.vue'));

Vue.component('input-help', require('./components/InputHelp.vue'));

Vue.component('message', require('./components/Message.vue'));

Vue.component('payment-method', require('./components/PaymentMethod.vue'));

Vue.component('reset-password-modal', require('./components/ResetPasswordModal.vue'));

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


const app = new Vue({
    el: '#main-wrapper',
});
require('./alerts');