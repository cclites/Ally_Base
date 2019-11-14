import Vue from 'vue'
import Vuex from 'vuex'
import business from './modules/business'
import paymentMethod from './modules/paymentMethod'
import notifications from './modules/notifications';
import tasks from './modules/tasks';
import quickbooks from './modules/quickbooks';
import claims from './modules/claims';
import filters from './modules/filters';
import openShiftRequests from './modules/openShiftRequests';

Vue.use(Vuex);

const debug = process.env.NODE_ENV !== 'production';

export default new Vuex.Store({
    modules: {

        openShiftRequests,
        business,
        paymentMethod,
        notifications,
        tasks,
        quickbooks,
        claims,
        filters,
    },
    strict: debug,
})