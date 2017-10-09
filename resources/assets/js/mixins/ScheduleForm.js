export default {
    data() {
        return {
            caregivers: [],
            clients: [],
            client_id: (this.client) ? this.client.id : null,
            interval: 15, // number of minutes in between each time period
            daysOfWeek: {
                'Sunday': 'su',
                'Monday': 'mo',
                'Tuesday': 'tu',
                'Wednesday': 'we',
                'Thursday': 'th',
                'Friday': 'fr',
                'Saturday': 'sa',
            },
        };
    },

    mounted() {
        this.loadClients();
        this.loadCaregivers();
    },

    methods: {
        dayOfMonth(date) {
            return moment(date).format('Do');
        },

        getUserUtcOffset() {
            return moment().local().format('Z');
        },

        getLocalMomentObject(server_date, server_time) {
            let timestamp = server_date + 'T' + server_time + '+00:00';
            console.log(timestamp);
            let obj = moment(timestamp).local();
            console.log(obj);
            return obj;
        },

        loadCaregivers() {
            if (this.client_id) {
                let component = this;
                axios.get('/business/clients/' + this.client_id + '/caregivers')
                    .then(response => {
                        component.caregivers = response.data;
                    });
            }
        },

        loadClients() {
            if (!this.client) {
                let component = this;
                axios.get('/business/clients/list')
                    .then(response => {
                        component.clients = response.data;
                    });
            }
        },

        refreshEvents() {
            this.$emit('refresh-events');
        }
    },

    computed: {

        startTimes() {
            let date = moment('01/01/2000 00:00:00');
            let rounds = Math.ceil(1440 / this.interval);
            let startTimes = [];
            for (let i = 0; i<rounds; i++) {
                startTimes.push({
                    value: date.format('HH:mm:ss'),
                    text: date.format('LT')
                });
                date.add(this.interval, 'minutes');
            }
            return startTimes;
        },

        endTimes() {
            let date = moment('01/01/2000 ' + this.form.time);
            let rounds = Math.ceil(1440 / this.interval);
            let endTimes = [];
            for (let i = 0; i<rounds; i++) {
                endTimes.push({
                    value: i * this.interval,
                    text: date.format('LT')
                });
                date.add(this.interval, 'minutes');
            }
            return endTimes;
        },

    },

    watch: {
        client_id(val) {
            this.loadCaregivers();
        }
    }
}