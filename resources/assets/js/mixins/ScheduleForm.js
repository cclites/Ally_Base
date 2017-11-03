export default {
    data() {
        return {
            allyPct: 0.05,
            carePlans: [],
            caregivers: [],
            clients: [],
            client_id: (this.client) ? this.client.id : null,
            interval: 30, // number of minutes in between each time period
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
            form: new Form({caregiver_id: null}),
        };
    },

    mounted() {
        this.loadCarePlans();
    },

    methods: {
        dayOfMonth(date) {
            return moment(date).format('Do');
        },

        loadAllyPctFromClient(client_id) {
            let component = this;
            axios.get('/business/clients/' + client_id + '/ally_pct').then(response => component.allyPct = response.data.percentage);
        },

        loadCarePlans() {
            let component = this;
            axios.get('/business/care_plans').then(response => component.carePlans = response.data);
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

        allyFee() {
            if (!parseFloat(this.form.caregiver_rate)) return null;
            let caregiverHourlyFloat = parseFloat(this.form.caregiver_rate);
            let providerHourlyFloat = parseFloat(this.form.provider_fee);
            let allyFee = (caregiverHourlyFloat + providerHourlyFloat) * parseFloat(this.allyPct);
            return allyFee.toFixed(2);
        },

        totalRate() {
            if (this.allyFee === null) return null;
            let caregiverHourlyFloat = parseFloat(this.form.caregiver_rate);
            let providerHourlyFloat = parseFloat(this.form.provider_fee);
            let totalRate = caregiverHourlyFloat + providerHourlyFloat + parseFloat(this.allyFee);
            return totalRate.toFixed(2);
        },

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
        },

    },

    watch: {
        'form.caregiver_id': function() {
            this.form.caregiver_rate = this.selectedCaregiver.pivot.caregiver_hourly_rate;
            this.form.provider_fee = this.selectedCaregiver.pivot.provider_hourly_fee;
        },
    }
}