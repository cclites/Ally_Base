import * as Vue from "vue";

const state = {
    queue: [],
    claim: {},
    claims: [],
    item: {},
    caregivers: [],
    services: [],

    remits: [],
    remit: {},
    remitClaimList: [],
};

// getters
const getters = {
    queue(state) {
        return state.queue;
    },
    claims(state) {
        return state.claims;
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
    serviceList(state) {
        return state.services ? state.services : [];
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
    setClaims(state, claims) {
        Vue.set(state, 'claims', claims);
    },
    setItem(state, item) {
        Vue.set(state, 'item', item);
    },
    setCaregiverList(state, data) {
        Vue.set(state, 'caregivers', data);
    },
    setServiceList(state, data) {
        Vue.set(state, 'services', data);
    },
    setRemits(state, data) {
        Vue.set(state, 'remits', data);
    },
    deleteRemit(state, remitId) {
        let index = state.remits.findIndex(x => x.id == remitId);
        if (index >= 0) {
            state.remits.splice(index, 1);
        }
        // Vue.set(state, 'remits', data);
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
    async fetchServiceList({commit, state}) {
        await axios.get(`/business/dropdown/services`)
            .then( ({ data }) => {
                commit('setServiceList', data);
            })
            .catch(() => {
                commit('setServiceList', []);
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
