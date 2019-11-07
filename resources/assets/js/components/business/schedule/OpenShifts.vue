<template>

  <div>

    <b-card :title=" active_business ? active_business.name : '' ">

      <loading-card v-show=" loading " />

      <div v-show="! loading" class="table-responsive">

          <ally-table id="open-shifts" :columns=" fields " :items=" events " sort-by="start" :perPage=" 1000 " :isBusy=" isBusy ">

            <template slot="start" scope="data">

                {{ formatDateFromUTC( data.item.start ) + ' ' + data.item.start_time + '-' + data.item.end_time }}
            </template>
            <template slot="actions" scope="data">

                <transition mode="out-in" name="slide-fade">

                    <b-button variant="success" size="sm" class="btn-block" v-if=" !hasRequest( data.item.request_status ) " @click=" requestShift( data.item, 'pending' ) " key="request">Request Shift</b-button>
                    <b-button variant="default" size="sm" class="btn-block" v-if=" hasRequest( data.item.request_status ) " @click=" requestShift( data.item, 'cancelled' ) " key="rescind">Cancel Request</b-button>
                </transition>
            </template>
            <template slot="requests_count" scope="data">

                <a href="#" @click.prevent=" showRequestModal( data.item.id ) " v-if=" data.item.requests_count > 0 ">{{ data.item.requests_count }} Request{{ data.item.requests_count > 1 ? 's' : '' }}</a>
                <span v-else>0</span>
            </template>

            <template slot="status" scope="data">Open</template>
          </ally-table>
      </div>
    </b-card>

    <b-modal id="schedule-requests-modal"
        title="Schedule Requests"
        size="xl"
        v-model=" scheduleModal "
        scrollable
    >
        <schedule-requests :selected-schedule-id=" selectedSchedule " v-if=" scheduleModal && selectedSchedule "></schedule-requests>
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
                events           : [],
                eventsLoaded     : false,
                active_business  : null,
                isBusy           : false,
                selectedSchedule : null,
                scheduleModal    : false,
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

            showRequestModal( schedule_id ){

                this.selectedSchedule = schedule_id;
                this.scheduleModal    = true;
            },
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
                const form = new Form({

                    status : status
                });

                form.post( `/schedule/requests/${schedule.id}` )
                    .then( res => {

                        console.log( res );
                        schedule.request_status = res.data.data.status;
                    })
                    .catch( e => {

                        console.log( 'error requesting shift', e );
                    })
                    .finally( () => {

                        this.isBusy = false;
                    });
            },
            fetchEvents( savePosition = false ) {

                this.loading = true;

                const form = new Form();

                form.get( this.eventsUrl )
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
            AuthUser
        ],

        components: {

            ScheduleRequests
        }
    }
</script>

<style scoped>

</style>