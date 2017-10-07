<template>
    <b-card>
        <full-calendar ref="calendar" :events="events" defaultView="listWeek" @event-selected="clockIn" />
    </b-card>
</template>

<script>
    export default {
        props: {
            caregiver: {},
        },

        data() {
            return {
                createModal: false,
                editModal: false,
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
                }
            }
        },

        mounted() {

        },

        methods: {
            refreshEvents(hideModals = true) {
                this.$refs.calendar.fireMethod('refetchEvents');
                if (hideModals) {
                    this.createModal = false;
                    this.editModal = false;
                }
            },

            clockIn(event, jsEvent, view) {
                console.log(event);
                window.location = '/clock-in/' + event.id;
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
            }

        }
    }
</script>