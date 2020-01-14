<template>

    <b-modal id="openShiftsModal" title="Open Shifts" v-model=" openShiftsModalActive " size="xl" no-close-on-backdrop no-close-on-esc ok-only ok-variant="default" ok-title="Close">

        <template v-slot:modal-header="{ close }">

            <div class="d-flex w-100 justify-content-end">

                <b-button size="sm" variant="default" @click=" toggleOpenShiftsModal() ">
                    Close Modal
                </b-button>
            </div>
        </template>

        <p class="mt-3 mb-4" v-if=" role_type == 'office_user' ">
            Check Settings > General for open shifts settings for your business
        </p>

        <loading-card v-show=" loading " />

        <transition-group mode="out-in" name="slide-fade">

            <schedule-requests :selected-schedule-id=" selectedScheduleId " v-if=" selectedScheduleId " @request-response=" requestResponded " class="mb-5" key="uno"></schedule-requests>
            <div class="d-flex w-100 justify-content-end mb-4" key="dos">

                <b-button variant="default" v-if=" selectedScheduleId " @click=" selectedScheduleId = null ">Close Requests</b-button>
            </div>
        </transition-group>

        <div v-show="! loading" class="table-responsive">

            <ally-table id="open-shifts" :columns=" fields " :items=" aggEvents " sort-by="start" :perPage=" 1000 " :isBusy=" form.busy ">

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

                            <b-button variant="danger" size="sm" class="btn-block" @click=" requestShift( data.item, OPEN_SHIFTS_STATUS.CANCELLED ) " key="rescind">Cancel Request</b-button>
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
    </b-modal>
</template>

<script>

    import FormatsDates from '../../../mixins/FormatsDates';
    import AuthUser from '../../../mixins/AuthUser';
    import HasOpenShiftsModal from '../../../mixins/HasOpenShiftsModal';
    import Constants from '../../../mixins/Constants';
    import ScheduleRequests from '../../business/schedule/ScheduleRequests';
    import { mapGetters, mapActions } from 'vuex';

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

            if( this.role_type == 'office_user' ){

                this.fetchEvents();
            }


        },

        computed: {

            ...mapGetters({

                openShifts : 'openShifts/mappedShifts',
                cgRequests : 'openShifts/requests',
            }),
            openShiftsModalActive : {

                get: function(){

                    return this.$store.getters[ 'openShifts/openShiftsModalActive' ];
                },
                set: function(){

                    this.toggleOpenShiftsModal();
                }
            },
            aggEvents(){

                if( this.openShifts.length == 0 ) return this.events;
                else return this.openShifts.filter( s => ![ this.OPEN_SHIFTS_STATUS.UNINTERESTED, this.OPEN_SHIFTS_STATUS.DENIED ].includes( s.request_status ) );
            },
            aggRequests(){

                if( this.cgRequests.length == 0 ) return this.requests;
                else return this.cgRequests;
            },
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

            ...mapActions({

                updateRequestStatus   : 'openShifts/updateRequestStatus',
                toggleOpenShiftsModal : 'openShifts/toggleOpenShiftsModal',
            }),
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

                        this.updateRequestStatus({ schedule_id: schedule.id, status: res.data.data.status, new_request: res.data.data.new_request });
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

                        // console.log( data );
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

            ScheduleRequests
        }
    }
</script>

<style scoped>

</style>