<template>
    <b-card id="schedule-card">
        <b-row class="no-print">
            <b-col md="6">
                <b-row>
                    <b-col class="statusFilters">
                        <label>
                            <input type="checkbox" v-model="allStatuses" :value="1"> <span class="badge badge-light" v-b-popover.hover="`Remove status filters to show all shift types. ${statusHelp}`">All Statuses</span>
                        </label>
                        <label>
                            <input type="checkbox" v-model="statusFilters" value="SCHEDULED"> <span class="badge badge-primary scheduled" v-b-popover.hover="`Filter scheduled shifts with no status change. ${statusHelp}`">Scheduled</span>
                        </label>
                        <label>
                            <input type="checkbox" v-model="statusFilters" value="CLOCKED_IN"> <span class="badge badge-primary clocked_in" v-b-popover.hover="`Filter shifts that a caregiver is currently clocked in to. ${statusHelp}`">Clocked In</span>
                        </label>
                        <!--<label>-->
                            <!--<input type="checkbox" v-model="statusFilters" value="MISSED_CLOCK_IN"> <span class="badge badge-primary missed_clock_in" v-b-popover.hover="`Filter shifts that have passed the start time and haven't been clocked in or created. ${statusHelp}`">Missed Clock In</span>-->
                        <!--</label>-->
                        <label>
                            <input type="checkbox" v-model="statusFilters" value="CONFIRMED"> <span class="badge badge-primary confirmed" v-b-popover.hover="`Filter shifts that have a created and confirmed record. ${statusHelp}`">Confirmed</span>
                        </label>
                        <label>
                            <input type="checkbox" v-model="statusFilters" value="UNCONFIRMED"> <span class="badge badge-primary unconfirmed" v-b-popover.hover="`Filter shifts that have a created record but are still unconfirmed. ${statusHelp}`">Unconfirmed</span>
                        </label>
                        <label>
                            <input type="checkbox" v-model="statusFilters" value="OPEN"> <span class="badge badge-primary open" v-b-popover.hover="`Filter scheduled shifts without a referred caregiver. ${statusHelp}`">Open Shift</span>
                        </label>
                        <label>
                            <input type="checkbox" v-model="statusFilters" value="CLIENT_CANCELED"> <span class="badge badge-primary client_canceled" v-b-popover.hover="`Filter scheduled shifts that are marked Client Canceled. ${statusHelp}`">Client Canceled</span>
                        </label>
                        <label>
                            <input type="checkbox" v-model="statusFilters" value="CAREGIVER_CANCELED"> <span class="badge badge-primary cg_canceled" v-b-popover.hover="`Filter scheduled shifts that are marked Caregiver Canceled. ${statusHelp}`">CG Canceled</span>
                        </label>
                        <label>
                            <input type="checkbox" v-model="statusFilters" value="CAREGIVER_NOSHOW"> <span class="badge badge-primary no_show" v-b-popover.hover="`Filter scheduled shifts that are marked Caregiver No Show. ${statusHelp}`">CG No Show</span>
                        </label>
                        <label>
                            <input type="checkbox" v-model="statusFilters" value="ATTENTION_REQUIRED"> <span class="badge badge-primary attention" v-b-popover.hover="`Filter scheduled shifts that are marked Attention Required. ${statusHelp}`">Attention Required</span>
                        </label>
                        <label>
                            <input type="checkbox" v-model="statusFilters" value="OVERTIME"> <span class="badge badge-primary overtime" v-b-popover.hover="`Filter scheduled shifts that are marked as overtime or holiday pay. ${statusHelp}`">HOL / OT</span>
                        </label>
                        <label>
                            <input type="checkbox" v-model="statusFilters" value="ADDED_TO_PAST"> <span class="badge badge-primary added_to_past" v-b-popover.hover="`Visit was added after the start date. This visit will not be copied into the Shift History and is not included in billing. ${statusHelp}`">Added to Past</span>
                        </label>
                        <label>
                            <input type="checkbox" v-model="statusFilters" value="HOSPITAL_HOLD"> <span class="badge badge-primary hospital_hold" v-b-popover.hover="`Will not be copied over to the Shift History. ${statusHelp}`">Hospital Hold</span>
                        </label>
                    </b-col>
                </b-row>
            </b-col>
            <b-col md="6">
                <b-row>
                    <b-col class="text-right">
                        <b-btn variant="info" @click="createSchedule()"><i class="fa fa-plus"></i> Schedule Shift</b-btn>
                        <b-btn variant="primary" @click="bulkUpdateModal = !bulkUpdateModal" v-if="!officeUserSettings.enable_schedule_groups">Update Schedules</b-btn>
                        <b-btn variant="danger" @click="bulkDeleteModal = !bulkDeleteModal">Delete Schedules</b-btn>
                    </b-col>
                </b-row>
            </b-col>
        </b-row>
        <b-row class="no-print">
            <b-col lg="6">
                <b-row>
                    <b-col class="ml-auto" v-if="!caregiver">
                        <b-form-group>
                            <b-form-select v-model="filterCaregiverId" id="calendar_caregiver_filter">
                                <option :value="-1" v-if="!client">All Caregivers</option>
                                <option :value="-1" v-else>All Referred Caregivers</option>
                                <option :value="0">Open Shifts</option>
                                <option v-for="item in caregivers" :value="item.id" :key="item.id">{{ item.nameLastFirst }}</option>
                            </b-form-select>
                        </b-form-group>
                    </b-col>
                    <b-col class="ml-auto" v-if="!client">
                        <b-form-group>
                            <b-form-select v-model="filterClientId" id="calendar_client_filter">
                                <option :value="-1">All Clients</option>
                                <option :value="-2" v-if="caregiver">Clients With Shifts</option>
                                <option v-for="item in clients" :value="item.id" :key="item.id">{{ item.nameLastFirst }}</option>
                            </b-form-select>
                        </b-form-group>
                    </b-col>
                    <b-col class="ml-auto">
                        <business-location-form-group v-model="filterBusinessId" :allow-all="true" :label="null" />
                    </b-col>
                </b-row>
            </b-col>
            <b-col lg="6">
                <div class="form-control icon-control" v-show="false">
                    <i class="fa fa-search"></i>
                    <input type="text"
                           placeholder="Search Schedule"
                           v-model="filterText"
                    />
                </div>
            </b-col>
        </b-row>
        <div class="calendar-view">
            <div class="print-fc-head">
                <h1>{{business.name}}</h1>
                <h4 v-if="business.phone1 || business.phone2">{{business.phone1 || business.phone2}}</h4>
                <h3 class="text-center">
                    Schedules for 
                    <span v-if="!currentClient">All Clients</span>
                    <span v-else>{{currentClient.nameLastFirst}} <span v-if="currentClient.phone_number">{{currentClient.phone_number.number}}</span></span>
                    - visits by 
                    <span v-if="!currentCaregiver">All Caregivers</span>
                    <span v-else>{{currentCaregiver.nameLastFirst}}</span>
                </h3>
            </div>

            <full-calendar ref="calendar"
                :events="filteredEvents"
                :resources="resources"
                :default-view="defaultView"
                :header="header"
                :config="config"
                @event-created="createSchedule"
                @event-selected="editSchedule"
                @event-render="renderEvent"
                @view-render="onLoadView"
                @events-reloaded="loadKpiToolbar"
                @event-mouseover="eventHover"
                @event-mouseout="eventLeave"
                :loading="loading"
            />
            <h6 class="print-date">Printed on <span>{{currentTime()}}</span></h6>
        </div>

        <schedule-notes-modal v-model="notesModal"
                                :event="selectedEvent"
                                @updateEvent="updateEvent"
        />

        <business-schedule-modal :model.sync="scheduleModal"
                                   :selected-schedule="selectedSchedule"
                                   :pass-clients="clients"
                                   :pass-caregivers="client ? null : caregivers"
                                   @refresh-events="fetchEvents(true)"
                                   @clock-out="showClockOutModal()"
        />

        <bulk-update-schedule-modal v-model="bulkUpdateModal"
                                    :caregiver-id="filterCaregiverId"
                                    :client-id="filterClientId"
                                    :pass-clients="clients"
                                    :pass-caregivers="caregivers"
                                    @refresh-events="fetchEvents(true)"
        />

        <bulk-delete-schedule-modal v-model="bulkDeleteModal"
                                    :caregiver-id="filterCaregiverId"
                                    :client-id="filterClientId"
                                    :pass-clients="clients"
                                    :pass-caregivers="caregivers"
                                    @refresh-events="fetchEvents(true)"
        />

        <schedule-clock-out-modal v-model="clockOutModal"
                                    :shift="selectedSchedule.clocked_in_shift"
                                    @refresh="fetchEvents(true)"
        ></schedule-clock-out-modal>

        <div v-show="preview"
            id="preview"
            class="preview-window"
            :style="{ top: previewTop, left: previewLeft }"
        >
            <div class="d-flex">
                <div class="f-1" v-if="caregiverView">
                    <h4 v-if="hoverShift.client"><a :href="`/business/clients/${hoverShift.client.id}`">{{ hoverShift.client.nameLastFirst }}</a></h4>
                </div>
                <div class="f-1" v-else>
                    <h4 v-if="hoverShift.caregiver"><a :href="`/business/caregivers/${hoverShift.caregiver.id}`">{{ hoverShift.caregiver.nameLastFirst }}</a></h4>
                    <h4 v-else>OPEN</h4>
                </div>
                <div class="ml-auto" v-if="hoverShift.client_address">
                    <a v-if="! hoverShift.caregiver" :href="`/business/communication/text-caregivers?preset=open-shift&shift_id=${hoverShift.id}`" class="mr-2"><i class="fa fa-envelope-o"></i> Text Caregivers</a>
                    <a v-if="! hoverShift.caregiver_address" :href="`https://www.google.com/maps/search/?api=1&query=${encodeURI(hoverShift.client_address)}`" target="_blank"><i class="fa fa-map-marker"></i> Map</a>
                    <a v-else :href="`https://www.google.com/maps/dir/${encodeURI(hoverShift.caregiver_address)}/${encodeURI(hoverShift.client_address)}`" target="_blank"><i class="fa fa-map-marker"></i> Map</a>
                </div>
            </div>
            <div>
                <div class="d-flex">
                    <div class="f-1" v-if="caregiverView && hoverShift.client">
                        <span v-if="hoverShift.client_phone">{{ hoverShift.client_phone }}</span>
                        <br v-if="hoverShift.client_phone && hoverShift.client.email" />
                        <span>{{ hoverShift.client.email }}</span>
                    </div>
                    <div class="f-1" v-else-if="hoverShift.caregiver">
                        <span v-if="hoverShift.caregiver_phone">{{ hoverShift.caregiver_phone }} ({{ hoverShift.caregiver_phone_type }})</span>
                        <br v-if="hoverShift.caregiver_phone && hoverShift.caregiver.email" />
                        <span>{{ hoverShift.caregiver.email }}</span>
                    </div>
                    <div class="ml-auto">
                        <user-avatar v-if="caregiverView && hoverShift.client" :src="hoverShift.client.avatar" size="50" />
                        <user-avatar v-else-if="!caregiverView && hoverShift.caregiver" :src="hoverShift.caregiver.avatar" size="50" />
                    </div>
                </div>
            </div>
            <div class="my-2">
                <b-btn variant="success" @click="editFromPreview()" size="xs"><i class="fa fa-edit"></i> Edit</b-btn>
                <!-- <b-btn variant="primary" @click="copySchedule()" class="ml-2" size="xs"><i class="fa fa-copy"></i> Copy</b-btn> -->
                <b-btn variant="danger" @click="deleteSchedule()" class="ml-2" size="xs"><i class="fa fa-times"></i> Delete</b-btn>
            </div>
            <div>
                <span><strong>Dates:</strong> {{ formatDate(hoverShift.start_date) }} {{ formatTime(hoverShift.start_date) }} - {{ formatDate(hoverShift.end_date) }} {{ formatTime(hoverShift.end_date) }}</span>
            </div>
            <div>

                <span><strong>Services:</strong></span>

                <div v-for=" ( service, index ) in hoverShift.service_summary" :key=" index ">

                    {{ service.duration }} - {{ service.name }}
                </div>
            </div>
            <div>
                <label for="hover_status"><strong>Status:</strong></label>
                <b-form-select
                    id="hover_status"
                    name="hover_status"
                    v-model="hoverShift.status"
                    @change="updateStatus"
                >
                    <option value="OK">No Status</option>
                    <option value="ATTENTION_REQUIRED">Attention Required</option>
                    <option value="CLIENT_CANCELED">Client Canceled</option>
                    <option value="CAREGIVER_CANCELED">Caregiver Canceled</option>
                    <option value="CAREGIVER_NOSHOW">Caregiver No Show</option>
                    <option value="OPEN_SHIFT">Open Shift</option>
                    <option value="HOSPITAL_HOLD">Hospital Hold</option>
                </b-form-select>
            </div>
        </div>
    </b-card>
