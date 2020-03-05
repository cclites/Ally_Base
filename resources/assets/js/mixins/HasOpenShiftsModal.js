import { mapActions } from 'vuex';
import Constants from './Constants';

export default {

    mixins : [ Constants ],
    data() {

        return {

            selectedScheduleId : null,
            selectedEvent      : null
        }
    },
    methods: {

        ...mapActions({

            updateCount           : 'openShiftRequests/updateCount',
            toggleOpenShiftsModal : 'openShifts/toggleOpenShiftsModal',
            setSelectedEvent      : 'openShifts/setSelectedEvent',
            setNewCaregiverName   : 'openShifts/setNewCaregiverName',
        }),
        nullifySelectedSchedule(){

            this.selectedScheduleId = null;
            this.setSelectedEvent( null );
            this.setNewCaregiverName( null );
        },
        showRequestModal( schedule ){

            this.setSelectedEvent( schedule );
            this.selectedScheduleId = schedule.id;
            this.selectedEvent      = schedule;
        },
        handleCalendarPropogation( newStatus ){

            // console.log( 'incoming new status: ', newStatus );
            // console.log( 'is it approved?: ', newStatus == this.OPEN_SHIFTS_STATUS.APPROVED );
            // console.log( 'this.selectedEvent: ', this.selectedEvent );
            // console.log( 'this.selectedEvent.requests_count: ', this.selectedEvent.requests_count );
            this.selectedEvent.requests_count = ( newStatus == this.OPEN_SHIFTS_STATUS.DENIED ? this.selectedEvent.requests_count -= 1 : 0 ); // if approved, set to zero and close the modal anyways

            if( newStatus == this.OPEN_SHIFTS_STATUS.APPROVED || ( newStatus == this.OPEN_SHIFTS_STATUS.DENIED && this.selectedEvent.requests_count == 0 ) ){

                this.updateEvent( this.selectedEvent.id, this.selectedEvent, newStatus );
            }
        },
        removeScheduleEvent( schedule_id ){

            // console.log( 'getting within the thing', schedule_id );
            const index = this.events.findIndex( e => e.id == schedule_id );
            this.events.splice( index, 1 );
        }
    }
}