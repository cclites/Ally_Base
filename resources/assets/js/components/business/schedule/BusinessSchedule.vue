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
                        <div class="form-control icon-control">
                            <i class="fa fa-search"></i>
                            <input type="text"
                                   placeholder="Search Schedule"
                                   v-model="filterText"
                            />
                        </div>
                    </b-col>
                </b-row>
            </b-col>
            <b-col md="5">
                <b-row>
                    <b-col class="statusFilters">
                        <label>
                            <input type="checkbox" v-model="allStatuses" :value="1"> <span class="badge badge-light">All Statuses</span>
                        </label>
                        <label>
                            <input type="checkbox" v-model="statusFilters" value="OK"> <span class="badge badge-primary scheduled">Scheduled</span>
                        </label>
                        <label>
                            <input type="checkbox" v-model="statusFilters" value="CLOCKED_IN"> <span class="badge badge-primary clocked_in">Clocked In</span>
                        </label>
                        <label>
                            <input type="checkbox" v-model="statusFilters" value="CONFIRMED"> <span class="badge badge-primary confirmed">Confirmed</span>
                        </label>
                        <label>
                            <input type="checkbox" v-model="statusFilters" value="UNCONFIRMED"> <span class="badge badge-primary unconfirmed">Unconfirmed</span>
                        </label>
                        <label>
                            <input type="checkbox" v-model="statusFilters" value="OPEN"> <span class="badge badge-primary">Open Shift</span>
                        </label>
                        <label>
                            <input type="checkbox" v-model="statusFilters" value="CLIENT_CANCELLED"> <span class="badge badge-primary client_cancelled">Client Cancelled</span>
                        </label>
                        <label>
                            <input type="checkbox" v-model="statusFilters" value="CAREGIVER_CANCELED"> <span class="badge badge-primary cg_cancelled">CG Cancelled</span>
                        </label>
                    </b-col>
                </b-row>
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
            :events="filteredEvents"
            :resources="resources"
            :default-view="defaultView"
            :header="header"
            :config="config"
            @day-click="createSchedule"
            @event-selected="editSchedule"
            @event-render="renderEvent"
            @view-render="onLoadView"
            @events-reloaded="loadKpiToolbar"
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

        <iframe id="printFrame" width="0" height="0" src="/calendar-print.html">
        </iframe>
    </b-card>
</template>

