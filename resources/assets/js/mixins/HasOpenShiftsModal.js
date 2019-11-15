import { mapActions } from 'vuex';

export default {

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

            const status = data.status;
            let schedule = this.events.find( e => e.id === data.request.pivot.schedule_id );

            // only applicable when on the schedule calendar
            if( this.selectedEvent ) this.handleCalendarPropogation( status, data.schedule, data.request );

            if( status == 'denied' ){

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
            const index = this.events.findIndex( e => e.id === data.schedule_id );
            this.events.splice( index, 1 );

            // close the modal
            this.requestsModal = false;

            // remove all marks within row from notification icon
            this.updateCount( -schedule.requests_count );
        },
        showRequestModal( schedule_id ){

            this.selectedScheduleId = schedule_id;
            this.requestsModal    = true;
        },
        handleCalendarPropogation( newStatus, schedule, request ){

            this.selectedEvent.requests_count = ( newStatus == 'denied' ? this.selectedEvent.requests_count -= 1 : 0 ); // if approved, set to zero and close the modal anyways

            if( newStatus == 'approved' || ( newStatus == 'denied' && this.selectedEvent.requests_count == 0 ) ){

                this.updateEvent( this.selectedEvent.id, this.selectedEvent );
            }
        },
    }
}