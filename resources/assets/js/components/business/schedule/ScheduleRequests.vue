<template>

    <div>

        <h3>Active Requests on this Schedule</h3>
        <div class="d-flex align-items-center mt-4">

            <div class="text-uppercase font-bold f-1">Caregiver Name</div>
            <div class="text-uppercase font-bold f-1">Request Date</div>
            <div class="text-uppercase font-bold f-1">Status</div>
            <div class="text-uppercase font-bold f-1">Actions</div>
        </div>
        <div v-if=" !loading ">

            <div v-for=" request in requests " :key=" request.id " class="d-flex align-items-center my-2">

                <div class="f-1">{{ request.nameLastFirst }}</div>
                <div class="f-1">{{ formatDateFromUTC( request.pivot.created_at ) }}</div>
                <div class="f-1">{{ request.pivot.status }}</div>
                <div class="f-1">

                    <b-button variant="success" size="sm" type="button" :disabled=" busy " @click=" respondToRequest( request, 'accept' ) " v-if=" [ 'pending', 'denied' ].includes( request.pivot.status ) && !anyApproved ">

                        <i v-if=" busy " class="fa fa-spinner fa-spin mr-2" size="sm"></i>
                        Accept
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
                requests : []
            }
        },
        computed : {

            anyApproved(){

                return this.requests.some( r => r.pivot.status == 'approved' );
            }
        },
        methods: {

            async fetchRequests(){

                console.log( 'loading...' );
                this.loading = true;

                const form = new Form({

                    schedule : this.selectedScheduleId
                });

                form.get( '/business/schedule/requests/' )
                    .then( response => {

                        console.log( 'loaded: ', response );
                        this.requests = response.data.data;
                    })
                    .catch( error => {

                        alert( 'Error loading schedule details' );
                    })
                    .finally( () => {

                        this.loading = false;
                    });
            },
            respondToRequest( schedule, status ){

                this.busy = true;
                let form = new Form({

                    'status'       : status,
                    'schedule_id'  : schedule.pivot.schedule_id,
                    'caregiver_id' : schedule.pivot.caregiver_id
                });

                form.patch( '/business/schedule/requests/' + schedule.pivot.id )
                    .then( res => {

                        console.log( res );
                        schedule.pivot.status = res.data.data;
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
        }
    }
</script>

<style scoped>

</style>