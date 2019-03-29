import * as Vue from "vue";

const state = {
    list: [],
    running: false,
    role: '',
};

// getters
const getters = {
    tasks(state) {
        return state.list;
    },
};

// mutations
const mutations = {
    start (state, role) {
        Vue.set(state, 'running', true);
    },

    setRole (state, role) {
        Vue.set(state, 'role', role);
    },

    update(state, tasks) {
        Vue.set(state, 'list', tasks);
    },
};

// actions
const actions = {
    async start ({ commit, dispatch, state }, role) {
        if (state.running) {
            return;
        }
        commit('start');
        commit('setRole', role);
        await dispatch('fetch');
        setInterval(() => {
            dispatch('fetch');
        }, 60000);
    },

    async fetch({ commit, state }) {
        let url = state.role == 'caregiver' ? '/tasks' : '/business/tasks';
        await axios.get(`${url}?pending=1&assigned=1`)
            .then( ({ data }) => {
                commit('update', data ? data : []);
            })
            .catch(e => {
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
