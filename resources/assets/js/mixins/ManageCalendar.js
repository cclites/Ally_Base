export default {
    data() {
        return {
            editModal: false,
            createModal: false,
            selectedSchedule: null,
            selectedEvent: null,
        };
    },

    methods: {
        createSchedule(date, jsEvent, view) {
            this.createModal = true;
            this.createType = null;
            if (date) {
                this.selectedEvent = date;
            }
            else {
                this.selectedEvent = moment().add(59, 'minutes').startOf('hour');
            }
        },

        editSchedule(event, jsEvent, view) {
            var component = this;
            component.selectedEvent = event;
            axios.get(this.events + '/' + event.id)
                .then(function(response) {
                    console.log(response.data);
                    component.selectedSchedule = response.data;
                    component.editModal = true;
                })
                .catch(function(error) {
                    alert('Error loading schedule details');
                });

        },

        refreshEvents(hideModals = true) {
            this.$refs.calendar.fireMethod('refetchEvents');
            if (hideModals) {
                this.createModal = false;
                this.editModal = false;
            }
        },
    }
}