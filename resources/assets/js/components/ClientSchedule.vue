<template>
    <b-card>
        <b-btn size="sm" variant="info" @click="createSchedule()"><i class="fa fa-plus"></i> Create a Schedule</b-btn>
        <full-calendar ref="calendar" :events="events" @day-click="createSchedule" @event-selected="editSchedule" />

        <create-schedule-modal :model.sync="createModal"
                               :client="client"
                               :selected-event="selectedEvent"
                               @refresh-events="refreshEvents()"
        ></create-schedule-modal>

        <edit-schedule-modal :model.sync="editModal"
                               :client="client"
                               :selected-event="selectedEvent"
                               :selected-schedule="selectedSchedule"
                               @refresh-events="refreshEvents()"
        ></edit-schedule-modal>

    </b-card>
</template>

<script>
    import ManageCalendar from '../mixins/ManageCalendar';

    export default {
        props: {
            client: {},
        },

        data() {
            return {
                events: '/business/clients/' + this.client.id + '/schedule',
            }
        },

        mixins: [ManageCalendar]
    }
</script>