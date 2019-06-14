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
    async fetchServices(context) {
        if (! context.state.config.business_id) {
            // cannot run until config is fetched
            return;
        }

        await axios.get(`quickbooks/${businessId}/services`)
            .then( ({ data }) => {
                context.commit('setServices', data ? data : []);
            })
            .catch(() => {});
    },
    async fetchConfig(context, businessId) {
        await axios.get(`quickbooks/${businessId}/services`)
            .then( ({ data }) => {
                context.commit('setServices', data ? data : []);
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
