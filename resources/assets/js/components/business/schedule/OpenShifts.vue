<template>

    <b-modal id="openShiftsModal" title="Open Shifts" v-model=" openShiftsModalActive " size="xl" no-close-on-backdrop no-close-on-esc>

        <template slot="modal-header">

            <div class="d-flex w-100 justify-content-between align-items-center">

                <div class="d-flex justify-content-center flex-column">

                    <h5 class="m-0 modal-title">Open Shifts</h5>
                    <p class="m-0" v-if=" role_type == 'office_user' "><small>
                        Check Settings > General for open shifts settings for your business
                    </small></p>
                    <p class="m-0" v-else><small>
                        Turn phone sideways for best view
                    </small></p>
                </div>

                <button type="button" class="close" @click=" toggleOpenShiftsModal() " style="cursor:pointer">
                    &times;
                </button>
            </div>
        </template>

        <loading-card v-show=" loading " />

        <transition-group mode="out-in" name="slide-fade">

            <schedule-requests :selected-schedule-id=" hasSelectedScheduleId " v-if=" hasSelectedScheduleId " @request-response=" openShiftsModalRequestResponded " class="mb-5" key="uno"></schedule-requests>
        </transition-group>

        <ally-table id="open-shifts"
            :columns=" fields "
            :items=" aggEvents "
            sort-by="start"
            :perPage=" 1000 "
            :isBusy=" form.busy "
            v-show="! loading"
            empty-text="There are no caregiver requests for open shifts."
        >

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

                        <b-button variant="success" size="sm" class="f-1 mr-1" @click=" requestShift( data.item, OPEN_SHIFTS_STATUS.UNINTERESTED ) " key="request" :disabled=" form.busy ">Not Interested</b-button>
                        <b-button variant="primary" size="sm" class="f-1 ml-1" @click=" requestShift( data.item, OPEN_SHIFTS_STATUS.PENDING ) " key="request" :disabled=" form.busy ">Request Shift</b-button>
                    </div>

                    <div v-if=" hasRequest( data.item.request_status ) " class="" key="second-block">

                        <b-button variant="danger" size="sm" class="btn-block" @click=" requestShift( data.item, OPEN_SHIFTS_STATUS.CANCELLED ) " key="rescind" :disabled=" form.busy ">Cancel Request</b-button>
                    </div>
                </transition>
            </template>
            <template slot="requests_count" scope="data">

                <transition mode="out-in" name="slide-fade">

                    <div class="text-center" key="requestcontainerone" v-if=" !currentlySelected( data.item.id ) ">

                        <a href="#" @click.prevent=" showRequestModal( data.item ) " class="w-100 text-center" key="showit">{{ data.item.requests_count + ", Click to View" }}</a>
                    </div>
                    <div class="text-center" key="requestcontainertwo" v-else>

                        <a href="#" @click=" nullifySelectedSchedule() " class="w-100 text-center text-danger">{{ "Click to Hide" }}</a>
                    </div>
                </transition>
            </template>

            <template slot="status" scope="data">Open</template>
        </ally-table>

        <template slot="modal-footer">

            <div class="d-flex w-100 justify-content-end">

                <b-button variant="default" @click=" toggleOpenShiftsModal() " style="cursor:pointer">
                    Close
                </b-button>
            </div>
        </template>
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

        props: [ 'role_type' ],
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
                        key        : this.role_type == 'caregiver' ? 'distance' : 'status',
                        label      : this.role_type == 'caregiver' ? 'Dist.' : 'Status',
                        sortable   : this.role_type == 'caregiver' ? true : false,
                        shouldShow : true,
                    }
                ]
            }
        },

        mounted() {

            if( this.role_type == 'office_user' ){

                this.fetchEvents();
            }
        },

        computed: {

            ...mapGetters({

                openShifts             : 'openShifts/mappedShifts',
                cgRequests             : 'openShifts/requests',
                onSchedulePage         : 'openShifts/onSchedulePage',
                vuexSelectedScheduleId : 'openShifts/selectedScheduleId',
                vuexSelectedEvent      : 'openShifts/selectedEvent',
            }),
            openShiftsModalActive : {

                get: function(){

                    return this.$store.getters[ 'openShifts/openShiftsModalActive' ];
                },
                set: function(){
                    // only here to stop an innacurate error from occurring

                }
            },
            hasSelectedScheduleId(){

                return this.selectedScheduleId || this.vuexSelectedScheduleId;
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

                return url;
            }
        },

        methods: {

            ...mapActions({

                updateRequestStatus    : 'openShifts/updateRequestStatus',
                toggleOpenShiftsModal  : 'openShifts/toggleOpenShiftsModal',
                emitToScheduleViaVuex  : 'openShifts/emitToScheduleViaVuex',
                decrementScheduleEvent : 'openShifts/decrementScheduleEvent',
            }),
            openShiftsModalRequestResponded( data ){

                // failsafe to make sure the item is selected
                if( this.vuexSelectedScheduleId ) this.selectedScheduleId = this.vuexSelectedScheduleId;

                const status = data.status;
                let schedule = this.events.find( e => e.id === data.request.schedule_id );

                // 1. only applicable when on the schedule calendar page, set to true from the mounted() method on BusinessSchedule.. this is soooo ugly im so sorry
                if( this.onSchedulePage ) this.emitToScheduleViaVuex({ status: status, schedule : _.cloneDeep( schedule ), caregiverName : data.request.nameLastFirst });

                if( status == this.OPEN_SHIFTS_STATUS.DENIED ){

                    // 2. decrement the # requests on the record
                    schedule.requests_count--;
                    // if( this.openShifts.length > 0 ) this.decrementScheduleEvent( scheduleIndex ); // this might be necessary for caregivers?

                    // 3. decrement the side & top-header icon counts
                    this.updateCount( -1 );

                    if( schedule.requests_count == 0 ){
                        // no more requests?

                        // 4. remove the row from the table
                        this.removeScheduleEvent( data.request.schedule_id );
                        this.selectedScheduleId = null;
                    }

                    return;
                } // else its an approval..

                // 2. remove the row from the table
                this.removeScheduleEvent( data.request.schedule_id );

                // 3. update the icon count to reflect the entire requests_count
                this.updateCount( -schedule.requests_count );

                // 4. de-select the schedule
                this.selectedScheduleId = null;
            },
            currentlySelected( id ){

                return [ this.selectedScheduleId, this.vuexSelectedScheduleId ].includes( id );
            },
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

        watch : {

            openShiftsModalActive( newVal, oldVal ) {

                if( !newVal ) {

                    this.selectedEvent = null;
                    this.nullifySelectedSchedule();
                }
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