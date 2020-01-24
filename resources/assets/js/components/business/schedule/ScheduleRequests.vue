<template>

    <div>

        <h3>Active Requests on this Schedule</h3>
        <h5><b>Client:</b> {{ schedule ? schedule.client.name : '' }} </h5>
        <h5><b>Shift Date:</b> {{ scheduled_time }} </h5>

        <hr />
        <h5><b>Requests</b></h5>
        <div class="d-flex align-items-center mt-4">

            <div class="font-bold f-1">Caregiver Name</div>
            <div class="font-bold f-1">Request Date</div>
            <div class="font-bold f-2">CG Worked with Client Prev.?</div>
            <div class="font-bold f-1 text-center">Status</div>
            <div class="font-bold f-1 text-center" style="min-width: 300px">Actions</div>
        </div>
        <div v-if=" !loading ">

            <div v-for=" request in requests " :key=" request.id " class="d-flex align-items-center my-2">

                <div class="f-1">{{ request.nameLastFirst }}</div>
                <div class="f-1">{{ formatDateFromUTC( request.created_at ) }}</div>
                <div class="f-2">{{ request.caregiverClientRelationshipExists ? 'Yes' : 'No' }}</div>
                <div class="f-1 text-right">{{ request.status | capitalize }}</div>
                <div class="f-1 text-right" style="min-width: 300px">

                    <transition name="slide-fade" mode="out-in" v-if=" request.status == 'pending' ">

                        <div v-if=" !chosenRequest || request.id != chosenRequest.id " key="first">

                            <b-button variant="success" size="sm" type="button" :disabled=" busy || form.busy " @click=" checkRequest( request ) ">

                                Check Warnings
                            </b-button>
                            <b-button variant="danger" size="sm" type="button" :disabled=" busy || form.busy " @click=" respondToRequest( request, OPEN_SHIFTS_STATUS.DENIED ) ">

                                Decline
                            </b-button>
                        </div>
                        <div v-else key="second">

                            <b-button variant="info" size="sm" type="button" :disabled=" busy || form.busy " @click=" respondToRequest( request, OPEN_SHIFTS_STATUS.APPROVED ) ">

                                Approve
                            </b-button>
                            <b-button variant="default" size="sm" type="button" :disabled=" busy || form.busy " @click=" cancelRequest() ">

                                Cancel
                            </b-button>
                        </div>
                    </transition>
                </div>
            </div>
        </div>
        <div v-if=" warnings && warnings.length " class="mt-4">

            <b-alert v-for=" ( warning, index ) in warnings " :key=" index" variant="warning" show>

                <strong>{{ warning.label }}:</strong> {{ warning.description }}
            </b-alert>
        </div>
    </div>
</template>

<script>

    import FormatsDates from '../../../mixins/FormatsDates';
    import ScheduleMethods from '../../../mixins/ScheduleMethods';
    import Constants from '../../../mixins/Constants';

    export default {

        mixins : [ FormatsDates, ScheduleMethods, Constants ],
        props  : {

            selectedScheduleId: {

                type    : Number,
                default : null
            }
        },
        data(){

            return {

                loading         : false,
                busy            : false,
                requests        : [],
                schedule        : null,
                checkingRequest : false,
                chosenRequest   : null,
                warnings        : null,
                form            : new Form({

                    status       : null,
                    caregiver_id : null
                })
            }
        },
        computed : {

            scheduled_time(){

                if( !this.schedule ) return '';

                else return this.formatDateFromUTC( this.schedule.start ) + ' ' + this.schedule.start_time + ' - ' + this.schedule.end_time;
            }
        },
        methods: {

            cancelRequest(){

                this.chosenRequest = null;
                this.checkingRequest = false;
                this.warnings = null;
            },
            checkRequest( request ){

                if( this.chosenRequest ) this.cancelRequest();

                this.chosenRequest = request;
                this.busy = true;

                let form = new Form({

                    caregiver  : request.caregiver_id,
                    client     : request.client_id,
                    duration   : this.getDuration(),
                    starts_at  : this.getStartsAt(),
                    id         : this.schedule.id,
                    payer_id   : this.schedule.payer_id,
                    service_id : this.schedule.service_id,
                    services   : this.schedule.services || null, // ERIK TODO => check if this is necessary, this isnt included with the requests modal.. 
                });

                form.alertOnResponse = false;
                form.post( '/business/schedule/warnings' )
                    .then( ({ data }) => {

                        this.warnings = data;
                        this.checkingRequest = true;
                    })
                    .catch( e => {} )
                    .finally( () => {

                        this.busy = false;
                    })
            },
            async fetchRequests(){

                this.loading = true;

                axios.get( `/business/schedule/requests/${this.selectedScheduleId}` )
                    .then( response => {

                        console.log( 'loaded: ', response );
                        this.requests = response.data.data.requests;
                        this.schedule = response.data.data.schedule;
                    })
                    .catch( error => {

                        alert( 'Error loading schedule details' );
                    })
                    .finally( () => {

                        this.loading = false;
                    });
            },
            respondToRequest( request, status ){

                if( ![ this.OPEN_SHIFTS_STATUS.DENIED, this.OPEN_SHIFTS_STATUS.APPROVED ].includes( status ) ) return false;

                this.form.status = status;
                this.form.caregiver_id = request.caregiver_id

                this.form.patch( `/business/schedule/requests/${request.id}/${request.schedule_id}` )
                    .then( res => {

                        request.status = res.data.data;

                        this.$emit( 'request-response', { status: res.data.data, schedule: this.schedule, request: request });

                        if( status === this.OPEN_SHIFTS_STATUS.DENIED ){

                            const index = this.requests.map( el => el.id ).indexOf( request.id );
                            this.requests.splice( index, 1 );
                        }
                    })
                    .catch( e => {

                        console.error( e );
                    })
            }
        },
        async mounted(){

            await this.fetchRequests();
        },
        watch: {

            async selectedScheduleId( oldVal, newVal ){

                this.cancelRequest();
                this.requests = [];
                this.schedule = null;
                await this.fetchRequests();
                this.setDataFromSchedule();
            }
        }
    }
</script>

<style scoped>

</style>