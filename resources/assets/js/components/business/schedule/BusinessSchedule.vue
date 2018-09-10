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
                        <strong>Hours Assigned | Open: </strong> {{ kpis.assigned_hours }} | {{ kpis.unassigned_hours }}<br/>
                        <strong>Open Shifts: </strong> {{ kpis.unassigned_shifts }}
                    </b-col>
                </b-row>
            </b-col>
            <b-col md="5">
                <b-row>
                    <b-col cols="6" class="ml-auto" v-if="caregivers.length">
                        <b-form-group label="Caregiver Filter" label-for="calendar_caregiver_filter">
                            <b-form-select v-model="filterCaregiverId" id="calendar_caregiver_filter">
                                <option :value="-1">All Caregivers</option>
                                <option :value="0">Open Shifts</option>
                                <option v-for="item in caregivers" :value="item.id" :key="item.id">{{ item.nameLastFirst }}</option>
                            </b-form-select>
                        </b-form-group>
                    </b-col>
                    <b-col cols="6" class="ml-auto" v-if="clients.length">
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

        <loading-card v-show="loading" v-if="!resourcesLoaded" />
        <full-calendar ref="calendar"
            :events="events"
            :resources="resources"
            :default-view="defaultView"
            :header="header"
            :config="config"
            @day-click="createSchedule"
            @event-selected="editSchedule"
            @event-render="renderEvent"
            @view-render="onLoadView"
            :loading="loading"
            v-else
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
                    return 'timelineWeek';
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
                    right:  'timelineDay,timelineWeek,month'
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
                resourceIdField: 'client_id',
                eventsLoaded: false, // initial events load
                caregiversLoaded: !!this.caregiver,
                clientsLoaded: !!this.client,
            }
        },

        mounted() {
            this.appendColorKey();
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

            rememberFilters() {
                return this.isFilterable && this.business && this.business.calendar_remember_filters;
            },

            resourcesLoaded() {
                return this.eventsLoaded && this.caregiversLoaded && this.clientsLoaded;
            },

            resources() {
                if (!this.resourcesLoaded) return [];

                let items = this.clients;
                this.resourceIdField = 'client_id';

                if (this.client) {
                    items = this.caregivers;
                    this.resourceIdField = 'caregiver_id';
                }

                let resources = items.map(item => {
                    return {
                        id: item.id,
                        title: item.nameLastFirst
                    };
                });

                if (this.client) {
                    resources.unshift({
                        id: 0,
                        title: 'Open Shifts',
                    });
                }

                return resources;

                // Filtering freezes browser, debug later
                return resources.filter(resource => {
                    return this.events.findIndex(event => event[this.resourceIdField] == resource.id) !== -1;
                });
            },

            config() {
                return {
                    nextDayThreshold: this.business ? this.business.calendar_next_day_threshold : '09:00:00',
                    resourceLabelText: this.resourceIdField === 'client_id' ? 'Client' : 'Caregiver',
                    resourceAreaWidth: '250px',
                    views: {
                        timelineWeek: {
                            slotDuration: '24:00'
                        },
                    }
                }
            },
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
                this.loadKpiToolbar();
            },

            loadKpiToolbar() {
                let $toolbar = $('.fc-toolbar .fc-center');
                let $element = $toolbar.find('h6');
                if (!$element.length) $element = $toolbar.append('<h6/>').find('h6');
                $element.html(`Scheduled Hours: 555 &nbsp; Completed Hours: 762 &nbsp; Projected Hours: 1243`)
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
                        this.events = data.events.map(event => {
                            event.resourceId = event[this.resourceIdField];
                            return event;
                        });
                        this.kpis = data.kpis;
                        this.eventsLoaded = true;
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
                let clientIsFilterable = !this.client;
                let caregiverIsFilterable = !this.caregiver;

                // Load the default filter values
                if (this.business) {
                    if (caregiverIsFilterable && this.business.calendar_caregiver_filter === 'unassigned') {
                        this.filterCaregiverId = 0;
                    }

                    if (this.rememberFilters) {
                        if (caregiverIsFilterable) {
                            let localCaregiverId = this.getLocalStorage('caregiver');
                            if (localCaregiverId !== null) this.filterCaregiverId = localCaregiverId;
                        }
                        if (clientIsFilterable) {
                            let localClientId = this.getLocalStorage('client');
                            if (localClientId !== null) this.filterClientId = localClientId;
                        }
                    }
                }

                // Fill the caregiver and client drop downs
                let count = 0;
                if (clientIsFilterable) {
                    axios.get('/business/clients').then(response => {
                        this.clients = response.data;
                        this.clientsLoaded = true;
                    });
                }
                if (caregiverIsFilterable) {
                    let url = '/business/caregivers';
                    if (this.client) url = '/business/clients/' + this.client.id + '/caregivers';
                    axios.get(url).then(response => {
                        this.caregivers = response.data;
                        this.caregiversLoaded = true;
                    });
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

                let content = element.find('.fc-content');
                if (view.name == 'agendaWeek') {
                    this.renderAgendaWeekEvent(content, event, note);
                } else if (view.name == 'timelineDay') {
                    this.renderTimelineDayEvent(content, event, note);
                } else if (view.name == 'timelineWeek') {
                    this.renderTimelineWeekEvent(content, event, note);
                } else {
                    this.renderDefaultEvent(content, event, note);
                }

                this.resetScrollPosition = true;
            },

            appendColorKey() {
                $('.fc-toolbar .fc-right').append(`
<button type="button" class="fc-button fc-state-default fc-corner-left fc-corner-right hidden-sm-down dropdown-toggle" data-toggle="dropdown">Color Key</button>
  <div class="dropdown-menu">
    <a class="dropdown-item"><span class="color-sample" style="background-color: #27c11e"></span> Clocked In</a>
    <a class="dropdown-item"><span class="color-sample" style="background-color: #1c81d9"></span> Future Shift</a>
    <a class="dropdown-item"><span class="color-sample" style="background-color: #849290"></span> Past Shift</a>
    <a class="dropdown-item"><span class="color-sample" style="background-color: #D0C3D3"></span> Unconfirmed Shift</a>
    <a class="dropdown-item"><span class="color-sample" style="background-color: #d9c01c"></span> Client Cancelled</a>
    <a class="dropdown-item"><span class="color-sample" style="background-color: #d91c4e"></span> CG Cancelled</a>
  </div>
`);
            },

            renderTimelineDayEvent(content, event, note) {
                let data = [`${event.caregiver}`];
                let title = $('<span/>', {
                    class: 'fc-title',
                    html: data.join('<br/>'),
                });
                content.html($('<div/>').append(note, title));
            },

            renderTimelineWeekEvent(content, event, note) {
                let data = [`${event.caregiver}`, `${event.start_time} - ${event.end_time}`];
                let title = $('<span/>', {
                    class: 'fc-title',
                    html: data.join('<br/>'),
                });
                content.html($('<div/>').append(note, title));
            },

            renderAgendaWeekEvent(content, event, note) {
                let data = [`C: ${event.client}`, `CG: ${event.caregiver}`, `${event.start_time} - ${event.end_time}`];
                let title = $('<span/>', {
                    class: 'fc-title',
                    html: data.join('<br/>'),
                });
                content.html($('<div/>').append(note, title));
            },

            renderDefaultEvent(content, event, note) {
                let data = [`C: ${event.client}`, `CG: ${event.caregiver}`, `${event.start_time} - ${event.end_time}`];
                let title = $('<span/>', {
                    class: 'fc-title',
                    html: data.join('<br/>'),
                });
                content.html(title);
                content.parent().prepend(note);
            }

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
.fc-view-container { font-size: 0.9em; }
.fc-event { text-align: left!important; }
.fc-note-btn { float: right!important; z-index: 9999; padding-left: 5px; position: relative; }
.fc-event { cursor: pointer; }
.fc-note-btn:hover {
    filter: brightness(85%);
    cursor: pointer;
}
.fa-commenting {
    color: #F2F214;
}
.fc-toolbar .dropdown-item {
    padding: 3px 6px;
}
.fc-toolbar .fc-center h2 {
    text-align: center;
    width: 100%;
}
.fc-toolbar h6 {
    /* Toolbar KPIs */
    clear: both;
}
.color-sample {
    display: inline-block;
    width: 12px;
    height: 12px;
    margin: 3px 3px 0 3px;
    border: 1px solid #000;
}
</style>
