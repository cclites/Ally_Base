<template>

  <div>

    <b-card :title=" active_business ? active_business.name : '' ">

      <loading-card v-show=" loading " />

      <div v-show="! loading" class="table-responsive">

          <ally-table id="open-shifts" :columns=" fields " :items=" events " sort-by="date" :perPage=" 1000 " :isBusy=" isBusy ">

            <template slot="start" scope="data">

                {{ formatDateFromUTC( data.item.start ) + ' ' + data.item.start_time + '-' + data.item.end_time + ' :: ' + data.item.id }}
            </template>
            <template slot="actions" scope="data">

                <transition mode="out-in" name="slide-fade">

                    <b-button variant="success" size="sm" class="btn-block" v-if=" !hasRequest( data.item.request_status ) " @click=" requestShift( data.item ) " key="request">Request Shift</b-button>
                    <b-button variant="default" size="sm" class="btn-block" v-if=" hasRequest( data.item.request_status ) " @click=" requestShift( data.item ) " key="rescind">Cancel Request</b-button>
                </transition>
            </template>
            <template slot="status" scope="data">

                {{ data.item.status == 'OK' ? 'Open' : data.item.status }}
            </template>
          </ally-table>
      </div>
    </b-card>
  </div>
</template>

<script>

    import FormatsDates from '../../../mixins/FormatsDates';
    import AuthUser from '../../../mixins/AuthUser';

    export default {

        props: [ 'businesses', 'role_type' ],
        data() {

            return {

                loading         : false,
                filtersReady    : false,
                // clients         : this.client ? [this.client] : [],
                // caregivers      : this.caregiver ? [this.caregiver] : [],
                events          : [],
                eventsLoaded    : false,
                active_business : null,
                isBusy          : false,
                fields : [

                    {
                        key        : 'start',
                        label      : 'Shift Date',
                        sortable   : true,
                        shouldShow : true,
                    },
                    {
                        key        : 'client',
                        label      : 'Client',
                        sortable   : true,
                        shouldShow : true,
                    },
                    {
                        key        : this.role_type == 'caregiver' ? 'actions' : 'requests_count',
                        label      : this.role_type == 'caregiver' ? 'Actions' : 'Requests',
                        sortable   : this.role_type == 'caregiver' ? false : true,
                        shouldShow : true,
                    },
                    {
                        key        : 'status',
                        label      : 'Status',
                        sortable   : true,
                        shouldShow : true,
                    },
                    // {
                    //   key: 'created_at',
                    //   label: 'First date referred',
                    //   sortable: true,
                    //   shouldShow: true,
                    //   formatter: x => { return this.formatDateFromUTC(x) }
                    // },
                ]
            }
        },

        mounted() {

            this.loadFiltersData();
            if( !Array.isArray( this.businesses ) ) this.active_business = this.businesses;
            else this.active_business = this.businesses[ 0 ].id || null;
        },

        computed: {

            eventsUrl() {

                if ( !this.filtersReady ) {

                    return '';
                }

                let url = '';

                switch( this.role_type ){

                    case 'caregiver':

                        url = '/schedule/open-shifts';
                        break;
                    case 'office_user':

                        url = '/business/schedule/open-shifts';
                        break;
                }

                url += '?json=1';

                url += '&businesses=' + this.active_business;

                /*

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
                */

                return url;
            },

            /*
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
                                field: 'title',
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
            */
        },

        methods: {

            hasRequest( status ){

                switch( status ){

                    case 'pending':
                    case 'denied':
                    case 'approved':
                        return true;

                        break;
                    case 'cancelled':
                    default:

                        return false;
                        break;
                }
            },
            requestShift( schedule ){

                this.isBusy = true;
                const form = new Form();

                form.post( `/schedule/open-shifts/${schedule.id}` )
                    .then( res => {

                        schedule.request_status = res.data.data.status;
                    })
                    .catch( e => {

                        console.log( 'error requesting shift', e );
                    })
                    .finally( () => {

                        this.isBusy = false;
                    });
            },
            loadFiltersData() {

                /*
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
                */
                // Fill the caregiver and client drop downs
                /*
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
                */

                this.filtersReady = true;
            },
            fetchEvents( savePosition = false ) {

                if ( !this.filtersReady ) {

                return;
                }

                this.loading = true;

                const form = new Form();

                form.get( this.eventsUrl )
                    .then( ({ data }) => {

                        this.events = data.events.map( event => {

                            event.resourceId      = event[ this.resourceIdField ];
                            // event.backgroundColor = this.getEventBackground( event );
                            return event;
                        });

                        // this.kpis = data.kpis;
                        this.eventsLoaded = true;
                    })
                    .catch( e => {

                        console.log( 'error getting events:' );
                        console.log( e );
                    })
                    .finally( () => {

                        this.loading = false;
                    });
            },

            /*
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
                
                    if (this.hoverShift.starts_at && moment(this.hoverShift.starts_at.date).isBefore(moment())) {
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

                setScrollPosition() {
                    if (this.scroll.top !== null) {
                        console.log('setScrollPosition called');
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

                updateEvent(id, data) {
                    this.saveScrollPosition();
                    let event = this.events.find(item => {
                        return item.id === id;
                    });
                    if (event) {
                        event.backgroundColor = this.getEventBackground(data);
                        event.note = data.note;
                        event.status = data.status;
                    }
                },

                getEventBackground(event) {
                    return event.backgroundColor || '#1c81d9';
                },

                renderEvent: function( event, element, view ) {
                    let note = '';

                    if (event.note) {
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

                getEventPersonName(event) {
                    return this.caregiverView ? event.client : event.caregiver;
                },

                renderTimelineDayEvent(content, event, note) {
                    let data = [`${this.getEventPersonName(event)} ${event.start_time} - ${event.end_time}`, ...event.service_types];
                    let title = $('<span/>', {
                        class: 'fc-title',
                        html: data.join('<br/>'),
                    });
                    content.html($('<div/>').append(note, title));
                },

                renderTimelineWeekEvent(content, event, note) {
                    let data = [this.getEventPersonName(event), `${event.start_time} - ${event.end_time}`, ...event.service_types];
                    let title = $('<span/>', {
                        class: 'fc-title',
                        html: data.join('<br/>'),
                    });
                    content.html($('<div/>').append(note, title));
                },

                renderAgendaWeekEvent(content, event, note) {
                    let data = [`C: ${event.client}`, `CG: ${event.caregiver}`, `${event.start_time} - ${event.end_time}`, ...event.service_types];
                    let title = $('<span/>', {
                        class: 'fc-title',
                        html: data.join('<br/>'),
                    });
                    content.html($('<div/>').append(note, title));
                },

                renderDefaultEvent(content, event, note) {
                    let data = [`C: ${event.client}`, `CG: ${event.caregiver}`, `${event.start_time} - ${event.end_time}`, ...event.service_types];
                    let title = $('<span/>', {
                        class: 'fc-title',
                        html: data.join('<br/>'),
                    });
                    content.html(title);
                    content.parent().prepend(note);
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
            */
        },

        watch: {

            eventsUrl( val, old ) {

            this.fetchEvents();
            },
            /*
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
            */
        },

        mixins: [

            FormatsDates,
            AuthUser
            //ManageCalendar, LocalStorage, FormatsNumbers, FormatsStrings
        ],
    }
</script>

<style scoped>

</style>