import * as Vue from "vue";

const state = {
    services: [],
    config: {},
};

// getters
const getters = {
    services(state) {
        return state.services;
    },
    config(state) {
        return state.config;
    },
    businessId(state) {
        return state.config.business_id;
    },
    isAuthorized(state) {
        return !!state.config.access_token;
    },
    mapServiceFromShifts(state) {
        return state.config.shift_service == null;
    }
};

// mutations
const mutations = {
    setServices(state, services) {
        Vue.set(state, 'services', services);
    },
    setConfig(state, config) {
        Vue.set(state, 'config', config);
    }
};

// actions
const actions = {
    async fetchConfig({commit}, businessId) {
        await axios.get(`quickbooks/${businessId}/config`)
            .then( ({ data }) => {
                commit('setConfig', data ? data.data : []);
            })
            .catch(() => {});
    },
    async fetchServices({commit, state}) {
        if (! state.config.business_id) {
            // cannot run until config is fetched
            return;
        }

        await axios.get(`quickbooks/${state.config.business_id}/services`)
            .then( ({ data }) => {
                commit('setServices', data ? data : []);
            })
            .catch(() => {});
    },
};

export default {
    state,
    getters,
    actions,
    mutations,
    namespaced: true,
}
