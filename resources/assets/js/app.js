
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
Vue.component('activity-list', require('./components/ActivityList.vue'));

Vue.component('business-deposit-history', require('./components/BusinessDepositHistory.vue'));
Vue.component('business-medicaid-report-caregivers', require('./components/BusinessMedicaidReportCaregivers.vue'));
Vue.component('business-payment-history', require('./components/BusinessPaymentHistory.vue'));
Vue.component('business-overtime-report', require('./components/BusinessOvertimeReport.vue'));
Vue.component('business-shift-history', require('./components/BusinessShiftHistory.vue'));
Vue.component('business-scheduled-payments', require('./components/BusinessScheduledPayments.vue'));
Vue.component('business-schedule', require('./components/BusinessSchedule.vue'));

Vue.component('caregiver-create', require('./components/CaregiverCreate.vue'));
Vue.component('caregiver-edit', require('./components/CaregiverEdit.vue'));
Vue.component('caregiver-list', require('./components/CaregiverList.vue'));
Vue.component('caregiver-schedule', require('./components/CaregiverSchedule.vue'));

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

Vue.component('shift-history', require('./components/ShiftHistory.vue'));

Vue.component('phone-number', require('./components/PhoneNumber.vue'));
Vue.component('user-address', require('./components/UserAddress.vue'));
Vue.component('document-list', require('./components/DocumentList.vue'));

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
    el: '#main-wrapper'
});
require('./alerts');
