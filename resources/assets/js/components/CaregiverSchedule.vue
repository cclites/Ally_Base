<template>
    <div>
        <b-card>
            <b-row>
                <b-col lg="12" class="text-center">
                    <b-btn href="/clock-in" size="lg" variant="info" class="btn-block">Press Here To Clock In</b-btn>
                </b-col>
            </b-row>
            <full-calendar ref="calendar" :events="events" defaultView="listWeek" @event-selected="viewDetails" :header="header" />
        </b-card>
        <b-modal id="view-event" title="View Scheduled Shift" v-model="viewModal">
            <b-container fluid>
                <b-row>
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
                            <th>{{ viewTitle }}</th>
                        </tr>
                    </table>
                </b-row>
            </b-container>
            <div slot="modal-footer">
                <b-btn variant="default" @click="viewModal=false">Close</b-btn>
                <b-btn variant="info" @click="clockIn()">Clock In</b-btn>
            </div>
        </b-modal>
    </div>
</template>

<script>
    export default {
        props: {
            caregiver: {},
        },

        data() {
            return {
                viewModal: false,
                selectedSchedule: null,
                selectedEvent: null,
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
                    right:  'listWeek,agendaWeek'
                }
            }
        },

        mounted() {


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
            }

        }
    }
</script>