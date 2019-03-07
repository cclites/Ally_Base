import Vue from 'vue'
import Vuex from 'vuex'
import business from './modules/business'
import paymentMethod from './modules/paymentMethod'
import notifications from './modules/notifications';

Vue.use(Vuex);

const debug = process.env.NODE_ENV !== 'production';

export default new Vuex.Store({
    modules: {
        business,
        paymentMethod,
        notifications,
    },
    strict: debug,
})