import * as Vue from "vue";


const state = {

    openShifts : [],
    requests   : [],
    openShiftsModalActive : false
};

// getters
const getters = {

    requests       : state => state.requests,
    openShifts     : state => Object.values( state.openShifts ),
    mappedShifts( state, getters ){

        return getters.openShifts.map( e => {

            for( let i = 0; i < state.requests.length; i++ ){

                if( state.requests[ i ].schedule_id == e.id ){

                    e.request_status = state.requests[ i ].status;
                    break;
                }
            }

            return e;
        });
    },
    newShiftsCount : ( state, getters ) => getters.mappedShifts.filter( e => !e.request_status ).length,
    openShiftsModalActive : state => state.openShiftsModalActive
};

// mutations
const mutations = {

    setOpenShifts( state, openShifts ) {

        state.openShifts = openShifts;
    },
    setRequests( state, requests ) {

        state.requests = requests;
    },
    updateOpenShifts( state, data ) {

        state.openShifts[ data.index ].request_status = data.status;
    },
    appendShiftRequest( state, req ) {

        state.requests.push( req );
    },
    toggleOpenShiftsModal : state => state.openShiftsModalActive = !state.openShiftsModalActive
};

// actions
const actions = {
    // major refactor to vuex in store for the future

    setShiftsAndRequests( context, data ){

        context.commit( 'setOpenShifts', Object.values( data.events ) );
        context.commit( 'setRequests', data.requests );
    },
    updateRequestStatus( context, data ){

        if( data.new_request ){
            // if new_request is populated, it needs to be appended

            context.commit( 'appendShiftRequest', data.new_request );
        } else {
            // else find the existing schedule request within requests object and update it

            let openShiftIndex = context.getters.openShifts.findIndex( s => s.id == data.schedule_id );
            if( openShiftIndex ) context.commit( 'updateOpenShifts', { index: openShiftIndex, status: data.status } );
        }
    },
    toggleOpenShiftsModal : context => context.commit( 'toggleOpenShiftsModal' ),
};

export default {

    state,
    getters,
    actions,
    mutations,
    namespaced: true,
}
