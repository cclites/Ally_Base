<template>
    <div>
        <b-card>
            <full-calendar ref="calendar" :events="events" defaultView="listDay" @event-selected="viewDetails" :header="header" />
        </b-card>
        <b-modal id="view-event" title="View Scheduled Shift" v-model="viewModal">
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
                                <th>Caregiver:</th>
                                <td>
                                    <div class="mb-2">{{ viewTitle }}</div>
                                    <b-button v-if="!selectedCaregiver.id" size="sm" @click="fetchCaregiver(selectedEvent.caregiver_id)">Show Details</b-button>
                                    <b-button v-else size="sm" @click="selectedCaregiver = {}">Hide Details</b-button>
                                </td>
                            </tr>

                        </table>
                        <loading-card v-show="loadingCaregiver" text=""></loading-card>
                        <client-caregiver-details v-if="selectedCaregiver.id"
                                                  :caregiver="selectedCaregiver"
                                                  :address="selectedCaregiver.address || {}"
                                                  :phone="selectedCaregiver.phone_number ? selectedCaregiver.phone_number.number : ''"
                        />
                    </b-col>
                </b-row>
            </b-container>
            <div slot="modal-footer">
                <b-btn variant="default" @click="viewModal=false">Close</b-btn>
            </div>
        </b-modal>
    </div>
</template>

<script>
    import ClientCaregiverDetails from "./clients/ClientCaregiverDetails";
    import AuthUser from '../mixins/AuthUser';

    export default {
        components: {ClientCaregiverDetails, AuthUser},

        props: {},

        data() {
            return {
                viewModal: false,
                selectedSchedule: null,
                selectedEvent: null,
                selectedCaregiver: {},
                selectedCaregiverAddress: {},
                selectedCaregiverPhone: {},
                loadingCaregiver: false,
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
                    left: 'prev,next today',
                    center: 'title',
                    right: 'listDay,agendaWeek'
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
                this.selectedCaregiver = {};
                this.selectedEvent = event;
                this.viewModal = true;
            },

            fetchCaregiver(id) {
                this.selectedCaregiver = {};
                this.loadingCaregiver = true;
                axios.get('/caregiver/' + id)
                    .then(response => {
                        this.selectedCaregiver = response.data.caregiver;
                    })
                    .catch(() => {
                    })
                    .finally(() => this.loadingCaregiver = false);
            }
        },

        computed: {
            events() {
                return 'scheduled-shifts/' + this.authUser.id + '/schedule';
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
        }
    }
</script>