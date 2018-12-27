import * as Vue from "vue";

const state = {
    businesses: [],
}

// getters
const getters = {
    defaultBusiness(state) {
        return (state.businesses.length === 1) ? state.businesses[0] : {};
    },
    getBusiness(state) {
        return id =>  getBusinessFromState(state, id);  // use as getBusiness(id)
    }
}

// actions
const actions = {}

// mutations
const mutations = {
    setBusinesses (state, businesses) {
        state.businesses = businesses
    },

    updateBusiness(state, business) {
        const index = state.businesses.findIndex(item => item.id == business.id);
        if (index !== -1) {
            Vue.set(state.businesses, index, business);
        }
        else {
            state.businesses.push(business);
        }
    },

    removeBusiness(state, business) {

    },
}

const getBusinessFromState = (state, id) => state.businesses.find(business => business.id == id) || {};

export default {
    state,
    getters,
    actions,
    mutations
}