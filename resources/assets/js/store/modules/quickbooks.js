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
        if (! state.config) {
            return null;
        }
        return state.config.business_id;
    },
    isAuthorized(state) {
        if (! state.config) {
            return false;
        }
        return (state.config.is_desktop && !!state.config.desktop_api_key) ||
            (! state.config.is_desktop && !!state.config.access_token);
    },
    mapServiceFromShifts(state) {
        if (! state.config) {
            return false;
        }
        return !!state.config.allow_shift_overrides;
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
    async fetchConfig({commit}, businessId) {
        if (businessId == null) {
            return;
        }
        await axios.get(`/business/quickbooks/${businessId}/config`)
            .then( ({ data }) => {
                commit('setConfig', data ? data.data : []);
            })
            .catch(() => {});
    },
    async fetchServices({commit, state}) {
        if (! state.config || ! state.config.business_id) {
            // cannot run until config is fetched
            return;
        }

        await axios.get(`/business/quickbooks/${state.config.business_id}/services`)
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
