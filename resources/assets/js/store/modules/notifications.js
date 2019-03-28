import * as Vue from "vue";

const state = {
    notifications: [],
    running: false,
};

// getters
const getters = {
    notifications(state) {
        return state.notifications;
    },
};

// mutations
const mutations = {
    start (state) {
        Vue.set(state, 'running', true);
    },

    update(state, notifications) {
        Vue.set(state, 'notifications', notifications);
    },
};

// actions
const actions = {
    async start ({ commit, dispatch, state }) {
        if (state.running) {
            return;
        }
        commit('start');
        await dispatch('fetch');
        setInterval(() => {
            dispatch('fetch');
        }, 60000);
    },

    async fetch(context) {
        await axios.get('/business/notifications?json=1')
            .then( ({ data }) => {
                context.commit('update', data ? data : []);
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
