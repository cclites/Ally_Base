<template>

  <div>

    <b-card :title=" active_business ? active_business.name : '' ">

      <loading-card v-show=" loading " />

      <div v-show="! loading" class="table-responsive">

          <ally-table id="open-shifts" :columns=" fields " :items=" events " sort-by="date" :perPage=" 1000 " :isBusy=" isBusy ">

            <template slot="start" scope="data">

                {{ formatDateFromUTC( data.item.start ) + ' ' + data.item.start_time + '-' + data.item.end_time + ' :: ' + data.item.id }}
            </template>
            <template slot="actions" scope="data">

                <transition mode="out-in" name="slide-fade">

                    <b-button variant="success" size="sm" class="btn-block" v-if=" !hasRequest( data.item.request_status ) " @click=" requestShift( data.item ) " key="request">Request Shift</b-button>
                    <b-button variant="default" size="sm" class="btn-block" v-if=" hasRequest( data.item.request_status ) " @click=" requestShift( data.item ) " key="rescind">Cancel Request</b-button>
                </transition>
            </template>
            <template slot="requests_count" scope="data">

                <a href="#" @click.prevent=" showRequestModal( data.item.id ) " v-if=" data.item.requests_count > 0 ">{{ data.item.requests_count }} Request{{ data.item.requests_count > 1 ? 's' : '' }}</a>
                <span v-else>0</span>
            </template>
            <template slot="status" scope="data">

                {{ data.item.status == 'OK' ? 'Open' : data.item.status }}
            </template>
          </ally-table>
      </div>
    </b-card>

    <b-modal id="schedule-requests-modal"
        title="Schedule Requests"
        size="xl"
        v-model=" scheduleModal "
        scrollable
    >
        <schedule-requests :selected-schedule=" selectedSchedule " v-if=" scheduleModal && selectedSchedule "></schedule-requests>
    </b-modal>
  </div>
</template>

<script>

    import FormatsDates from '../../../mixins/FormatsDates';
    import AuthUser from '../../../mixins/AuthUser';
    import ScheduleRequests from './ScheduleRequests';

    export default {

        props: [ 'businesses', 'role_type' ],
        data() {

            return {

                loading          : false,
                filtersReady     : false,
                // clients          : this.client ? [this.client] : [],
                // caregivers       : this.caregiver ? [this.caregiver] : [],
                events           : [],
                eventsLoaded     : false,
                active_business  : null,
                isBusy           : false,
                selectedSchedule : null,
                scheduleModal    : false,
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
                    },
                    // {
                    //   key: 'created_at',
                    //   label: 'First date referred',
                    //   sortable: true,
                    //   shouldShow: true,
                    //   formatter: x => { return this.formatDateFromUTC(x) }
                    // },
                ]
            }
        },

        mounted() {

            this.loadFiltersData();
            if( !Array.isArray( this.businesses ) ) this.active_business = this.businesses;
            else this.active_business = this.businesses[ 0 ].id || null;
        },

        computed: {

            eventsUrl() {

                if ( !this.filtersReady ) {

                    return '';
                }

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

                /*

                if (this.filterCaregiverId > -1) {
                    url += '&caregiver_id=' + this.filterCaregiverId;
                    if (this.filterClientId > -1) {
                        url += '&client_id=' + this.filterClientId;
                    }
                }
                else if (this.filterClientId > -1) {
                    url += '&client_id=' + this.filterClientId;
                }

                if (this.filterBusinessId) {
                    url += '&businesses[]=' + this.filterBusinessId;
                }
                */

                return url;
            },

            /*
                rememberFilters() {
                    return this.isFilterable && this.officeUserSettings.calendar_remember_filters;
                },

                calendarHeight() {
                    return 'auto';
                    // return window.innerHeight - (this.fullscreen ? 180 : 400);
                },

                config() {
                    return {
                        height: this.calendarHeight,
                        eventBorderColor: '#333',
                        eventOverlap: false,
                        nextDayThreshold: this.officeUserSettings.calendar_next_day_threshold || '09:00:00',
                        nowIndicator: true,
                        resourceAreaWidth: '280px',
                        resourceColumns: [
                            {
                                labelText: this.resourceIdField === 'client_id' ? 'Client' : 'Caregiver',
                                field: 'title',
                            },
                            {
                                labelText: 'S',
                                field: 'scheduled',
                                width: '30px',
                            },
                            {
                                labelText: 'C',
                                field: 'completed',
                                width: '30px',
                            },
                            {
                                labelText: 'P',
                                field: 'projected',
                                width: '30px',
                            }
                        ],
                        resourceRender: this.resourceRender,
                        views: {
                            timelineWeek: {
                                slotLabelFormat: 'ddd D',
                                slotDuration: '24:00'
                            },
                        },
                        customButtons: {
                            caregiverView: {
                                text: this.caregiverView ? 'Client View' : 'Caregiver View',
                                click: this.caregiverViewToggle
                            },
                            fullscreen: {
                                text: ' ',
                                click: this.fullscreenToggle
                            },
                            print: {
                                text: ' ',
                                click: this.printCalendar
                            }
                        },
                        firstDay: this.weekStart,
                    }
                },

                filteredEvents() { return this.getFilteredEvents(); },

                kpis() { return this.getKpis(); },

                resources() { return this.getResources(); },

                filteredCaregiverResources() {
                    return (this.filterCaregiverId > -1 && !this.caregiver);
                },

                filteredClientResources() {
                    return (this.filterClientId > -1 && !this.client) || this.filterClientId === -2;
                },

                currentClient() {
                    if (this.clients && this.filterClientId !== -1) {
                        return this.clients.find(x => x.id === this.filterClientId);
                    }
                    return this.client;
                },

                currentCaregiver() {
                    if (this.caregivers && this.filterCaregiverId !== -1) {
                        return this.caregivers.find(x => x.id === this.filterCaregiverId);
                    }
                    return this.caregiver;
                }
            */
        },

        methods: {

            showRequestModal( schedule_id ){

                this.selectedSchedule = schedule_id;
                this.scheduleModal = true;
            },
            hasRequest( status ){

                switch( status ){

                    case 'pending':
                    case 'denied':
                    case 'approved':
                        return true;

                        break;
                    case 'cancelled':
                    default:

                        return false;
                        break;
                }
            },
            requestShift( schedule ){

                this.isBusy = true;
                const form = new Form();

                form.post( `/schedule/open-shifts/${schedule.id}` )
                    .then( res => {

                        schedule.request_status = res.data.data.status;
                    })
                    .catch( e => {

                        console.log( 'error requesting shift', e );
                    })
                    .finally( () => {

                        this.isBusy = false;
                    });
            },
            loadFiltersData() {

                this.filtersReady = true;
            },
            fetchEvents( savePosition = false ) {

                if ( !this.filtersReady ) {

                return;
                }

                this.loading = true;

                const form = new Form();

                form.get( this.eventsUrl )
                    .then( ({ data }) => {

                        this.events = data.events.map( event => {

                            event.resourceId      = event[ this.resourceIdField ];
                            return event;
                        });

                        this.eventsLoaded = true;
                    })
                    .catch( e => {

                        console.log( 'error getting events:' );
                        console.log( e );
                    })
                    .finally( () => {

                        this.loading = false;
                    });
            },
        },

        watch: {

            eventsUrl( val, old ) {

                this.fetchEvents();
            },
        },

        mixins: [

            FormatsDates,
            AuthUser
        ],

        components: {

            ScheduleRequests
        }
    }
</script>

<style scoped>

</style>