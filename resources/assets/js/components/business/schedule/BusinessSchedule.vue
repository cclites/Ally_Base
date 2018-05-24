<template>
    <b-card>
        <b-row>
            <b-col md="7">
                <b-row>
                    <b-col>
                        <b-btn size="sm" variant="info" @click="createSchedule()"><i class="fa fa-plus"></i> Schedule Shift</b-btn>
                        <b-btn size="sm" variant="primary" @click="bulkUpdateModal = !bulkUpdateModal">Update Schedules</b-btn>
                        <b-btn size="sm" variant="danger" @click="bulkDeleteModal = !bulkDeleteModal">Delete Schedules</b-btn>
                    </b-col>
                </b-row>
                <b-row>
                    <b-col class="mt-3">
                        <strong>Hours Assigned | Unassigned: </strong> {{ kpis.assigned_hours }} | {{ kpis.unassigned_hours }}<br/>
                        <strong>Unassigned Shifts: </strong> {{ kpis.unassigned_shifts }}
                    </b-col>
                </b-row>
            </b-col>
            <b-col md="5">
                <b-row v-if="isFilterable">
                    <b-col cols="6">
                        <b-form-group label="Caregiver Filter" label-for="calendar_caregiver_filter">
                            <b-form-select v-model="filterCaregiverId" id="calendar_caregiver_filter">
                                <option :value="-1">All Caregivers</option>
                                <option :value="0">Unassigned Shifts</option>
                                <option v-for="item in caregivers" :value="item.id" :key="item.id">{{ item.nameLastFirst }}</option>
                            </b-form-select>
                        </b-form-group>
                    </b-col>
                    <b-col cols="6">
                        <b-form-group label="Client Filter" label-for="calendar_client_filter">
                            <b-form-select v-model="filterClientId" id="calendar_client_filter">
                                <option :value="-1">All Clients</option>
                                <option v-for="item in clients" :value="item.id" :key="item.id">{{ item.nameLastFirst }}</option>
                            </b-form-select>
                        </b-form-group>
                    </b-col>
                </b-row>
            </b-col>
        </b-row>

        <full-calendar ref="calendar"
            :events="events"
            :default-view="defaultView"
            :header="header"
            @day-click="createSchedule"
            @event-selected="editSchedule"
            @event-render="renderEvent"
            @view-render="onLoadView"
            :loading="loading"
        />

        <schedule-notes-modal v-model="notesModal"
                                :event="selectedEvent"
                                @updateEvent="updateEvent"
        />

        <business-schedule-modal :model.sync="scheduleModal"
                               :selected-event="selectedEvent"
                               :selected-schedule="selectedSchedule"
                               :initial-values="initialCreateValues"
                               @refresh-events="fetchEvents(true)"
                               @clock-out="showClockOutModal()"
        />

        <bulk-edit-schedule-modal v-model="bulkUpdateModal"
                                  :caregiver-id="filterCaregiverId"
                                  :client-id="filterClientId"
                                  @refresh-events="fetchEvents(true)"
        />

        <bulk-delete-schedule-modal v-model="bulkDeleteModal"
                                    :caregiver-id="filterCaregiverId"
                                    :client-id="filterClientId"
                                    @refresh-events="fetchEvents(true)"
        />

        <schedule-clock-out-modal v-model="clockOutModal"
                                    :shift="selectedSchedule.clocked_in_shift"
                                    @refresh="fetchEvents(true)"
        ></schedule-clock-out-modal>
    </b-card>
</template>

