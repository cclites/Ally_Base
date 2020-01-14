export default {

    data() {

        return {

            startDate : null,
            startTime : null,
            endTime   : null,
            endDate   : null
        }
    },
    methods: {

        getDuration() {

            if ( this.endTime && this.startTime ) {

                if ( this.startTime === this.endTime ) {

                    return 1440; // have 12:00am to 12:00am = 24 hours
                }

                let start = moment( this.startDate + ' ' + this.startTime, 'MM/DD/YYYY HH:mm' );
                let end   = moment( this.startDate + ' ' + this.endTime, 'MM/DD/YYYY HH:mm'   );

                if ( start && end ) {

                    if ( end.isBefore( start ) ) {

                        end = end.add( 1, 'days' );
                    }
                    let diff = end.diff( start, 'minutes' );
                    if ( diff ) {

                        return parseInt( diff );
                    }
                }
            }

            return null;
        },
        getStartsAt() {

            if ( this.startDate && this.startTime ) {

                return moment( this.startDate + ' ' + this.startTime, 'MM/DD/YYYY HH:mm' ).format();
            }
            return null;
        },
        setDataFromSchedule(){

            if ( !this.schedule ) return;

            let startsAt = moment( this.schedule.starts_at, 'YYYY-MM-DD HH:mm:ss' );
            console.log( startsAt );

            this.clientId  = this.schedule.client_id;
            this.startDate = startsAt.format( 'MM/DD/YYYY' );
            this.startTime = startsAt.format( 'HH:mm' );
            this.endTime   = moment( startsAt ).add( this.schedule.duration, 'minutes' ).format( 'HH:mm' );
        },
    }
}