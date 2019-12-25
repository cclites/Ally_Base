import * as Vue from "vue";


const state = {

    // openShiftRequests : [],
    openShifts           : [],
    requests             : [],
    // running           : false,
};

// getters
const getters = {

    // all   : state => state.openShiftRequests,
    requests       : state => state.requests,
    openShifts     : state => state.openShifts,
    mappedShifts( state ){

        return state.openShifts.map( e => {

            for( let i = 0; i < state.requests.length; i++ ){

                if( state.requests[ i ].schedule_id == e.id ){

                    e.request_status = state.requests[ i ].status;
                    break;
                }
            }

            return e;
        });
    },
    newShiftsCount : ( state, getters ) => getters.mappedShifts.filter( e => !e.request_status ).length
};

// mutations
const mutations = {

    // start( state ) {

    //     Vue.set( state, 'running', true );
    // },

    // update( state, data ) {

    //     state.all = data.openShiftRequests;
    // },

    setOpenShifts( state, openShifts ) {

        state.openShifts = openShifts;
    },
    setRequests( state, requests ) {

        state.requests = requests;
    },
    updateOpenShifts( state, shifts ) {

        state.openShifts = shifts;
    },
};

// actions
const actions = {
    // major refactor to vuex in store for the future

    setShiftsAndRequests( context, data ){

        context.commit( 'setOpenShifts', data.events );
        context.commit( 'setRequests', data.requests );
    },
    updateRequestStatus( context, data ){

        // find schedule within requests object
        let openShiftIndex = context.state.openShifts.findIndex( s => s.id == data.schedule_id );
        if( openShiftIndex ){

            context.state.openShifts[ openShiftIndex ].request_status = data.status;
            let newShifts = _.cloneDeep( context.state.openShifts );
            context.commit( 'updateOpenShifts', newShifts );
        }
    }
};

export default {

    state,
    getters,
    actions,
    mutations,
    namespaced: true,
}
