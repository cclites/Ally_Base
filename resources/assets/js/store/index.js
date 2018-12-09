import Vue from 'vue'
import Vuex from 'vuex'
import business from './modules/business'
import paymentMethod from './modules/paymentMethod'

Vue.use(Vuex);

const debug = process.env.NODE_ENV !== 'production';

export default new Vuex.Store({
    modules: {
        business,
        paymentMethod
    },
    strict: debug,
})