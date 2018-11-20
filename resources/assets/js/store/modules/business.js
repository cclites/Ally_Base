// initial state
const state = {
    businesses: []
}

// getters
const getters = {
    defaultBusiness(state) {
        return (state.businesses.length === 1) ? state.businesses[0] : {};
    }
}

// actions
const actions = {}

// mutations
const mutations = {
    setBusinesses (state, businesses) {
        state.businesses = businesses
    },

    addBusiness(state, business) {

    },

    removeBusiness(state, business) {

    },
}

export default {
    state,
    getters,
    actions,
    mutations
}