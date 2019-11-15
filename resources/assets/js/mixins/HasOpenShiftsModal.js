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

            // only applicable when on the schedule calendar
            if( this.selectedEvent ) this.handleCalendarProgogation( status );

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
        handleCalendarPropogation( newStatus ){

            this.selectedEvent.requests_count = ( newStatus == 'denied' ? this.selectedEvent.requests_count-- : 0 ); // if approved, set to zero and close the modal anyways
            if( newStatus == 'approved' ) this.approveScheduleRequest();

            console.log( 'updating this event: ', this.selectedEvent );
            this.updateEvent( this.selectedEvent.id, this.selectedEvent );
        },
        approveScheduleRequest(){

            let form = new Form({

                status : 'approved'
            });

            // Submit form
            let url = `/business/schedule/${this.selectedEvent.id}`;

            form.hideErrorsFor( 449 ).patch( url )
                .then( response => {

                    this.fetchEvents( true );
                })
                .catch( error => {

                    this.handleErrors( error ); // ERIK TODO => handle the over-hours.. it is error 449
                })
                .finally( () => {

                });
        }
    }
}