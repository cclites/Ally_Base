
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

window.moment = require('moment');
window.Vue = require('vue');

import BootstrapVue from 'bootstrap-vue'
Vue.use(BootstrapVue);

import vSelect from "vue-select"
Vue.component('v-select', vSelect);

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

// App Components
Vue.component('client-profile', require('./components/ClientProfile.vue'));
Vue.component('change-password', require('./components/ChangePassword.vue'));
Vue.component('input-help', require('./components/InputHelp.vue'));
Vue.component('message', require('./components/Message.vue'));
Vue.component('user-address', require('./components/UserAddress.vue'));

Vue.filter('date', value => {
    return moment.utc(value).local().format('L')
});

Vue.filter('datetime', value => {
    return moment.utc(value).local().format('L LT');
});

Vue.filter('capitalize', value => {
    if (! value && value !== 0) {
        return '';
    }

    return value.toString().charAt(0).toUpperCase()
        + value.slice(1);
});

Vue.filter('nl2br', value => {
    return value.toString().replace(/(?:\r\n|\r|\n)/g, '<br />');
});

const app = new Vue({
    el: '#main-wrapper'
});
require('./alerts');
