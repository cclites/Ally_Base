export default {
    data() {
        return {
            allyPct: 0.05,
            paymentType: 'NONE',
            carePlans: [],
            caregivers: [],
            clients: [],
            client_id: (this.client) ? this.client.id : null,
            end_time: null,
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
            specialHoursChange: false,
            maxHoursWarning: false,
        };
    },

    computed: {

        allyFee() {
            if (!parseFloat(this.form.caregiver_rate)) return null;
            let caregiverHourlyFloat = parseFloat(this.form.caregiver_rate);
            let providerHourlyFloat = parseFloat(this.form.provider_fee);
            let allyFee = (caregiverHourlyFloat + providerHourlyFloat) * parseFloat(this.allyPct);
            return allyFee.toFixed(2);
        },

        displayAllyPct() {
            return (parseFloat(this.allyPct) * 100).toFixed(2);
        },

        totalRate() {
            if (this.allyFee === null) return null;
            let caregiverHourlyFloat = parseFloat(this.form.caregiver_rate);
            let providerHourlyFloat = parseFloat(this.form.provider_fee);
            let totalRate = caregiverHourlyFloat + providerHourlyFloat + parseFloat(this.allyFee);
            return totalRate.toFixed(2);
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

    methods: {
        dayOfMonth(date) {
            return moment(date).format('Do');
        },

        loadAllyPctFromClient(client_id) {
            let component = this;
            axios.get('/business/clients/' + client_id + '/payment_type').then(function(response) {
                component.allyPct = response.data.percentage_fee;
                component.paymentType = response.data.payment_type;
            });
        },

        loadCaregivers() {
            if (this.form.client_id) {
                axios.get('/business/clients/' + this.form.client_id + '/caregivers')
                    .then(response => {
                        this.caregivers = response.data;
                    });
            }
        },

        loadClientData() {
            if (!this.client.id) {
                let component = this;
                axios.get('/business/clients/list')
                    .then(response => {
                        component.clients = response.data;
                        this.loadCaregivers();
                    });
            }
            else {
                // Load caregivers and ally pct immediately
                this.loadCaregivers();
                this.loadAllyPctFromClient(this.client.id);
            }
        },

        getDuration() {
            if (this.endTime && this.startTime) {
                if (this.startTime === this.endTime) {
                    return 1440; // have 12:00am to 12:00am = 24 hours
                }
                let start = moment('2017-01-01 ' + this.startTime, 'YYYY-MM-DD HH:mm');
                let end = moment('2017-01-01 ' + this.endTime, 'YYYY-MM-DD HH:mm');
                console.log(start, end);
                if (start && end) {
                    if (end.isBefore(start)) {
                        end = moment('2017-01-02 ' + this.endTime, 'YYYY-MM-DD HH:mm');
                    }
                    let diff = end.diff(start, 'minutes');
                    if (diff) {
                        return parseInt(diff);
                    }
                }
            }
            return null;
        },

        getStartsAt() {
            if (this.startDate && this.startTime) {
                return moment(this.startDate + ' ' + this.startTime, 'MM/DD/YYYY HH:mm').format('X');
            }
            return null;
        },

        refreshEvents() {
            this.$emit('refresh-events');
        },

        showMaxHoursWarning(response) {
            this.maxHoursWarning = true;
            // Recreate the form with max override
            let data = this.form.data();
            data.override_max_hours = 1;
            this.form = new Form(data);
        },

        hideMaxHoursWarning() {
            this.maxHoursWarning = false;
        },

        handleErrors(error) {
            if (error.response) {
                switch(error.response.status) {
                    case 449:
                        this.showMaxHoursWarning(error.response);
                        break;
                }
            }
        },
    },

    watch: {
        'form.client_id': function(val) {
            this.loadAllyPctFromClient(val);
            this.loadCaregivers();
        },

        'form.caregiver_id': function(val, old_val) {
            if (this.selectedSchedule) {
                // Use the schedule's rates if the caregiver_id matches the schedule's caregiver_id
                if (this.selectedSchedule.caregiver_id == val) {
                    this.form.caregiver_rate = this.selectedSchedule.caregiver_rate;
                    this.form.provider_fee = this.selectedSchedule.provider_fee
                    return;
                }
            }

            this.form.caregiver_rate = this.selectedCaregiver.pivot.caregiver_hourly_rate;
            this.form.provider_fee = this.selectedCaregiver.pivot.provider_hourly_fee;
        },

        'form.hours_type': function(val, old_val) {
            if (old_val) {
                if (val === 'holiday' || val === 'overtime') {
                    this.specialHoursChange = true;
                    return;
                }
            }
            this.specialHoursChange = false;
        },

        model(val) {
            if (!val) {
                this.hideMaxHoursWarning();
            }
        },


    }
}