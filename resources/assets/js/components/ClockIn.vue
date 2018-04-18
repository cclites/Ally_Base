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
        <b-card title="Select a Shift to Clock In">
            <form @submit.prevent="clockIn()" @keydown="form.clearError($event.target.name)">
                <b-row>
                    <b-col lg="12">
                        <b-form-group label="Current Time" label-for="time">
                            <b-form-input v-model="time" readonly></b-form-input>
                        </b-form-group>
                        <b-form-group label="Select the shift you are clocking in for." label-for="schedule_id">
                            <b-form-select
                                    id="schedule_id"
                                    name="schedule_id"
                                    v-model="form.schedule_id"
                                    required
                            >
                                <option value="">--Select a Shift--</option>
                                <option v-for="item in events" :value="item.id" :key="item.id">{{ getTitle(item) }}</option>

                            </b-form-select>
                            <input-help :form="form" field="" text="Only shifts scheduled within 12 Hours of the current time will show."></input-help>
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
                <b-row v-if="care_plan.activities" class="mb-4">
                    <b-col lg="12">
                        <h4>Care Plan for Selected Client - Please Note:</h4>
                        <label>Activities to be Performed:</label>
                        <ul class="">
                            <li v-for="activity in care_plan.activities" :key="activity.id">{{ activity.name }}</li>
                        </ul>
                        <div v-if="care_plan.notes">
                            <label>Note:</label>
                            <p>{{ care_plan.notes }}</p>
                        </div>
                    </b-col>
                </b-row>
                <b-row>
                    <b-col lg="12">
                        <b-button id="manual-clock-in" variant="danger" type="button" @click="manualSubmit()" v-if="showManual">Manual Clock In</b-button>
                        <b-button id="complete-clock-in" variant="success" type="submit">Complete Clock In</b-button>
                    </b-col>
                </b-row>
            </form>
        </b-card>
    </div>


</template>

<script>
    export default {
        props: {
            'events': {},
            'selected': {}
        },

        data() {
            return {
                form: new Form({
                    schedule_id: "",
                    latitude: null,
                    longitude: null,
                    manual: 0,
                    debugMode: false,
                }),
                allowDebug: false,
                showManual: false,
                stats: [],
                care_plan: {},
            }
        },

        mounted() {
            if (this.selected) {
                this.form.schedule_id = this.selected;
            }
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

            manualSubmit() {
                this.form.manual = 1;
                this.submitForm();
            },

            getTitle(item) {
                return item.title + ' ' + moment.utc(item.start).local().format('LT') + ' - ' + moment.utc(item.end).local().format('LT');
            },

            loadCarePlan(schedule_id) {
                let index = this.events.findIndex(item => item.id == schedule_id);
                if (index != -1) {
                    this.care_plan = this.events[index].care_plan || {};
                }
            },
        },

        computed: {
            time() {
                return moment().local().format('LT');
            }
        },

        watch: {
            'form.schedule_id': function(val) {
                this.loadCarePlan(val);
            }
        },

    }
</script>
