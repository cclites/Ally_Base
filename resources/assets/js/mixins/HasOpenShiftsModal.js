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
            let schedule = this.events.find( e => e.id === data.schedule_id );

            if( status == 'denied' ){

                // remove a mark from the row
                schedule.requests_count--;

                // remove a mark from the notifcation icon
                this.updateCount( -1 );

                if( this.selectedEvent ){
                    // only applicable when on the schedule calendar

                    this.selectedEvent.requests_count--;
                    console.log( 'updating this event: ', this.selectedEvent );
                    this.updateEvent( this.selectedEvent.id, this.selectedEvent );
                }

                if( schedule.requests_count == 0 ){

                    this.requestsModal = false;
                }

                return false;
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
    }
}