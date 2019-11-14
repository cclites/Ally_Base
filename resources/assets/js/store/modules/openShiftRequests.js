import * as Vue from "vue";

const state = {

    openShiftRequests : [],
    count             : 0,
    running           : false,
};

// getters
const getters = {

    all   : state => state.openShiftRequests,
    count : state => state.count, // this may not be necessary
};

// mutations
const mutations = {

    start( state ) {

        Vue.set( state, 'running', true );
    },

    update( state, data ) {

        state.all = data.openShiftRequests;
    },

    setCount( state, count ) {

        state.count = count;
    },
    updateCount( state, count ) {

        state.count += count;
    },
};

// actions
const actions = {
    // major refactor to vuex in store for the future

    setCount( context, count ){

        context.commit( 'setCount', count );
    },
    updateCount( context, count ){

        context.commit( 'updateCount', count );
    }
};

export default {

    state,
    getters,
    actions,
    mutations,
    namespaced: true,
}
