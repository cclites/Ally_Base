<template>

    <div>

        <h3>Active Requests on this Schedule</h3>
        <div class="d-flex align-items-center mt-4">

            <div class="text-uppercase font-bold f-1">Caregiver Name</div>
            <div class="text-uppercase font-bold f-1">Request Date</div>
            <div class="text-uppercase font-bold f-1">Status</div>
            <div class="text-uppercase font-bold f-1">Actions</div>
        </div>
        <div v-for=" schedule in selectedSchedule " :key=" schedule.id " class="d-flex align-items-center my-2">

            <div class="f-1">{{ schedule.pivot.id + ' :: ' + schedule.nameLastFirst }}</div>
            <div class="f-1">{{ formatDateFromUTC( schedule.pivot.created_at ) }}</div>
            <div class="f-1">{{ schedule.pivot.status }}</div>
            <div class="f-1">

                <b-button variant="success" size="sm" type="button" :disabled=" loading " @click=" respondToRequest( schedule, 'accept' ) " v-if=" [ 'pending', 'denied' ].includes( schedule.pivot.status ) && !anyApproved ">

                    <i v-if=" loading " class="fa fa-spinner fa-spin mr-2" size="sm"></i>
                    Accept
                </b-button>
                <b-button variant="danger" size="sm" type="button" :disabled=" loading " @click=" respondToRequest( schedule, 'reject' ) " v-if=" [ 'pending', 'approved' ].includes( schedule.pivot.status ) ">

                    <i v-if=" loading " class="fa fa-spinner fa-spin mr-2" size="sm"></i>
                    Reject
                </b-button>
            </div>
        </div>
    </div>
</template>

<script>

    import FormatsDates from '../../../mixins/FormatsDates';

    export default {

        mixins : [ FormatsDates ],
        props  : {

            selectedSchedule: {

                type: Array,
                default() {

                    return {};
                }
            }
        },
        data(){

            return {

                loading : false
            }
        },
        computed : {

            anyApproved(){

                return this.selectedSchedule.some( s => s.pivot.status == 'approved' );
            }
        },
        methods: {

            respondToRequest( schedule, status ){

                this.loading = true;
                let form = new Form({

                    'status'              : status,
                    'schedule_request_id' : schedule.pivot.id,
                    'caregiver_id'        : schedule.pivot.caregiver_id
                });

                form.post( '/business/schedule/requests/' + schedule.pivot.schedule_id )
                    .then( res => {

                        console.log( res );
                        schedule.pivot.status = res.data.data;
                    })
                    .catch( e => {

                        console.error( e );
                    })
                    .finally( () => {

                        this.loading = false;
                    });
            }
        }
    }
</script>

<style scoped>

</style>