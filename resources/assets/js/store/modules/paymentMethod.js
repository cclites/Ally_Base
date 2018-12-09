import * as Vue from "vue";

const state = {
    payment: {}
}

// getters
const getters = {
    getPaymentMethodDetail(state) {
        return () => state.payment;
    }
}

// actions
const actions = {}

// mutations
const mutations = {
    setPaymentMethodDetail (state, payment) {
        state.payment = payment;
    },
}

export default {
    state,
    getters,
    actions,
    mutations
}