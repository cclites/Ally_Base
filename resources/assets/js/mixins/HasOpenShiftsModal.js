import { mapActions } from 'vuex';
import Constants from './Constants';

export default {

    mixins : [ Constants ],
    data() {

        return {

            requestsModal      : false,
            selectedScheduleId : null,
            selectedEvent      : null
        }
    },
    methods: {

        ...mapActions({

            updateCount : 'openShiftRequests/updateCount'
        }),
        requestResponded( data ){

            // console.log( data );

            const status = data.status;
            let schedule = this.events.find( e => e.id === data.request.schedule_id );

            // only applicable when on the schedule calendar
            if( this.selectedEvent ) this.handleCalendarPropogation( status );

            if( status == this.OPEN_SHIFTS_STATUS.DENIED ){

                // remove a mark from the row
                schedule.requests_count--;

                // remove a mark from the notifcation icon
                this.updateCount( -1 );

                if( schedule.requests_count == 0 ){
                    // no more requests? close the modal

                    this.requestsModal = false;
                }

                return;
            }

            // remove the entire row
            this.removeScheduleEvent( data.request.schedule_id );

            // close the modal
            this.requestsModal = false;

            // remove all marks within row from notification icon
            this.updateCount( -schedule.requests_count );

            this.selectedScheduleId = null;
        },
        showRequestModal( schedule_id ){

            this.selectedScheduleId = schedule_id;
            this.requestsModal    = true;
        },
        handleCalendarPropogation( newStatus ){

            this.selectedEvent.requests_count = ( newStatus == this.OPEN_SHIFTS_STATUS.DENIED ? this.selectedEvent.requests_count -= 1 : 0 ); // if approved, set to zero and close the modal anyways

            if( newStatus == this.OPEN_SHIFTS_STATUS.APPROVED || ( newStatus == this.OPEN_SHIFTS_STATUS.DENIED && this.selectedEvent.requests_count == 0 ) ){

                this.updateEvent( this.selectedEvent.id, this.selectedEvent );
            }
        },
        removeScheduleEvent( schedule_id ){

            const index = this.events.findIndex( e => e.id == schedule_id );
            this.events.splice( index, 1 );
        }
    }
}