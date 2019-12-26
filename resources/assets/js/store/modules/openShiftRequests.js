import * as Vue from "vue";

const state = {

    openShiftRequests : [],
    count             : 0,
    running           : false,
    debounced         : false
};

// getters
const getters = {

    all       : state => state.openShiftRequests,
    debounced : state => state.debounced,
    count     : state => state.count, // this may not be necessary.. could just be a count on the requests array.. revisit later
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
    debounceCall( state ){

        state.debounced = true;
    }
};

// actions
const actions = {
    // major refactor to vuex in store for the future

    setCount( context, count ){

        context.commit( 'setCount', count );
    },
    updateCount( context, count ){

        context.commit( 'updateCount', count );
    },
    debounce( context ){

        context.commit( 'debounceCall' );
    }
};

export default {

    state,
    getters,
    actions,
    mutations,
    namespaced: true,
}
