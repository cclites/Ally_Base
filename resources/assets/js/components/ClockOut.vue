<template>
    <b-card header=""
        header-bg-variant="info"
        header-text-variant="white"
        >
        <form @submit.prevent="clockOut()" @keydown="form.clearError($event.target.name)">
            <b-row>
                <b-col lg="12">
                    <b-form-group label="Current Time" label-for="time">
                        <b-form-input v-model="time" readonly></b-form-input>
                    </b-form-group>
                </b-col>
            </b-row>
            <b-row v-if="carePlanActivities().length > 0">
                <b-col lg="12" class="with-padding-bottom">
                    <h5>Recommended Care Plan Activities</h5>
                    <div class="form-check">
                        <input-help :form="form" field="activities" text="Check off the activities of daily living that were performed."></input-help>
                        <label class="custom-control custom-checkbox" v-for="activity in carePlanActivities()" style="clear: left; float: left;">
                            <input type="checkbox" class="custom-control-input" v-model="form.activities" :value="activity.id">
                            <span class="custom-control-indicator"></span>
                            <span class="custom-control-description">{{ activity.code }} - {{ activity.name }}</span>
                        </label>
                    </div>
                </b-col>
            </b-row>
            <b-row>
                <b-col lg="12">
                    <h5>Additional Activities Performed</h5>
                        <div class="form-check">
                            <input-help :form="form" field="activities" text="Check off any additional activities of daily living performed."></input-help>
                            <label class="custom-control custom-checkbox" v-for="activity in additionalActivities()" style="clear: left; float: left;">
                                <input type="checkbox" class="custom-control-input" v-model="form.activities" :value="activity.id">
                                <span class="custom-control-indicator"></span>
                                <span class="custom-control-description">{{ activity.code }} - {{ activity.name }}</span>
                            </label>
                        </div>
                </b-col>
            </b-row>
            <b-row>
                <b-col lg="6">
                    <b-form-group label="Recorded Mileage" label-for="mileage">
                        <b-form-input
                                id="mileage"
                                name="mileage"
                                type="number"
                                step="any"
                                v-model="form.mileage"
                        >
                        </b-form-input>
                        <input-help :form="form" field="mileage" text="Enter the number of miles driven during your shift."></input-help>
                    </b-form-group>
                </b-col>
                <b-col lg="6">

                </b-col>
            </b-row>
            <b-row>
                <b-col lg-6>
                    <b-form-group label="Other Expenses" label-for="other_expenses">
                        <b-form-input
                                id="other_expenses"
                                name="other_expenses"
                                type="number"
                                step="any"
                                v-model="form.other_expenses"
                        >
                        </b-form-input>
                        <input-help :form="form" field="other_expenses" text="Enter the amount of expenses incurred during your shift."></input-help>
                    </b-form-group>
                </b-col>
                <b-col lg="6">
                    <b-form-group label="Other Expenses Description">
                        <b-form-textarea v-model="form.other_expenses_desc"
                                         :rows="2">
                        </b-form-textarea>
                    </b-form-group>
                </b-col>
            </b-row>
            <b-row>
                <b-col lg="12">
                    <b-form-group label="Were you injured on your shift?" label-for="caregiver_injury">
                        <b-form-select
                            id="injuries"
                            name="injuries"
                            v-model="form.caregiver_injury"
                            required
                        >
                            <option value="">--Please indicate--</option>
                            <option value="0">No</option>
                            <option value="1">Yes, I was injured.</option>
                        </b-form-select>
                        <input-help :form="form" field="caregiver_injury" text="Indicate if you suffered an injury."></input-help>
                    </b-form-group>
                    <b-form-group label="Were there any other issues on your shift?" label-for="issue_text">
                        <b-textarea
                                id="issue_text"
                                name="issue_text"
                                v-model="form.issue_text"
                                :rows="2"
                        >
                        </b-textarea>
                        <input-help :form="form" field="issue_text" text="Add comments about any issues or injuries on your shift."></input-help>
                    </b-form-group>
                </b-col>
            </b-row>
            <b-row>
                <b-col lg="12">
                    <b-form-group label="Comments / Notes" label-for="caregiver_comments">
                        <b-form-textarea id="caregiver_comments"
                                         v-model="form.caregiver_comments"
                                         :rows="3"
                                         >
                        </b-form-textarea>
                        <input-help :form="form" field="caregiver_comments" text="Enter any important notes or comments about your shift."></input-help>
                    </b-form-group>
                </b-col>
            </b-row>
            <b-row>
                <b-col lg="12">
                    <b-button id="manual-clock-out" variant="danger" type="button" @click="manualSubmit()" v-if="showManual">Manual Clock Out</b-button>
                    <b-button id="complete-clock-out" variant="success" type="submit">Press Here To Clock Out</b-button>
                </b-col>
            </b-row>
        </form>
    </b-card>
</template>

<script>
    export default {
        props: {
            'shift': {},
            'activities': Array,
            'carePlanActivityIds': Array,
        },

        data() {
            return {
                form: new Form({
                    caregiver_comments: null,
                    mileage: 0,
                    other_expenses: 0.00,
                    latitude: null,
                    longitude: null,
                    manual: 0,
                    activities: [],
                    caregiver_injury: 0,
                    issue_text: null,
                    other_expenses_desc: null
                }),
                showManual: false
            }
        },

        methods: {
            clockOut() {
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
                this.form.post('/clock-out')
                    .then(function(response) {
                        window.location = '/clock-in?clocked_out=1'
                    })
                    .catch(function(error) {
                        component.showManual = true;
                    });
            },

            manualSubmit() {
                this.form.manual = 1;
                this.submitForm();
            },

            carePlanActivities() {
                let component = this;
                let activities = this.activities.slice(0);
                return activities.filter(function(activity) {
                   return (component.carePlanActivityIds.findIndex(item => item === activity.id) !== -1);
                });
            },

            additionalActivities() {
                let component = this;
                let activities = this.activities.slice(0);
                return activities.filter(function(activity) {
                    return (component.carePlanActivityIds.findIndex(item => item === activity.id) === -1);
                });
            }
        },

        computed: {
            time() {
                return moment().local().format('LT');
            }
        }
    }
</script>
