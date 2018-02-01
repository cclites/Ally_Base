<template>
    <b-card>
        <b-row>
            <b-col md="7">
                <b-btn size="sm" variant="info" @click="createSchedule()"><i class="fa fa-plus"></i> Schedule Shift</b-btn>
                <b-btn size="sm" variant="primary" @click="bulkUpdateModal = !bulkUpdateModal">Update Schedules</b-btn>
                <b-btn size="sm" variant="danger" @click="bulkDeleteModal = !bulkDeleteModal">Delete Schedules</b-btn>
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

        <business-schedule-modal :model.sync="scheduleModal"
                               :selected-event="selectedEvent"
                               :selected-schedule="selectedSchedule"
                               :initial-values="initialCreateValues"
                               @refresh-events="refreshEvents()"
        />

        <bulk-edit-schedule-modal v-model="bulkUpdateModal"
                                  :caregiver-id="filterCaregiverId"
                                  :client-id="filterClientId"
                                  @refresh-events="refreshEvents()"
        />

        <bulk-delete-schedule-modal v-model="bulkDeleteModal"
                                    :caregiver-id="filterCaregiverId"
                                    :client-id="filterClientId"
                                    @refresh-events="refreshEvents()"
        />
    </b-card>
</template>

<script>
    import ManageCalendar from '../../../mixins/ManageCalendar';

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
                bulkUpdateModal: false,
                bulkDeleteModal: false,
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
            },
            initialCreateValues() {
                return {
                    'client_id': (this.filterClientId > 0) ? this.filterClientId : "",
                    'caregiver_id': (this.filterCaregiverId > 0) ? this.filterCaregiverId : "",
                }
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