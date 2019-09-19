import * as Vue from "vue";

const state = {
    payers: [],
    loading: [],
    loaded: [],
};

// getters
const getters = {
    payerList(state) {
        return state.payers;
    },
    isPayersLoading(state) {
        return state.loading.includes('payers');
    },
    isPayersLoaded(state) {
        return state.loaded.includes('payers');
    },
};

// mutations
const mutations = {
    setPayerList(state, data) {
        Vue.set(state, 'payers', data);
    },
    setLoaded(state, filter) {
        if (state.loaded.includes(filter)) {
            return;
        }

        let x = state.loaded;
        x.push(filter);
        Vue.set(state, 'loaded', x);
    },
    setLoading(state, filter) {
        if (state.loading.includes(filter)) {
            return;
        }

        let x = state.loading;
        x.push(filter);
        Vue.set(state, 'loading', x);
    },
    doneLoading(state, filter) {
        let index = state.loading.findIndex(x => x == filter);
        if (index >= 0) {
            state.loading.splice(index, 1);
        }
    },
};

// actions
const actions = {
    async fetchPayers({commit, getters}) {
        if (getters.isPayersLoaded || getters.isPayersLoading) {
            return;
        }

        commit('setLoading', 'payers');
        await axios.get(`/business/dropdown/payers`)
            .then( ({ data }) => {
                commit('setPayerList', data);
            })
            .catch(() => {
                commit('setPayerList', []);
            })
            .finally(() => {
                commit('setLoaded', 'payers');
                commit('doneLoading', 'payers');
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
