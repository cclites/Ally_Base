<template>
    <b-card header="Clock In"
        header-bg-variant="info"
        header-text-variant="white"
        >
        <form @submit.prevent="clockIn()" @keydown="form.clearError($event.target.name)">
            <b-row>
                <b-col lg="12">
                    <b-form-group label="Current Time" label-for="time">
                        <b-form-input v-model="time" readonly></b-form-input>
                    </b-form-group>
                    <b-form-group label="Select the shift you are checking in for." label-for="schedule_id">
                        <b-form-select
                            id="schedule_id"
                            name="schedule_id"
                            v-model="form.schedule_id"
                            required
                            >
                            <option v-for="item in events" :value="item.id">{{ getTitle(item) }}</option>

                        </b-form-select>
                        <input-help :form="form" field="" text=""></input-help>
                    </b-form-group>
                </b-col>
            </b-row>
            <b-row>
                <b-col lg="12">
                    <b-button id="save-profile" variant="success" type="submit">Complete Check In</b-button>
                </b-col>
            </b-row>
        </form>
    </b-card>
</template>

<script>
    export default {
        props: {
            'events': {},
        },

        data() {
            return {
                form: new Form({
                    schedule_id: null,
                    latitude: null,
                    longitude: null,
                    manual: 0,
                }),
            }
        },

        mounted() {

        },

        methods: {

            clockIn() {
                if (!navigator.geolocation) {
                    alert('Location services are not supported on your device.');
                    return;
                }
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
                this.form.post('/clock-in')
                    .then(function(response) {
                        window.location = '/clock-out';
                    });
            },

            getTitle(item) {
                return item.title + ' ' + moment.utc(item.start).format('LT') + ' - ' + moment.utc(item.end).format('LT');
            }

        },

        computed: {
            time() {
                return moment().local().format('LT');
            }
        }


    }
</script>
