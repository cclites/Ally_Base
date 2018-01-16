<template>
    <b-card>
        <b-row>
            <b-col md="7">
                <b-btn size="sm" variant="info" @click="createSchedule()"><i class="fa fa-plus"></i> Schedule Shift</b-btn>
            </b-col>
            <b-col md="5">
                <b-row v-if="isFilterable()">
                    <b-col cols="6">
                        <b-form-select v-model="filterCaregiverId">
                            <option :value="-1">All Caregivers</option>
                            <option :value="0">Unassigned Shifts</option>
                            <option v-for="item in caregivers" :value="item.id">{{ item.nameLastFirst }}</option>
                        </b-form-select>
                    </b-col>
                    <b-col cols="6">
                        <b-form-select v-model="filterClientId">
                            <option :value="-1">All Clients</option>
                            <option v-for="item in clients" :value="item.id">{{ item.nameLastFirst }}</option>
                        </b-form-select>
                    </b-col>
                </b-row>
            </b-col>
        </b-row>
        <full-calendar ref="calendar" :events="filteredEventsUrl" :default-view="defaultView" :header="header" @day-click="createSchedule" @event-selected="editSchedule"  />

        <create-schedule-modal :model.sync="createModal"
                               :selected-event="selectedEvent"
                               @refresh-events="refreshEvents()"
        ></create-schedule-modal>

        <!--<edit-schedule-modal :model.sync="editModal"-->
                             <!--:selected-event="selectedEvent"-->
                             <!--:selected-schedule="selectedSchedule"-->
                             <!--@refresh-events="refreshEvents()"-->
        <!--&gt;</edit-schedule-modal>-->
    </b-card>
</template>

<script>
    import ManageCalendar from '../mixins/ManageCalendar';

    export default {
        props: {
            'caregiver': Object,
            'client': Object,
            'defaultView': {
                default() {
                    return 'month';
                }
            }
        },

        data() {
            return {
                filterCaregiverId: (this.caregiver) ? this.caregiver.id : -1,
                filterClientId: (this.client) ? this.client.id : -1,
                header: {
                    left:   'prev,next today',
                    center: 'title',
                    right:  'listDay,agendaWeek,month'
                },
                clients: [],
                caregivers: [],
                events: '/business/schedule/events',
            }
        },

        mounted() {
            if (this.isFilterable()) {
                this.loadFiltersData();
            }
        },

        computed: {
            filteredEventsUrl() {
                let url = this.events;
                if (this.filterCaregiverId > -1) {
                    url = url + '?caregiver_id=' + this.filterCaregiverId;
                    if (this.filterClientId > -1) {
                        url = url + '&client_id=' + this.filterClientId;
                    }
                }
                else if (this.filterClientId > -1) {
                    url = url + '?client_id=' + this.filterClientId;
                }
                return url;
            }
        },

        methods: {
            loadFiltersData() {
                axios.get('/business/settings?json=1').then(response => {
                    if (response.data.calendar_caregiver_filter === 'unassigned') {
                        this.filterCaregiverId = 0;
                    }
                });
                axios.get('/business/clients').then(response => this.clients = response.data);
                axios.get('/business/caregivers').then(response => this.caregivers = response.data);
            },
            isFilterable() {
                if (this.client || this.caregiver) return false;
                return true;
            }
        },

        mixins: [ManageCalendar]
    }
</script>