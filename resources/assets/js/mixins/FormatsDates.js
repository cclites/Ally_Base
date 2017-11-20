export default {
    methods: {
        formatDate(date) {
            return moment(date).format('L');
        },

        formatTime(dateTime, seconds = false) {
            let format = 'h:mm a';
            if (seconds) format = 'h:mm:ss a';
            return moment(dateTime).format(format);
        },

        formatDateTime(dateTime) {
            return this.formatDate(dateTime) + ' ' + this.formatTime(dateTime);
        }
    }
}