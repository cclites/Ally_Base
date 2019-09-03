import * as Vue from "vue";

const state = {
    queue: [],
    claim: {},
    item: {},
};

// getters
const getters = {
    queue(state) {
        return state.queue;
    },
    claim(state) {
        return state.claim;
    },
    item(state) {
        return state.item;
    },
    claimItems(state) {
        return state.claim ? state.claim.items : [];
    }
};

// mutations
const mutations = {
    setClaim(state, claim) {
        Vue.set(state, 'claim', claim);
    },

    setItem(state, item) {
        Vue.set(state, 'item', item);
    }
};

// actions
const actions = {
    // async fetchConfig({commit}, businessId) {
    //     await axios.get(`/business/quickbooks/${businessId}/config`)
    //         .then( ({ data }) => {
    //             commit('setConfig', data ? data.data : []);
    //         })
    //         .catch(() => {});
    // },
};

export default {
    state,
    getters,
    actions,
    mutations,
    namespaced: true,
}
