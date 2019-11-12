import * as Vue from "vue";

const state = {
    notifications: [],
    total: 0,
    running: false,
};

// getters
const getters = {
    notifications(state) {
        return state.notifications;
    },
    total(state) {
        return state.total;
    },
};

// mutations
const mutations = {
    start (state) {
        Vue.set(state, 'running', true);
    },

    update(state, {total, notifications}) {
        Vue.set(state, 'total', total);
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
        await axios.get('/business/notifications/preview')
            .then( ({ data }) => {
                console.log('repsonse:', data);
                context.commit('update', data ? data : {notifications: [], total: 0});
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
