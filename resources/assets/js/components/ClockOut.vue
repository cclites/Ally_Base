<template>
    <div>
        <b-card :title="`Confirm Clock Out for ${shift.client.name}`" :class="{ translucent: !!loadingText }">
            <form @submit.prevent="clockOut()" @keydown="form.clearError($event.target.name)">
                <b-row>
                    <b-col lg="12">
                        <b-form-group label="Clocked In Time" label-for="time">
                            <b-form-input v-model="clockInTime" readonly></b-form-input>
                        </b-form-group>
                    </b-col>
                    <b-col lg="12">
                        <b-form-group label="Current Time" label-for="time">
                            <b-form-input v-model="time" readonly></b-form-input>
                        </b-form-group>
                    </b-col>
                </b-row>
                <b-row v-if="carePlanActivities().length > 0" class="with-padding-bottom-top blue-box">
                    <b-col lg="12">
                        <h5>Recommended Activities</h5>
                        <div class="form-check">
                            <input-help :form="form" field="activities" text="Check off the activities of daily living that were performed."></input-help>
                            <label class="large-checkbox" v-for="activity in carePlanActivities()" :key="activity.id">
                                <input type="checkbox" v-model="form.activities" :value="activity.id">
                                <span class="custom-control-description">{{ activity.code }} - {{ activity.name }}</span>
                            </label>
                        </div>
                    </b-col>
                    <b-col lg="12" class="with-padding-top" v-if="carePlanNotes">
                        <h5>Notes</h5>
                        <p v-html="carePlanNotes"></p>
                    </b-col>
                </b-row>
                <b-row>
                    <b-col lg="12" class="with-padding-top">
                        <h5>Activities Performed</h5>
                        <div class="form-check">
                            <input-help :form="form" field="activities" text="Check off any activities of daily living performed."></input-help>
                            <label class="large-checkbox" v-for="activity in additionalActivities()" :key="activity.id">
                                <input type="checkbox" v-model="form.activities" :value="activity.id">
                                <span class="custom-control-description">{{ activity.code }} - {{ activity.name }}</span>
                            </label>
                        </div>
                    </b-col>
                </b-row>
                <b-row v-if="business.co_mileage">
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
                <b-row v-if="business.co_expenses">
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
                <b-row v-if="business.co_injuries">
                    <b-col lg="12">
                        <b-form-group label="Were you injured on your shift?" label-for="caregiver_injury" label-class="required">
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
                    </b-col>
                </b-row>
                <b-row v-if="business.co_issues">
                    <b-col lg="12">
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
                <b-row v-if="business.co_comments">
                    <b-col lg="12">
                        <b-form-group label="Comments / Notes" label-for="caregiver_comments">
                            <b-form-textarea id="caregiver_comments"
                                             v-model="form.caregiver_comments"
                                             :rows="3"
                            >
                            </b-form-textarea>
                            <input-help :form="form" field="caregiver_comments" text="Enter any important notes or comments about your shift.  Shift related only: do not use for Client Narrative."></input-help>
                        </b-form-group>
                    </b-col>
                </b-row>
                <b-row v-if="questions.length">
                    <b-col lg="12">
                        <b-form-group v-for="question in questions"
                            :key="question.id"
                            :label="question.question + (question.required ? ' *' : '')">
                            <textarea v-model="form.questions[question.id]" class="form-control" rows="3" wrap="soft"></textarea>
                            <input-help :form="form" :field="`questions.${question.id}`"></input-help>
                        </b-form-group>
                    </b-col>
                </b-row>
                <b-row v-if="goals.length">
                    <b-col lg="12">
                        <h4>Goals:</h4>
                        <b-form-group v-for="goal in goals"
                            :key="goal.id"
                            :label="goal.question">
                            <!-- for some reason b-form-textarea had issues syncing with the dynamic goals object -->
                            <textarea v-model="form.goals[goal.id]" class="form-control" rows="3" wrap="soft"></textarea>
                        </b-form-group>
                    </b-col>
                </b-row>
                <b-row>
                    <b-col lg="12">
                        <b-form-group label="Add Note to Client Narrative" for="narrative_notes">
                            <textarea v-model="form.narrative_notes" id="narrative_notes" class="form-control" rows="3" wrap="soft"></textarea>
                            <input-help :form="form" field="narrative_notes" text="This will be added to the Client Narration."></input-help>
                        </b-form-group>
                    </b-col>
                </b-row>
                <b-row class="my-2">
                    <b-col v-if="business.co_signature">
                        <signature-pad
                            v-model="form.client_signature"
                            :buttonTitle=" 'Add Client Signature' ">
                        </signature-pad>
                    </b-col>
                    <b-col v-if="business.co_caregiver_signature">
                        <signature-pad
                            v-model="form.caregiver_signature"
                            :buttonTitle=" 'Add Caregiver Signature' ">
                        </signature-pad>
                    </b-col>
                </b-row>

                <b-row>
                    <b-col lg="12">
                        <b-button id="complete-clock-out" variant="success" type="submit">Press Here To Clock Out</b-button>
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
            'shift': Object,
            'activities': Array,
            'carePlanActivityIds': Array,
            'business': Object,
            'questions': Array,
            'goals': Array,
        },

        data() {
            return {
                form: new Form({
                    caregiver_comments: null,
                    mileage: "",
                    other_expenses: "",
                    latitude: null,
                    longitude: null,
                    activities: [],
                    caregiver_injury: 0,
                    issue_text: null,
                    other_expenses_desc: null,
                    client_signature: null,
                    caregiver_signature: null,
                    goals: {},
                    questions: {},
                    narrative_notes: '',
                }),
                showManual: false,
                time: null,
                clockInTime: null,
                loadingText: null,
            }
        },

        mounted() {
            this.setTimes();
            this.setupGoalsForm();
            this.setupQuestions();
        },

        methods: {
            showLoading(text = 'Loading..') {
                this.loadingText = text;
            },

            hideLoading() {
                this.loadingText = null;
            },

            clockOut() {
                if (!navigator.geolocation) {
                    alert('Location services are not supported on your device.');
                    return;
                }
                this.showLoading('Waiting for location..');
                navigator.geolocation.getCurrentPosition(position => {
                    this.form.latitude = position.coords.latitude;
                    this.form.longitude = position.coords.longitude;
                    console.log(position.coords);
                    this.hideLoading();
                    this.submitForm();
                }, error => {
                    this.form.latitude = null;
                    this.form.longitude = null;
                    console.log(error);
                    this.hideLoading();
                    this.submitForm();
                }, {
                    enableHighAccuracy: true,
                    timeout: 5000,
                    maximumAge: 0
                });
            },

            async submitForm() {

                if ( this.needsTwoAdls && this.form.activities.length < 2 ) {

                    alert( 'You must have at least 2 activities to clock out' );
                    return;
                }

                this.showLoading( 'Clocking out..' );
                try {
                    await this.form.post(`/clock-out/${this.shift.id}`);
                    window.location = '/schedule?clocked_out=1'
                }
                catch (err) {}
                this.hideLoading();
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
            },

            nl2br(val) {
                if (!val || val.length === 0) {
                    return null;
                }

                return (val + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + '<br/>' + '$2');
            },

            setTimes() {
                this.time = this.formatTime( new Date() );
                this.clockInTime = this.formatTimeFromUTC(this.shift.checked_in_time);
                setInterval(() => this.time = this.formatTime( new Date() ), 1000 * 15)
            },

            setupGoalsForm() {
                this.form.goals = {};
                this.goals.forEach(item => {
                    this.form.goals[item.id] = '';
                });
            },

            setupQuestions() {
                this.form.questions = {};
                this.questions.forEach(item => {
                    this.form.questions[item.id] = '';
                });
            },
        },

        computed: {
            carePlanNotes() {
                if (!this.shift || !this.shift.schedule || !this.shift.schedule.care_plan) {
                    return null;
                }

                return this.nl2br(this.shift.schedule.care_plan.notes);
            },
            needsTwoAdls(){

                if( !this.shift ) return null;
                return ![ 'private_pay' ].includes( this.shift.client.client_type.toLowerCase() );
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

    .blue-box {
        background-color: #e8f2fa;
        border: 1px solid #c8e6f4;
    }
</style>