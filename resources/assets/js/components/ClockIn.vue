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
        <b-card title="Select a Shift to Clock In" :class="{ translucent: !!loadingText }">
            <form @submit.prevent="clockIn()" @keydown="form.clearError($event.target.name)">
                <b-row>
                    <b-col lg="12">
                        <b-form-group label="Current Time" label-for="time">
                            <b-form-input v-model="time" readonly></b-form-input>
                        </b-form-group>
                        <b-form-group label="Select the client you are clocking in for." label-for="client_id">
                            <b-form-select
                                    id="client_id"
                                    name="client_id"
                                    v-model="form.client_id"
                                    required
                            >
                                <option value="">--Select a Client--</option>
                                <option v-for="client in clients" :value="client.id" :key="client.id">{{ client.nameLastFirst }}</option>

                            </b-form-select>
                            <input-help :form="form" field="" text="">Only clients assigned to you will show.</input-help>
                        </b-form-group>
                    </b-col>
                </b-row>
                <b-row v-if="allowDebug">
                    <b-col lg="12">
                        <div class="form-check">
                            <input-help :form="form" field="debugMode" text="Enable debug mode (returns variables but does not clock in)"></input-help>
                            <label class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" name="debugMode" v-model="form.debugMode" value="1">
                                <span class="custom-control-indicator"></span>
                                <span class="custom-control-description"></span>
                            </label>
                        </div>
                    </b-col>
                </b-row>
                <b-row>
                    <b-col>
                        <div class="alert alert-warning" v-show="!!locationWarning">
                            <strong>Warning: </strong> {{ locationWarning }}
                        </div>
                        <div class="form-group" v-for="schedule in schedules" :key="schedule.id">
                            <b-button variant="info" type="submit">Clock in your shift at {{ formatTime(schedule.starts_at.date) }}</b-button>
                        </div>
                        <div class="form-group" v-if="form.client_id">
                            <b-button variant="success" type="submit">Clock in to an unscheduled shift</b-button>
                        </div>
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

    export default {
        mixins: [FormatsDates],

        props: {
            'events': {},
            'selected': {}
        },

        data() {
            return {
                form: new Form({
                    client_id: "",
                    schedule_id: null,
                    latitude: null,
                    longitude: null,
                    debugMode: false,
                }),
                clients: [],
                schedules: [],
                allowDebug: false,
                stats: [],
                time: null,
                loadingText: null,
                locationWarning: null,
                watchId: null,
                locationOptions: {
                    enableHighAccuracy: true,
                    maximumAge: 10000,
                    timeout: 30000,
                },
            }
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

            async loadClients() {
                this.showLoading('Loading clients..');
                try {
                    const response = await axios.get('/caregiver/clients');
                    this.clients = response.data;
                }
                catch (err) {
                    alert('Unable to load client list.  Make sure you have network connectivity.');
                }
                this.hideLoading();
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
                this.hideLoading();

                // After loading schedules, verify the location.
                // We do this here so the showing/hiding of loading messages don't conflict
                this.verifyLocation();
            },

            async verifyLocation() {
                this.showLoading('Waiting for your location..');
                if (this.form.latitude === null) {
                    await navigator.geolocation.getCurrentPosition(this.setLocation, this.handleLocationError, {
                        maximumAge: 20000,
                        timeout: 10000,
                    });
                    if (this.form.latitude === null) {
                        // If the latitude is still null, do not try to verify the location
                        if (!this.locationWarning) this.displayLocationWarning();
                        return;
                    }
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
                        this.displayLocationWarning('Location services are disabled.  You may still clock in but the shift will need to be verified by the provider.');
                        break;
                    default:
                        this.displayLocationWarning('Your device\'s location could not be found.  You may still clock in but the shift will need to be verified by the provider.');
                }
            },

            displayLocationWarning(text) {
                if (!text) {
                    text = 'Your current location does not match the client’s address.  You may still clock in but the shift will need to be verified by the provider.';
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

            clockIn() {
                navigator.geolocation.getCurrentPosition(function(position) {
                    this.form.latitude = position.coords.latitude;
                    this.form.longitude = position.coords.longitude;
                    console.log(position.coords);
                    this.submitForm();
                }.bind(this), function(error) {
                    this.form.latitude = null;
                    this.form.longitude = null;
                    console.log(error);
                    this.submitForm();
                }.bind(this), {
                    enableHighAccuracy: true,
                    timeout: 5000,
                    maximumAge: 0
                });
            },

            submitForm() {
                var component = this;
                this.form.post('/clock-in')
                    .then(function(response) {
                        if (response.data.stats) {
                            component.stats = response.data.stats;
                        }
                        else {
                            window.location = '/clock-out';
                        }
                    })
                    .catch(function(error) {
                       component.showManual = true;
                    });
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