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
            display: {
                date_format: 'MM/DD/YYYY',
                time_format: 'h:mm A',
            },
            overrideRate: false,
        };
    },

    methods: {
        dayOfMonth(date) {
            return moment(date).format('Do');
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

        loadClientData() {
            if (!this.client) {
                let component = this;
                axios.get('/business/clients/list')
                    .then(response => {
                        component.clients = response.data;
                        this.loadCaregivers();
                    });
            }
            else {
                // Load caregivers immediately
                this.loadCaregivers();
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
                    text: date.format(this.display.time_format)
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
                    text: date.format(this.display.time_format)
                });
                date.add(this.interval, 'minutes');
            }
            return endTimes;
        },

        selectedCaregiver() {
            if (this.form.caregiver_id) {
                for(let index in this.caregivers) {
                    let caregiver = this.caregivers[index];
                    if (caregiver.id == this.form.caregiver_id) {
                        return caregiver;
                    }
                }
            }
            return {
                pivot: {}
            };
        }

    },

    watch: {
        overrideRate(val) {
            if (!val) {
                this.form.caregiver_rate = null;
                this.form.provider_fee = null;
            }
        },
    }
}