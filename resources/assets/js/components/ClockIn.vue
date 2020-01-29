<template>
    <div>
        <b-card v-if="stats.length > 0"
                header="Debug Mode"
                header-bg-variant="danger"
                header-text-variant="white"
        >
            <table class="table table-bordered">
                <tr v-for="stat in stats" :key="stat.key">
                    <th>{{ stat.key }}</th>
                    <td>{{ stat.value }}</td>
                </tr>
            </table>
        </b-card>
        <b-card :class="{ translucent: !!loadingText }">
            <form @keydown="form.clearError($event.target.name)">
                <b-row>
                    <b-col lg="12">
                        <b-form-group label="Current Time" label-for="time">
                            <b-form-input v-model="time" readonly></b-form-input>
                        </b-form-group>
                        <b-form-group label="Select the client you are clocking in for." label-for="client_id" label-class="required">
                            <b-form-select
                                    id="client_id"
                                    name="client_id"
                                    v-model="form.client_id"
                                    required
                                    :disabled="authInactive"
                            >
                                <option value="">--Select a Client--</option>
                                <option v-for="client in clients" :value="client.id" :key="client.id">{{ client.nameLastFirst }}</option>

                            </b-form-select>
                            <input-help :form="form" field="" text="">Only clients assigned to you will show.</input-help>
                        </b-form-group>
                    </b-col>
                </b-row>
                <b-row>
                    <b-col>
                        <div class="alert alert-warning" v-show="!!locationWarning">
                            <strong>Warning: </strong> {{ locationWarning }}
                        </div>
                    </b-col>
                </b-row>
                <b-row>
                    <b-col md="6" v-if="schedules.length > 0">
                        <div class="form-group" v-for="schedule in schedules" :key="schedule.id">
                            <b-button variant="info" @click="clockIn(schedule)" :disabled="submitting || authInactive">Clock Into Your {{ formatTime(schedule.starts_at) }} Shift</b-button>
                        </div>
                    </b-col>
                    <b-col md="6" v-else>
                        <div class="form-group" v-if="form.client_id">
                            <b-button variant="success" @click="clockInWithoutSchedule()" :disabled="submitting || authInactive">Clock in</b-button>
                        </div>
                    </b-col>
                    <b-col md="6">
                        <div class="d-flex">
                            <user-avatar v-if="form.client_id" :src="avatar" size="75" class="ml-auto" />
                        </div>
                    </b-col>
                </b-row>
                <b-row v-if="form.client_id && schedules.length > 0">
                    <b-col md="12" class="mt-3 text-center text-small">
                        <div v-if="allowUnscheduledClockin">
                            <b-button variant="success" @click="clockInWithoutSchedule()" :disabled="submitting || authInactive">Clock Into An Unscheduled Shift</b-button>
                        </div>
                        <div v-else>
                            <b-button variant="link" @click="allowUnscheduledClockin = true">If you do not see your shift, Click Here.</b-button>
                        </div>
                    </b-col>
                </b-row>
                <b-row>
                    <b-col md="12" class="mt-3">
                        <adjoining-caregivers-card ref="adjoiningCaregivers" :client="form.client_id" :auto-load="false"></adjoining-caregivers-card>
                    </b-col>
                </b-row>
            </form>
        </b-card>
        <b-card class="loading-card" v-show="!!loadingText">
            <div class="text-center">
                <i class="fa fa-spinner fa-spin fa-3x fa-fw"></i>
                <p>
                    {{ loadingText }}
                </p>
            </div>
        </b-card>
    </div>
</template>

