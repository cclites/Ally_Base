export default {
    data() {
        return {
            scheduleModal: false,
            selectedSchedule: {},
            selectedEvent: null,
            initialCreateValues: {},
        };
    },

    methods: {
        createSchedule(date, jsEvent, view, resource) {
            this.hidePreview();
            this.scheduleModal = true;
            this.selectedSchedule = {};
            this.initialCreateValues = {
                'client_id': (this.filterClientId > 0) ? this.filterClientId : this.getInitialFromResource(resource, 'client_id'),
                'caregiver_id': (this.filterCaregiverId > 0) ? this.filterCaregiverId : this.getInitialFromResource(resource, 'caregiver_id'),
            };
            if (date) {
                this.selectedEvent = date;
            }
            else {
                this.selectedEvent = moment().add(59, 'minutes').startOf('hour');
            }
        },

        getInitialFromResource(resource, field) {
            if (resource && resource.id && this.resourceIdField === field) {
                return resource.id;
            }
            return "";
        },

        editSchedule(event, jsEvent, view) {
            this.hidePreview();
            axios.get('/business/schedule/' + event.id)
                .then(response => {
                    this.selectedSchedule = response.data;
                    this.scheduleModal = true;
                })
                .catch(function(error) {
                    alert('Error loading schedule details');
                });
        },

        refreshEvents() {
            this.$refs.calendar.fireMethod('refetchEvents');
        },
    }
}
