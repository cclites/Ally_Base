import * as Vue from "vue";

const state = {
    queue: [],
    claim: {},
    item: {},
    caregivers: [],

    remits: [],
    remit: {},
    remitClaimList: [],
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
    },
    caregiverList(state) {
        return state.caregivers ? state.caregivers : [];
    },
    remits(state) {
        return state.remits ? state.remits : [];
    },
    remit(state) {
        return state.remit ? state.remit : {};
    },
    remitClaimList(state) {
        return state.remitClaimList ? state.remitClaimList : [];
    },
};

// mutations
const mutations = {
    setClaim(state, claim) {
        Vue.set(state, 'claim', claim);
    },
    setItem(state, item) {
        Vue.set(state, 'item', item);
    },
    setCaregiverList(state, data) {
        Vue.set(state, 'caregivers', data);
    },
    setRemits(state, data) {
        Vue.set(state, 'remits', data);
    },
    setRemit(state, data) {
        Vue.set(state, 'remit', data);
    },
    setRemitClaimList(state, data) {
        Vue.set(state, 'remitClaimList', data);
    },
};

// actions
const actions = {
    async fetchCaregiverList({commit, state}) {
        await axios.get(`/business/dropdown/caregivers?business=${state.claim.business_id}&active=all`)
            .then( ({ data }) => {
                commit('setCaregiverList', data);
            })
            .catch(() => {
                commit('setCaregiverList', []);
            });
    },
};

export default {
    state,
    getters,
    actions,
    mutations,
    namespaced: true,
}
