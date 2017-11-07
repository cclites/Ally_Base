<template>
    <b-card>
        <b-btn size="sm" variant="info" @click="createSchedule()"><i class="fa fa-plus"></i> Create a Schedule</b-btn>
        <b-btn size="lg" variant="info" class="pull-right"><i class="fa fa-clock-o"></i> Clock In</b-btn>
        <full-calendar ref="calendar" :events="events" default-view="agendaWeek" :header="header" @day-click="createSchedule" @event-selected="editSchedule"  />

        <create-schedule-modal :model.sync="createModal"
                               :selected-event="selectedEvent"
                               @refresh-events="refreshEvents()"
        ></create-schedule-modal>

        <edit-schedule-modal :model.sync="editModal"
                             :selected-event="selectedEvent"
                             :selected-schedule="selectedSchedule"
                             @refresh-events="refreshEvents()"
        ></edit-schedule-modal>
    </b-card>
</template>

<script>
    import ManageCalendar from '../mixins/ManageCalendar';

    export default {

        data() {
            return {
                events: '/business/schedule/events',
                header: {
                    left:   'prev,next today',
                    center: 'title',
                    right:  'listDay,agendaWeek,month'
                }
            }
        },

        mixins: [ManageCalendar]
    }
</script>