<script>
    import ManageCalendar from '../../../mixins/ManageCalendar';
    import LocalStorage from "../../../mixins/LocalStorage";

    export default {
        props: {
            'business': Object,
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
                loading: false,
                filtersReady: false,
                filterCaregiverId: (this.caregiver) ? this.caregiver.id : -1,
                filterClientId: (this.client) ? this.client.id : -1,
                header: {
                    left:   'prev,next today',
                    center: 'title',
                    right:  'listDay,agendaWeek,month'
                },
                clients: [],
                caregivers: [],
                bulkUpdateModal: false,
                bulkDeleteModal: false,
                notesModal: false,
                clockOutModal: false,
                selectedEvent: {},
                events: [],
                start: '',
                end: '',
                kpis: {
                    assigned_hours: 0,
                    unassigned_hours: 0,
                    unassigned_shifts: 0,
                },
                localStoragePrefix: 'business_schedule_',
                resetScrollPosition: false,
                scroll: { top: null, left: null },
            }
        },

        mounted() {
            this.loadFiltersData();
        },

        computed: {
            eventsUrl() {
                if (!this.filtersReady) {
                    return '';
                }

                let url = '/business/schedule/events?';

                if (this.filterCaregiverId > -1) {
                    url = url + 'caregiver_id=' + this.filterCaregiverId;
                    if (this.filterClientId > -1) {
                        url = url + '&client_id=' + this.filterClientId;
                    }
                }
                else if (this.filterClientId > -1) {
                    url = url + 'client_id=' + this.filterClientId;
                }

                url += '&start=' + this.start;
                url += '&end=' + this.end;

                return url;
            },

            initialCreateValues() {
                return {
                    'client_id': (this.filterClientId > 0) ? this.filterClientId : "",
                    'caregiver_id': (this.filterCaregiverId > 0) ? this.filterCaregiverId : "",
                }
            },

            isFilterable() {
                if (this.client || this.caregiver) return false;
                return true;
            },

            rememberFilters() {
                return this.isFilterable && this.business && this.business.calendar_remember_filters;
            }
        },

        methods: {
            saveScrollPosition() {
                this.scroll = {
                    top: $(window).scrollTop(),
                    left: $(window).scrollLeft(),
                }
            },

            clearScrollPosition() {
                this.scroll = {
                    top: null,
                    left: null,
                };
            },

            setScrollPosition() {
                if (this.scroll.top !== null) {
                    console.log('setScrollPosition called');
                    $(window).scrollTop(this.scroll.top);
                    $(window).scrollLeft(this.scroll.left);
                }
            },

            showClockOutModal() {
                this.clockOutModal = true;
            },

            onLoadView(view, element) {
                this.start = view.start.format('YYYY-MM-DD');
                this.end = view.end.format('YYYY-MM-DD');
                this.fetchEvents();
            },

            fetchEvents(savePosition = false) {
                if (!this.filtersReady) {
                    return;
                }
                savePosition ? this.saveScrollPosition() : this.clearScrollPosition();
                this.events = [];
                this.loading = true;
                axios.get(this.eventsUrl)
                    .then( ({ data }) => {
                        this.events = data.events;
                        this.kpis = data.kpis;
                        this.loading = false;
                    })
                    .catch(e => {
                        this.loading = false;
                        console.log('error getting events:');
                        console.log(e);
                    })
            },

            updateEvent(id, data) {
                this.saveScrollPosition();
                let event = this.events.find(item => {
                    return item.id === id;
                });
                if (event) {
                    event.backgroundColor = data.backgroundColor;
                    event.note = data.note;
                    event.status = data.status;
                }
            },

            loadFiltersData() {
                if (this.isFilterable) {
                    // Load the default filter values
                    if (this.business) {
                        if (this.business.calendar_caregiver_filter === 'unassigned') {
                            this.filterCaregiverId = 0;
                        }
                        if (this.rememberFilters) {
                            let localCaregiverId = this.getLocalStorage('caregiver');
                            if (localCaregiverId !== null) this.filterCaregiverId = localCaregiverId;
                            let localClientId = this.getLocalStorage('client');
                            if (localClientId !== null) this.filterClientId = localClientId;
                        }
                    }

                    // Fill the caregiver and client drop downs
                    axios.get('/business/clients').then(response => this.clients = response.data);
                    axios.get('/business/caregivers').then(response => this.caregivers = response.data);
                }
                this.filtersReady = true;
            },

            renderEvent: function( event, element, view ) {
                let note = $('<span/>', {
                    class: 'fc-note-btn',
                    html: $('<i/>', {
                        class: event.note ? 'fa fa-commenting' : 'fa fa-comment',
                    }),
                });

                let vm = this;
                note.click((e) => {
                    vm.selectedEvent = event;
                    vm.notesModal = true;
                    e.preventDefault();
                    e.stopPropagation();
                });

                let data = [`C: ${event.client}`, `CG: ${event.caregiver}`, `${event.start_time} - ${event.end_time}`];
                let title = $('<span/>', {
                    class: 'fc-title',
                    html: data.join('<br/>'),
                });

                let content = element.find('.fc-content');
                if (view.name == 'agendaWeek') {
                    content.html($('<div/>').append(note, title));
                } else {
                    content.html(title);
                    content.parent().prepend(note);
                }

                this.resetScrollPosition = true;
            },
        },

        watch: {
            filterCaregiverId(val) {
                this.fetchEvents();
                if (this.rememberFilters) {
                    this.setLocalStorage('caregiver', val);
                }
            },

            filterClientId(val) {
                this.fetchEvents();
                if (this.rememberFilters) {
                    this.setLocalStorage('client', val);
                }
            },

            filtersReady(val) {
                if (val) this.fetchEvents();
            },

            resetScrollPosition(val, old) {
                if (val && val !== old) {
                    setTimeout(() => {
                        this.setScrollPosition();
                        this.resetScrollPosition = false;
                    }, 10);
                }
            }
        },

        mixins: [ManageCalendar, LocalStorage]
    }
</script>

<style>
.fc-event { text-align: left!important; }
.fc-note-btn { float: right!important; z-index: 99; padding-left: 5px }
.fc-event { cursor: pointer; }
.fc-note-btn:hover {
    filter: brightness(85%);
    cursor: pointer;
}
.fa-commenting {
    color: #F2F214;
}
</style>