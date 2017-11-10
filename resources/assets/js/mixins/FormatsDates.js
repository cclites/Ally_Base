export default {
    methods: {
        formatDate(date) {
            return moment(date).format('L');
        },

        formatTime(dateTime) {
            return moment(dateTime).format('h:mm:ss a');
        }
    }
}