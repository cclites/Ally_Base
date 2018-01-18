export default {
    data() {
        return {
            scheduleModal: false,
            selectedSchedule: {},
            selectedEvent: null,
        };
    },

    methods: {
        createSchedule(date, jsEvent, view) {
            this.scheduleModal = true;
            this.selectedSchedule = {};
            if (date) {
                this.selectedEvent = date;
            }
            else {
                this.selectedEvent = moment().add(59, 'minutes').startOf('hour');
            }
        },

        editSchedule(event, jsEvent, view) {
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