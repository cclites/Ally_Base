export default {
    methods: {
        formatDate(date) {
            return moment(date).format('L');
        },

        formatDateFromUTC(date) {
            return moment.utc(date).local().format('L');
        },

        formatTime(dateTime, seconds = false) {
            let format = 'h:mm a';
            if (seconds) format = 'h:mm:ss a';
            return moment(dateTime).format(format);
        },

        formatTimeFromUTC(dateTime, seconds = false) {
            dateTime = moment.utc(dateTime).local();
            return this.formatTime(dateTime, seconds);
        },

        formatDateTime(dateTime) {
            return this.formatDate(dateTime) + ' ' + this.formatTime(dateTime);
        },

        formatDateTimeFromUTC(dateTime) {
            return this.formatDateFromUTC(dateTime) + ' ' + this.formatTimeFromUTC(dateTime);
        }
    }
}