import * as Vue from "vue";


const state = {

    openShifts : [],
    requests   : [],
    onSchedulePage : false,
    openShiftsModalActive : false,
    selectedEvent : null, // TODO => this is going to need to be a part of a larger refactor to bring the schedule into vuex.. now that multiple components from different views are using the same data... this is too big for this specific task
    triggerBusinessScheduleToAct : false, // TODO => this again is a really wierd way to have the modal component communicate to the businessSchedule component.. cross component communication like this shouldn't be necessary - but this is the bandaide to get us through tomorrow
    newStatus : null,
};

// getters
const getters = {

    triggerBusinessScheduleToAct : state => state.triggerBusinessScheduleToAct,
    requests   : state => state.requests,
    openShifts : state => Object.values( state.openShifts ),
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
    openShiftsModalActive : state => state.openShiftsModalActive,
    selectedEvent : state => state.selectedEvent,
    selectedScheduleId : state => state.selectedEvent ? state.selectedEvent.id : null,
    onSchedulePage : state => state.onSchedulePage,
    newStatus : state => state.newStatus,
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
    toggleOpenShiftsModal : state => state.openShiftsModalActive = !state.openShiftsModalActive,
    setSelectedEvent : ( state, event ) => state.selectedEvent = event,
    establishWeAreOnSchedulePage : state => state.onSchedulePage = true,
    triggerBusinessScheduleToAct : ( state, bool ) => state.triggerBusinessScheduleToAct = bool,
    setNewStatus : ( state, value ) => state.newStatus = value,
    decrementScheduleEvent( state, index ){

        // console.log( 'index: ', index );
        // console.log( 'state shfits: ', state.openShifts );
        let event = state.openShifts[ index ];
        event.requests_count--;
    }
};

// actions
const actions = {
    // major refactor to vuex in store for the future

    establishWeAreOnSchedulePage : context => context.commit( 'establishWeAreOnSchedulePage' ),
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
    toggleOpenShiftsModal( context, event = null ){

        const e = event ? {

            id               : event.id,
            requests_count   : event.requests_count,
            background_color : event.background_color
        } : null;
        context.commit( 'setSelectedEvent', e );
        context.commit( 'toggleOpenShiftsModal' );
    },
    setSelectedEvent( context, event ){

        context.commit( 'setSelectedEvent', event );
    },
    emitToScheduleViaVuex( context, data ){

        context.commit( 'setNewStatus', data.status );
        context.commit( 'setSelectedEvent', data.schedule );
        context.commit( 'triggerBusinessScheduleToAct', true );
    },
    toggleTrigger: ( context, bool ) => context.commit( 'triggerBusinessScheduleToAct', bool ),
    setNewStatus: ( context, status ) => context.commit( 'setNewStatus', status ),
    decrementScheduleEvent: ( context, index ) => context.commit( 'decrementScheduleEvent', index ),

};

export default {

    state,
    getters,
    actions,
    mutations,
    namespaced: true,
}