<script>
    import ManageCalendar from '../../../mixins/ManageCalendar';
    import LocalStorage from "../../../mixins/LocalStorage";
    import FormatsNumbers from "../../../mixins/FormatsNumbers";

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
                    right:  'timelineDay,timelineWeek,month caregiverView print fullscreen'
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
                localStoragePrefix: 'business_schedule_',
                resetScrollPosition: false,
                scroll: { top: null, left: null },
                resourceIdField: 'client_id',
                eventsLoaded: false, // initial events load
                caregiversLoaded: !!this.caregiver,
                clientsLoaded: !!this.client,
                caregiverView: !!this.client,
                filterText: '',
                statusFilters: [],
                allStatuses: 1,
            }
        },

        mounted() {
            this.appendColorKey();
            this.loadFiltersData();
        },

        computed: {
            filteredEvents() {
                let events = this.events;

                if (this.statusFilters.length) {
                    events = events.filter(event => {
                        if (this.statusFilters.includes(event.status)) {
                            return true;
                        }

                        if (this.statusFilters.includes('OPEN') && event.caregiver_id == 0) {
                            return true;
                        }

                        return false;
                    });
                }

                if (this.filterText.length > 2) {
                    let regex = new RegExp(this.filterText, "i");
                    events = events.filter(event => {
                        let str = [event.note, event.caregiver, event.client].join( "|" );
                        return regex.test(str);
                    })
                }

                return events;
            },

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

                if (this.caregiverView) {
                    items = this.caregivers;
                    this.resourceIdField = 'caregiver_id';
                }

                let resources = items.map(item => {
                    return {
                        id: item.id,
                        title: item.nameLastFirst
                    };
                });

                if (this.caregiverView) {
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

            kpis() {
                let statuses = ['OK', 'CLOCKED_IN', 'CONFIRMED', 'UNCONFIRMED', 'COMPLETED', 'PROJECTED', 'CLIENT_CANCELLED', 'CAREGIVER_CANCELLED', 'CANCELLED', 'OPEN'];
                let kpis = {};

                for (let status of statuses) {
                    kpis[status] = {
                        hours: 0,
                        shifts: 0
                    }
                }

                kpis = this.filteredEvents.reduce((totals, event) => {
                    const calc = function (status) {
                        totals[status] = {
                            hours: totals[status].hours + (event.duration / 60),
                            shifts: totals[status].shifts + 1
                        }
                    };

                    calc(event.status);
                    if (event.caregiver_id == 0) {
                        calc('OPEN');
                    }

                    return totals;
                }, kpis);

                kpis['COMPLETED'] = {
                    hours: kpis.CONFIRMED.hours + kpis.UNCONFIRMED.hours,
                    shifts: kpis.CONFIRMED.shifts + kpis.UNCONFIRMED.shifts
                };

                kpis['PROJECTED'] = {
                    hours: kpis.COMPLETED.hours + kpis.CLOCKED_IN.hours + kpis.OK.hours,
                    shifts: kpis.COMPLETED.shifts + kpis.CLOCKED_IN.shifts + kpis.OK.shifts
                };

                kpis['CANCELLED'] = {
                    hours: kpis.CLIENT_CANCELLED.hours + kpis.CAREGIVER_CANCELLED.hours,
                    shifts: kpis.CLIENT_CANCELLED.shifts + kpis.CAREGIVER_CANCELLED.shifts
                };

                return kpis;
            },

            config() {
                return {
                    nextDayThreshold: this.business ? this.business.calendar_next_day_threshold : '09:00:00',
                    nowIndicator: true,
                    resourceLabelText: this.resourceIdField === 'client_id' ? 'Client' : 'Caregiver',
                    resourceAreaWidth: '250px',
                    views: {
                        timelineWeek: {
                            slotDuration: '24:00'
                        },
                    },
                    customButtons: {
                        caregiverView: {
                            text: this.caregiverView ? 'Client View' : 'Caregiver View',
                            click: this.caregiverViewToggle
                        },
                        fullscreen: {
                            text: ' ',
                            click: this.fullscreenToggle
                        },
                        print: {
                            text: ' ',
                            click: this.printCalendar
                        }
                    },
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
            },

            loadKpiToolbar() {
                let $toolbar = $('.fc-toolbar .fc-center');
                let $element = $toolbar.find('h6');
                if (!$element.length) $element = $toolbar.append('<h6/>').find('h6');

                let formatHours = (status) => this.numberFormat(this.kpis[status].hours);
                let formatShifts = (status) => parseInt(this.kpis[status].shifts);

                $element.html(`
                Scheduled: ${formatHours('OK')} (${formatShifts('OK')}) &nbsp;
                Completed: ${formatHours('COMPLETED')} (${formatShifts('COMPLETED')}) &nbsp;
                Projected: ${formatHours('PROJECTED')} (${formatShifts('PROJECTED')}) &nbsp;
                Cancelled: ${formatHours('CANCELLED')} (${formatShifts('CANCELLED')}) &nbsp;
                Open: ${formatHours('OPEN')} (${formatShifts('OPEN')}) &nbsp;
                `);
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
                        // this.kpis = data.kpis;
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

            getEventPersonName(event) {
                return this.caregiverView ? event.client : event.caregiver;
            },

            renderTimelineDayEvent(content, event, note) {
                let data = [this.getEventPersonName(event)];
                let title = $('<span/>', {
                    class: 'fc-title',
                    html: data.join('<br/>'),
                });
                content.html($('<div/>').append(note, title));
            },

            renderTimelineWeekEvent(content, event, note) {
                let data = [this.getEventPersonName(event), `${event.start_time} - ${event.end_time}`];
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
            },

            fullscreenToggle() {
                let $element = $(this.$el);
                $element.toggleClass('fullscreen-calendar');
                this.$refs.calendar.$emit('rerender-events');
            },

            caregiverViewToggle() {
                this.caregiverView = !this.caregiverView;
                this.$refs.calendar.$emit('rerender-events');
            },

            printCalendar() {
                console.log($(this.$refs.calendar.$el));
                let html = $(this.$refs.calendar.$el).html();
                $("#printFrame").contents().find('body').html(html);
                document.getElementById("printFrame").contentWindow.print();
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
            },

            allStatuses(val) {
                if (val) this.statusFilters = [];
            },

            statusFilters(val) {
                this.allStatuses = val.length ? 0 : 1;
            }
        },

        mixins: [ManageCalendar, LocalStorage, FormatsNumbers]
    }
</script>

<style type="scss">
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
.fc-fullscreen-button:before {
    font: normal normal normal 14px/1 FontAwesome;
    content: "\f0b2";
}
.fc-print-button:before {
    font: normal normal normal 14px/1 FontAwesome;
    content: "\f02f";
}
.fullscreen-calendar {
    z-index: 101;
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
}
.badge.scheduled { background-color: #1c81d9; }
.badge.clocked_in { background-color: #27c11e; }
.badge.confirmed { background-color: #849290; }
.badge.unconfirmed { background-color: #D0C3D3; }
.badge.client_cancelled { background-color: #d91c4e; }
.badge.cg_cancelled { background-color: #d9c01c; }
</style>

<style scoped>
    :checked + span { border: 2px solid black; }
    .statusFilters input {
        display: none;
    }
</style>
