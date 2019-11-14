<template>

    <div>

        <h3>Active Requests on this Schedule</h3>
        <h5><b>Client:</b> {{ schedule ? schedule.client.name : '' }} </h5>
        <h5><b>Shift Date:</b> {{ scheduled_time }} </h5>

        <hr />
        <h5><b>Requests</b></h5>
        <div class="d-flex align-items-center mt-4">

            <div class="text-uppercase font-bold f-1">Caregiver Name</div>
            <div class="text-uppercase font-bold f-1">Request Date</div>
            <div class="text-uppercase font-bold f-1">CG Worked with Client Prev.?</div>
            <div class="text-uppercase font-bold f-1">Status</div>
            <div class="text-uppercase font-bold f-1">Actions</div>
        </div>
        <div v-if=" !loading ">

            <div v-for=" request in requests " :key=" request.id " class="d-flex align-items-center my-2">

                <div class="f-1">{{ request.nameLastFirst }}</div>
                <div class="f-1">{{ formatDateFromUTC( request.pivot.created_at ) }}</div>
                <div class="f-1">{{ request.caregiver_client_relationship_exists ? 'yes' : 'no' }}</div>
                <div class="f-1">{{ request.pivot.status }}</div>
                <div class="f-1">

                    <b-button variant="success" size="sm" type="button" :disabled=" busy " @click=" respondToRequest( request, 'approved' ) " v-if=" [ 'pending', 'denied' ].includes( request.pivot.status ) && !anyApproved ">

                        <i v-if=" busy " class="fa fa-spinner fa-spin mr-2" size="sm"></i>
                        Approve
                    </b-button>
                    <b-button variant="danger" size="sm" type="button" :disabled=" busy " @click=" respondToRequest( request, 'denied' ) " v-if=" !anyApproved || ( request.pivot.status == 'approved' && request.pivot.caregiver_id == request.id )">

                        <i v-if=" busy " class="fa fa-spinner fa-spin mr-2" size="sm"></i>
                        Decline
                    </b-button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>

    import FormatsDates from '../../../mixins/FormatsDates';

    export default {

        mixins : [ FormatsDates ],
        props  : {

            selectedScheduleId: {

                type    : Number,
                default : null
            }
        },
        data(){

            return {

                loading  : false,
                busy     : false,
                requests : [],
                schedule : null
            }
        },
        computed : {

            anyApproved(){

                return this.requests.some( r => r.pivot.status == 'approved' );
            },
            scheduled_time(){

                if( !this.schedule ) return '';

                else return this.formatDateFromUTC( this.schedule.start ) + ' ' + this.schedule.start_time + ' - ' + this.schedule.end_time;
            }
        },
        methods: {

            async fetchRequests(){

                console.log( 'loading...' );
                this.loading = true;

                axios.get( `/business/schedule/requests/${this.selectedScheduleId}` )
                    .then( response => {

                        console.log( 'loaded: ', response );
                        this.requests = response.data.data.requests;
                        this.schedule = response.data.data.schedule;
                    })
                    .catch( error => {

                        console.error( error );
                        alert( 'Error loading schedule details' );
                    })
                    .finally( () => {

                        this.loading = false;
                    });
            },
            respondToRequest( request, status ){

                if( ![ 'denied', 'approved' ].includes( status ) ) return false;

                this.busy = true;
                let form = new Form({

                    'status'       : status,
                    'schedule_id'  : request.pivot.schedule_id,
                    'caregiver_id' : request.pivot.caregiver_id
                });

                form.patch( '/business/schedule/requests/' + request.pivot.id )
                    .then( res => {

                        console.log( 'response from the change: ', res );
                        request.pivot.status = res.data.data;

                        this.$emit( 'request-response', { status: res.data.data, schedule_id: request.pivot.schedule_id });

                        if( status === 'denied' ){

                            const index = this.requests.map( el => el.pivot.schedule_id ).indexOf( request.pivot.schedule_id );
                            this.requests.splice( index, 1 );
                        }
                    })
                    .catch( e => {

                        console.error( e );
                    })
                    .finally( () => {

                        this.busy = false;
                    });
            }
        },
        async mounted(){

            await this.fetchRequests();
        },
        watch: {

            async selectedScheduleId( oldVal, newVal ){

                this.requests = [];
                this.schedule = null;
                await this.fetchRequests();
            }
        }
    }
</script>

<style scoped>

</style>