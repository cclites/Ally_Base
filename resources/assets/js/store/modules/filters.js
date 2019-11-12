import * as Vue from "vue";

const state = {
    activeBusiness: null,
    loading: [],
    loaded: [],

    // chain resources
    services: [],
    payers: [],

    // business resources
    clients: [],
    caregivers: [],
    activities: [],
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

    clientList(state) {
        if (! state.activeBusiness) {
            return state.clients;
        }
        return state.clients.filter(x => x.business_id == state.activeBusiness);
    },
    isClientsLoading(state) {
        return state.loading.includes('clients');
    },
    isClientsLoaded(state) {
        return state.loaded.includes('clients');
    },

    caregiverList(state) {
        if (! state.activeBusiness) {
            return state.caregivers;
        }
        return state.caregivers.filter(x => x.businesses.includes(state.activeBusiness));
    },
    isCaregiversLoading(state) {
        return state.loading.includes('caregivers');
    },
    isCaregiversLoaded(state) {
        return state.loaded.includes('caregivers');
    },

    serviceList(state) {
        return state.services;
    },
    isServicesLoading(state) {
        return state.loading.includes('services');
    },
    isServicesLoaded(state) {
        return state.loaded.includes('services');
    },

    activityList(state) {
        return state.activities;
    },
    isActivitiesLoading(state) {
        return state.loading.includes('activities');
    },
    isActivitiesLoaded(state) {
        return state.loaded.includes('activities');
    },

    isOrWasLoaded(state, resource) {
        return state.loaded.includes(resource) || state.loading.includes(resource);
    },
};

// mutations
const mutations = {
    setBusiness(state, business_id) {
        Vue.set(state, 'activeBusiness', business_id);
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
    setPayerList(state, data) {
        Vue.set(state, 'payers', data);
    },
    setResourceList(state, { resource, data }) {
        Vue.set(state, resource, data);
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

    async fetchResources({commit, getters, state}, resources) {
        if (! Array.isArray(resources)) {
            resources = [resources];
        }

        let unloadedResources = [];
        for (const resource of resources) {
            if (state.loading.includes(resource) || state.loaded.includes(resource)) {
                continue;
            }
            commit('setLoading', resource);
            unloadedResources.push(resource);
        }

        if (unloadedResources.length === 0) {
            return;
        }

        await axios.get(`/business/resources?resources=`+unloadedResources.join(','))
            .then( ({ data }) => {
                for (const resource of unloadedResources) {
                    commit('setResourceList', {resource, data: data[resource]});
                }
            })
            .catch(() => {
                for (const resource of unloadedResources) {
                    commit('setResourceList', {resource, data: []});
                }
            })
            .finally(() => {
                for (const resource of unloadedResources) {
                    commit('setLoaded', resource);
                    commit('doneLoading', resource);
                }
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
