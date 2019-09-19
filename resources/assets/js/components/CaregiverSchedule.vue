<template>
    <div>
        <b-card>
            <b-row>
                <b-col lg="12" class="text-center">
                    <b-btn href="/clock-in" size="lg" variant="info" class="btn-block" :disabled="authInactive">Press Here To Clock In</b-btn>
                </b-col>
            </b-row>
            <full-calendar ref="calendar" :events="events" defaultView="listDay" @event-selected="viewDetails" :header="header" />
        </b-card>
        <b-modal id="view-event" title="View Scheduled Shift" v-model="viewModal" size="xl" scrollable>
            <b-container fluid>
                <b-row>
                    <b-col>
                        <table class="table">
                            <tr>
                                <th>Start:</th>
                                <td>{{ viewStartTime }}</td>
                            </tr>
                            <tr>
                                <th>End:</th>
                                <td>{{ viewEndTime }}</td>
                            </tr>
                            <tr>
                                <th>Client:</th>
                                <th>
                                    {{ viewTitle }}
                                    <b-button size="sm" @click="fetchClient(selectedEvent.client_id)" v-if="!selectedClient.id">Show Details</b-button>
                                    <b-button size="sm" @click="selectedClient = {}" v-else>Hide Details</b-button>
                                </th>
                            </tr>
                        </table>
                        <loading-card v-show="loadingClient"></loading-card>
                            <caregiver-client-details v-if="selectedClient.id"
                                                      :client="selectedClient"
                                                      :care-plan="selectedEvent.care_plan || {}"
                                                      :address="selectedClient.evv_address || {}"
                                                      :phone="selectedClient.evv_phone ? selectedClient.evv_phone.number : ''"
                                                      :care-details="selectedClient.care_details || {}"
                            />
                    </b-col>
                </b-row>
            </b-container>
            <div slot="modal-footer">
                <b-btn variant="default" @click="viewModal=false">Close</b-btn>
                <b-btn variant="info" @click="clockIn()" v-if="canClockIn" :disabled="authInactive">Go to Clock In</b-btn>
            </div>
        </b-modal>
    </div>
</template>

<script>
    import CaregiverClientDetails from "./caregivers/CaregiverClientDetails";
    import AuthUser from '../mixins/AuthUser';

    export default {
        components: {CaregiverClientDetails, AuthUser},

        props: {
            caregiver: {},
        },

        data() {
            return {
                viewModal: false,
                selectedSchedule: null,
                selectedEvent: null,
                selectedClient: {},
                loadingClient: false,
                editForm: new Form(),
                createForm: new Form(),
                editType: null,
                createType: null,
                interval: 15, // number of minutes in between each time period
                daysOfWeek: {
                    'Sunday': 'su',
                    'Monday': 'mo',
                    'Tuesday': 'tu',
                    'Wednesday': 'we',
                    'Thursday': 'th',
                    'Friday': 'fr',
                    'Saturday': 'sa',
                },
                header: {
                    left:   'prev,next today',
                    center: 'title',
                    right:  'listDay,agendaWeek'
                }
            }
        },
        methods: {
            refreshEvents(hideModals = true) {
                this.$refs.calendar.fireMethod('refetchEvents');
                if (hideModals) {
                    this.viewModal = false;
                }
            },

            viewDetails(event, jsEvent, view) {
                console.log(event);
                this.selectedEvent = event;
                this.viewModal = true;
            },

            clockIn() {
                window.location = '/clock-in/' + this.selectedEvent.id;
            },

            fetchClient(id) {
                this.selectedClient = {};
                this.loadingClient = true;
                axios.get('/caregiver/clients/' + id).then(response => {
                    this.selectedClient = response.data;
                }).finally(() => this.loadingClient = false);
            }
        },

        watch: {
        },

        computed: {

            events() {
                if (this.caregiver) {
                    return '/business/caregivers/' + this.caregiver.id + '/schedule';
                }
                return '/schedule/events';
            },

            viewStartTime() {
                if (!this.selectedEvent) return '';
                return moment(this.selectedEvent.start).local().format('L LT');
            },

            viewEndTime() {
                if (!this.selectedEvent) return '';
                return moment(this.selectedEvent.end).local().format('L LT');
            },

            viewTitle() {
                if (!this.selectedEvent) return '';
                return this.selectedEvent.title;
            },

            canClockIn() {
                if (this.selectedEvent) {
                    let now = moment();
                    let start = moment(this.selectedEvent.start);
                    let end = moment(this.selectedEvent.end);
                    if (now >= start && now <= end.add(2, 'hours')) {
                        return true;
                    }
                    if (now <= end && now >= start.subtract(2, 'hours')) {
                        return true;
                    }
                }

                return false;
            },

        }
    }
</script>

<style scoped>
    caregiver-client-details{
        overflow-y: visible;
    }
</style>