</template>

<script>
    import ManageCalendar from '../../../mixins/ManageCalendar';
    import LocalStorage from "../../../mixins/LocalStorage";
    import FormatsDates from "../../../mixins/FormatsDates";
    import FormatsNumbers from "../../../mixins/FormatsNumbers";
    import FormatsStrings from "../../../mixins/FormatsStrings";
    import BusinessLocationFormGroup from "../BusinessLocationFormGroup";
    import moment from 'moment';
    import HasOpenShiftsModal from '../../../mixins/HasOpenShiftsModal';
    import { mapActions, mapGetters } from 'vuex';

    export default {
        components: {BusinessLocationFormGroup },
        props: {
            'business': Object,
            'caregiver': Object,
            'client': Object,
            'defaultView': {
                default() {
                    return 'timelineWeek';
                }
            },
            'weekStart': {
                type: String,
                default: "1",
            },
        },

        data() {
            return {
                loading: false,
                filtersReady: false,
                filterCaregiverId: (this.caregiver) ? this.caregiver.id : -1,
                filterClientId: (this.client) ? this.client.id : -1,
                filterBusinessId: (this.client) ? this.client.business_id : "",
                header: {
                    left:   'prev,next today',
                    center: 'title',
                    right:  'timelineDay,timelineWeek,month caregiverView print fullscreen'
                },
                clients: this.client ? [this.client] : [],
                caregivers: this.caregiver ? [this.caregiver] : [],
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
                fullscreen: false,

                previewTop: 0,
                previewLeft: 0,
                preview: false,
                previewTimer: null,
                hoverShift: {},
                hoverTarget: '',
                location: 'all',

                statusHelp: "\nClick to activate or deactivate this filter.",

                service_types: ''
            }
        },

        mounted() {
            // this.appendColorKey();
            this.establishWeAreOnSchedulePage();
            this.loadFiltersData();
        },

        computed: {

            ...mapGetters({

                triggerBusinessScheduleToAct : 'openShifts/triggerBusinessScheduleToAct',
                vuexSelectedScheduleId       : 'openShifts/selectedScheduleId',
                vuexSelectedEvent            : 'openShifts/selectedEvent',
                newCaregiverName             : 'openShifts/newCaregiverName',
                newStatus                    : 'openShifts/newStatus',
            }),
            requestEvents(){

                return this.events.filter( e => e.requests_count > 0 );
            },
            eventsUrl() {
                if (!this.filtersReady || !this.end) {
                    return '';
                }

                let url = '/business/schedule/events?start=' + this.start + '&end=' + this.end;

                if (this.filterCaregiverId > -1) {
                    url += '&caregiver_id=' + this.filterCaregiverId;
                    if (this.filterClientId > -1) {
                        url += '&client_id=' + this.filterClientId;
                    }
                }
                else if (this.filterClientId > -1) {
                    url += '&client_id=' + this.filterClientId;
                }

                if (this.filterBusinessId) {
                    url += '&businesses[]=' + this.filterBusinessId;
                }

                return url;
            },

            rememberFilters() {
                return this.isFilterable && this.officeUserSettings.calendar_remember_filters;
            },

            calendarHeight() {
                return 'auto';
                // return window.innerHeight - (this.fullscreen ? 180 : 400);
            },

            config() {
                return {
                    height: this.calendarHeight,
                    eventBorderColor: '#333',
                    eventOverlap: false,
                    nextDayThreshold: this.officeUserSettings.calendar_next_day_threshold || '09:00:00',
                    nowIndicator: true,
                    resourceAreaWidth: '280px',
                    resourceColumns: [
                        {
                            labelText: this.resourceIdField === 'client_id' ? 'Client' : 'Caregiver',
                            text: function(resource) {
                                return resource.title;
                            },
                            render: function(resource, el) {
                                // need client/caregiver link
                                if (resource.title !== 'Open Shifts') {
                                    let link = `<a href='/business/${resource.role}/${resource.id}' target='_blank'>${resource.title}</a>`;
                                    el.html(link);
                                }
                            }
                        },
                        {
                            labelText: 'S',
                            field: 'scheduled',
                            width: '30px',
                        },
                        {
                            labelText: 'C',
                            field: 'completed',
                            width: '30px',
                        },
                        {
                            labelText: 'P',
                            field: 'projected',
                            width: '30px',
                        }
                    ],
                    resourceRender: this.resourceRender,
                    views: {
                        timelineWeek: {
                            slotLabelFormat: 'ddd D',
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
                    firstDay: this.weekStart,
                }
            },

            filteredEvents() { return this.getFilteredEvents(); },

            kpis() { return this.getKpis(); },

            resources() { return this.getResources(); },

            filteredCaregiverResources() {
                return (this.filterCaregiverId > -1 && !this.caregiver);
            },

            filteredClientResources() {
                return (this.filterClientId > -1 && !this.client) || this.filterClientId === -2;
            },

            currentClient() {
                if (this.clients && this.filterClientId !== -1) {
                    return this.clients.find(x => x.id === this.filterClientId);
                }
                return this.client;
            },

            currentCaregiver() {
                if (this.caregivers && this.filterCaregiverId !== -1) {
                    return this.caregivers.find(x => x.id === this.filterCaregiverId);
                }
                return this.caregiver;
            }
        },

        methods: {

            ...mapActions({

                establishWeAreOnSchedulePage : 'openShifts/establishWeAreOnSchedulePage',
                toggleTrigger : 'openShifts/toggleTrigger',
                setNewStatus  : 'openShifts/setNewStatus',
                setNewCaregiverName  : 'openShifts/setNewCaregiverName',
                setSelectedEvent  : 'openShifts/setSelectedEvent',
            }),
            getFilteredEvents() {
                let events = this.events;

                // console.log( events );

                if (this.statusFilters.length) {
                    events = events.filter(event => {
                        return this.statusFilters.includes(event.status)
                                || this.statusFilters.includes(event.shift_status)
                                // Open shifts are calculated from the cg canceled status or a missing cg assignment
                                || (this.statusFilters.includes('OPEN') && (event.caregiver_id == 0 || event.status === 'CAREGIVER_CANCELED'))
                                || (this.statusFilters.includes('OVERTIME') && event.has_overtime)
                                || (this.statusFilters.includes('ADDED_TO_PAST') && event.added_to_past == true);
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

            currentTime() {
                return moment().format('YYYY-MM-DD HH:mm:ss A');
            },

            getResources() {
                let items;

                if (this.caregiverView) {
                    this.resourceIdField = 'caregiver_id';
                    items = this.caregivers;
                }
                else {
                    this.resourceIdField = 'client_id';
                    items = this.clients;
                    if (this.filterBusinessId) {
                        items = items.filter(client => client.business_id == this.filterBusinessId);
                    }
                }


                let resources = items.map(item => {
                    let kpis = this.getKpis(this.resourceIdField, item.id);
                    return {
                        id: item.id,
                        title: item.nameLastFirst,
                        scheduled: kpis.SCHEDULED.hours.toFixed(0),
                        completed: kpis.COMPLETED.hours.toFixed(0),
                        projected: kpis.PROJECTED.hours.toFixed(0),
                        role: this.resourceIdField === 'client_id' ? 'clients' : 'caregivers',
                    };
                });

                if (this.caregiverView) {
                    let openkpis = this.getKpis(this.resourceIdField, 0);
                    resources.unshift({
                        id: 0,
                        title: 'Open Shifts',
                        scheduled: openkpis.SCHEDULED.hours.toFixed(0),
                        completed: openkpis.COMPLETED.hours.toFixed(0),
                        projected: openkpis.PROJECTED.hours.toFixed(0),
                    });
                }

                if ( this.filteredClientResources || this.filteredCaregiverResources ) {
                    let filtered = [this.filterClientId, this.filterCaregiverId];
                    return resources.filter(resource => {
                        return filtered.includes(resource.id) || this.events.findIndex(event => event[this.resourceIdField] == resource.id) !== -1;
                    });
                }
                return resources;
            },

            getKpis(matchColumn=null, matchValue=null) {

                let events = this.filteredEvents;
                if (matchColumn) {
                    events = events.filter(event => event[matchColumn] == matchValue);
                }

                let statuses = ['SCHEDULED', 'CLOCKED_IN', 'CONFIRMED', 'UNCONFIRMED', 'CLIENT_CANCELED', 'CAREGIVER_CANCELED', 'OPEN'];
                let kpis = {};

                for (let status of statuses) {
                    kpis[status] = {
                        hours: 0,
                        shifts: 0
                    }
                }

                kpis = events.reduce((totals, event) => {
                    const calc = function (status) {
                        if (!totals[status]) return;
                        totals[status] = {
                            hours: totals[status].hours + (event.duration / 60),
                            shifts: totals[status].shifts + 1
                        }
                    };

                    calc(event.status);
                    calc(event.shift_status);
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
                    hours: kpis.COMPLETED.hours + kpis.CLOCKED_IN.hours + kpis.SCHEDULED.hours,
                    shifts: kpis.COMPLETED.shifts + kpis.CLOCKED_IN.shifts + kpis.SCHEDULED.shifts
                };

                kpis['CANCELED'] = {
                    hours: kpis.CLIENT_CANCELED.hours + kpis.CAREGIVER_CANCELED.hours,
                    shifts: kpis.CLIENT_CANCELED.shifts + kpis.CAREGIVER_CANCELED.shifts
                };

                return kpis;
            },

            updateStatus(val, e) {
                if (! this.hoverShift.id) {
                    return;
                }

                if (this.hoverShift.starts_at && moment(this.hoverShift.starts_at).isBefore(moment())) {
                    if (! confirm('Modifying past schedules will NOT change the shift history or billing.  Continue?')) {
                        return;
                    }
                }

                let url = `/business/schedule/${this.hoverShift.id}/status`;
                // this.busy = true;
                let form = new Form({
                    id: this.hoverShift.id,
                    status: val,
                });

                form.patch(url)
                    .then(response => {
                        // this.$emit('updateEvent', this.form.id, response.data.data);
                        // this.showModal = false;
                        this.fetchEvents(true);
                        // this.busy = false;
                    })
                    .catch(e => {
                        // this.busy = false;
                    });
            },

            editFromPreview() {
                axios.get('/business/schedule/' + this.hoverShift.id)
                    .then(response => {
                        this.selectedSchedule = response.data;
                        this.scheduleModal = true;
                    })
                    .catch(function(error) {
                        alert('Error loading schedule details');
                    });
                this.hidePreview();
            },

            deleteSchedule() {
                let confirmMessage = 'Are you sure you wish to delete this scheduled shift?';
                if (moment(this.hoverShift.start_date).isBefore(moment())) {
                    confirmMessage = "Are you sure you wish to delete this past entry?\nNote: This will not affect any shift already in the Shift History.";
                }
                if (this.hoverShift.id && confirm(confirmMessage)) {
                    let form = new Form();
                    form.submit('delete', '/business/schedule/' + this.hoverShift.id)
                        .then(response => {
                            this.fetchEvents(true);
                        });
                }
            },

            // copySchedule() {
            //     axios.get('/business/schedule/' + this.hoverShift.id)
            //         .then(response => {
            //             this.selectedSchedule = response.data;
            //             this.scheduleModal = true;
            //         })
            //         .catch(function(error) {
            //             alert('Error loading schedule details');
            //         });
            //     this.hidePreview();
            // },

            eventHover(event, jsEvent, view) {
                let target = null;

                if ($(jsEvent.currentTarget).is('a')) {
                    target = $(jsEvent.currentTarget);
                } else {
                    target = $(jsEvent.currentTarget).parent('a');
                }

                if (this.previewTimer) {
                    clearTimeout(this.previewTimer);
                }

                this.previewTimer = setTimeout(function (event, target) {
                    axios.get('/business/schedule/' + event.id + '/preview')
                        .then(response => {
                            this.hoverShift = response.data;
                            this.showPreview(target, event.id);
                        })
                        .catch(function(error) {
                            this.hoverShift = {};
                        });
                }.bind(this, event, target), 500);

            },

            eventLeave() {
                if (this.previewTimer) {
                    clearTimeout(this.previewTimer);
                    this.previewTimer = null;
                }
            },

            showPreview(target, shift_id) {
                // the first next tick is used to allow the data to update and change the size
                // of the preview window before it is used to judge where to place it on the screen
                Vue.nextTick().then(() => {
                    let left = target.offset().left - $('#schedule-card').offset().left;
                    let top = target.offset().top + target.height() - $('#schedule-card').offset().top;

                    let availableWidth = document.documentElement.clientWidth - $('#schedule-card').offset().left;
                    let availableHeight = document.documentElement.clientHeight - $('#schedule-card').offset().top + document.documentElement.scrollTop;

                    if (left + $('#preview').outerWidth() > availableWidth) {
                        left = left - $('#preview').outerWidth() + target.width();
                    }

                    if (top + $('#preview').outerHeight() > availableHeight) {
                        top = top - $('#preview').outerHeight() - target.height();
                    }

                    this.previewLeft = left + "px";
                    this.previewTop = top + "px";
                    this.preview = true;

                    // this next tick is used because the window need to be visible on the screen
                    // in order to check if the mouse is hovering over it
                    Vue.nextTick().then(() => {
                        var eventRect = target.get(0).getBoundingClientRect();
                        var divRect = document.getElementById('preview').getBoundingClientRect();

                        let handler = function(e) {
                            if (this.hoverShift.id == shift_id) {
                                let extra = 5;
                                if (e.clientX >= eventRect.left - extra && e.clientX <= eventRect.right + extra &&
                                    e.clientY >= eventRect.top - extra && e.clientY <= eventRect.bottom + extra) {
                                        return;
                                }

                                if (e.clientX >= divRect.left - extra && e.clientX <= divRect.right + extra &&
                                    e.clientY >= divRect.top - extra && e.clientY <= divRect.bottom + extra) {
                                        return;
                                }
                            }

                            this.preview = false;
                            document.body.removeEventListener('mousemove', handler);
                        }.bind(this);
                        document.body.addEventListener('mousemove', handler, false);
                    });
                });
            },

            hidePreview() {
                this.preview = false;
                this.hoverShift = {};
            },

            scrollSelector() {
                if (this.calendarHeight === 'auto') return $(window);
                return $('.fc-widget-content .fc-scroller').last();
            },

            saveScrollPosition() {
                this.scroll = {
                    top: this.scrollSelector().scrollTop(),
                    left: this.scrollSelector().scrollLeft(),
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
                    // console.log('setScrollPosition called');
                    this.scrollSelector().scrollTop(this.scroll.top);
                    this.scrollSelector().scrollLeft(this.scroll.left);
                }
            },

            showClockOutModal() {
                this.clockOutModal = true;
            },

            onLoadView(view, element) {
                this.start = view.start.format('YYYY-MM-DD');
                this.end = view.end.format('YYYY-MM-DD');
                // Events will be fetched if end date changes due to the end watch
            },

            loadKpiToolbar() {
                let $toolbar = $('.fc-toolbar .fc-center');
                let $element = $toolbar.find('h6');
                if (!$element.length) $element = $toolbar.append('<h6/>').find('h6');

                let formatHours = (status) => this.numberFormat(this.kpis[status].hours);
                let formatShifts = (status) => parseInt(this.kpis[status].shifts);

                $element.html(`
                Scheduled: ${formatHours('SCHEDULED')} (${formatShifts('SCHEDULED')}) &nbsp;
                Completed: ${formatHours('COMPLETED')} (${formatShifts('COMPLETED')}) &nbsp;
                Projected: ${formatHours('PROJECTED')} (${formatShifts('PROJECTED')}) &nbsp;
                Canceled: ${formatHours('CANCELED')} (${formatShifts('CANCELED')}) &nbsp;
                Open: ${formatHours('OPEN')} (${formatShifts('OPEN')}) &nbsp;
                `);
            },

            fetchEvents(savePosition = false) {
                if (!this.filtersReady) {
                    return;
                }
                savePosition ? this.saveScrollPosition() : this.clearScrollPosition();
                this.loading = true;
                axios.get(this.eventsUrl)
                    .then( ({ data }) => {
                        this.events = data.events.map(event => {
                            event.resourceId = event[this.resourceIdField];
                            event.backgroundColor = this.getEventBackground(event);
                            return event;
                        });
                        // this.kpis = data.kpis;
                        this.eventsLoaded = true;
                        this.loading = false;
                    })
                    .catch(e => {
                        this.loading = false;
                        // console.log('error getting events:');
                        console.log(e);
                    })
            },

            updateEvent( id, data, status = null ) {

                this.saveScrollPosition();
                let event = this.events.find( item => {

                    return item.id === id;
                });

                // console.log( 'found event here:', event );
                if( event ){

                    if( status && status == this.OPEN_SHIFTS_STATUS.APPROVED ) event.caregiver = _.cloneDeep( this.newCaregiverName );
                    event.backgroundColor = this.getEventBackground( data, status );
                    event.note            = data.note;
                    event.requests_count  = data.requests_count;
                    event.status          = data.status;
                }
            },

            getEventBackground( event, status = null ){

                if( status && status == this.OPEN_SHIFTS_STATUS.APPROVED ) return '#1c81d9';
                return !event.caregiver_id ? '#d9c01c' : '#1c81d9';
            },

            loadFiltersData() {
                let clientIsFilterable = !this.client;
                let caregiverIsFilterable = !this.caregiver;

                // Load the default filter values
                if (caregiverIsFilterable && this.officeUserSettings.calendar_caregiver_filter === 'unassigned') {
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

                // Fill the caregiver and client drop downs
                let count = 0;
                if (clientIsFilterable) {
                    axios.get('/business/clients?json=1&address=1&phone_number=1&care_plans=1').then(response => {
                        this.clients = response.data;
                        this.clientsLoaded = true;
                    });
                }
                if (caregiverIsFilterable) {
                    let url = '/business/caregivers?json=1&address=1&phone_number=1';
                    if (this.client) url = '/business/clients/' + this.client.id + '/caregivers';
                    axios.get(url).then(response => {
                        this.caregivers = response.data;
                        this.caregiversLoaded = true;
                    });
                }

                this.filtersReady = true;
            },

            renderEvent: function( event, element, view ) {
                let note = '';
                let requests = '';

                if (event.note) {
                    // adds the widget-icon for the note

                    note = $('<span/>', {
                        class: 'fc-note-btn',
                        html: $('<i/>', {
                            class: event.note ? 'fa fa-commenting' : 'fa fa-comment',
                        }),
                    });

                    let vm = this;
                    note.click((e) => {
                        vm.selectedEvent = event;
                        vm.hidePreview();
                        vm.notesModal = true;
                        e.preventDefault();
                        e.stopPropagation();
                    });
                }

                if( !event.caregiver_id && event.requests_count > 0 ){
                    // adds the widget-icon for shift requests

                    requests = $('<span/>', {
                        class: 'fc-note-btn hand-icon-sizing',
                        html: $('<i/>', {
                            class: 'solid-open-shifts-icon',
                        }),
                    });

                    let vm = this;
                    requests.click((e) => {

                        vm.selectedEvent = event;
                        vm.selectedScheduleId = event.id;
                        vm.hidePreview();
                        vm.$store.dispatch( 'openShifts/toggleOpenShiftsModal', event );
                        e.preventDefault();
                        e.stopPropagation();
                    });
                }

                let content = element.find('.fc-content');
                if (view.name == 'agendaWeek') {
                    this.renderAgendaWeekEvent(content, event, note, requests);
                } else if (view.name == 'timelineDay') {
                    this.renderTimelineDayEvent(content, event, note, requests);
                } else if (view.name == 'timelineWeek') {
                    this.renderTimelineWeekEvent(content, event, note, requests);
                } else {
                    this.renderDefaultEvent(content, event, note, requests);
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
    <a class="dropdown-item"><span class="color-sample" style="background-color: #ad92b0"></span> Unconfirmed Shift</a>
    <a class="dropdown-item"><span class="color-sample" style="background-color: #d9c01c"></span> Client Canceled</a>
    <a class="dropdown-item"><span class="color-sample" style="background-color: #d91c4e"></span> CG Canceled</a>
    <a class="dropdown-item"><span class="color-sample" style="background-color: #63cbc7"></span> CG No Show</a>
  </div>
`);
            },

            getEventPersonName(event) {
                return this.caregiverView ? event.client : event.caregiver;
            },

            renderTimelineDayEvent(content, event, note, requests) {
                let data = [`${this.getEventPersonName(event)} ${event.start_time} - ${event.end_time}`, ...event.service_types];
                let title = $('<span/>', {
                    class: 'fc-title',
                    html: data.join('<br/>'),
                });
                content.html($('<div/>').append(requests, note, title));
            },

            renderTimelineWeekEvent(content, event, note, requests) {
                let data = [this.getEventPersonName(event), `${event.start_time} - ${event.end_time}`, ...event.service_types];
                let title = $('<span/>', {
                    class: 'fc-title',
                    html: data.join('<br/>'),
                });
                content.html($('<div/>').append(requests, note, title));
            },

            renderAgendaWeekEvent(content, event, note, requests) {
                let data = [`C: ${event.client}`, `CG: ${event.caregiver}`, `${event.start_time} - ${event.end_time}`, ...event.service_types];
                let title = $('<span/>', {
                    class: 'fc-title',
                    html: data.join('<br/>'),
                });
                content.html($('<div/>').append(requests, note, title));
            },

            renderDefaultEvent(content, event, note, requests) {
                let data = [`C: ${event.client}`, `CG: ${event.caregiver}`, `${event.start_time} - ${event.end_time}`, ...event.service_types];
                let title = $('<span/>', {
                    class: 'fc-title',
                    html: data.join('<br/>'),
                });
                content.html(title);
                content.parent().prepend(note, requests);
            },

            resourceRender(resource, $td)  {
                $td.closest('tr').popover({
                    content: this.getPhoneAndAddress(resource.id),
                    placement: function(context, src) {
                        $(context).addClass('resource-popover');
                        return 'right';
                    },
                    title: resource.title,
                    trigger: 'manual',
                }).on('mouseenter', function () {
                    this.previewTimer = setTimeout((event, target) => {
                        let $this = $(this);
                        $this.popover('show');
                        $('.popover').on('mouseleave', function () {
                            $this.popover('hide');
                        });
                    }, 750);
                }).on('mouseleave', function () {
                    clearTimeout(this.previewTimer);
                    this.previewTimer = null;
                    let $this = $(this);
                    setTimeout(function () {
                        if (!$('.popover:hover').length) {
                            $this.popover('hide');
                        }
                    }, 250);
                });
            },

            getPhoneAndAddress(id) {
                let resource;
                if (this.caregiverView) {
                    resource = this.caregivers.find(caregiver => caregiver.id == id);
                } else {
                    resource = this.clients.find(client => client.id == id);
                }

                let str = '';
                try {
                    if (resource.phone_number) {
                        str = resource.phone_number.number  + "\n";
                    }
                    str = str + this.addressFormat(resource.address);
                } catch (e) {}
                return str || 'No address on file.';
            },

            fullscreenToggle() {
                let $element = $(this.$el);
                $element.toggleClass('fullscreen-calendar');
                $('.left-sidebar').toggle();
                $('.footer').toggle();
                this.fullscreen = !this.fullscreen;
                this.$refs.calendar.$emit('rerender-events');
            },

            caregiverViewToggle() {
                this.caregiverView = !this.caregiverView;
                $('.fc-caregiverView-button').text(this.caregiverView ? 'Client View' : 'Caregiver View');
                $('.fc-resource-area .fc-cell-text:first').text(this.caregiverView ? 'Caregiver' : 'Client');
                this.fetchEvents();
            },

            printCalendar() {
                window.print();
            },
        },

        watch: {

            triggerBusinessScheduleToAct( newVal, oldVal ) {

                if( newVal ){
                    // i want to run updateEvent() with the current data from Vuex! YES
                    // this is honestly a really convoluted solution that needs to immediately be replaced

                    // console.log( 'vuex ID: ', _.cloneDeep( this.vuexSelectedScheduleId ) );
                    // console.log( 'vuex Event: ', _.cloneDeep( this.vuexSelectedEvent ) );
                    // console.log( 'new value: ', newVal );
                    // console.log( 'old value: ', oldVal );
                    this.selectedEvent = _.cloneDeep( this.vuexSelectedEvent );
                    this.handleCalendarPropogation( _.cloneDeep( this.newStatus ) );
                    this.setNewStatus( null );
                    this.setSelectedEvent( null );
                    this.setNewCaregiverName( null );
                    this.toggleTrigger( false );
                }
            },
            calendarHeight(val) {
                this.$refs.calendar.setOption('height', val);
            },

            filterCaregiverId(val) {
                if (this.rememberFilters) {
                    this.setLocalStorage('caregiver', val);
                }
            },

            filterClientId(val) {
                if (this.rememberFilters) {
                    this.setLocalStorage('client', val);
                }
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
            },

            eventsUrl(val, old) {
                this.fetchEvents();
            },
        },

        mixins: [ManageCalendar, LocalStorage, FormatsDates, FormatsNumbers, FormatsStrings, HasOpenShiftsModal],
    }
</script>

<style lang="scss">
    .fc-view-container { font-size: 0.9em; }
    .fc-event { text-align: left!important; }
    .hand-icon-sizing { height: 20px; width: 20px; }
    .fc-note-btn { float: right!important; z-index: 9999; padding-left: 5px; position: relative; }
    .fc-event { cursor: pointer; }
    .fc-note-btn:hover {
        filter: brightness(85%);
        cursor: pointer;
    }
    .fa-commenting {
        color: #F2F214;
    }
    .fc-toolbar {
        padding: 0;
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
    .fc-resource-area .fc-content {
        background-color: #fff;
    }
    .fc-resource-area .fc-cell-content {
        padding-left: 2px; padding-right: 2px;
    }
    .fc-resource-area .fc-widget-content:not(:first-child) .fc-cell-content {
        overflow: visible;
    }
    .statusFilters .badge { cursor: pointer; }
    .badge.scheduled { background-color: #1c81d9; }
    .badge.clocked_in { background-color: #27c11e; }
    .badge.confirmed { background-color: #849290; }
    .badge.unconfirmed { background-color: #ad92b0; }
    .badge.client_canceled { background-color: #730073; }
    .badge.cg_canceled { background-color: #ff8c00; }
    .badge.open { background-color: #d9c01c; }
    .badge.attention { background-color: #C30000; }
    .badge.missed_clock_in { background-color: #E468B2; }
    .badge.no_show { background-color: #63cbc7; }
    .badge.overtime { background-color: #fc4b6c; }
    .badge.added_to_past { background-color: #124aa5; }
    .badge.hospital_hold { background-color: #9881e9; }

    .fc-resource-area .fc-scroller {
        /* disables horizontal scroll bar in resource area */
        overflow: hidden !important;
    }
    .fc-time-area td, .fc-month-view tbody td {
        /* calendar borders, event borders are in the config property */
        border-color: rgba(120, 130, 140, 0.25) !important;
    }
    .fc-timeline-event {
        margin-right: 4px !important;
    }
    .fc-cell-text {
        color: #222;
        font-weight: 500;
    }
    .preview-window {
        z-index: 9999!important;
        position: absolute;
        background-color: #fff;
        padding: 1em;
        border: 1px solid #456789;
        width: 420px;
    }
    .resource-popover {
        margin-right: 12px !important;
        white-space: pre-line;
    }

    .print-fc-head {
        display: none;
        max-width: 85%;
        margin: 0 auto;

        h1 {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 20px;
        }

        h4 {
            font-weight: bold;
        }

        h3 {
            font-weight: bold;
        }
    }

    .print-date {
        display: none;
    }

    @media print {
        .print-date {
            display: block;
            text-align: center;
            font-style: italic;
            font-weight: bold;
        }

        .print-fc-head {
            display: block;
        }

        #schedule-card {
            border: none !important;
        }

        .fc-month-view tbody td {
            border-color: #888 !important;
        }

        .fc-view-container {
            border: 1px solid #888;
        }

        .fc-toolbar {
            h2 {
                font-weight: bold;
            }

            h6 {
                display: none;
            }
        }
    }
</style>

<style scoped>
    :checked + span { border: 3px solid black; }
    .statusFilters input {
        display: none;
    }
</style>
