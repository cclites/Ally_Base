<template>
    <b-card>
        <full-calendar ref="calendar" :events="events" default-view="agendaWeek" :header="header" @event-selected="alert('Coming soon! Please manage scheduling from the Client section')" @day-click="alert('Coming soon! Please manage scheduling from the Client section')"  />
    </b-card>
</template>

<script>
    export default {
        props: {
        },

        data() {
            return {
                events: '/business/schedule/events',
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
                },
                header: {
                    left:   'prev,next today',
                    center: 'title',
                    right:  'listDay,agendaWeek,month'
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
            alert(message) {
                alert(message)
            }
        },

        watch: {
        },

        computed: {
        }
    }
</script>