<script>
    import FormatsDates from "../mixins/FormatsDates";
    import AuthUser from '../mixins/AuthUser';

    export default {
        mixins: [FormatsDates, AuthUser],

        props: {
            'selectedSchedule': {}
        },

        data() {
            return {
                submitting: false,
                form: new Form({
                    client_id: "",
                    schedule_id: null,
                    latitude: null,
                    longitude: null,
                }),
                clients: [],
                schedules: [],
                stats: [],
                time: null,
                loadingText: null,
                locationWarning: null,
                watchId: null,
                locationOptions: {
                    enableHighAccuracy: true,
                    maximumAge: 15000,
                    timeout: 30000,
                },
                allowUnscheduledClockin: false,
            }
        },

        computed: {
            avatar() {
                let client = this.clients.find(e => e.id == this.form.client_id);
                if (client) {
                    return client.avatar; 
                }
                return '';
            },
        },

        mounted() {
            this.loadClients();
            this.watchLocation();
            this.loadTime();

            // Refresh the time every 15s
            setInterval(this.loadTime, 15000);

            // Automatically stop watching the location if they haven't clocked in within 5 minutes
            setTimeout(this.stopWatchingLocation, 300000);
        },

        methods: {

            showLoading(text = 'Loading..') {
                this.loadingText = text;
            },

            hideLoading() {
                this.loadingText = null;
            },

            checkSelected() {
                if (!this.selectedSchedule.id) return;

                let client = this.clients.findIndex(item => item.id === this.selectedSchedule.client_id);
                if (client === -1) {
                    alert('You are unable to clock in to the selected schedule.  The registry has not assigned you to this client.');
                }

                this.form.client_id = this.selectedSchedule.client_id;
            },

            async loadClients() {
                this.showLoading('Loading clients..');
                try {
                    const response = await axios.get('/caregiver/clients?active=1');
                    this.clients = response.data;
                }
                catch (err) {
                    alert('Unable to load client list.  Make sure you have network connectivity.');
                }
                this.hideLoading();

                // Load the selected schedule
                this.checkSelected();
            },

            async loadSchedules() {
                this.showLoading('Searching available shifts..');
                try {
                    const response = await axios.get('/caregiver/schedules/' + this.form.client_id);
                    this.schedules = response.data;
                }
                catch (err) {
                    alert('Unable to load available shifts.  Make sure you have network connectivity.');
                }

                await this.$refs.adjoiningCaregivers.fetch();

                this.hideLoading();

                // After loading schedules, verify the location.
                // We do this here so the showing/hiding of loading messages don't conflict
                this.verifyLocation();
            },

            async verifyLocation() {
                this.loadLocation();
                if (this.form.latitude === null) {
                    // If the latitude is still null, do not try to verify the location
                    if (!this.locationWarning) this.displayLocationWarning();
                    return;
                }
                try {
                    this.showLoading('Verifying location..');
                    const response = await axios.post('/caregiver/verify_location/' + this.form.client_id, this.form.data());
                    if (response.data.success) {
                        this.hideLocationWarning();
                    }
                    else {
                        this.displayLocationWarning();
                    }
                }
                catch(err) {
                    this.displayLocationWarning();
                }
                this.hideLoading();
            },

            handleLocationError(error) {
                console.log(error);
                switch (error.code) {
                    case 1:
                        this.displayLocationWarning('Location services are disabled.  You may still clock in but the shift will need to be verified by the client or home care company.');
                        break;
                    default:
                        this.displayLocationWarning('Your device\'s location could not be found.  You may still clock in but the shift will need to be verified by the client or home care company.');
                }
            },

            displayLocationWarning(text) {
                if (!text) {
                    text = 'Your current location does not match the client’s address.  You may still clock in but the shift will need to be verified by the client or home care company.';
                }
                this.locationWarning = text;
            },

            hideLocationWarning() {
                this.locationWarning = null;
            },

            watchLocation()
            {
                if (!navigator.geolocation) {
                    alert('Location services are not supported on your device.');
                    return;
                }

                if (this.watchId) {
                    // Already watching
                    return;
                }

                this.watchId = navigator.geolocation.watchPosition(this.setLocation, null, this.locationOptions);
            },

            stopWatchingLocation()
            {
                navigator.geolocation.clearWatch(this.watchId);
                this.watchId = null;
                this.form.latitude = null;
                this.form.longitude = null;
            },

            setLocation(position)
            {
                this.form.latitude = position.coords.latitude;
                this.form.longitude = position.coords.longitude;
            },

            async loadLocation()
            {
                if (this.form.latitude === null || !this.watchId) {
                    this.showLoading('Waiting for your location..');
                    await navigator.geolocation.getCurrentPosition(this.setLocation, this.handleLocationError, this.locationOptions);
                    this.hideLoading();
                }
            },

            clockInWithoutSchedule() {
                this.submitting = true;
                this.form.schedule_id = null;
                this.loadLocation();
                this.submitForm();
            },

            clockIn(schedule) {
                this.submitting = true;
                this.form.schedule_id = schedule.id;
                this.loadLocation();
                this.submitForm();
            },

            async submitForm() {
                this.showLoading('Clocking in to shift..');
                try {
                    const response = await this.form.post('/clock-in');
                    if (response.data.stats) {
                        this.stats = response.data.stats;
                        this.submitting = false;
                    }
                    else {
                        window.location = '/clocked-in';
                    }
                }
                catch (err) {}
                this.hideLoading();
            },

            loadTime() {
                this.time = moment().local().format('LT');
            }
        },

        watch: {
            'form.client_id': function(val) {
                if (val) this.loadSchedules();
            }
        },

    }
</script>

<style>
    .loading-card {
        z-index: 10000;
        position: absolute;
        top: 20%;
        width: 250px;
        left: 50%;
        margin-left: -125px;
    }

    .translucent {
        opacity: .5;
    }
</style>
