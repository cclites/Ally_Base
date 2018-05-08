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

        refreshEvents() {
            this.$refs.calendar.fireMethod('refetchEvents');
        },
    }
}