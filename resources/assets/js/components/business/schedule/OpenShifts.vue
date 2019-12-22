<template>

  <div>

    <b-card :title=" active_business ? active_business.name : '' ">

        <p class="mt-3 mb-4" v-if=" role_type == 'office_user' ">
            Caregivers can see all of these open shifts
            - anytime within the Ally app
            - that do not overlap any of their scheduled shifts.
            You do not have to send out text messages for every open shifts.
            Instead, maybe send a reminder once a week that all caregivers should check the available open shifts within the Ally App
        </p>

        <loading-card v-show=" loading " />

        <div v-show="! loading" class="table-responsive">

            <ally-table id="open-shifts" :columns=" fields " :items=" events " sort-by="start" :perPage=" 1000 " :isBusy=" form.busy ">

                <template slot="start" scope="data">

                    {{ ( data.item ? formatDateFromUTC( data.item.start ) + ' ' : '' ) + ( data.item ? data.item.start_time + '-' : '' ) + ( data.item ? data.item.end_time : '' ) }}
                </template>
                <template slot="client" scope="data">

                    <a v-if=" role_type == 'office_user' " :href=" '/business/clients/' + data.item.client_id " target="_blank">{{ data.item.client }}</a>
                    <p v-else>{{ data.item.client }}</p>
                </template>
                <template slot="actions" scope="data">

                    <transition mode="out-in" name="slide-fade">

                        <div v-if=" !hasRequest( data.item.request_status ) " class="d-flex" key="first-block">

                            <b-button variant="success" size="sm" class="f-1 mr-1" @click=" requestShift( data.item, OPEN_SHIFTS_STATUS.UNINTERESTED ) " key="request">Not Interested</b-button>
                            <b-button variant="primary" size="sm" class="f-1 ml-1" @click=" requestShift( data.item, OPEN_SHIFTS_STATUS.PENDING ) " key="request">Request Shift</b-button>
                        </div>

                        <div v-if=" hasRequest( data.item.request_status ) " class="" key="second-block">

                            <b-button variant="default" size="sm" class="btn-block" @click=" requestShift( data.item, OPEN_SHIFTS_STATUS.CANCELLED ) " key="rescind">Cancel Request</b-button>
                        </div>
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
    import Constants from '../../../mixins/Constants';

    export default {

        props: [ 'businesses', 'role_type' ],
        data() {

            return {

                loading          : false,
                filtersReady     : false,
                events           : [],
                eventsLoaded     : false,
                active_business  : null,
                requests         : [],
                form             : new Form({ status : null }, false ),
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
                    case this.OPEN_SHIFTS_STATUS.CANCELLED:

                        return false;
                        break;
                    case this.OPEN_SHIFTS_STATUS.PENDING:
                    case this.OPEN_SHIFTS_STATUS.DENIED:
                    case this.OPEN_SHIFTS_STATUS.APPROVED:
                    case this.OPEN_SHIFTS_STATUS.UNINTERESTED:

                        return true;
                        break;
                }
            },
            requestShift( schedule, status ){

                if( this.role_type != 'caregiver' ) return false;

                this.form.status = status;

                this.form.post( `/schedule/requests/${schedule.id}` )
                    .then( res => {

                        schedule.request_status = res.data.data.status;
                        if( schedule.request_status == this.OPEN_SHIFTS_STATUS.UNINTERESTED ) this.removeScheduleEvent( schedule.id );
                    })
                    .catch( e => {

                        this.removeScheduleEvent( schedule.id );
                    });
            },
            fetchEvents( savePosition = false ) {

                this.loading = true;

                axios.get( this.eventsUrl )
                    .then( ({ data }) => {

                        console.log( data );
                        this.requests = data.requests;
                        this.events   = Object.values( data.events ).map( e => {

                            for( let i = 0; i < this.requests.length; i++ ){

                                if( this.requests[ i ].schedule_id == e.id ){

                                    e.request_status = this.requests[ i ].status;
                                    break;
                                }
                            }

                            return e;
                        }).filter( e => ![ this.OPEN_SHIFTS_STATUS.DENIED, this.OPEN_SHIFTS_STATUS.UNINTERESTED ].includes( e.request_status ) );

                        this.eventsLoaded = true;
                    })
                    .catch( e => {

                        console.error( 'error getting events:', e );
                    })
                    .finally( () => {

                        this.loading = false;
                    });
            },
        },

        mixins: [

            FormatsDates,
            AuthUser,
            HasOpenShiftsModal,
            Constants
        ],

        components: {

            ScheduleRequestModal
        }
    }
</script>

<style scoped>

</style>