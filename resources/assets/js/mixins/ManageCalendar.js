export default {
    data() {
        return {
            scheduleModal: false,
            selectedSchedule: {},
            selectedEvent: null,
        };
    },

    methods: {
        createSchedule({start, end, jsEvent, view, resource} = {}) {
            this.hidePreview();

            const acceptableTimeViews = ['timelineDay', 'agendaWeek'];
            if (view && !acceptableTimeViews.includes(view.name)) {
                // timelineWeek and month always show 12am-12am, ignore the times for those views (always use 8am start)
                start = start.hour(8); end = null;
            }

            if (!start) {
                start = moment('0800', 'HHmm');
            }

            start = start.local();
            if (end) end = end.local();

            this.selectedSchedule = {
                'starts_at': start.format('YYYY-MM-DD HH:mm:ss'),
                'duration': end ? end.diff(start, 'minutes') : 120,
                'client_id': (this.filterClientId > 0) ? this.filterClientId : this.getInitialFromResource(resource, 'client_id'),
                'caregiver_id': (this.filterCaregiverId > 0) ? this.filterCaregiverId : this.getInitialFromResource(resource, 'caregiver_id'),
            };
            this.scheduleModal = true;
        },

        getInitialFromResource(resource, field) {
            if (resource && resource.id && this.resourceIdField === field) {
                return resource.id;
            }
            return "";
        },

        async editSchedule(event, jsEvent, view) {
            this.hidePreview();
            this.selectedSchedule = {};
            this.scheduleModal = true;
            try {
                const response = await axios.get('/business/schedule/' + event.id);
                this.selectedSchedule = response.data;
            }
            catch (error) {
                alert('Error loading schedule details');
                this.scheduleModal = false;
            }
        },

        refreshEvents() {
            this.$refs.calendar.fireMethod('refetchEvents');
        },
    }
}
