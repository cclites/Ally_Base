<template>
    <div ref="calendar" id="calendar">
        <loading-card v-show="loading" style="z-index: 1000;position: absolute;top: 500px;left:  50%;width: 100%;margin-left: -50%;" />
    </div>
</template>

<script>
    import fullcalendar from 'fullcalendar';
    import fullcalendarScheduler from 'fullcalendar-scheduler';

    export default {
        props: {
            loading: false,

            resources: {
                default() {
                    return []
                },
            },

            events: {
                default() {
                    return []
                },
            },

            eventSources: {
                default() {
                    return []
                },
            },

            editable: {
                default() {
                    return false
                },
            },

            selectable: {
                default() {
                    return true
                },
            },

            selectHelper: {
                default() {
                    return true
                },
            },

            header: {
                default() {
                    return {
                        left:   'prev,next today',
                        center: 'title',
                        right:  'listMonth,agendaWeek,month'
                    }
                },
            },

            defaultView: {
                default() {
                    return 'month'
                },
            },

            sync: {
                default() {
                    return false
                }
            },

            config: {
                type: Object,
                default() {
                    return {}
                },
            },
        },

        data() {
            return {
                currentView: this.defaultView,
            }
        },

        computed: {
            defaultConfig() {
                const self = this;
                return {
                    header: this.header,
                    defaultView: this.currentView,
                    editable: this.editable,
                    selectable: this.selectable,
                    selectHelper: this.selectHelper,
                    aspectRatio: 2,
                    timeFormat: 'h:mma',
                    timezone: false, // keep timezone sent from server
                    events: this.events,
                    eventSources: this.eventSources,
                    allDaySlot: false,
                    weekNumberCalculation: 'iso',
                    renderHtml: false,
                    height: 'auto',
                    schedulerLicenseKey: window.fcsKey,

                    resources(callback, start, end, timezone) {
                        callback(self.resources);
                    },

                    eventRender(...args) {
                        if (this.sync) {
                            self.events = cal.fullCalendar('clientEvents')
                        }
                        self.$emit('event-render', ...args,);

                        const eventObj = args[0], $el = args[1], view = args[2];
                        const spansMultipleDays = e => {
                            if (!e.start || !e.end) {
                                return false;
                            }

                            return e.start.format("D") !== e.end.format("D");
                        };

                        const isListView = $el.hasClass("fc-list-item");

                        if (isListView && spansMultipleDays(eventObj)) {
                            // only hide if NOT start day

                            const startDate = eventObj.start.format('LL');
                            const selectedDate = view.calendar.currentDate.format('LL');

                            if (startDate !== selectedDate) {
                                $el.closest("tr").hide();
                            }
                        }
                    },

                    eventDestroy(event) {
                        if (this.sync) {
                            self.events = cal.fullCalendar('clientEvents')
                        }
                    },

                    eventClick(...args) {
                        self.$emit('event-selected', ...args)
                    },

                    eventMouseover(...args) {
                        self.$emit('event-mouseover', ...args)
                    },

                    eventMouseout(...args) {
                        self.$emit('event-mouseout', ...args)
                    },

                    eventDrop(...args) {
                        self.$emit('event-drop', ...args)
                    },

                    eventResize(...args) {
                        self.$emit('event-resize', ...args)
                    },

                    viewRender(...args) {
                        this.currentView = args[0].name;
                        self.$emit('view-render', ...args)
                    },

                    dayClick(...args){
                        self.$emit('day-click', ...args)
                    },

                    select(start, end, jsEvent, view, resource) {
                        self.$emit('event-created', {
                            start,
                            end,
                            allDay: !start.hasTime() && !end.hasTime(),
                            view,
                            resource
                        })
                    },

                    loading(isLoading) {
                        self.$emit('update:loading', isLoading);
                    }
                }
            },
        },

        mounted() {
            this.$on('remove-event', (event) => {
                if(event && event.hasOwnProperty('id')){
                    $(this.$el).fullCalendar('removeEvents', event.id);
                }else{
                    $(this.$el).fullCalendar('removeEvents', event);
                }
            })

            this.$on('rerender-events', () => {
                $(this.$el).fullCalendar('rerenderEvents');
                this.hideWeekButtonOnSmallDevices();
            })

            this.$on('refetch-events', () => {
                $(this.$el).fullCalendar('refetchEvents')
            })

            this.$on('render-event', (event) => {
                $(this.$el).fullCalendar('renderEvent', event)
            })

            this.$on('reload-events', () => {
                $(this.$el).fullCalendar('removeEvents')
                $(this.$el).fullCalendar('addEventSource', this.events)
                this.$emit('events-reloaded');
            })

            this.$on('rebuild-sources', () => {
                $(this.$el).fullCalendar('removeEvents')
                this.eventSources.map(event => {
                    $(this.$el).fullCalendar('addEventSource', event)
                })
            })

            this.createCalendar();
        },

        methods: {
            fireMethod(...options) {
                $(this.$el).fullCalendar(...options)
            },

            createCalendar() {
                $(this.$el).fullCalendar(_.defaultsDeep(this.config, this.defaultConfig));
                this.hideWeekButtonOnSmallDevices();
            },

            destroyCalendar() {
                $(this.$el).fullCalendar('destroy');
            },

            printCalendar() {
                $(this.$refs.calendar).print();
            },

            setOption(option, value) {
                $(this.$el).fullCalendar('option', option, value);
            },

            hideWeekButtonOnSmallDevices()
            {
                let $button = $('.fc-agendaWeek-button');
                $button.addClass('hidden-sm-down');
            }
        },

        watch: {
            events: {
                deep: true,
                handler(val) {
                    this.$emit('reload-events');
                },
            },
            eventSources: {
                deep: true,
                handler(val) {
                    this.$emit('rebuild-sources')
                },
            },
            resources() {
                $(this.$el).fullCalendar('refetchResources');
            }
        },

        beforeDestroy() {
            this.$off('remove-event')
            this.$off('rerender-events')
            this.$off('refetch-events')
            this.$off('render-event')
            this.$off('reload-events')
            this.$off('rebuild-sources')
        },
    }
</script>

<style src="fullcalendar/dist/fullcalendar.css"></style>
<style src="fullcalendar-scheduler/dist/scheduler.css"></style>
<style>
    .fc-now-indicator {
        border-color: blue;
    }
    .fc-now-indicator-line {
        border-style: dotted;
        border-left-width: 2px !important;
    }
    #calendar {
        min-height: 600px;
    }
</style>
