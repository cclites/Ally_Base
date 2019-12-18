<template>

  <div>

    <b-card :title=" active_business ? active_business.name : '' ">

        <p class="mt-3 mb-4">
            Caregivers can see all of these open shifts
            - anytime within the Ally app
            - that do not overlap any of their scheduled shifts.
            You do not have to send out text messages for every open shifts.
            Instead, maybe send a reminder once a week that all caregivers should check the available open shifts within the Ally App
        </p>

        <loading-card v-show=" loading " />

        <div v-show="! loading" class="table-responsive">

            <ally-table id="open-shifts" :columns=" fields " :items=" events " sort-by="start" :perPage=" 1000 " :isBusy=" isBusy ">

                <template slot="start" scope="data">

                    {{ ( data.item ? formatDateFromUTC( data.item.start ) + ' ' : '' ) + ( data.item ? data.item.start_time + '-' : '' ) + ( data.item ? data.item.end_time : '' ) }}
                </template>
                <template slot="actions" scope="data">

                    <transition mode="out-in" name="slide-fade">

                        <b-button variant="success" size="sm" class="btn-block" v-if=" !hasRequest( data.item.request_status ) " @click=" requestShift( data.item, 'pending' ) " key="request">Request Shift</b-button>
                        <b-button variant="default" size="sm" class="btn-block" v-if=" hasRequest( data.item.request_status ) " @click=" requestShift( data.item, 'cancelled' ) " key="rescind">Cancel Request</b-button>
                    </transition>
                </template>
                <template slot="requests_count" scope="data">

                    <div class="text-center">

                        <a href="#" @click.prevent=" showRequestModal( data.item.id ) " v-if=" data.item.requests_count > 0 " class="w-100 text-center">{{ data.item.requests_count }}</a>
                        <span v-else>0</span>
                    </div>
                </template>

                <template slot="status" scope="data">Open</template>
            </ally-table>
        </div>
    </b-card>

    <schedule-request-modal v-model=" requestsModal " :selected-schedule-id=" selectedScheduleId " @request-response=" requestResponded "></schedule-request-modal>
  </div>
</template>

<script>

    import FormatsDates from '../../../mixins/FormatsDates';
    import AuthUser from '../../../mixins/AuthUser';
    import HasOpenShiftsModal from '../../../mixins/HasOpenShiftsModal';
    import ScheduleRequestModal from "../../modals/ScheduleRequestModal";

    export default {

        props: [ 'businesses', 'role_type' ],
        data() {

            return {

                loading          : false,
                filtersReady     : false,
                events           : [],
                eventsLoaded     : false,
                active_business  : null,
                isBusy           : false,
                requests         : [],
                fields : [

                    {
                        key        : 'start',
                        label      : 'Shift Date',
                        sortable   : true,
                        shouldShow : true,
                    },
                    {
                        key        : 'client',
                        label      : 'Client',
                        sortable   : true,
                        shouldShow : true,
                    },
                    {
                        key        : this.role_type == 'caregiver' ? 'actions' : 'requests_count',
                        label      : this.role_type == 'caregiver' ? 'Actions' : 'Requests',
                        sortable   : this.role_type == 'caregiver' ? false : true,
                        shouldShow : true,
                    },
                    {
                        key        : 'status',
                        label      : 'Status',
                        sortable   : true,
                        shouldShow : true,
                    }
                ]
            }
        },

        mounted() {

            // this will be for when we allow caregivers to switch between businesses
            if( !Array.isArray( this.businesses ) ) this.active_business = this.businesses;
            else this.active_business = this.businesses[ 0 ].id || null;

            this.fetchEvents();
        },

        computed: {

            eventsUrl() {

                let url = '';

                switch( this.role_type ){

                    case 'caregiver':

                        url = '/schedule/open-shifts';
                        break;
                    case 'office_user':

                        url = '/business/schedule/open-shifts';
                        break;
                }

                url += '?json=1';

                url += '&businesses=' + this.active_business;

                return url;
            }
        },

        methods: {

            hasRequest( status ){

                switch( status ){

                    case null:
                    case 'cancelled':

                        return false;
                        break;
                    case 'pending':
                    case 'denied':
                    case 'approved':

                        return true;
                        break;
                }
            },
            requestShift( schedule, status ){

                if( this.role_type != 'caregiver' ) return false;

                this.isBusy = true;

                axios.post( `/schedule/requests/${schedule.id}`, { status : status } )
                    .then( res => {

                        schedule.request_status = res.data.data.status;
                    })
                    .catch( e => {

                        alert( 'error requesting shift, please refresh or contact support' );
                        const index = this.events.findIndex( e => e.id == schedule.id );
                        this.events.splice( index, 1 );
                    })
                    .finally( () => {

                        this.isBusy = false;
                    });
            },
            fetchEvents( savePosition = false ) {

                this.loading = true;

                axios.get( this.eventsUrl )
                    .then( ({ data }) => {

                        console.log( 'returned schedules: ', data );

                        this.requests = data.requests;
                        this.events   = data.events.map( e => {

                            for( let i = 0; i < this.requests.length; i++ ){

                                if( this.requests[ i ].schedule_id == e.id ){

                                    e.request_status = this.requests[ i ].status;
                                    break;
                                }
                            }
                            return e;
                        });

                        this.eventsLoaded = true;
                    })
                    .catch( e => {

                        console.error( 'error getting events:' );
                        console.error( e );
                    })
                    .finally( () => {

                        this.loading = false;
                    });
            },
        },

        mixins: [

            FormatsDates,
            AuthUser,
            HasOpenShiftsModal
        ],

        components: {

            ScheduleRequestModal
        }
    }
</script>

<style scoped>

</style>